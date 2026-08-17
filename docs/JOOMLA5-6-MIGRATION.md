# Joomla 5/6 migration

This branch modernises SportsManagement for Joomla 5.4 and Joomla 6 while keeping the existing data model and upgrade path intact.

## Migration strategy

The migration is intentionally incremental. SportsManagement still contains important bootstrap logic in the legacy site and administrator entry points, so the modern Joomla component dispatcher must not be enabled before equivalent controller/bootstrap behaviour exists in namespaced classes.

### Phase 1 – architecture scaffold

- [x] Create the `joomla5-6` branch from `master`.
- [x] Add a namespaced component extension class under `admin/src/Extension`.
- [x] Add the Joomla service-provider scaffold under `admin/services`.
- [ ] Add the component namespace and `src`/`services` folders to the manifest only when the modern dispatcher can safely take over.
- [ ] Add namespaced Site and Administrator `DisplayController` classes.
- [ ] Move shared bootstrap logic out of the legacy entry points into reusable services/helpers.

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

## Activation gate

`admin/services/provider.php` is deliberately **not yet listed in `sportsmanagement.xml`**. Once the provider is listed in the manifest Joomla will boot the component through the modern component dispatcher. Activating it before the controller/bootstrap migration would bypass behaviour that currently lives in the legacy `sportsmanagement.php` entry points.

The provider will be activated only after a namespaced dispatcher/controller path can reproduce the required frontend and administrator bootstrap behaviour.
