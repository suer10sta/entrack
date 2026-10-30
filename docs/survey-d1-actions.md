# Phase 5 - Étape 3B : actions D1 et Contact

## Périmètre et référence

Référence : Questionnaire Dalytrac.pdf, règles D1, et demande de l'étape 3B. Uniquement les effets après réponse finale D1 : Contact consolidé éventuel et accusé de prise en compte de l'abandon pour manque de temps. Aucun déclenchement D2-D6, priorité Contact, modification CRM/campagnes/Stripe, nouveau Support, nouvelle route publique ou changement SMTP.

TicketService, ContactManagementService, ContactReplySender, ContactSupportMailer et les routes/interfaces Contact restent inchangés. Aucun Contact n'est créé directement par le moteur : TicketService est l'unique point d'entrée.

## Inventaire

12 fichiers créés :

- app/Services/Surveys/D1ActionService.php
- app/Services/Surveys/D1AbandonMailer.php
- app/Mail/D1AccountWaitingMail.php
- database/migrations/2026_10_29_100002_add_survey_actions_processed_type.php
- resources/views/emails/d1-account-waiting.blade.php
- resources/lang/fr/d1-actions.php
- resources/lang/en/d1-actions.php
- resources/lang/nl/d1-actions.php
- resources/lang/es/d1-actions.php
- tests/Feature/SurveyD1ActionsTest.php
- tests/Support/survey-d1-actions-worker.php
- docs/survey-d1-actions.md

5 fichiers modifiés :

- app/Services/Surveys/D1AnswerService.php : callback après commit de la réponse finale.
- app/Console/Commands/ProcessD1Surveys.php : reprise des actions des D1 terminées.
- app/Enums/SurveyActionType.php : ACTIONS_PROCESSED.
- tests/Feature/SurveyD1LifecycleTest.php : attentes adaptées à la création finale de Contact et à l'accusé d'abandon ; une assertion de libellé anglais lit désormais la traduction actuelle au lieu de figer l'ancienne casse Dalytrac.
- .gitignore : exceptions ciblées des nouveaux tests et de cette documentation.

Aucune réécriture de migration existante. La migration additive étend seulement le CHECK des types survey_actions pour ACTIONS_PROCESSED ; aucune modification de contacts. Comme les traitements de concurrence déjà présents, cette extension cible PostgreSQL. Les migrations ont été testées uniquement dans des schémas jetables de la base dédiée de test, jamais sur la base applicative/production.

## Mapping exact, version 1

Les actions lisent les réponses stockées et les questions de la version persistée. La validation du formulaire n'est pas dupliquée. Le calcul des questions applicables est réutilisé pour ne pas exploiter de réponse masquée. Une version inconnue reste en attente de règles explicitement définies ; aucune règle de version 1 n'est appliquée arbitrairement à une autre version.

| Réponse | Règle technique | Besoin / sujet après préfixe D1 |
| --- | --- | --- |
| q1=connexion | q1.connexion | Problème de connexion |
| q1=probleme_technique | q1.probleme_technique | Problème technique |
| q2=Un message d’erreur s’affiche | q2.erreur | Message d’erreur, avec q2b s'il existe |
| q3=Une assistance technique | q3.assistance | Demande d’assistance |
| q3=Une démonstration | q3.demonstration | Demande de démonstration |
| q1=reussi ET aucune activité réelle | q1.reussi_sans_activite | Démarrage déclaré non constaté |
| q1=reussi avec activité réelle | aucune | Aucun Contact |
| Autres réponses sans déclencheur | aucune | Aucun Contact |
| q1=abandon | aucune règle Contact | Aucun Contact, même en présence de réponses masquées |
| q1=abandon et q4=Je manque de temps | accusé email unique | Votre compte vous attend, sans Contact |

« Je manque de temps » est une valeur exacte du catalogue D1 q4 ; aucune nouvelle interprétation du motif n'a été inventée. La version 1 est la seule version d'actions actuellement prise en charge.

## Consolidation et contenu

Tous les besoins applicables sont collectés dans l'ordre du tableau. Le premier détermine le sujet principal `D1 — {besoin}` ; ils sont tous inclus dans le même message. Une Survey ne génère jamais un Contact par règle.

Message en texte brut lisible : origine Questionnaire D1 - Démarrage, version, liste des besoins, puis libellés français et réponses pertinentes (situation, difficulté, q2b, texte Autre et accompagnement). Aucun token, lien de réponse ou donnée de configuration SMTP n'est ajouté. Le token connu de la Survey est également masqué s'il a été collé dans un texte libre. Le texte utilisateur n'est pas transformé en HTML ; les vues existantes Manager Support assurent l'échappement, vérifié avec une valeur script.

Source ContactSource::D1Survey, statut initial Pending, numéro DT-XXXXXX et événement created attribués par TicketService. IP NULL car l'action est automatique. Email = Survey.email. User et Entreprise = relations explicites de la Survey, sans recherche de rattachement par email. Nom = User.name, sinon Entreprise.name, sinon libellé neutre Utilisateur DalyTrac. Une incohérence User/Entreprise est refusée par TicketService et reste en attente, sans réassociation silencieuse.

Métadonnées du created : survey_type=D1, survey_id, survey_version, rules (clés techniques du tableau). Ni token ni réponses libres dans ces métadonnées. Aucun ContactSupportMailer ni email du formulaire public n'est appelé lors de la création.

## Déclenchement et transactions

D1AnswerService conserve sa transaction de validation et d'enregistrement. Il enregistre un callback afterCommit uniquement pour la soumission finale. Le callback attend le commit externe le plus haut si la réponse était dans une transaction englobante. Un rollback externe ne déclenche aucune action.

