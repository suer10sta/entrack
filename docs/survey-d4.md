# D4 — Fin d’essai sans achat

## 1. Fichiers créés/modifiés

- Créé : `app/Console/Commands/ProcessD4Surveys.php`
- Créé : `app/Http/Controllers/SurveyD4Controller.php`
- Créé : `app/Mail/D4SurveyMail.php`
- Créé : `app/Services/Surveys/D4ActionService.php`
- Créé : `app/Services/Surveys/D4AnswerService.php`
- Créé : `app/Services/Surveys/D4Eligibility.php`
- Créé : `app/Services/Surveys/D4Lifecycle.php`
- Créé : `app/Services/Surveys/D4StageGuard.php`
- Créé : `app/Services/Surveys/SurveySubscriptionState.php`
- Créé : `database/migrations/2026_10_30_100005_add_d4_signal_action_types.php`
- Créé : `tests/Feature/SurveyD4Test.php`
- Créé : `tests/JavaScript/SurveyD4.cjs`
- Créé : `tests/Support/survey-d4-worker.php`
- Modifié : `.gitignore`
- Modifié : `app/Enums/SurveyActionType.php`
- Modifié : `app/Http/Controllers/SurveyPublicController.php`
- Modifié : `app/Services/Surveys/D2Eligibility.php`
- Modifié : `app/Services/Surveys/SurveyActionService.php`
- Modifié : `app/Services/Surveys/SurveyLifecycle.php`
- Modifié : `app/Services/Surveys/SurveyLink.php`
- Modifié : `resources/lang/en/surveys.php`
- Modifié : `resources/lang/es/surveys.php`
- Modifié : `resources/lang/fr/surveys.php`
- Modifié : `resources/lang/nl/surveys.php`
- Modifié : `routes/console.php`
- Modifié : `routes/surveys.php`
- Modifié : `tests/Feature/SurveyFoundationTest.php`
- Créé : `docs/survey-d4.md`.

Aucun fichier supprimé. L'état antérieur D3, déjà présent dans le répertoire de travail, a été conservé. Catalogue config/surveys.php, vues/CSS/JS de production Survey, modèles, TicketService, routes/web.php et code Stripe/Cashier inchangés.

## 2. Diagnostic trial et abonnement

RegisteredUserController initialise entreprises.trial_ends_at à inscription + 15 jours. La fin réelle du trial vient de cette date, sans calcul sur entreprise.created_at. SubscriptionController::paymentSuccess et le webhook invoice.payment_succeeded convergent le plan payé vers type_compt et peuvent effacer trial_ends_at. Les statuts pertinents déjà retenus par D2/D3 sont active, trialing, past_due, unpaid, avec ends_at NULL ou futur. Un type_compt non NULL suffit également à écarter ces questionnaires d'essai.

La requête de diagnostic SELECT stripe_status, count(*) sur subscriptions, dans une transaction explicitement READ ONLY, a retourné zéro ligne sur la base applicative locale. Aucun statut empirique supplémentaire n'est donc inventé. Le flux Cashier et les services existants suffisent à implémenter D4 ; aucun appel réseau Stripe n'a été ajouté.

La définition locale existante a été extraite à l'identique dans SurveySubscriptionState et D2Eligibility l'utilise maintenant. D3 hérite toujours de D2Eligibility. D4 réutilise ce même service : pas de deuxième définition de l'abonnement pertinent, pas de changement de comportement D2/D3.

## 3. Éligibilité D4 exacte

trial_ends_at présent ET trial_ends_at + 24 heures <= maintenant UTC. Cela implique un trial terminé et le délai complet de 24 h. Aucun type_compt choisi et aucune ligne d'abonnement pertinente. Premier utilisateur de l'entreprise par id, email valide et vérifié, aucun arrêt global Survey. Aucun D4 du cycle déjà traité ; contrainte unique et verrou de livraison existants. La barrière de 72 h est appliquée avant création/envoi et lors des relances. Un report ne crée pas de doublon et le Scheduler peut reprendre plus tard.

## 4. Cycle

cycle_key = once, cycle unique du trial standard, cohérent avec D1–D3. Unicité DB (entreprise_id, survey_type, cycle_key). Une prolongation, annulation ou nouvelle exécution ne crée pas un nouveau cycle D4. Un D4 annulé reste fermé même si le plan local est retiré ensuite.

## 5. Transition D3 → D4

