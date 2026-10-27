# Campagnes email CRM Prospects

## Parcours Manager

Manager → SEO → Campagnes. Créer un brouillon (nom, objet sans retours à la ligne, contenu texte simple), enregistrer le message, filtrer les prospects par statut/source, sélectionner sur une ou plusieurs pages ou ajouter tous les prospects filtrés, puis enregistrer la sélection. Le compteur local déduplique les emails et le compteur enregistré sert de référence à la confirmation d’envoi. Les modifications non enregistrées désactivent le bouton Envoyer. Une confirmation explicite est requise. Une révision numérique verrouillée en base empêche une confirmation périmée après modification du message ou de la sélection par un autre Manager.

L’envoi fige le brouillon et ses destinataires : aucune modification ni réexpédition de cette campagne. Les écrans conservent les destinataires, adresses utilisées, dates, état et incidents. La sélection est partagée entre les Managers du CRM comme les prospects existants ; manager_id conserve son auteur. Toutes les routes sont sous auth:manager et CSRF ; le POST d’envoi est limité à dix demandes/minute.

## Données

Deux nouvelles tables : crm_campaigns et crm_campaign_recipients. Relations Manager → campagnes, campagne → destinataires, destinataire → Prospect (y compris archivés pour l’historique). Le CRM Manager étant global, aucun entreprise_id propriétaire n’est ajouté ; ne pas confondre avec linked_entreprise_id, qui reste le compte client.

Chaque campagne ne peut avoir qu’un destinataire par prospect ET par email normalisé (contraintes SQL). trim + lowercase réutilise Prospect::normalizeEmail. Les adresses invalides et les doublons sont ignorés et signalés. Maximum 5 000 destinataires. Les adresses et noms sont figés lors de la sélection ; avant un envoi, un prospect archivé ou dont l’adresse a changé est ignoré. Aucun User/Entreprise créé, aucun matching Register/Stripe déclenché.

## Infrastructure et déploiement

Réutilise Mail::to()->send(), le mailer global DalyTrac config/mail.php, son expéditeur et ses identifiants SMTP existants. Aucun nouveau transport, compte SMTP ou système d’authentification. Le Mailable ProspectCampaignMail utilise un HTML échappé et une alternative texte. Pas de HTML arbitraire. Le suivi CRM des nouveaux envois est décrit dans la section Tracking ci-dessous.

Déployer le code et exécuter la migration 2026_10_27_000100_create_crm_campaigns.php (via le processus de migrations habituel, par exemple php artisan migrate --force sur le serveur après revue des migrations en attente). La migration n’a pas été appliquée aux données de développement/production par l’agent.

La commande `php artisan crm:send-campaigns` est enregistrée dans le scheduler existant toutes les minutes. Le cron Laravel habituel doit fonctionner : `* * * * * cd /chemin/dalytrac && php artisan schedule:run >> /dev/null 2>&1`. Aucun worker de queue supplémentaire n’est requis et QUEUE_CONNECTION=sync ne provoque pas un envoi dans la requête web.

Configuration facultative : CRM_CAMPAIGN_BATCH_SIZE=10 (1–100 tentatives globales par minute glissante, toutes campagnes confondues), CRM_CAMPAIGN_INTERVAL_MS=1000 (pause entre messages d’un lot, 0–5000 ms), CRM_CAMPAIGN_STALE_MINUTES=15 (au moins 5). Ajuster selon les limites réelles du prestataire SMTP. Le sender utilise le verrou PostgreSQL partagé entre processus/nœuds et le scheduler withoutOverlapping. La commande peut être exécutée manuellement par un opérateur, mais elle envoie réellement les campagnes en attente : ne pas la lancer simplement pour vérifier une installation.

Les transports log/array et les configurations failover/roundrobin les contenant sont refusés pour éviter de présenter un email journalisé comme envoyé. Les campagnes restent en attente et un avertissement est journalisé jusqu’à correction du mailer existant.

## Résultats et anti-doublons

États campagne : Brouillon → En attente → En cours → Terminée / Terminée avec incidents / Échec. États destinataire : Sélectionné → En attente → En cours → Envoyé / Échec / À vérifier / Ignoré. La ligne En cours est validée en base avant l’appel SMTP ; aucun verrou transactionnel n’est maintenu pendant l’appel réseau. Un appel répété au POST Envoyer ou à la commande ne remet jamais un destinataire en attente.

