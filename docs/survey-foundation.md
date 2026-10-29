# Questionnaire D1-D6 - Phase 5, étape 2

## Périmètre et référence

Fondation seulement : schéma, modèles, enums, catalogue versionné et tests PostgreSQL. Référence lue : `C:/Users/Station11/Desktop/SEO/Questionnaire Dalytrac.pdf`, 11 pages. Aucun déclencheur métier, email, relance, route publique, Controller, Scheduler, création automatique de Contact ou interface Manager. Aucun fichier Contact/Support/CRM/Stripe/authentification existant n'est modifié. Aucune migration exécutée sur la base applicative ou de production.

Les migrations et la base configurée ont été inspectées en lecture seule : `entreprises.id`, `users.id`, `subscriptions.id`, `contacts.id` sont des bigint. Cashier utilise actuellement `subscriptions.entreprise_id` et `Laravel\Cashier\Subscription`. Les trois nouvelles PK sont UUID ; leurs relations vers les tables existantes sont bigint.

## Fichiers

- `database/migrations/2026_10_29_100000_create_survey_foundation_tables.php`
- `app/Models/SurveyResponse.php`, `SurveyAnswer.php`, `SurveyAction.php`
- `app/Enums/SurveyType.php`, `SurveyStatus.php`, `SurveyActionType.php`
- `app/Services/Surveys/SurveyCatalog.php`
- `config/surveys.php`
- `resources/lang/{fr,en,nl,es}/surveys.php`
- `tests/Feature/SurveyFoundationTest.php`
- `tests/Support/survey-foundation-worker.php`
- ce document

Seul fichier préexistant modifié : `.gitignore`, avec des exceptions ciblées pour les deux nouveaux fichiers de test et ce document, auparavant ignorés. Aucune relation inverse ajoutée à Contact : les relations unidirectionnelles suffisent pour cette fondation.

## Schéma exact

Tous les champs sont NOT NULL sauf mention contraire. Les timestamps de ces tables sont avec fuseau horaire sur PostgreSQL.

### survey_responses

| Colonne | Type / défaut |
| --- | --- |
| id | UUID, PK |
| survey_type | varchar(255), CHECK D1 à D6 |
| survey_version | integer, CHECK > 0, obligatoire explicitement |
| cycle_key | varchar(255), défaut `once`, CHECK non vide |
| entreprise_id | bigint, FK entreprises.id, DELETE RESTRICT |
| user_id | bigint nullable, FK users.id, DELETE SET NULL |
| subscription_id | bigint nullable, FK subscriptions.id, DELETE SET NULL |
| email | varchar(255), adresse de destination historique |
| token | varchar(64), UNIQUE, CHECK 64 caractères hexadécimaux minuscules |
| status | varchar(255), CHECK SurveyStatus, défaut `sent` |
| sent_at | timestamptz nullable |
| reminder_sent_at | timestamptz nullable |
| clicked_at | timestamptz nullable |
| completed_at | timestamptz nullable |
| recall_due_at | timestamptz nullable |
| converted_after_answer | boolean, défaut false |
| converted_at | timestamptz nullable |
| contact_id | bigint nullable, FK contacts.id, DELETE SET NULL |
| created_at / updated_at | timestamptz nullable, conventions Eloquent |

Contrainte UNIQUE `(entreprise_id, survey_type, cycle_key)` : indépendante d'une vérification applicative et efficace en concurrence. D1-D5 utilisent `once`. D6 utilisera une clé canonique de période terminée, stable entre tentatives ; aucune date de cycle n'est calculée maintenant. Le futur orchestrateur ne devra pas attribuer une nouvelle clé arbitraire à D1-D5.

Index : `(survey_type, status, sent_at)`, `(status, recall_due_at)`, `(entreprise_id, sent_at)`, `(entreprise_id, reminder_sent_at)`, `(entreprise_id, status)`, plus ceux créés par les clés et contraintes uniques.

`sent_at` reste nullable afin que la seule création d'un modèle ne prétende pas avoir envoyé un email. Le statut par défaut est celui de la spécification ; aucun envoi n'est déduit de ce statut. Le futur parcours devra établir explicitement la cohérence statut/date et la gestion des échecs d'envoi. Aucun état `draft` supplémentaire n'est introduit.