D4 est traité avant D3. Dès que l'étape D4 est réellement pertinente, avant même le contrôle de cadence d'envoi, les D3 non finalisés sont annulés avec le mécanisme commun cancel, motif trial_ended. Leur relance ne peut plus partir ; réponses partielles, actions et historique restent présents. Un D3 finalisé n'est pas modifié. Cette annulation correspond au changement d'étape déjà reconnu par le moteur D3 à la fin du trial.

## 6. Achat avant ou pendant D4

- Avant création : D4 inéligible.
- Créé mais non envoyé : traitement suivant annule D4 sans email.
- Déjà envoyé : ouverture de l'ancien lien renvoie 410 immédiatement dès que les données locales signalent la conversion, sans enregistrer de réponse ni d'action métier.
- GET reste strictement en lecture seule : il considère le lien comme obsolète ; l'annulation persistée et son reçu sont écrits par le Scheduler. Un POST public obsolète est également bloqué avant toute réponse.
- D4AnswerService protège aussi les appels directs : verrou Survey puis entreprise, contrôle du stage, commit de l'annulation avant rejet 410, aucune réponse sauvée si conversion.
- D4ActionService revérifie le stage dans la transaction métier, sous les mêmes verrous. Une finalisation déjà enregistrée mais dont les actions étaient en attente ne peut créer de Contact/signaux après conversion. Les réponses restent conservées.
- Aucun retry ne réactive un D4 annulé. Une extension manuelle du trial rend aussi D4 obsolète ; D4 ne touche pas lui-même à cette date.

Le test concurrent acquiert le verrou entreprise dans une transaction de conversion puis lance des finalisations concurrentes : aucune réponse ni action métier n'est produite après le commit de conversion.

## 7. Email et formulaire public

D4SurveyMail et SurveyD4Controller réutilisent le moteur Survey, les templates et l'interface existants. Email sobre de retour sur l'essai ; il rappelle la conservation des données et la possibilité de retrouver l'accès via un forfait, conformément au PDF. Pas de campagne commerciale additionnelle. Neuf choix Q1 cliquables, token cryptographique, URL signée, date limite exacte sent_at + 15 jours. Route GET/POST /{locale}/questionnaires/D4/{survey}/{token}, sans login, CSRF et throttle conservés. GET sans mutation et confirmation volontaire par POST avant enregistrement.

## 8. Questions et conditions

Catalogue D4 version 1 existant et inchangé :

- Q1 obligatoire : raison principale, neuf valeurs canoniques du PDF.
- q1_autre facultatif uniquement pour Autre (texte libre), 2 000 caractères Unicode maximum.
- Q2 : niveau d'utilisation pendant l'essai.
- Q3 : ce qui aurait permis de continuer, dont Davantage de temps pour tester.
- Q4 : échange de 20 minutes, Oui/Peut-être/Non. Seul Oui demande une intervention humaine.

Q1 confirmée immuable, validation serveur stricte, champs masqués ignorés même si invalides, prévisualisation utilisable sans JS, erreurs/confirmation/états terminés/expirés/annulés réutilisés. La version persistée est toujours utilisée.

## 9. Mapping complet réponses → actions

| Valeur canonique | Résultat |
| --- | --- |
| Q1 Je n’ai pas eu le temps de tester suffisamment | PROJECT_FOLLOW_UP, relance_projet, contexte fin_essai et raison |
| Q1 Ce n’était pas le bon moment pour mon entreprise | Même signal de relance ultérieure |
| Q1 La prise en main était trop difficile | Contact normal, difficulté d'onboarding |
| Q1 Il manque une fonctionnalité essentielle | Contact normal, collecte du besoin sans promesse |
| Q1 Je n’ai pas perçu assez de gain de temps | INSUFFICIENT_VALUE, valeur_insuffisante |
| Q1 Le prix des forfaits ne me convient pas | PRICE_BARRIER, frein_prix, sans Contact pour ce seul motif |
| Q1 Je dois obtenir l’accord d’un décideur | Contact normal, décision interne avant abonnement |
| Q1 J’ai choisi une autre solution | COMPETITOR_CHOSEN, autre_solution |
| Q1 Autre (texte libre) | Réponse/précision conservées ; aucune action automatique pour ce seul motif |
| Q2 tous les choix | Réponse conservée, aucun déclencheur inventé |
| Q3 Davantage de temps pour tester | TRIAL_EXTENSION_REQUESTED, extension_trial_potentielle, décision manager_required |
| Q3 autres choix | Réponses conservées, aucun déclencheur supplémentaire inventé |
| Q4 Oui | Contact normal, échange de 20 minutes |
| Q4 Peut-être/Non ou absence | Aucun Contact pour ce seul motif |