« Envoyé » signifie accepté par le mailer/serveur, pas preuve de réception en boîte de réception. Une exception ou annulation du mailer produit un Échec sans changement CRM, sans retry automatique. Un crash ou une perte DB après acceptation SMTP laisse une tentative En cours ; après expiration elle devient À vérifier et n’est jamais renvoyée automatiquement. SMTP ne permet pas une garantie d’exactly-once entre acceptation réseau et commit DB : le choix conservateur évite les doubles envois au prix de tentatives à vérifier manuellement. Les erreurs affichées sont génériques ; les détails techniques sont dans les logs (campaign_id/recipient_id).

Après acceptation et persistance de l’envoi, seuls les prospects encore Nouveau passent à Contacté via ProspectStatusService, source campaign et clé campaign:ID:prospect:ID. Les autres statuts restent identiques, Perdu compris. Une activité « Campagne email envoyée » conserve campagne, destinataire, objet et date. La synchronisation CRM est atomique et idempotente. En cas d’échec CRM, le statut mail reste Envoyé ; le prochain passage retente uniquement la synchronisation, sans renvoyer l’email.

## Tests

CrmCampaignTest utilise PostgreSQL de test et un schéma isolé, applique seulement les migrations dans ce schéma et simule le mailer. Couvre autorisations Manager, validation/headers, filtres, sélection persistante, normalisation/doublons SQL, confirmation et gel, lots et budget global, concurrence, exceptions/annulation SMTP, états ambigus, échec DB après SMTP, reprise CRM sans renvoi, archivage/changement d’adresse, tous les statuts et Perdu, HTML échappé et commande Artisan. Aucun email réel envoyé pendant les tests. Aucun test n’utilise les données de développement ou de production.

## Fichiers concernés

- database/migrations/2026_10_27_000100_create_crm_campaigns.php
- app/Models/CrmCampaign.php ; app/Models/CrmCampaignRecipient.php
- app/Services/CrmCampaignService.php ; app/Services/CrmCampaignSender.php
- app/Http/Controllers/manager/CrmCampaignController.php
- app/Console/Commands/SendCrmCampaigns.php ; app/Console/Kernel.php
- app/Mail/ProspectCampaignMail.php
- resources/views/emails/prospect-campaign.blade.php ; prospect-campaign-text.blade.php
- resources/views/manager/campaigns/index.blade.php ; show.blade.php ; partials/content-fields.blade.php
- resources/views/manager/sidebar.blade.php
- resources/views/manager/prospects/partials/timeline-entry.blade.php (libellé de l’activité de campagne et de son origine)
- routes/prospects.php ; config/crm.php
- public/css/crm-campaigns.css ; public/js/crm-campaigns.js
- tests/Feature/CrmCampaignTest.php ; docs/crm-campaigns.md

Validation finale : 21 tests dédiés aux campagnes ; suite CRM ciblée de 129 tests réussis / 865 assertions. Build Vite réussi. Vérifications visuelles desktop/mobile et compteur de sélection dans le navigateur avec fixtures temporaires supprimées ensuite. Aucun envoi réel.

## Compatibilité des bases déjà migrées

La migration additive 2026_10_27_000101_add_revision_to_crm_campaigns.php ajoute revision avec DEFAULT 0 si cette colonne manque sur une installation ayant déjà exécuté la création des campagnes. Modifier une migration déjà exécutée ne modifie pas les tables existantes. Le correctif utilise PostgreSQL ADD COLUMN IF NOT EXISTS, conserve les campagnes/destinataires et est sans effet sur une installation récente disposant déjà de revision. Son rollback conserve volontairement cette colonne également définie par la migration de création.

## Lecture des résultats SMTP

L’historique affiche par campagne le total des destinataires, les succès SMTP, les échecs réels, les envois en attente et le début réel de l’envoi (la date de demande reste distinguée). Les états techniques completed_with_errors et failed sont présentés « Terminée avec erreurs ». Les résultats incertains et les destinataires ignorés restent séparés des échecs SMTP et des réussites.

« Voir les détails » ouvre la fiche et son tableau de résultats : nom, email, état d’envoi, date d’envoi confirmé ou de tentative, erreur enregistrée. Le bandeau « Campagne terminée : X emails envoyés, Y échecs » apparaît uniquement pour une campagne terminée par le sender, avec completed_at et des comptes calculés depuis les destinataires. Les pages En attente/En cours se rechargent toutes les 15 secondes lorsqu’elles sont visibles, puis cessent une fois terminées ; aucun brouillon n’est rechargé automatiquement. Aucun changement au sender, à l’idempotence ou à la règle Nouveau → Contacté : un échec ne marque toujours pas le prospect Contacté.

