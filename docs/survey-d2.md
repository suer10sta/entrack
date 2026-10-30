# D2 — Premier usage

## 1. Fichiers créés et modifiés

Créés :

- `app/Console/Commands/ProcessD2Surveys.php`
- `app/Http/Controllers/SurveyD2Controller.php`
- `app/Http/Controllers/SurveyPublicController.php`
- `app/Mail/D2SurveyMail.php`
- `app/Mail/QuestionnaireMail.php`
- `app/Services/Surveys/D2ActionService.php`
- `app/Services/Surveys/D2AnswerService.php`
- `app/Services/Surveys/D2Eligibility.php`
- `app/Services/Surveys/D2Lifecycle.php`
- `app/Services/Surveys/SurveyActionService.php`
- `app/Services/Surveys/SurveyAnswerService.php`
- `app/Services/Surveys/SurveyLifecycle.php`
- `database/migrations/2026_10_29_100003_add_d2_signal_action_types.php`
- `tests/Feature/SurveyD2Test.php`
- `tests/Support/survey-d2-worker.php`
- `tests/JavaScript/SurveyD2.cjs`
- `docs/survey-d2.md`

Modifiés pendant D2 :

- `app/Services/Surveys/D1ActionService.php`
- `.gitignore`
- `app/Enums/SurveyActionType.php`
- `app/Http/Controllers/SurveyD1Controller.php`
- `app/Mail/D1SurveyMail.php`
- `app/Services/Surveys/D1AnswerService.php`
- `app/Services/Surveys/D1Lifecycle.php`
- `app/Services/Surveys/SurveyLink.php`
- `resources/lang/en/surveys.php`
- `resources/lang/es/surveys.php`
- `resources/lang/fr/surveys.php`
- `resources/lang/nl/surveys.php`
- `resources/views/emails/survey_d1.blade.php`
- `resources/views/surveys/d1.blade.php`
- `routes/console.php`
- `routes/surveys.php`
- `tests/Feature/SurveyD1LifecycleTest.php`
- `tests/Feature/SurveyFoundationTest.php`

Aucun fichier supprimé. Les changements Contact/D1 actions déjà présents avant cette intervention sont conservés. Les vues, CSS et JS D1 servent aux deux questionnaires : les vues reçoivent maintenant le type et le dictionnaire UI ; aucun deuxième thème ni deuxième moteur de conditions.

## 2. Éligibilité

La création et l'envoi initial utilisent exclusivement `entreprise.trial_ends_at - 12 jours <= maintenant UTC < trial_ends_at`. Aucun calcul sur `entreprise.created_at`, aucun champ `trial_started_at`. Le flux RegisteredUserController initialise trial_ends_at à maintenant + 15 jours ; ce calcul applique la règle confirmée par l'utilisateur.

Entreprise existante, type_compt NULL, destinataire = premier User de l'entreprise par id, email vérifié et valide, aucun abandon global Survey. Aucun abonnement local de statut active/trialing/past_due/unpaid avec ends_at NULL ou futur. Toutes les lignes sont examinées : une ligne annulée plus récente ne masque pas un abonnement actif. Aucun appel réseau Stripe et aucune modification de facturation. Le trial est revérifié à la réservation de chaque envoi. Après sa fin ou un abonnement devenu pertinent, une D2 ouverte est annulée au traitement suivant, avec historique conservé.

## 3. Cycle

`cycle_key = once` : le cycle unique de premier essai d'une entreprise, comme D1. Unicité existante (entreprise_id, survey_type, cycle_key). Les reports, prolongations, annulations et retries ne créent pas un nouveau cycle. Aucun D2 répétitif par date de fin mouvante.

## 4. Interaction D1/D2

D2 est planifié avant D1. La barrière partagée entreprise impose 72 heures entre emails Survey, initial et relance inclus. Si elle bloque D2, aucune relance D1 n'est supprimée prématurément. À la réservation effective de l'envoi initial D2, la méthode existante suppressReminder ajoute reminder:suppressed sur les D1 non finalisés, sans supprimer le questionnaire, ses réponses ou son historique, ni fermer sa page. Un D1 finalisé reste intact.

