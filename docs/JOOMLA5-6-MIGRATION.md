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

## Training-data assignment ACL follow-up (8 November 2026)

The administrator `TrainingsdataModel` now requires an authenticated identity and delegates editing rights to Joomla's parent form-model ACL instead of consulting the unrelated legacy `com_sportsmanagement.message.<id>` asset. The season-relation ACL regression now includes training assignments, including missing identities, denied permissions and permitted edits.

## Event-type form field migration follow-up (8 November 2026)

`admin/src/Model/EventtypeModel.php` now validates the configurable Joomla media-field type before setting the event icon edit field. Invalid PHP arrays, objects, numeric identifiers and unsafe field names revert to Joomla's media field. Non-string default icon configuration is ignored without an array-to-string warning. The existing event-types MVC workflow now runs `joomla56_admin_eventtype_media_config.php` and checks the model's detailed PHPDoc.

## Joomla root and JSON frontend view error isolation (8 November 2026)

Root and JSON HTML views now use `SportsManagementViewErrorReporter`: private model errors are written to Joomla's component log, and a translated generic runtime error is raised to the visitor rather than exposing database connection details or returning a silent false page. The native JSON stylesheet still loads for healthy forms. The PHP 8.3/8.4 standalone `joomla56_site_view_error_privacy.php` regression covers private error logging, public exceptions and successful view rendering.

## Native XML/PHP extended file editor listing (8 November 2026)

`SmextxmleditorsModel` now lists only readable regular XML/PHP files with safe basenames from its expected administrator extended assets folder. Symlinks, traversal-style names and missing folders are excluded. The associated native view requires `SmextxmleditorsModel` and privately logs any model errors instead of exposing SQL messages in public/admin error pages. `joomla56_admin_extended_editor_files.php` tests file enumeration, view dispatch, log privacy and toolbar behaviour on PHP 8.3/8.4.

## Site, administrator and GCalendar input boundary follow-up (10 November 2026)

- The native frontend `ResultsController` only forwards safe, bounded Joomla layout identifiers in result-edit redirects. Invalid names, path separators, control characters and oversized values fall back to the results form layout; valid custom names remain supported.
- The administrator `GithubController` now refuses GitHub issue creation for absent or guest identities even if an unexpected ACL implementation grants `core.manage`. POST CSRF validation and existing administrator redirects remain intact.
- The GCalendar module's native helper validates the legacy site timezone/offset before creating Joomla date labels. Non-string values, malformed timezone names, NUL/control characters and excessively long settings fall back to UTC without PHP 8 warnings.
- Regression coverage was extended for frontend redirects and module timezone options, with a new isolated GitHub issue identity/CSRF test. `.github/workflows/joomla5-6-site-admin-module-input-guards.yml` lints the changed PHP, checks all four file-header tags, runs the tests on PHP 8.2/8.3/8.4 and enforces selected-method PHPDoc. The administrator GitHub issue controller is now included in that PHPDoc gate.

This is a verified, incremental compatibility batch, not a claim that all legacy site, administrator and module files have been converted. The full function-documentation inventory remains report-only until its remaining gaps are addressed, and installation/functional testing on real Joomla 5.4 and Joomla 6 sites is still required.

## Site result writes, administrator GitHub installer and calendar module (10 November 2026)

- **Site / compact result editor:** `site/src/Model/ResultsEditModel.php` now caps direct model requests at 500 selected matches and validates every edited match payload before the first database mutation. Nested values, malformed split scores and oversized split arrays are rejected rather than being converted to unintended strings or persisted. Valid partial match edits continue to use the existing result and authorization services.
- **Administrator / update helper:** The native GitHub installation controller requires an authenticated administrator identity (not a guest, even when a legacy authorization implementation grants access). It passes only bounded string update URLs to the native model. The model resets stale archive state, records extraction exceptions in Joomla's private log with generic administrator feedback, and refuses symlinked extracted update directories.
- **Site modules / calendar:** `mod_sportsmanagement_calendar` now defaults malformed legacy layout values and site timezones safely, validates the timezone consistently for calendar grid/event generation, avoids non-string custom-prefix casts, and excludes non-scalar date values without PHP 8 conversion warnings.
- **Regression coverage:** `.github/tests/joomla56_results_write_integrity.php`, `joomla56_admin_githubinstaller_guards.php` and `joomla56_calendar_module_input_guards.php` verify rejection without mutations, guest/CSRF safety, non-disclosure of archive diagnostics and malformed calendar settings. The new `.github/workflows/joomla5-6-site-admin-calendar-legacy-inputs.yml` exercises these cases and PHP syntax on PHP 8.2, 8.3 and 8.4.
- **Documentation:** Existing function PHPDocs were expanded for changed behavior, and the new result-form and timezone validators have detailed purpose, parameter and return-value descriptions. Selected-method PHPDoc CI coverage now includes the modified result edit and installer classes. Every changed first-party PHP file retains `@version`, `@author`, `@copyright` and `@license`.

**Validation scope:** Regression and static checks pass for this batch. They are not replacements for upgrade, installation and end-to-end use-case tests on real Joomla 5.4 and Joomla 6 instances. The full PHPDoc inventory remains report-only for untouched legacy files.

## JSON calendar feed and random quotes compatibility follow-up (10 November 2026)

