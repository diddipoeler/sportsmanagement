# SportsManagement migration (Joomla 5/6)

This document tracks the transition from the legacy Joomla naming/loading scheme to the Joomla 5/6 component service provider, dispatcher and namespaced MVC stack.

## Current architecture

The component manifest installs the namespace `Diddipoeler\Component\SportsManagement`, `site/src`, `admin/src`, `admin/services`, `admin/tmpl` and `admin/forms`.

Both site and administrator entry points use Joomla component dispatchers. `SportsManagementMVCFactory` always prefers a namespaced Joomla 5/6 class and only falls back to the old SportsManagement MVC object when a native replacement does not yet exist.

The transition supports installations using a separate SportsManagement database. Administrator list/form bases and the native site model base restore `sportsmanagementHelper::getDBConnection()` after Joomla injects its default database connection.

## Administrator dispatcher boundaries

Administrator routes are deliberately split into three levels.

### Fully native CRUD

These groups have native list and edit/write stacks:

- `clubname` / `clubnames`
- `eventtype` / `eventtypes`
- `extrafield` / `extrafields`
- `season` / `seasons` for standard CRUD
- `sportstype` / `sportstypes` for standard CRUD

They use namespaced controllers, `SportsManagementAdminModel`, `SportsManagementListModel`, native tables/views/templates and XML forms under `admin/forms`.

Season person/team assignment workflows and sport-type import/export remain legacy-backed because they are separate special operations.

### Native list and narrow update paths

The following administrator groups have native list models/views, native table/action models and safe plural state/ordering actions:

- `leagues`
- `playgrounds`
- `positions`
- `rosterpositions`
- `teams`

Additional native special actions are explicitly allowlisted:

- `leagues.saveshort`
- `positions.saveshort`
- `rosterpositions.addhome`
- `rosterpositions.addaway`
- `teams.saveshort`
- `teams.copysave`

Their complex singular editors remain legacy-backed. `LEGACY_DEFAULT_VIEWS` prevents a partial native action model from accidentally being combined with an old singular view through the generic display route.

### Fully native display-only administrator stacks

These display routes no longer depend on their legacy list/view implementation:

- `agegroups`
- `close`
- `clubs`
- `currentseasons`
- `divisions`

## Teams cutover

`TeamsModel` is now a native `SportsManagementListModel`. It preserves search, country, sport type, age group, publication and club filtering and keeps the existing `com_sportsmanagement.club_id` state. It also retains compatibility helpers used elsewhere (`getTeamListSelect`, playground team lookup and team extraction from match lists).

`teams.saveshort` updates only the selected teams' sport type and age group after loading each complete table row. `teams.copysave` creates independent team copies through the native table layer. Publish/unpublish, trash, check-in and ordering are also native.

The singular team editor remains legacy-backed because it still combines training data, season assignments, club merging, dependent fields, Extended/ExtraFields, standard playground handling and media operations.

## League, position, playground and roster-position boundaries

League list/filtering and inline updates are native. Singular league editing remains legacy because of historical logos, association/age-group dependencies and dynamic ExtraFields.

Position list/filtering and parent-position inline changes are native. Singular position editing remains legacy because it also persists event-type/statistic relations.

Playground list/filtering and state/ordering operations are native. Singular playground editing remains legacy because of notes, logo history, geodata and Extended/ExtraFields.

Roster-position list/filtering and state/ordering operations are native. HOME/AWAY creation redirects are modern, while the graphical editor and its registry/coordinate setup remain legacy-backed.

## Native administrator form fields

Reusable namespaced Joomla 5/6 fields now exist under `admin/src/Field`:

- `CountryField`
- `SportstypeField`
- `AgegroupField`
- `AssociationField`
- `FederationField`
- `LeaguelevelField`

They use the configured SportsManagement database and allow migrated XML forms to stop depending on the old global field classes under `admin/models/fields`.

## Site architecture

The site dispatcher supports HTML, raw and PDF display formats and uses the same native-first MVC factory strategy. The legacy tree still provides fallback coverage for 69 HTML views, four raw views and one PDF view while the implementations are migrated incrementally.

### Native site foundation

`site/src/Model/SportsManagementModel.php` is the first shared native frontend model base. It restores the configured SportsManagement database connection without loading the legacy site MVC stack.

`site/src/View/SportsManagementHtmlView.php` is a deliberately small Joomla 5/6 view base. It provides application, input, component parameters, URI and database-selector context. It does **not** copy the old monolithic `site/libraries/sportsmanagement/view.php`; project context, global template fragments, PDF helpers and prediction-specific behaviour will be separated into focused services as additional views migrate.

### Fully native site views

The first frontend views no longer inherit old SportsManagement MVC classes:

- `about`
- `close`

`about` uses a native model/view and a modernized template in the already-installed `site/views/about/tmpl` path. It no longer boots `LegacyBootstrap` or `sportsmanagementViewAbout`.

`close` uses a native model/view and no longer depends on the removed SqueezeBox JavaScript API; modal callers reload their parent window, while direct navigation falls back to browser history.

### Site views still transitional

Project/prediction-heavy views remain behind the native-first legacy fallback until their shared context is extracted. Explicit adapters still include, among others:

- `clubs`
- `teams`
- `referees`
- `predictionrules`

`jlxmlexports` is intentionally not treated as a passive display view because its legacy `display()` triggers an export operation.

## Shared native infrastructure

Administrator:

- `SportsManagementAdminModel`
- `SportsManagementListModel`
- `SportsManagementAdminController`
- `SportsManagementFormController`
- native table layer beginning with `SportsManagementTable`

Site:

- `SportsManagementModel`
- `SportsManagementHtmlView`

The legacy bridges remain migration scaffolding only for routes whose business logic still requires them.

## Validation

`.github/workflows/joomla5-6-lint.yml` validates:

- syntax of migrated PHP files
- component manifest and namespace
- five complete administrator CRUD groups
- five native administrator list/narrow-update groups
- the explicit special-task and singular-editor dispatcher boundaries
- native administrator field classes and form XML
- native site model/view bases
- native `about` and `close` site views
- absence of legacy MVC inheritance/bootstrap calls from the native site classes
- absence of SqueezeBox usage from the new close view
- the complete remaining legacy view inventory used by the transitional factory
- creation of an installable development ZIP after all gates pass

## Remaining priorities

1. Extract native site project/template context and migrate `referees`, `clubs` and `teams` without inheriting the legacy monolithic view base.
2. Split the singular team/league/position/playground/roster-position edit dependencies so those write routes can become native.
3. Migrate relation-heavy administrator areas such as `teamplayers` and `projectteams` as coherent blocks rather than partial list rewrites.
4. Separate `cpanel` display from database initialization and maintenance side effects.
5. Migrate import/export and AJAX/JSON controller tooling.
6. Remove the remaining legacy bootstrap bridges and class aliases when no route needs them.
7. Add installation and route smoke tests on real Joomla 5.4 and Joomla 6.1 environments.

Until real Joomla runtime gates are green, packages from this branch remain development/test builds rather than production releases.
