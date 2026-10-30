# Contact Support - priorité normal/high

## Périmètre

Ajout d'une information de priorité au nouveau système Contact uniquement. Aucun changement de statut, source, numérotation, relations, événements, suppression ou logique Reply/Close/Reopen/Delete. Aucun tri automatique par priorité. Pas d'éditeur de priorité Manager dans cette étape, ni règle D2/D5.

## Schéma et modèle

Migration additive : `database/migrations/2026_10_29_000005_add_priority_to_contacts.php`. Elle ajoute contacts.priority, non nullable, défaut normal, indexé, avec CHECK PostgreSQL limitant les valeurs à normal/high via Schema::enum. Les enregistrements préexistants sont donc lus avec normal immédiatement après migration, sans modifier leurs autres valeurs. Le rollback retire seulement l'index et cette colonne. Aucune migration historique réécrite et aucune migration appliquée sur la base applicative/production.

Enum `ContactPriority` : Normal=normal et High=high. Contact ajoute uniquement priority à fillable et au cast Enum. Les autres propriétés et relations restent inchangées.

## TicketService et compatibilité

Un paramètre typé optionnel est ajouté APRÈS metadata : `ContactPriority $priority = ContactPriority::Normal`. Les appels positionnels historiques et les appels nommés existants restent compatibles. La priorité est persistée dans la même création transactionnelle, avec la même séquence DT-XXXXXX et le même événement created ; aucun email ou nouvel événement de priorité n'est ajouté.

Les appels /contact et D1 ne fournissent pas de priorité : ils restent normaux. Leurs Controllers, lifecycle et moteurs d'actions ne sont pas modifiés. Les futurs D2/D5 pourront explicitement passer ContactPriority::High ; leurs règles ne sont pas développées ici. L'ancien SupportTicket conserve ses propres valeurs de priorité et n'est pas utilisé.

## Manager Support

- La requête de liste sélectionne maintenant priority.
- Les cards high affichent un petit badge ambre « Prioritaire » ; les cards normal n'ont aucun badge de priorité supplémentaire.
- La page détail affiche le badge high et la valeur de priorité dans les informations du ticket.
- Nouveau filtre serveur : Toutes les priorités / Normal / Prioritaire. Valeurs validées via l'Enum, cumulables avec recherche, statut et source.
- Tri newest/oldest strictement conservé, avec les mêmes départages par id.
- Pagination withQueryString conserve priority et tous les autres paramètres.
- Aucun changement aux routes, actions Manager ou compteurs de statut existants.

Les nouvelles clés priority, all_priorities et priorities.normal/high sont présentes dans les quatre fichiers support.php FR/EN/NL/ES. Les autres clés ont été conservées. CSS limité au nouveau badge cs-priority-high ; la grille de filtres existante prend déjà en charge le retour à la ligne et le mobile.

## Inventaire exact

4 fichiers créés :

- app/Enums/ContactPriority.php
- database/migrations/2026_10_29_000005_add_priority_to_contacts.php
- tests/Feature/ContactPriorityTest.php
- docs/contact-priority.md

14 fichiers modifiés :

- app/Models/Contact.php
- app/Services/TicketService.php
- app/Http/Controllers/manager/ContactSupportController.php
- resources/views/manager/support/index.blade.php
- resources/views/manager/support/show.blade.php
- public/css/contact-support.css
- resources/lang/fr/support.php
- resources/lang/en/support.php
- resources/lang/nl/support.php
- resources/lang/es/support.php
- tests/Feature/PublicContactSubmissionTest.php (assertion de priorité normale)
- tests/Feature/SurveyD1ActionsTest.php (assertion de priorité normale sur les six règles D1)
- tests/Feature/SurveyFoundationTest.php (l'ancienne assertion d'absence de priorité devient présence)
- .gitignore (exceptions ciblées pour le test et cette documentation)

## Validation

Tests sur PostgreSQL dédié, schémas contact_test_* jetables ; MailFake/array uniquement, aucun email réel. Le test de migration crée un ancien Contact avant ajout de la colonne et vérifie le défaut, la préservation des valeurs et le rollback.

- ContactPriorityTest : 9 tests, 80 assertions, succès.
- Suite ContactPriorityTest + ContactFoundationTest + PublicContactSubmissionTest + ManagerContactSupportTest + ManagerContactActionsTest + ManagerContactCardsTest + SurveyFoundationTest + SurveyD1LifecycleTest + SurveyD1ActionsTest : 156 tests, 1879 assertions, succès, aucun test ignoré.
- Couverture : défaut normal, high explicite, signature positionnelle historique, cast, contraintes DB, migration sur données existantes, numéro inchangé, badge liste/détail, filtres normal/high et combinés, validation du filtre, conservation des paramètres de pagination, tri non influencé, quatre langues et actions HTTP reply/close/reopen/delete sur high.
- Tests existants complétés pour vérifier /contact normal et D1 normal, sans changer leurs règles.
- Pint : 13 fichiers PHP concernés ; contrôle final dans le rapport de livraison.
- Vue Support rendue sur ordinateur 1280 px et mobile 390 px : un badge pour le ticket high, aucun pour le normal, trois options de filtre, aucun débordement horizontal.

La migration doit être appliquée selon la procédure de déploiement avant l'utilisation du nouveau code. Elle n'a pas été exécutée sur la base applicative ou de production pendant cette tâche. Arrêt après l'infrastructure de priorité ; D2 et D5 non commencés.