Toutes les actions commencent uniquement après une finalisation valide.

## 10–11. Signaux nouveaux et réutilisés

Nouveaux types : TRIAL_EXTENSION_REQUESTED, INSUFFICIENT_VALUE, COMPETITOR_CHOSEN. PRICE_BARRIER réutilise exactement le concept introduit par D3. PROJECT_FOLLOW_UP réutilise la relance projet de D2, avec contexte fin_essai et raison ; aucune campagne ni email immédiat. VALUE_CONFIRMED de D2 est un signal positif et ne représente pas une valeur insuffisante : il n'a pas été détourné.

Clés uniques D4:PRICE_BARRIER, D4:PROJECT_FOLLOW_UP, D4:TRIAL_EXTENSION_REQUESTED, D4:INSUFFICIENT_VALUE, D4:COMPETITOR_CHOSEN. Données JSON exploitables dans survey_actions ; aucun nouveau stockage parallèle.

## 12–13. Contacts et consolidation

Via TicketService existant, source d4-survey, priorité normal, identité par relations User/Entreprise, numéro DT et métadonnées de Survey. Une seule Survey crée au maximum un Contact : le message regroupe raison, demande d'appel et réponses utiles. Sujet selon le premier motif humain. Aucun engagement de fonctionnalité, coupon, prix, Checkout ou réservation d'appel.

contact_id, allocation du numéro, reçu TICKET D4:CREATE_CONTACT, signaux et D4:ACTIONS_EVALUATED sont transactionnels. Le reçu durable empêche la recréation après suppression du Contact. Les signaux peuvent se cumuler avec une demande d'appel sans multiplier les Contacts.

## 14–15. Extension du trial

Davantage de temps pour tester produit uniquement un signal, sans Contact si aucun autre besoin humain. **Aucune écriture de trial_ends_at par D4** : ni extension, ni réactivation, ni changement de plan/Stripe. Le test vérifie l'égalité de la date avant/après et l'absence d'abonnement créé. Décision future réservée à l'Admin/Manager.

## 16. Idempotence et reprise

Verrou de session PostgreSQL par entreprise pour les emails ; unicité entreprise/type/cycle, réponse/question et action_key ; verrous de ligne Survey/entreprise pour D4 réponses/actions. Q1_CLICKED et ANSWERED uniques. Actions après commit ; une panne après allocation du numéro annule Contact/numéro/signaux/reçu mais conserve les réponses, puis la commande reprend sans doublon. Concurrence de création, finalisation, actions et conversion testée avec processus PHP distincts.

Une tentative email initiale non confirmée conserve EMAIL_ATTEMPTED/EMAIL_UNCONFIRMED et n'est pas renvoyée aveuglément, comme D1–D3. Aucun email réel pendant les tests.

## 17. Relance et expiration

Moteur commun : une seule relance entre J+3 et J+5 après envoi initial, avec cadence 72 h. Aucune après réponse finale ou achat. Expiration au premier terme de reminder_sent_at + 10 jours ou sent_at + 15 jours. Le lien signé ne gagne pas de validité à la relance. Aucune relance après J+5 ; report possible jusqu'à J+5 inclus. Historique conservé après annulation/expiration.

## 18. Arrêt Survey

Mécanisme D1Eligibility::optedOut réutilisé : abandon global, y compris Q1 abandon déjà confirmée sans finalisation. Empêche tout envoi D4. Aucun lien ajouté avec statut CRM Perdu ou désabonnement des campagnes ; ces systèmes restent séparés.

## 19. Scheduler

Commande php artisan surveys:process-d4 ; hourly, withoutOverlapping(55), journal scheduler existant. Ordre final **D4 → D3 → D2 → D1** : étapes avancées d'abord sous la barrière commune. D4 annule les D3 devenus obsolètes ; D2/D3 continuent leurs contrôles de fin de trial. Les autres tâches ne sont pas modifiées. Traitement par lots, report de cadence et retry des actions finales sans réactivation d'une Survey annulée.

## 20. Manager Support

Source D4Survey/d4-survey déjà disponible. Test HTTP liste/cards filtrée source D4 et priorité normal, détail, identité/email/sujet/message, puis Reply/Close/Reopen/Delete via le service existant. Replay après suppression vérifié. Aucun écran ni route Support supplémentaire.

## 21. Traductions

D4 complet et d4_ui complet en FR/EN/NL/ES : neuf raisons, toutes les autres questions/choix, email/rappel, boutons, validation, erreurs et états. Pas de fallback français nécessaire. Comparaison structurée des dictionnaires : toutes les sections préexistantes hors D4 sont identiques à l'état initial, y compris D1–D3 et D5/D6.

