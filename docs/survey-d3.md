# D3 — Valeur et intention d’achat

## 1. Fichiers créés/modifiés

- Créé : `app/Console/Commands/ProcessD3Surveys.php`
- Créé : `app/Http/Controllers/SurveyD3Controller.php`
- Créé : `app/Mail/D3BusinessMail.php`
- Créé : `app/Mail/D3SurveyMail.php`
- Créé : `app/Services/Surveys/D3ActionService.php`
- Créé : `app/Services/Surveys/D3AnswerService.php`
- Créé : `app/Services/Surveys/D3BusinessMailer.php`
- Créé : `app/Services/Surveys/D3Eligibility.php`
- Créé : `app/Services/Surveys/D3Lifecycle.php`
- Créé : `database/migrations/2026_10_30_100004_add_d3_signal_action_types.php`
- Créé : `resources/lang/en/d3-actions.php`
- Créé : `resources/lang/es/d3-actions.php`
- Créé : `resources/lang/fr/d3-actions.php`
- Créé : `resources/lang/nl/d3-actions.php`
- Créé : `resources/views/emails/d3-business.blade.php`
- Créé : `tests/Feature/SurveyD3Test.php`
- Créé : `tests/JavaScript/SurveyD3.cjs`
- Créé : `tests/Support/survey-d3-worker.php`
- Modifié : `.gitignore`
- Modifié : `app/Enums/SurveyActionType.php`
- Modifié : `app/Http/Controllers/SurveyPublicController.php`
- Modifié : `resources/lang/en/surveys.php`
- Modifié : `resources/lang/es/surveys.php`
- Modifié : `resources/lang/fr/surveys.php`
- Modifié : `resources/lang/nl/surveys.php`
- Modifié : `routes/console.php`
- Modifié : `routes/surveys.php`
- Modifié : `tests/Feature/SurveyFoundationTest.php`
- Créé : `docs/survey-d3.md`.

Aucun fichier supprimé. Le catalogue config/surveys.php, les modèles, TicketService et les services D1/D2 n'ont pas été modifiés.

## 2. Diagnostic initial

Le PDF Questionnaire Dalytrac.pdf confirme J+10 avec usage constaté, forte intention = Très probable ou Plutôt probable, Q5 sous cette même condition, email des forfaits, résumé pour le décideur, Contact pour fonctionnalité manquante/gain faible et questions restantes techniques/prix.

D2 conserve valeur_constatee dans une action VALUE_CONFIRMED (D2:VALUE_CONFIRMED) pour usage_reel + résultat Oui, clairement/En partie. Ce signal prépare D3 mais n'est pas un prérequis supplémentaire : le prompt autorise usage_reel confirmé même sans résultat. D1Eligibility::hasActivity fournit déjà la définition unique d'activité : projet, employé non automatique, client ou pointage. Seule la fiche automatiquement créée pour le propriétaire est exclue. D2Eligibility::trialRelevant vérifie la date de fin future, l'absence de plan choisi et les abonnements locaux pertinents ; aucun appel à Stripe.

SurveyEmailGate fournit le verrou PostgreSQL par entreprise et la limite de 72 h. SurveyLifecycle fournit la création, les réservations durables d'emails, relance J+3 à J+5, expiration et annulation après changement d'étape. Aucune raison technique n'imposait d'arrêter après le diagnostic.

## 3–4. Éligibilité et calcul J+10

Trial standard de 15 jours confirmé : trial_start = trial_ends_at - 15 jours ; J+10 = trial_ends_at - 5 jours. D3Eligibility exige :

- entreprise présente, trial_ends_at présent et strictement futur ;
- trial_ends_at - 5 jours atteint (UTC), sans utiliser entreprise.created_at ;
- type_compt NULL ;
- aucun abonnement local active/trialing/past_due/unpaid avec ends_at NULL ou futur ;
- premier utilisateur de l'entreprise par id, email valide et vérifié ;
- aucun arrêt global Survey, y compris Q1 abandon confirmée non finalisée ;
- usage réel établi selon le point 6.

SurveyLifecycle applique ensuite unicité et barrière d'envoi. Trial/abonnement sont revérifiés au traitement et à la réservation d'envoi. Une Survey ouverte est annulée au traitement suivant si le trial n'est plus pertinent. Aucun trial_started_at ajouté, aucune logique de paiement modifiée.

## 5. Cycle

cycle_key = once : cycle unique du premier essai de l'entreprise, cohérent avec D1/D2. Contrainte DB existante (entreprise_id, survey_type, cycle_key). Reporter ou prolonger un essai ne crée pas un deuxième D3. Un D3 terminé/annulé/expiré n'est pas recréé.

## 6. D2 et activité réelle

Au moins l'une des conditions : réponse D2 Q1 usage_reel déjà confirmée/persistée, ou D1Eligibility::hasActivity. L'absence de D2 n'empêche pas D3 si une activité réelle existe. decouverte seule n'est pas suffisante. Le signal valeur_constatee est conservé mais non exigé en plus du critère usage_reel. Aucun enregistrement D1/D2 n'est supprimé, modifié ou créé par D3 ; aucune suppression supplémentaire de leurs relances n'a été ajoutée.

