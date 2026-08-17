# SportsManagement migration (Joomla 5/6)

This document tracks the transition from the legacy Joomla naming/loading scheme to the Joomla 5/6 component service provider, dispatcher and namespaced MVC stack.

## Current architecture

The component manifest installs the namespace `Diddipoeler\Component\SportsManagement`, `site/src`, `admin/src`, `admin/services`, `admin/tmpl` and `admin/forms`.

Modern entry controllers exist in both areas:

- `site/src/Controller/DisplayController.php`
- `admin/src/Controller/DisplayController.php`

The component service provider uses `SportsManagementMVCFactory`, a component-specific subclass of Joomla's `MVCFactory`. Native Joomla 5/6 classes are always preferred. When a namespaced model or view has not yet been rewritten, the factory can load the existing SportsManagement class, expose it under the namespace Joomla expects and retain the legacy template path.

This transitional factory also restores `sportsmanagementHelper::getDBConnection()` after Joomla injects its normal database connection, preserving installations that keep SportsManagement data in a separate database.

## Dispatcher cutover

Both component dispatchers send normal display requests through Joomla's modern component dispatcher when a supported view exists. Legacy controller tasks and special layouts continue through the original entry points unless an entity group has been explicitly promoted to native CRUD.

Administrator access is checked with the current Joomla identity and `core.manage`.

### Administrator display coverage

The legacy tree contains 111 administrator directories with `view.html.php`. Their class names follow the `sportsmanagementView<ViewName>` convention used by `SportsManagementMVCFactory`, so their normal default HTML display routes can be resolved through the Joomla 5/6 dispatcher.

### Site coverage

The site tree contains 69 directly dispatchable HTML views, four raw views and one PDF view. The component dispatcher and MVC factory can resolve those formats while the underlying implementation is migrated incrementally.

Six additional site view directories are partial/helper areas without their own `view.html.php` (`flash`, `map`, `overall`, `predictionflash`, `predictionoverall`, `tree`). They are not independent HTML dispatcher targets.

## Fully native administrator display stacks

The following display stacks no longer depend on their legacy view/model implementation:

- `agegroups`
- `close`
- `clubs`
- `currentseasons`
- `divisions`

Each has a namespaced model/view and a template under `admin/tmpl`.

`SportsManagementListModel` is the native base for migrated list models. It preserves the optional SportsManagement-specific database connection and explicitly registers `administrator/components/com_sportsmanagement/forms` before Joomla resolves SearchTools filter forms.

## Fully native administrator CRUD groups

The complete write-capable Joomla 5/6 entity groups are now:

- `eventtype` / `eventtypes`
- `extrafield` / `extrafields`
- `season` / `seasons` for standard CRUD
- `sportstype` / `sportstypes` for standard CRUD

For these groups the modern stack contains:

- singular and plural controllers under `admin/src/Controller`
- native singular `AdminModel` implementations
- native plural `ListModel` implementations
- namespaced tables under `admin/src/Table`
- native list and edit views under `admin/src/View`
- native list/edit templates under `admin/tmpl`
- form and filter XML under `admin/forms`

The standard CRUD task set includes add/edit/apply/save/save-and-new/save-as-copy, publish/unpublish, archive/trash, check-in and ordering actions.

### Safe partial cutover for seasons

The normal season list and edit/save flow is native. The existing season assignment workflows remain deliberately legacy-backed:

- `season.saveshortpersons`
- `season.saveshortteams`
- non-default assignment layouts such as team/person assignment

The native season list keeps links into those assignment routes; because the layouts are non-default they are still delegated to the legacy dispatcher.

### Safe partial cutover for sport types

Normal sport-type CRUD is native. The form no longer depends on the legacy `extensionradiobutton` field: `sportsart` and `eventtime` use Joomla core radio switchers under `admin/forms/sportstype.xml`.

Import and export remain legacy-backed:

- `sportstype.import`
- `sportstype.export`

The native toolbar still exposes those actions, but the dispatcher does not include them in the standard CRUD allowlist, so they fall through to the existing implementation.

### `SportsManagementAdminModel`

