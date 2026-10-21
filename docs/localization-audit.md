# DalyTrac localization audit

Supported locales: French (`fr`), English (`en`), Dutch (`nl`), Spanish (`es`).

## Root causes and corrections

- Laravel validation catalogs were absent. Published complete rule catalogs for the installed framework, with localized field names. Rule translations come from [Laravel Lang](https://github.com/Laravel-Lang/lang); its MIT notice is retained in `resources/lang/Laravel-Lang-LICENSE`.
- Locale middleware ran after subscription, binding and CSRF checks. It now runs immediately after the session starts, validates the selected locale, and resolves the manager account on manager pages. The manager language switch updates the manager account even when a normal account is also signed in.
- Page catalogs differed across languages. Completed the catalogs, including dynamic product-page keys and the invoice starting-number helper shown in the reported screenshot.
- Replaced hardcoded labels, titles, tooltips, placeholders, notifications, confirmations, trial/upgrade content, reporting labels and frontend error messages with catalog calls.
- Translated CSV import validation at its source, including row numbers and missing-column parameters. Calendar and client validation use the selected locale.
- Replaced unsafe translated JavaScript string interpolation with Blade's JavaScript-safe encoding, including strings containing HTML. Dutch calendar month/day names now load the Dutch Moment locale.
- Localized standard HTTP/authentication errors. Production JSON exceptions and caught technical exceptions present a localized message, while technical details are retained in server logs. Development exception diagnostics are unchanged.
- Country selectors translate display labels while retaining the existing stored country values. IANA time-zone identifiers, CSV column identifiers, user-entered content, company names and legal/business identifiers remain data.
- Repaired incomplete translation expressions in the legacy super-admin metrics partial and the raw Blade block that prevented employee-sidebar labels from compiling.

## Repeatable checks

Run `php artisan localization:audit` to check literal and dynamically selected catalog keys across source files and all four languages. It fails on missing entries or incompatible substitution parameters; English fallback does not count as a completed translation.

Regression coverage in `tests/Feature/LocalizationTest.php` checks locale precedence, manager account selection and switching, validation fields/rules, HTTP and authentication errors, technical-error presentation, country labels, CSV errors, the invoice helper and safe JavaScript encoding.

## Verification and limits

- Source/catalog audit: 438 PHP/JavaScript source files and 3,897 translation entries, including dynamically selected entries; all four catalogs have matching keys and parameters.
- All 309 compiled Blade templates passed PHP syntax checks.
- Frontend production build passed.
- All 24 JavaScript tests passed.
- Targeted localization, rendered email, profile and manager invoice checks passed.
- The broader PHP suite produced 168 passes and nine failures in legacy password-reset, registration and profile tests. Those tests expect the framework reset notification (the app uses a custom notification), registration with `name` (the app requires `full_name`), or profile access without the required role. Their authentication/permission behavior was not changed for this audit.

This is a source, catalog, compilation and request/render-test audit. An authenticated visual walkthrough of every page was not performed; browser inspection tools were unavailable in this session. User-authored text and third-party content are not automatically translated by the UI catalog system.
