# SportsManagement controller migration (Joomla 5/6)

This document tracks the transition from the legacy Joomla controller naming/loading scheme to namespaced Joomla 5/6 controllers.

## Modern entry controllers added

- `site/src/Controller/DisplayController.php`
- `admin/src/Controller/DisplayController.php`

The site display controller can map directly to `BaseController` because the legacy `site/controller.php` contains no component-specific dispatch behaviour.

The administrator display controller preserves the legacy default view (`cpanel`).

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

The administrator component contains a large set of legacy CRUD controllers under `admin/controllers/`, plus AJAX and database/tooling controllers.

Migration order:

1. Simple list controllers (`*s.php`)
2. Simple form controllers (singular entities)
3. `cpanel` and utility controllers
4. Database/import/export controllers
5. AJAX / JSON controllers

## Dispatcher activation gate

Do **not** add `admin/services` to the install manifest until the required namespaced controllers/models/views for the initial smoke-test path exist.

The current legacy bootstraps dynamically add model/view/template paths and extension controllers. Activating the modern component dispatcher before replacing that behaviour would bypass working legacy initialization.

## Initial smoke-test path

The first modern-dispatch smoke test should cover:

- Administrator: `index.php?option=com_sportsmanagement` -> `cpanel`
- Site: one read-only view with no custom task controller
- Existing database configuration remains readable
- No writes to schema during a normal page request

