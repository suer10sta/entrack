# Phase 5 - Étape 3A : D1 Démarrage

## Périmètre

Implémentation D1 uniquement : éligibilité, création unique, envoi, confirmation Q1, formulaire public, réponses, abandon, annulation, relance unique et expiration. Aucun Contact/ticket, notification Contact, email « Votre compte vous attend », traitement métier des réponses, D2-D6, priorité, écran Manager ni statistiques. Le catalogue D2-D6 reste inchangé. La référence des questions est `C:/Users/Station11/Desktop/SEO/Questionnaire Dalytrac.pdf` ; l'exclusion de la fiche automatique du propriétaire a été explicitement confirmée par l'utilisateur.

## Critères réels d'éligibilité

Inspection des modèles, migrations et de RegisteredUserController :

- Vérification : `users.email_verified_at`, cast datetime. Association : `users.entreprise_id` -> `entreprises.id`.
- Destinataire propriétaire : premier utilisateur de l'entreprise par id croissant. Le schéma ne possède pas de FK propriétaire dédiée ; la création d'inscription enregistre cet utilisateur avant les employés ajoutés ensuite. La vérification d'un membre ajouté ultérieurement ne remplace pas celle du propriétaire.
- Vérification datant d'au moins 24 heures, adresse email valide, aucun abandon historique de l'entreprise.
- Projet : Model Project, table `projects`, `entreprise_id` de type texte dans le schéma actuel.
- Client : Model Client, table `clients`, `entreprise_id` numérique.
- Pointage : Model tasks_timer, table `tasks_timer`, `entreprise_id` numérique. Une affectation `employees_tasks` n'est pas un pointage. Tout timer existant compte, même en cours ou en pause.
- Employé : Model Employees, table `employees`, `entreprise_id` et `user_id`. Toutes les lignes comptent, indépendamment de leur état ou archivage, sauf la fiche automatique du propriétaire.

La demande ajoute explicitement les projets au périmètre et ses tests exigent l'absence d'employé : les quatre catégories sont donc vérifiées à l'éligibilité ET à l'annulation.

L'inscription crée automatiquement une ligne Employees liée au propriétaire. Il n'existe pas de marqueur « créé automatiquement ». Exclusion conservatrice retenue : uniquement la première fiche liée au premier User, créée entre son created_at et created_at + une minute. Les autres fiches, y compris une nouvelle fiche du propriétaire créée plus tard, comptent comme activité. Cette fenêtre est une convention d'identification, pas une preuve historique absolue ; un historique atypique ou une inscription très lente reste non éligible plutôt que d'ignorer arbitrairement des employés. Aucun hook ni champ n'a été ajouté aux modules existants.

## Commande et Scheduler

Commande manuelle : `php artisan surveys:process-d1`. En production, son exécution peut envoyer de vrais emails via la configuration existante ; les tests emploient uniquement MailFake ou le transport array.

La commande parcourt par lots de 100 les entreprises avec un utilisateur vérifié depuis au moins 24 heures OU une D1 existante, puis revérifie le véritable propriétaire et les critères dans D1Lifecycle. Elle traite aussi les D1 déjà envoyées pour annulation/relance/expiration. Une erreur est isolée par entreprise, avec journalisation des seuls identifiants et classe d'exception. Les résultats sont comptés en JSON ; une erreur ou livraison non confirmée donne un code d'échec.

Ajout Scheduler dans routes/console.php via le callback de résolution Schedule : une exécution par heure, sans chevauchement pendant 55 minutes. Les quatre tâches préexistantes sont conservées et leur présence est testée. Aucun changement à app/Console/Kernel.php.

Cycle D1 : `once`. Contrainte DB `(entreprise_id, survey_type, cycle_key)` et firstOrCreate, avec verrou PostgreSQL par entreprise. Une limite d'envoi ne crée pas de nouvelle Survey artificielle : si aucun envoi n'est encore possible, on ne crée pas D1 ; une exécution ultérieure peut reprendre.

## Coordination des emails et incidents

