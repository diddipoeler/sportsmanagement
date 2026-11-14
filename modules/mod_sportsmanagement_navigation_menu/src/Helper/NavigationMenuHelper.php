<?php
/**
 * Shared data and compatibility helper for the SportsManagement navigation menu module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementNavigationMenu\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

class NavigationMenuHelper
{
    private Registry $params;
    private CMSApplicationInterface $app;
    private DatabaseInterface $db;
    private int $projectId = 0;
    private int $teamId = 0;
    private int $divisionId = 0;
    private int $roundId = 0;
    private ?object $project = null;
    private ?array $teamOptions = null;

    /**
     * Initialise the navigation helper and optional Joomla dependencies.
     * Uses supplied params, site application and database when available
     * and derives selected navigation state for existing legacy callers.
     * @param Registry|null $params Optional module configuration.
     * @param CMSApplicationInterface|null $app Optional active site application.
     * @param DatabaseInterface|null $db Optional database connection.
     * @return void
     */
    public function __construct(
        ?Registry $params = null,
        ?CMSApplicationInterface $app = null,
        ?DatabaseInterface $db = null
    ) {
        $this->params = $params ?? new Registry();
        $this->app = $app ?? self::siteApplication();

        if ($db !== null) {
            $this->db = $db;
        }

        if ($params !== null) {
            $this->initialiseState();
        }
    }

    /**
     * Configure native module navigation data for the current request.
     * Resolves the requested SportsManagement database, reads project
     * and team state and returns the helper plus selectors required by
     * the Joomla module layout. Malformed external database selectors
     * produce an empty payload so the native dispatcher can safely skip
     * rendering instead of silently changing the database connection.
     * It also validates the configured start view and optional Joomla menu
     * item identifier without lossy PHP casts or array conversion warnings.
     * @param Registry $params Navigation module configuration.
     * @param CMSApplicationInterface $app Site application context.
     * @param DatabaseInterface $joomlaDatabase Base database connection.
     * @return array Prepared navigation template data.
     */
    public function getData(
        Registry $params,
        CMSApplicationInterface $app,
        DatabaseInterface $joomlaDatabase
    ): array {
        $this->params = $params;
        $this->app = $app;
        $selector = self::databaseSelector($params->get('cfg_which_database', 0));

        if ($selector === null) {
            // A malformed legacy setting must never become a different
            // database connection through lossy PHP integer conversion.
            return [];
        }

        $this->db = SportsManagementDatabaseResolver::resolve(
            $joomlaDatabase,
            $selector
        );
        $this->initialiseState();

        $defaultView = self::normaliseDefaultView($params->get('project_start', 'ranking'));
        $defaultItemId = self::normaliseMenuItemId($params->get('custom_item_id', 0));

        return [
            'helper' => $this,
            'seasonselect' => $this->getSeasonSelect(),
            'leagueselect' => $this->getLeagueSelect(),
            'projectselect' => $this->getProjectSelect(),
            'divisionselect' => $this->getDivisionSelect(),
            'teamselect' => $this->getTeamSelect(),
            'defaultview' => $defaultView,
            'defaultitemid' => $defaultItemId,
        ];
    }

    /**
     * Validate the configured default SportsManagement navigation view.
     *
     * Preserves simple lowercase native route names while rejecting arrays,
     * objects, path separators, padded values and oversized identifiers.
     * Malformed configuration falls back to the existing ranking start page.
     *
     * @param mixed $raw Saved project_start module setting.
     * @return string Safe route view identifier, or ranking as a fallback.
     */
    private static function normaliseDefaultView(mixed $raw): string
    {
        return is_string($raw) && strlen($raw) <= 64
            && preg_match('/^[a-z][a-z0-9_]*$/D', $raw) === 1
            ? $raw
            : 'ranking';
    }

    /**
     * Resolve a Joomla menu item ID without partially converting bad input.
     *
     * Zero disables the custom menu item; positive integers and fully decimal
     * zero-padded strings retain their expected value. Rejects bools, floats,
     * arrays, objects, negative IDs, text suffixes and integer overflow.
     *
     * @param mixed $raw Untrusted custom_item_id module parameter.
     * @return int Valid Joomla Itemid, or zero when the setting is invalid.
     */
    private static function normaliseMenuItemId(mixed $raw): int
    {
        if (is_int($raw)) {
            return max(0, $raw);
        }

        if (!is_string($raw) || $raw === '' || strlen($raw) > 24
            || preg_match('/^[0-9]+$/D', $raw) !== 1) {
            return 0;
        }

        $digits = ltrim($raw, '0');

        if ($digits === '') {
            return 0;
        }

        $id = filter_var($digits, FILTER_VALIDATE_INT);

        return $id !== false && $id >= 0 ? $id : 0;
    }

    /**
     * Validate the native module's primary or external database selection.
     *
     * Accepts only zero or one, including decimal strings with optional
     * leading zeros. Rejects arrays, booleans, negative, floating-point
     * and partially numeric values before the Joomla database resolver
     * can access an unintended configured external connection.
     *
     * @param mixed $raw Saved cfg_which_database module parameter.
     * @return int|null Valid selector, or null for malformed configuration.
     */
    private static function databaseSelector(mixed $raw): ?int
    {
        if (is_int($raw)) {
            return $raw === 0 || $raw === 1 ? $raw : null;
        }

        if (!is_string($raw) || !preg_match('/^0*([01])$/D', $raw, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * Read the active project, division, league and team selection.
     * Combines the module's configured defaults with Joomla frontend
     * request parameters, normalising the identifiers used by select menus.
     * @return void
     */
    private function initialiseState(): void
    {
        $input = $this->app->getInput();
        $defaultProject = max(0, (int) $this->params->get('default_project_id', 0));
        $this->projectId = $input->getCmd('option') === 'com_sportsmanagement'
            ? max(0, $input->getInt('p', $defaultProject))
            : $defaultProject;
        $this->roundId = max(0, $input->getInt('r', 0));
        $this->divisionId = max(0, $input->getInt('division', 0));
        $this->teamId = max(0, $input->getInt('tid', 0));
        $this->project = null;
        $this->teamOptions = null;
    }

    /**
     * Load the selected project and cache its navigation metadata.
     * Returns false when no valid project was selected; otherwise
     * retrieves the current project context needed by related selectors.
     * @return object|false Selected project record or false.
     */
    public function getProject(): object|false
    {
        if ($this->project !== null) {
            return $this->project;
        }
        if ($this->projectId <= 0) {
            return false;
        }

        $projectId = $this->projectId;
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('p.id'),
                $this->db->quoteName('p.name'),
                $this->db->quoteName('p.season_id'),
                $this->db->quoteName('p.league_id'),
                $this->db->quoteName('p.project_type'),
                $this->db->quoteName('p.alias', 'project_alias'),
                $this->db->quoteName('l.id', 'joined_league_id'),
                $this->db->quoteName('l.alias', 'league_alias'),
                $this->db->quoteName('s.id', 'joined_season_id'),
                $this->db->quoteName('s.alias', 'season_alias'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $this->db->quoteName('p.league_id') . ' = ' . $this->db->quoteName('l.id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $this->db->quoteName('p.season_id') . ' = ' . $this->db->quoteName('s.id'))
            ->where($this->db->quoteName('p.id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);
        $this->project = $this->db->loadObject() ?: null;

        if ($this->project !== null) {
            // Preserve the historical id:alias fields without MySQL-specific
            // CONCAT_WS, including its handling of NULL joined rows.
            $this->project->project_slug = self::slug(
                $this->project->id ?? null,
                $this->project->project_alias ?? null
            );
            $this->project->league_slug = self::slug(
                $this->project->joined_league_id ?? null,
                $this->project->league_alias ?? null
            );
            $this->project->season_slug = self::slug(
                $this->project->joined_season_id ?? null,
                $this->project->season_alias ?? null
            );
        }

        return $this->project ?: false;
    }

    /**
     * Render the Joomla season dropdown for the navigation menu.
     * Loads available season options, includes the configured prompt and
     * marks the season associated with the current project.
     * @return string HTML selection control for seasons.
     */
    public function getSeasonSelect(): string
    {
        $options = [
            (object) [
                'value' => '0',
                'text' => Text::_((string) $this->getParam('seasons_text')),
            ],
        ];
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('s.id', 'value'),
                $this->db->quoteName('s.name', 'text'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_season', 's'))
            ->order($this->db->quoteName('s.name') . ' DESC');
        $this->db->setQuery($query);
        $options = array_merge($options, $this->db->loadObjectList() ?: []);

        return HTMLHelper::_('select.genericlist', $options, 's', ['class' => 'jlnav-select form-select'], 'value', 'text', $this->getSeasonId());
    }

    /**
     * Return the season identifier of the selected project.
     * Uses cached project data when possible and reports zero when the
     * navigation has no active project.
     * @return int Selected season ID or zero.
     */
    public function getSeasonId(): int
    {
        $project = $this->getProject();

        return $project ? (int) $project->season_id : 0;
    }

    /**
     * Render the division dropdown scoped to the current project.
     * Returns false without a project and otherwise includes configured
     * prompt text and project division options.
     * @return string|false Division selector HTML or false.
     */
    public function getDivisionSelect(): string|false
    {
        $project = $this->getProject();
        if (!$project) {
            return false;
        }

        $options = [
            (object) [
                'value' => '0',
                'text' => Text::_((string) $this->getParam('divisions_text')),
            ],
        ];
        $projectId = (int) $project->id;
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('d.id', 'value'),
                $this->db->quoteName('d.name', 'text'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_division', 'd'))
            ->where($this->db->quoteName('d.project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->order($this->db->quoteName('d.name'));

        if ((int) $this->getParam('show_only_subdivisions', 0) === 1) {
            $query->where($this->db->quoteName('d.parent_id') . ' > 0');
        }

        $this->db->setQuery($query);
        $options = array_merge($options, $this->db->loadObjectList() ?: []);

        return HTMLHelper::_('select.genericlist', $options, 'd', ['class' => 'jlnav-division form-select'], 'value', 'text', $this->divisionId);
    }

    /**
     * Expose the division identifier from the navigation selection.
     * Allows legacy template code to read the normalised current state.
     * @return int Selected division ID or zero.
     */
    public function getDivisionId(): int
    {
        return $this->divisionId;
    }

    /**
     * Build the league selection control for the frontend menu.
     * Queries league options in the selected database and renders the
     * module's configured translated prompt and selected value.
     * @return string League selection HTML.
     */
    public function getLeagueSelect(): string
    {
        $options = [
            (object) [
                'value' => '0',
                'text' => Text::_((string) $this->getParam('leagues_text')),
            ],
        ];
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('l.id', 'value'),
                $this->db->quoteName('l.name', 'text'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_league', 'l'))
            ->order($this->db->quoteName('l.name'));
        $this->db->setQuery($query);
        $options = array_merge($options, $this->db->loadObjectList() ?: []);

        return HTMLHelper::_('select.genericlist', $options, 'l', ['class' => 'jlnav-select form-select'], 'value', 'text', $this->getLeagueId());
    }

    /**
     * Return the current project's league identifier.
     * Avoids assuming a project exists when navigation is displayed
     * outside a SportsManagement component view.
     * @return int League ID or zero.
     */
    public function getLeagueId(): int
    {
        $project = $this->getProject();

        return $project ? (int) $project->league_id : 0;
    }

    /**
     * Render the project dropdown with its configured filtering.
     * Combines module selectors and current navigation context to
     * produce linkable Joomla select options for available projects.
     * @return string Project selection HTML.
     */
    public function getProjectSelect(): string
    {
        $options = [
            (object) [
                'value' => '0',
                'text' => Text::_((string) $this->getParam('text_project_dropdown')),
            ],
        ];
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('p.id', 'value'),
                $this->db->quoteName('p.name', 'text'),
                $this->db->quoteName('s.name', 'season_name'),
                $this->db->quoteName('st.name', 'sports_type_name'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $this->db->quoteName('s.id') . ' = ' . $this->db->quoteName('p.season_id'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $this->db->quoteName('l.id') . ' = ' . $this->db->quoteName('p.league_id'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON ' . $this->db->quoteName('st.id') . ' = ' . $this->db->quoteName('p.sports_type_id'))
            ->where($this->db->quoteName('p.published') . ' = 1');

        $project = $this->getProject();
        if ((string) $this->getParam('show_project_dropdown') === 'season') {
            if ($project) {
                $seasonId = (int) $project->season_id;
                $leagueId = (int) $project->league_id;
                $query->where($this->db->quoteName('p.season_id') . ' = :seasonId')
                    ->where($this->db->quoteName('p.league_id') . ' = :leagueId')
                    ->bind(':seasonId', $seasonId, ParameterType::INTEGER)
                    ->bind(':leagueId', $leagueId, ParameterType::INTEGER);
            } else {
                $seasonId = (int) $this->getParam('s', 0);

                if ($seasonId > 0) {
                    $query->where($this->db->quoteName('p.season_id') . ' = :seasonId')
                        ->bind(':seasonId', $seasonId, ParameterType::INTEGER);
                }
            }
        }

        $order = match ((int) $this->getParam('project_ordering', 0)) {
            1 => $this->db->quoteName('p.ordering') . ' DESC',
            2 => $this->db->quoteName('s.ordering') . ' ASC, ' . $this->db->quoteName('l.ordering') . ' ASC, ' . $this->db->quoteName('p.ordering') . ' ASC',
            3 => $this->db->quoteName('s.ordering') . ' DESC, ' . $this->db->quoteName('l.ordering') . ' DESC, ' . $this->db->quoteName('p.ordering') . ' DESC',
            4 => $this->db->quoteName('p.name') . ' ASC',
            5 => $this->db->quoteName('p.name') . ' DESC',
            6 => $this->db->quoteName('l.ordering') . ' ASC, ' . $this->db->quoteName('p.ordering') . ' ASC, ' . $this->db->quoteName('s.ordering') . ' ASC',
            7 => $this->db->quoteName('l.ordering') . ' DESC, ' . $this->db->quoteName('p.ordering') . ' DESC, ' . $this->db->quoteName('s.ordering') . ' DESC',
            default => $this->db->quoteName('p.ordering') . ' ASC',
        };
        $query->order($order);
        $this->db->setQuery($query);

        foreach ($this->db->loadObjectList() ?: [] as $item) {
            $sportsType = (int) $this->getParam('project_include_sports_type_name', 0) === 1
                ? ' (' . Text::_((string) $item->sports_type_name) . ')'
                : '';
            $label = match ((int) $this->getParam('project_include_season_name', 0)) {
                1 => $item->season_name . ' - ' . $item->text . $sportsType,
                2 => $item->text . ' - ' . $item->season_name . $sportsType,
                default => $item->text . $sportsType,
            };
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => $label,
            ];
        }

        return HTMLHelper::_('select.genericlist', $options, 'p', ['class' => 'jlnav-project form-select'], 'value', 'text', $this->projectId);
    }

    /**
     * Render the team selector for the active project.
     * Returns false without a valid project and otherwise uses the
     * project-scoped team options prepared by getTeamsOptions().
     * @return string|false Team select HTML or false.
     */
    public function getTeamSelect(): string|false
    {
        if ($this->projectId <= 0) {
            return false;
        }

        $options = [
            (object) [
                'value' => '0',
                'text' => Text::_((string) $this->getParam('text_teams_dropdown')),
            ],
        ];
        $options = array_merge($options, $this->getTeamsOptions());

        return HTMLHelper::_('select.genericlist', $options, 'tid', ['class' => 'jlnav-team form-select'], 'value', 'text', $this->teamId);
    }

    /**
     * Query and cache the teams available for this project.
     * Restricts entries to the selected project and reuses cached
     * options for multiple navigation controls in the same request.
     * @return array<int,object> Project team selection records.
     */
    protected function getTeamsOptions(): array
    {
        if ($this->teamOptions !== null) {
            return $this->teamOptions;
        }
        if ($this->projectId <= 0) {
            return [];
        }

        $projectId = $this->projectId;
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('t.id', 'value'),
                $this->db->quoteName('t.name', 'text'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $this->db->quoteName('st.id') . ' = ' . $this->db->quoteName('pt.team_id'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $this->db->quoteName('st.team_id') . ' = ' . $this->db->quoteName('t.id'))
            ->where($this->db->quoteName('pt.project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->order($this->db->quoteName('t.name') . ' ASC');

        if ($this->divisionId > 0) {
            $divisionId = $this->divisionId;
            $query->where($this->db->quoteName('pt.division_id') . ' = :divisionId')
                ->bind(':divisionId', $divisionId, ParameterType::INTEGER);
        }

        $this->db->setQuery($query);
        $this->teamOptions = $this->db->loadObjectList() ?: [];

        return $this->teamOptions;
    }

    /**
     * Return the selected team identifier for navigation links.
     * Exposes the helper's normalised team state to legacy templates.
     * @return int Current team ID or zero.
     */
    public function getTeamId(): int
    {
        return $this->teamId;
    }

    /**
     * Construct a frontend URL for a supported navigation destination.
     * Rejects separators and missing projects, then forwards selected
     * database, project, division and team context to the site router.
     * @param mixed $view Requested SportsManagement view or separator.
     * @return string|false Routed destination, or false when unavailable.
     */
    public function getLink($view): string|false
    {
        if ($this->projectId <= 0 || $view === 'separator') {
            return false;
        }

        $input = $this->app->getInput();
        $base = [
            'cfg_which_database' => $input->getInt('cfg_which_database', (int) $this->params->get('cfg_which_database', 0)),
            's' => $input->getInt('s', 0),
            'p' => $this->projectId,
        ];

        switch ((string) $view) {
            case 'calendar':
                return SiteRouteHelper::view('teamplan', $base + [
                    'tid' => $this->teamId,
                    'division' => $this->divisionId,
                    'mode' => 0,
                    'ptid' => 0,
                ]);

            case 'curve':
                return SiteRouteHelper::view('curve', $base + [
                    'tid1' => $this->teamId,
                    'tid2' => 0,
                    'division' => $this->divisionId,
                ]);

            case 'eventsranking':
                return SiteRouteHelper::view('eventsranking', $base + [
                    'division' => $this->divisionId,
                    'tid' => $this->teamId,
                    'evid' => 0,
                    'mid' => 0,
                ]);

            case 'matrix':
                return SiteRouteHelper::view('matrix', $base + [
                    'division' => $this->divisionId,
                    'r' => 0,
                ]);

            case 'referees':
                return SiteRouteHelper::view('referees', $base);

            case 'results':
            case 'resultsmatrix':
            case 'resultsranking':
                return SiteRouteHelper::view((string) $view, $base + [
                    'r' => $this->roundId,
                    'division' => $this->divisionId,
                    'mode' => 0,
                    'order' => '',
                    'layout' => '',
                ]);

            case 'resultsrankingmatrix':
                return SiteRouteHelper::view('resultsrankingmatrix', $base + [
                    'r' => $this->roundId,
                    'division' => $this->divisionId,
                ]);

            case 'roster':
                if ($this->teamId <= 0) {
                    return false;
                }

                return SiteRouteHelper::view('roster', $base + [
                    'tid' => $this->teamId,
                    'ptid' => 0,
                ]);

            case 'stats':
                return SiteRouteHelper::view('stats', $base + [
                    'division' => $this->divisionId,
                ]);

            case 'statsranking':
                return SiteRouteHelper::view('statsranking', $base + [
                    'division' => $this->divisionId,
                    'tid' => 0,
                    'sid' => 0,
                    'order' => '',
                ]);

            case 'teaminfo':
                if ($this->teamId <= 0) {
                    return false;
                }

                return SiteRouteHelper::view('teaminfo', $base + [
                    'tid' => $this->teamId,
                    'ptid' => 0,
                ]);

            case 'teamplan':
                if ($this->teamId <= 0) {
                    return false;
                }

                return SiteRouteHelper::view('teamplan', $base + [
                    'tid' => $this->teamId,
                    'division' => $this->divisionId,
                    'mode' => 0,
                    'ptid' => 0,
                ]);

            case 'teamstats':
                if ($this->teamId <= 0) {
                    return false;
                }

                return SiteRouteHelper::view('teamstats', $base + [
                    'tid' => $this->teamId,
                ]);

            case 'treetonode':
                return SiteRouteHelper::view('treetonode', [
                    'cfg_which_database' => 0,
                    'p' => $this->projectId,
                ]);

            case 'ranking':
            default:
                return SiteRouteHelper::view('ranking', $base + [
                    'type' => 0,
                    'r' => $this->roundId,
                    'from' => 0,
                    'to' => 0,
                    'division' => $this->divisionId,
                ]);
        }
    }

    /**
     * Read a module option while respecting a supplied default.
     * Provides a compatibility facade for older navigation templates
     * without exposing the underlying Registry property.
     * @param string $name Module option name.
     * @param mixed $default Fallback when the setting is absent.
     * @return mixed Configured option or supplied fallback.
     */
    protected function getParam(string $name, mixed $default = null): mixed
    {
        return $this->params->get($name, $default);
    }

    /** Match CONCAT_WS(':', id, alias) without relying on a SQL dialect. */
    private static function slug(mixed $id, mixed $alias): string
    {
        if ($id === null) {
            return $alias === null ? '' : (string) $alias;
        }

        return (string) $id . ($alias === null ? '' : ':' . (string) $alias);
    }

    /**
     * Obtain the Joomla site application for legacy helper construction.
     * Resolves the app through the component's dedicated Joomla 5/6
     * service instead of relying on deprecated global factories.
     * @return CMSApplicationInterface Active Joomla frontend application.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }
}
