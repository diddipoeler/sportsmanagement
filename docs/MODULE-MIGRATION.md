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

## Modules requiring special handling

The remaining module migration is deliberately split by behavior rather than migrated as a bulk mechanical rename.

### Read-only/list modules

These should be migrated first where their active behavior is data retrieval plus rendering. Candidate groups include ranking/statistics, random-player, event-ranking, team-player and other counter/list modules. Each should receive the same provider/dispatcher/helper separation and should not keep legacy MVC bootstrap code in the active path.

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
- validates the namespace/service/src contract for the first native module wave,
- rejects legacy MVC/bootstrap markers, database disconnects and old `JVERSION`/`JURI` compatibility paths from the active first-wave files,
- validates key data/preparation contracts for Act Season, Count Rekord and Quickicon,
- reports remaining legacy module markers outside the first native wave without failing the build solely because an unmigrated module still contains them.

Static module gates are not a substitute for installing and rendering the modules on real Joomla 5.4 and Joomla 6.1 environments.

## Next module priorities

1. Migrate the remaining low-risk read-only/list/statistics modules in coherent waves.
2. Split `mod_sportsmanagement_new_project` display from its Joomla-content writer before enabling a native display stack.
3. Migrate calendar/map/external-integration modules with explicit WebAssetManager/API boundaries.
4. Migrate AJAX/navigation/live modules together with their request/token endpoints.
5. Classify and migrate training/data-coupled modules without allowing render-time writes.
6. Remove retained legacy module entry/helper files only after no installed/update path requires them.
7. Add Joomla 5.4 and Joomla 6.1 module install/render smoke tests.
