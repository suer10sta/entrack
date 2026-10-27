# Interface manuelle CRM Manager

## Navigation et routes

Le lien Prospects se trouve uniquement dans la sidebar Manager, sous SEO, après Google Search Console et Google Analytics. Le layout, les cartes, le tableau responsive et la palette existants sont réutilisés.

Toutes les routes ci-dessous sont dans le groupe web avec `auth:manager`, donc sessions, validation Laravel et CSRF pour les écritures :

| Méthode | Chemin | Action |
| --- | --- | --- |
| GET | /manager/seo/prospects | Liste |
| GET | /manager/seo/prospects/create | Formulaire de création |
| POST | /manager/seo/prospects | Création |
| GET | /manager/seo/prospects/{prospect} | Fiche et timeline |
| GET | /manager/seo/prospects/{prospect}/edit | Redirection fiche avec dialogue de modification |
| PUT/PATCH | /manager/seo/prospects/{prospect} | Enregistrement |
| PATCH | /manager/seo/prospects/{prospect}/status | Transition manuelle |
| POST | /manager/seo/prospects/{prospect}/activities | Activité manuelle |
| DELETE | /manager/seo/prospects/{prospect} | Archivage logique |

Les noms de routes utilisent le préfixe `manager.prospects`. Le contrôleur est `app/Http/Controllers/manager/ProspectController.php`. Les vues sont dans `resources/views/manager/prospects` : index, form, show, et les partiels feedback/status.

## Formulaires en dialogue

Les boutons Nouveau prospect et Importer Excel ouvrent des dialogues natifs sur la liste. Les anciennes URL GET redirigent vers cette liste avec le dialogue demandé ouvert. Les erreurs de validation rouvrent le formulaire concerné ; la création conserve les valeurs saisies et sa redirection de succès vers la fiche. Le résultat d’import reste dans le dialogue et dans le cache privé du Manager. Fermeture par la croix, Annuler, Échap ou le fond, puis retour du focus au bouton d’origine. Les services CRM, règles de validation et transactions restent identiques.

## Liste et édition

Les sept compteurs sont globaux sur les prospects non archivés. La recherche est insensible à la casse sur nom complet, email, téléphone et société ; les caractères `%` et `_` sont traités littéralement. Les filtres statut/source, le tri récent/ancien/dernière activité et la pagination 15/25/50/100 sont calculés côté serveur. Les paramètres sont conservés entre les pages. Les sources proposées viennent des prospects non archivés.

La dernière activité considère activités et historique des statuts ; le tri utilise la création en repli. Le champ existant `name` est présenté comme « Prénom et nom » : aucune séparation approximative ni migration supplémentaire n'est introduite.

La création est transactionnelle : création en Nouveau, transition éventuelle par `ProspectStatusService`, puis note initiale facultative. L'auteur Manager est enregistré. La modification utilise une liste blanche de champs de contact/acquisition et conserve les rattachements User/Entreprise. L'email normalisé est toujours recalculé par le modèle.

Le formulaire de statut utilise exclusivement le service central et conserve le Manager et le motif. Un envoi répété du statut courant n'ajoute pas d'historique.

## Activités et conservation

Les types manuels disponibles sont note, contact, appel, email, démo et relance. L'email est une consignation d'action, sans envoi. Une échéance est obligatoire pour une relance ; une date de démo est facultative. Les dates sont explicitement saisies et affichées en UTC. La prochaine relance correspond à la première échéance parmi les relances non terminées. Aucun rappel, notification ou système de clôture des relances n'est ajouté dans cette étape.

La timeline fusionne activités et changements de statut, avec pagination de 20 éléments, dates, auteurs, notes et échéances. La création est indiquée au bas de la dernière page sans créer une activité en double.

L'archivage passe par les Soft Deletes existants. Il retire le prospect de la liste et des accès directs, sans supprimer historique, activités, lifecycle events, User ou Entreprise. Aucune purge ni interface de restauration n'est ajoutée.

## Tests et limites

`php artisan test --compact --filter=ManagerProspectsTest` utilise obligatoirement PostgreSQL dans une base dédiée de test, distincte du développement. Chaque test crée un schéma aléatoire contenant ses seules tables, puis supprime ce schéma. Aucune remise à zéro de la base réelle n'est exécutée.

Les tests couvrent les neuf routes et l'interdiction des sessions visiteur/utilisateur classique, les vues, recherche, filtres, pagination, tri par activité, création, rollback de création, normalisation, liens conservés, historique, activités, validation, protection XSS, timeline paginée et archivage.

Les quatre migrations du socle doivent être déployées avant d'utiliser ces pages sur une base qui ne les contient pas encore. Aucun raccordement Register/Trial, Stripe, campagnes, tracking email, GA4 ou Search Console n'est ajouté. Aucun problème bloquant du socle n'a été identifié ; les doublons d'email restent autorisés et leur résolution appartient à l'étape de matching future.

## Modification compacte

Le bouton Modifier ouvre le même dialogue depuis la liste et la fiche. Il contient nom, email, téléphone, société, pays et source d’acquisition (sélecteur identique à la création ; les sources historiques restent sélectionnables). Les paramètres UTM ne sont plus des champs de ce formulaire : ils restent conservés en base, comme les liens au compte, le statut et le token public. La validation et le PATCH existants sont réutilisés ; les erreurs rouvrent le dialogue sur la fiche avec les valeurs saisies, et le succès conserve la redirection vers la fiche. L’ancienne URL GET edit devient un alias ouvrant ce dialogue. Aucun service métier modifié.