Cette réservation est durable avant SMTP : comme D1, un envoi non confirmé n'est jamais réessayé aveuglément. La suppression de relance D1 reste alors conservée par prudence ; le reçu EMAIL_UNCONFIRMED permet une vérification manuelle.

## 5. Email et page publique

D2SurveyMail et SurveyD2Controller réutilisent QuestionnaireMail, SurveyPublicController, SurveyLink et les vues D1. Route GET/POST /{locale}/questionnaires/D2/{survey}/{token}, sans login, protégée par signature, token, type attendu, date limite exacte, CSRF et throttle. GET préremplit Q1 sans aucune écriture. POST confirm_q1 enregistre volontairement la première réponse ; POST final termine puis exécute les actions après commit. États invalides, expirés, annulés, déjà répondus et confirmation traduits.

## 6. Questions conditionnelles

Catalogue D2 versionné existant, version persistée sur la Survey. Q1 obligatoire : usage_reel/decouverte/pas_temps/pas_donnees/blocage. Q2 et Q3 uniquement usage_reel ou decouverte ; texte q2_autre uniquement pour Autre (texte libre). Q4 facultative et toujours proposée. Les champs masqués sont ignorés au serveur ; JS désactive les contrôles invisibles ; prévisualisation POST utilisable sans JS. Texte libre limité à 2 000 caractères Unicode. Q1 confirmée immuable.

## 7–11. Mapping des actions, Contacts, signaux et consolidation

| Réponse finale | Résultat |
| --- | --- |
| Q1 blocage | Un Contact, source `d2-survey`, priorité high |
| Q4 Oui, une courte démonstration | Un Contact, priorité normal hors blocage |
| Q4 Oui, un guide ciblé | Un Contact, priorité normal hors blocage |
| Plusieurs besoins | Un seul Contact contenant tous les motifs et réponses utiles ; high si blocage |
| Q1 pas_temps ou pas_donnees | Signal PROJECT_FOLLOW_UP, détail JSON signal=relance_projet et reason ; aucun Contact pour cette seule raison |
| Q1 usage_reel + Q3 Oui, clairement ou En partie | Signal VALUE_CONFIRMED, détail JSON signal=valeur_constatee et result ; aucun lancement de D3 |
| Autres réponses | Aucun Contact/signal injustifié |

Les signaux sont conservés dans survey_actions, pas dans une nouvelle table ni sous forme d'un email supplémentaire. Un besoin Q4 peut se cumuler avec relance_projet ; un Contact est alors créé pour Q4. TicketService existant est utilisé, sans modification : numéro DT, identité par relations utilisateur/entreprise, métadonnées de Survey, message lisible et token personnel masqué.

## 12. Idempotence et reprise

Verrou PostgreSQL commun par entreprise pour les envois ; verrou de ligne Survey pour les réponses et les actions. Contraintes uniques réponse/question, entreprise/type/cycle et action_key. Actions après commit des réponses ; Contact, numéro, lien Survey, signaux et reçu métier sont atomiques. Les clés D2:CREATE_CONTACT, D2:ACTIONS_EVALUATED, D2:PROJECT_FOLLOW_UP et D2:VALUE_CONFIRMED empêchent les doublons. Après suppression du Contact, son reçu durable interdit toute recréation. Une erreur d'action conserve les réponses et la commande réessaie uniquement les actions non confirmées.

## 13. Relance et expiration

Une seule relance entre J+3 et J+5 après l'envoi, sous réserve de la barrière 72 h, d'un trial toujours pertinent et de l'absence de finalisation/abandon. Expiration au premier des deux termes : relance + 10 jours ou envoi initial + 15 jours. La relance ne prolonge jamais la signature du lien initial. Après J+5, aucune relance tardive.

## 14. Scheduler et commandes

