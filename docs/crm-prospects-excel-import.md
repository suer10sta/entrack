# Import Excel des prospects

Le bouton **Importer Excel**, dans Manager → SEO → Prospects, ouvre un formulaire dans un dialogue sur la liste, protégé par `auth:manager`. L’ancienne URL GET ouvre ce dialogue. Les routes GET/POST `/manager/seo/prospects/import` sont déclarées avant la ressource avec paramètre `{prospect}`. Le POST bénéficie de CSRF et d'une limite de dix tentatives par minute.

`ProspectImportController` valide le fichier puis appelle `ProspectExcelImport`. PhpSpreadsheet 3.10.8 est installé via Composer ; la version verrouillée de ZipStream conserve la compatibilité PHP 8.1 existante. Aucune migration supplémentaire n'est nécessaire ; les quatre migrations du socle CRM doivent déjà être déployées.

## Fichier attendu

Format `.xlsx`, maximum 5 Mo, première feuille uniquement, première ligne réservée aux en-têtes. Maximum 1 000 lignes de données et 32 colonnes. Le contenu ZIP décompressé est limité à 50 Mo. Les fichiers illisibles, protégés ou dont les en-têtes reconnus sont répétés sont refusés avant toute écriture.

Colonnes : `name`, `email`, `phone`, `company_name`, `country`, `source`, `utm_source`, `utm_medium`, `utm_campaign`, `utm_content`, `utm_term`, `status`.

`name` peut être remplacé par `first_name` / `last_name`, assemblés en nom complet. Les alias `Prénom`, `Nom`, `Nom de famille`, `Nom complet`, `Téléphone`, `Société`, `Pays`, `Statut` et `company` sont reconnus. Casse, accents et espaces des en-têtes sont normalisés. Les colonnes non reconnues sont ignorées, notamment tous les champs de liaison ou dates métier.

Les téléphones doivent être enregistrés en texte pour conserver zéros initiaux et préfixe `+`. Les formules ne sont pas évaluées : les cellules reconnues contenant une formule ou une erreur Excel rendent leur ligne invalide. Copier/coller leurs valeurs dans Excel avant l'import si nécessaire.

## Validation et doublons

Chaque ligne est validée selon les longueurs et le format email du CRM. Le nom est obligatoire, les autres champs restent facultatifs. Le modèle normalise l'email par `trim + lowercase`, en conservant points et suffixes `+`.

Un email déjà présent dans le fichier ou dans le CRM, même archivé, est ignoré sans mise à jour ni restauration. Les lignes invalides ne réservent pas leur email au détriment d'une ligne valide ultérieure. Les imports sont sérialisés entre eux par un verrou transactionnel PostgreSQL ; aucune contrainte globale d'unicité n'est ajoutée, et la création manuelle existante conserve son comportement.

Les contacts sans email peuvent être créés mais ne sont pas dédupliqués : une nouvelle importation peut donc les recréer. Cette limite est indiquée dans le formulaire et le rapport.

Seuls `nouveau`, `contacte`, `engage`, `demo_essai`, `client`, `perdu` sont stockés. Un statut vide ou non reconnu devient Nouveau ; un statut non reconnu produit un avertissement. Le prospect est créé en Nouveau puis, si nécessaire, changé via `ProspectStatusService` avec origine `manual` et auteur Manager. Importer explicitement Perdu est donc une décision manuelle du Manager, sans changement des règles automatiques.

## Résultats et atomicité

Le rapport distingue des catégories exclusives : créés, doublons, invalides et lignes vides ignorées. Leur somme correspond aux lignes traitées. Les numéros de lignes, résultats et erreurs sont affichés dans une table défilante. Les lignes invalides ne bloquent pas les lignes valides.

Les créations et historiques sont dans une transaction commune : une erreur interne imprévue annule toutes les écritures de cet import et produit une erreur diagnostiquable. Le classeur original n'est pas conservé. Le rapport reste trente minutes dans le cache backend, avec une clé isolée par Manager et identifiant aléatoire ; seule sa référence est en session. Le redirect après POST évite le renvoi du fichier lors du rafraîchissement de la page de résultats.

Aucun User ou Entreprise n'est créé ou lié. Aucun lifecycle event d'inscription, essai, conversion Stripe, email ou appel Analytics n'est déclenché.

## Tests

`php artisan test --compact --filter=ProspectExcelImportTest`

Les tests utilisent une base PostgreSQL dédiée et un schéma jetable par test. Ils couvrent les accès, les vues, les champs, la normalisation, les doublons et archives, les statuts et historiques, les alias, les lignes sans email, les formules/erreurs, les fichiers invalides et limites, la première feuille, le rollback global et l'isolation des rapports entre Managers. Aucune donnée de développement n'est modifiée.

Documentation du lecteur : [PhpSpreadsheet — Reading Files](https://phpspreadsheet.readthedocs.io/en/latest/topics/reading-files/).