SurveyEmailGate est commun aux futurs types. Un advisory lock PostgreSQL de session par base/schéma/entreprise couvre la décision, la réservation, le transport et son résultat. Aucun appel SMTP n'est effectué dans une transaction DB ouverte. Le contrôle examine tous les types et les deux dates sent_at/reminder_sent_at, avec une limite de 72 heures. Les tentatives récentes non confirmées comptent aussi par prudence. Une autre entreprise n'est pas bloquée.

Les mutations Questionnaire sont transactionnelles et une tentative est réservée avant le transport, avec une action unique `delivery:initial:attempt` ou `delivery:reminder:attempt`. Deux nouvelles valeurs de SurveyActionType : EMAIL_ATTEMPTED et EMAIL_UNCONFIRMED ; la migration additive 100001 adapte le CHECK PostgreSQL sans modifier l'ancienne migration. Aucun EmailSendsLog n'est utilisé.

Après confirmation du transport : sent_at ou reminder_sent_at et une action SENT ou REMINDER_SENT sont enregistrés atomiquement. L'instant utilisé pour signer le premier lien est le même que celui conservé dans sent_at ; la construction de l'email utilise une copie de la Survey avant le transport.

Un échec, une annulation du mailer ou un résultat ambigu conserve le reçu et ajoute EMAIL_UNCONFIRMED. Aucun renvoi aveugle de cette tentative : revue manuelle requise. Si le processus meurt après réservation ou si la DB échoue après acceptation SMTP, le reçu seul suffit à empêcher un nouvel envoi automatique. Il ne faut pas le supprimer pour « débloquer » l'envoi. La livraison exactement une fois à travers SMTP et DB n'est pas revendiquée. Une réservation initiale non confirmée peut laisser sent_at NULL ; son éventuel lien ne sera pas accepté tant que la livraison n'est pas réconciliée.

Les changements du propriétaire ou de son adresse vérifiée annulent la D1 existante plutôt que de renvoyer son lien à une autre identité. Les modules CRM, campagnes et leur limite d'envoi sont indépendants.

## Email, lien et accès public

D1SurveyMail lit Q1 et ses six choix dans SurveyCatalog avec la version persistée. Les libellés ne sont pas dupliqués dans le Mailable. Le mailer et l'expéditeur sont ceux de la configuration DalyTrac existante.

Route GET/POST : `/{locale}/questionnaires/D1/{survey}/{token}`, nom `surveys.d1`. URL temporairement signée, liant UUID, token, locale et choix Q1. Pas d'email, nom, identifiant numérique User ou entreprise dans l'URL. Vérifications serveur : signature, UUID, paire Survey/token via comparaison constante, type D1, sent_at présent, échéance exacte sent_at + 15 jours, puis statut ouvert. Modifier la paire ou le choix signé invalide le lien. Le lien est une capacité personnelle de réponse ; il fonctionne sans connexion, même si un autre compte est ouvert dans le navigateur. Il n'expose aucune donnée de compte et ne permet pas de choisir un autre User/Survey dans le formulaire.

Les middlewares web, session, CSRF et throttle restent présents ; seules CheckSubscription et SetUserTimezone sont exclues sur cette nouvelle route, pour que la réponse publique ne dépende pas d'une session ou de l'abonnement connecté. Aucun changement à leurs classes ni aux routes existantes. Cache-Control no-store/private, Referrer-Policy no-referrer et X-Robots-Tag noindex/nofollow. Aucun tracker ou contenu externe n'est chargé par cette page.

Les dates Questionnaire sont sérialisées avec leur décalage UTC explicite pour éviter de perdre une heure avec une session PostgreSQL non UTC ; les comparaisons de fenêtre email utilisent des instants ISO 8601. Aucun changement de timezone global ni de configuration de connexion existante.

## Scanners et confirmation Q1

Un GET ne modifie ni Survey, ni réponse, ni action. Le choix de l'email est simplement présélectionné. Le bouton « Confirmer ma première réponse » effectue un POST CSRF ; l'envoi du formulaire complet est également une interaction explicite pouvant confirmer Q1 et terminer en une transaction.

