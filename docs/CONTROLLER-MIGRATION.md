# SportsManagement migration (Joomla 5/6)

This document tracks the transition from the legacy Joomla naming/loading scheme to the Joomla 5/6 component service provider, dispatcher and namespaced MVC stack.

## Current architecture

The component manifest installs the namespace `Diddipoeler\Component\SportsManagement`, `site/src`, `admin/src`, `admin/services`, `admin/tmpl` and `admin/forms`.

Both site and administrator entry points use Joomla component dispatchers. `SportsManagementMVCFactory` always prefers a namespaced Joomla 5/6 class and only falls back to the old SportsManagement MVC object when a native replacement does not yet exist.

The transition supports installations using a separate SportsManagement database. Administrator list/form bases and the native site model bases restore `sportsmanagementHelper::getDBConnection()` after Joomla injects its default database connection.

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

The site dispatcher supports HTML, raw and PDF display formats and uses the same native-first MVC factory strategy. The legacy tree still provides fallback coverage for 69 HTML views, four raw views and one PDF view while implementations are migrated incrementally.

### Native site foundation

`site/src/Model/SportsManagementModel.php` is the shared native frontend database-model base. It restores the configured SportsManagement database connection without loading the legacy site MVC stack.

`site/src/View/SportsManagementHtmlView.php` is the lightweight Joomla 5/6 frontend view base. It provides application, input, component parameters, URI and database-selector context.

### Native site project context

`site/src/Model/SportsManagementProjectModel.php` is the shared project-aware frontend model base. It resolves project and division IDs directly from the request and queries project metadata through the configured SportsManagement database rather than calling the old `sportsmanagementModelProject`.

It also owns the project template configuration path:

- default values are read from `site/settings/default/<template>.xml`
- project overrides are read from `#__sportsmanagement_template_config`
- when a project has no local template row, `master_template` is used as the fallback source
- division subtree IDs are resolved natively for project views that support division filtering
- season-specific league-logo overrides are retained

`site/src/View/SportsManagementProjectHtmlView.php` is the matching project-aware view base. It prepares project, overall configuration and view configuration, exposes the existing layout class/modal context and registers the installed `views/globalviews/tmpl` path for transitional shared template fragments.

The base no longer inherits the monolithic `site/libraries/sportsmanagement/view.php`. Presentation-only utility helpers are registered explicitly, and jQuery is requested through Joomla's WebAssetManager rather than through the old view bootstrap.

### Native site prediction context

`site/src/Model/SportsManagementPredictionModel.php` now isolates the read-only prediction context from the former monolithic `sportsmanagementModelPrediction`. It reads request IDs into instance state and owns the Joomla 5/6 database paths for:

- published prediction games
- prediction template configuration with default-XML and `master_template` fallback
- the current or selected prediction member
- published prediction projects and their effective start dates
- prediction administrators
- the scoring rules used by the rules-page examples

`site/src/View/SportsManagementPredictionHtmlView.php` is the matching frontend view base. It prepares prediction game/member/project context, merges `predictionoverall` with the view-specific configuration, registers the shared `globalviews` and `predictionheading` template paths, and requests jQuery through Joomla's WebAssetManager.

The shared `predictionheading` template no longer reads global static `sportsmanagementModelPrediction::$...` state. It uses the explicit IDs and member/game state provided by the native prediction view context while retaining the existing route helper for link generation.

### Fully native site MVC stacks

The following frontend MVC stacks no longer inherit legacy SportsManagement models/views:

- `about`
- `close`
- `clubs`
- `teams`
- `referees`
- `predictionrules`

`about` uses a native model/view and a modernized template in the installed `site/views/about/tmpl` path.

`close` uses a native model/view and no longer depends on the removed SqueezeBox API.

`clubs` now queries participating clubs and their project teams directly. Division filtering uses the native division subtree from `SportsManagementProjectModel`; team lists are grouped per club without invoking the legacy project model.

`teams` now queries project-team, season-team, team, club, division and playground data directly through the native project model stack and preserves the existing slug/contact/social fields needed by the installed templates.

`referees` now queries project referees, people and project positions directly and retains the correlated match-count calculation without calling the legacy referee/project models.

`predictionrules` now uses `SportsManagementPredictionModel`/`SportsManagementPredictionHtmlView`. Its main, info and shared heading templates no longer invoke `sportsmanagementModelPrediction::...`; the five legacy scoring examples are calculated by the native model instead.

### Transitional site presentation

The installed templates under several site view directories and the shared `globalviews` fragments are still reused. Presentation helpers therefore remain transitional even where the models and views are already namespaced native Joomla MVC classes.

The larger prediction pages `predictionresults`, `predictionranking`, `predictionusers` and `predictionentry` remain transitional because their templates still combine scoring/ranking queries, pagination, charts, edit/member operations and in some cases point persistence. They should be separated as coherent model/service blocks rather than partially redirected.

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
- `SportsManagementProjectModel`
- `SportsManagementProjectHtmlView`
- `SportsManagementPredictionModel`
- `SportsManagementPredictionHtmlView`

The legacy bridges remain migration scaffolding only for routes whose business logic still requires them.

## Validation

`.github/workflows/joomla5-6-lint.yml` validates the overall migration and creates the installable development ZIP. It covers PHP syntax, manifest/namespace checks, administrator native stacks, the native site foundation and the remaining legacy view inventory.

`.github/workflows/joomla5-6-site-project-context.yml` separately gates the native project-aware site stack. It verifies:

- syntax and presence of the project model/view bases
- native `clubs`, `teams` and `referees` model/view pairs
- absence of `LegacyBootstrap`, `sportsmanagementModelProject` and old view inheritance from those classes
- project/template-config/default/master-template contract markers
- registration of the shared `globalviews` template path
- Joomla WebAssetManager usage for jQuery

`.github/workflows/joomla5-6-site-prediction-context.yml` separately gates the native prediction stack. It validates the prediction model/view bases, native `predictionrules`, the rules/heading templates, the required prediction tables and template fallback contract, and rejects direct legacy static prediction MVC dependencies from the migrated files.

## Remaining priorities

1. Split `predictionresults`, `predictionranking` and `predictionusers` into native query/scoring/pagination services before moving those pages onto `SportsManagementPredictionHtmlView`.
2. Split the singular team/league/position/playground/roster-position edit dependencies so those write routes can become native.
3. Migrate relation-heavy administrator areas such as `teamplayers` and `projectteams` as coherent blocks rather than partial list rewrites.
4. Separate `cpanel` display from database initialization and maintenance side effects.
5. Migrate import/export and AJAX/JSON controller tooling.
6. Remove remaining legacy bootstrap bridges, shared legacy template assumptions and class aliases when no route needs them.
7. Add installation and route smoke tests on real Joomla 5.4 and Joomla 6.1 environments.

Until real Joomla runtime gates are green, packages from this branch remain development/test builds rather than production releases.
