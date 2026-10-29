# Désabonnement des campagnes CRM

## Diagnostic et périmètre

Les campagnes utilisent ProspectCampaignMail et les vues emails/prospect-campaign (HTML et texte). Le contenu et la salutation sont actuellement français, sans langue de campagne/destinataire. CrmCampaignSender traite les campagnes queued/sending, libère les campagnes programmées à échéance et revalide les contacts avant envoi. Les destinataires peuvent représenter un Prospect ou un User. Avant cette modification, les contacts archivés/supprimés ou dont l'adresse avait changé étaient ignorés ; le seul statut Perdu n'interdisait pas l'envoi. Ce comportement reste inchangé pour les pertes sans opt-out.

CrmCampaignTracking réécrit uniquement le contenu et le CTA en liens marketing opaques et ajoute un pixel. ProspectStatusService centralise les transitions, reçus idempotents, historique, origine et auteur. Le statut Perdu était réservé aux changements manuels ; l'exception ci-dessous ne modifie pas ProspectStatus::allowsAutomaticTransitionTo.

Aucun changement Questionnaire, Survey foundation, D1-D6, Contact, Support Manager, Stripe, Scheduler ou SMTP global. Aucun second système de campagne. Aucune fonctionnalité de réabonnement.

## Stockage

Migration additive `2026_10_29_200000_add_crm_campaign_opt_out.php` : `prospects.unsubscribed_at`, timestamptz nullable et indexé. La préférence appartient au Prospect, indépendamment du statut, de la campagne et du destinataire d'origine. Le champ n'est pas assignable en masse ; les formulaires Manager existants ne peuvent pas l'effacer. Un changement manuel de statut ne le modifie pas.

Aucune colonne de token supplémentaire ni nouvelle table. Aucun changement de structure aux tables campagnes, destinataires, historiques ou activités. Aucune migration exécutée sur la base applicative/production ; appliquer la migration selon la procédure habituelle avant de déployer le code d'envoi qui la requiert.

## Token et destinataire

CrmCampaignUnsubscribe utilise Crypt et la clé applicative existante : chiffrement aléatoire authentifié Laravel, puis représentation URL-safe. Le contenu protégé est limité à un marqueur de finalité versionné, l'identité du CampaignRecipient, celle du Prospect, son public_token immuable et le hash de l'adresse figée du destinataire. Aucun email, identifiant Prospect/entreprise lisible ni donnée personnelle en clair dans l'URL. Le token est distinct du tracking et n'est jamais stocké dans CrmCampaignLink.

La résolution vérifie l'authenticité, la finalité, les types, l'existence des deux enregistrements, le jeton immuable du Prospect, le hash de l'adresse du destinataire et la correspondance du Prospect lorsqu'il est directement référencé. Les champs POST prospect_id/email/etc. ne servent jamais à choisir la cible. Un identifiant de Prospect réutilisé ne suffit pas à rendre un ancien token valide.

Le lien reste réutilisable sans échéance arbitraire, tant que ses enregistrements et la clé applicative restent disponibles. Une campagne archivée n'invalide pas le lien ; un destinataire supprimé ou un Prospect inexistant renvoie une page de lien invalide. Un Prospect soft-deleted peut encore enregistrer son refus sans être restauré.

Pour une audience User, on réutilise un Prospect CRM déjà connu via linked_user_id ou email_normalized. Aucun Prospect n'est créé automatiquement. Un compte sans Prospect CRM correspondant n'entre pas dans ce workflow de désabonnement Prospect ; sa sélection et son envoi existants restent inchangés. Les prospects en doublon partageant une adresse sont tous exclus de l'envoi dès qu'une préférence d'opt-out correspond à cette adresse, sans imposer une transition de statut à toutes les fiches doublons.

## GET, POST et page publique

Route GET/POST `/{locale}/crm/unsubscribe/{token}`, sous web/session/CSRF/throttle, sans authentification obligatoire. Seuls CheckSubscription et SetUserTimezone sont exclus sur cette nouvelle route, sans modification de leurs classes ou des routes existantes.

GET affiche « Vous souhaitez vous désabonner ? » avec un bouton POST explicite. Il ne modifie ni Prospect, ni historique, ni activité, ni compteur marketing. Pas de désabonnement par GET/HEAD, User-Agent ou simple préchargement d'URL.