## 7. Email Survey

D3SurveyMail réutilise QuestionnaireMail et la vue existante. Cinq liens Q1 (chaînes canoniques 1 à 5), token aléatoire, signature et expiration exacte à sent_at + 15 jours. GET sans mutation ; POST explicite avec CSRF pour confirmer/finaliser. Route GET/POST /{locale}/questionnaires/D3/{survey}/{token}, sans login, avec throttle et contrôle du type attendu. Aucun SMTP réel pendant les tests.

## 8. Formulaire et réponses

Catalogue D3 versionné existant, version persistée, vue/CSS/JS Survey existants. Q1 obligatoire 1–5 ; Q2 fonctionnalité utile ; q2_autre facultatif si Autre (texte libre), maximum 2 000 caractères ; Q3 frein ; Q4 intention ; Q5 uniquement Très probable/Plutôt probable. Validation serveur stricte et champs masqués ignorés, prévisualisation sans JS, Q1 confirmée immuable, verrous et reçus Q1_CLICKED/ANSWERED/completed_at conservés. Confirmations et états de lien invalide/expiré/annulé/déjà répondu réutilisés.

## 9–10. Mapping complet et signaux

Les valeurs ci-dessous sont les valeurs canoniques exactes du catalogue existant, pas des libellés dépendant de la langue du prospect.

| Déclencheur | Actions |
| --- | --- |
| Q1 = 1 ou 2 | Contact normal : entretien de prise en main / valeur perçue faible |
| Q3 = Un problème technique | Contact normal : question technique avant achat |
| Q3 = Une fonctionnalité manque | Contact normal : collecte du besoin, sans promesse |
| Q3 = Le prix des forfaits | PRICE_BARRIER, clé D3:PRICE_BARRIER, détail signal=frein_prix |
| Prix + Q4 forte | Même signal + Contact normal questions restantes avant achat |
| Q4 = Très probable / Plutôt probable | PURCHASE_INTENT, clé D3:PURCHASE_INTENT, signal=forte_intention_achat, likelihood et plan envisagé (ou null) ; email forfaits |
| Q3 = Je dois obtenir l’accord d’un responsable | DECISION_APPROVAL, clé D3:DECISION_APPROVAL, signal=validation_decideur ; email bénéfices |
| Q5 applicable | Réponse conservée et reprise dans PURCHASE_INTENT ; aucun paiement/plan modifié |
| Autres valeurs valides | Réponses conservées, aucune action humaine inutile |

Onboarding/équipe peu active seuls restent des réponses exploitables ; aucun déclencheur inventé en dehors des règles demandées. Q1 élevé ne bloque pas les autres déclencheurs. Q2 ne déclenche pas d'action.

## 11–12. Contacts et consolidation

D3ActionService hérite du traitement transactionnel SurveyActionService et utilise TicketService. Source d3-survey, priorité normal pour toutes les règles D3, identité par relations User/Entreprise, métadonnées survey_type/id/version/règles. Un seul Contact par Survey ; sujet selon le premier besoin et message regroupant tous les motifs et réponses pertinentes. Le token personnel est masqué dans le message. Le lien contact_id et le reçu TICKET D3:CREATE_CONTACT sont atomiques ; suppression ultérieure du Contact ne permet pas sa recréation.

## 13. Emails automatiques

D3BusinessMail/D3BusinessMailer séparent deux emails de l'email Survey : plans et benefits. Plans renvoie vers la route publique tarifs avec locale explicite (compatible avec le Scheduler) ; benefits fournit une synthèse partageable, sans promesse de développement. Aucun abonnement, réduction, Checkout ou changement de plan automatique.

Comme l'accusé métier D1, ces emails de réponse sont envoyés après finalisation, indépendamment de la cadence des invitations Survey. Ils prennent le verrou commun entreprise ; leurs réservations EMAIL_ATTEMPTED repoussent les invitations/relances Survey futures via la barrière existante. Les deux emails métier peuvent être envoyés après une même réponse si les deux conditions sont réunies. Les invitations et relances Survey restent espacées de 72 h.

## 14. Idempotence et erreurs

Traitement après commit des réponses. Verrou Survey, transactions Contact+numéro+signaux+reçu D3:ACTIONS_EVALUATED, contraintes uniques existantes. Les signaux sont écrits une fois ; les emails ont chacun leurs clés D3:PLANS/ BENEFITS:ATTEMPT, :SENT et :UNCONFIRMED. Réservation durable avant SMTP ; pas de transaction DB ouverte pendant SMTP. Aucun replay ne renvoie un email déjà tenté. Si SMTP est non confirmé, les réponses/signaux/Contact restent conservés et un reçu EMAIL_UNCONFIRMED exige une vérification manuelle, comme D1. Une erreur de rendu avant réservation peut être reprise par la commande ; un verrou temporairement occupé peut également être repris. Aucune fausse garantie d'exactement une acceptation SMTP après crash : stratégie au plus une tentative, sans renvoi aveugle.

