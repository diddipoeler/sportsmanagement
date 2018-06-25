<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
* @version   1.0.05
* @file      teams.php
* @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license   This file is part of SportsManagement.
* @package   sportsmanagement
* @subpackage models
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementModelTeams
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelTeams extends JSMModelList {

    var $_identifier = "teams";

    /**
     * sportsmanagementModelTeams::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array()) {
        $config['filter_fields'] = array(
            't.name',
            't.sports_type_id',
            't.website',
            't.middle_name',
            't.short_name',
            't.info',
            't.alias',
            't.picture',
            't.id',
            't.ordering',
            't.checked_out',
            't.checked_out_time',
            't.agegroup_id',
            'ag.name'
        );
        parent::__construct($config);

        $this->app = JFactory::getApplication();
        $this->jinput = $this->app->input;
        $this->option = $this->jinput->getCmd('option');

        $this->club_id = $this->jinput->post->get('club_id');
        if (empty($this->club_id)) {
            $this->club_id = $this->jinput->get->get('club_id');
        }

        //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jinput<br><pre>'.print_r($this->jinput ,true).'</pre>'),'');
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_id<br><pre>'.print_r($this->club_id,true).'</pre>'),'');

        $getDBConnection = sportsmanagementHelper::getDBConnection();
        parent::setDbo($getDBConnection);

        $this->user = JFactory::getUser();
        $this->jsmdb = $this->getDbo();
        $this->query = $this->jsmdb->getQuery(true);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since	1.6
     */
    protected function populateState($ordering = 't.name', $direction = 'asc') {

        if (JComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend')) {
            $this->jsmapp->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' context -> ' . $this->context . ''), '');
            $this->jsmapp->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' identifier -> ' . $this->_identifier . ''), '');
        }

        // Load the filter state.
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $published);
        $temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.sports_type', 'filter_sports_type', '');
        $this->setState('filter.sports_type', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', '');
        $this->setState('filter.search_nation', $temp_user_request);
        $value = $this->jsmjinput->getUInt('limitstart', 0);
        $this->setState('list.start', $value);

        $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
        $this->setState('list.limit', $value);
        // List state information.
        parent::populateState($ordering, $direction);
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
        $this->setState('list.start', $value);
    }

    /**
     * sportsmanagementModelTeams::getListQuery()
     * 
     * @return
     */
    function getListQuery() {
        // Select some fields
        $this->query->select('t.*');
        $this->query->select('st.name AS sportstype');
        $this->query->select('ag.name AS agename');
        // From table
        $this->query->from('#__sportsmanagement_team AS t');
        $this->query->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = t.sports_type_id');
        $this->query->join('LEFT', '#__sportsmanagement_agegroup as ag ON ag.id = t.agegroup_id');
        // Join over the clubs
        $this->query->select('c.name As clubname,c.country');
        $this->query->join('LEFT', '#__sportsmanagement_club AS c ON c.id = t.club_id');
        // Join over the users for the checked out user.
        $this->query->select('uc.name AS editor');
        $this->query->join('LEFT', '#__users AS uc ON uc.id = t.checked_out');

        if ($this->getState('filter.search')) {
            $this->query->where('LOWER(t.name) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
        }

        if ($this->getState('filter.search_nation')) {
            $this->query->where('c.country LIKE ' . $this->jsmdb->Quote('' . $this->getState('filter.search_nation') . ''));
        }
        if ($this->getState('filter.sports_type')) {
            $this->query->where('t.sports_type_id = ' . $this->getState('filter.sports_type'));
        }
        if (is_numeric($this->getState('filter.state'))) {
            $this->jsmquery->where('t.published = ' . $this->getState('filter.state'));
        }
        if ($this->club_id) {
            $this->app->setUserState("$this->option.club_id", $this->club_id);
            $this->query->where('club_id =' . $this->club_id);
        } else {
            $this->app->setUserState("$this->option.club_id", '0');
        }

        $this->query->order($this->jsmdb->escape($this->getState('list.ordering', 't.name')) . ' ' .
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));

        if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = ' <br><pre>' . print_r($this->query->dump(), true) . '</pre>';
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
        }

        return $this->query;
    }

    /**
     * sportsmanagementModelTeams::getTeamListSelect()
     * 
     * @return
     */
    public function getTeamListSelect() {

        $starttime = microtime();
        $results = array();
        // Select some fields
        $this->query->select('id,id AS value,name,club_id,short_name, middle_name,info');
        // From table
        $this->query->from('#__sportsmanagement_team');
        $this->query->order('name');

        $this->jsmdb->setQuery($this->query);

        if (COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO) {
            $this->app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' Ausfuehrungszeit query<br><pre>' . print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()), true) . '</pre>'), 'Notice');
        }

        if ($results = $this->jsmdb->loadObjectList()) {
            foreach ($results AS $team) {
                $team->text = $team->name . ' - (' . $team->info . ')';
            }
            return $results;
        }
        //return false;
        return $results;
    }

    /**
     * sportsmanagementModelTeams::getTeams()
     * 
     * @param mixed $playground_id
     * @return
     */
    function getTeams($playground_id) {

        $teams = array();

        //$playground = self::getPlayground();
        if ($playground_id > 0) {
            $this->jsmquery->clear();
            // Select some fields
            $this->jsmquery->select('pt.id, st.team_id, pt.project_id');
            // From table
            $this->jsmquery->from('#__sportsmanagement_project_team as pt');
            $this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
            $this->jsmquery->where('pt.standard_playground = ' . (int) $playground_id);

            $starttime = microtime();

            $this->jsmdb->setQuery($this->jsmquery);
            if (COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO) {
                $this->app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' Ausfuehrungszeit query<br><pre>' . print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()), true) . '</pre>'), 'Notice');
            }

            $rows = $this->jsmdb->loadObjectList();

            foreach ($rows as $row) {
                $teams[$row->id]->project_team[] = $row;
                // Select some fields
                $this->jsmquery->clear();
                $this->jsmquery->select('name, short_name, notes');
                // From table
                $this->jsmquery->from('#__sportsmanagement_team');
                $this->jsmquery->where('id=' . (int) $row->team_id);

                $starttime = microtime();
                $this->jsmdb->setQuery($this->jsmquery);
                if (COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO) {
                    $this->app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' Ausfuehrungszeit query<br><pre>' . print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()), true) . '</pre>'), 'Notice');
                }

                $teams[$row->id]->teaminfo[] = $this->jsmdb->loadObjectList();

                // Select some fields
                $this->jsmquery->clear();
                $this->jsmquery->select('name');
                // From table
                $this->jsmquery->from('#__sportsmanagement_project');
                $this->jsmquery->where('id=' . $row->project_id);
                $starttime = microtime();
                $this->jsmdb->setQuery($this->jsmquery);
                if (COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO) {
                    $this->app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' Ausfuehrungszeit query<br><pre>' . print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()), true) . '</pre>'), 'Notice');
                }
                $teams[$row->id]->project = $this->jsmdb->loadResult();
            }
        }
        return $teams;
    }

    /**
     * sportsmanagementModelTeams::getTeamsFromMatches()
     * 
     * @param mixed $games
     * @return
     */
    function getTeamsFromMatches(& $games) {

        $teams = Array();

        if (!count($games)) {
            return $teams;
        }

        foreach ($games as $m) {
            $teamsId[] = $m->team1;
            $teamsId[] = $m->team2;
        }
        $listTeamId = implode(",", array_unique($teamsId));

        // Select some fields
        $this->jsmquery->clear();
        $this->jsmquery->select('t.id, t.name');
        // From table
        $this->jsmquery->from('#__sportsmanagement_team AS t');
        $this->jsmquery->where('t.id IN (' . $listTeamId . ')');

        $this->jsmdb->setQuery($this->jsmquery);
        $result = $this->jsmdb->loadObjectList();

        foreach ($result as $r) {
            $teams[$r->id] = $r;
        }

        return $teams;
    }

}

?>
