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

## Modules requiring special handling

### Render-time writers

`mod_sportsmanagement_new_project` is not treated as a read-only module. Its legacy helper can create Joomla content records while the module is rendered. That display/write behavior must be separated before its native display stack is enabled.

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

Static gates are not a substitute for installing and rendering the modules on real Joomla 5.4 and Joomla 6.1 environments.

## Next module priorities

1. Split `mod_sportsmanagement_new_project` display from its Joomla-content writer.
2. Migrate calendar/map/external-integration modules with explicit WebAssetManager/API boundaries.
3. Migrate AJAX/navigation/live modules together with their request/token endpoints.
4. Classify and migrate training/data-coupled modules without allowing render-time writes.
5. Reuse the shared `RankingEngine` in remaining legacy site ranking consumers before removing `site/helpers/ranking.php`.
6. Finish the remaining `projectteams` / `teamplayers` special relation actions on the native relation service.
7. Remove retained legacy module entry/helper/default-template files only after no installed/update path requires them.
8. Add Joomla 5.4 and Joomla 6.1 module install/render smoke tests.