### survey_answers

| Colonne | Type / défaut |
| --- | --- |
| id | UUID, PK |
| survey_id | UUID, FK survey_responses.id, DELETE CASCADE |
| question_id | varchar(255) |
| value | text, CHECK longueur Unicode <= 2000 sur PostgreSQL |
| created_at / updated_at | timestamptz nullable |

UNIQUE `(survey_id, question_id)`. Les codes et textes libres sont des chaînes, pas des tableaux JSON. Le modèle valide aussi la limite de 2000 caractères. Les codes autorisés, questions visibles, Q1 obligatoire et règles de remplacement seront validés ultérieurement à partir du catalogue ; aucun moteur de réponse n'est ajouté ici.

### survey_actions

| Colonne | Type / défaut |
| --- | --- |
| id | UUID, PK |
| survey_id | UUID, FK survey_responses.id, DELETE RESTRICT |
| action_type | varchar(255), CHECK SurveyActionType |
| action_key | varchar(255), obligatoire, CHECK non vide |
| detail | text nullable, précision libre comme dans le PDF |
| triggered_at | timestamptz, défaut CURRENT_TIMESTAMP |

UNIQUE `(survey_id, action_key)` ; index `(survey_id, triggered_at)`. Pas de created_at/updated_at : triggered_at est la date du journal. UPDATE, DELETE et TRUNCATE sont refusés par triggers PostgreSQL ; les mises à jour/suppressions Eloquent sont également refusées. L'append-only est une garantie DB sur PostgreSQL, pas seulement une convention applicative. La migration descendante peut explicitement supprimer les tables et la fonction de trigger.

## Modèles et enums

SurveyResponse : HasUuids ; casts SurveyType, SurveyStatus, dates immuables, version entière et booléen de conversion. Relations entreprise, user, subscription, contact, answers et actions.

SurveyAnswer : HasUuids ; relation survey ; constante MAX_VALUE_LENGTH=2000 et validation à la sauvegarde.

SurveyAction : HasUuids ; relation survey ; cast SurveyActionType et date immuable ; journal append-only.

SurveyType : D1, D2, D3, D4, D5, D6.

SurveyStatus : sent, q1_clicked, answered, relaunched, expired, cancelled, abandon.

SurveyActionType : SENT, REMINDER_SENT, Q1_CLICKED, ANSWERED, TICKET, AUTO_EMAIL, UPSELL, STOP_COMMERCIAL, RECALL_DONE, CANCELLED. Ces valeurs sont centralisées ; aucun comportement ni transition automatique n'est attaché aux enums.

## Catalogue et langues

`SurveyCatalog::definition(SurveyType, version)` charge `surveys.types.{D1-D6}.versions.{version}`. Sans version explicite, il utilise current_version ; une version inconnue est refusée. Une réponse persistée devra toujours être interprétée avec sa version enregistrée, jamais avec la version courante par défaut.

Version 1 : toutes les questions et tous les choix du PDF sont représentés, avec Q1 obligatoire et les autres facultatives. Les champs contiennent id, label_key, type, choices (value/label_key), required, condition, email_clickable, max_length et free_text_question_id. `condition` est un descripteur question_id/operator/values (`in` ou `not_in`) ; il n'est pas exécuté à cette étape. Le futur moteur devra vérifier que la réponse parente existe et que ses propres conditions sont satisfaites, et ignorer les réponses masquées, y compris en cascade.

Les identifiants suivent q1, q2, etc., q2b pour D1 et les champs qN_autre des choix explicitement accompagnés de texte libre. Totaux incluant ces champs : D1=6, D2=5, D3=6, D4=5, D5=5, D6=5. D1 q4 « Autre » n'est pas transformé en texte libre : ce n'est pas spécifié ainsi dans le PDF.

Le PDF ne fournit des codes explicites que pour D1 Q1 et D2 Q1 ; ces codes sont repris exactement. D3 Q1 utilise les valeurs numériques textuelles 1 à 5. Pour les autres choix, aucun code métier n'est inventé : la valeur est le libellé français exact, y compris « Autre (texte libre) ». Les traductions futures n'altéreront jamais ces valeurs. Si des codes techniques distincts sont validés ultérieurement, il faudra une nouvelle version plutôt qu'une réécriture des valeurs historiques.