## 22–23. Périmètre préservé

Comportement D1–D3 conservé, hors transition D3 → D4 explicitement demandée. Seule extraction de la vérification locale d'abonnement et ajout de hooks communs sans effet par défaut ; le contrôle de lien/action spécifique s'applique exclusivement à D4. Catalogue versionné et règles/actions D1–D3 inchangés. Aucun développement D5/D6 ni Manager Questionnaires, aucune extension automatique/campagne. Aucun changement /contact, ancien SupportTicket, CRM, désabonnement campagnes, Stripe/Cashier, plans, SMTP, /manager/direct-dashboard.

## 24–27. Résultats de validation

PHPUnit : **241 tests, 3 586 assertions**, zéro échec/erreur/test ignoré, durée 1 min 46,119 s. PostgreSQL dédié avec schémas isolés, MailFake/NotificationFake et processus concurrents réels.

| Suite | Tests | Assertions |
| --- | ---: | ---: |
| ContactFoundationTest | 19 | 101 |
| ContactPriorityTest | 9 | 80 |
| ManagerContactActionsTest | 17 | 147 |
| ManagerContactCardsTest | 5 | 52 |
| ManagerContactSupportTest | 20 | 142 |
| PublicContactSubmissionTest | 17 | 133 |
| SurveyD1ActionsTest | 20 | 215 |
| SurveyD1LifecycleTest | 33 | 502 |
| SurveyD2Test | 28 | 501 |
| SurveyD3Test | 25 | 598 |
| SurveyD4Test | 32 | 576 |
| SurveyFoundationTest | 16 | 539 |

Commande :

```powershell
$env:CONTACT_TEST_POSTGRES='1'
& C:\xampp\php\php.exe vendor/phpunit/phpunit/phpunit --filter 'SurveyD4Test|SurveyD3Test|SurveyD2Test|ContactPriorityTest|SurveyD1ActionsTest|SurveyD1LifecycleTest|SurveyFoundationTest|ContactFoundationTest|PublicContactSubmissionTest|ManagerContactSupportTest|ManagerContactActionsTest|ManagerContactCardsTest' tests/Feature
```

D4 : 32 tests / 576 assertions. Frontières exactes de 24 h, absence de plan/abonnement, arrêt Survey, 72 h, unicité/cycle, annulation D3 avec historique préservé, conversion avant envoi/après envoi/avant actions/concurrente, lien fermé et absence de réponse/action métier, absence de réactivation, toutes les règles Contact/signaux et combinaison, date de trial/plan inchangés, signatures/token/type/paires invalides, GET lecture seule, CSRF, Q1 immuable, double submit, champ Autre masqué/sans JS/Unicode, retry après erreur de TicketService avec rollback du numéro, concurrence création/réponses/actions et suppression puis replay, relance/expiration et envoi initial non confirmé, quatre langues et workflow Manager Support.

JavaScript : **17 tests réussis**, zéro échec (6 D1, 4 D2, 4 D3, 3 D4). Commande : `node --test tests/JavaScript/SurveyD1.cjs tests/JavaScript/SurveyD2.cjs tests/JavaScript/SurveyD3.cjs tests/JavaScript/SurveyD4.cjs` avec PHP_BINARY=C:\xampp\php\php.exe.

Pint appliqué puis `--test` sur les **25 fichiers PHP touchés** hors vues Blade : PASS. `git diff --check` : code 0, aucune erreur d'espacement ; nouveaux fichiers contrôlés en plus via `git diff --no-index --check`. Les dictionnaires préexistants hors D4 ont été comparés dans les quatre langues. Les fichiers protégés de facturation, Contact et règles d'action D1/D2/D3 sont identiques à l'état initial de cette intervention.

## 28–29. Migration et bases

Créée : database/migrations/2026_10_30_100005_add_d4_signal_action_types.php, extension additive du CHECK survey_actions.action_type avec trois types D4. Tous les types précédents sont conservés. Down rétablit les types antérieurs et refuse normalement si des lignes incompatibles sont encore présentes.

**Aucune migration exécutée sur la base applicative ou de production.** Seul diagnostic applicatif : SELECT de statuts dans une transaction READ ONLY, annulée ensuite. Les migrations ont été exécutées uniquement dans les schémas PostgreSQL aléatoires de la base dédiée de tests, protégés par garde de nom/environnement et supprimés après chaque test. Toutes les migrations encore en attente restent à appliquer via le processus de déploiement autorisé.