- **Site:** The native `JsonfeedModel` treats an explicitly submitted `gcids` selection as authoritative. Missing `gcids` still supports the older `gcid` request parameter; an invalid/empty/malformed explicit `gcids` value now returns an empty feed rather than silently selecting a different calendar. Existing access restrictions and 50-calendar bounds remain in place.
- **Administrator:** The native `SmquotestxtModel` continues to list and naturally sort only readable, basename-safe packaged quote text files. If the quote module folder is removed or inaccessible while Joomla builds an edit form, the `DirectoryIterator` exception is caught and the optional selector is returned as an empty list. Symlinks remain excluded.
- **Modules:** The namespaced `RquotesHelper` now validates the Joomla site timezone before calculating daily, weekly, monthly and yearly rotation. Valid IANA regions and numerical timezone offsets remain supported; malformed arrays, objects, control characters, overlong values and invalid timezone names fall back to UTC without emitting PHP 8 type warnings.
- **Tests and CI:** New standalone tests `.github/tests/joomla56_jsonfeed_selector_precedence.php` and `.github/tests/joomla56_admin_rquotes_module_compat.php` validate these behaviours. The `joomla5-6-jsonfeed-rquotes-compat.yml` workflow runs them alongside the existing JSON selector and quote file/configuration tests under PHP 8.2, 8.3 and 8.4. It also verifies PHP syntax, required `@version`, `@author`, `@copyright`, `@license` file tags and selected named-function PHPDocs. `JsonfeedModel` was added to the PHPDoc gate.

This is an incremental migration batch. Remaining legacy MVC classes and unmodified files still require their own review, and end-to-end installation testing on actual Joomla 5.4 and Joomla 6 sites is not covered by these isolated tests.

## Native navigation route and menu-field follow-up (10 November 2026)

- **Site route boundary:** `site/src/Helper/SiteRouteHelper.php` validates an explicit `view` value in both `view()` and generic `query()` calls. Only a bounded Joomla view identifier can be routed for `com_sportsmanagement`; arrays, booleans, path separators, fragments and empty view names are rejected with `InvalidArgumentException`. Joomla `Itemid` normalization and forced component/view authority are preserved.
- **Administrator navigation selector:** `admin/src/Field/JsmmenuitemsField.php` retains historical stored menu values and localized labels while documenting their handling through Joomla's native ListField API. Options without a corresponding native frontend view remain listed for existing module settings, but should not be routed to nonexistent views.
- **Navigation module:** `modules/mod_sportsmanagement_navigation_menu/src/Helper/NativeNavigationMenuHelper.php` adds native Joomla 5/6 routes for `allprojectrounds`, `jlxmlexports`, `leaguechampionoverview`, `rosteralltime`, `statsrankingteams`, `teams`, and `teamstree`. Roster links still require a selected team. Module/database, season, round and division selectors are bounded; invalid or corrupt `cfg_which_database` values do not silently choose a different database.
- **Not yet migrated:** `jltournamenttree` is a historical module option with an existing native model but no equivalent `site/src/View/Jltournamenttree` entry. The menu helper intentionally continues to omit that link instead of guessing a replacement; the correct native view/layout must be completed separately. The existing results/matrix legacy options also require a dedicated routing review.
- **Tests and CI:** `.github/tests/joomla56_native_navigation_route_contract.php` exercises the actual administrator form field, module link generation and site router with Joomla service stubs, including invalid parameters and missing team/project state. `.github/workflows/joomla5-6-native-navigation-routes.yml` runs it with the existing unknown-view regression on PHP 8.2, 8.3 and 8.4, lints modified PHP files, checks all four required file-header tags and runs selected-function PHPDoc coverage for the changed classes.

These changes are isolated routing regressions, not end-to-end installation or upgrade tests on Joomla 5.4 and Joomla 6 sites. Unmigrated legacy view/template bridges remain on the broader migration checklist.

## Native jltournamenttree site view and navigation follow-up (10 November 2026)

- **Native site MVC:** `site/src/View/Jltournamenttree/HtmlView.php` now supplies the missing namespaced frontend view for the historical `view=jltournamenttree` URL, matching the existing `JltournamenttreeModel`. The native dispatcher can select the Joomla 5/6 view without falling back to the global legacy view. The view accepts only a selected tournament/division-league project and clamps configured bracket dimensions, fonts and CSS colors before rendering.
- **Structured presentation:** `site/src/Helper/TournamentTreePayload.php` converts the existing model's bracket objects to bounded, JSON-ready initial team pairs, ordered round labels and integer-only scores. `site/tmpl/jltournamenttree/default.php` outputs escaped headings and safe non-executable JSON, not raw SQL-derived JavaScript fragments. The new read-only `site/assets/js/sportsmanagement-tournament-tree.js` renderer uses jQuery Bracket through Joomla's Web Asset Manager, inserts labels as text, and has a scoped stylesheet. The component manifest already packages the `site/src`, `site/tmpl` and `site/assets` directories.
- **Model and diagnostics:** The old `JltournamenttreeModel` methods now have detailed PHPDocs. Its compatibility score fragment accepts only bounded nonnegative integers/null; database exceptions are logged privately with generic frontend error feedback. Previously verbose debug output (team names and score arrays) is replaced with non-sensitive counts in Joomla's log.
- **Admin and modules:** The existing `jltournamenttree` option in `admin/src/Field/JsmmenuitemsField.php` now resolves through `NativeNavigationMenuHelper::getLink()` to the restored native frontend view, preserving stored module choices without sending users to an unrelated view.
- **Coverage:** `.github/tests/joomla56_tournament_tree_payload.php` and `.github/tests/joomla56_tournament_tree_native_view.php` exercise JSON escaping, safe scores, private diagnostics, native view preparation, rendered HTML, asset registration and invalid configuration. The earlier native-navigation route regression now includes the restored tournament destination. `.github/workflows/joomla5-6-native-tournament-tree.yml` runs these tests, PHP syntax, JavaScript syntax, all four PHP file-header tags and selected named-function PHPDocs under PHP 8.2, 8.3 and 8.4.

