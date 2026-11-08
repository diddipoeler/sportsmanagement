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
