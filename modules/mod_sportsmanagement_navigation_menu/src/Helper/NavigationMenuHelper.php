<?php
/**
 * Shared data and compatibility helper for the SportsManagement navigation menu module.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementNavigationMenu\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
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

    public function __construct(
        ?Registry $params = null,
        ?CMSApplicationInterface $app = null,
        ?DatabaseInterface $db = null
    ) {
        $this->loadRouteHelper();
        $this->params = $params ?? new Registry();
        $this->app = $app ?? self::siteApplication();

        if ($db !== null) {
            $this->db = $db;
        }

        if ($params !== null) {
            $this->initialiseState();
        }
    }

    public function getData(
        Registry $params,
        CMSApplicationInterface $app,
        DatabaseInterface $joomlaDatabase
    ): array {
        $this->params = $params;
        $this->app = $app;
        $this->db = SportsManagementDatabaseResolver::resolve(
            $joomlaDatabase,
            (int) $params->get('cfg_which_database', 0)
        );
        $this->initialiseState();

        return [
            'helper' => $this,
            'seasonselect' => $this->getSeasonSelect(),
            'leagueselect' => $this->getLeagueSelect(),
            'projectselect' => $this->getProjectSelect(),
            'divisionselect' => $this->getDivisionSelect(),
            'teamselect' => $this->getTeamSelect(),
            'defaultview' => (string) $params->get('project_start', 'ranking'),
            'defaultitemid' => (int) $params->get('custom_item_id', 0),
        ];
    }

    private function initialiseState(): void
    {
        $input = $this->app->getInput();
        $defaultProject = (int) $this->params->get('default_project_id', 0);
        $this->projectId = $input->getCmd('option') === 'com_sportsmanagement'
            ? $input->getInt('p', $defaultProject)
            : $defaultProject;
        $this->roundId = $input->getInt('r', 0);
        $this->divisionId = $input->getInt('division', 0);
        $this->teamId = $input->getInt('tid', 0);
        $this->project = null;
        $this->teamOptions = null;
    }

    public function getProject(): object|false
    {
        if ($this->project !== null) {
            return $this->project;
        }
        if ($this->projectId <= 0) {
            return false;
        }

        $query = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('p.id'),
                $this->db->quoteName('p.name'),
                $this->db->quoteName('p.season_id'),
                $this->db->quoteName('p.league_id'),
                $this->db->quoteName('p.project_type'),
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                "CONCAT_WS(':', l.id, l.alias) AS league_slug",
                "CONCAT_WS(':', s.id, s.alias) AS season_slug",
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_league', 'l') . ' ON p.league_id = l.id')
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season', 's') . ' ON p.season_id = s.id')
            ->where('p.id = ' . $this->projectId);
        $this->db->setQuery($query, 0, 1);
        $this->project = $this->db->loadObject() ?: null;

        return $this->project ?: false;
    }

    public function getSeasonSelect(): string
    {
        $options = [
            (object) [
                'value' => '0',
                'text' => Text::_((string) $this->getParam('seasons_text')),
            ],
        ];
        $query = $this->db->getQuery(true)
            ->select('s.id AS value, s.name AS text')
            ->from($this->db->quoteName('#__sportsmanagement_season', 's'))
            ->order('s.name DESC');
        $this->db->setQuery($query);
        $options = array_merge($options, $this->db->loadObjectList() ?: []);

        return HTMLHelper::_('select.genericlist', $options, 's', ['class' => 'jlnav-select form-select'], 'value', 'text', $this->getSeasonId());
    }

    public function getSeasonId(): int
    {
        $project = $this->getProject();
        return $project ? (int) $project->season_id : 0;
    }

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
        $query = $this->db->getQuery(true)
            ->select([
                'd.id AS value',
                'd.name AS text',
                "CONCAT_WS(':', d.id, d.alias) AS division_slug",
            ])
            ->from($this->db->quoteName('#__sportsmanagement_division', 'd'))
            ->where('d.project_id = ' . (int) $project->id)
            ->order('d.name');

        if ((int) $this->getParam('show_only_subdivisions', 0) === 1) {
            $query->where('d.parent_id > 0');
        }

        $this->db->setQuery($query);
        $options = array_merge($options, $this->db->loadObjectList() ?: []);

        return HTMLHelper::_('select.genericlist', $options, 'd', ['class' => 'jlnav-division form-select'], 'value', 'text', $this->divisionId);
    }

    public function getDivisionId(): int
    {
        return $this->divisionId;
    }

    public function getLeagueSelect(): string
    {
        $options = [
            (object) [
                'value' => '0',
                'text' => Text::_((string) $this->getParam('leagues_text')),
            ],
        ];
        $query = $this->db->getQuery(true)
            ->select([
                'l.id AS value',
                'l.name AS text',
                "CONCAT_WS(':', l.id, l.alias) AS league_slug",
            ])
            ->from($this->db->quoteName('#__sportsmanagement_league', 'l'))
            ->order('l.name');
        $this->db->setQuery($query);
        $options = array_merge($options, $this->db->loadObjectList() ?: []);

        return HTMLHelper::_('select.genericlist', $options, 'l', ['class' => 'jlnav-select form-select'], 'value', 'text', $this->getLeagueId());
    }

    public function getLeagueId(): int
    {
        $project = $this->getProject();
        return $project ? (int) $project->league_id : 0;
    }

    public function getProjectSelect(): string
    {
        $options = [
            (object) [
                'value' => '0',
                'text' => Text::_((string) $this->getParam('text_project_dropdown')),
            ],
        ];
        $query = $this->db->getQuery(true)
            ->select([
                'p.id AS value',
                'p.name AS text',
                's.name AS season_name',
                'st.name AS sports_type_name',
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_season', 's') . ' ON s.id = p.season_id')
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_league', 'l') . ' ON l.id = p.league_id')
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON st.id = p.sports_type_id')
            ->where('p.published = 1');

        $project = $this->getProject();
        if ((string) $this->getParam('show_project_dropdown') === 'season') {
            if ($project) {
                $query->where('p.season_id = ' . (int) $project->season_id)
                    ->where('p.league_id = ' . (int) $project->league_id);
            } elseif ((int) $this->getParam('s', 0) > 0) {
                $query->where('p.season_id = ' . (int) $this->getParam('s'));
            }
        }

        $order = match ((int) $this->getParam('project_ordering', 0)) {
            1 => 'p.ordering DESC',
            2 => 's.ordering ASC, l.ordering ASC, p.ordering ASC',
            3 => 's.ordering DESC, l.ordering DESC, p.ordering DESC',
            4 => 'p.name ASC',
            5 => 'p.name DESC',
            6 => 'l.ordering ASC, p.ordering ASC, s.ordering ASC',
            7 => 'l.ordering DESC, p.ordering DESC, s.ordering DESC',
            default => 'p.ordering ASC',
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

    protected function getTeamsOptions(): array
    {
        if ($this->teamOptions !== null) {
            return $this->teamOptions;
        }
        if ($this->projectId <= 0) {
            return [];
        }

        $query = $this->db->getQuery(true)
            ->select('t.id AS value, t.name AS text')
            ->from($this->db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.id = pt.team_id')
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_team', 't') . ' ON st.team_id = t.id')
            ->where('pt.project_id = ' . $this->projectId)
            ->order('t.name ASC');

        if ($this->divisionId > 0) {
            $query->where('pt.division_id = ' . $this->divisionId);
        }

        $this->db->setQuery($query);
        $this->teamOptions = $this->db->loadObjectList() ?: [];
        return $this->teamOptions;
    }

    public function getTeamId(): int
    {
        return $this->teamId;
    }

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

    protected function getParam(string $name, mixed $default = null): mixed
    {
        return $this->params->get($name, $default);
    }

    private function loadRouteHelper(): void
    {
        if (class_exists(SiteRouteHelper::class)) {
            return;
        }

        $routeHelper = JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php';

        if (is_file($routeHelper)) {
            require_once $routeHelper;
        }

        if (!class_exists(SiteRouteHelper::class)) {
            throw new \RuntimeException('SportsManagement route helper is unavailable.');
        }
    }

    private static function siteApplication(): SiteApplication
    {
        $app = Factory::getApplication();

        if (!$app instanceof SiteApplication) {
            throw new \RuntimeException('SportsManagement site application is unavailable.');
        }

        return $app;
    }
}