**Remaining:** The legacy tournament files remain for compatibility and template overrides; this batch does not promise feature-for-feature visual equivalence or end-to-end behaviour in Joomla 5.4/6 installations. Related legacy results/matrix display paths and other unported views still require dedicated migrations and real Joomla browser tests.

## Native matrix display, SQL scoping and navigation configuration (10 November 2026)

- **Site / matrix MVC:** `MatrixModel` now limits requested round metadata to the selected project, rejects match-result requests for a different project ID and applies the requested division (or immediate child) as a real SQL filter instead of a non-filtering LEFT JOIN. Matrix query failures go to Joomla's private error log with generic frontend feedback.
- **Site / native HTML:** `site/tmpl/matrix/default.php` replaces the historical matrix default that depended on removed `Factory::getApplication()` and concatenated unescaped HTML. The native `Matrix/HtmlView.php` no longer prioritizes the old default template. The new `MatrixPresentationHelper.php` validates and indexes project-team IDs, normalizes scores/decision results and preserves up to 20 fixtures per team pair as plain text. All team names, project labels, division labels and generated URLs are HTML-escaped in the native matrix. Optional roster links, matrix visibility, help text and division sections are supported. Stored team-name selectors are restricted to known public fields.
- **Current presentation limits:** The new default matrix displays a responsive plain-text cross-table; legacy per-match icons, Russian matrix formatting, rich result links, specialized export buttons and custom historical overrides have not yet been migrated with full feature parity. Existing old templates remain available on disk for explicit compatibility work, but are no longer used for the native matrix default.
- **Administrator / module:** The native `JsmmenuitemsField` keeps Joomla XML-defined options and labels, skipping duplicate bundled options. The navigation module checks the raw `cfg_which_database` URL parameter before routing, and uses its validated stored database selector only if the request parameter is absent. Corrupt arrays, strings and booleans are no longer silently converted by `getInt()` into another database connection.
- **Tests:** `.github/tests/joomla56_matrix_native_template.php` covers encoded names, links, division grouping, multi-match scores, decision and cancellation handling; `joomla56_matrix_project_scope.php` checks bound SQL identifiers, real division filters and private diagnostics. `.github/tests/joomla56_native_navigation_route_contract.php` now tests malformed raw URL selectors. `joomla56_admin_navigation_options.php` covers XML option precedence. The native matrix and navigation workflows lint PHP, verify all four PHP file tags, check named-function PHPDocs and run on PHP 8.2, 8.3 and 8.4.

End-to-end Joomla 5.4/6 installation, browser rendering and data-migration tests remain necessary. Additional results-ranking and results-matrix legacy combinations still require dedicated Joomla MVC migration.

## Native combined resultsmatrix frontend view (10 November 2026)

- **Site model:** `site/src/Model/ResultsmatrixModel.php` delegates read-only fixtures and team/division lists to the already scoped native `MatrixModel`. Missing project selections short-circuit before any queries. Result rows and the cross-table use the same bounded project/round/division database selection.
- **Site view and templates:** `site/src/View/Resultsmatrix/HtmlView.php` and `site/tmpl/resultsmatrix/default.php` replace the previously missing native combined view. Results are normalized by `MatrixPresentationHelper::fixtures()` with bounded decimal scores, and rendered in `site/tmpl/resultsmatrix/default_results.php`. The combined page reuses the existing native escaped `site/tmpl/matrix/default.php`, not the older global resultsmatrix/matrix scripts. Both sections remain visible via accessible heading links, ordered by the saved `what_to_show_first` menu option, without legacy jQuery tabs.
- **Menu/module compatibility:** The original `resultsmatrix` menu XML metadata is retained under `site/tmpl/resultsmatrix/default.xml`; old component navigation options continue to use `view=resultsmatrix` and are now backed by the new namespaced view. Existing navigation contract tests cover this view.
- **Tests:** `.github/tests/joomla56_resultsmatrix_combined_data.php` verifies missing-project guards, project/division delegation and safe score normalization. `.github/tests/joomla56_resultsmatrix_native_view.php` renders both real layouts and checks HTML escaping, saved panel ordering, missing projects and incorrect model injection. the extended `.github/workflows/joomla5-6-native-matrix.yml` runs both under PHP 8.2, 8.3 and 8.4 with existing matrix regressions, syntax checks, required file tags and selected function-level PHPDocs.

**Known limitations:** This is a safe read-only combined fixture list and cross-table. It does not yet reproduce legacy maps, live ticker controls, event icons, interactive tabs or full rich results formatting. The separate `resultsranking` view and installation/browser tests on actual Joomla 5/6 installations remain pending.

## Native combined resultsranking site view (10 November 2026)

