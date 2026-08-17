# SportsManagement controller migration (Joomla 5/6)

This document tracks the transition from the legacy Joomla controller naming/loading scheme to namespaced Joomla 5/6 controllers.

## Modern entry controllers added

- `site/src/Controller/DisplayController.php`
- `admin/src/Controller/DisplayController.php`

The site display controller can map directly to `BaseController` because the legacy `site/controller.php` contains no component-specific dispatch behaviour.

The administrator display controller preserves the legacy default view (`cpanel`).

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

The CRUD controllers remain transitional where necessary: they extend the existing `JSMControllerForm` / `JSMControllerAdmin` base classes so the shared SportsManagement save, redirect, ACL and modal behaviour is not lost before those base classes are migrated.

`DivisionsController` no longer relies on dynamic controller properties for application/input/project state, and its CSRF failure message now uses `Joomla\CMS\Language\Text` explicitly.

## Legacy controller groups

### Site

The site component still contains task-specific controllers under `site/controllers/`, including editing, match handling, predictions, AJAX/JSON and export-related endpoints.

Migration order:

1. Simple display/list controllers
2. Simple form/edit controllers
3. Match and prediction controllers
4. AJAX / JSON endpoints
5. Import/export and image handling

### Administrator

The administrator component still contains many legacy CRUD controllers under `admin/controllers/`, plus AJAX and database/tooling controllers.

Migration order:

1. Continue simple list/form controllers
2. Migrate the shared `JSMControllerAdmin` / `JSMControllerForm` behaviour into namespaced base controllers
3. Migrate matching models and views for the first smoke-test route
4. Database/import/export controllers
5. AJAX / JSON controllers

## Manifest state

The component manifest now registers:

- namespace `Diddipoeler\Component\SportsManagement`
- `site/src`
- `admin/src`

The `admin/services` directory is intentionally **not** installed yet. This keeps Joomla on the legacy component bootstrap until the modern MVC route can resolve the required controller/model/view classes without relying on the old dynamic path setup.

## Dispatcher activation gate

Do **not** add `admin/services` to the install manifest until the required namespaced controllers/models/views for the initial smoke-test path exist.

The current legacy bootstraps dynamically add model/view/template paths and extension controllers. Activating the modern component dispatcher before replacing that behaviour would bypass working legacy initialization.

## Initial smoke-test path

The first modern-dispatch smoke test should cover:

- Administrator: `index.php?option=com_sportsmanagement` -> `cpanel`
- Site: one read-only view with no custom task controller
- Existing database configuration remains readable
- No writes to schema during a normal page request
