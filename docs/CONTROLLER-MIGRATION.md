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

These administrator groups use native list models/views and safe narrow update actions:

- `leagues`
- `playgrounds`
- `positions`
- `rosterpositions`
- `teams`
- `projectteams` default list and project-team-owned inline fields
- `teamplayers` default player/staff relation list

Explicit native special actions include the existing league/position/roster/team actions plus the safe relation-list actions documented below. Complex singular editors remain legacy-backed. `LEGACY_DEFAULT_VIEWS` prevents a partial native action model from being combined accidentally with an old singular edit view.

### Native project/team relation lists

`ProjectRelationService` is the shared read-only administrator relation service. It resolves project metadata, project-team/season-team context, project positions, divisions and playground options without rendering-time writes.

`projectteams` now uses a native default list model/view/template:

- project/team/person relations are loaded with bounded joins and correlated counts instead of per-row legacy lookup calls.
- individual-sport projects are handled through season-person relations without joining team/club-only aliases.
- `projectteams.saveshort` validates every selected project-team ID against the current project and only updates fields owned by `#__sportsmanagement_project_team`.
- division and playground IDs are validated server-side; omitted fields preserve their current value.
- native status, `is_in_score` and `use_finally` actions are POST/CSRF/permission protected.
- the native inline path deliberately does not write club/team master records or matches; those entities remain owned by their dedicated workflows.

`teamplayers` now uses a native player/staff relation list:

- the model derives project, season, season-team and person-type context server-side from the selected project-team relation.
- project-position/publication values are loaded in the main query rather than through the legacy per-row N+1 methods.
- `teamplayers.saveshort` validates each season-team-person relation before updating jersey number, market data, start points and project position.
- project-position changes update `#__sportsmanagement_person_project_position` explicitly and may fill previously empty match-player/staff position references for the validated relation.
- publish/unpublish/archive/trash validate the same relation context and require POST CSRF plus `core.edit.state`.
- the old `checkProjectPositions()` behavior that attempted `ALTER TABLE`/schema mutation from a list model is not part of the native path.

The following complex relation operations remain intentionally legacy-backed and are not in the native dispatcher allowlist:

- `projectteams.assign`
- `projectteams.matchgroups`
- `projectteams.setseasonid`
- `projectteams.set_playground`
- `projectteams.set_playground_match`
- `teamplayers.assignplayerscountry`
- `teamplayers.assignpersonsclub`

### Fully native display-only administrator stacks

These display routes no longer depend on their legacy list/view implementation:

- `agegroups`
- `close`
- `clubs`
- `currentseasons`
- `divisions`

Reusable Joomla 5/6 administrator fields exist under `admin/src/Field` for country, sport type, age group, association, federation, league level and project-division selection.

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
- `predictionentry` default/read, registration and tip-write paths

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

The singular `predictionuser` editor is a separate native write stack:

- `PredictionuserModel` is read-only and resolves the editable member, project teams, groups and project-start locks.
- `PredictionmemberModel` is the only member writer and updates `#__sportsmanagement_prediction_member` through `updateObject()`.
- the writer validates the member ID and prediction ID against server-side model context rather than trusting a posted `user_id`.
- group, champion and Final4 changes are validated server-side; closed projects retain their existing competitive selections even if a crafted POST attempts to change them.
- registration date/time changes are accepted only for a real prediction administrator.
- `approved` remains read-only, matching the effective legacy persistence behavior where the editor displayed it but `savememberdata()` did not store it.
- the native edit form uses normal POST buttons, Joomla CSRF tokens and no longer depends on `Joomla.submitform`, `joomla.javascript.js` or remote Chart.js.
- only `view=predictionuser&layout=edit`, `predictionuser.save` and `predictionuser.cancel` are explicitly routed natively; the old `predictionusers.savememberdata` task remains outside the native allowlist.

`predictionentry` is now split into explicit read and write responsibilities:

- `PredictionentryModel` is read-only. It validates published prediction projects, project rounds, optional configured match/round/project-team restrictions, member selection and match closing times.
- normal users are always rebound to their own prediction membership regardless of a crafted `uid`; prediction administrators may explicitly select another member.
- unapproved memberships cannot submit tips. Registration state is read independently from tip-entry authorization.
- per-match editability is recomputed server-side from match/result state, `closing_time` and the configured `BEGIN_OF_MATCH`, `FIRSTMATCH_OF_TIPPROUND` or `FIRSTMATCH_OF_TIPPGAME` deadline rule.
- `PredictiontipModel` is the isolated tip writer. It reloads the allowed match set from the database and only writes those rows that are still editable at save time.
- the tip writer does not trust legacy client target arrays such as `cids`, `prids`, `pids`, `ptippmode` or a posted `user_id`. It validates prediction/member/project/round targets against the model context, enforces the joker limit server-side and writes only prediction result, prediction round-result and member `last_tipp` data.
- `PredictionmembershipModel` is the isolated registration writer. The Joomla identity supplies `user_id`, while `auto_approve` comes from the loaded prediction game; neither value is accepted from the registration form.
- registration and optional tip receipts use Joomla's `MailerFactoryInterface` rather than legacy mail/bootstrap calls.
- crowd tip tendencies are withheld while an ordinary member can still edit the match, avoiding disclosure of aggregate tips before the betting deadline; prediction administrators retain the administrative view.
- `predictionentry.select`, `predictionentry.selectprojectround`, `predictionentry.register` and `predictionentry.addtipp` are explicit native POST/CSRF-protected actions.
- the native entry templates contain no legacy static prediction calls and no legacy trusted target fields. The old entry MVC/templates remain installed only as migration/fallback inventory for routes that still reference legacy files directly.