- **Native Joomla MVC:** `site/src/View/Resultsranking/HtmlView.php` now supplies the missing namespaced `view=resultsranking` frontend presentation using the existing `ResultsrankingModel`. The matching native project/view dispatcher route and administrator/module menu option remain unchanged. The view opts out of historical presentation globals, jQuery tabs and JCE MediaBox.
- **Project-scoped sporting calculation:** `ResultsrankingModel::getCombinedData()` shares a validated project and project-owned round selection between `RankingModel` + `RankingCalculationAdapter` and the native `MatrixModel`. It rejects missing/foreign projects before loading matches or rankings, and falls back to an available project round if the supplied round belongs to another project. The adapter retains the legacy sport-specific ranking engine; this batch does not attempt to reimplement competition scoring rules.
- **Safe table output:** `site/src/Helper/CombinedRankingPresentationHelper.php` converts the adapter's current table into bounded display rows (division, rank, team, played, wins, draws, losses, points); unrecognized or nonnumeric statistic fields are replaced with dashes. The native `site/tmpl/resultsranking/default.php` and `default_ranking.php` display the table with escaped labels, while `default_results.php` reuses the native escaped `resultsmatrix` fixture sublayout. Both sections appear as accessible HTML, with the saved `what_to_show_first` order honored.
- **Menu metadata:** `site/tmpl/resultsranking/default.xml` preserves the old Joomla menu definition and its database, season, project, division and round selectors. The native navigation regression includes the restored `resultsranking` route.
- **Regression/CI:** `.github/tests/joomla56_resultsranking_model_contract.php` checks foreign-project short-circuiting, allowed-round fallback, database/division selectors, calculated standings and malicious score handling. `.github/tests/joomla56_resultsranking_native_view.php` renders both real native templates with escaped team/project/round labels, checks stored section order and verifies missing/wrong-model handling. `.github/workflows/joomla5-6-native-resultsranking.yml` runs PHP 8.2, 8.3 and 8.4 syntax/metadata checks, model and view contracts and selected function PHPDoc tests.

**Pending:** The separate native combined resultsranking table is intentionally read-only. Legacy interactive table variants, map integration, export controls, per-event icons and full display parity need later Joomla browser tests and dedicated migrations. The sport-specific ranking adapter still uses a historical calculation engine and requires real-world fixture coverage.

## Native resultsrankingmatrix triple frontend (10 November 2026)

- **Joomla 5/6 MVC:** `site/src/Model/ResultsrankingmatrixModel.php` extends the native `ResultsrankingModel` and `site/src/View/Resultsrankingmatrix/HtmlView.php` supplies the previously absent namespaced frontend view for the existing `view=resultsrankingmatrix` navigation target. This view is read-only and does not initialise global legacy presentation helpers, obsolete tab JavaScript or media lightboxes. The underlying ranking calculation still delegates to the historical sport-specific engine via `RankingCalculationAdapter`.
- **Single, consistent dataset:** `ResultsrankingModel::getCombinedData()` now also returns the project-scoped native `teams` and `results` records already fetched for its fixture list. The triple model reuses them for the matrix instead of fetching matches again. Both the ranking and the matrix use a round ID verified against the current project's round list; a selected positive division is checked against published divisions returned by the project-scoped `MatrixModel::getDivisions()` before the ranking calculation, rejecting unrelated division selectors rather than allowing them to reach the adapter.
- **Native layouts and menu:** `site/tmpl/resultsrankingmatrix/default.php` renders all three HTML sections with links to their section anchors. `default_ranking.php`, `default_results.php` and `default_matrix.php` reuse the existing escaped Joomla ranking, fixtures and cross-table templates through constant component-relative paths. The new `default.xml` offers database/season/project/division/round choices and a three-choice `what_to_show_first` list: 0 ranking first, 1 results first, 2 matrix first. Existing navigation links to `resultsrankingmatrix` now have an actual native Joomla View/Model/Template target.
- **Regression coverage:** `.github/tests/joomla56_resultsrankingmatrix_model_contract.php` checks project/round/division validation, score handling, the shared query count and published-division metadata. `.github/tests/joomla56_resultsrankingmatrix_native_view.php` renders all real native PHP templates and tests escaped project/team/round labels, link encoding, all three menu orders, missing-project guards, wrong-model rejection and XML menu layout structure. The existing `joomla56_resultsranking_model_contract.php` now also prevents calculating standings for foreign division IDs. The navigation contract and selected named-function PHPDoc inventory include this route and model/view.
- **CI verification:** `.github/workflows/joomla5-6-native-resultsrankingmatrix.yml` passed on PHP 8.2, 8.3 and 8.4 (GitHub Actions run 42896153562). The existing native resultsranking workflow passed after the shared model adjustment (run 42896115755). Native navigation routing (run 42896115391), function PHPDocs (run 42896115042) and global PHP metadata audit (run 42896153435) also passed for the corresponding code.

**Not yet validated:** End-to-end installation/browser operation with Joomla 5.4/6, rich interactive three-view tabs, enhanced match report/event icons, full sport-specific legacy table variations, map/export functionality and historical template override parity. The native resultsrankingmatrix layout provides secure, accessible read-only results, standings and matrix; it does not claim complete legacy visual or functional parity.

## Native UEFA five-year coefficient presentation (10 November 2026)

