# Corrections SEO DalyTrac — 23 octobre 2026

## 1. Corrigé

- Architecture Laravel/Blade conservée, sans dépendance React ni migration de base.
- 13 pages publiques dans chacune des 4 langues : `/fr`, `/en`, `/nl`, `/es`, avec les mêmes chemins traduits par le contenu (par exemple `/en/produit`). Cette structure évite de renommer les routes métier. La langue du chemin prime sur session/compte pour ces pages ; les préférences existantes restent utilisées ailleurs.
- Anciennes URL publiques redirigées en 301 vers la version française, indépendamment des cookies. Les paramètres existants sont conservés à la redirection ; les paramètres de tracking sont retirés des canonicals.
- Liens du sélecteur public directement vers les URL linguistiques. `/lang/change` reste compatible avec les usages existants et sait rejoindre la page publique équivalente.
- Canonicals propres à chaque version ; hreflang fr/en/nl/es réciproques et x-default français. Aucun hreflang vers `/lang/change`.
- `/sitemap.xml` : 52 pages statiques localisées + articles réellement publiés, sans doublons, sans pages privées/utilitaires. `lastmod` uniquement à partir de la vraie date de modification des articles. Déclaration dans robots.txt.
- Pagination du blog : `?page=2` conservé dans canonical, og:url et alternates. Paramètres de tracking exclus ; numéro de page invalide normalisé comme le paginateur Laravel.
- JSON-LD Accueil et Produit encodé comme objet complet ; même identité SoftwareApplication ; description traduite. FAQ construite avec les questions réellement affichées, encodage sûr commun. Aucun avis ni prix ajouté.
- Composant Blade SEO partagé pour toutes les pages publiques : titre, description, robots, canonical, OG, Twitter, langue, image et alternates.
- Carte sociale de marque locale `public/img/dalytrac-social.png` (1200 × 630), avec script GD de régénération. Utilisation d'une image propre de l'article lorsqu'une image locale existante est présente dans son corps ; sinon fallback.
- `noindex,follow` dans les vues de connexion, inscription, récupération/réinitialisation et connexion manager ; en-tête X-Robots-Tag pour les réponses utilitaires et privées. Middlewares auth, rôles et signatures conservés.
- Contact : H2 principal transformé en H1 sans changement de classes.
- Blog et À propos ajoutés au footer ; lien « Lire la suite » complété avec le titre pour les lecteurs d'écran.
- Même scope `Post::published()` pour la liste, le détail et le sitemap : brouillons, articles futurs et articles sans date exclus.
- Titres et descriptions ciblés raccourcis en FR/EN/NL/ES ; titre Blog plus descriptif. Les autres textes et fonctionnalités ne sont pas réécrits.
- Ressources relatives des vues À propos/Contact corrigées pour fonctionner sous les nouveaux préfixes linguistiques.
- Vidéos absentes : aucun source MP4 inexistant émis ; état vide traduit avec lien Contact. Si seulement certaines vidéos sont présentes, elles seules ont une source ; cartes indisponibles désactivées et navigation limitée aux vidéos disponibles. Autoplay bloqué par le navigateur géré sans promesse rejetée non interceptée.

## 2. Partiellement corrigé / limites

- Les articles ne disposent pas de modèle de traduction ni de langue en base. Leur URL existante `/blog/{slug}` est conservée et aucun hreflang d'article n'est inventé. Leur interface continue à suivre le système de langue existant. Une vraie traduction éditoriale nécessiterait un modèle dédié, hors de ce changement.
- Le choix d'image d'article couvre les fichiers locaux réellement disponibles dans `public/`, y compris les images servies via un lien storage existant. Les images distantes non vérifiées sont ignorées ; aucun champ d'image fictif n'a été ajouté au modèle.
- Les titres, descriptions, H1 supplémentaires et images contenus dans les articles déjà stockés en production ne sont pas tous contrôlés par les fixtures de test : une revue éditoriale reste nécessaire.
- L'origine canonique est centralisée à `https://dalytrac.com`. En production, les liens générés utilisent cette racine lorsque APP_URL est HTTPS. Le middleware normalise origine et slash final en une réponse pour les GET/HEAD publics, sans réécrire les URL signées ou métier.
- La configuration Nginx/TLS de l'hôte n'est pas modifiée. Le fichier Nginx du dépôt redirige déjà www, mais la réponse HTTP externe, les certificats, les éventuelles règles CDN et le nombre réel de sauts ne sont pas certifiés localement.
- Un contrôleur SitemapController existait dans le dépôt, mais n'était pas raccordé aux routes inspectées. Il a été remplacé par l'implémentation active couvrant langues et articles.

## 3. À faire manuellement en production