POST revalide le token puis enregistre la préférence via le service CRM. Une confirmation déjà effectuée affiche simplement le succès. La page est responsive, aux couleurs DalyTrac, sans donnée personnelle, tracker, image distante ou ressource tierce. En-têtes no-store/private, no-referrer et noindex/nofollow. CSRF reste actif et testé.

Les pages confirmation, succès et lien invalide ainsi que les boutons existent en FR/EN/NL/ES. Le sélecteur de langue ne change jamais l'identité cible. Le footer des campagnes reste français, conformément à leur langue actuelle ; aucun changement de langue du corps commercial n'est introduit.

## Service CRM, historique et timeline

L'entrée dédiée `ProspectStatusService::unsubscribeFromCampaigns` verrouille le Prospect dans une transaction, vérifie unsubscribed_at et enregistre la date. Elle utilise ensuite la même logique interne de transition que transition(), avec la seule exception explicite campagne -> Perdu. L'entrée générique transition(..., source='campaign_unsubscribe') ne peut pas contourner la règle manuelle de Perdu.

Origine : campaign_unsubscribe ; Manager : NULL ; note : « Prospect désabonné des campagnes email ». Reçu CrmLifecycleEvent unique par source et clé `campaign-unsubscribe:{public_token}`. Une activité de type automatic porte ce même libellé et une metadata event=campaign_unsubscribe, avec clé métier et CampaignRecipient d'origine.

Le Controller de timeline corrèle activité et transition par cette clé, pour afficher un seul événement explicite avec le changement de statut et l'origine « Désabonnement campagne ». Si le Prospect était déjà Perdu, aucune transition redondante n'est créée ; l'activité enregistre tout de même son opt-out explicite.

Préférence, statut, reçu, historique et activité sont atomiques. L'échec d'une écriture annule l'ensemble. La timezone PostgreSQL est fixée localement à UTC pendant cette transaction, sans modifier la configuration globale.

Après un changement manuel ultérieur du statut, une nouvelle visite ou confirmation reste un succès sans réappliquer Perdu ni effacer la préférence. Aucun événement/timeline supplémentaire n'est créé.

## Protection de toutes les campagnes

CrmCampaignConsent vérifie les préférences des Prospects correspondant au prospect_id, à l'adresse normalisée ou au linked_user_id. Le contrôle inclut les fiches archivées : un archivage, une restauration ou un autre statut ne réactive pas l'envoi.

- CrmCampaignService filtre les sélections personnalisées.
- CrmCampaignAudience filtre prospects, abonnés et essais, sans modifier la définition métier de ces audiences.
- CrmCampaignSender contrôle à la réservation puis à nouveau immédiatement avant le transport SMTP. Cette seconde vérification couvre le désabonnement intervenu après la préparation/réservation.
- Les campagnes déjà programmées conservent leur programmation ; leurs destinataires désabonnés seront skipped au moment de l'envoi.
- Les destinataires déjà préparés ne sont pas effacés : leur état skipped conserve l'explication et respecte les résultats/KPI existants.

Une coordination PostgreSQL par adresse et Prospect est partagée entre envoi et confirmation. Les verrous de session sont acquis dans un ordre stable et couvrent le dernier contrôle, le transport et son résultat, sans transaction DB ouverte pendant SMTP. Une confirmation attend un envoi déjà en cours pour ce même destinataire ; après son retour réussi, les envois suivants voient obligatoirement l'opt-out. Un message déjà accepté par SMTP ne peut pas être rappelé. Le verrou global d'envoi CRM et son budget existants sont conservés.

## Tracking et email

Le lien discret « Se désabonner » est ajouté au bas des versions HTML ET texte, après la décoration marketing du contenu/CTA. Il n'est pas converti en URL crm/t/c, ne crée pas de CrmCampaignLink et ne touche ni click_count, ni first_clicked_at, ni les KPI marketing. Les liens commerciaux et le pixel gardent leur comportement antérieur.

Les prévisualisations et emails de test Manager sans destinataire Prospect réel ne reçoivent pas de lien d'action fictif. Chaque Mailable réel lié à un destinataire Prospect construit son lien personnel. Réafficher le même Mailable ne modifie pas le lien déjà construit.

## Inventaire

Fichiers créés :

