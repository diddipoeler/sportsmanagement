<?php
/**
 * Joomla 5/6 data/link helper for the AJAX top navigation module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementAjaxTopNavigationMenu\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class NavigationDataHelper
{
    public static $_project_id = 0;
    public static $_country_fed = [];
    public static $query_getFederations = '';
    public static $query_getFederationSelect = '';
    public static $query_getAssocLeagueSelect = '';
    public static $query_getCountryAssocSelect = '';
    public static $query_getCountryFederation = '';
    public static $query_getCountrySubAssocSelect = '';
    public static $query_getCountrySubSubAssocSelect = '';
    public static $query_getLeagueAssocId = '';
    public static $query_getLeagueSelect = '';

    public $_project_slug = '';
    public $_league_slug = '';
    public $_round_slug = '';
    public $_season_slug = '';
    public $_team_slug = '';
    public $_club_slug = '';
    public $_division_slug = '';
    public $project_type = 'SIMPLE_LEAGUE';
    public $_league_id = 0;
    public $_team_id = 0;
    public $_club_id = 0;
    public $_division_id = 0;
    public $_tnid = 0;
    public $_round_id = null;

    protected Registry $_params;
    protected DatabaseInterface $_db;
    protected $_query;
    protected CMSApplicationInterface $_app;
    protected $_teamoptions = null;
    protected $_project = null;
    protected string $_user_name = '';

    public function __construct(
        $params,
        ?CMSApplicationInterface $app = null,
        ?DatabaseInterface $database = null
    ) {
        $this->_params = $params instanceof Registry ? $params : new Registry($params);

        $container = Factory::getContainer();
        /** @var CMSApplicationInterface $resolvedApp */
        $resolvedApp = $app ?? $container->get(SiteApplication::class);
        $this->_app = $resolvedApp;

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = $database ?? $container->get(DatabaseInterface::class);
        $input = $this->_app->getInput();
        $selector = $input->getInt(
            'cfg_which_database',
            (int) $this->_params->get(
                'cfg_which_database',
                (int) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0)
            )
        );
        $this->_db = SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector);

        if (self::$_project_id) {
            $input = $this->_app->getInput();
            $input->set('jlamtopseason', $this->getSeasonId());
            $input->set('jlamtopleague', $this->getLeagueId());
            $input->set('jlamtopproject', self::$_project_id);
            $input->set('jlamtopteam', $this->_team_id);
            $input->set('jlamtopdivisionid', $this->_division_id);
        }
    }

    public function getSeasonId()
    {
        $project = $this->getProject();
        return $project ? (int) $project->season_id : 0;
    }

    public function getProject($league_id = 0)
    {
        if ($this->_project) {
            return $this->_project;
        }

        if (!self::$_project_id && !$league_id) {
            return false;
        }

        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select([
                'p.id',
                'p.name',
                'p.league_id',
                'p.project_type',
                "CONCAT_WS(':',p.id,p.alias) AS project_slug",
                "CONCAT_WS(':',s.id,s.alias) AS season_slug",
                "CONCAT_WS(':',l.id,l.alias) AS league_slug",
                "CONCAT_WS(':',r.id,r.alias) AS round_slug",
                'p.season_id',
                'p.current_round',
            ])
            ->from('#__sportsmanagement_project AS p')
            ->join('INNER', '#__sportsmanagement_season AS s ON s.id = p.season_id')
            ->join('INNER', '#__sportsmanagement_league AS l ON l.id = p.league_id')
            ->join('LEFT', '#__sportsmanagement_round AS r ON p.current_round = r.id');

        if ((int) $league_id > 0) {
            $query->where('p.league_id = ' . (int) $league_id);
        } else {
            $query->where('p.id = ' . (int) self::$_project_id);
        }

        $db->setQuery($query, 0, 1);
        $project = $db->loadObject();
        if (!$project) {
            return false;
        }

        $this->_project = $project;
        $this->project_type = (string) $project->project_type;
        self::$_project_id = (int) $project->id;
        $this->_project_slug = (string) $project->project_slug;
        $this->_season_slug = (string) $project->season_slug;
        $this->_league_slug = (string) $project->league_slug;
        $this->_round_slug = (string) ($project->round_slug ?? '');

        return $this->_project;
    }

    public function setProject($project_id, $team_id, $division_id): void
    {
        self::$_project_id = (int) $project_id;
        $this->_team_id = (int) $team_id;
        $this->_division_id = (int) $division_id;
        $this->_project = null;
        $this->_teamoptions = null;
        $this->_project = $this->getProject();
        $this->_round_id = $this->getCurrentRoundId();

        $input = $this->_app->getInput();
        $input->set('jlamtopseason', $this->getSeasonId());
        $input->set('jlamtopleague', $this->getLeagueId());
        $input->set('jlamtopproject', self::$_project_id);
        $input->set('jlamtopteam', $this->_team_id);
        $input->set('jlamtopdivisionid', $this->_division_id);
    }

    public function getLeagueId()
    {
        $project = $this->getProject();
        if (!$project) {
            return 0;
        }

        $this->_league_id = (int) $project->league_id;
        return $this->_league_id;
    }

    public function getCurrentRoundId()
    {
        $project = $this->getProject();
        return $project ? (int) $project->current_round : 0;
    }

    public function getFederations(): array
    {
        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select(['name', 'id'])
            ->from('#__sportsmanagement_federations')
            ->where('published = 1');
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getCountrySubSubAssocSelect($assoc_id): array
    {
        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select('s.id AS value, s.name AS text')
            ->from('#__sportsmanagement_associations AS s')
            ->where('s.parent_id = ' . (int) $assoc_id)
            ->where('s.published = 1')
            ->order('s.name');
        $db->setQuery($query);
        $res = $db->loadObjectList() ?: [];

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_('-- Kreisverbände -- '))],
            $res
        );
    }

    public function getCountrySubAssocSelect($assoc_id): array
    {
        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select('s.id AS value, s.name AS text')
            ->from('#__sportsmanagement_associations AS s')
            ->where('s.parent_id = ' . (int) $assoc_id)
            ->where('s.published = 1')
            ->order('s.name');
        $db->setQuery($query);
        $res = $db->loadObjectList() ?: [];

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_('-- Landesverbände -- '))],
            $res
        );
    }

    public function getCountryAssocSelect($country): array
    {
        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select('s.id AS value, s.name AS text')
            ->from('#__sportsmanagement_associations AS s')
            ->where('s.country = ' . $db->quote((string) $country))
            ->where('s.parent_id = 0')
            ->where('s.published = 1')
            ->order('s.name');
        $db->setQuery($query);
        $res = $db->loadObjectList() ?: [];

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_('-- Regionalverbände -- '))],
            $res
        );
    }

    public function getFederationSelect($federation = '', $federationid = 0): array
    {
        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select('s.alpha3 AS value, s.name AS text')
            ->from('#__sportsmanagement_countries AS s')
            ->join('INNER', '#__sportsmanagement_league AS l ON l.country = s.alpha3')
            ->where('s.federation = ' . (int) $federationid)
            ->group('s.name')
            ->order('s.name DESC');
        $db->setQuery($query);
        $res = $db->loadObjectList() ?: [];

        foreach ($res as $row) {
            $row->text = Text::_($row->text);
        }

        $res = ArrayHelper::sortObjects($res, 'text', 1);
        foreach ($res as $row) {
            self::$_country_fed[$row->value] = (string) $federation;
        }

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_((string) $federation))],
            $res
        );
    }

    public function getCountryFederation($country_id)
    {
        if (!$country_id) {
            return false;
        }

        return self::$_country_fed[$country_id] ?? false;
    }

    public function getQueryValues(): array
    {
        $input = $this->_app->getInput();

        return [
            'option' => $input->getCmd('option', ''),
            'view' => $input->getCmd('view', ''),
            'cfg_which_database' => $input->getInt('cfg_which_database', 0),
            's' => $input->getInt('s', 0),
            'p' => $input->getInt('p', 0),
            'division' => $input->getInt('division', 0),
            'type' => $input->getInt('type', 0),
            'r' => $input->getInt('r', 0),
            'from' => $input->getInt('from', 0),
            'to' => $input->getInt('to', 0),
            'mid' => $input->getInt('mid', 0),
            'tid' => $input->getInt('tid', 0),
            'cid' => $input->getInt('cid', 0),
            'Itemid' => $input->getInt('Itemid', 0),
        ];
    }

    public function getUserName(): string
    {
        $user = $this->_app->getIdentity();
        $this->_user_name = (string) ($user->username ?? '');
        return $this->_user_name;
    }

    public function getAssocParentId($assoc_id)
    {
        if ((int) $assoc_id <= 0) {
            return false;
        }

        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select('parent_id')
            ->from('#__sportsmanagement_associations')
            ->where('id = ' . (int) $assoc_id);
        $db->setQuery($query);
        $result = (int) $db->loadResult();

        return $result > 0 ? $result : false;
    }

    public function getLeagueAssocId()
    {
        if ($this->_league_id <= 0) {
            return false;
        }

        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select('associations')
            ->from('#__sportsmanagement_league')
            ->where('id = ' . (int) $this->_league_id);
        $db->setQuery($query);
        $result = (int) $db->loadResult();

        return $result > 0 ? $result : false;
    }

    public function getDivisionId()
    {
        return $this->_division_id;
    }

    public function setDivisionID($division_id): void
    {
        $this->_division_id = (int) $division_id;
    }

    public function getFavTeams($project_id)
    {
        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select('fav_team')
            ->from('#__sportsmanagement_project')
            ->where('id = ' . (int) $project_id);
        $db->setQuery($query);
        $teams = trim((string) $db->loadResult());
        if ($teams === '') {
            return false;
        }

        $ids = array_values(array_filter(array_map('intval', explode(',', $teams))));
        if (!$ids) {
            return false;
        }

        $query = $db->getQuery(true)
            ->select('t.id AS team_id, t.name, t.club_id')
            ->from('#__sportsmanagement_team AS t')
            ->where('t.id IN (' . implode(',', $ids) . ')');
        $db->setQuery($query);
        return $db->loadObjectList() ?: false;
    }

    public function getTeamId($project_id, $club_id)
    {
        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select('pt.team_id')
            ->from('#__sportsmanagement_project_team AS pt')
            ->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id')
            ->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id')
            ->where('pt.project_id = ' . (int) $project_id)
            ->where('t.club_id = ' . (int) $club_id);
        $db->setQuery($query, 0, 1);
        $result = (int) $db->loadResult();
        if ($result <= 0) {
            return false;
        }

        $this->_team_id = $result;
        return $this->_team_id;
    }

    public function setTeamID($team_id): void
    {
        $this->_team_id = (int) $team_id;
    }

    public function getSeasonSelect(): array
    {
        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select('s.id AS value, s.name AS text')
            ->from('#__sportsmanagement_season AS s')
            ->where('s.published = 1')
            ->order('s.name DESC');
        $db->setQuery($query);

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_((string) $this->getParam('seasons_text')))],
            $db->loadObjectList() ?: []
        );
    }

    protected function getParam($name, $default = null)
    {
        return $this->_params->get($name, $default);
    }

    public function getDivisionSelect($project_id): array
    {
        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select('d.id AS value, d.name AS text')
            ->from('#__sportsmanagement_division AS d')
            ->where('d.project_id = ' . (int) $project_id);

        if ((int) $this->getParam('show_only_subdivisions', 0) === 1) {
            $query->where('d.parent_id > 0');
        }

        $query->order('d.name');
        $db->setQuery($query);
        $res = $db->loadObjectList() ?: [];

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_((string) $this->getParam('divisions_text')))],
            $res
        );
    }

    public function getAssocLeagueSelect($country_id, $associd): array
    {
        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select('l.id AS value, l.name AS text')
            ->from('#__sportsmanagement_league AS l')
            ->join('INNER', '#__sportsmanagement_project AS p ON l.id = p.league_id')
            ->join('INNER', '#__sportsmanagement_season AS s ON s.id = p.season_id')
            ->where('l.country = ' . $db->quote((string) $country_id))
            ->group('l.name')
            ->order('l.name');

        if ((int) $associd > 0) {
            $query->where('l.associations = ' . (int) $associd);
        }

        $db->setQuery($query);
        $res = $db->loadObjectList() ?: [];

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_((string) $this->getParam('leagues_text')))],
            $res
        );
    }

    public function getProjectCountry($project_id)
    {
        if ((int) $project_id <= 0) {
            return false;
        }

        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select('l.country')
            ->from('#__sportsmanagement_league AS l')
            ->join('INNER', '#__sportsmanagement_project AS p ON l.id = p.league_id')
            ->where('p.id = ' . (int) $project_id);
        $db->setQuery($query, 0, 1);
        return $db->loadResult() ?: false;
    }

    public function getLeagueSelect($season): array
    {
        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select('l.id AS value, l.name AS text')
            ->from('#__sportsmanagement_league AS l')
            ->join('INNER', '#__sportsmanagement_project AS p ON l.id = p.league_id')
            ->join('INNER', '#__sportsmanagement_season AS s ON s.id = p.season_id')
            ->where('s.id = ' . (int) $season)
            ->where('s.published = 1')
            ->where('l.published = 1')
            ->group('l.name')
            ->order('l.name');
        $db->setQuery($query);

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_((string) $this->getParam('leagues_text')))],
            $db->loadObjectList() ?: []
        );
    }

    public function getProjectSelect($league_id): array
    {
        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select('p.id AS value, p.name AS text')
            ->from('#__sportsmanagement_project AS p')
            ->join('INNER', '#__sportsmanagement_season AS s ON s.id = p.season_id')
            ->join('INNER', '#__sportsmanagement_league AS l ON l.id = p.league_id')
            ->where('p.published = 1')
            ->where('p.league_id = ' . (int) $league_id)
            ->order('s.name DESC, p.name ASC');
        $db->setQuery($query);

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_((string) $this->getParam('text_project_dropdown')))],
            $db->loadObjectList() ?: []
        );
    }

    public function getTeamSelect($project_id): array
    {
        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_((string) $this->getParam('text_teams_dropdown')))],
            $this->getTeamsOptions($project_id)
        );
    }

    protected function getTeamsOptions($project_id): array
    {
        if ($this->_teamoptions !== null) {
            return $this->_teamoptions;
        }

        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select('t.id AS value, t.name AS text')
            ->from('#__sportsmanagement_project_team AS pt')
            ->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id')
            ->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id')
            ->where('pt.project_id = ' . (int) $project_id)
            ->order('t.name ASC');

        try {
            $db->setQuery($query);
            $this->_teamoptions = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->_app->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                'error'
            );
            $this->_app->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__),
                'error'
            );
            $this->_teamoptions = [];
        }

        return $this->_teamoptions;
    }

    public function getLinkFavTeam($view, $team_id, $club_id)
    {
        $base = $this->baseRouteParameters();

        switch ($view) {
            case 'roster':
                return SiteRouteHelper::view('roster', $base + [
                    'tid' => $team_id,
                    'ptid' => 0,
                ]);

            case 'teaminfo':
                return SiteRouteHelper::view('teaminfo', $base + [
                    'tid' => $team_id,
                    'ptid' => 0,
                ]);

            case 'teamplan':
                return SiteRouteHelper::view('teamplan', $base + [
                    'tid' => $this->_team_id,
                    'division' => 0,
                    'mode' => 0,
                    'ptid' => 0,
                ]);

            case 'clubinfo':
                $this->getClubId();
                return SiteRouteHelper::view('clubinfo', $base + [
                    'cid' => $club_id,
                ]);

            case 'clubplan':
                $this->getClubId();
                return SiteRouteHelper::view('clubplan', $base + [
                    'cid' => $club_id,
                ]);

            case 'teamstats':
                return SiteRouteHelper::view('teamstats', $base + [
                    'tid' => $this->_team_id,
                    'ptid' => 0,
                    'division' => 0,
                ]);
        }

        return false;
    }

    public function getClubId()
    {
        if ($this->_team_id <= 0) {
            return false;
        }

        $db = $this->_db;
        $query = $db->getQuery(true)
            ->select([
                't.club_id',
                "CONCAT_WS(':',t.id,t.alias) AS team_slug",
                "CONCAT_WS(':',c.id,c.alias) AS club_slug",
            ])
            ->from('#__sportsmanagement_team AS t')
            ->join('INNER', '#__sportsmanagement_club AS c ON t.club_id = c.id')
            ->where('t.id = ' . (int) $this->_team_id);
        $db->setQuery($query, 0, 1);
        $res = $db->loadObject();
        if (!$res) {
            return false;
        }

        $this->_club_id = (int) $res->club_id;
        $this->_club_slug = (string) $res->club_slug;
        $this->_team_slug = (string) $res->team_slug;
        return $this->_club_id;
    }

    public function getLink($view)
    {
        if (!self::$_project_id) {
            return false;
        }

        $base = $this->baseRouteParameters();

        switch ($view) {
            case 'calendar':
                return SiteRouteHelper::view('calendar', $base + [
                    'tid' => $this->_team_id,
                    'division' => 0,
                    'mode' => 0,
                    'ptid' => 0,
                ]);

            case 'curve':
                return SiteRouteHelper::view('curve', $base + [
                    'tid1' => $this->_team_slug,
                    'tid2' => 0,
                    'division' => $this->_division_id,
                ]);

            case 'eventsranking':
                return SiteRouteHelper::view('eventsranking', $base + [
                    'division' => $this->_division_id,
                    'tid' => $this->_team_id,
                    'evid' => 0,
                    'mid' => 0,
                ]);

            case 'matrix':
            case 'referees':
                return SiteRouteHelper::view((string) $view, $base + [
                    'division' => $this->_division_id,
                    'r' => 0,
                ]);

            case 'results':
            case 'allprojectrounds':
                return SiteRouteHelper::view((string) $view, $base + [
                    'r' => $this->_round_slug,
                    'division' => $this->_division_id,
                    'mode' => 0,
                    'order' => '',
                    'layout' => '',
                ]);

            case 'resultsmatrix':
            case 'resultsranking':
                return SiteRouteHelper::view((string) $view, $base + [
                    'r' => $this->_round_slug,
                    'division' => $this->_division_id,
                    'mode' => 0,
                    'order' => 0,
                    'layout' => 0,
                ]);

            case 'rankingmatrix':
                return SiteRouteHelper::view('rankingmatrix', $base + [
                    'division' => $this->_division_id,
                    'r' => $this->_round_slug,
                ]);

            case 'rankingalltime':
                return SiteRouteHelper::view('rankingalltime', [
                    'cfg_which_database' => $base['cfg_which_database'],
                    'l' => $this->_league_id,
                    'points' => $this->getParam('show_alltimetable_points'),
                    'type' => 0,
                    'order' => 0,
                    'dir' => 0,
                    's' => 0,
                    'p' => $this->_project_slug,
                ]);

            case 'leaguechampionoverview':
                return SiteRouteHelper::view('leaguechampionoverview', [
                    'cfg_which_database' => $base['cfg_which_database'],
                    'l' => $this->_league_id,
                    's' => 0,
                    'p' => $this->_project_slug,
                ]);

            case 'resultsrankingmatrix':
                return SiteRouteHelper::view('resultsrankingmatrix', $base + [
                    'r' => $this->_round_slug,
                    'division' => $this->_division_id,
                ]);

            case 'roster':
                if (!$this->ensureTeamSlugs()) {
                    return false;
                }
                return SiteRouteHelper::view('roster', $base + [
                    'tid' => $this->_team_slug,
                    'ptid' => 0,
                ]);

            case 'rosteralltime':
                if (!$this->ensureTeamSlugs()) {
                    return false;
                }
                return SiteRouteHelper::view('rosteralltime', $base + [
                    'tid' => $this->_team_slug,
                ]);

            case 'stats':
                return SiteRouteHelper::view('stats', $base + [
                    'division' => $this->_division_id,
                ]);

            case 'statsranking':
            case 'statsrankingteams':
                return SiteRouteHelper::view((string) $view, $base + [
                    'division' => $this->_division_id,
                    'tid' => $this->_team_id,
                ]);

            case 'teaminfo':
                if (!$this->ensureTeamSlugs()) {
                    return false;
                }
                return SiteRouteHelper::view('teaminfo', $base + [
                    'tid' => $this->_team_slug,
                    'ptid' => 0,
                ]);

            case 'teamplan':
                if ($this->_team_id <= 0) {
                    return false;
                }
                return SiteRouteHelper::view('teamplan', $base + [
                    'tid' => $this->_team_id,
                    'division' => 0,
                    'mode' => 0,
                    'ptid' => 0,
                ]);

            case 'clubinfo':
                if (!$this->getClubId()) {
                    return false;
                }
                return SiteRouteHelper::view('clubinfo', $base + [
                    'cid' => $this->_club_slug,
                ]);

            case 'clubplan':
                if (!$this->getClubId()) {
                    return false;
                }
                return SiteRouteHelper::view('clubplan', $base + [
                    'cid' => $this->_club_slug,
                ]);

            case 'teamstats':
                if ($this->_team_id <= 0) {
                    return false;
                }
                return SiteRouteHelper::view('teamstats', $base + [
                    'tid' => $this->_team_id,
                    'ptid' => 0,
                    'division' => 0,
                ]);

            case 'teams':
            case 'teamstree':
                return SiteRouteHelper::view((string) $view, $base + [
                    'division' => $this->_division_id,
                ]);

            case 'treetonode':
                return SiteRouteHelper::view('treetonode', $base + [
                    'tnid' => $this->_tnid,
                ]);

            case 'jltournamenttree':
                return SiteRouteHelper::view('jltournamenttree', $base + [
                    'r' => $this->_round_slug,
                ]);

            case 'tournamentbracket':
                return SiteRouteHelper::view('tournamentbracket', $base + [
                    'r' => $this->_round_slug,
                ]);

            case 'separator':
                return false;

            case 'ranking':
            default:
                return SiteRouteHelper::view((string) $view, $base + [
                    'type' => 0,
                    'r' => $this->_round_slug,
                    'from' => 0,
                    'to' => 0,
                    'division' => $this->_division_id,
                ]);
        }
    }

    private function baseRouteParameters(): array
    {
        $input = $this->_app->getInput();

        return [
            'cfg_which_database' => $input->getInt(
                'cfg_which_database',
                (int) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0)
            ),
            's' => $input->getInt('s', 0),
            'p' => $this->_project_slug,
        ];
    }

    private function ensureTeamSlugs(): bool
    {
        if ($this->_team_id <= 0) {
            return false;
        }

        if ($this->_team_slug !== '') {
            return true;
        }

        return (bool) $this->getClubId();
    }
}
