# SportsManagement migration (Joomla 5/6)

This document tracks the transition from the legacy Joomla naming/loading scheme to namespaced Joomla 5/6 MVC classes.

## Modern entry controllers

- `site/src/Controller/DisplayController.php`
- `admin/src/Controller/DisplayController.php`

The site display controller maps directly to `BaseController` because the legacy `site/controller.php` contains no component-specific dispatch behaviour.

The administrator display controller still preserves the legacy default view (`cpanel`). The `cpanel` route is deliberately **not** used as the first modern smoke path because its legacy view performs database checks and initialization during rendering.

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

`CurrentseasonsModel` is different: it is a native Joomla `ListModel` and no longer depends on `JSMModelList`. It provides a read-only project/season/league/sport-type query for the modern smoke route.

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

## Legacy controller groups still remaining

### Site

The site component still contains task-specific controllers under `site/controllers/`, including editing, match handling, predictions, AJAX/JSON and export-related endpoints.

Migration order:

1. Simple display/list controllers and read-only views
2. Simple form/edit controllers
3. Match and prediction controllers
4. AJAX / JSON endpoints
5. Import/export and image handling

### Administrator

The administrator component still contains many legacy CRUD controllers under `admin/controllers/`, plus database/tooling and AJAX controllers.

Migration order:

1. Continue entity controller/model/table groups
2. Migrate their list/edit views and forms
3. Separate `cpanel` rendering from database initialization
4. Database/import/export controllers
5. AJAX / JSON controllers

## Manifest state

The component manifest now registers:

- namespace `Diddipoeler\Component\SportsManagement`
- `site/src`
- `admin/src`
- `admin/tmpl`

The `admin/services` directory is intentionally **not** installed yet. This keeps Joomla on the legacy component bootstrap while modern routes are built out safely.

## Dispatcher activation gate

Do **not** add `admin/services` to the install manifest until either:

1. the administrator default route (`cpanel`) has a safe modern implementation, or
2. a deliberate hybrid dispatcher/fallback mechanism is implemented for still-legacy views.

Activating the modern component dispatcher too early would bypass the current dynamic legacy initialization for controllers, model/view paths and SportsManagement extensions.

## Validation

A branch workflow at `.github/workflows/joomla5-6-lint.yml` lints the migrated PHP paths on PHP 8.1 and PHP 8.3. Runtime validation against real Joomla installations remains a separate gate after the service provider is activated in a controlled test package.
