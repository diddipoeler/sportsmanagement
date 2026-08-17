# SportsManagement migration (Joomla 5/6)

This document tracks the transition from the legacy Joomla naming/loading scheme to the Joomla 5/6 component service provider, dispatcher and namespaced MVC stack.

## Current architecture

The component manifest installs the namespace `Diddipoeler\Component\SportsManagement`, `site/src`, `admin/src`, `admin/services`, `admin/tmpl` and `admin/forms`.

Modern entry controllers exist in both areas:

- `site/src/Controller/DisplayController.php`
- `admin/src/Controller/DisplayController.php`

The component service provider uses `SportsManagementMVCFactory`, a component-specific subclass of Joomla's `MVCFactory`. Native Joomla 5/6 classes are always preferred. When a namespaced model or view has not yet been rewritten, the factory can still expose the existing SportsManagement implementation through the class name Joomla expects and retain its legacy template path.

The transitional factory and native model bases restore `sportsmanagementHelper::getDBConnection()` after Joomla injects its normal database connection. This preserves installations that store SportsManagement data in a separate database.

## Dispatcher cutover

Both component dispatchers send supported display routes through Joomla's component dispatcher. Legacy controller tasks and special layouts continue through the original entry points unless a group has an explicit safe native route.

Administrator access is checked with the current Joomla identity and `core.manage`.

### Administrator display coverage

The legacy tree contains 111 administrator directories with `view.html.php`. Their class names follow the convention used by `SportsManagementMVCFactory`, so normal default HTML display routes can be resolved through the Joomla 5/6 dispatcher while their implementation is migrated incrementally.

### Site coverage

The site tree contains 69 directly dispatchable HTML views, four raw views and one PDF view. Six additional site view directories are partial/helper areas without their own `view.html.php` (`flash`, `map`, `overall`, `predictionflash`, `predictionoverall`, `tree`).

## Fully native administrator display stacks

The following display stacks no longer depend on their legacy view/model implementation:

- `agegroups`
- `close`
- `clubs`
- `currentseasons`
- `divisions`

`SportsManagementListModel` is the shared native list-model base. It preserves the component database connection and registers `administrator/components/com_sportsmanagement/forms` before SearchTools resolves filter forms.

## Fully native administrator CRUD groups

The complete write-capable Joomla 5/6 entity groups are now:

- `clubname` / `clubnames`
- `eventtype` / `eventtypes`
- `extrafield` / `extrafields`
- `season` / `seasons` for standard CRUD
- `sportstype` / `sportstypes` for standard CRUD

For these groups the modern stack contains singular/plural controllers, native `AdminModel`/`ListModel` classes, namespaced tables, native list/edit views, native templates and form/filter XML under `admin/forms`.

The standard CRUD task set covers add/edit/apply/save/save-and-new/save-as-copy, publish/unpublish, archive/trash, check-in and ordering actions.

### Club names

`ClubnameModel` now uses `SportsManagementAdminModel` instead of the transitional legacy model adapter. `ClubnamesModel` is a native list model with search, publication and country filters. The form uses the new namespaced `CountryField`.

`clubnames.import` remains legacy-backed because import is intentionally not part of the standard CRUD allowlist.

### Seasons

Normal list/edit/save is native. Assignment workflows remain deliberately legacy-backed:

- `season.saveshortpersons`
- `season.saveshortteams`
- non-default team/person assignment layouts

### Sport types

Normal CRUD is native. `sportsart` and `eventtime` use Joomla core radio switchers instead of the old `extensionradiobutton` field. `sportstype.import` and `sportstype.export` remain legacy-backed.

## Native administrator form fields

Reusable Joomla 5/6 field classes now exist under `admin/src/Field`:

- `CountryField`
- `SportstypeField`
- `AgegroupField`
- `AssociationField`
- `FederationField`
- `LeaguelevelField`

They share `SportsManagementListField`, query the configured SportsManagement database connection directly and are referenced from native XML forms through `addfieldprefix="Diddipoeler\Component\SportsManagement\Administrator\Field"`.

This removes a growing dependency on the old global classes under `admin/models/fields` for migrated forms and SearchTools filters.

## League: native list and inline updates

The league **list path** is now native:

- `LeagueTable`
- `LeagueModel` for narrowly scoped inline updates
- `LeaguesModel`
- `LeaguesController`
- `View/Leagues/HtmlView`
- `admin/tmpl/leagues/default.php`
- `admin/forms/filter_leagues.xml`