- **Site frontend:** Added `site/tmpl/uefawertung/default.php` for the existing namespaced `Uefawertung/HtmlView.php`. The previously selected historical view template relied on `sportsmanagementHelper`, `sportsmanagementHelperHtml`, `JSMCountries`, an obsolete image/modal popup and old global template-path registration. The new Joomla 5/6 layout renders an accessible responsive country/season/total table using only plain data, a native year-select and HTML-escaped strings. It preserves the view's active menu title and URL.
- **Menu layout:** `site/tmpl/uefawertung/default.xml` provides the native Joomla menu item definition and retains the historic `coefficientyear` field. The isolated view regression also verifies the XML field when DOMDocument is available.
- **Year selection:** `Uefawertung/HtmlView.php` now validates the selected season against the native model's available seasons before requesting coefficient points. Invalid, malformed or unrelated season selectors produce an empty dataset instead of being forwarded to SQL. Year labels/IDs are bounded and escaped in the view. The original `UefawertungModel` continues to provide aggregated five-season totals and private exception logging.
- **Security and limits:** Country labels, numeric coefficient cells, season headings, menu titles and URLs are encoded for HTML. A maximum of 150 offered coefficient years and 300 displayed country rows bounds response size. Historical flag icons and modal images remain intentionally omitted until migrated to native media helpers. The read-only table does not claim complete visual parity with the old layout.
- **Tests:** `.github/tests/joomla56_uefa_native_view.php` exercises the real native template with malicious country/year/menu strings, valid and invalid season choices and an incompatible MVC model. `.github/tests/joomla56_site_uefa_error_privacy.php` continues verifying that database SQL details remain private. The new `.github/workflows/joomla5-6-native-uefa-table.yml` runs file lint and four header tags, both regression scripts and selected function PHPDoc checks across PHP 8.2, 8.3 and 8.4.

Actual installation/browser compatibility with Joomla 5.4 and 6, migration of remaining ranking-all-time/tournament-bracket layouts and historical presentation overrides remain pending.

## Read-only frontend project selection and administrator write guards (14 November 2026)

- The native frontend project model now checks the selected project when resolving divisions and descendants. Invalid or foreign-project division roots cannot influence the team list. Favourite-team IDs are parsed strictly rather than partially cast from malformed database values.
- Resolving the effective frontend round no longer updates `current_round` during an anonymous page view. Automatic round selection remains available as display data; persistent updates belong to an explicit, authorised administrator operation and should be tested separately before final release.
- The administrator Project model now checks component permissions for direct calls to quick save, league-champion toggles, project copying, deletion and dependent-record cleanup. Bulk project IDs are validated without partially converting untrusted strings or arrays.
- Independent PHP 8.3/8.4 regressions cover project ownership, read-only round reads and direct administrator write denial. Additional integration tests on real Joomla 5 and Joomla 6 installations are still required.

## Further native frontend and backend hardening (14 November 2026)

- The native iCalendar model now binds project, team, division and playground selectors. It constrains season-team joins to the requested project and season, and constructs route slugs and display times in PHP without MySQL-specific CONCAT_WS or DATE_FORMAT calls. Its exception logs contain only the exception class, not private SQL or database credentials.
- The native site ImagehandlerModel now documents all named methods and restricts the selected files to supported images located within the canonical component media root. File and directory symlinks cannot escape this root. Invalid folder selections fail closed instead of showing the media-root listing.
- The administrator Projects list model now turns failed season/league lookups into a generic Joomla model error and a sanitized class-only log message, preserving private database diagnostics.
- Isolated regressions cover these changes under PHP 8.3 and PHP 8.4. They do **not** replace installation, web server, upgrade or actual Joomla UI integration tests, which are still outstanding.

## Round reader, backend image model and module privacy (14 November 2026)

- The native `ProjectRoundReader` now computes selected rounds without persisting administrator-managed project state during frontend reads. The legacy `persist` argument remains accepted but does not enable writes. MySQL-only `INTERVAL`/`CONCAT_WS` expressions were replaced with bound UTC cutoff parameters and PHP slug generation.
- Administrator image-field assignments now validate component edit permission, record IDs and local image filenames. Image browsing resolves real directory and file paths, preventing symlink escapes, and model exceptions are not reflected in user responses or logs.
- The UEFA coefficient and TrainingsData module helpers now log only database exception classes. UEFA database selectors are validated before a resolver call, eliminating malformed-selector fallbacks to unintended data sources.
- Regression checks cover these changes on PHP 8.3 and 8.4; installed Joomla integration checks and release migration work remain outstanding.

## Native dispatchers and repeatable calendar assets (14 November 2026)

- The site and administrator component dispatchers now document each named routing method with its task, input constraints, return value and migration fallback behaviour. Their file headers retain `@version`, `@author`, `@copyright` and `@license`.
- The native GCalendar module dispatcher registers CSS/JavaScript assets only if absent from the Joomla WebAssetManager registry, then activates them for every rendered calendar instance. The module-specific script options remain keyed to the Joomla module ID.
- The all-PHP-metadata workflow now runs the named-function PHPDoc gate and PHP syntax lint on the two component dispatchers and GCalendar dispatcher, plus a standalone multi-instance asset reuse regression. Full Joomla 5.4 and Joomla 6 installation/browser tests are still pending.

## Administrator MVC factory PHPDoc and extension-name type safety (14 November 2026)