### Transitional site areas

The main prediction display/write routes are now covered by the native stack. Remaining site migration work is concentrated in other project-heavy views and shared presentation infrastructure rather than the core prediction workflow.

Shared `globalviews` fragments and some presentation helpers remain transitional even where the MVC stack is already native.

`jlxmlexports` remains intentionally outside passive display migration because its legacy display path triggers an export operation.

## Shared native infrastructure

Administrator:

- `SportsManagementAdminModel`
- `SportsManagementListModel`
- `SportsManagementAdminController`
- `SportsManagementFormController`
- `ProjectRelationService` as the read-only project/team/person relation context
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
- `PredictionentryModel` as the read-only tip-entry context
- `PredictiontipModel` as the explicit tip/result writer
- `PredictionmembershipModel` as the explicit prediction-membership registration writer

Legacy bridges remain migration scaffolding only for routes whose business logic still requires them.

## Validation

`.github/workflows/joomla5-6-lint.yml` validates the overall migration and builds the development installation ZIP. It covers PHP syntax, manifest/namespace checks, administrator native stacks, the native site foundation and the remaining legacy fallback inventory.

`.github/workflows/joomla5-6-admin-relations.yml` gates the native `projectteams` / `teamplayers` lists. It rejects legacy MVC/schema mutation and render-time database writes, validates server-side relation/project-position/division/playground targeting, confines project-team inline writes to `#__sportsmanagement_project_team`, requires POST CSRF/permissions and fails if the still-complex legacy assignment/matchgroup/playground actions enter the native dispatcher allowlist.

`.github/workflows/joomla5-6-site-project-context.yml` gates the native project-aware site stack and rejects legacy project/view inheritance in migrated project views.

`.github/workflows/joomla5-6-site-prediction-context.yml` gates the native prediction stack. It validates rules, heading, ranking and results; rejects direct legacy static prediction MVC dependencies; enforces read-only prediction ranking/results models; and permits prediction-result writes only through the explicit `PredictionpointsModel` writer and protected controller action.

`.github/workflows/joomla5-6-site-prediction-users.yml` gates the native prediction-users default profile. It rejects legacy prediction MVC/static calls, database writes and remote Chart.js from the native read stack, validates the profile/privacy/chart read helpers, requires CSRF on the native member/project selectors and fails if `predictionusers.savememberdata` is added to the native dispatcher allowlist.

`.github/workflows/joomla5-6-site-prediction-user-editor.yml` gates the singular native member editor. It keeps the editor model/view/template read-only, confines the database update to `PredictionmemberModel`, checks member/prediction target validation and project-team/start locks, requires POST CSRF and authorization in the controller, and rejects legacy submit JavaScript or a posted `user_id` authorization shortcut.

`.github/workflows/joomla5-6-site-prediction-entry.yml` gates the native prediction-entry stack. It requires a write-free display/read layer, validates server-side member/prediction/project/round targeting and deadline/joker checks in `PredictiontipModel`, validates identity/`auto_approve` registration in `PredictionmembershipModel`, requires POST CSRF for entry actions, and rejects legacy client target arrays/static prediction MVC calls from the native forms and writers.

Static gates are not a substitute for a real Joomla runtime test.

## Remaining priorities

1. Migrate the remaining complex project-team/person relation actions (`assign`, match groups, season/playground reassignment and assignment dialogs) as explicit relation services/actions rather than adding them to the new inline list models.
2. Split the singular team/league/position/playground/roster-position edit dependencies so those write routes can become native.
3. Modernize shared `globalviews` fragments and presentation helpers still used by already-native site views.
4. Separate `cpanel` display from database initialization and maintenance side effects.
5. Migrate import/export and AJAX/JSON controller tooling.
6. Remove remaining legacy bootstrap bridges, shared legacy template assumptions and class aliases when no route needs them.
7. Add installation and route smoke tests on real Joomla 5.4 and Joomla 6.1 environments.

Until real Joomla runtime gates are green, packages from this branch remain development/test builds rather than production releases.
