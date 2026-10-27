# Socle CRM Prospects

Le CRM appartient à la plateforme DalyTrac. Un prospect représente un contact potentiel ; il n'utilise pas le modèle Client de facturation et n'a aucun tenant propriétaire `entreprise_id`.

## Stockage et relations

- `prospects` : identité, email original et normalisé, propriétaire Manager facultatif, source et cinq UTM, dates métier, rattachement facultatif à un User et une Entreprise. Suppression logique.
- `prospect_status_history` : transitions, ancien/nouveau statut, date, origine, Manager, motif et clé d'idempotence. Historique conservé avec le prospect archivé ; modifications et suppressions individuelles bloquées par Eloquent.
- `prospect_activities` : notes, contacts, appels, emails, campagnes, démos, relances et activités automatiques. Structure uniquement, aucun envoi.
- `crm_lifecycle_events` : événement identifié par le couple unique `(source, event_key)`, type, références facultatives, payload, dates de réception métier et traitement. Peut conserver un événement avant son rattachement. Aucun consommateur Register/Stripe n'est branché.

Les liens User/Entreprise/Manager deviennent nuls en cas de suppression de leur cible. Les suppressions physiques de prospects référencés sont restreintes pour préserver l'historique.

## Statuts et service

`ProspectStatus` centralise : `nouveau`, `contacte`, `engage`, `demo_essai`, `client`, `perdu`.

Créer un prospect initialise `nouveau`, sans inventer une transition historique. Utiliser ensuite `ProspectStatusService::transition()` pour chaque changement : mise à jour et historique sont atomiques, avec verrouillage du prospect. La sauvegarde Eloquent directe d'un changement de statut est bloquée. La méthode interne du modèle et les mises à jour SQL brutes ne doivent pas être utilisées par les futurs contrôleurs.

Les automatismes peuvent avancer dans le pipeline, y compris sauter des étapes. Ils ne peuvent ni régresser, ni mettre en perdu, ni rouvrir un client ou un perdu. Ainsi un événement d'inscription tardif ne peut pas transformer un client en démo/essai. Une transition identique ne crée pas d'historique.

Un changement manuel exige un Manager persistant et peut corriger un statut. Les futurs endpoints devront aussi vérifier l'authentification et les permissions Manager : le service ne remplace pas une Policy.

Une clé d'idempotence enregistre un reçu durable, même pour une transition sans changement. Un rejeu déjà traité rend le statut courant sans réappliquer l'ancien statut. Une réutilisation de clé pour une autre cible ou un autre prospect est rejetée. Les contraintes uniques et les verrous assurent la sérialisation ; une erreur annule statut, historique et reçu dans la même transaction.

## Email et synchronisation future

L'email original est conservé. `email_normalized` applique uniquement trim et lowercase, sans retirer points ou suffixes `+`. Son index n'est pas unique : les doublons et le matching ambigu devront être traités avant toute synchronisation automatique. Aucun email User existant n'est modifié.

Les UTM sont des champs CRM indépendants de GA4. Leur collecte et les changements automatiques des dates d'essai/paiement seront ajoutés lors des étapes métier suivantes.

## Validation et déploiement

Les quatre migrations sont créées mais ne sont pas appliquées automatiquement à la base de développement. Leur déploiement devra suivre la procédure habituelle du projet.

Tests ciblés : `php artisan test --compact --filter=ProspectFoundationTest` utilise SQLite en mémoire. Pour vérifier PostgreSQL, définir temporairement `CRM_TEST_POSTGRES=1` : le test exige une base dédiée dont le nom finit par `_test` ou `_testing`, différente de la base de développement, et utilise un schéma aléatoire supprimé après chaque test. Il n'exécute pas de remise à zéro de la base existante.

La suite couvre création, relations, normalisation, historique transactionnel, rejeux, conflits d'idempotence, régressions interdites, corrections manuelles, rollback sur échec, archivage et rollback des quatre migrations. Elle ne simule pas encore des workers concurrents ni les parcours Register/Stripe, qui restent hors périmètre.