Les libellés français sont dans `resources/lang/fr/surveys.php`, jamais dans les tables. Les fichiers EN/NL/ES sont préparés mais restent à traduire intégralement. `SurveyCatalog::label` assure un repli explicite vers le français pour chaque clé manquante, indépendamment de la langue de repli globale de l'application. Une langue non prise en charge ou une clé française manquante est refusée.

## Tokens et futurs liens

À la création Eloquent, un token opaque de 32 octets aléatoires (`random_bytes(32)`, représentation hexadécimale de 64 caractères) est généré. UNIQUE en DB protège des collisions. Il n'est pas assignable en masse et il est masqué dans les tableaux/JSON du modèle. Pas de secret supplémentaire, de lien public, de génération d'email ou de validation de token maintenant.

À l'étape publique : construire une URL Laravel temporairement signée avec id, survey_type et token, en utilisant la clé applicative existante. Échéance immuable `sent_at + 15 jours` ; une nouvelle visite ou relance ne prolonge pas cette durée. Sans sent_at, le lien devra être refusé. Vérifier signature, type, token et statut côté serveur, sans journaliser le token ni l'URL complète. Le token est un secret porteur stocké en DB ; son masquage JSON ne remplace pas les contrôles d'accès. Comparaison en temps constant lors de la future validation.

Validité du lien et état de l'enquête restent deux contrôles distincts : l'expiration métier est prévue à reminder_sent_at + 10 jours, tandis que le lien dure 15 jours depuis l'envoi. Avec une relance à J+3, ces échéances diffèrent. Ne pas prolonger implicitement le lien pour les faire coïncider ; appliquer les deux restrictions.

## Idempotence et future intégration Contact

Chaque ligne du journal porte une action_key stable, locale à l'enquête. Exemples de conventions futures : `sent:initial`, `reminder:only`, `q1:first`, `answered:final`, `ticket:technical-block:v1`. Deux règles distinctes peuvent avoir le même action_type mais doivent avoir des clés distinctes. Les changements de dates, un refresh ou un retry ne doivent pas produire de nouvelle clé.

La fondation ne crée pas de Contact et ne traite aucune action. À l'étape métier, insérer le reçu et appeler TicketService dans LA MÊME transaction sur la même connexion, puis rattacher le Contact avant commit. Un conflit UNIQUE signifie que l'action a déjà été prise en compte ; le perdant ne doit pas appeler TicketService. Une erreur doit annuler à la fois le reçu et le Contact. Un reçu ne doit pas être validé avant l'effet DB correspondant : cela pourrait perdre définitivement l'action. Les effets externes (emails) demanderont une stratégie de livraison après commit distincte ; cette contrainte unique seule ne garantit pas leur livraison exactement une fois.

La relation directe survey_responses.contact_id désigne le Contact principal éventuel. Aucune FK ni dépendance vers SupportTicket. Si plusieurs règles d'une enquête créent plusieurs Contacts, leur cardinalité et leur rattachement détaillé devront être décidés à l'étape métier ; le schéma n'impose pas silencieusement un seul effet par enquête, car les reçus sont par règle. Ne pas utiliser contact_id NULL comme autorisation de recréer un Contact : seul le reçu décide.

Supprimer un Contact met contact_id à NULL mais ne touche pas survey_actions. Le rejeu de la même action_key reste donc refusé. Une enquête sans action peut être supprimée, ses réponses sont supprimées en cascade. Une enquête avec au moins une action ne peut pas être supprimée, afin de conserver les reçus. La suppression d'une entreprise référencée est refusée ; la suppression d'un utilisateur ou abonnement met seulement sa FK à NULL. Ce choix de rétention est explicite : il peut empêcher une suppression d'entreprise ; un futur processus de purge/anonymisation devra être décidé séparément, sans supprimer accidentellement l'historique d'abandon ou les reçus.

Les FK ne valident pas seules l'appartenance mutuelle des user/subscription/contact à l'entreprise. Le futur service de création devra recevoir des identités de confiance et contrôler leur cohérence avant toute enquête ; aucune association par email non vérifiée ne devra servir de preuve d'appartenance.

## Préparation des règles communes