La première confirmation écrit q1, clicked_at et Q1_CLICKED avec la clé unique `q1:first`. Le statut devient q1_clicked, ou reste relaunched si une relance a déjà eu lieu. Reconfirmer le même choix est un no-op. Un autre POST ne peut pas le remplacer : réponse 409 localisée. Ouvrir ensuite un autre lien de choix montre la première réponse conservée et un message explicite ; le champ Q1 est verrouillé dans le formulaire.

## Formulaire, conditions et validation

La Blade et le JavaScript utilisent les questions du catalogue versionné. Page responsive aux couleurs DalyTrac ; vérifiée à 1280 et 390 pixels, sans débordement horizontal. Une prévisualisation par POST sans mutation permet également de faire apparaître les questions sans JavaScript. Toutes les valeurs réaffichées sont échappées.

- Q1 obligatoire, choix autorisé uniquement.
- Q2 et Q3 : affichées si Q1 est renseignée et différente de reussi/abandon.
- Q2b : seulement si Q2 est elle-même visible et vaut « Un message d’erreur s’affiche ».
- q2_autre : seulement si Q2 visible vaut « Autre (texte libre) ».
- Q4 : seulement si Q1=abandon.

Le serveur calcule les mêmes conditions, vérifie les parents récursivement dans l'ordre du catalogue, ignore entièrement les réponses masquées et les clés inconnues, refuse tableaux/choix invalides et textes de plus de 2000 caractères Unicode. Une erreur de validation ne persiste aucune réponse ni événement de confirmation.

À la soumission finale : verrou Survey, nouvelle vérification du statut et des échéances, validation, réponses applicables, completed_at, puis action ANSWERED unique `answered:final`. Tout est dans la même transaction. Le statut devient answered ou abandon selon Q1. Pour abandon, le détail de l'action indique `status=abandon` ; aucun STOP_COMMERCIAL ni autre effet métier n'est exécuté. Le motif est conservé pour l'étape 3B.

Une enquête answered/abandon refuse les nouvelles soumissions avec 409, sans nouvelle réponse/action. Une enquête cancelled/expired renvoie 410. Les liens invalides ou expirés sont refusés avec un message localisé. Les contraintes DB restent une seconde protection contre les doublons.

## Relance, annulation et abandon

Une seule relance, au premier traitement possible entre J+3 et J+5 inclus, avec nouvelle vérification du statut, de l'activité, du destinataire, de l'abandon global et de la fenêtre de 72 heures. Pas de relance après réponse finale. Un Q1 partiellement répondu peut être relancé, sauf abandon. L'échéance du lien reste celle du premier envoi.

Une enquête sans réponse finale expire à reminder_sent_at + 10 jours. Si la fenêtre de relance a été manquée ou la relance supprimée, elle ne sera pas envoyée tardivement ; l'enquête expire au plus tard à sent_at + 15 jours. L'accès public applique les échéances même entre deux passages du Scheduler.

La présence d'une activité annule une enquête encore ouverte avec une action CANCELLED, sans suppression de réponses ou historique. Les états finaux ne sont pas réécrits. Une réponse Q1=abandon confirmée bloque déjà les sollicitations, même avant la soumission du motif ; après finalisation, le statut abandon est également contrôlé sur tout l'historique entreprise.

Le point d'extension `D1Lifecycle::suppressReminder` réserve idempotemment `reminder:suppressed` pour le futur D2, sans créer D2 ni fermer artificiellement le formulaire D1. Il devra être appelé sous le même verrou entreprise que les envois.

## Traductions et protection du périmètre

D1, ses choix, textes email/page, boutons, erreurs et validations ont des traductions propres FR/EN/NL/ES, vérifiées sans repli français. Les valeurs enregistrées et codes restent inchangés ; traduire un libellé ne change pas sa valeur. Les sections D2-D6 des traductions ont été conservées et comparées avant/après fusion. Aucune modification de config/surveys.php pour cette étape.

