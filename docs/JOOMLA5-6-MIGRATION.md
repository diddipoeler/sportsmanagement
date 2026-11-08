# Joomla 5/6 migration

This branch modernises SportsManagement for Joomla 5.4 and Joomla 6 while keeping the existing data model and upgrade path intact.

## Migration strategy

The migration remains incremental. The Joomla 5/6 component namespace, service provider and namespaced dispatchers are already active. `SportsManagementMVCFactory` resolves native MVC implementations first and retains controlled legacy fallbacks for operations not yet migrated; the site and administrator entry points remain compatibility bridges during this transition.

### Phase 1 – architecture scaffold

- [x] Create the `joomla5-6` branch from `master`.
- [x] Add a namespaced component extension class under `admin/src/Extension`.
- [x] Add the Joomla service-provider scaffold under `admin/services`.
- [x] Register the component namespace and the native `src`/`services` folders in the manifest.
- [x] Add namespaced Site and Administrator `DisplayController` classes.
- [x] Introduce a native-first MVC factory, extension boot logic and the legacy controller bridges.
- [ ] Finish isolating the remaining legacy operations in reusable services and models.

### Phase 2 – MVC migration

- [ ] Migrate controllers to `Diddipoeler\\Component\\SportsManagement\\{Site|Administrator}\\Controller`.
- [ ] Migrate models and replace static `getInstance()` patterns with the component MVC factory where practical.
- [ ] Migrate views and templates to the Joomla 5/6 MVC naming conventions.
- [ ] Migrate tables to namespaced table classes.
- [ ] Replace manual `JLoader::import()` dependencies with namespace autoloading.

### Phase 3 – compatibility cleanup

- [ ] Remove Joomla 1.5/1.6/1.7/2.5/3 compatibility branches.
- [ ] Replace remaining legacy aliases and removed APIs with Joomla CMS namespaced APIs.
- [ ] Review HTML helpers, form behaviours, routing, ACL, sessions, database queries and web assets for Joomla 5/6 compatibility.
- [ ] Review PHP 8.2+ deprecations and runtime warnings.

### Phase 4 – extensions and packaging

- [ ] Validate install and update from an existing SportsManagement installation without losing data.
- [ ] Migrate bundled modules.
- [ ] Migrate bundled plugins.
- [ ] Validate update-server metadata.
- [ ] Produce an installable beta ZIP for Joomla 5.4 and Joomla 6.

## Active dispatcher and compatibility boundary

`sportsmanagement.xml` installs the component namespace and `admin/services`. Joomla boots `admin/services/provider.php` and the native component extension. The native-first MVC factory and the site/administrator `DisplayController` implementations are already in use. Legacy controller, model and view fallbacks remain deliberately available for workflows that have not yet been migrated; do not remove them without migrating and testing their callers.

## PHPDoc and security progress (8 November 2026)

- All 29 frontend module service providers and the administrator quick-icon provider now document `register()` and its Joomla dependency-injection purpose.
- Missing method PHPDoc was added to the frontend router, image handler, database resolver, Inline-Hockey API client, match mutation and next-match ranking services, together with administrator extension, GitHub, installer and image-handler views/controllers.
- Frontend and administrator image-handler scripts now JSON-encode callback strings and errors with HTML-safe flags. The administrator image actions also reject missing or guest identities before checking permissions.
- The existing file-header audit checks `@version`, `@author`, `@copyright` and `@license` in first-party PHP files. The selected function-PHPDoc gate now includes the newly documented classes and module providers. Bundled vendor code retains its upstream attribution.

**Remaining work:** finish native MVC conversion and legacy API cleanup, audit outstanding function documentation using `.github/tests/joomla56_function_docs_inventory.php`, validate modules and installer on real Joomla 5.4 and Joomla 6 environments, and complete packaging and update tests.

## Follow-up: native JSON feed, quote files and training data (8 November 2026)

- The site JSON feed resolves its application through the Joomla 5/6 site resolver, requires the native JsonfeedModel, and selects the compact layout only for `compact=1`. Both JSON templates no longer call the static Joomla application factory.
- The administrator quote-file picker lists only readable, regular TXT files that satisfy the frontend module reader's basename contract; symlinks, scripts, and unsafe filenames are excluded.
- Training data creation messages use the actual number of created records (one) instead of a database record ID.
- Added named-function documentation and targeted regression tests for JSON view dispatch and quote-source filtering.

## Native frontend documentation follow-up (8 November 2026)

Added detailed function-level PHPDoc to the Joomla 5/6 UEFA coefficient and project referee read models and tournament-node view. These classes retain their existing read-only query and template behaviour; the selected-function PHPDoc CI gate now covers all named methods in the updated files.

## Administrator match commentary read error handling (8 November 2026)

`admin/src/Model/MatchcommentaryModel.php` now documents its scoped chronological read query and sends database exception details to Joomla's private log rather than into the model's public error list. A generic translated error is retained for the view. The standalone `joomla56_admin_match_commentary_privacy.php` test checks empty IDs, the bound match identifier, successful reads and confidential query failures on PHP 8.3/8.4.

## Season relation ACL and prediction group save semantics (8 November 2026)

Administrator Seasonteam, Seasonperson and Seasonteamperson form models now require an authenticated identity and delegate record editing decisions to Joomla's native parent AdminModel. Their historical lookups against `com_sportsmanagement.message.<id>` referenced unrelated ACL assets and could override Joomla's intended permission result. Creation notifications for Predictiongroup use the number of inserted records (one) instead of the generated database ID. `joomla56_admin_season_relation_acl.php` covers no identity, denied/granted native ACL and translated notification count.

The historical Sportsmanagement sample-record model is different: its legacy table genuinely defines `com_sportsmanagement.message.<id>` as the record ACL asset. This model retains that legitimate per-record permission but now rejects missing administrator identities safely. Its core-database table selection remains unchanged.

## Native calendar event-ID safety and database failure handling (8 November 2026)

The site Google Calendar reader now checks actual ASCII control characters in remote event IDs without rejecting ordinary IDs containing `x`. Failure to prepare or execute the authorised calendar SQL query is logged only inside Joomla, returning no calendar data rather than crashing public event views. Every named method in `GoogleCalendarReadService` now has descriptive PHPDoc, and the selected-function audit includes the service.

## Frontend calendar event request and view guards (8 November 2026)

The native EventModel rejects array/object/numeric, oversized and ASCII-control-character event IDs before creating the Google Calendar read service. Event HtmlView now validates its EventModel before attempting to render the event title. The new `joomla56_event_mvc_request_validation.php` regression test covers malformed requests, missing models, valid event titles and missing-event layout fallback on PHP 8.3/8.4.

## Administrator sports-type and extension-list compatibility (8 November 2026)

The Sportstype administrator form now normalises invalid legacy media-field configuration to Joomla's media field instead of casting arrays or loading malformed field types. Optional database column-size hints remain nonfatal; the single-row sports-type lookup logs SQL exceptions privately with a generic administrator error. The filesystem-backed SpecialextensionsModel now excludes symlinked extension directories and has complete function PHPDoc. Regression tests and a PHP 8.3/8.4 workflow cover form-field validation, SQL error privacy, file enumeration and the empty list query.