| Règle future | Support dans la fondation / travail restant |
| --- | --- |
| Lien 15 jours | token unique + sent_at ; URL signée et vérification à développer |
| Q1 depuis email | catalogue email_clickable, clicked_at, statut et action Q1_CLICKED ; parcours à développer |
| Relance unique J+3 à J+5 | reminder_sent_at + reçu unique futur reminder:only ; aucun envoi |
| Expiration après relance | reminder_sent_at + statut expired ; aucun traitement périodique |
| Changement d'étape | liens entreprise/subscription, cancelled et action CANCELLED ; conditions non codées |
| Abandon définitif | statut abandon et historique conservé ; le futur orchestrateur devra consulter TOUT l'historique de l'entreprise, pas seulement son cycle courant |
| Un email tous les 3 jours par entreprise | index entreprise/sent_at et entreprise/reminder_sent_at ; le futur envoi devra prendre un verrou partagé par tous les types sur l'entreprise, consulter les deux dates et réévaluer avant envoi. Aucune garantie d'espacement n'est revendiquée à cette étape |
| D2 annule la relance D1 sans réponse | types/status/dates disponibles ; orchestration transactionnelle à développer |
| Rappel D6 et conversion | recall_due_at, converted_after_answer et converted_at ; aucun calcul ni suivi automatique |

Le PDF mentionne last_survey_email_at mais cette étape n'ajoute aucun champ à Entreprise. Les dates initiales et de relance permettent un contrôle ultérieur sous verrou entreprise. Il faudra traiter explicitement les échecs et la réservation d'envoi pour coordonner les effets SMTP, sans confondre tentative et envoi confirmé.

## Robots email, priorités, Manager

Le PDF demande qu'un clic Q1 enregistre immédiatement le choix et qu'un second clic ne remplace pas le premier. Un GET peut toutefois provenir d'un scanner. Stratégie prévue : GET de consultation sans mutation, choix prérempli, puis confirmation explicite par POST protégé ; aucune détection de robot fondée uniquement sur User-Agent. Cela ajoute une confirmation par rapport au clic littéral du PDF : cette adaptation devra être validée à l'étape publique. Aucun de ces parcours n'est développé maintenant.

Priorité demandée par le PDF : D2 Q1=blocage ; D5 Q2=« Non, à cause d'un problème technique ». D5 distingue les difficultés/autres aides, qui créent un ticket sans priorité explicite. D1/D3/D4/D6 demandent des tickets dans certains cas, sans qualifier leur priorité. Aucun champ de priorité n'est ajouté à Contact ; décision ultérieure obligatoire.

La future entrée Manager « Questionnaires » devra être immédiatement SOUS Support. Aucune route, page ni modification de sidebar maintenant.

## Vérification

Tests dans des schémas jetables `survey_test_*` de la base PostgreSQL dédiée de test, jamais la base applicative. Le worker de concurrence vérifie environnement testing, nom de base suffixé _test/_testing et schéma autorisé. Les tests Contact sont exécutés avec CONTACT_TEST_POSTGRES=1. Les résultats exacts sont indiqués dans le rapport de livraison.

Couverture : six types, UUID/tokens, FK et relations numériques dont Cashier réel, contraintes concurrentes enquête/action, cycles D6, réponses uniques et Unicode <=2000, cascade des réponses, append-only SQL/Eloquent, persistance des reçus après suppression Contact, restrictions de suppression, enums, catalogue/conditions/repli linguistique, absence de dépendance vers SupportTicket et rollback/réapplication de migration.

Résultats de livraison :

- Fondation seule : 16 tests, 495 assertions, succès.
- Fondation + ContactFoundationTest + PublicContactSubmissionTest + ManagerContactSupportTest + ManagerContactActionsTest + ManagerContactCardsTest, avec CONTACT_TEST_POSTGRES=1 : 94 tests, 1069 assertions, succès, aucun test ignoré.
- Pint : 15 fichiers PHP, succès ; vérification finale `--test` réussie.
- `git diff --check` : code 0 ; contrôle additionnel des nouveaux fichiers sans erreur d'espacement.
- Empreintes des fichiers préexistants : seule .gitignore a changé.
- Vérification SQL finale en lecture seule : aucune des trois tables Questionnaire dans le schéma applicatif.
