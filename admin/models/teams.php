<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       teams.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelTeams
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelTeams extends JSMModelList
{
	var $_identifier = "teams";

	/**
	 * sportsmanagementModelTeams::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
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
			'ag.name','state','search_nation','search_agegroup','sports_type'
		);
		parent::__construct($config);

		$this->app    = Factory::getApplication();
		$this->jinput = $this->app->input;
		$this->option = $this->jinput->getCmd('option');

		$this->club_id = $this->jinput->post->get('club_id');

		if (empty($this->club_id))
		{
			$this->club_id = $this->jinput->get->get('club_id');
		}

		$getDBConnection = sportsmanagementHelper::getDBConnection();
		parent::setDbo($getDBConnection);

		$this->user  = Factory::getUser();
		$this->jsmdb = $this->getDbo();
		$this->query = $this->jsmdb->getQuery(true);
	}

	/**
	 * sportsmanagementModelTeams::getListQuery()
	 *
	 * @return
	 */
	function getListQuery()
	{
		$this->query->select('t.*');
		$this->query->select('st.name AS sportstype');
		$this->query->select('ag.name AS agename');
		$this->query->from('#__sportsmanagement_team AS t');
		$this->query->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = t.sports_type_id');
		$this->query->join('LEFT', '#__sportsmanagement_agegroup as ag ON ag.id = t.agegroup_id');
		$this->query->select('c.name As clubname,c.country');
		$this->query->join('LEFT', '#__sportsmanagement_club AS c ON c.id = t.club_id');
		$this->query->select('uc.name AS editor');
		$this->query->join('LEFT', '#__users AS uc ON uc.id = t.checked_out');

		if ($this->getState('filter.search'))
		{
			$this->query->where('LOWER(t.name) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
		}

		if ($this->getState('filter.search_nation'))
		{
			$this->query->where('c.country LIKE ' . $this->jsmdb->Quote('' . $this->getState('filter.search_nation') . ''));
		}

		if ($this->getState('filter.sports_type'))
		{
			$this->query->where('t.sports_type_id = ' . $this->getState('filter.sports_type'));
		}
		
		if ($this->getState('filter.search_agegroup'))
		{
			$this->query->where('t.agegroup_id = ' . $this->getState('filter.search_agegroup'));
		}

		if (is_numeric($this->getState('filter.state')))
		{
			$this->jsmquery->where('t.published = ' . $this->getState('filter.state'));
		}

		if ($this->club_id)
		{
			$this->app->setUserState("$this->option.club_id", $this->club_id);
			$this->query->where('club_id =' . $this->club_id);
		}
		else
		{
			$this->app->setUserState("$this->option.club_id", '0');
		}

		if ($this->jsmapp->input->getVar('layout') == 'assignteams')
		{
			$this->_season_id = $this->jsmapp->input->get('season_id');
			$this->jsmsubquery1->select('stp.team_id');
			$this->jsmsubquery1->from('#__sportsmanagement_season_team_id AS stp ');
			$this->jsmsubquery1->where('stp.season_id = ' . $this->_season_id);
			$this->query->where('t.id NOT IN (' . $this->jsmsubquery1 . ')');
		}

		$this->query->order(
			$this->jsmdb->escape($this->getState('list.ordering', 't.name')) . ' ' .
			$this->jsmdb->escape($this->getState('list.direction', 'ASC'))
		);

		return $this->query;
	}

	/**
	 * sportsmanagementModelTeams::getTeamListSelect()
	 *
	 * @return
	 */
	public function getTeamListSelect()
	{
		$starttime = microtime();
		$results   = array();

		$this->query->select('id,id AS value,name,club_id,short_name, middle_name,info');
		$this->query->from('#__sportsmanagement_team');
		$this->query->order('name');
		$this->jsmdb->setQuery($this->query);

		if ($results = $this->jsmdb->loadObjectList())
		{
			foreach ($results AS $team)
			{
				$team->text = $team->name . ' - (' . $team->info . ')';
			}

			return $results;
		}

		return $results;
	}

	/**
	 * sportsmanagementModelTeams::getTeams()
	 *
	 * @param   mixed  $playground_id
	 *
	 * @return
	 */
	function getTeams($playground_id)
	{
		$teams = array();

		if ($playground_id > 0)
		{
			$this->jsmquery->clear();
			$this->jsmquery->select('pt.id, st.team_id, pt.project_id');
			$this->jsmquery->select('CONCAT_WS(\':\',p.id,p.alias) AS project_slug');
			$this->jsmquery->from('#__sportsmanagement_project_team as pt');
			$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
			$this->jsmquery->join('INNER', '#__sportsmanagement_project as p ON p.id = pt.project_id ');
			$this->jsmquery->where('pt.standard_playground = ' . (int) $playground_id);

			$starttime = microtime();

			$this->jsmdb->setQuery($this->jsmquery);
			$rows = $this->jsmdb->loadObjectList();

			foreach ($rows as $row)
			{
				$teams[$row->id] = new stdClass();
				$teams[$row->id]->project_team[] = $row;

				$this->jsmquery->clear();
				$this->jsmquery->select('t.name, t.short_name, t.notes');
				$this->jsmquery->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
				$this->jsmquery->from('#__sportsmanagement_team as t');
				$this->jsmquery->where('t.id=' . (int) $row->team_id);
				$starttime = microtime();
				$this->jsmdb->setQuery($this->jsmquery);
				$teams[$row->id]->teaminfo[] = $this->jsmdb->loadObjectList();
				$this->jsmquery->clear();
				$this->jsmquery->select('name');
				$this->jsmquery->from('#__sportsmanagement_project');
				$this->jsmquery->where('id=' . $row->project_id);
				$starttime = microtime();
				$this->jsmdb->setQuery($this->jsmquery);
				$teams[$row->id]->project = $this->jsmdb->loadResult();
			}
		}

		return $teams;
	}

	/**
	 * sportsmanagementModelTeams::getTeamsFromMatches()
	 *
	 * @param   mixed  $games
	 *
	 * @return
	 */
	function getTeamsFromMatches(&$games)
	{
		$teams = Array();

		if (!count($games))
		{
			return $teams;
		}

		foreach ($games as $m)
		{
			$teamsId[] = $m->team1;
			$teamsId[] = $m->team2;
		}

		$listTeamId = implode(",", array_unique($teamsId));

		$this->jsmquery->clear();
		$this->jsmquery->select('t.id, t.name');
		$this->jsmquery->from('#__sportsmanagement_team AS t');
		$this->jsmquery->where('t.id IN (' . $listTeamId . ')');
		$this->jsmdb->setQuery($this->jsmquery);
		$result = $this->jsmdb->loadObjectList();

		foreach ($result as $r)
		{
			$teams[$r->id] = $r;
		}

		return $teams;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since 1.6
	 */
	protected function populateState($ordering = 't.name', $direction = 'asc')
	{

		if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend'))
		{
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' context -> ' . $this->context . ''), '');
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' identifier -> ' . $this->_identifier . ''), '');
		}
		$list = $this->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array');
        $stateVar = $this->jsmapp->getUserStateFromRequest( "com_sportsmanagement.limit", 'limit', 0 );

		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));
		$this->setState('filter.sports_type', $this->getUserStateFromRequest($this->context . '.filter.sports_type', 'filter_sports_type', ''));
		$this->setState('filter.search_nation', $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', ''));
		$this->setState('filter.search_agegroup', $this->getUserStateFromRequest($this->context . '.filter.search_agegroup', 'filter_search_agegroup', ''));
		 if ( $stateVar )
      {
      $this->setState('list.limit',$stateVar );  
      }
      else
      {
      $this->setState('list.limit', $this->getUserStateFromRequest($this->context . '.list.limit', 'list_limit', $this->jsmapp->get('list_limit'), 'int'));
      }
		$this->setState('list.start', $this->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int'));
        $orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 't.name';
		}

		$this->setState('list.ordering', $orderCol);
		$listOrder = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);
	}

}

