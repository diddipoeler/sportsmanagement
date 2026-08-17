# SportsManagement migration (Joomla 5/6)

This document tracks the transition from the legacy Joomla naming/loading scheme to namespaced Joomla 5/6 MVC classes.

## Modern entry controllers

- `site/src/Controller/DisplayController.php`
- `admin/src/Controller/DisplayController.php`

The administrator default route remains `cpanel`. The `cpanel` route is deliberately **not** modernized yet because its legacy view performs database checks and initialization during rendering.

## Hybrid dispatcher

The Joomla component service provider is now installed and active through the manifest.

Two component-specific dispatchers make the cutover incremental:

- `admin/src/Dispatcher/Dispatcher.php`
- `site/src/Dispatcher/Dispatcher.php`

The administrator dispatcher sends only these read-only routes through the modern Joomla dispatcher:

- `view=close`
- `view=currentseasons`

All other administrator requests are delegated to the existing `administrator/components/com_sportsmanagement/sportsmanagement.php` entry point.

The site dispatcher currently delegates every request to the existing site entry point. This allows the service provider, namespace and MVCFactory to be active without forcing the not-yet-migrated frontend through the modern dispatcher.

## Component service provider

`admin/services/provider.php` now follows the Joomla core component pattern:

- registers `MVCFactory`
- registers `ComponentDispatcherFactory`
- creates `SportsManagementComponent`
- injects the HTML registry
- injects the MVCFactory into the component instance

This is important because models which boot the component can now retrieve the same namespaced MVCFactory.

## Administrator controllers migrated

The following controllers now have namespaced equivalents under `admin/src/Controller/`:

- `AgegroupController`
- `AgegroupsController`
- `ClubController`
- `ClubsController`
- `ClubnameController`
- `ClubnamesController`
- `CpanelController`
- `CurrentseasonController`
- `DivisionController`
- `DivisionsController`

## Native shared controller bases

### `SportsManagementAdminController`

Native replacement for `JSMControllerAdmin`. Migrated list controllers no longer depend on the global legacy admin-controller base.

It preserves the SportsManagement-specific `core.admin` ordering restriction and modal-close behaviour while using the current Joomla application identity APIs.

### `SportsManagementFormController`

Native Joomla 5/6 replacement for the shared `JSMControllerForm` path used by the first migrated entity forms.

It keeps Joomla's injected MVCFactory, application, input and form factory, while preserving the existing SportsManagement save/redirect behaviour for:

- apply
- save
- save & new
- save as copy
- modal workflows
- club/team-specific redirects
- project/project-team pid redirects
- optional team creation when saving a club

The following form controllers now use this native base instead of `JSMControllerForm`:

- `ClubController`
- `ClubnameController`
- `AgegroupController`
- `DivisionController`

## Model migration

The first MVCFactory-resolvable administrator models exist under `admin/src/Model/`:

- `ClubModel`
- `ClubnameModel`
- `AgegroupModel`
- `DivisionModel`

These four are transitional adapters: they are namespaced and can be resolved by Joomla's MVCFactory, but they still reuse the existing entity model/business logic and the large legacy `JSMModelAdmin` save implementation.

`CurrentseasonsModel` is a native Joomla `ListModel` and no longer depends on `JSMModelList`. It provides a read-only project/season/league/sport-type query for the modern smoke route.

`CloseModel` is an intentionally empty native model for the side-effect-free modal-close smoke route.

## Table migration

The first entity table stack is fully namespaced and native:

- `SportsManagementTable`
- `ClubTable`
- `ClubnameTable`
- `AgegroupTable`
- `DivisionTable`

`SportsManagementTable` preserves the old array/Registry binding behaviour for `extended`, `extendeduser`, `params`, `comp_params` and `season_ids`, but uses Joomla's current `Table` API and an injected `DatabaseInterface` with the configured SportsManagement database connection as the component-specific override.

## View migration and smoke routes

### `view=close`

Native files:

- `admin/src/Model/CloseModel.php`
- `admin/src/View/Close/HtmlView.php`
- `admin/tmpl/close/default.php`

This is the smallest side-effect-free modern route. The legacy SqueezeBox JavaScript is not reused.

### `view=currentseasons`

Native files:

- `admin/src/Model/CurrentseasonsModel.php`
- `admin/src/View/Currentseasons/HtmlView.php`
- `admin/tmpl/currentseasons/default.php`

The smoke version intentionally omits the legacy per-project enrichment through divisions, positions, referees, teams and rounds models. It reads and renders project/season/league/sport-type data only, so dispatcher/MVC failures can be isolated from the rest of the legacy model graph.

## Legacy groups still remaining

### Site

The site component still contains task-specific controllers under `site/controllers/`, including editing, match handling, predictions, AJAX/JSON and export-related endpoints. All frontend requests currently use the hybrid dispatcher's legacy fallback.

### Administrator

The administrator component still contains many legacy CRUD controllers under `admin/controllers/`, plus database/tooling and AJAX controllers. These continue to work through the administrator hybrid fallback until their full controller/model/view group is migrated.

Migration order:

1. Continue entity controller/model/table groups
2. Migrate matching list/edit views and forms
3. Separate `cpanel` rendering from database initialization
4. Database/import/export controllers
5. AJAX / JSON controllers
6. Gradually expand the modern dispatcher allowlist

## Manifest state

The component manifest now registers and installs:

- namespace `Diddipoeler\Component\SportsManagement`
- `site/src`
- `admin/src`
- `admin/services`
- `admin/tmpl`

## Validation

The branch workflow `.github/workflows/joomla5-6-lint.yml` validates:

- migrated PHP syntax on PHP 8.1
- migrated PHP syntax on PHP 8.3
- the service provider
- the component manifest as XML

Runtime validation on real Joomla 5.4/6.1 installations is the next separate gate. The hybrid dispatcher is specifically designed so a test package can boot through the modern service provider without immediately forcing every legacy route through the modern MVC stack.