Validation de cette amélioration : 24 tests campagnes et suite CRM de 132 tests / 909 assertions réussis ; build Vite réussi ; contrôles visuels desktop/mobile et lien vers les détails vérifiés. Aucune migration supplémentaire nécessaire, aucun email réel envoyé.

## Création guidée en trois étapes

L’historique reste sur GET /manager/seo/campaigns et le bouton « Créer une campagne » ouvre /campaigns/create. Les brouillons utilisent les mêmes campagnes et destinataires : Informations → Audience cible → Contenu de l’email. La sélection et ses filtres restent inchangés, avec sauvegarde avant Précédent/Suivant et compteur dédupliqué en direct.

La migration additive 2026_10_27_000102_add_campaign_composition_fields.php ajoute quatre colonnes nullable : preheader, utm_campaign, cta_text, cta_url. Déployer cette migration avant les nouveaux écrans. Les anciennes campagnes sont conservées.

POST /campaigns/{campaign}/setup enregistre les étapes 1 et 3 avec contrôle de révision. GET /campaigns/{campaign}/preview rend le même Mailable que le sender, sans aucun envoi ; aperçu isolé dans une iframe sandbox avec réponse privée sans cache. Le contenu est du texte échappé. La confirmation finale sauvegarde le message et met la campagne en attente atomiquement avec le contrôle de révision existant.

Un CTA exige texte et URL HTTP(S) sans identifiants. Le lien conserve les paramètres métier et le fragment, remplace les anciens utm_source/utm_medium/utm_campaign et ajoute utm_source=dalytrac, utm_medium=email et la campagne facultative. Le suivi par destinataire des nouveaux envois est décrit dans la section Tracking ci-dessous. Le sender reçoit seulement les nouveaux paramètres de présentation du Mailable ; lots, limites SMTP, scheduler, résultats et déduplication restent identiques.

Validation du parcours guidé : 134 tests CRM / 951 assertions réussis, vérification de syntaxe JavaScript et Pint réussie, vues Blade compilées, contrôle visuel des étapes Informations/Contenu et vérification navigateur du résumé dynamique. Migration additive appliquée localement. Aucun email réel envoyé, aucun lancement du scheduler/sender.

## Images et pièces jointes — étape 3

La migration additive 2026_10_27_000103_add_campaign_media.php ajoute media (JSONB nullable). Elle conserve les anciennes campagnes. Les fichiers sont enregistrés sur le disque local privé dans crm-campaigns/{id} ; préserver ce répertoire sur les déploiements et le rendre accessible au processus SMTP/scheduler.

Image facultative : upload ou URL HTTP(S) publique directe ; JPG/JPEG, PNG, GIF et WEBP, 2 Mo maximum. Document facultatif : upload ou URL directe ; PDF, DOC/DOCX, XLS/XLSX, PPT/PPTX, ODT/ODS/ODP, RTF, TXT et CSV, 10 Mo maximum. Les MIME sont contrôlés à partir des octets. SVG, exécutables et archives génériques ne sont pas acceptés. Les limites PHP/reverse proxy doivent permettre une requête d’au moins 12 Mo plus les données de formulaire.

Les URLs sont téléchargées lors de l’enregistrement du brouillon, avec délai et taille limités, contrôle d’adresses Internet publiques, IP épinglée avec cURL, sans proxy, identifiants ni redirections. Utiliser un lien public direct vers les octets du fichier, pas une page de partage. Le contenu distant n’est jamais récupéré lors des envois par destinataire. Les remplacements/suppressions sont appliqués après commit ; les nouveaux fichiers sont supprimés si la transaction échoue.

Le Mailable existant intègre l’image en CID et joint réellement le document au message MIME. Laravel remplace les images CID par des données inline pour l’aperçu sandbox ; aucune URL d’image externe n’est chargée dans l’aperçu. Le nom de la pièce jointe est visible dans l’aperçu ; le fichier est joint à l’email final, sans création d’une URL publique. Un fichier manquant provoque un échec d’envoi normal, sans passer le prospect à Contacté. Le CTA facultatif et son lien UTM généré sont regroupés dans une section dédiée. Audience, scheduler, lots, SMTP et déduplication restent inchangés.

