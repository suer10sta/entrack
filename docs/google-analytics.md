# Google Analytics — Manager / SEO

Configurer `GOOGLE_ANALYTICS_PROPERTY_ID` (ID numérique de propriété GA4, pas l’identifiant de mesure G-…). La configuration reconnaît aussi `GA4_PROPERTY_ID`. Puis vider/reconstruire le cache de configuration Laravel selon le déploiement.

Page : `/manager/seo/google-analytics`. Endpoints GET : `/api/seo/google-analytics/{overview,traffic,pages,sources,campaigns,events,countries,devices}`. Accès protégé par la session `auth:manager`. Paramètres : `days=7|28|90|180`, `refresh=0|1`. Cache de trois heures isolé par Manager, connexion Google, propriété et rapport. Seuls les succès sont mis en cache. Aucun token n’est envoyé au navigateur.

L’OAuth, le callback, la table de tokens chiffrés et le verrou de refresh sont ceux de Search Console. Depuis la page Analytics, « Autoriser Analytics » ajoute le scope analytics.readonly en conservant webmasters.readonly, via le même client OAuth et le même callback. Une autorisation nouvelle peut être nécessaire même si le scope est déjà configuré dans Google Cloud. Le compte Google doit également avoir accès à la propriété GA4. Il n’existe pas de seconde authentification ni de deuxième stockage de tokens.

Les requêtes Data API v1beta runReport utilisent des périodes relatives jusqu’à hier, évaluées dans le fuseau de la propriété, renvoyé dans metadata.timeZone. Les KPI sont demandés sans dimension pour dédupliquer les utilisateurs sur la période ; ils ne sont pas calculés à partir des tableaux ou de la somme des utilisateurs quotidiens. Le taux d’engagement vient de l’API ; engagement moyen par utilisateur = userEngagementDuration / activeUsers. Les courbes affichent les valeurs brutes par date sur une échelle commune, sans normalisation. Les lignes absentes ne sont pas interpolées. Les tableaux sont limités aux 1 000 premiers résultats, triés par la métrique principale. Le nombre total de lignes et les métadonnées de qualité sont exposés afin d’indiquer les limites et seuils Google.

Les données peuvent être vides juste après installation GA4 ; vérifier les droits, la propriété et le traitement des données. La collecte publique GA4 et la page Search Console ne sont pas modifiées par ce module.

Références : https://developers.google.com/analytics/devguides/reporting/data/v1 et https://developers.google.com/analytics/devguides/reporting/data/v1/api-schema