- All fifteen methods of the native-first administrator `SportsManagementMVCFactory` now describe their purpose, routing constraints, parameters and return values, including its retained site/admin legacy controller, model, view and template lookup paths.
- The legacy extension directory list rejects non-string values before regular-expression validation. Invalid array/object records no longer trigger PHP 8 warnings or conversion errors when resolving administrator MVC fallbacks.
- A framework-independent regression exercises the extension list with valid and malformed registry values. The all-PHP-metadata workflow also performs syntax lint and the descriptive PHPDoc audit on the MVC factory; installed Joomla integration checks remain outstanding.

## Site SEF router method documentation (14 November 2026)

- The modern site router now documents its constructor and all named helper methods, including menu candidate selection, allowed route views, positional parameters and scalar input handling. Its existing Joomla 5/6 URL-building behavior remains unchanged.
- The migration metadata workflow checks the router's descriptive function PHPDoc and PHP syntax. Existing SEF route regression workflows remain in place; installed Joomla/browser testing is still outstanding.

## Administrator League image selector: missing document (14 November 2026)

- The shared `ImageSelectHelper` previously resolved a separate `SiteApplication` even when an administrator field was rendered. The backend application could therefore receive a null document from that unrelated frontend instance, leading to `method_exists(): Argument #1 must be object|string, null given` when a league form used the image selector.
- The helper now uses the actual Joomla 5/6 running application (`Factory::getApplication()`), restricted to site and administrator clients. Inline image-select JavaScript is registered only when an HTML document exists; missing or non-HTML documents no longer cause a fatal exception. Frontend modal paths still point into the administrator image handler, while backend modal paths use the active backend base URL.
- The helper and both native/compatibility administrator fields now have descriptive method PHPDoc and retain the required four file header tags. A standalone test covers null documents, active administrator documents, both field classes, frontend upload URLs and unsupported clients. The all-PHP-metadata workflow runs it with lint and PHPDoc checks. Installation tests on Joomla 5 and Joomla 6 remain required.

### Image selector CI follow-up (14 November 2026)

- Reattached descriptive PHPDoc immediately before the JavaScript builder method and kept a distinct PHPDoc on the shared current-application resolver. The old helper migration gate now permits the Joomla 5/6-supported `Factory::getApplication()` specifically for this cross-client image selector, while keeping its original guard for client-specific helpers.
- The Clubicons website-link security test now recognises the typed `is_string($website)` validation call already used by the native module. No Clubicons production logic was changed.

## Shared image selector and multi-instance Ranking stylesheet (14 November 2026)

- The administrator's historical `ImageselectField.php` now loads the canonical `ImageSelectField.php` instead of declaring a second PHP class that differs only by case. Both legacy entry points can be loaded together without class redeclaration errors, and copied/original image fields retain their submit names.
- The shared site image folder helper now rejects non-string, non-integer, empty, traversal, malformed and oversized legacy event folder names before building upload/delete directory paths. Explicitly mapped league, team and club folders retain their original paths; safe unknown event folders continue to resolve below `events/`.
- The native Ranking module activates its stylesheet on every render but registers it only once per Joomla WebAssetManager document, preventing duplicate asset declarations when two Ranking modules appear on the same page. A dedicated PHP regression covers two registrations, and the existing image selector regression now tests both case variants in one process plus malformed folder types.
- New CI assertions lint the changed PHP files and verify descriptive PHPDoc; Joomla 5/6 installation and browser integration testing remain pending.

## Native administrator League editor privacy and script reuse (14 November 2026)

- The native administrator league edit view now documents all six named methods, including load/bind behavior, optional forms, asset registration and application resolution. Joomla form factory failures and model error strings can include SQL statements; these are no longer exposed in backend alerts or unhandled model exceptions. Logs retain exception classes or model error counts only, while the UI displays a generic translated warning.
- League editor JavaScript is registered only once per HTML document, retaining its `core` and `form.validate` dependencies for repeated editor renders.
- A PHP 8.3 CI regression covers optional forms that throw SQL-bearing exceptions, model error redaction, and two script activations on the same Joomla document. Full installed Joomla UI testing is still pending.

### CI follow-up: League and Ranking view contracts (14 November 2026)

- The isolated League view test now uses the untyped return signature of the Joomla-compatible base view stub, avoiding a PHP method-declaration mismatch unrelated to the production code. The older League workflow now checks WebAssetManager `assetExists`, `registerScript` and `useScript` instead of the removed `registerAndUseScript` call.
- A historical Ranking workflow had an incorrectly escaped PHP namespace grep that could never match the actual single-backslash `use` statement. It now verifies the real namespace spelling.

## Backend Matches: ProjectModel protected container fix (14 November 2026)

- The Matches administrator view uses the project model to read the selected project's metadata and `backend_matches` template settings. In `admin/src/Model/ProjectModel.php`, the static database resolver previously called `self::backendApplication()->getContainer()` (line 907), which invokes Joomla's protected `CMSApplication::getContainer()` and causes a fatal error. The model now obtains `DatabaseInterface` from the public Joomla `Factory::getContainer()`, validates its type and preserves the SportsManagement database-selector behavior.
- The application-container CI scan now also rejects calls via `self::backendApplication()` and similar static resolver patterns. A standalone PHP test exercises the public database lookup, a Matches-style `getProject()` call, the league/season selector and invalid container services; it is integrated into the all-PHP-metadata workflow with PHP lint and function PHPDoc validation.
- No database migrations are required for this correction. Validation on an installed Joomla 5 or Joomla 6 administrator remains necessary.

