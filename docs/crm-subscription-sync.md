# CRM — paiement Stripe confirmé

## Branchement

`admin/SubscriptionController::choosePlan()` prépare Stripe Checkout (Price ID existant, metadata plan_name / entreprise_id / user_id / renewal_preference). `paymentSuccess()` récupère la Checkout Session serveur avec Cashier et vérifie payment_status=paid, le plan et l’entreprise. Après sauvegarde de type_compt, account_status=active, trial_ends_at et auto_renew, il appelle `CrmSubscriptionSync::paid()`, avant les actions de renouvellement/facturation. Aucun contrôle Stripe, activation ou redirect existant n’est modifié. Le hook et le service isolent les exceptions CRM de la réponse paiement.

Le service refait les contrôles de cohérence essentiels et contrôle le user_id des metadata lorsqu’il est présent. L’entreprise reste la référence : seuls les prospects non archivés avec linked_entreprise_id correspondant sont recherchés, sans matching email et sans création de prospect. L’utilisateur payeur peut être différent du linked_user_id ; il doit appartenir à l’entreprise et correspondre aux metadata du Checkout.

## Statuts et date

Un prospect unique en demo_essai passe à client par ProspectStatusService, source stripe. Les statuts nouveau/contacte/engage autorisent également cette progression selon les règles centrales existantes : conversion acceptée et signalée dans le payload historical_status/from_status ainsi que dans les logs. Client et Perdu restent inchangés, sans nouvelle activité ni modification de date. Perdu reste exclusivement manuel.

Le champ existant first_paid_at sert de date de première conversion (pas de converted_at ni de migration). Il est renseigné une seule fois avec la date de confirmation CRM serveur, et non avec session.created qui date l’ouverture du Checkout, pas le règlement.

## Idempotence et diagnostics

Un reçu crm_lifecycle_events (source stripe, event_type subscription_paid, clé checkout:SESSION_ID) conserve Prospect, User, Entreprise, Checkout, abonnement/facture Stripe, montant en unités mineures, devise, date et résultat. L’unicité SQL source/event_key et des verrous transactionnels sur l’entreprise, le reçu et les prospects empêchent les rejeux. Le service central crée son propre reçu technique prospect.status_transition avec clé checkout:SESSION_ID:client. Ce sont deux types distincts, pas deux conversions ; aucun n’est recréé au rejeu.

Transition, first_paid_at, activité et finalisation du reçu sont atomiques. La timeline existante montre « Abonnement DalyTrac payé » et la transition vers Client. Aucune vue ni requête timeline n’est modifiée.

Résultats : converted, already_client, lost_preserved, no_match, conflict. Les conflits conservent une raison et les IDs candidats (au plus trois pour le diagnostic). En cas d’échec, rollback des écritures CRM, reçu conservé avec state=failed/error_class et processed_at vide, logs d’erreur. Un retraitement explicite avec la même session vérifiée est possible sans doublon ; aucun mécanisme de retry automatique n’est ajouté.

## Limites du flow volontairement conservé

Le retour navigateur n’est jamais une preuve. Sans session Stripe valide/non payée/mauvais plan/mauvaise entreprise, le hook n’est pas atteint. Un refresh après activation suit le retour anticipé préexistant « Abonnement déjà activé », sans nouvelle requête Stripe ni écriture CRM. Il ne relance donc pas un échec CRM antérieur. Aucun rattrapage historique n’est effectué. Un client qui ne revient jamais sur success n’est pas converti par cette étape.

Un StripeWebhookController historique existe (invoice.payment_succeeded : activation, Analytics, facture), mais ne contient aucun appel CRM et reste inchangé. Il ne double donc pas la conversion CRM. S’il active déjà le plan avant le retour success, le retour anticipé existant peut empêcher le hook CRM : c’est une limite conservée, sans création de dépendance webhook ni changement du flow demandé.

Aucun appel à GA4, Consent Mode, Measurement Protocol ou Search Console. Aucun changement Checkout, Price ID, metadata, trial, renouvellement ou Cashier. Aucune campagne ni tracking email ajouté.

## Validation

Tests dédiés CrmSubscriptionSyncTest dans un schéma PostgreSQL jetable sur une base de test explicitement distincte de la base de développement. Checkout serveur simulé au niveau client Stripe ; vrai contrôleur paymentSuccess, vraies sauvegardes entreprise et transactions CRM. Aucun paiement externe et aucune migration sur la base de développement/production.