`admin/src/Model/SportsManagementAdminModel.php` is the shared native form-model base for these and future migrated CRUD groups. It extends Joomla's `AdminModel` and centralises:

- the component-specific SportsManagement database connection
- loading forms from `admin/forms`
- edit-session form data
- modified/modified-by metadata
- check-in metadata reset on save
- save-as-copy preparation
- the existing SportsManagement action-log hook without making logging failure abort a successful save
- hooks for entity-specific pre/post-save behaviour

`EventtypeModel` adds its media/icon field configuration. `ExtrafieldModel`, `SeasonModel` and `SportstypeModel` use the common native save path; `SportstypeModel` also retains a simple `getSportstype()` lookup helper.

## Transitional namespaced adapters

Administrator adapters still backed by legacy business logic include, among others:

- `clubnames`
- `leagues`
- `playgrounds`
- `positions`
- `rosterpositions`
- `teams`

Site adapters include:

- `about`
- `clubs`
- `predictionrules`
- `referees`
- `teams`

`eventtypes`, `extrafields`, `seasons` and `sportstypes` have been removed from this transitional group for their standard CRUD paths.

## Controller and table migration

`SportsManagementAdminController` replaces the shared legacy list-controller base for migrated controllers. `SportsManagementFormController` replaces the shared legacy form-controller path while preserving SportsManagement save and redirect behaviour.

The native table stack now includes:

- `SportsManagementTable`
- `ClubTable`
- `ClubnameTable`
- `AgegroupTable`
- `DivisionTable`
- `EventtypeTable`
- `ExtrafieldTable`
- `SeasonTable`
- `SportstypeTable`

Club, club-name, age-group and division form models still reuse parts of the previous save/business implementation. Event type, extra field, season and sport type use the native `SportsManagementAdminModel` for their standard form path.

## Why leagues remain on the legacy write fallback

The league entity has a wider dependency graph than the completed CRUD groups: historical logo handling, association and age-group form dependencies, extra-field integration, inline `saveshort`, import and export are coupled to the existing controller/model implementation.

This is deliberate: the dispatcher only promotes a write route after its full controller/model/table/form dependency set has a safe replacement or a narrowly defined fallback boundary.

## Legacy boot bridges

The transition remains isolated in:

- `admin/src/Legacy/LegacyBootstrap.php`
- `site/src/Legacy/LegacyBootstrap.php`

They load old libraries/helpers only for MVC objects that are still legacy-backed. As each entity is migrated natively, its dependency on these bridges disappears without changing its public route.

## Validation

The branch workflow `.github/workflows/joomla5-6-lint.yml` validates:

- syntax of migrated PHP files
- the component namespace and required manifest folders, including `admin/forms`
- the fully native display smoke routes
- the complete legacy view inventory used by the transitional MVC fallback
- complete native CRUD file sets for event types, extra fields, seasons and sport types
- XML validity of all native CRUD form/filter definitions
- absence of `JSMModelAdmin`, `JSMModelList`, explicit legacy bootstrap calls and old legacy view inheritance from the native CRUD classes
- presence of the safe standard CRUD dispatcher allowlist
- absence of season assignment and sport-type import/export tasks from that allowlist
- creation of an installable development ZIP after the gates pass

The workflow uses the PHP already present on the GitHub Ubuntu 24.04 runner so transient rate limits while downloading a PHP setup action cannot make the migration gate red before the repository is checked out.

## Remaining work

The next migration priorities are:

1. Migrate leagues as a coherent block including logo history, association/age-group dependencies, extra fields, `saveshort`, import and export boundaries.
2. Migrate playgrounds, positions/roster positions and teams onto the native CRUD infrastructure.
3. Separate `cpanel` display from database initialization and maintenance side effects.
4. Migrate database/import/export tooling.
5. Migrate AJAX, JSON and other non-HTML controller endpoints.
6. Remove the legacy bootstrap bridges and legacy class aliases once no route requires them.
7. Add installation and route smoke tests on real Joomla 5.4 and Joomla 6.1 environments.

Until the final runtime gate is green, packages from this branch should be treated as development/test builds rather than production releases.