The native list preserves search, country, association, federation, age-group, league-level, champions-complete and publication filtering. It also retains the old `com_sportsmanagement.leaguenation` and `com_sportsmanagement.leaguefederation` user-state values for compatibility.

`leagues.saveshort` now runs natively. It loads each selected league before updating only the inline fields (`country`, `associations`, `agegroup_id`, `published_act_season`, `champions_complete`) and stores the complete row.

Normal plural list actions such as publish/unpublish, trash, check-in and ordering can use the native Joomla controller/model/table path.

The **singular league edit/save path remains legacy-backed**. This is intentional because the edit workflow still combines:

- historical logo handling
- dynamic SportsManagement ExtraFields
- dependent association and age-group selections
- import/export boundaries

The dispatcher therefore does not place the singular `league` controller in the safe standard CRUD set. `league.add`, `league.edit`, `league.save`, import and export continue to fall through to the legacy entry point.

## Positions: native list and inline parent updates

The position **list path** is now native:

- `PositionTable`
- `PositionModel` for parent-position inline updates
- `PositionsModel`
- `PositionsController`
- `View/Positions/HtmlView`
- `admin/tmpl/positions/default.php`
- `admin/forms/filter_positions.xml`

The list supports search, publication, sport-type and person-type filtering, joins the parent position and sport type, and reports the number of assigned event types and statistics without loading additional models per row.

`positions.saveshort` runs natively and updates only the parent position after loading the complete record. A position cannot be assigned to itself as parent.

Plural state/ordering actions use the native list controller. The **singular position edit/save path remains legacy-backed** because the edit flow still persists event-type and statistic assignments through the existing position relation models.

## Shared native model infrastructure

`SportsManagementAdminModel` extends Joomla's current `AdminModel` and centralises:

- the SportsManagement-specific database connection
- native form loading from `admin/forms`
- edit-session form data
- modified/modified-by metadata
- checkout metadata reset on save
- save-as-copy preparation
- the existing action-log hook without making logging failure abort a successful save
- entity-specific pre/post-save hooks

`SportsManagementAdminController` is the native shared list-controller base. `SportsManagementFormController` is the shared form-controller base for migrated form workflows.

## Native table stack

The native table layer now includes:

- `SportsManagementTable`
- `ClubTable`
- `ClubnameTable`
- `AgegroupTable`
- `DivisionTable`
- `EventtypeTable`
- `ExtrafieldTable`
- `SeasonTable`
- `SportstypeTable`
- `LeagueTable`
- `PositionTable`

## Transitional adapters still remaining

Administrator areas still backed substantially by legacy business logic include, among others:

- `playgrounds`
- `rosterpositions`
- `teams`
- the singular league edit flow
- the singular position edit flow

Site adapters include:

- `about`
- `clubs`
- `predictionrules`
- `referees`
- `teams`

The legacy bootstrap bridges remain in `admin/src/Legacy/LegacyBootstrap.php` and `site/src/Legacy/LegacyBootstrap.php` only for routes whose underlying MVC objects are still legacy-backed.

## Validation

The branch workflow `.github/workflows/joomla5-6-lint.yml` validates:

- syntax of migrated PHP files
- component namespace and required manifest folders
- native display smoke routes
- complete native CRUD file sets for club names, event types, extra fields, seasons and sport types
- native league/position list and inline-update stacks
- native administrator field classes and XML form validity
- absence of `JSMModelAdmin`, `JSMModelList`, explicit legacy bootstrap calls and old view inheritance from migrated classes
- presence of `leagues.saveshort` and `positions.saveshort` in the explicit special-task allowlist
- absence of singular `league` and `position` controllers from the safe standard CRUD routing set
- complete legacy view inventory used by the transitional MVC fallback
- creation of an installable development ZIP after all gates pass

## Remaining work

The next migration priorities are:

1. Migrate playgrounds and roster positions onto the native CRUD infrastructure.
2. Separate the remaining league edit dependencies (logo history, dynamic ExtraFields and dependent fields) before promoting singular league writes.
3. Migrate position event/statistic relation editing before promoting singular position writes.
4. Migrate teams and their larger dependency graph.
5. Separate `cpanel` display from database initialization and maintenance side effects.
6. Migrate database/import/export tooling and AJAX/JSON endpoints.
7. Remove the legacy bootstrap bridges and aliases once no route requires them.
8. Add installation and route smoke tests on real Joomla 5.4 and Joomla 6.1 environments.

Until the runtime gates are green, packages from this branch remain development/test builds rather than production releases.