- database/migrations/2026_10_29_200000_add_crm_campaign_opt_out.php
- app/Services/CrmCampaignConsent.php
- app/Services/CrmCampaignUnsubscribe.php
- app/Http/Controllers/CrmCampaignUnsubscribeController.php
- routes/crm-unsubscribe.php
- resources/views/crm/unsubscribe.blade.php
- public/css/crm-unsubscribe.css
- resources/lang/fr/crm-unsubscribe.php
- resources/lang/en/crm-unsubscribe.php
- resources/lang/nl/crm-unsubscribe.php
- resources/lang/es/crm-unsubscribe.php
- tests/Feature/CrmCampaignUnsubscribeTest.php
- tests/Support/crm-unsubscribe-worker.php
- docs/crm-campaign-unsubscribe.md

Fichiers modifiés :

- .gitignore (exceptions ciblées pour les tests et ce document)
- app/Models/Prospect.php (cast de la préférence)
- app/Services/ProspectStatusService.php
- app/Services/CrmCampaignService.php
- app/Services/CrmCampaignAudience.php
- app/Services/CrmCampaignSender.php
- app/Mail/ProspectCampaignMail.php
- resources/views/emails/prospect-campaign.blade.php
- resources/views/emails/prospect-campaign-text.blade.php
- app/Http/Controllers/manager/ProspectController.php
- resources/views/manager/prospects/partials/timeline-entry.blade.php
- app/Providers/RouteServiceProvider.php (chargement du nouveau fichier de routes)
- tests/Feature/CrmCampaignTest.php (application de la migration dans le schéma isolé de test)

Les autres modifications déjà présentes ou apparues simultanément dans le workspace ont été conservées.

## Vérification et limites des suites historiques

Tests dans PostgreSQL dédié, schémas crm_unsubscribe_test_* jetables. Aucun email réel : mailer array pour les rendus et mocks du transport d'envoi. Le worker vérifie l'environnement testing, le suffixe de la base et le nom du schéma avant toute opération.

Couverture : footer HTML/texte personnalisé, aucun GET mutant, token altéré/invalide et cibles inexistantes/incohérentes, CSRF, langues, préférence/statut/historique/timeline, six statuts initiaux, déjà Perdu, rejeu après changement manuel, exclusion personnalisée et multi-audiences, campagnes préparées et programmées, course après réservation, tracking inchangé, archivage, rollback atomique, deux confirmations concurrentes et coordination avec le verrou d'un envoi en cours.

La suite CRM élargie inclut deux échecs historiques de fixtures : CrmSubscriptionSyncTest::test_confirmed_payment_activates_company_and_converts_with_history_and_activity et ProspectReferralTest::test_detail_page_displays_link_and_copy_control_without_a_generation_form. Leurs schémas ne créent pas crm_campaign_recipients, pourtant déjà interrogée par la timeline initiale. Les deux mêmes erreurs 500 ont été reproduites avec le Controller Prospect d'origine (empreinte correspondant au début de tâche), chargé uniquement dans un runner temporaire hors dépôt, sans remplacer les sources. Aucun correctif produit ou Stripe/Referral hors périmètre n'a été ajouté pour masquer ces échecs.

Résultats exacts :

- Les 21 tests dédiés au désabonnement passent dans la suite ciblée ci-dessous.
- Suite ciblée CrmCampaignUnsubscribeTest + CrmCampaignTest + ProspectFoundationTest + ManagerProspectsTest + CrmRegistrationSyncTest : 141 tests, 1279 assertions, succès. Elle comprend les 57 tests de campagnes existantes et les 21 tests de désabonnement.
- Suite élargie ajoutant CrmSubscriptionSyncTest et ProspectReferralTest : 175 tests, 1523 assertions, 2 échecs historiques ci-dessus, aucun autre échec.
- Reproduction ciblée avec Controller initial : 2 tests, 15 assertions, les 2 mêmes échecs.
- Pint : 20 fichiers PHP, vérification finale --test réussie.
- git diff --check : code 0.
- Rendus ordinateur 1280 px et mobile 390 px inspectés, sans débordement horizontal.

Le contrôle des empreintes a également détecté des modifications parallèles dans les quatre traductions surveys.php après le début de cette tâche ; elles n'ont été écrites par aucune de ses opérations et ont été laissées intactes. Les autres sources Questionnaire/Contact contrôlées sont identiques à leur état initial.