### Updated legacy CI contracts for ProjectModel database resolution

Three older Joomla 5/6 workflow gates (project/playground models batch 32; compatibility batches 680 and 665) still required the now-removed protected `backendApplication()->getContainer()` call or banned every `Factory::` use. Their checks have been updated to require exactly one public `Factory::getContainer()->get(DatabaseInterface::class)` access, continue rejecting deprecated `Factory::getApplication()`, and explicitly forbid the protected application container call. Unrelated project, Ical, module and playground checks remain intact.

## Native backend Matches: repeatable assets and safe diagnostics (14 November 2026)

The administrator Matches view now registers its fixed form CSS and matches.js only when absent from the shared HTML WebAssetManager; it activates both on every render and skips non-HTML documents. The original core JavaScript dependency is preserved. The Matches view no longer exposes raw model errors, which may contain internal SQL or database details: it logs only their count and displays Joomla's generic translated message. All nine named view methods now have descriptive PHPDoc. A PHP regression tests two renders, missing/non-HTML documents and SQL-bearing error records, and the all-PHP-metadata workflow runs that test and PHP lint/PHPDoc checks.
\n## Club history, administrator action logs and module navigation (14 November 2026)\n\n- **Site:** The native ClubHistoryPresentationHelper now has detailed PHPDoc on all seven named methods, including return contracts and recursion safeguards. Successor histories place nested predecessor lists inside the associated parent list item, rather than after a closed list item; link labels remain HTML-escaped.\n- **Administrator:** ActionLogHelper now validates complete positive entity and actor IDs before recording Joomla action logs. Malformed imported arrays, booleans, partially numeric identifiers and integer overflow are rejected rather than being partially cast and attributed to another record or user. Positive decimal strings, including leading zeros, are supported. Both named methods are documented.\n- **Modules:** The native Navigation Menu helper validates the stored start view and Joomla Itemid before exporting template data. Invalid arrays, paths and partial numbers fall back to safe defaults; saved native view names and legitimate zero-padded menu IDs continue to work. Both new normalisers include descriptive PHPDoc.\n- **Regressions:** The framework-independent `.github/tests/joomla56_club_history_log_navigation_regression.php` exercises nested club markup and cyclic relations, valid and invalid action-log IDs, and navigation parameter normalisation. The all-PHP-metadata workflow lints the touched PHP and runs the named-function documentation audit. Installed Joomla 5.4/6 integration tests remain outstanding.\n\n## Native match events, extra-field copy paths and New Project log privacy (14 November 2026)\n\n- **Site:** The MatchEventPresentationHelper now documents all ten methods with purpose, input and output contracts. The public render boundary drops non-object match events, event types and substitutions before typed callbacks and rendering; missing project-team IDs no longer absorb events stored against team zero. Existing event labels and person-name escaping remain intact.\n- **Administrator:** The ExtraFieldsSaveHelper increments only existing, completely decimal save-as-copy counters within PHP's integer range. Missing, malformed and overflowing counters are left unchanged; literal zero directories, empty segments and trailing slashes are preserved. The copy-path method has expanded descriptive PHPDoc.\n- **Modules:** The New Project article writer no longer logs raw Joomla content-model exception messages, which can include SQL queries, paths or credentials. Its dedicated logging method records only the exception class and validated project ID, while retaining the generic AJAX error contract and detailed PHPDoc.\n- **Tests:** `.github/tests/joomla56_site_admin_module_event_paths_logs.php` covers malformed event lists, zero team IDs, valid/invalid copy counters and SQL-bearing exception privacy, and is integrated with PHP lint and named-method documentation checks in the all-PHP-metadata workflow. Complete Joomla 5.4/6 installation and browser validation remains necessary.\n\n### CI compatibility contract follow-up (14 November 2026)\n\n- The Club controller now reads posted club data via `getApplication()->getInput()`, avoiding its legacy `$this->input` property.\n- Four older GitHub Actions contracts were realigned with the already-active Joomla 5/6 implementation: they permit preserved contributor attribution, validate the New Project database selector without collapsing valid nonzero selections, require Joomla 5.4-compatible `createQuery()`, and test the Nominatim/HTTP image helper separation from ClubModel. The club view selector check also recognises the actual multiline `HTMLHelper::_('select.genericlist')` usage.\n- The extra-field error fixture now rejects SQL error details and credentials in application logs while checking that error classes are retained; its previous requirement to log a database password conflicted with the production privacy guard.\n- These changes correct stale CI expectations rather than removing the functional PHP lint and regression checks.\n
## Published project navigation, installer error privacy and Count Record (14 November 2026)