`php artisan surveys:process-d2` traite les entreprises par lots et reprend les actions finales en erreur. Tâche hourly, withoutOverlapping(55), avant surveys:process-d1. La commande D1 et les autres tâches sont conservées.

## 15. Traductions

FR/EN/NL/ES : catalogue D2 complet et dictionnaire d2_ui complet (email, relance, questions/choix, boutons, validation, confirmation et erreurs). Aucun fallback français requis pour D2. Les données traduites de D1 et D3–D6 sont conservées.

## 16–18. Intégration et périmètre

Test HTTP Manager Support : liste filtrée source D2 + priorité high, détail, identité, Reply/Close/Reopen/Delete par le service existant. Aucun écran Support spécifique. Les suites D1 lifecycle et actions vérifient le comportement conservé après extraction partagée. Aucun développement D3–D6, Manager Questionnaires, CRM, ancienne passerelle Manager, ancien SupportTicket, SMTP ou nouvelles règles Stripe.

## 19–21. Validation

Validation finale PHP : **184 tests, 2 388 assertions**, zéro échec/erreur/test ignoré (89,788 s). PostgreSQL dédié avec schémas isolés et mails simulés ; concurrence par processus PHP distincts.

| Suite | Tests | Assertions |
| --- | ---: | ---: |
| SurveyD2Test | 28 | 501 |
| SurveyD1LifecycleTest | 33 | 502 |
| SurveyD1ActionsTest | 20 | 215 |
| SurveyFoundationTest | 16 | 515 |
| ContactFoundationTest | 19 | 101 |
| PublicContactSubmissionTest | 17 | 133 |
| ManagerContactSupportTest | 20 | 142 |
| ManagerContactActionsTest | 17 | 147 |
| ManagerContactCardsTest | 5 | 52 |
| ContactPriorityTest | 9 | 80 |

Commande PHPUnit :

```powershell
$env:CONTACT_TEST_POSTGRES='1'
& C:\xampp\php\php.exe vendor/phpunit/phpunit/phpunit --filter 'SurveyD2Test|ContactPriorityTest|SurveyD1ActionsTest|SurveyD1LifecycleTest|SurveyFoundationTest|ContactFoundationTest|PublicContactSubmissionTest|ManagerContactSupportTest|ManagerContactActionsTest|ManagerContactCardsTest' --log-junit storage/d2-validation.xml tests/Feature
```

JavaScript : 10 tests réussis (6 D1, 4 D2), zéro échec, via `node --test tests/JavaScript/SurveyD1.cjs tests/JavaScript/SurveyD2.cjs` avec PHP_BINARY=C:\xampp\php\php.exe.

Pint appliqué puis `--test` sur les 30 fichiers PHP touchés (hors vues Blade) : PASS. `git diff --check` : code 0 ; contrôle supplémentaire des fichiers créés via `git diff --no-index --check` : aucune erreur d'espacement. Vérification structurée des quatre dictionnaires : toutes les sections préexistantes hors D2 sont identiques à l'état initial.

Les tests D2 couvrent notamment le GET sans mutation, la Q1 immuable, CSRF, signature/token/type, conditions serveur/JS/sans JS, limites Unicode, finalisation, tous les mappings d'actions, consolidation/priorités, reprise après échec d'action et rollback du numéro, création/soumissions/actions concurrentes, suppression puis replay, 72 h, suppression D1 et respect de son historique, relance/expiration et envoi non confirmé.

## 22. Migrations

Aucune migration exécutée sur la base applicative ou de production. La nouvelle migration 2026_10_29_100003_add_d2_signal_action_types.php étend uniquement le CHECK de survey_actions avec PROJECT_FOLLOW_UP et VALUE_CONFIRMED. Elle est testée dans des schémas PostgreSQL aléatoires de la base dédiée de tests, détruits après chaque test. Elle devra être appliquée lors du déploiement autorisé avant utilisation de ces actions D2. Les migrations précédentes en attente restent également à appliquer selon le processus de déploiement.
