# Google Analytics — Manager / SEO

Configurer `GOOGLE_ANALYTICS_PROPERTY_ID` (ID numérique de propriété GA4, pas l’identifiant de mesure G-…). La configuration reconnaît aussi `GA4_PROPERTY_ID`. Puis vider/reconstruire le cache de configuration Laravel selon le déploiement.

Page : `/manager/seo/google-analytics`. Endpoints GET : `/api/seo/google-analytics/{overview,traffic,pages,sources,campaigns,events,countries,devices}`. Accès protégé par la session `auth:manager`. Paramètres : `days=7|28|90|180`, `refresh=0|1`. Cache de trois heures isolé par Manager, connexion Google, propriété et rapport. Seuls les succès sont mis en cache. Aucun token n’est envoyé au navigateur.

L’OAuth, le callback, la table de tokens chiffrés et le verrou de refresh sont ceux de Search Console. Depuis la page Analytics, « Autoriser Analytics » ajoute le scope analytics.readonly en conservant webmasters.readonly, via le même client OAuth et le même callback. Une autorisation nouvelle peut être nécessaire même si le scope est déjà configuré dans Google Cloud. Le compte Google doit également avoir accès à la propriété GA4. Il n’existe pas de seconde authentification ni de deuxième stockage de tokens.

Les requêtes Data API v1beta runReport utilisent des périodes relatives jusqu’à hier, évaluées dans le fuseau de la propriété, renvoyé dans metadata.timeZone. Les KPI sont demandés sans dimension pour dédupliquer les utilisateurs sur la période ; ils ne sont pas calculés à partir des tableaux ou de la somme des utilisateurs quotidiens. Le taux d’engagement vient de l’API ; engagement moyen par utilisateur = userEngagementDuration / activeUsers. Les courbes affichent les valeurs brutes par date sur une échelle commune, sans normalisation. Les lignes absentes ne sont pas interpolées. Les tableaux sont limités aux 1 000 premiers résultats, triés par la métrique principale. Le nombre total de lignes et les métadonnées de qualité sont exposés afin d’indiquer les limites et seuils Google.

Les données peuvent être vides juste après installation GA4 ; vérifier les droits, la propriété et le traitement des données. La collecte publique GA4 et la page Search Console ne sont pas modifiées par ce module.

Références : https://developers.google.com/analytics/devguides/reporting/data/v1 et https://developers.google.com/analytics/devguides/reporting/data/v1/api-schema

## Consent Mode v2, campagnes et événements métier

Le composant head public définit les quatre consentements Google (`analytics_storage`, `ad_storage`, `ad_user_data`, `ad_personalization`) à denied **avant** config. Seul analytics_storage peut passer à granted : accepter la mesure d’audience n’autorise pas la publicité. Mode avancé : le tag public peut envoyer des signaux sans cookies sous consentement refusé ; aucun événement métier Measurement Protocol n’est envoyé sans consentement positif et client_id Google. La configuration gtag/page_view reste unique et limitée aux pages marketing et Register.

`public/js/analytics-consent.js` réutilise les deux clés locales historiques, en privilégiant la décision la plus récente. La bannière et les préférences de la page Cookies mettent à jour les deux clés et Google immédiatement. L’événement storage synchronise également les onglets. Register affiche la même bannière, pas une seconde implémentation. Le contexte client/session Google est transmis à `/analytics/consent` avec CSRF et session Laravel ; aucune URL privée ni donnée personnelle de compte (nom/email) n’est transmise à Google. Une révision et un verrou de session empêchent une ancienne requête d’acceptation d’écraser un refus plus récent.

Les UTM restent gérés automatiquement par GA4 sur les URLs publiques. Aucun événement campagne manuel, aucun page_view supplémentaire, aucune duplication de l’attribution. Le tableau Campagnes existant utilise aussi sessionManualAdContent et sessionManualTerm pour utm_content et utm_term. Source/support et campagne restent dans les dimensions GA4 existantes.

Configurer `GOOGLE_ANALYTICS_API_SECRET` dans le .env et conserver le Measurement ID du flux (`GOOGLE_ANALYTICS_MEASUREMENT_ID`, défaut G-MDJNVZY2WF). Ce secret d’écriture Measurement Protocol est distinct des tokens OAuth de lecture ; aucune seconde authentification OAuth n’est créée. Exécuter la migration `2026_10_26_000001_create_analytics_business_events_tables` avant déploiement, puis reconstruire le cache de configuration si utilisé.

Déclencheurs :

- sign_up : fin du traitement d’inscription, après création des enregistrements et connexion réussie, pas sur GET Register ; identifiant de déduplication lié à l’utilisateur.
- subscription_success : webhook invoice.payment_succeeded protégé par la signature Stripe, première facture d’abonnement (subscription_create), statut payé et montant positif ; clé unique par abonnement. Pas de déclenchement sur l’URL de retour, sur refresh, sur échec, sur facture d’essai gratuite ou sur renouvellement. Montant/devise/identifiants et périodicité proviennent de Stripe lorsque disponibles.

Les tables de contexte et d’événements sont chiffrées avec APP_KEY. Le contexte consentement est revérifié avant la conversion de paiement. La livraison est différée après réponse pour ne pas bloquer le parcours métier. La clé unique en base et la revendication atomique permettent au plus une tentative par événement, y compris lors d’un webhook rejoué. Le statut sent signifie réponse HTTP réussie, **pas** confirmation de présence dans les rapports Google. Après un timeout, l’état uncertain n’est pas relancé automatiquement : on privilégie l’absence de doubles conversions, au prix d’une éventuelle perte de livraison. Les événements pending en l’absence de secret ne sont pas envoyés rétroactivement par un nouveau job ; ne pas les rejouer manuellement sans vérifier consentement et ancienneté. L’attribution de session est fournie uniquement pour un contexte récent (<24h).

Dans GA4 → Administration → Événements / Événements clés, marquer les noms métier souhaités comme événements clés. Le scope OAuth existant est volontairement en lecture seule : DalyTrac ne modifie pas cette configuration Google. Les nouveaux événements apparaissent dans le tableau Événements existant ; les colonnes Événements clés reflètent le paramétrage GA4. Aucun nouveau panneau n’est ajouté.

Validation : tests automatisés consentement et événements, vérification des exclusions Login/privé, et validation stricte des deux payloads auprès de `/debug/mp/collect` (aucune statistique créée par ces essais). La validation Google ne vérifie pas le secret lui-même et n’atteste pas l’ingestion finale. Vérifier un prochain parcours réel consenti dans GA4 Temps réel/DebugView puis dans les rapports, sans créer un paiement factice en production.

Références : https://developers.google.com/tag-platform/security/guides/consent et https://developers.google.com/analytics/devguides/collection/protocol/ga4/validating-events

Le funnel de conversion est limité à Visiteur → sign_up → subscription_success. Aucun événement trial_start ou begin_checkout n’est émis. Les éventuelles données historiques GA4 restent inchangées.
