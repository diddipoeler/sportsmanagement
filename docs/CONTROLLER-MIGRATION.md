# SportsManagement migration (Joomla 5/6)

This document tracks the transition from the legacy Joomla naming/loading scheme to the Joomla 5/6 component service provider, dispatcher and namespaced MVC stack.

## Current architecture

The component manifest installs the namespace `Diddipoeler\Component\SportsManagement`, `site/src`, `admin/src`, `admin/services` and `admin/tmpl`.

Modern entry controllers exist in both areas:

- `site/src/Controller/DisplayController.php`
- `admin/src/Controller/DisplayController.php`

The component service provider uses `SportsManagementMVCFactory`, a component-specific subclass of Joomla's `MVCFactory`. Native Joomla 5/6 classes are always preferred. When a namespaced model or HTML view has not yet been rewritten, the factory can load the existing SportsManagement class, expose it under the namespace Joomla expects and retain the legacy template path.

This transitional factory also restores `sportsmanagementHelper::getDBConnection()` after Joomla injects its normal database connection, preserving installations that keep SportsManagement data in a separate database.

## Dispatcher cutover

Both component dispatchers now send normal HTML display requests through Joomla's modern component dispatcher when a matching HTML view exists.

The modern path is limited to requests with:

- `task=display`
- `layout=default`
- `format=html`
- no explicit controller, or `controller=display`

Administrator access is additionally checked with the current Joomla identity and `core.manage`.

Everything outside those conditions continues through the original entry points. This intentionally keeps add/edit/save, publish/unpublish, import/export, AJAX/JSON endpoints, raw responses and special layouts on the legacy path until their complete controller/model dependencies are migrated.

### Administrator coverage

The legacy tree contains 111 administrator directories with `view.html.php`. Their class names follow the `sportsmanagementView<ViewName>` convention used by `SportsManagementMVCFactory`, so their normal default HTML display routes can be resolved through the Joomla 5/6 dispatcher.

### Site coverage

The site tree contains 69 directories with a directly dispatchable `view.html.php`. Their class names follow the same convention and can be resolved through the Joomla 5/6 dispatcher.

Six additional site view directories are partial/helper areas without their own `view.html.php` (`flash`, `map`, `overall`, `predictionflash`, `predictionoverall`, `tree`). They are not independent HTML dispatcher targets and remain available to the legacy templates that include them.

## Fully native administrator views

The following display stacks no longer depend on their legacy view/model implementation:

- `agegroups`
- `close`
- `clubs`
- `currentseasons`
- `divisions`

Each has a namespaced model/view and a template under `admin/tmpl`.

`SportsManagementListModel` is the native base for the migrated list models and preserves the optional SportsManagement-specific database connection.

Notable behaviour retained or improved:

- `ClubsModel` keeps search, publication, country, association, season, geo-data and placeholder-logo filtering. The season filter uses `EXISTS` instead of grouping joined rows.
- `AgegroupsModel` no longer inserts age-group records merely because an empty list is displayed.
- `DivisionsModel` preserves the active project `pid` in Joomla user state.
- `CurrentseasonsModel` is read-only and uses the SportsManagement database connection.

## Transitional namespaced adapters

A first explicit adapter group exists for frequently used list/display views in addition to the generic factory fallback.

Administrator adapters include:

- `clubnames`
- `eventtypes`
- `extrafields`
- `leagues`
- `playgrounds`
- `positions`
- `rosterpositions`
- `seasons`
- `sportstypes`
- `teams`

Site adapters include:

- `about`
- `clubs`
- `predictionrules`
- `referees`
- `teams`

These classes are namespaced entry points but intentionally reuse existing SportsManagement business logic and templates while the deeper model/view code is rewritten incrementally.

## Controller and table migration

Namespaced administrator controllers already exist for the first entity groups, including clubs, club names, age groups, divisions and current seasons.

`SportsManagementAdminController` replaces the shared legacy list-controller base for migrated controllers. `SportsManagementFormController` replaces the shared legacy form-controller path for the first migrated entities while preserving SportsManagement save and redirect behaviour.

The first table stack is fully namespaced:

- `SportsManagementTable`
- `ClubTable`
- `ClubnameTable`
- `AgegroupTable`
- `DivisionTable`

The transitional form models for those entities remain backed by the existing save/business logic until that code is split out of `JSMModelAdmin`.

## Legacy boot bridges

The transition is isolated in two bridge classes:

- `admin/src/Legacy/LegacyBootstrap.php`
- `site/src/Legacy/LegacyBootstrap.php`

They load only the old libraries/helpers required by a legacy-backed MVC object. The site bridge additionally reproduces view-specific dependencies formerly selected by the large switch in `site/sportsmanagement.php`.

These bridges are migration scaffolding, not the final architecture. As each view/model is rewritten natively, its dependency on the bridge can be removed without changing its public route.

## Validation

The branch workflow `.github/workflows/joomla5-6-lint.yml` now validates:

- syntax of all files under the migrated `admin/src`, `admin/services`, `site/src` and `admin/tmpl` trees
- the component namespace and required manifest folders
- the five fully native administrator smoke routes
- the complete legacy HTML-view inventory and expected class naming convention in administrator and site
- presence of the custom MVC factory and both legacy bridge classes
- creation of an installable development ZIP after the gates pass

The workflow deliberately uses the PHP already present on the GitHub Ubuntu 24.04 runner so transient rate limits while downloading a PHP setup action cannot make the migration gate red before the repository is even checked out. A previous full PHP 8.1 branch run has already passed; version-specific Joomla runtime tests remain a separate gate.

## Remaining work

The normal default HTML view surface is now routed through the Joomla 5/6 component dispatcher, but this does **not** mean all underlying code is native yet.

The remaining migration work is:

1. Replace generic legacy-backed models/views with native Joomla 5/6 implementations, prioritising the most-used administrator lists and site pages.
2. Migrate edit/form views together with their write controllers and form models.
3. Separate `cpanel` display from database initialization and maintenance side effects.
4. Migrate database/import/export tooling.
5. Migrate AJAX, JSON and other non-HTML controller endpoints.
6. Remove the legacy bootstrap bridges and legacy class aliases once no route requires them.
7. Add installation and route smoke tests on real Joomla 5.4 and Joomla 6.1 environments.

Until the final runtime gate is green, packages from this branch should be treated as development/test builds rather than production releases.
