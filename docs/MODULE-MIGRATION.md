# SportsManagement module migration (Joomla 5/6)

This document tracks the Joomla 5/6 migration of the SportsManagement modules packaged by the component.

## Inventory

`sportsmanagement.xml` currently packages 29 modules: one administrator module (`mod_sportsmanagement_quickicon`) and 28 site modules. The module gate treats that inventory, every module manifest and the complete module PHP tree as an explicit migration boundary.

## Native module architecture

Migrated modules use the Joomla 5/6 module service architecture:

- a module namespace declared in the module XML manifest,
- `services/provider.php` as the module service entry point,
- a namespaced `src/Dispatcher/Dispatcher.php` based on `AbstractModuleDispatcher`,
- namespaced helpers where data preparation is required,
- passive templates that render prepared data rather than bootstrapping legacy SportsManagement MVC classes.

Legacy entry/helper/default-template files may remain in migrated module directories as compatibility and migration inventory. Where a migrated module needs a clean presentation boundary, its dispatcher forces the dedicated `native` layout so the old default template is not part of the active path.

## Native module waves

### Wave 1

#### `mod_sportsmanagement_act_season`

The active-season module uses a native provider, dispatcher and helper. Projects, leagues, rounds, countries and federations are resolved in a joined read query, replacing the former federation N+1 loop and removing the old `$db->disconnect()` calls from the active path.

#### `mod_sportsmanagement_count_rekord`

The count-record module uses a native provider/dispatcher/helper stack. Match counting is prepared before rendering and the template is passive.

#### Administrator `mod_sportsmanagement_quickicon`

The administrator quick-icon module uses a native module provider and dispatcher. Its active stack uses Joomla `ComponentHelper`, `Route` and `Uri` APIs and no longer contains `JVERSION`/`version_compare` or `JURI` compatibility branches.

### Wave 2

#### `mod_sportsmanagement_eventsranking`

The events-ranking module uses a native provider, dispatcher and read-only helper. The active path no longer loads the old static Project/EventsRanking model state. Project/event ranking rows, filters, names, images and routes are prepared before rendering; the template is passive.

#### `mod_sportsmanagement_sports_type_statistics`

The sports-type statistics module uses a native provider, dispatcher and helper instead of the legacy administrator SportsTypes model. Counters are produced by explicit database reads and the template receives only prepared values.

### Wave 3

#### `mod_sportsmanagement_randomplayer`

The random-player module is now a native read-only module. Its active helper no longer chains through the static legacy Project, Person and Player models and no longer disconnects the database connection.

The helper selects a valid published season-team/person relation server-side, chooses a random candidate in PHP, reloads the complete player/project/team context with bounded joins and prepares display name, image/flag URLs and Joomla routes. The dispatcher forces `layout=native`; the native template contains no legacy SportsManagement model, country or route helper calls.

#### `mod_sportsmanagement_ranking`

The ranking module now uses the shared native ranking service for its display calculation. The active display helper no longer references `JSMRanking` or `sportsmanagementModelProject`.

The shared service is split into four read-only responsibilities:

- `RankingEngine` is the small facade used by modules and future native site views,
- `RankingDataLoader` reads project metadata, ranking template configuration, project-team/division/final-table state and counted published matches,
- `RankingCalculator` performs match aggregation and ranking in memory without database or request access,
- `RankingRow` carries the ranking values and compatibility-style metric methods needed by presentation code.

The native calculator covers the generic legacy ranking contract including configurable points, regular/add-time/penalty result types, decisions, bonus points, goals/results, legs, matchpoints, sets, games, start/penalty/final values, division filtering, final-table ranking and head-to-head criteria. It also retains the generic Soccer, Faustball and small-bore-rifle branches that existed inside the old base ranking helper.

The old `site/helpers/ranking.php` remains installed because other legacy site views still depend on it. It is no longer part of the active `mod_sportsmanagement_ranking` display path and must not be removed until those remaining consumers are migrated.

The former `ishd_update` render-time writer remains separated from display. Inline-hockey refresh is an explicit `com_ajax` action exposed only when the current user can manage SportsManagement. The refresh action:

- accepts POST only through Joomla's CSRF token check,
- requires `core.manage` for `com_sportsmanagement`,
- accepts only a module ID from the request and reloads project/update settings from the published module record,
- verifies that `ishd_update` is enabled in that stored module configuration,
- refuses the compatibility importer for an alternate SportsManagement database because the legacy inline-hockey importer writes through Joomla's own database connection,
- checks for stale matches server-side before invoking the compatibility importer.

No inline-hockey import or other write/network action is executed merely because the ranking module is rendered.

### Wave 4

#### `mod_sportsmanagement_new_project`