1. Déployer les sources et le nouveau visuel social, reconstruire les assets (`npm ci` puis `npm run build` selon le processus de déploiement), reconstruire l'image PHP si Docker est utilisé, puis régénérer les caches Laravel avec le processus habituel. `APP_ENV=production` et `APP_URL=https://dalytrac.com` sont attendus. Ne pas changer APP_KEY.
2. Vérifier que la table `posts`, déjà nécessaire au blog, est disponible dans la base de production. Aucun schéma métier n'a été modifié.
3. Vérifier le proxy externe : HTTPS sur le domaine principal, redirection HTTP vers HTTPS et www vers non-www, transmission du Host et de X-Forwarded-Proto par un proxy approuvé. Conserver le traitement des signatures existant.
4. Tester avec curl ou un navigateur les anciennes URL, les 4 langues, les slashs finaux, `/robots.txt`, `/sitemap.xml` et `/img/dalytrac-social.png`. Vérifier 200 sur les destinations canoniques, 301 sur les anciennes URL et 404 sur les articles non publiés. Les nouvelles URL n'ont pas été déployées pendant cette tâche.
5. Copier de vraies vidéos dans `public/videos/` si elles doivent être proposées. Les dix fichiers suivants sont absents localement et sont ignorés par Git :

- `public/videos/DashboardDemo.mp4`
- `public/videos/InvoiceDemo.mp4`
- `public/videos/DevisDemo.mp4`
- `public/videos/Projects_TasksDemo.mp4`
- `public/videos/TimeTrackerDemo.mp4`
- `public/videos/ExpenseDemo.mp4`
- `public/videos/ClientDemo.mp4`
- `public/videos/EmployeesDemo.mp4`
- `public/videos/PlanningDemo.mp4`
- `public/videos/DayDemo.mp4`

Avec compose.yaml actuel, Nginx lit le checkout hôte tandis que PHP lit son image. Les MP4 doivent donc être disponibles **dans les deux systèmes de fichiers** : copier avant la reconstruction de l'image PHP, ou prévoir un montage partagé en lecture seule pour `public/videos` dans PHP et Nginx. Copier seulement sur l'hôte après le build ne suffit pas au contrôle `is_file()` côté PHP. Aucun faux MP4 n'a été créé.

## 4. Fichiers modifiés / ajoutés

Les changements sont limités au SEO public, aux directives d'indexation, au contrôle de publication demandé et aux tests associés. `.gitignore` conserve l'exclusion générale des tests locaux, avec exceptions explicites pour les tests SEO et l'adaptation du test mobile.

- `.gitignore`
- `app/Http/Controllers/BlogController.php`
- `app/Http/Controllers/LangController.php`
- `app/Http/Controllers/SitemapController.php`
- `app/Http/Kernel.php`
- `app/Http/Middleware/Language.php`
- `app/Http/Middleware/SeoPolicy.php`
- `app/Providers/AppServiceProvider.php`
- `app/Support/Seo.php`
- `config/demo.php`
- `config/seo.php`
- `public/img/dalytrac-social.png`
- `public/robots.txt`
- `resources/lang/en/messages.php`
- `resources/lang/es/messages.php`
- `resources/lang/fr/messages.php`
- `resources/lang/nl/messages.php`
- `resources/views/auth/layouts/recovery.blade.php`
- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- `resources/views/auth/verify-email.blade.php`
- `resources/views/blog/index.blade.php`
- `resources/views/blog/show.blade.php`
- `resources/views/components/Home_page/about.blade.php`
- `resources/views/components/Home_page/contact.blade.php`
- `resources/views/components/Home_page/demo.blade.php`
- `resources/views/components/Home_page/faq.blade.php`
- `resources/views/components/json-ld.blade.php`
- `resources/views/components/public/footer.blade.php`
- `resources/views/components/public/header.blade.php`
- `resources/views/components/seo.blade.php`
- `resources/views/home/index.blade.php`
- `resources/views/legal/cgu.blade.php`
- `resources/views/legal/cgv.blade.php`
- `resources/views/legal/confidentialite.blade.php`
- `resources/views/legal/cookies.blade.php`
- `resources/views/legal/mentions.blade.php`
- `resources/views/manager/auth/login.blade.php`
- `resources/views/pricing/index.blade.php`
- `resources/views/product/index.blade.php`
- `routes/seo.php`
- `routes/web.php`
- `scripts/generate-social-image.php`
- `tests/Feature/ExampleTest.php`
- `tests/Feature/HomeMobilePageTest.php`
- `tests/Feature/PublicSeoTest.php`

- `docs/seo-corrections-2026-10-23.md` (ce rapport)

## 5. Tests exécutés et résultats

