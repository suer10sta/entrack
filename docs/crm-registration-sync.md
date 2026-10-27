# CRM : inscription et vérification email

## Périmètre et fichiers

La synchronisation concerne uniquement les nouvelles inscriptions passant par `RegisteredUserController::store()` après ce déploiement. Elle ne crée jamais de prospect, ne recherche pas tous les comptes historiques et ne dépend d'aucun consentement ou service Analytics.

- `app/Services/CrmRegistrationSync.php` : préparation, matching confirmé, audit et gestion non bloquante des erreurs.
- `app/Listeners/SyncProspectAfterVerification.php` et `app/Providers/EventServiceProvider.php` : écoute de l'événement Laravel `Verified` existant.
- `app/Http/Controllers/Auth/RegisteredUserController.php` : un appel après toutes les créations métier réussies, après `Auth::login`, avant l'appel GA4 existant. Le parcours de création Entreprise/User/Employé et le début d'essai restent inchangés.
- `app/Http/Controllers/Auth/VerifyEmailController.php` : le mécanisme de vérification reste identique ; les branches déjà vérifiées retentent uniquement le traitement d'un événement CRM éligible. Signature et hash sont toujours contrôlés avant le callback public.
- `app/Models/Prospect.php` : extraction de la normalisation existante en méthode réutilisable `normalizeEmail`.
- `tests/Feature/CrmRegistrationSyncTest.php` et exception `.gitignore` correspondante.

Aucune nouvelle table ou migration, aucun raccordement Stripe, aucune modification du design, de GA4 ou de Search Console.

## Deux étapes métier

À la fin du Register, un événement `registration / user:{id}:registered` de type `account.registered` conserve User/Entreprise, l'email normalisé, les identifiants de candidats éventuels et le début réel d'essai. Son état initial est `awaiting_verification`, sans lien définitif ni changement de statut. Une inscription sans prospect conserve uniquement ce reçu, sans créer de contact CRM.

L'instant de début d'essai est la création de l'Entreprise, dont l'essai est défini dans le même parcours Register. Il est capturé dans le payload et reporté plus tard vers `trial_started_at`. La date de vérification ne sert jamais de début d'essai ; `trial_ends_at` n'est pas modifié.

Après `Verified`, le service relit le User en base, exige un email réellement vérifié et un reçu de Register existant. Il vérifie que User, Entreprise et email correspondent toujours au snapshot. Un ancien compte sans reçu n'est pas rapproché automatiquement.

## Matching prudent

Le matching utilise `prospects.email_normalized`, avec la même normalisation que le modèle : trim + lowercase, sans suppression de points ou suffixes `+`.

Il faut exactement un prospect non archivé et aucun doublon d'email normalisé côté User. Plusieurs prospects, même si l'un est déjà lié, rendent le matching ambigu. Les conflits recensent un motif et les identifiants disponibles dans le payload, et sont journalisés sans choix arbitraire du premier résultat. Les diagnostics limitent les listes à trois identifiants, suffisants pour établir l'ambiguïté.

Un lien partiel, ou un rattachement à un autre User/Entreprise, est conservé et signalé comme conflit. Un rattachement complet au même compte peut terminer son traitement sans déplacer les liens.

Après confirmation, le service renseigne `linked_user_id`, `linked_entreprise_id`, `linked_at` (vérification) et `trial_started_at` (Register). Les dates déjà établies sont conservées. Les transactions CRM utilisent temporairement UTC pour éviter qu'une session PostgreSQL dans un autre fuseau ne décale les colonnes `timestamptz` par rapport aux dates métier existantes sans fuseau. Le fuseau de session est restauré à la fin de la transaction.

## Statuts, timeline et idempotence

Nouveau, Contacté et Engagé passent à Démo / Essai exclusivement via `ProspectStatusService`, avec origine `registration`. Un prospect déjà en Démo / Essai n'ajoute pas un historique identique.

Client conserve son statut. **Perdu reste entièrement manuel** : aucune entrée ou sortie automatique de ce statut. Client/Perdu peuvent toutefois être rattachés à leur compte vérifié sans changement de statut.

Le rattachement ajoute une seule activité automatique « Compte DalyTrac créé / email vérifié ». Le changement de statut, s'il existe, est une entrée distincte de l'historique central. La timeline existante affiche ces deux informations sans nouvelle table.

Le reçu d'inscription est unique par `(source, event_key)`. La transition réutilise la clé stable distincte `user:{id}:demo_essai` du service de statuts : un reçu décrit le rattachement et l'autre garantit l'idempotence de la transition. Rattachement, activité, historique et traitement du reçu sont atomiques. Les verrous de lignes et un verrou transactionnel PostgreSQL par email sérialisent les traitements concurrents.

Les états terminaux du reçu sont `linked`, `no_match` ou `conflict`, avec `processed_at`. Les callbacks répétés ne déplacent aucun lien et ne recréent ni activité ni historique. Un conflit traité n'est pas retenté aveuglément : la résolution Manager reste une étape ultérieure.

## Erreurs et reprise

Toute exception CRM annule ses propres écritures, mais laisse le compte créé/vérifié utilisable. L'erreur contient stage, User, clé stable et exception dans les logs. Si le reçu est accessible, il est conservé en état `failed`, sans `processed_at`, avec classe d'erreur et date.

Un nouveau callback signé valide sur un email déjà vérifié retente un reçu échoué ou en attente, sans déclencher une nouvelle vérification ni un nouvel email de bienvenue. Le service `verified` est aussi rejouable de façon ciblée par un opérateur pour ces reçus éligibles. Aucun balayage de comptes historiques ni outil de réconciliation massive n'est ajouté.

Si le stockage CRM est totalement indisponible dès Register (notamment migrations non déployées), aucun reçu ne peut être garanti : les logs constituent alors la trace à réconcilier manuellement. Restaurer le stockage ne déclenche pas de rapprochement historique automatique. Les quatre migrations du socle doivent donc être appliquées avant d'activer le parcours en production.

## Validation

`php artisan test --compact --filter=CrmRegistrationSyncTest`

La suite exige une base PostgreSQL dédiée `_test`/`_testing`, différente de la base de développement. Elle crée puis supprime uniquement un schéma aléatoire propre à chaque test. Les tables métier minimales servent de fixtures ; le middleware de contrôle d'abonnement est désactivé dans ces tests pour éviter d'introduire Stripe dans ce périmètre. Les protections de signature/hash et le code Register/Verify réels restent testés.

Les cas couverts incluent absence de prospect, attente de vérification, liens corrects, dates d'essai et de confirmation, statuts autorisés et protégés, doublons User/Prospect, liens partiels, comptes historiques, email changé, archivage, callbacks invalides, rejouabilité, rollback, indisponibilité CRM, indépendance GA4 et maintien du parcours Register/Verify.
