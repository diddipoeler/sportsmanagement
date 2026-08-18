<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       teams.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementModelTeams
 */
class sportsmanagementModelTeams extends JSMModelList
{
	protected $_identifier = 'teams';
	protected $_season_id = 0;
	protected $app;
	protected $jinput;
	protected $option;
	protected $club_id = 0;

	/**
	 * Constructor.
	 *
	 * @param array $config Model configuration.
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
			'ag.name',
			'state',
			'search_nation',
			'search_agegroup',
			'sports_type'
		);

		parent::__construct($config);

		$this->app     = Factory::getApplication();
		$this->jinput  = $this->app->getInput();
		$this->option  = $this->jinput->getCmd('option', 'com_sportsmanagement');
		$this->club_id = $this->jinput->post->getInt('club_id', 0);

		if (!$this->club_id)
		{
			$this->club_id = $this->jinput->getInt('club_id', 0);
		}

		$this->setDatabase(sportsmanagementHelper::getDBConnection());
	}

	/**
	 * Build the teams list query.
	 *
	 * @return mixed Database query object.
	 */
	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->createQuery();

		$query->select('t.*');
		$query->select('st.name AS sportstype');
		$query->select('ag.name AS agename');
		$query->from('#__sportsmanagement_team AS t');
		$query->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = t.sports_type_id');
		$query->join('LEFT', '#__sportsmanagement_agegroup AS ag ON ag.id = t.agegroup_id');
		$query->select('c.name AS clubname,c.country');
		$query->join('LEFT', '#__sportsmanagement_club AS c ON c.id = t.club_id');
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = t.checked_out');

		$search = trim((string) $this->getState('filter.search'));

		if ($search !== '')
		{
			$query->where('LOWER(t.name) LIKE ' . $db->quote('%' . strtolower($search) . '%'));
		}

		$nation = trim((string) $this->getState('filter.search_nation'));

		if ($nation !== '')
		{
			$query->where('c.country = ' . $db->quote($nation));
		}

		$sportsType = (int) $this->getState('filter.sports_type');

		if ($sportsType > 0)
		{
			$query->where('t.sports_type_id = ' . $sportsType);
		}

		$ageGroup = (int) $this->getState('filter.search_agegroup');