Fichiers préexistants modifiés par cette étape : .gitignore (exceptions ciblées), RouteServiceProvider (chargement du seul nouveau fichier de routes), routes/console.php (ajout Scheduler), SurveyActionType (deux types de livraison), SurveyResponse/SurveyAnswer/SurveyAction (format des dates), quatre traductions surveys.php et SurveyFoundationTest (traductions D1 désormais réelles et nombre de types d'action). Les autres fichiers de cette étape sont nouveaux. Aucun fichier des modules protégés n'est modifié.

## Vérification

PostgreSQL dédié de test, schémas survey_test_* jetables, MailFake/array seulement. Le worker indépendant vérifie environnement testing, base suffixée _test/_testing et schéma autorisé avant toute opération.

Tests D1 : éligibilité, fiche automatique, quatre activités, unicité et limite globale, emails/signatures/versions, GET sans mutation, contrôles token/type/paire, conditions et validations, premier choix immuable, réponses finales/abandon, double submit et concurrence réelle, annulation, relance/expiration, suppression future de relance, CSRF, locales, timezone PostgreSQL, livraison non confirmée, Scheduler existant.

JavaScript : six tests des conditions sur le script réel et le catalogue réel. Contrôle navigateur desktop/mobile et six vérifications interactives des changements de conditions.

Aucune migration appliquée sur la base applicative ou de production. Les migrations 100000 puis 100001 doivent être déployées par la procédure habituelle avant d'activer le traitement D1. Aucune commande d'envoi n'a été lancée contre la base applicative pendant cette étape. Arrêt après D1 lifecycle, sans étape 3B.

### Résultats

- D1 seul : 33 tests, 501 assertions, succès.
- Suite combinée D1 + SurveyFoundationTest + ContactFoundationTest + PublicContactSubmissionTest + ManagerContactSupportTest + ManagerContactActionsTest + ManagerContactCardsTest : 127 tests, 1578 assertions, succès, aucun test ignoré.
- JavaScript : 6 tests, tous réussis.
- Navigateur : 6 contrôles interactifs réussis ; rendu à 1280 et 390 pixels, sans débordement horizontal.
- Pint : 23 fichiers PHP ; trois corrections de style puis contrôle --test réussi.
- git diff --check : code 0 ; contrôle additionnel des nouveaux fichiers sans erreur d'espacement.

### Inventaire exact de l'étape 3A

18 fichiers créés :

- app/Console/Commands/ProcessD1Surveys.php
- app/Http/Controllers/SurveyD1Controller.php
- app/Mail/D1SurveyMail.php
- app/Services/Surveys/D1AnswerService.php
- app/Services/Surveys/D1Eligibility.php
- app/Services/Surveys/D1Lifecycle.php
- app/Services/Surveys/SurveyEmailGate.php
- app/Services/Surveys/SurveyLink.php
- database/migrations/2026_10_29_100001_add_survey_delivery_action_types.php
- resources/views/emails/survey_d1.blade.php
- resources/views/surveys/d1.blade.php
- public/css/survey-d1.css
- public/js/survey-d1.js
- routes/surveys.php
- tests/Feature/SurveyD1LifecycleTest.php
- tests/Support/survey-d1-worker.php
- tests/JavaScript/SurveyD1.cjs
- docs/survey-d1-lifecycle.md

12 fichiers modifiés par rapport au début de cette étape :

- .gitignore
- app/Providers/RouteServiceProvider.php
- routes/console.php
- app/Enums/SurveyActionType.php
- app/Models/SurveyResponse.php
- app/Models/SurveyAnswer.php
- app/Models/SurveyAction.php
- resources/lang/fr/surveys.php
- resources/lang/en/surveys.php
- resources/lang/nl/surveys.php
- resources/lang/es/surveys.php
- tests/Feature/SurveyFoundationTest.php

Des changements indépendants apparus dans resources/lang/fr/messages.php et resources/views/components/public/footer.blade.php n'appartiennent pas à cette étape et ont été laissés intacts.