## 15. Relance et expiration

Moteur commun inchangé : une seule relance entre envoi+3 et envoi+5 jours, trial toujours pertinent, pas de finalisation et barrière 72 h respectée. Expiration au plus tôt de relance+10 jours ou envoi+15 jours ; une relance ne prolonge pas l'URL. Fin d'essai/abonnement pertinent annule l'enquête au traitement. Les états et l'historique sont préservés.

## 16. Scheduler et reprise

Commande : php artisan surveys:process-d3. Traitement hourly, withoutOverlapping(55), journal scheduler existant. D3 est planifié avant D2 puis D1 afin de donner priorité au jalon J+10 sous le verrou partagé. Les commandes D1/D2 et les autres tâches sont conservées. Entreprises par lots de 100 ; cycle existant repris sans doublon ; actions finales et emails non encore tentés repris par retryCompany. Erreurs/non-confirmation initiale donnent un code de sortie en échec ; reçus non confirmés des emails métier restent auditables sans renvoi automatique lors des appels suivants.

## 17. Traductions

FR/EN/NL/ES : D3 complet (questions/choix) et d3_ui complet (email, rappel, boutons, validation, états). d3-actions fournit sujet/corps/CTA des deux emails métier dans chaque langue. Aucun fallback FR nécessaire pour D3. Les sections D1/D2 et D4–D6 sont identiques à l'état initial dans les quatre langues (comparaison structurée).

## 18. Manager Support

Test HTTP de la liste avec source d3-survey et priorité normal, détail/identité, puis Reply/Close/Reopen/Delete via le service existant. Aucune nouvelle interface ni modification du Support nécessaire, la source D3 préexistait déjà. Replay après suppression vérifié.

## 19–20. Périmètre préservé

D1/D2 : services, mailables, catalogue, moteur de lifecycle, activité réelle et sécurité inchangés ; seul le dispatch du contrôleur commun reçoit le troisième type. Suites de non-régression exécutées. Aucun développement D4/D5/D6 ni Manager Questionnaires. Aucun changement /contact, ancien SupportTicket, CRM, désabonnement campagnes, Stripe, tarifs/plans, SMTP ou /manager/direct-dashboard.

## 21–24. Validation

PHPUnit : **209 tests, 2 998 assertions**, zéro échec/erreur/test ignoré, durée 1 min 46,797 s. Base PostgreSQL dédiée, schémas isolés, mails simulés et processus concurrents réels.

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
| SurveyFoundationTest | 16 | 527 |

Commande de validation :

```powershell
$env:CONTACT_TEST_POSTGRES='1'
& C:\xampp\php\php.exe vendor/phpunit/phpunit/phpunit --filter 'SurveyD3Test|SurveyD2Test|ContactPriorityTest|SurveyD1ActionsTest|SurveyD1LifecycleTest|SurveyFoundationTest|ContactFoundationTest|PublicContactSubmissionTest|ManagerContactSupportTest|ManagerContactActionsTest|ManagerContactCardsTest' tests/Feature
```

D3 : éligibilité/activité/opt-out, cinq notes canoniques, signature/type/token, GET sans mutation, CSRF, Q1 immuable, Q5/Autre/sans JS/champs masqués/Unicode, tous les déclencheurs métier, consolidation et priorité normale, suppression puis replay, échec de création Contact après allocation du numéro puis reprise, erreurs d'email avant et après tentative, concurrence initiale/réponses/actions/emails, relance/expiration/annulation, langues et Manager Support.

JavaScript : **14 tests réussis** (6 D1, 4 D2, 4 D3), zéro échec. Commande : `node --test tests/JavaScript/SurveyD1.cjs tests/JavaScript/SurveyD2.cjs tests/JavaScript/SurveyD3.cjs`, PHP_BINARY=C:\xampp\php\php.exe.

Pint appliqué puis `--test` : **PASS sur 25 fichiers PHP**, hors vues Blade. `git diff --check` : **code 0**, aucune erreur d'espacement ; nouveaux fichiers contrôlés aussi via `git diff --no-index --check`.

Contrôle du périmètre : code des services D1/D2, moteurs communs de cycle/réponses/actions, D1AbandonMailer, TicketService, config/surveys.php et routes/web.php inchangés ; traductions préexistantes hors D3 identiques dans les quatre langues.

## 25–26. Migration et bases

Créée : database/migrations/2026_10_30_100004_add_d3_signal_action_types.php. Étend le CHECK survey_actions.action_type avec PURCHASE_INTENT, PRICE_BARRIER et DECISION_APPROVAL ; conserve les types D1/D2. Down rétablit les types précédents et refuse normalement si des actions D3 incompatibles existent.

**Aucune migration exécutée sur la base applicative ou de production.** Les tests utilisent uniquement la base PostgreSQL dédiée de tests, avec un schéma aléatoire protégé et supprimé après chaque test. Les migrations antérieures et cette migration restent à appliquer par le processus de déploiement autorisé.