		if ($ageGroup > 0)
		{
			$query->where('t.agegroup_id = ' . $ageGroup);
		}

		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('t.published = ' . (int) $state);
		}

		if ($this->club_id)
		{
			$this->app->setUserState($this->option . '.club_id', $this->club_id);
			$query->where('t.club_id = ' . (int) $this->club_id);
		}
		else
		{
			$this->app->setUserState($this->option . '.club_id', 0);
		}

		if ($this->jinput->getCmd('layout') === 'assignteams')
		{
			$this->_season_id = $this->jinput->getInt('season_id', 0);

			if ($this->_season_id > 0)
			{
				$subquery = $db->createQuery();
				$subquery->select('stp.team_id');
				$subquery->from('#__sportsmanagement_season_team_id AS stp');
				$subquery->where('stp.season_id = ' . (int) $this->_season_id);
				$query->where('t.id NOT IN (' . $subquery . ')');
			}
		}

		$query->order(
			$db->escape($this->getState('list.ordering', 't.name')) . ' '
			. $db->escape($this->getState('list.direction', 'ASC'))
		);

		return $query;
	}

	/**
	 * Return teams for selection lists.
	 *
	 * @return array
	 */
	public function getTeamListSelect()
	{
		$db    = $this->getDatabase();
		$query = $db->createQuery();
		$query->select('id,id AS value,name,club_id,short_name,middle_name,info');
		$query->from('#__sportsmanagement_team');
		$query->order('name');
		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results as $team)
		{
			$team->text = $team->name . ' - (' . $team->info . ')';
		}

		return $results;
	}

	/**
	 * Return project teams using a playground.
	 *
	 * @param int $playground_id Playground ID.
	 *
	 * @return array
	 */
	public function getTeams($playground_id)
	{
		$teams = array();
		$playgroundId = (int) $playground_id;

		if ($playgroundId <= 0)
		{
			return $teams;
		}

		$db    = $this->getDatabase();
		$query = $db->createQuery();
		$query->select('pt.id, st.team_id, pt.project_id');
		$query->select("CONCAT_WS(':',p.id,p.alias) AS project_slug");
		$query->from('#__sportsmanagement_project_team AS pt');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id');
		$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt.project_id');
		$query->where('pt.standard_playground = ' . $playgroundId);
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $row)
		{
			$teams[$row->id] = new stdClass();
			$teams[$row->id]->project_team = array($row);

			$query = $db->createQuery();
			$query->select('t.name, t.short_name, t.notes');
			$query->select("CONCAT_WS(':',t.id,t.alias) AS team_slug");
			$query->from('#__sportsmanagement_team AS t');
			$query->where('t.id = ' . (int) $row->team_id);
			$db->setQuery($query);
			$teams[$row->id]->teaminfo = array($db->loadObjectList());

			$query = $db->createQuery();
			$query->select('name');
			$query->from('#__sportsmanagement_project');
			$query->where('id = ' . (int) $row->project_id);
			$db->setQuery($query);
			$teams[$row->id]->project = $db->loadResult();
		}

		return $teams;
	}

	/**
	 * Return team records referenced by matches.
	 *
	 * @param array $games Match records.
	 *
	 * @return array
	 */
	public function getTeamsFromMatches(&$games)
	{
		$teams = array();

		if (!count($games))
		{
			return $teams;
		}

		$teamIds = array();

		foreach ($games as $match)
		{
			$teamIds[] = (int) $match->team1;
			$teamIds[] = (int) $match->team2;
		}

		$teamIds = array_values(array_unique(array_filter($teamIds, static fn($id) => $id > 0)));

		if (!$teamIds)
		{
			return $teams;
		}

		$db    = $this->getDatabase();
		$query = $db->createQuery();
		$query->select('t.id, t.name');
		$query->from('#__sportsmanagement_team AS t');
		$query->where('t.id IN (' . implode(',', $teamIds) . ')');

		try
		{
			$db->setQuery($query);
			$result = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage(
				Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
				'notice'
			);
			$this->app->enqueueMessage(
				Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__),
				'notice'
			);
			return $teams;
		}

		foreach ($result as $team)
		{
			$teams[$team->id] = $team;
		}

		return $teams;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param string $ordering  Ordering field.
	 * @param string $direction Ordering direction.
	 *
	 * @return void
	 */
	protected function populateState($ordering = 't.name', $direction = 'asc')
	{
		if (ComponentHelper::getParams($this->option)->get('show_debug_info_backend'))
		{
			$this->app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' context -> ' . $this->context), '');
			$this->app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' identifier -> ' . $this->_identifier), '');
		}

		$stateLimit = (int) $this->app->getUserStateFromRequest('com_sportsmanagement.limit', 'limit', 0);

		$this->setState(
			'filter.search',
			$this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string')
		);
		$this->setState(
			'filter.state',
			$this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string')
		);
		$this->setState(
			'filter.sports_type',
			$this->getUserStateFromRequest($this->context . '.filter.sports_type', 'filter_sports_type', '')
		);
		$this->setState(
			'filter.search_nation',
			$this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', '')
		);
		$this->setState(
			'filter.search_agegroup',
			$this->getUserStateFromRequest($this->context . '.filter.search_agegroup', 'filter_search_agegroup', '')
		);

		if ($stateLimit > 0)
		{
			$this->setState('list.limit', $stateLimit);
		}
		else
		{
			$this->setState(
				'list.limit',
				$this->getUserStateFromRequest(
					$this->context . '.list.limit',
					'list_limit',
					(int) Factory::getConfig()->get('list_limit', 20),
					'int'
				)
			);
		}

		$this->setState(
			'list.start',
			$this->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int')
		);

		$orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($orderCol, $this->filter_fields, true))
		{
			$orderCol = 't.name';
		}

		$this->setState('list.ordering', $orderCol);
		$listOrder = $this->getUserStateFromRequest(
			$this->context . '.filter_order_Dir',
			'filter_order_Dir',
			'',
			'cmd'
		);

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''), true))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);
	}
}