The New Project module now has a native provider, dispatcher, helper and dedicated `native` layout. Its display path is read-only: it loads today's published new/updated projects, league/current-round context and country flags, then prepares links and image fallbacks before rendering.

The legacy helper used to create Joomla articles while the module was merely rendered, directly inserting into `#__content` and `#__content_frontpage`, hard-coding `created_by = 62` and manually triggering an AutoTweet-related content event. None of those write paths is present in the active render path.

Article creation is now an explicit `com_ajax` action. The browser submits only the module ID and Joomla CSRF token. The writer reloads the published module and its `new_project_article`/category settings server-side, validates the configured published `com_content` category, requires both `core.manage` for SportsManagement and `core.create` for that content category, reloads the eligible projects server-side and deduplicates them by `xreference`.

Articles are created through Joomla's administrator `com_content` Article MVC model rather than direct table inserts. The current authenticated Joomla identity becomes the author, and Joomla's content model owns its normal save/event/featured handling. Legacy numeric project references are recognised for duplicate detection while new records use `sportsmanagement-project:<id>` as an explicit reference namespace.

## Modules requiring special handling

### Calendar, maps and external integrations

Calendar/Google-calendar, project-map and related modules require an asset/API review. Their migration must remove obsolete Joomla asset/bootstrap assumptions without introducing render-time external network work.

### AJAX/navigation/live modules

AJAX navigation, live ticker and similar modules need their endpoint, token and asset-loading contracts reviewed together with their module entry points.

### Training/data-coupled modules

Training and other data-coupled modules require explicit read/write classification before migration so writes are not hidden in render helpers.

## Validation

`.github/workflows/joomla5-6-modules.yml` remains the broad module migration gate. It inventories the 29 packaged modules, validates every module manifest and lints PHP across the module tree.

`.github/workflows/joomla5-6-ranking-engine.yml` specifically gates the shared native ranking engine. It:

- lints the four ranking services plus the active Ranking module stack,
- rejects `JSMRanking`, `sportsmanagementModelProject`, legacy MVC/bootstrap, request-global and database-write markers from the native ranking services,
- requires the active module helper to use `RankingEngine`,
- keeps the display section free of importer/write/network work,
- requires the explicit inline-hockey refresh to retain CSRF, authorization, server-side module configuration and database-boundary checks,
- executes semantic ranking tests for normal win/draw aggregation, three-point scoring, overtime counters and head-to-head ordering.

`.github/workflows/joomla5-6-module-new-project.yml` gates the New Project display/writer split. It requires the native namespace/services/src contract, keeps the render section free of write/legacy-MVC markers and requires the article writer to use POST CSRF, server-side module configuration, SportsManagement and content-category ACLs plus the Joomla `com_content` Article model. Direct content/frontpage inserts, manual content-event triggering and the historical hard-coded author ID are rejected.

Static gates are not a substitute for installing and rendering the modules on real Joomla 5.4 and Joomla 6.1 environments.

## Next module priorities

1. Migrate calendar/map/external-integration modules with explicit WebAssetManager/API boundaries.
2. Migrate AJAX/navigation/live modules together with their request/token endpoints.
3. Classify and migrate training/data-coupled modules without allowing render-time writes.
4. Reuse the shared `RankingEngine` in remaining legacy site ranking consumers before removing `site/helpers/ranking.php`.
5. Finish the remaining `projectteams` / `teamplayers` special relation actions on the native relation service.
6. Remove retained legacy module entry/helper/default-template files only after no installed/update path requires them.
7. Add Joomla 5.4 and Joomla 6.1 module install/render smoke tests.

## Safe rendering of database quotations

The native `mod_sportsmanagement_rquotes` layouts now share `QuoteText::render()` for database-backed quotes. The output retains line breaks and encoded text while treating all other stored HTML markup as plain text, preventing imported quote tags and attributes from executing in Joomla 5/6 pages. The `joomla56_rquotes_output_encoding.php` regression test covers entity encoding, newline preservation and script/attribute injection on PHP 8.3/8.4. Existing quote records are not modified.

## Shared native/legacy quote picture validation

Retained template overrides can still call `modRquotesHelper::renderRquote()`. That compatibility path now uses `RquotesHelper::pictureServer()` and `pictureUrl()`, the same validated remote-server and picture-path resolution as the active native module. Existing prepared image URLs are checked again; invalid credentials, schemes, traversal paths or unsafe external media settings are rejected. `joomla56_rquotes_media_urls.php` exercises those rules in PHP 8.3 and 8.4.

## Packaged quote text directory (8 November 2026)

