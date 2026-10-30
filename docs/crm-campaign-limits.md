# Limites des campagnes CRM

## Fenêtre SMTP et reprise

Le sender utilise le mailer configuré par `mail.default`. Seul le sender CRM réserve
des tentatives : les factures, rappels et notifications ne passent pas par ce budget.
Les autres applications/client mails utilisant le compte ne sont pas comptabilisés.

Avant chaque réservation, il compte les tentatives dont `attempted_at > UTC maintenant
- 3600 secondes`. Le plafond est 150, toutes campagnes et tous Managers confondus
pour le compte. La tentative située exactement 3600 secondes auparavant sort de la
fenêtre. Il ne s'agit pas d'une remise à zéro à chaque heure civile : les places se
libèrent progressivement selon les heures des tentatives précédentes.

Le journal `crm_campaign_smtp_attempts` conserve chaque tentative (réussie, refusée
ou incertaine) pour que les retries ne remplacent pas l'historique. Une réservation
interrompue ou annulée compte aussi par prudence. Sa suppression ne suit pas celle
du destinataire : le quota ne doit pas être remis à zéro par une suppression.
Les anciennes tentatives sans journal sont comptées contre tous les comptes tant
qu'elles restent dans la fenêtre, leur identité SMTP historique étant inconnue.

L'identifiant du compte est le SHA-256 du transport, de l'hôte et du login SMTP
normalisés. Aucun mot de passe n'est stocké dans ce journal. Les alias du même
mailer et les changements de port/mot de passe partagent le budget. Les mailers
composites débitent prudemment tous leurs comptes possibles et respectent les
budgets/pauses de chacun. Si plusieurs noms d'hôte différents désignent la même
boîte SMTP, utiliser le même hôte canonique dans les configurations.

Le verrou PostgreSQL existant, partagé par tous les processus/nœuds utilisant la
même base, couvre vérification, réservation et envoi. Une transaction courte écrit
le journal et l'état `sending` avant SMTP ; aucune transaction DB n'est maintenue
pendant l'appel réseau. Le plafond minute existant reste appliqué par compte :
10 tentatives/minute par défaut, réglables de 1 à 100.

`CRM_CAMPAIGN_HOURLY_LIMIT` vaut 150 par défaut ; une valeur supérieure est ramenée
à 150. Une valeur inférieure permet de réduire le plafond. Sans changement du
plafond minute, 150 tentatives sont effectuées en plusieurs lots, pas en une fois.

À la limite interne, aucun destinataire supplémentaire n'est réservé : il reste
`pending`, sans `attempted_at`, sans échec. La campagne reste `queued`/`sending`
tant qu'elle a des destinataires `pending`/`sending`. Le scheduler existant appelle
`crm:send-campaigns` chaque minute ; il reprend à la première exécution où une place
est disponible. Il n'attend pas une heure en bloquant un processus et ne requiert
pas de nouveau worker de queue.

## Refus SMTP réel

Le refus OVH constaté sur la campagne 6, destinataires 2933–2941, est :
`550-5.7.0 Mail quota exceeded ... 200 messages per hour and per account`.
Seule une `UnexpectedResponseException` avec code de refus SMTP reconnu et message
explicite `Mail quota exceeded` + `per hour`/`hourly` autorise la reprogrammation.
Un code 550 seul ne suffit jamais.

Le destinataire reste `pending`, avec `next_attempt_at = heure du refus + 3600 s`,
`last_smtp_code`, `attempts_count` et une explication visible. Une pause persistante
de la même durée suspend les campagnes utilisant ce compte, y compris les autres
campagnes. Chaque nouveau refus confirmé reporte encore d'une heure. Il n'y a pas
de nombre maximal de retries sur ces refus explicites : ils restent en attente
jusqu'à acceptation, suppression de campagne ou exclusion du destinataire.
Les erreurs permanentes restent `failed`. Les erreurs de connexion/timeout sans
réponse définitive deviennent `uncertain`, sans retry automatique. Les envois déjà
`sent` et ceux interrompus après une possible acceptation ne sont pas rejoués.
Le consentement et l'adresse sont revérifiés à chaque tentative.

Le plafond CRM réserve une marge théorique de 50 sur un quota de 200. Il ne garantit
pas que les autres usages de la boîte consommeront moins de 50 : la pause sur refus
réel reste nécessaire. Aucun blocage n'est installé sur les emails fonctionnels.

## Une confirmation par Manager et par jour

Le POST de lancement, y compris la confirmation finale du wizard, réserve une ligne
dans `crm_campaign_daily_launches` pour le Manager authentifié et la date UTC.
Un appel interne sans session utilise le Manager auteur. Le verrou de ligne Manager
et la contrainte unique `(manager_id, launch_date)` empêchent les confirmations
concurrentes de campagnes différentes. La réservation et la mise en attente sont
atomiques : une confirmation invalide ne consomme pas la journée.

Une programmation consomme le jour de sa confirmation, quelle que soit sa date
d'exécution future. Les reprises et le déclenchement automatique d'une campagne
programmée ne consomment pas de nouvelle réservation. Les autres Managers ont leur
propre journée, mais partagent toujours le budget SMTP. Minuit UTC libère le jour
suivant. Rejouer la même confirmation ne relance pas la campagne. Supprimer une
campagne, même définitivement, ne libère pas la réservation de sa journée.

## Déploiement

Déployer la migration additive `2026_10_30_200000_add_crm_campaign_smtp_limits.php`
avant d'activer le nouveau sender, avec la procédure de déploiement habituelle et
sans ancien sender en cours. Elle ajoute les colonnes destinataire et trois tables.
Elle reprend les réservations historiques par Manager/jour, y compris les campagnes
supprimées logiquement. Pour les programmations historiques sans date de confirmation,
`updated_at` est utilisé en dernier recours après `queued_at` et `started_at`.

Appliquer seulement les migrations revues ; ne pas lancer aveuglément toutes les
migrations en attente du dépôt. Reconstruire le cache de configuration si nécessaire.
Avec Docker `env_file`, une modification des variables exige de recréer le conteneur,
pas seulement de le redémarrer. Vérifier le cron existant ; aucun envoi de test réel
ou lancement manuel du sender n'est nécessaire pour valider les tests automatisés.

Les anciens `failed` ne sont pas remis automatiquement en attente : leurs messages
génériques en base ne prouvent pas un refus de quota. Pour récupérer les neuf anciens
échecs, vérifier les logs puis effectuer une opération ciblée et auditée sur la campagne
6 et les destinataires 2933–2941, après déploiement. Ne jamais réinitialiser globalement
les destinataires `failed`, `sent` ou `uncertain`. Aucune modification des données VPS
n'est effectuée par cette implémentation.