Validation : 138 tests CRM / 1 000 assertions réussis, dont uploads image/PDF, document Excel réel, image/document par URL, refus des adresses privées/redirections/formats invalides/fichiers surdimensionnés, rollback des fichiers et échec contrôlé si un fichier enregistré manque. Vérification du vrai message MIME (image inline et document joint) avec transport mémoire, aperçu navigateur et URL UTM dynamique. Pint et syntaxe JS réussis. Migration 000103 appliquée localement. Aucun email réel envoyé.

## Tracking CRM des campagnes

Migration additive : 2026_10_27_000104_add_campaign_tracking.php. Elle ajoute aux destinataires un token opaque unique, first_opened_at/open_count, first_clicked_at/click_count et last_clicked_url, ainsi que crm_campaign_links (destinataire, token unique, destination figée, hash de destination unique par destinataire, premier clic et compteur par URL). Aucun suivi rétroactif des emails déjà envoyés : les anciens destinataires et les campagnes en attente reçoivent un token lors du rendu de leur prochain envoi normal, sans renvoi.

Le Mailable de chaque destinataire ajoute un GIF transparent 1×1 et remplace le CTA ainsi que les liens HTTP(S) du message texte par des URLs opaques distinctes, dans les alternatives HTML et texte. Les destinations conservent paramètres métier, fragment et UTM de campagne. Le rendu est idempotent et les liens sont réutilisés par destinataire/destination. L’aperçu Manager reste sans pixel ni lien de tracking et ne génère aucun événement.

Routes publiques sans session, cookies, authentification ou dépendance GA4/consentement : GET /crm/t/o/{token}.gif et GET /crm/t/c/{token}. Les tokens sont 32 octets aléatoires encodés en hexadécimal (64 caractères). Aucun email ou ID interne dans ces URLs. Les liens ne permettent pas de modifier la destination via la query string. Les réponses ont Cache-Control: no-store et Referrer-Policy: no-referrer. Exclure ces routes de tout cache CDN/proxy. APP_URL doit être l’URL HTTPS publique de DalyTrac pour les envois CLI ; déployer la migration et les nouvelles routes avant de reprendre le scheduler existant.

Un pixel valide met à jour uniquement les compteurs et la première date ; aucun changement CRM. Un clic enregistre la première date, le nombre et la destination réelle, puis redirige en 302. Seuls les destinataires confirmés sent avec sent_at sont comptés. HEAD et requêtes explicitement signalées prefetch ne comptent pas. Les autres scanners/proxies peuvent encore influencer les statistiques ; les ouvertures ne prouvent pas une lecture humaine. Pixel inconnu bien formé : GIF sans écriture ; token de clic inconnu ou destination invalide : 404. Les premiers événements et incréments sont protégés par transaction/verrou destinataire ; les envois SMTP n’ont pas changé.

Si le prospect non archivé est actuellement Contacté, un clic déclenche ProspectStatusService vers Engagé, avec source campaign_click et reçu d’idempotence permanent campaign-click:{recipient}. Les autres statuts restent inchangés. Aucun passage automatique à Perdu. Les erreurs de synchronisation CRM ne bloquent ni le compteur ni la redirection ; un prochain clic peut réessayer le même reçu, sans doublon de transition. Aucun job, transport SMTP ou processus scheduler supplémentaire.

L’historique et les détails affichent les destinataires uniques ouverts/cliqués et les taux (uniques / destinataires envoyés), distincts des compteurs bruts. Les détails présentent Envoyé → Ouvert → Cliqué, dates des premiers événements, compteurs et destinations cliquées. Un clic sans pixel chargé n’invente pas d’ouverture. Les anciens résultats SMTP, erreurs, en attente et incertains sont conservés.

Validation tracking : 145 tests CRM / 1 118 assertions réussis, dont rendu réel du sender avec tokens propres aux destinataires et aperçu sans suivi, ouvertures répétées sans statut CRM, clics répétés/destinations UTM figées, reçus idempotents après changement manuel de statut, protection de Nouveau/Engagé/Démo/Client/Perdu, erreurs CRM puis reprise, liens multiples/statistiques uniques, tokens invalides, non-envoyés, HEAD/prefetch, prospect archivé et fuseau PostgreSQL non UTC. Pint et compilation Blade réussis. Contrôle visuel du dashboard et de la timeline avec fixtures temporaires supprimées. Migration 000104 appliquée localement. Aucun email réel envoyé.
