# SportsManagement module migration (Joomla 5/6)

This document tracks the Joomla 5/6 migration of the SportsManagement modules packaged by the component.

## Inventory

`sportsmanagement.xml` currently packages 29 modules. The module gate treats this number and the presence/parseability of every packaged module manifest as an explicit migration inventory.

The package contains one administrator module (`mod_sportsmanagement_quickicon`) and 28 site modules. Additional module directories may still exist in the repository for compatibility/history; only modules listed in the component package manifest are counted as part of this migration inventory.

## Native module architecture

Migrated modules use the Joomla 5/6 module service architecture:

- a module namespace declared in the module XML manifest,
- `services/provider.php` as the module service entry point,
- a namespaced `src/Dispatcher/Dispatcher.php` based on `AbstractModuleDispatcher`,
- namespaced helpers where data preparation is required,
- passive templates that render data prepared by the dispatcher/helper rather than bootstrapping legacy SportsManagement MVC classes themselves.

Legacy entry/helper files are retained in migrated module directories for package compatibility and migration inventory, but the native module service provider is the active module entry point.

## First native module wave

### `mod_sportsmanagement_act_season`

The active-season module now uses a native module provider, dispatcher and helper.

The helper resolves projects, leagues, rounds, countries and federations in one joined read query. This replaces the former per-federation N+1 lookup loop and removes the old `$db->disconnect()` calls. The active template no longer uses `JSMCountries`, `sportsmanagementHelperRoute`, `JSMModelLegacy` or `JLoader::import`.

A small database bridge remains intentionally in the helper solely to preserve the component's optional alternate SportsManagement database selection; it uses the existing `sportsmanagementHelper::getDBConnection()` when available and otherwise falls back to Joomla's `DatabaseInterface` service.

### `mod_sportsmanagement_count_rekord`

The count-record module now has a native provider/dispatcher/helper stack. Match counting is read-only and prepared before rendering; the template is passive and no longer performs the helper/database call itself. No connection `disconnect()` or legacy MVC bootstrap remains in the active native stack.

### Administrator `mod_sportsmanagement_quickicon`

The administrator quick-icon module now uses a native module provider and dispatcher. The active stack uses Joomla `ComponentHelper`, `Route` and `Uri` APIs and no longer contains `JVERSION`/`version_compare` or `JURI` compatibility branches.

## Second native module wave

### `mod_sportsmanagement_eventsranking`

The events-ranking module now uses a native provider, dispatcher and read-only helper. Its active path no longer loads the old static `sportsmanagementModelProject` / EventsRanking model state or the legacy route/country helpers.

The helper resolves the selected project/event types and ranking rows directly from SportsManagement tables. Ranking data is prepared independently for each selected event type, including division/team/match filters, DART event ordering, player/team display names, images and Joomla routes. The template only renders the prepared data and contains no database/model/helper calls.

The optional alternate SportsManagement database remains supported through the same narrow database bridge used by the other native module helpers.

### `mod_sportsmanagement_sports_type_statistics`

The sports-type statistics module now uses a native provider, dispatcher and helper instead of the legacy administrator `sportsmanagementModelSportsTypes` model.

Counts are computed with explicit Joomla database queries. Project-dependent entities are filtered through the selected sport type while the historically global counters (for example leagues, seasons, playgrounds, clubs and persons) retain their effective legacy semantics. The active template is passive and receives only the selected sport type plus prepared counters.

## Modules requiring special handling

The remaining module migration is deliberately split by behavior rather than migrated as a bulk mechanical rename.

### Ranking module with render-time writer

`mod_sportsmanagement_ranking` is not yet part of the read-only native waves. When `ishd_update` is enabled, its legacy module entry can invoke the inline-hockey model and refresh match data during module rendering. That write/update behavior must first be separated from ranking display before the module can be declared a native passive module.

### Random-player module

`mod_sportsmanagement_randomplayer` is read-oriented, but its current helper still depends on several static legacy site models and explicitly disconnects the database connection. Its migration should extract the required player/team/project context into a native read helper rather than wrapping the existing static model chain.

### Render-time writers

`mod_sportsmanagement_new_project` is not treated as a read-only module. Its legacy helper can create Joomla content records while the module is rendered. That behavior must be split into an explicit writer/action before the display path can be declared native and read-only.

### Calendar, maps and external integrations

Calendar/Google-calendar, project-map and related modules need a separate asset/API review. Their migration must remove obsolete Joomla asset/bootstrap assumptions without silently introducing render-time external network work.

### AJAX/navigation/live modules

AJAX navigation, live ticker and similar modules need their endpoint, token and asset loading contracts reviewed together with their PHP module entry points.

### Training/data-coupled modules

Training and other data-coupled modules require explicit read/write classification before migration so that writes are not hidden in render helpers.

## Validation

`.github/workflows/joomla5-6-modules.yml` is the module migration gate. It:

- inventories the 29 modules packaged by `sportsmanagement.xml`,
- requires each packaged module directory and XML manifest to exist and parse,
- lints PHP across `modules/` and `admin/modules/`,
- validates the namespace/service/src contract for both native module waves,
- rejects legacy MVC/bootstrap markers, database disconnects and old `JVERSION`/`JURI` compatibility paths from the active native files,
- validates key data/preparation contracts for Act Season, Count Rekord, Quickicon, Events Ranking and Sports Type Statistics,
- requires the migrated templates to remain passive and free of model/helper/database calls,
- reports remaining legacy module markers outside the native waves without failing the build solely because an unmigrated module still contains them.

Static module gates are not a substitute for installing and rendering the modules on real Joomla 5.4 and Joomla 6.1 environments.

## Next module priorities

1. Split `mod_sportsmanagement_ranking` display from its optional inline-hockey update writer and then migrate the read-only ranking path.
2. Extract `mod_sportsmanagement_randomplayer` from its static project/person/player model chain and remove its database disconnect behavior.
3. Split `mod_sportsmanagement_new_project` display from its Joomla-content writer before enabling a native display stack.
4. Migrate calendar/map/external-integration modules with explicit WebAssetManager/API boundaries.
5. Migrate AJAX/navigation/live modules together with their request/token endpoints.
6. Classify and migrate training/data-coupled modules without allowing render-time writes.
7. Remove retained legacy module entry/helper files only after no installed/update path requires them.
8. Add Joomla 5.4 and Joomla 6.1 module install/render smoke tests.