The native quote text reader now looks first in `modules/mod_sportsmanagement_rquotes/mod_sportsmanagement_rquotes/`, matching the shipped `rquotes.txt` file and the module XML filelist path. A guarded fallback to the module root preserves already configured custom text files from older installations. The backend text-file picker uses the packaged directory; its basename filter remains aligned with the frontend reader. Both locations reject symlinked TXT files, invalid filenames and oversized files. The standalone `joomla56_rquotes_text_file_paths.php` regression test validates folder precedence, the default package asset, legacy fallback and malicious names.

## Joomla 5/6 native GCalendar query failure isolation (8 November 2026)

`GcalendarHelper::getCalendars()` now returns an empty calendar list when the Joomla database cannot prepare or execute a calendar read. Driver exception details are kept in Joomla's component error log and are not exposed in module output. Existing access-level conditions, malformed-ID refusal and the 50-calendar bound remain intact. The `joomla56_gcalendar_options_rows.php` test adds both query-setup and execution failure cases, using the existing PHP 8.3/8.4 calendar-selection workflow.

## Locale-safe Google Calendar weekday headings (8 November 2026)

The native GCalendar module no longer assumes that the optional PHP mbstring extension is available. `GcalendarHelper::shortWeekdayLabel()` uses `mb_substr()` where present and a Unicode-aware PCRE fallback elsewhere, avoiding split multibyte characters in German, French, Japanese and Chinese weekday labels. The helper has detailed PHPDoc and `joomla56_gcalendar_weekday_utf8.php` runs within the existing calendar-selection PHP 8.3/8.4 regression matrix.

## Navigation menu database selector safety (8 November 2026)

The native Navigation Menu module now validates `cfg_which_database` before selecting the SportsManagement database. Only integer 0/1 and zero-padded decimal equivalents are accepted; malformed arrays, booleans, negative values and partial numeric strings cannot silently redirect the module to a different configured connection. The native dispatcher skips rendering when the helper returns no safe navigation payload, and a PHP 8.3/8.4 test guards both the selector and dispatch contract.

## Regression CI maintenance after native navigation selector migration

The general site/admin/modules batch-264 contract now permits Joomla 5/6's supported `Factory::getContainer()->get(DatabaseInterface::class)` lookup in existing module dispatchers, while still rejecting removed `Factory::getDbo()` APIs and protected application container access. The New Project article-reference test also had a double namespace separator before `ReflectionMethod`, which is now valid PHP syntax. Both corrections address failures revealed by the broader branch CI run; no production business logic was changed in those two files.

## Project Map HTML output (14 November 2026)

Federation names from the database are now translated and escaped before being placed into map region labels, matching the existing country and league popup encoding. The project-map regression verifies the escaped HTML and safely encoded JavaScript configuration. Bundled module file headers and descriptive method PHPDoc remain mandatory.

## Ranking multi-instance CSS registration and updated CI contracts (14 November 2026)

The Ranking dispatcher now activates the same WebAssetManager stylesheet across multiple module instances without registering the asset twice. Two older Ranking CI workflows still expected pre-migration local Joomla application/container variables and a removed SiteApplication type check; their contracts now verify the current typed service resolver, site-client check and repeatable CSS regression. No ranking calculation or query behavior was changed in those CI-only corrections.

## Reusable Joomla WebAssets in Matches and Matches Slider modules (14 November 2026)

- The native Matches dispatcher now registers the module-wide native CSS only once per shared HTML document. Optional template CSS is still activated only when the validated selected template has a packaged file and is registered under a distinct name for each template. Repeated renders do not redeclare either asset.
- The native Matches Slider dispatcher now registers its slider CSS and deferred JavaScript once per document and activates both on every render, preserving the original `version=auto` and `defer` attributes. Both dispatchers skip non-HTML documents as before.
- An isolated PHP regression uses one Joomla-like WebAssetManager for both modules and verifies two repeat renders, separate layout CSS files, missing optional CSS and duplicate-asset rejection. It is wired into both module-specific workflows and the all-PHP-metadata audit; updated slider CI assertions check native register/use semantics rather than the removed `registerAndUse` calls. New helper methods have descriptive PHPDoc and file headers retain all four required metadata tags.

### Matches modules: historical regression fixtures (14 November 2026)

The existing Matches media regression now composes NativeQueryTrait, NativeFormatTrait and NativeLinkTrait together like the production helper, so the static bounding methods used by team/status formatting exist during isolated PHP tests. The Matches Slider media regression now expects invalid configured external image hosts (embedded credentials, query strings, fragments, malformed schemes) to produce an empty picture base, consistent with the production privacy guard; such images must not silently fall back to local Joomla image paths. No asset behavior or production media restrictions were relaxed. The modified test stubs include descriptive PHPDoc.