Le Controller public ne contient aucune règle métier supplémentaire. GET, prévisualisation et confirmation Q1 seule n'appellent pas les actions. D1ActionService exige D1, completed_at, statut answered/abandon et reçu answered:final avant tout effet.

Le moteur ouvre ensuite une transaction distincte et verrouille la Survey. Contact via TicketService, lien contact_id, reçu TICKET et reçu de décision sont validés ensemble sur la même connexion. Un échec annule Contact, lien, reçus et allocation de numéro de cette tentative, tout en conservant les réponses déjà validées et completed_at. Le callback journalise seulement Survey ID et classe d'exception et laisse la page publique afficher la réussite de l'enregistrement des réponses.

La commande existante surveys:process-d1 reprend ensuite les D1 terminées dont la décision n'est pas encore enregistrée. Elle n'ajoute pas de nouvelle tâche Scheduler. Une erreur compte dans le résultat de la commande et peut être reprise au passage suivant. Une Survey déjà liée à un Contact sans reçu automatique n'est pas écrasée : une réconciliation explicite est nécessaire.

## Idempotence et suppression

- `D1:CREATE_CONTACT` : reçu TICKET unique par Survey, créé seulement si le Contact a réellement été créé dans la même transaction.
- `D1:ACTIONS_EVALUATED` : reçu ACTIONS_PROCESSED unique, détail JSON non sensible contact_required / abandon_email. Il fige aussi une décision sans Contact, sans inventer de TICKET. Le retrait ultérieur d'une activité ne transforme pas une décision déjà traitée en nouveau ticket.
- Verrou Survey et UNIQUE(survey_id, action_key) empêchent deux processus d'exécuter deux fois la création.
- Un reçu TICKET déjà présent suffit à interdire toute recréation, même si le reçu de décision manque.
- Supprimer le Contact via Manager Support met contact_id à NULL, mais conserve les reçus append-only. Rejouer la Survey retourne sans créer de remplacement.

Les réponses terminées ne sont pas modifiées et l'état answered/abandon reste celui décidé par le lifecycle.

## Activité réelle pour reussi

Réutilisation exacte de D1Eligibility::hasActivity : projets, clients, pointages et employés réels ; seule la fiche automatique du propriétaire est exclue selon la convention existante documentée en 3A. Pas de seconde définition ni de hook dans les modules d'activité. Avec activité réelle : aucun ticket. Sans activité : un Contact « D1 — Démarrage déclaré non constaté ».

## Email d'abandon

D1AbandonMailer traite seulement abandon terminé, avec motif exact q4=Je manque de temps et décision enregistrée. L'état abandon et l'arrêt des sollicitations Questionnaire restent conservés. Cet email est l'accusé exceptionnel explicitement prévu par le document, pas une nouvelle invitation Questionnaire ni une campagne CRM. Il ne dépend pas de la fenêtre de trois jours des invitations/relances. Aucun appel aux campagnes ou à leur consentement et aucun changement SMTP.

Le message « Votre compte vous attend » existe en FR/EN/NL/ES. Il rappelle le choix d'arrêter les questionnaires et renvoie au site public, sans lien Survey, token, offre ou promesse de prolongation.

Le verrou d'envoi entreprise existant est réutilisé. Le rendu a lieu avant réservation. Le reçu EMAIL_ATTEMPTED `D1:ACCOUNT_WAITING:ATTEMPT` est validé avant le transport, hors transaction SMTP. Après acceptation confirmée : AUTO_EMAIL `D1:ACCOUNT_WAITING:SENT`. En cas d'échec/annulation/ambiguïté : EMAIL_UNCONFIRMED, sans AUTO_EMAIL mensonger et sans renvoi automatique aveugle. Le reçu de tentative subsiste aussi si le processus s'arrête après acceptation mais avant enregistrement du succès : revue manuelle, pas de tentative répétée.

Un problème de rendu ou un verrou momentanément occupé avant réservation peut être repris par la commande. Une tentative déjà réservée n'est jamais rejouée. Cette stratégie garantit au plus une tentative SMTP automatique ; elle ne prétend pas rendre la livraison SMTP et la DB atomiques.

## Manager Support

Le ticket D1 utilise l'interface existante : liste et filtre source D1, numéro, identité, sujet, message, état Pending, détail et historique. Tests d'intégration de consultation et d'échappement, puis utilisation de ContactManagementService pour reply/close/reopen/delete, sans logique spéciale. Les tests complets Phase 4 vérifient aussi leurs routes et l'envoi des réponses Manager. Aucun changement dans ces services, routes ou vues.

## Validation

- Nouveaux tests seuls : 20 tests, 209 assertions, succès.
- Suite actions D1 + D1 lifecycle + Survey foundation + ContactFoundationTest + PublicContactSubmissionTest + ManagerContactSupportTest + ManagerContactActionsTest + ManagerContactCardsTest : 147 tests, 1792 assertions, succès, aucun test ignoré.
- PostgreSQL dédié de test et schémas survey_test_* jetables ; MailFake/array seulement, aucun email réel.
- Concurrence réelle entre processus : un Contact / un reçu TICKET ; un accusé d'abandon / un AUTO_EMAIL.
- Tests d'échec après allocation et d'échec d'insertion TICKET, reprise par commande, suppression Contact puis rejeu, commit/rollback englobant, identités, six règles, absence de ticket, conservation de décision, secrets, Manager Support et quatre langues.
- Pint sur les 14 fichiers PHP concernés ; résultat final et diff --check indiqués dans le rapport de livraison.

Arrêt après les actions D1. Aucune migration exécutée sur la base applicative ou de production.
