# Liens LinkedIn individuels

## Stockage et génération

La migration `2026_10_27_000004_add_public_token_to_prospects_table.php` ajoute `prospects.public_token` (48 caractères, obligatoire, index unique). Elle attribue également un token aux contacts déjà présents, archivés compris, sans modifier leurs statuts, liens ou dates métier. Son rollback supprime uniquement cette colonne et son index.

Le hook `creating` du modèle `Prospect` génère `bin2hex(random_bytes(24))` : 192 bits aléatoires indépendants de l'ID, de l'email et des autres informations personnelles. Le token n'est pas mass assignable et ne peut pas être remplacé par une sauvegarde Eloquent après création.

La création Manager et l'import Excel utilisent tous deux `Prospect::create()` et reçoivent donc le même comportement automatique. Un doublon Excel ignoré ne crée pas un nouveau token. Aucune génération n'ajoute de statut, activité ou rattachement.

L'attribut calculé `linkedin_url` construit toujours exactement :

`https://dalytrac.com/?utm_source=linkedin&utm_medium=message_prive&ref=TOKEN_PUBLIC`

Ce format est fixe, indépendant des champs d'acquisition UTM existants du prospect et des réglages de langue/URL de l'application. Seul `ref` varie. Aucun identifiant PostgreSQL ou renseignement personnel n'est présent dans le lien.

## Fiche Manager

La fiche affiche la section **Lien LinkedIn**, le lien en lecture seule et **Copier le lien**, sans formulaire de configuration. `public/js/prospect-referral-link.js` utilise le presse-papiers du navigateur ; s'il est indisponible ou refusé, le texte est sélectionné et une instruction de copie manuelle apparaît. Le message de résultat est accessible via une zone `aria-live`.

## Attribution du visiteur

Le middleware web `CaptureProspectReferral`, après le démarrage de session, examine les GET des pages marketing publiques et de Register. Il reconnaît aussi les anciennes routes, notamment `/`, avant leur redirection vers la version localisée, en conservant les paramètres UTM existants.

`ProspectReferralAttribution::resolve()` accepte uniquement le format de token attendu et recherche un prospect non archivé par l'index unique. Aucun endpoint public n'expose les détails du prospect. Les refs invalides, tableaux, inconnues ou archivées n'associent aucun prospect. Une indisponibilité CRM est journalisée sans bloquer la page publique.

Le contexte `crm_prospect_referral` reste dans la session Laravel existante : token, identifiant interne côté serveur, date de capture et paramètres fixes LinkedIn. Les pages suivantes sans `ref` le conservent. Un autre lien valide le remplace ; un `ref` explicitement invalide l'efface. La durée est celle de la session existante, avec un plafond de trente jours vérifié lors de l'utilisation. Aucun nouveau cookie persistant ni localStorage n'est ajouté.

## Register et sécurité du rattachement

Après la réussite du Register, le contrôleur consomme le contexte validé et le transmet au service CRM. Celui-ci le conserve dans `crm_lifecycle_events.payload.referral` du reçu `account.registered`. La session est alors nettoyée pour ne pas réattribuer un compte ultérieur dans le même navigateur.

Cette donnée est une provenance non vérifiée, jamais une preuve d'identité. Le traitement `Verified` ne l'utilise pas pour sélectionner un prospect ou contourner une ambiguïté. Le matching sécurisé par email vérifié, la gestion des doublons, les liens existants et la protection Client/Perdu restent inchangés.

Ainsi, si le lien du prospect A est transféré à une personne dont l'email correspond au prospect B, seul B peut être rattaché après vérification ; A reste inchangé. Sans correspondance email, aucun prospect n'est créé ou rattaché, même avec un ref valide.

Aucun clic n'écrit de statut, ne rattache User/Entreprise ou ne crée une activité CRM. Il n'est pas comptabilisé comme une visite humaine unique : les aperçus LinkedIn et les liens transférés ne constituent pas une identité. Cette étape conserve la provenance, sans ajouter de statistiques de clics ou de tracking d'emails.

## Consentement et périmètre

Le mécanisme utilise uniquement Laravel et PostgreSQL. Il fonctionne sans GA4 ou consentement Analytics. Les paramètres UTM du lien restent disponibles pour le tracking GA4 déjà présent lorsque le consentement l'autorise ; aucune nouvelle configuration GA4, conversion ou logique Stripe n'est introduite.

## Validation et déploiement

Les tests PostgreSQL isolés couvrent création manuelle, import Excel, unicité, immutabilité, format fixe, absence de données personnelles, résolution et refus des tokens, navigation et redirections, absence de changements de statut/liens, migration des anciennes lignes et sécurité Register/Verified face aux liens transférés ou doublons.

- `php artisan test --compact --filter=ProspectReferralTest`
- Suites existantes `ProspectExcelImportTest` et `CrmRegistrationSyncTest`, enrichies pour le nouveau parcours.
- `node tests/JavaScript/ProspectReferralLink.cjs` vérifie la copie et ses replis.

Les tests ne touchent pas la base de développement. Déployer la nouvelle migration (après les quatre migrations du socle si elles sont encore en attente) avant d'utiliser le code de création. Aucun rapprochement des comptes historiques n'est déclenché par la migration.
