# SportsManagement migration (Joomla 5/6)

This document tracks the migration from the legacy Joomla naming/loading scheme to the Joomla 5/6 component service provider, dispatcher and namespaced MVC stack.

## Current architecture

The component manifest installs the namespace `Diddipoeler\Component\SportsManagement`, `site/src`, `admin/src`, `admin/services`, `admin/tmpl` and `admin/forms`.

Both site and administrator entry points use Joomla component dispatchers. `SportsManagementMVCFactory` prefers namespaced Joomla 5/6 classes and falls back to legacy SportsManagement MVC only where no native replacement exists. The native administrator and site model bases retain support for a separately configured SportsManagement database.

## Administrator status

### Fully native CRUD

The following groups have native list and edit/write stacks:

- `clubname` / `clubnames`
- `eventtype` / `eventtypes`
- `extrafield` / `extrafields`
- `season` / `seasons` for standard CRUD
- `sportstype` / `sportstypes` for standard CRUD

Season person/team assignment workflows and sport-type import/export remain separate legacy-backed special operations.

### Native list and narrow update paths

These administrator groups use native list models/views, native tables/action models and safe plural state/ordering actions:

- `leagues`
- `playgrounds`
- `positions`
- `rosterpositions`
- `teams`

Explicit native special actions currently include:

- `leagues.saveshort`
- `positions.saveshort`
- `rosterpositions.addhome`
- `rosterpositions.addaway`
- `teams.saveshort`
- `teams.copysave`

Their complex singular editors remain legacy-backed. `LEGACY_DEFAULT_VIEWS` prevents a partial native action model from being combined accidentally with an old singular edit view.

### Fully native display-only administrator stacks

These display routes no longer depend on their legacy list/view implementation:

- `agegroups`
- `close`
- `clubs`
- `currentseasons`
- `divisions`

Reusable Joomla 5/6 administrator fields exist under `admin/src/Field` for country, sport type, age group, association, federation and league level selection.

## Site foundation

`site/src/Model/SportsManagementModel.php` is the shared native frontend database-model base. `site/src/View/SportsManagementHtmlView.php` is the lightweight Joomla 5/6 frontend view base.

`SportsManagementProjectModel` / `SportsManagementProjectHtmlView` provide the native project-aware context, including project metadata, division filtering, default XML template values, saved project template configuration and `master_template` fallback.

### Native prediction context

`SportsManagementPredictionModel` owns the prediction game/member/project/template context without the former global static `sportsmanagementModelPrediction` state.

`SportsManagementPredictionReadModel` is the shared read-only result/ranking layer. It owns round/member/result queries, project-team lookup and native scoring/ranking helpers. CI rejects database-write operations in this model.

`SportsManagementPredictionHtmlView` prepares prediction game/member/project context and registers the shared `globalviews` and `predictionheading` template paths.

### Fully native site MVC stacks

The following frontend MVC stacks no longer inherit legacy SportsManagement models/views for their migrated route:

- `about`
- `close`
- `clubs`
- `teams`
- `referees`
- `predictionrules`
- `predictionheading`
- `predictionranking`
- `predictionresults`
- `predictionusers` default profile/read path
- singular `predictionuser` edit/member-write path

`predictionrules` uses the native prediction context and native scoring examples.

`predictionheading` uses the explicit view context rather than global static prediction state.

`predictionranking` uses `PredictionrankingModel`, a native view/controller and read-only templates. Rendering no longer persists corrected prediction points, and map output no longer performs external geocoding during page rendering.

`predictionresults` explicitly separates display and persistence:

- `PredictionresultsModel` is read-only and prepares project/round filters, matches, members, tip visibility, computed points, ranking and pagination.
- the Results view/templates contain no database write path and no legacy static prediction MVC calls.
- `PredictionpointsModel` is the isolated prediction-result writer.
- `predictionresults.recalculatepoints` is an explicit POST-only, CSRF-protected action and additionally requires prediction-administrator permission.
- `predictionresults.selectprojectround` is also a native POST/CSRF-protected controller action.

The Results display preserves the legacy profile privacy rule for member avatars: another member's private profile does not expose that member's configured avatar.

`predictionusers` now has a native, read-only default profile stack:

- `PredictionusersModel` validates the selected member/project context and reads profile statistics, favourite teams, champion/final4 tips and points/ranking series without write operations.
- `Predictionusers\HtmlView` uses templates under `site/src/View/Predictionusers/tmpl`, so read presentation remains isolated from member persistence.
- member and project selectors use native `predictionusers.select` / `predictionusers.selectprojectround` POST actions with CSRF protection.
- private profiles are shown only to the member or a prediction administrator; the same boundary protects profile/avatar data.
- the active native profile path no longer loads remote Chart.js. Points and ranking series use passive Joomla/Bootstrap-compatible progress rendering.

The singular `predictionuser` editor is now a separate native write stack:

- `PredictionuserModel` is read-only and resolves the editable member, project teams, groups and project-start locks.
- `PredictionmemberModel` is the only member writer and updates `#__sportsmanagement_prediction_member` through `updateObject()`.
- the writer validates the member ID and prediction ID against server-side model context rather than trusting a posted `user_id`.
- group, champion and Final4 changes are validated server-side; closed projects retain their existing competitive selections even if a crafted POST attempts to change them.
- registration date/time changes are accepted only for a real prediction administrator.
- `approved` remains read-only, matching the effective legacy persistence behavior where the editor displayed it but `savememberdata()` did not store it.
- the native edit form uses normal POST buttons, Joomla CSRF tokens and no longer depends on `Joomla.submitform`, `joomla.javascript.js` or remote Chart.js.
- only `view=predictionuser&layout=edit`, `predictionuser.save` and `predictionuser.cancel` are explicitly routed natively; `predictionusers.savememberdata` remains outside the native allowlist.

### Transitional site areas

The main remaining prediction write area is:

- `predictionentry`

It still combines prediction-entry rendering and tip persistence and should be migrated as an explicit read/write block rather than leaking writes into display rendering.

Shared `globalviews` fragments and some presentation helpers remain transitional even where the MVC stack is already native.

`jlxmlexports` remains intentionally outside passive display migration because its legacy display path triggers an export operation.

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
- `SportsManagementPredictionReadModel`
- `SportsManagementPredictionHtmlView`
- `PredictionpointsModel` as the explicit prediction-result writer
- `PredictionuserModel` as the read-only member editor context
- `PredictionmemberModel` as the explicit prediction-member writer

Legacy bridges remain migration scaffolding only for routes whose business logic still requires them.

## Validation

`.github/workflows/joomla5-6-lint.yml` validates the overall migration and builds the development installation ZIP. It covers PHP syntax, manifest/namespace checks, administrator native stacks, the native site foundation and the remaining legacy fallback inventory.

`.github/workflows/joomla5-6-site-project-context.yml` gates the native project-aware site stack and rejects legacy project/view inheritance in migrated project views.

`.github/workflows/joomla5-6-site-prediction-context.yml` gates the native prediction stack. It validates rules, heading, ranking and results; rejects direct legacy static prediction MVC dependencies; enforces read-only prediction ranking/results models; and permits prediction-result writes only through the explicit `PredictionpointsModel` writer and protected controller action.

`.github/workflows/joomla5-6-site-prediction-users.yml` gates the native prediction-users default profile. It rejects legacy prediction MVC/static calls, database writes and remote Chart.js from the native read stack, validates the profile/privacy/chart read helpers, requires CSRF on the native member/project selectors and fails if `predictionusers.savememberdata` is added to the native dispatcher allowlist.

`.github/workflows/joomla5-6-site-prediction-user-editor.yml` gates the singular native member editor. It keeps the editor model/view/template read-only, confines the database update to `PredictionmemberModel`, checks member/prediction target validation and project-team/start locks, requires POST CSRF and authorization in the controller, and rejects legacy submit JavaScript or a posted `user_id` authorization shortcut.

Static gates are not a substitute for a real Joomla runtime test.

## Remaining priorities

1. Migrate `predictionentry` as the remaining major prediction read/write stack, keeping tip persistence behind explicit CSRF/authorization-controlled actions.
2. Split the singular team/league/position/playground/roster-position edit dependencies so those write routes can become native.
3. Migrate relation-heavy administrator areas such as `teamplayers` and `projectteams` as coherent blocks.
4. Separate `cpanel` display from database initialization and maintenance side effects.
5. Migrate import/export and AJAX/JSON controller tooling.
6. Remove remaining legacy bootstrap bridges, shared legacy template assumptions and class aliases when no route needs them.
7. Add installation and route smoke tests on real Joomla 5.4 and Joomla 6.1 environments.

Until real Joomla runtime gates are green, packages from this branch remain development/test builds rather than production releases.