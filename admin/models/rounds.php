<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       rounds.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelRounds
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelRounds extends JSMModelList
{
	var $_identifier = "rounds";

	static $_project_id = 0;

	/**
	 * sportsmanagementModelRounds::__construct()
	 *
	 * @param   mixed $config
	 * @return void
	 */
	public function __construct($config = array())
	{
		$config['filter_fields'] = array(
						'r.name',
						'r.alias',
						'r.roundcode',
						'r.round_date_first',
						'r.round_date_last',
						'r.id',
						'r.ordering'
						);
		parent::__construct($config);
				parent::setDbo($this->jsmdb);
				self::$_project_id    = $this->jsmjinput->getInt('pid', 0);

		if (!self::$_project_id)
		{
			self::$_project_id    = $this->jsmapp->getUserState("$this->jsmoption.pid", '0');
		}

							  $this->jsmapp->setUserState("$this->jsmoption.pid", self::$_project_id);

	}

		  /**
		   * Method to auto-populate the model state.
		   *
		   * Note. Calling getState in this method will result in recursion.
		   *
		   * @since 1.6
		   */
	protected function populateState($ordering = null, $direction = null)
	{
		if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend'))
		{
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' context -> ' . $this->context . ''), '');
			  $this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' identifier -> ' . $this->_identifier . ''), '');
		}

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $published);
		$value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
		$this->setState('list.limit', $value);

		// List state information.
		$value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);

		// Filter.order
		$orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'r.roundcode';
		}

		$this->setState('list.ordering', $orderCol);
		$listOrder = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);
	}

	/**
	 * sportsmanagementModelRounds::getListQuery()
	 *
	 * @return
	 */
	protected function getListQuery()
	{

		// Create a new query object.
		$this->jsmquery->clear();

		// Select some fields
		$this->jsmquery->select('p.season_id');

		// From table
		$this->jsmquery->from('#__sportsmanagement_project AS p');
		$this->jsmquery->where('p.id = ' . self::$_project_id);
		$this->jsmdb->setQuery($this->jsmquery);
		$this->_season_id = $this->jsmdb->loadResult();
		$this->jsmapp->setUserState("$this->jsmoption.season_id", $this->_season_id);

			  // Create a new query object.
		$this->jsmquery->clear();
		$this->jsmsubquery1->clear();
		$this->jsmsubquery2->clear();
		$this->jsmsubquery3->clear();

			  // Select some fields
		$this->jsmquery->select('r.*');

		// From the rounds table
		$this->jsmquery->from('#__sportsmanagement_round as r');

		// Join match
		$this->jsmsubquery1->select('count(published)');
		$this->jsmsubquery1->from('#__sportsmanagement_match ');
		$this->jsmsubquery1->where('round_id=r.id and published=0');

		// Join match
		$this->jsmsubquery2->select('count(*)');
		$this->jsmsubquery2->from('#__sportsmanagement_match ');
		$this->jsmsubquery2->where('round_id=r.id AND cancel=0 AND (team1_result is null OR team2_result is null)');

		// Join match
		$this->jsmsubquery3->select('count(*)');
		$this->jsmsubquery3->from('#__sportsmanagement_match ');
		$this->jsmsubquery3->where('round_id=r.id');

			  $this->jsmquery->select('(' . $this->jsmsubquery1 . ') AS countUnPublished');
		$this->jsmquery->select('(' . $this->jsmsubquery2 . ') AS countNoResults');
		$this->jsmquery->select('(' . $this->jsmsubquery3 . ') AS countMatches');

				// $query->where(' r.project_id = '.$this->_project_id);
		  $this->jsmquery->where(' r.project_id = ' . self::$_project_id);

		if ($this->getState('filter.search'))
		{
			$this->jsmquery->where(' LOWER(r.name) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
		}

		if (is_numeric($this->getState('filter.state')))
		{
			$this->jsmquery->where('r.published = ' . $this->getState('filter.state'));
		}

			  $this->jsmquery->order(
				  $this->jsmdb->escape($this->getState('list.ordering', 'r.roundcode')) . ' ' .
				  $this->jsmdb->escape($this->getState('list.direction', 'ASC'))
			  );

			 return $this->jsmquery;
	}

	/**
	 * return count of  project rounds
	 *
	 * @param  int project_id
	 * @return integer
	 */
	function getRoundsCount($project_id)
	{
		 // Create a new query object.
		$this->jsmquery->clear();

		 // Select some fields
		$this->jsmquery->select('count(*) AS count');

		// From the table
		$this->jsmquery->from('#__sportsmanagement_round');
		$this->jsmquery->where('project_id = ' . $project_id);

		try
		{
			  $this->jsmdb->setQuery($this->jsmquery);

			return $this->jsmdb->loadResult();
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');

			return false;
		}
	}

	/**
	 *
	 * @param   int $projectid
	 * @return assocarray
	 */
	public static function getFirstRound($projectid,$cfg_which_database = 0)
	{
		 $app = Factory::getApplication();
		$option = $app->input->getCmd('option');

			  $db = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

			  // Select some fields
		$query->select('CONCAT_WS( \':\', id, alias ) AS id');
		$query->select('id AS round_id');
		$query->select('roundcode');

		// From the table
		$query->from('#__sportsmanagement_round');
		$query->where('project_id = ' . $projectid);
		$query->order('roundcode ASC, id ASC');

		try
		{
			 $db->setQuery($query);
			$result = $db->loadAssocList();

			if ($result)
			{
				return $result[0];
			}
			else
			{
				return false;
			}
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_($e->getMessage()), 'error');
			$app->enqueueMessage(Text::_(COM_SPORTSMANAGEMENT_RANKING_NO_ROUNDS), 'Notice');

			return false;
		}

	}

	/**
	 *
	 * @param   int $projectid
	 * @return assocarray
	 */
	public static function getLastRound($projectid,$cfg_which_database = 0)
	{
		$app = Factory::getApplication();
		$option = $app->input->getCmd('option');

			  $db = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

			  // Select some fields
		$query->select('CONCAT_WS( \':\', id, alias ) AS id');
		$query->select('id AS round_id');
		$query->select('roundcode');

		// From the table
		$query->from('#__sportsmanagement_round');
		$query->where('project_id = ' . $projectid);
		$query->order('roundcode DESC, id DESC');

		try
		{
			$db->setQuery($query);
			$result = $db->loadAssocList();

			if ($result)
			{
				return $result[0];
			}
			else
			{
				return false;
			}
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_($e->getMessage()), 'error');
			$app->enqueueMessage(Text::_(COM_SPORTSMANAGEMENT_RANKING_NO_ROUNDS), 'Notice');

			return false;
		}

	}

	/**
	 *
	 * @param   int $roundid
	 * @param   int $projectid
	 * @return assocarray
	 */
	public static function getPreviousRound($roundid, $projectid,$cfg_which_database = 0)
	{
		$app = Factory::getApplication();
		$option = $app->input->getCmd('option');
		$db = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

			  // Select some fields
		$query->select('CONCAT_WS( \':\', id, alias ) AS id');
		$query->select('roundcode');

		// From the table
		$query->from('#__sportsmanagement_round');
		$query->where('project_id = ' . $projectid);
		$query->order('id ASC');

		try
		{
			$db->setQuery($query);
			$result = $db->loadAssocList();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_($e->getMessage()), 'error');
			$app->enqueueMessage(Text::_(COM_SPORTSMANAGEMENT_RANKING_NO_ROUNDS), 'Notice');

			return false;
		}

		for ($i = 0,$n = count($result); $i < $n; $i++)
		{
			if (isset($result[$i - 1]))
			{
				return $result[$i - 1];
			}
			else
			{
				return $result[$i];
			}
		}
	}

	/**
	 *
	 * @param   int $roundid
	 * @param   int $projectid
	 * @return assocarray
	 */
	public static function getNextRound($roundid, $projectid,$cfg_which_database = 0)
	{
		$app = Factory::getApplication();
		$option = $app->input->getCmd('option');
		$db = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

				// Select some fields
		  $query->select('CONCAT_WS( \':\', id, alias ) AS id');
		$query->select('roundcode');

		// From the table
		$query->from('#__sportsmanagement_round');
		$query->where('project_id = ' . $projectid);
		$query->order('id ASC');

		try
		{
			$db->setQuery($query);
			$result = $db->loadAssocList();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_($e->getMessage()), 'error');
			$app->enqueueMessage(Text::_(COM_SPORTSMANAGEMENT_RANKING_NO_ROUNDS), 'Notice');

			return false;
		}

		for ($i = 0,$n = count($result); $i < $n; $i++)
		{
			if ($result[$i]['id'] == $roundid)
			{
				if (isset($result[$i + 1]))
				{
					return $result[$i + 1];
				}
				else
				{
					return $result[$i];
				}
			}
		}
	}

	/**
	 * Get the next round by todays date
	 *
	 * @param   int $project_id
	 * @return assocarray
	 */
	function getNextRoundByToday($projectid)
	{
		 // Select some fields
		 $this->jsmquery->clear();
		$this->jsmquery->select('id, roundcode, round_date_first , round_date_last');

		// From the table
		$this->jsmquery->from('#__sportsmanagement_round');
		$this->jsmquery->where('project_id = ' . $projectid);
		$this->jsmquery->where('DATEDIFF(CURDATE(), DATE(round_date_first)) < 0');
		$this->jsmquery->order('round_date_first ASC');

		try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->loadAssocList();

			return $result;
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');

			return false;
		}
	}

	/**
	 * return project rounds as array of objects(roundid as value, name as text)
	 *
	 * @param   string $ordering
	 * @return array
	 */
	public static function getRoundsOptions($project_id, $ordering='ASC',$cfg_which_database = 0)
	{
		$app = Factory::getApplication();
		$option = $app->input->getCmd('option');
		$db = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

			   // Select some fields
		$query->select('CONCAT_WS( \':\', id, alias ) AS value');
		$query->select('name AS text');
		$query->select('id, name, round_date_first, round_date_last, roundcode');

		// From the table
		$query->from('#__sportsmanagement_round');
		$query->where('project_id = ' . $project_id);
		$query->order('roundcode ' . $ordering);

		try
		{
			 $db->setQuery($query);
				$result = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');

			return false;
		}

			  return $result;
	}

	function populate($project_id, $scheduling, $time, $interval, $start, $roundname, $teamsorder = null)
	{
		 $db = sportsmanagementHelper::getDBConnection();
		 $date = Factory::getDate();
		$user = Factory::getUser();
		 include_once JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'class.roundrobin.php';

		if (!strtotime($start))
		{
			 $start = strftime('%Y-%m-%d');
		}

		if (!preg_match("/^[0-9]+:[0-9]+$/", $time))
		{
			$time = '20:00';
		}

				$mdlProject = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
		  $teams = $mdlProject->getProjectTeamsOptions($project_id);

		if ($teamsorder)
		{
			$ordered = array();

			foreach ($teamsorder as $ptid)
			{
				foreach ($teams as $t)
				{
					if ($t->value == $ptid)
					{
						$ordered[] = $t;
						break;
					}
				}
			}

			if (count($ordered))
			{
				$teams = $ordered;
			}
		}

				  // Diddipoeler
			$rrteams = array();

		foreach ($teams as $team)
		{
					$rrteams[] = $team;
		}

			$roundrobin = new roundrobin($rrteams);
			$roundrobin->free_ticket = false; // Free tickets off
			$roundrobin->create_matches();

		if ($roundrobin->finished)
		{
			$i = 1;

			while ($roundrobin->next_matchday())
			{
					echo "-------Matchday " . $i . "-------<br />";

				while ($match = $roundrobin->next_match())
				{
						// Echo $match[0]."  <b>vs</b>  ".$match[1]."<br />";
				}

					$i++;
					echo"<br />";
			}
		}

		if (!$teams || !count($teams))
		{
			$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_ERROR_NO_TEAM'));

			return false;
		}

			$rounds = $this->getRoundsOptions($project_id);
			$rounds = $rounds ? $rounds : array();

		if ($scheduling < 2)
		{
			include_once JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'RRobin.class.php';
			$helper = new RRobin;
			$helper->create($teams);
			$schedule = $helper->getSchedule($scheduling + 1);
		}
		else
		{
			$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_ERROR_UNDEFINED_SCHEDULING'));

			return false;
		}

				$current_date = null;
			$current_code = 0;

		foreach ($schedule as $k => $games)
		{
			if (isset($rounds[$k])) // Round exists
			{
				$round_id  = $rounds[$k]->id;
				$current_date = $rounds[$k]->round_date_first;
				$current_code = $rounds[$k]->roundcode;
			}
			else // Create the round !
			{
				$round = new stdClass;
				$round->project_id       = $project_id;
				$round->round_date_first = strtotime($current_date) ? strftime('%Y-%m-%d', strtotime($current_date) + $interval * 24 * 3600) : $start;
				$round->round_date_last = $round->round_date_first;
				$round->roundcode = $current_code ? $current_code + 1 : 1;
				$round->name      = sprintf($roundname, $round->roundcode);
				$round->modified = $date->toSql();
				$round->modified_by = $user->get('id');
				/**
 * Insert the object into the table.
 */
				try
				{
					$resultinsert = $db->insertObject('#__sportsmanagement_round', $round);
					$result = $db->insertid();
				}
				catch (Exception $e)
				{
					$this->setError('COM_SPORTSMANAGEMENT_ADMIN_ROUND_FAILED');

					return false;
				}

					$current_date = $round->round_date_first;
					$current_code = $round->roundcode;

					  $round_id = $result;
			}

				  // Create games !
			foreach ($games as $g)
			{
				if (!isset($g[0]) || !isset($g[1]))
				{
					// Happens if number of team is odd ! one team gets a by
					continue;
				}

				$game = new stdClass;
				$game->round_id = $round_id;
				  $game->division_id = 0;
				$game->projectteam1_id = $g[0]->value;
				$game->projectteam2_id = $g[1]->value;
				$game->published = 1;
				$game->match_date = $current_date . ' ' . $time;
				$game->modified = $date->toSql();
				$game->modified_by = $user->get('id');
				/**
 * Insert the object into the table.
 */
				try
				{
					$resultinsert = $db->insertObject('#__sportsmanagement_match', $game);
					$result = $db->insertid();
				}
				catch (Exception $e)
				{
					$this->setError('COM_SPORTSMANAGEMENT_ADMIN_MATCH_FAILED');

					return false;
				}
			}
		}

				return true;
	}


}
