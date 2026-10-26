# Google Search Console — Manager

Configurer côté serveur (ne jamais exposer le secret dans le navigateur) :

```dotenv
GOOGLE_SEARCH_CONSOLE_CLIENT_ID=
GOOGLE_SEARCH_CONSOLE_CLIENT_SECRET=
GOOGLE_SEARCH_CONSOLE_REDIRECT_URI=https://votre-domaine/api/auth/google/callback
```

Le redirect URI doit être identique dans Google Cloud, avec HTTPS en production. Conserver APP_KEY : les tokens sont chiffrés avec cette clé. Utiliser un cache partagé compatible avec les verrous (Redis recommandé pour plusieurs serveurs), et une session serveur (file/database/redis).

Les variables existantes `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` et `GOOGLE_REDIRECT_URI` sont également reconnues. Les variables `GOOGLE_SEARCH_CONSOLE_*` prennent priorité lorsqu’elles sont renseignées. Après une modification du `.env` sur un serveur avec configuration mise en cache, exécuter `php artisan config:cache`.

Installation : `composer install`, puis `php artisan migrate --force` et `php artisan config:cache`. Ouvrir `/manager/seo`, connecter Google et sélectionner une propriété accessible. Aucun secret n’est nécessaire dans le frontend. Les comptes OAuth en mode test Google peuvent avoir des refresh tokens de durée limitée ; publier l’application OAuth selon les exigences Google avant mise en production.

Routes GET protégées par `auth:manager` et session web : `/api/auth/google`, `/api/auth/google/callback`, `/api/seo/sites`, `/api/seo/{overview,queries,pages,countries,devices,sitemaps}`. Les rapports acceptent `site` (identifiant exact, y compris `sc-domain:`), `start` et `end` au format YYYY-MM-DD. Le cache de succès est isolé par Manager, connexion, propriété, période et rapport pendant trois heures. Les erreurs ne sont pas mises en cache ; 429 inclut Retry-After. Les réponses HTTP restent privées et non stockables côté navigateur.

Les KPI proviennent d’une requête agrégée sans dimension, pas de la somme des top requêtes. Les rapports détaillés sont limités à 1 000 lignes. Les jours sans données sont omis par Google. Les sitemaps sont les sitemaps envoyés connus de Google ; ce rapport ne constitue pas un contrôle d’indexation de toutes les URL.

## Comparaison avec Search Console

Les dates de début et de fin sont inclusives et interprétées par Google dans `America/Los_Angeles` (heure du Pacifique, avec changement saisonnier). Aucune conversion des dates saisies vers UTC n’est effectuée. Par défaut, la période couvre les 28 jours se terminant hier en heure du Pacifique.

Les rapports acceptent aussi `type=web|image|video|news` et `data_state=all|final`. `all` est la valeur par défaut pour inclure les données récentes ; Google peut encore les réviser. Aucun filtre pays, appareil, requête, page ou apparence n’est ajouté. Comparer exactement la même propriété (domaine et préfixe URL sont distincts), période et type de recherche, sans filtre additionnel dans Google.

Le total est `raw_aggregate_response.rows[0]`, avec `aggregationType=byProperty`, sans dimensions ni rowLimit. Les 1 000 lignes des tableaux détaillés n’influencent aucun KPI. CTR et position sont les valeurs agrégées de Google, jamais une moyenne simple des lignes ; seul le CTR est multiplié par 100 pour l’affichage. La période maximale de 16 mois tient dans les 1 000 lignes quotidiennes.

Le bouton « Actualiser depuis Google » envoie `refresh=1` et remplace seulement le cache des rapports demandés. Le cache v2 distingue propriété, période, type et état des données et ignore les anciennes entrées final-only. « Source et diagnostic des KPI » montre la requête, la réponse agrégée brute, les KPI backend, les valeurs affichées, l’heure de collecte, l’utilisation du cache et la date de début des données incomplètes. Cette trace est uniquement accessible au Manager connecté. `GOOGLE_SEARCH_CONSOLE_DEBUG=true` active en plus une trace serveur limitée aux agrégats et au contexte, sans tokens ni réponses contenant des requêtes de recherche ; désactiver après investigation.

### Incident vérifié le 26 octobre 2026

Comparaison API réelle, propriété `sc-domain:dalytrac.com`, recherche Web, 26 septembre–26 octobre 2026 inclus : `final` renvoyait 0 clic, 7 impressions, CTR 0, position 27,714285714285715 ; `all` renvoyait 0 clic, 10 impressions, CTR 0, position 26,3. Les sommes quotidiennes correspondaient dans les deux réponses. Google signalait des données incomplètes à partir du 25 octobre. L’ancien cache contenait les mêmes 7 impressions que la nouvelle requête `final` : la cause vérifiée était l’exclusion des données récentes, pas une perte d’agrégation ni un cache périmé.

Références : https://developers.google.com/webmaster-tools/v1/searchanalytics/query et https://developers.google.com/identity/protocols/oauth2/web-server

Validation réelle à effectuer avec un Manager et des identifiants Google configurés : consentement, callback, sélection d’une propriété, rapports, puis reconnexion après révocation. Les tests automatisés simulent Google sans contacter de compte réel.
