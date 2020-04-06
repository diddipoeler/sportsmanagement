<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage mod_sportsmanagement_navigation_menu
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * modsportsmanagementNavigationMenuHelper
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class modsportsmanagementNavigationMenuHelper
{

    protected $_params;

    protected $_db;

    protected $_project_id;
    protected $_team_id;
    protected $_division_id = 0;
    protected $_round_id = null;
    protected $_teamoptions;

    protected $_project;
    
    protected $_project_slug = '';
    protected $_team_slug = '';
    protected $_division_slug = '';
    protected $_round_slug = '';
    
    /**
     * modsportsmanagementNavigationMenuHelper::__construct()
     * 
     * @param  mixed $params
     * @return void
     */
    public function __construct($params)
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $this->jinput = $app->input;
        
        $this->_params = $params;
        $this->_db = Factory::getDbo();

        if ($this->jinput->getCmd('option') == 'com_sportsmanagement') {
            $p = $this->jinput->getInt('p', $params->get('default_project_id'));
        } else {
            $p = $params->get('default_project_id');
        }
        $this->_project_id = intval($p);
        $this->_project = $this->getProject();
        $this->_round_id = $this->jinput->getInt('r');
        $this->_division_id = $this->jinput->getInt('division', 0);
        $this->_team_id = $this->jinput->getInt('tid', 0);

    }

    /**
     * modsportsmanagementNavigationMenuHelper::getSeasonId()
     * 
     * @return
     */
    public function getSeasonId()
    {
        if ($this->getProject()) {
            return $this->getProject()->season_id;
        } else {
            return 0;
        }
    }

    /**
     * modsportsmanagementNavigationMenuHelper::getLeagueId()
     * 
     * @return
     */
    public function getLeagueId()
    {
        if ($this->getProject()) {
            return $this->getProject()->league_id;
        } else {
            return 0;
        }
    }

    /**
     * modsportsmanagementNavigationMenuHelper::getDivisionId()
     * 
     * @return
     */
    public function getDivisionId()
    {
        return $this->_division_id;
    }

    /**
     * modsportsmanagementNavigationMenuHelper::getTeamId()
     * 
     * @return
     */
    public function getTeamId()
    {
        return $this->_team_id;
    }

    /**
     * returns the selector for season
     *
     * @return string html select
     */
    public function getSeasonSelect()
    {
        $mainframe = Factory::getApplication();
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $options = array(HTMLHelper::_('select.option', 0, Text::_($this->getParam('seasons_text'))));

        $query->select('s.id AS value, s.name AS text');
        $query->from('#__sportsmanagement_season AS s');

        $db->setQuery($query);
        $res = $db->loadObjectList();
        if ($res) {
            $options = array_merge($options, $res);
        }
        return HTMLHelper::_(
            'select.genericlist', $options, 's', 'class="jlnav-select"',
            'value', 'text', $this->getSeasonId()
        );
    }

    /**
     * returns the selector for division
     * 
     * @return string html select
     */
    public function getDivisionSelect()
    {
        $mainframe = Factory::getApplication();
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $project = $this->getProject();
        if (!is_object($project)) {
            return false;
        }
        if (!$this->_project_id && !($this->_project_id > 0) && $project->project_type !=        'DIVISION_LEAGUE'
        ) {
            return false;
        }
        $options = array(HTMLHelper::_('select.option', 0, Text::_($this->getParam('divisions_text'))));

        $query->select('d.id AS value, d.name AS text');
        $query->select('CONCAT_WS( \':\', d.id, d.alias ) AS division_slug');
        $query->from('#__sportsmanagement_division AS d');
        $query->where('d.project_id = ' . $project->id);
        if ($this->getParam("show_only_subdivisions", 0)) {
            $query->where('d.parent_id > 0 = ');
        }
        $query->order('d.name');

        $db->setQuery($query);
        $res = $db->loadObjectList();
        if ($res) {
            $options = array_merge($options, $res);
        }
        return HTMLHelper::_(
            'select.genericlist', $options, 'd', 'class="jlnav-division"',
            'value', 'text', $this->getDivisionId()
        );
    }

    /**
     * returns the selector for league
     * 
     * @return string html select
     */
    public function getLeagueSelect()
    {
        $mainframe = Factory::getApplication();
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $options = array(HTMLHelper::_('select.option', 0, Text::_($this->getParam('leagues_text'))));

        $query->select('l.id AS value, l.name AS text');
        $query->select('CONCAT_WS( \':\', l.id, l.alias ) AS league_slug');
        $query->from('#__sportsmanagement_league AS l');

        $db->setQuery($query);
        $res = $db->loadObjectList();
        if ($res) {
            $options = array_merge($options, $res);
        }
        return HTMLHelper::_(
            'select.genericlist', $options, 'l', 'class="jlnav-select"',
            'value', 'text', $this->getLeagueId()
        );
    }

    /**
     * returns the selector for project
     * 
     * @return string html select
     */
    public function getProjectSelect()
    {
        $mainframe = Factory::getApplication();
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $options = array(HTMLHelper::_('select.option', 0, Text::_($this->getParam('text_project_dropdown'))));

        $query->select('p.name AS text, s.name AS season_name, st.name as sports_type_name');
        $query->select('p.id AS value');
        $query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
        $query->from('#__sportsmanagement_project AS p');
        $query->join('INNER', '#__sportsmanagement_season AS s on s.id = p.season_id ');
        $query->join('INNER', '#__sportsmanagement_league AS l on l.id = p.league_id ');
        $query->join('INNER', '#__sportsmanagement_sports_type AS st on st.id = p.sports_type_id ');

        $query->where('p.published = 1');
        
        if ($this->getParam('show_project_dropdown') == 'season' && !$this->getProject()) {
            if ($this->getParam('s', 0) ) {
                $query->where('p.season_id = ' . (int)$this->getParam('s'));
            }    
        }

        if ($this->getParam('show_project_dropdown') == 'season' && $this->getProject()) {

            $query->where('p.season_id = ' . $this->getProject()->season_id);
            $query->where('p.league_id = ' . $this->getProject()->league_id);
        }

        $query->group('p.id');

        switch ($this->getParam('project_ordering', 0)) {
        case 0:
            $query->order('p.ordering ASC');
            break;

        case 1:
            $query->order('p.ordering DESC');
            break;

        case 2:
            $query->order('s.ordering ASC, l.ordering ASC, p.ordering ASC');
            break;

        case 3:
            $query->order('s.ordering DESC, l.ordering DESC, p.ordering DESC');
            break;

        case 4:
            $query->order('p.name ASC');
            break;

        case 5:
            $query->order('p.name DESC');
            break;

        case 6:
            $query->order('l.ordering ASC, p.ordering ASC, s.ordering ASC');
            break;

        case 7:
            $query->order('l.ordering DESC, p.ordering DESC, s.ordering DESC');
            break;
        }

        $db->setQuery($query);
        $res = $db->loadObjectList();

        if ($res) {
            switch ($this->getParam('project_include_season_name', 0)) {
            case 2:
                foreach ($res as $p) {
                    $stText = ($this->getParam('project_include_sports_type_name', 0) == 1) ? ' (' .
                        Text::_($p->sports_type_name) . ')' : '';
                    $options[] = HTMLHelper::_(
                        'select.option', $p->value, $p->text . ' - ' . $p
                            ->season_name . $stText
                    );
                }
                break;
            case 1:
                foreach ($res as $p) {
                    $stText = ($this->getParam('project_include_sports_type_name', 0) == 1) ? ' (' .
                        Text::_($p->sports_type_name) . ')' : '';
                    $options[] = HTMLHelper::_(
                        'select.option', $p->value, $p->season_name . ' - ' . $p
                            ->text . $stText
                    );
                }
                break;
            case 0:
            default:
                foreach ($res as $p) {
                    $stText = ($this->getParam('project_include_sports_type_name', 0) == 1) ? ' (' .
                        Text::_($p->sports_type_name) . ')' : '';
                    $options[] = HTMLHelper::_('select.option', $p->value, $p->text . $stText);
                }
            }
        }
        return HTMLHelper::_(
            'select.genericlist', $options, 'p', 'class="jlnav-project"',
            'value', 'text', $this->_project_id
        );
    }

    /**
     * returns the selector for teams
     * 
     * @return string html select
     */
    public function getTeamSelect()
    {
        if (!$this->_project_id) {
            return false;
        }
        $options = array(HTMLHelper::_('select.option', 0, Text::_($this->getParam('text_teams_dropdown'))));
        $res = $this->getTeamsOptions();
        if ($res) {
            $options = array_merge($options, $res);
        }
        return HTMLHelper::_(
            'select.genericlist', $options, 'tid', 'class="jlnav-team"',
            'value', 'text', $this->getTeamId()
        );
    }

    /**
     * returns select for project teams
     * 
     * @return string html select
     */
    protected function getTeamsOptions()
    {
        $mainframe = Factory::getApplication();
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        if (empty($this->_teamoptions)) {
            if (!$this->_project_id) {
                return false;
            }

            $query->select('t.id AS value, t.name AS text');
            $query->from('#__sportsmanagement_project_team AS pt');
            $query->join('INNER', '#__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
            $query->join('INNER', '#__sportsmanagement_team AS t ON st.team_id = t.id ');

            $query->where('pt.project_id = ' . intval($this->_project_id));
            if ($this->_division_id) {
                $query->where('pt.division_id = ' . intval($this->_division_id));
            }
            $query->order('t.name ASC');


            $db->setQuery($query);
            $res = $db->loadObjectList();

            if (!$res) {
                Log::add($db->getErrorMsg());
            }
            $this->_teamoptions = $res;
        }
        return $this->_teamoptions;
    }

    /**
     * return info for current project
     * 
     * @return object
     */
    public function getProject()
    {
        $mainframe = Factory::getApplication();
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        if (!$this->_project) {
            if (!$this->_project_id) {
                return false;
            }

            $query->select('p.id, p.name, p.season_id, p.league_id');
            $query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
            $query->select('CONCAT_WS( \':\', l.id, l.alias ) AS league_slug');
            $query->select('CONCAT_WS( \':\', s.id, s.alias ) AS season_slug');
            $query->from('#__sportsmanagement_project AS p');
            $query->join('LEFT', '#__sportsmanagement_league AS l ON p.league_id = l.id ');
            $query->join('LEFT', '#__sportsmanagement_season AS s ON p.season_id = s.id ');
            $query->where('p.id = ' . $this->_project_id);

            $db->setQuery($query);
            $this->_project = $db->loadObject();
        }
        return $this->_project;
    }

    /**
     * return link for specified view - allow seo consistency
     * 
     * @param  string $view
     * @return string url
     */
    public function getLink($view)
    {
        if (!$this->_project_id) {
            return false;
        }
        switch ($view) {
        case "calendar":
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database', 0);
            $routeparameter['s'] = $this->jinput->getInt('s', 0);
            $routeparameter['p'] = $this->_project_id;
            $routeparameter['tid'] = $this->_team_id;
            $routeparameter['division'] = $this->_division_id;
            $routeparameter['mode'] = 0;
            $routeparameter['ptid'] = 0;
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan', $routeparameter);
            break;

        case "curve":
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database', 0);
            $routeparameter['s'] = $this->jinput->getInt('s', 0);
            $routeparameter['p'] = $this->_project_id;
            $routeparameter['tid1'] = $this->_team_id;
            $routeparameter['tid2'] = 0;
            $routeparameter['division'] = $this->_division_id;
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('curve', $routeparameter);
            break;

        case "eventsranking":
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database', 0);
            $routeparameter['s'] = $this->jinput->getInt('s', 0);
            $routeparameter['p'] = $this->_project_id;
            $routeparameter['division'] = $this->_division_id;
            $routeparameter['tid'] = $this->_team_id;
            $routeparameter['evid'] = 0;
            $routeparameter['mid'] = 0;
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('eventsranking', $routeparameter);
            break;

        case "matrix":
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database', 0);
            $routeparameter['s'] = $this->jinput->getInt('s', 0);
            $routeparameter['p'] = $this->_project_id;
            $routeparameter['division'] = $this->_division_id;
            $routeparameter['r'] = 0;
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('matrix', $routeparameter);
            break;

        case "referees":
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database', 0);
            $routeparameter['s'] = $this->jinput->getInt('s', 0);
            $routeparameter['p'] = $this->_project_id;
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('referees', $routeparameter);
            break;

        case "results":
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database', 0);
            $routeparameter['s'] = $this->jinput->getInt('s', 0);
            $routeparameter['p'] = $this->_project_id;
            $routeparameter['r'] = $this->_round_id;
            $routeparameter['division'] = $this->_division_id;
            $routeparameter['mode'] = 0;
            $routeparameter['order'] = '';
            $routeparameter['layout'] = '';
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);
            break;

        case "resultsmatrix":
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database', 0);
            $routeparameter['s'] = $this->jinput->getInt('s', 0);
            $routeparameter['p'] = $this->_project_id;
            $routeparameter['r'] = $this->_round_id;
            $routeparameter['division'] = $this->_division_id;
            $routeparameter['mode'] = 0;
            $routeparameter['order'] = '';
            $routeparameter['layout'] = '';
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsmatrix', $routeparameter);
            break;

        case "resultsranking":
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database', 0);
            $routeparameter['s'] = $this->jinput->getInt('s', 0);
            $routeparameter['p'] = $this->_project_id;
            $routeparameter['r'] = $this->_round_id;
            $routeparameter['division'] = $this->_division_id;
            $routeparameter['mode'] = 0;
            $routeparameter['order'] = '';
            $routeparameter['layout'] = '';
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute(
                'resultsranking',
                $routeparameter
            );
            break;

        case "resultsrankingmatrix":
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database', 0);
            $routeparameter['s'] = $this->jinput->getInt('s', 0);
            $routeparameter['p'] = $this->_project_id;
            $routeparameter['r'] = $this->_round_id;
            $routeparameter['division'] = $this->_division_id;
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute(
                'resultsrankingmatrix',
                $routeparameter
            );
            break;

        case "roster":
            if (!$this->_team_id) {
                return false;
            }
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database', 0);
            $routeparameter['s'] = $this->jinput->getInt('s', 0);
            $routeparameter['p'] = $this->_project_id;
            $routeparameter['tid'] = $this->_team_id;
            $routeparameter['ptid'] = 0;
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('roster', $routeparameter);
            break;

        case "stats":
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database', 0);
            $routeparameter['s'] = $this->jinput->getInt('s', 0);
            $routeparameter['p'] = $this->_project_id;
            $routeparameter['division'] = $this->_division_id;
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('stats', $routeparameter);
            break;

        case "statsranking":
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database', 0);
            $routeparameter['s'] = $this->jinput->getInt('s', 0);
            $routeparameter['p'] = $this->_project_id;
            $routeparameter['division'] = $this->_division_id;
            $routeparameter['tid'] = 0;
            $routeparameter['sid'] = 0;
            $routeparameter['order'] = '';
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('statsranking', $routeparameter);
            break;

        case "teaminfo":
            if (!$this->_team_id) {
                return false;
            }
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database', 0);
            $routeparameter['s'] = $this->jinput->getInt('s', 0);
            $routeparameter['p'] = $this->_project_id;
            $routeparameter['tid'] = $this->_team_id;
            $routeparameter['ptid'] = 0;
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);
            break;

        case "teamplan":
            if (!$this->_team_id) {
                return false;
            }
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database', 0);
            $routeparameter['s'] = $this->jinput->getInt('s', 0);
            $routeparameter['p'] = $this->_project_id;
            $routeparameter['tid'] = $this->_team_id;
            $routeparameter['division'] = $this->_division_id;
            $routeparameter['mode'] = 0;
            $routeparameter['ptid'] = 0;
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan', $routeparameter);
            break;

        case "teamstats":
            if (!$this->_team_id) {
                return false;
            }
            $link = sportsmanagementHelperRoute::getTeamStatsRoute(
                $this->_project_id, $this
                    ->_team_id
            );
            break;

        case "treetonode":
            $link = sportsmanagementHelperRoute::getBracketsRoute($this->_project_id);
            break;

        case "separator":
            return false;

        default:
        case "ranking":
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database', 0);
            $routeparameter['s'] = $this->jinput->getInt('s', 0);
            $routeparameter['p'] = $this->_project_id;
            $routeparameter['type'] = 0;
            $routeparameter['r'] = $this->_round_id;
            $routeparameter['from'] = 0;
            $routeparameter['to'] = 0;
            $routeparameter['division'] = $this->_division_id;
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);
        }
        return $link;
    }

    /**
     * return param value or default if not found
     * 
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    protected function getParam($name, $default = null)
    {
        return $this->_params->get($name, $default);
    }
}