- **Site:** ProjectNavigationHelper now limits previous/next routes to published projects in the same league and rejects oversized project names. The existing parameterized name/ID ordering and platform-neutral ID:alias slugs remain intact.
- **Administrator:** GithubinstallModel handles thrown downloader failures as well as extraction failures, resets stale extraction state, logs only exception classes and displays generic Joomla messages instead of exposing internal archive URLs, tokens or paths. The existing controller CSRF and administrator rights checks are preserved.
- **Modules:** CountRekordHelper now distinguishes true zero match counts from null, false, malformed and overflowing COUNT(*) results. Unavailable counts suppress module output and emit sanitized diagnostics rather than incorrectly reporting target completion.
- **Regressions:** Added \`.github/tests/joomla56_project_navigation_visible_count.php\` and expanded GitHub installer tests for credential-bearing exceptions. The all-PHP-metadata workflow lints the changes and checks descriptive PHPDoc. Real Joomla 5.4 and Joomla 6 installation/browser verification is still pending.

## Extended site form paths, administrator image import and training rows (14 November 2026)

- **Site:** The frontend ExtendedFormHelper now verifies that the resolved XML path stays inside the component's real extended-form asset directory, rejecting external symlink targets as the existing administrator helper already does. The extended-form fixture covers this case.
- **Administrator:** The SmimageimportsController now rejects absent Joomla identities before ACL checks and never echoes raw model error text to administrators. SmimageimportModel validates and deduplicates at most 100 positive selected picture IDs without nested-value integer coercion; runtime failures are reported using a generic translated error and sanitized exception-class server logging. Repeated PHPDoc blocks have been consolidated into detailed descriptions.
- **Modules:** TrainingsDataHelper now filters non-object query rows, replaces nested place/note values with empty text, and defaults malformed numeric training times/weekdays to zero before rendering. The existing PHP 8.3/8.4 regression test exercises these cases.
- **Follow-up:** Native Joomla 5.4/6 installation/browser validation and a wider migration audit remain outstanding.

### Administrator image-import input/runtime regression (14 November 2026)

The image-import model now rejects nested, non-scalar and oversized picture names, folder names, destination directories and archive filename parameters without PHP 8 array-to-string warnings. A framework-free regression fixture covers administrator CSRF and permission checks, strict selection IDs, malformed POST fields, sanitized network exceptions and safe controller messages. The all-PHP-metadata GitHub Actions gate runs the fixture, PHP lint and named-function documentation checks.

## Backend match date save: missing global sportsmanagementHelper (14 November 2026)

A real administrator save attempted to instantiate the legacy `JSMModelAdmin` and failed at `admin/libraries/sportsmanagement/model.php:122` because `sportsmanagementHelper::getDBConnection()` was not loaded. All three legacy base model constructors now use the namespaced `LegacyModelDatabaseHelper`, which obtains Joomla's default database via DI and retains the verified external database selector. The legacy base save code uses the native date, action-log and extra-field helpers instead of the removed global class. The historical `admin/models/match.php` routes its match dates, timestamps and project-timezone conversion through `SportsManagementDateHelper`, so ordinary saves and quick-date edits no longer depend on that global class. An isolated regression instantiates all legacy model variants and the legacy match model without defining `sportsmanagementHelper`; it verifies date conversion and the configured database selector. Complete Joomla 5.4/6 installed-browser validation is still necessary.

### Legacy match PHP file-header and compatibility gate correction (14 November 2026)

The canonical PHP opening tag in `admin/libraries/sportsmanagement/model.php` was corrected from uppercase `<?PHP` to `<?php` for the strict file-level PHPDoc validator. Older compatibility workflows are being updated to reflect the already-migrated Joomla administrator identity, component database resolver and current PHP date-save paths, including robust shell quoting. The isolated legacy model/date runtime test remains in GitHub Actions.

## Backend round matches: missing Mannschaft selector options (14 November 2026)

When games have missing division assignments or are played across divisions,
`default_4_matches.php` previously requested an absent `teams_` array key,
while the native Matches view had built `teams_0` (or another division key).
All three selectors (home team, away team and team referee) now use the
same `HtmlView::teamOptionsForMatch()` resolver as the view's list builder.
The view always provides `teams_0` with the complete set of project teams;
division-specific lists are used only when both sides share a valid division.
Missing, incomplete or malformed division lists fall back to full project
teams, preserving team labels and previously selected project-team IDs.

The existing Matches view regression now exercises NULL division IDs, matches
across divisions, shared divisions, missing list keys and malformed options.
The central PHP metadata workflow lints the legacy template and runs the
regression alongside the Matches asset/privacy tests. A real installed Joomla
administrator round-list smoke test remains outstanding.

## Club editing: invalid SportsManagement image folder type (14 November 2026)

The club edit form could throw `Invalid SportsManagement image folder type` from `ImageSelectHelper::legacyEventFolder()` because `ClubModel::getForm()` dynamically changed Joomla media inputs to the SportsManagement `imageselect` field without setting `targetfolder`. The field then passed an empty type into the strictly validated folder mapper. Each club logo and home/away kit field now receives an explicit command-safe folder alias plus its existing media directory, in both `admin/forms/club.xml` and the native `ClubModel`. The dedicated `clubkits_home`/`clubkits_away` aliases map to the actual `clubs/trikot_home` and `clubs/trikot_away` directories; older `clubs_trikot_home`/`clubs_trikot_away` mappings are retained for legacy callers. Existing folder traversal and malformed input rejection stays in place. CI tests both the administrator form under `imageselect` and `media` options and actual backend upload/browse links.

## Administrator player and league image folder selection (14 November 2026)

The player edit model switched picture fields to the configured imageselect field without setting targetfolder; absent media configuration also produced a field type of "0". PlayerModel and its XML now use targetfolder="persons" while preserving the native Joomla Media directory and placeholder, and unknown/blank tool settings safely fall back to Joomla's "media" field. The league picture form now explicitly declares targetfolder="leagues" for custom selectors. A self-contained PHP 8 regression checks both configured media modes, absent/malformed settings, form-load failure, and both XML files; it runs in the all-PHP-metadata workflow.