- Avant modifications : `php artisan test --compact` → **186 réussis, 15 échoués**, 2035 assertions.
- Suite complète après corrections : **192 réussis, 15 échoués**, 5976 assertions. Les 15 échecs restants correspondent aux groupes déjà en échec avant les modifications. Les deux tests existants liés à l'accueil ont été adaptés aux nouvelles URL et aux liens de langue directs.
- Après les dernières améliorations : suite ciblée SEO + accueil mobile + proxy HTTPS + vérification de compte → **27 tests réussis, 4264 assertions** ; dernier passage de PublicSeoTest après validation des paramètres page invalides → **6 tests réussis, 3939 assertions**.
- Les tests traversent le noyau HTTP Laravel et analysent le HTML réellement rendu avec DOMXPath : 52 variantes publiques en 200, langue imposée par l'URL malgré une session contraire, un seul titre/description/H1, unicité des métadonnées entre pages, canonical, alternates, OG/Twitter, JSON-LD décodable, fichiers CSS/images locaux et liens internes résolus par le routeur.
- Sitemap XML parsé ; 52 pages + 11 articles de test, toutes les destinations demandées en 200, articles futurs/brouillons/non datés exclus et en 404. Pagination vérifiée avec tracking et paramètre invalide. Pages utilitaires et protection du dashboard vérifiées.
- Sources vidéo et données JSON de la playlist contrôlées ; images propres d'articles existantes sélectionnées, images inexistantes ignorées.
- Redirection origine + slash testée ; absence de réécriture des URL techniques signées vérifiée. Les tests de signatures et de proxy existants passent.
- Syntaxe PHP des fichiers modifiés : OK. Compilation de toutes les vues Blade (`php artisan view:cache`) : OK.
- `npm run build` : OK, 54 modules. Première tentative bloquée par les permissions des dossiers parents dans le bac à sable ; nouvelle exécution autorisée hors bac à sable réussie. Avertissements non bloquants sur l'ancienneté des données Browserslist/Baseline, sans mise à jour de dépendances hors périmètre.
- Ces contrôles sont locaux ; ils ne constituent ni un crawl du serveur déployé ni une certification Google de l'indexation.

Échecs restants de la suite complète :

- `Tests\Feature\Auth\PasswordResetTest::test_reset_password_link_can_be_requested`
- `Tests\Feature\Auth\PasswordResetTest::test_reset_password_screen_can_be_rendered`
- `Tests\Feature\Auth\PasswordResetTest::test_password_can_be_reset_with_valid_token`
- `Tests\Feature\Auth\RegistrationTest::test_new_users_can_register`
- `Tests\Feature\InvoiceNumberingTest::test_first_invoice_uses_exact_start_and_companies_have_independent_sequences`
- `Tests\Feature\InvoiceNumberingTest::test_sequence_uses_numeric_order_and_continues_across_years`
- `Tests\Feature\InvoiceNumberingTest::test_period_preview_and_pdf_show_exact_number_and_hide_missing_details`
- `Tests\Feature\OnboardingStep1Test::test_update_infos_returns_404_for_unknown_company`
- `Tests\Feature\ProfileTest::test_profile_page_is_displayed`
- `Tests\Feature\ProfileTest::test_profile_information_can_be_updated`
- `Tests\Feature\ProfileTest::test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged`
- `Tests\Feature\ProfileTest::test_user_can_delete_their_account`
- `Tests\Feature\ProfileTest::test_correct_password_must_be_provided_to_delete_account`
- `Tests\Feature\TaskCommentToolsTest::test_automatic_processing_rejects_empty_provider_output`
- `Tests\Feature\TasksBoardPresentationTest::test_only_projects_with_open_timers_expand_including_other_employees`

Les échecs concernent les notifications de reset, l'ancien scénario d'inscription, des fixtures de facturation, les permissions profil/onboarding, les outils de commentaires et les timers. Ils ne sont pas corrigés ici afin de ne pas modifier les fonctionnalités métier.

## 6. Points restant à vérifier dans Google Search Console

- Valider la propriété du domaine avec le compte approprié (aucun accès au compte n'a été utilisé).
- Soumettre `https://dalytrac.com/sitemap.xml` après déploiement et vérifier lecture, nombre d'URL découvertes et absence d'erreurs.
- Inspecter des URL canoniques dans chaque langue, Produit, Contact, Blog, un article et une page de pagination réellement remplie : accès autorisé, HTML rendu, canonical déclarée et canonical choisie par Google.
- Vérifier après nouveau crawl la prise en compte des redirections des anciennes URL et l'exclusion des pages utilitaires noindex.
- Contrôler le JSON-LD dans les outils de validation adaptés ; ne pas promettre de résultats enrichis FAQ pour un SaaS.
- Surveiller erreurs 404, ressources bloquées, statistiques d'exploration et Core Web Vitals sur données de terrain. Le sitemap ne garantit pas à lui seul l'indexation.
