<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teamplayers
 * @file       teamplayers.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelteamplayers
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class sportsmanagementModelteamplayers extends JSMModelList
{
	static $db_num_rows = 0;
	var $_identifier = "teamplayers";
	var $_project_id = 0;
	var $_season_id = 0;
	var $_team_id = 0;
	var $_project_team_id = 0;
	var $_persontype = 0;

	/**
	 * sportsmanagementModelteamplayers::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		$config['filter_fields'] = array(
			'ppl.lastname',
			'tp.person_id',
			'ppl.position_id',
			'ppl.published',
			'ppl.ordering',
			'ppl.picture',
			'ppl.id',
			'tp.market_value',
			'tp.jerseynumber'
		);
		parent::__construct($config);
		$getDBConnection = sportsmanagementHelper::getDBConnection();
		parent::setDbo($getDBConnection);
	}

	/**
	 * sportsmanagementModelteamplayers::getListQuery()
	 *
	 * @return
	 */
	function getListQuery()
	{
		$this->jsmquery->clear();

		$this->_project_id = $this->jsmapp->getUserState("$this->jsmoption.pid", '0');

		$this->jsmquery->select('ppl.id,ppl.firstname,ppl.lastname,ppl.nickname,ppl.picture,ppl.id as person_id,ppl.injury,ppl.suspension,ppl.away,ppl.ordering,ppl.checked_out,ppl.checked_out_time  ');
		$this->jsmquery->select('ppl.position_id as person_position_id');
		$this->jsmquery->select('tp.id as tpid, tp.market_value, tp.jerseynumber,tp.picture as season_picture,tp.published');
		$this->jsmquery->select('u.name AS editor');
		$this->jsmquery->select('st.season_id AS season_id,st.id as projectteam_id');
		$this->jsmquery->select('ppl.country');

		$this->jsmquery->from('#__sportsmanagement_person AS ppl');
		$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_person_id AS tp on tp.person_id = ppl.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id and st.season_id = tp.season_id');
		$this->jsmquery->join('LEFT', '#__users AS u ON u.id = tp.checked_out');
		$this->jsmquery->where('ppl.published = 1');
		$this->jsmquery->where('st.team_id = ' . $this->getState('filter.team_id'));
		$this->jsmquery->where('st.season_id = ' . $this->getState('filter.season_id'));
		$this->jsmquery->where('tp.season_id = ' . $this->getState('filter.season_id'));
		$this->jsmquery->where('tp.persontype = ' . $this->getState('filter.persontype'));

		$this->jsmsubquery1->clear();
		$this->jsmsubquery1->select('ppos.id');
		$this->jsmsubquery1->from('#__sportsmanagement_project_position AS ppos');
		$this->jsmsubquery1->join('LEFT', '#__sportsmanagement_person_project_position AS ppp on ppp.project_position_id = ppos.id');
		$this->jsmsubquery1->where('ppp.person_id = ppl.id');
		$this->jsmsubquery1->where('ppp.project_id = ' . $this->_project_id);
		$this->jsmsubquery1->where('ppp.persontype = ' . $this->getState('filter.persontype'));
		$this->jsmquery->select('(' . $this->jsmsubquery1 . ') AS project_position_id');

		$this->jsmsubquery2->clear();
		$this->jsmsubquery2->select('ppp.published');
		$this->jsmsubquery2->from('#__sportsmanagement_person_project_position AS ppp');
		$this->jsmsubquery2->where('ppp.person_id = ppl.id');
		$this->jsmsubquery2->where('ppp.project_id = ' . $this->_project_id);
		$this->jsmsubquery2->where('ppp.persontype = ' . $this->getState('filter.persontype'));
		$this->jsmquery->select('(' . $this->jsmsubquery2 . ') AS project_published');

		if (is_numeric($this->getState('filter.state')))
		{
			$this->jsmquery->where('tp.published = ' . $this->getState('filter.state'));
		}

		if ($this->getState('filter.search'))
		{
			$this->jsmquery->where(
				'(LOWER(ppl.lastname) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') .
				'OR LOWER(ppl.firstname) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') .
				'OR LOWER(ppl.nickname) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') . ')'
			);
		}

		$this->jsmquery->order(
			$this->jsmdb->escape($this->getState('list.ordering', 'ppl.lastname')) . ' ' .
			$this->jsmdb->escape($this->getState('list.direction', 'ASC'))
		);

		return $this->jsmquery;
	}

	/**
	 * sportsmanagementModelteamplayers::PersonProjectPosition()
	 *
	 * @return void
	 */
	function PersonProjectPosition($project_id, $_persontype)
	{
		$this->jsmquery->clear();

		$this->jsmquery->select('ppl.*');
		$this->jsmquery->from('#__sportsmanagement_person_project_position AS ppl');
		$this->jsmquery->where('ppl.project_id = ' . $project_id);
		$this->jsmquery->where('ppl.persontype = ' . $_persontype);

		$this->jsmdb->setQuery($this->jsmquery);

		$result = $this->jsmdb->loadObjectList();

		if (!$result)
		{
			return false;
		}

		return $result;

	}

	/**
	 * sportsmanagementModelteamplayers::checkProjectPositions()
	 *
	 * @param   mixed  $project_id
	 * @param   mixed  $persontype
	 * @param   mixed  $team_id
	 * @param   mixed  $season_id
	 *
	 * @return
	 */
	function checkProjectPositions($project_id, $persontype, $team_id, $season_id, $insert = 1)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Create a new query object.
		// $db   = sportsmanagementHelper::getDBConnection();
		$db          = sportsmanagementHelper::getDBConnection();
		$query       = $db->getQuery(true);
		$date        = Factory::getDate();
		$user        = Factory::getUser();
		$modified    = $date->toSql();
		$modified_by = $user->get('id');

		/**
		 * tabelle: sportsmanagement_person_project_position
		 * feld import_id einfügen
		 */
		$jsm_table = '#__sportsmanagement_person_project_position';

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `" . $jsm_table . "` ADD `import_id` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
			sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

			// $result = $db->execute();
		}
		catch (Exception $e)
		{
			//    // catch any database errors.
			//    $db->transactionRollback();
			//    JErrorPage::render($e);
		}

		// Select some fields
		$query = $db->getQuery(true);
		$query->clear();
		$query->select('stp.person_id,ppos.id as project_position_id');
		$query->from('#__sportsmanagement_season_team_person_id as stp');
		$query->join('INNER', '#__sportsmanagement_person AS p ON p.id = stp.person_id');

		$query->join('INNER', '#__sportsmanagement_project_position AS ppos ON ppos.position_id = p.position_id');

		$query->where('stp.team_id = ' . $team_id);
		$query->where('stp.season_id = ' . $season_id);
		$query->where('stp.persontype = ' . $persontype);
		$query->where('ppos.project_id = ' . $project_id);

		try
		{
			$db->setQuery($query);
			$result = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			// Catch any database errors.
			$result = false;

			//    $db->transactionRollback();
			//    JErrorPage::render($e);
		}

		if ($result)
		{
			foreach ($result as $row)
			{
				$query->clear();
				$query->select('person_id');
				$query->from('#__sportsmanagement_person_project_position');
				$query->where('person_id = ' . $row->person_id);
				$query->where('project_id = ' . $project_id);
				$query->where('project_position_id = ' . $row->project_position_id);
				$query->where('persontype = ' . $persontype);
				$db->setQuery($query);
				$resultcheck = $db->loadResult();

				if (!$resultcheck)
					// Projekt position eintragen
				{
					// Create a new query object.
					$insertquery = $db->getQuery(true);

					// Insert columns.
					$columns = array('person_id', 'project_id', 'project_position_id', 'persontype', 'import_id');

					// Insert values.
					$values = array($row->person_id, $project_id, $row->project_position_id, $persontype, 1);

					// Prepare the insert query.
					$insertquery
						->insert($db->quoteName('#__sportsmanagement_person_project_position'))
						->columns($db->quoteName($columns))
						->values(implode(',', $values));

					// Set the query using our newly populated query object and execute it.
					$db->setQuery($insertquery);

					if ($insert)
					{
						if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
						{
						}
						else
						{
						}
					}
				}
			}

			return true;
		}
		else
		{
			return false;
		}

	}

	/**
	 * sportsmanagementModelteamplayers::getProjectTeamplayers()
	 *
	 * @param   mixed  $project_team_id
	 *
	 * @return
	 */
	function getProjectTeamplayers($team_id = 0, $season_id = 0)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Create a new query object.
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		$user  = Factory::getUser();

		// Select some fields
		$query->select('ppl.*');

		// From table
		$query->from('#__sportsmanagement_person AS ppl');
		$query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp on tp.person_id = ppl.id');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id');
		$query->where('st.team_id IN (' . $team_id . ')');
		$query->where('st.season_id = ' . $season_id);
		$query->where('tp.team_id = ' . $season_id);

		try
		{
			$db->setQuery($query);
			$result = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			// Catch any database errors.
			$result = false;

			//    $db->transactionRollback();
			//    JErrorPage::render($e);
		}

		return $result;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since 1.6
	 */
	protected function populateState($ordering = 'ppl.lastname', $direction = 'asc')
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

		if (Factory::getApplication()->input->getVar('team_id'))
		{
			$this->setState('filter.team_id', Factory::getApplication()->input->getVar('team_id'));
		}
		else
		{
			$this->setState('filter.team_id', $this->jsmapp->getUserState("$this->jsmoption.team_id", '0'));
		}

		if (Factory::getApplication()->input->getVar('persontype'))
		{
			$this->setState('filter.persontype', Factory::getApplication()->input->getVar('persontype'));
		}
		else
		{
			$this->setState('filter.persontype', $this->jsmapp->getUserState("$this->jsmoption.persontype", '0'));
		}

		if (Factory::getApplication()->input->getVar('project_team_id'))
		{
			$this->setState('filter.project_team_id', Factory::getApplication()->input->getVar('project_team_id'));
		}
		else
		{
			$this->setState('filter.project_team_id', $this->jsmapp->getUserState("$this->jsmoption.project_team_id", '0'));
		}

		$this->setState('filter.pid', $this->jsmapp->getUserState("$this->jsmoption.pid", '0'));
		$this->setState('filter.season_id', $this->jsmapp->getUserState("$this->jsmoption.season_id", '0'));

		$value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
		$this->setState('list.limit', $value);

		// List state information.
		parent::populateState($ordering, $direction);
		$value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);

		// Filter.order
		$orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'ppl.lastname';
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
