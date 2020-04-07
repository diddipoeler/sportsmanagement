<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       seasons.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage seasons
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

/**
 * sportsmanagementModelSeasons
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelSeasons extends JSMModelList
{
	var $_identifier = "seasons";

	var $_order = "s.name";

	/**
	 * sportsmanagementModelSeasons::__construct()
	 *
	 * @param   mixed $config
	 * @return void
	 */
	public function __construct($config = array())
	{
		// Reference global application object
		$this->app = Factory::getApplication();

		// JInput object
		$this->jinput = $this->app->input;

							  $layout = $this->jinput->getVar('layout');

		switch ($layout)
		{
			case 'assignteams':
				$this->_order = 't.name';
			break;

			case 'assignpersons':
				$this->_order = 'p.lastname';
			break;

			default:
				$this->_order = 's.name';
break;
		}

							$config['filter_fields'] = array(
							's.name',
							's.alias',
							's.id',
							's.ordering',
							's.published',
							's.modified',
							's.modified_by',
							's.checked_out',
							's.checked_out_time'
						);
				parent::__construct($config);
				parent::setDbo($this->jsmdb);

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

			  $layout = $this->jsmjinput->getVar('layout');

		// Initialise variables.
		// $app = Factory::getApplication('administrator');
		$order = '';

		if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info'))
		{
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' context -> ' . $this->context . ''), '');
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' identifier -> ' . $this->_identifier . ''), '');
		}

			// Load the filter state.
			$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
			$this->setState('filter.search', $search);
			$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
			$this->setState('filter.state', $published);
			$temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', '');
			$this->setState('filter.search_nation', $temp_user_request);
			$value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
			$this->setState('list.limit', $value);

			  // List state information.
			$value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
			$this->setState('list.start', $value);

			  // Filter.order
			$orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($orderCol, $this->filter_fields))
		{
					$orderCol = 's.name';
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
	 * sportsmanagementModelSeasons::getListQuery()
	 *
	 * @return
	 */
	protected function getListQuery()
	{

		$layout = $this->jsmjinput->getVar('layout');
		$season_id = $this->jsmjinput->getVar('id');

			  $this->setState('list.ordering', $this->_order);

		switch ($layout)
		{
			case 'assignteams':
				$this->jsmquery->clear();

				// Select some fields
				$this->jsmquery->select('t.*');

				// From the seasons table
				$this->jsmquery->from('#__sportsmanagement_team as t');
				$this->jsmquery->join('LEFT', '#__sportsmanagement_club AS c ON c.id = t.club_id');
				$this->jsmsubquery1->select('stp.team_id');
				$this->jsmsubquery1->from('#__sportsmanagement_season_team_id AS stp  ');
				$this->jsmsubquery1->where('stp.season_id = ' . $season_id);
				$this->jsmquery->where('t.id NOT IN (' . $this->jsmsubquery1 . ')');

				if ($this->getState('filter.search_nation'))
				{
					$this->jsmquery->where('c.country LIKE ' . $this->jsmdb->Quote('' . $this->getState('filter.search_nation') . ''));
				}

				if ($this->getState('filter.search'))
				{
					$this->jsmquery->where(' LOWER(t.name) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
				}

				// $order = 't.name';
			break;

			case 'assignpersons':
				$this->jsmquery->clear();

				// Select some fields
				$this->jsmquery->select('p.*');

				// From the seasons table
				$this->jsmquery->from('#__sportsmanagement_person as p');
				$this->jsmsubquery1->select('stp.person_id');
				$this->jsmsubquery1->from('#__sportsmanagement_season_person_id AS stp  ');
				$this->jsmsubquery1->where('stp.season_id = ' . $season_id);
				$this->jsmquery->where('p.id NOT IN (' . $this->jsmsubquery1 . ')');

				if ($this->getState('filter.search_nation'))
				{
								$this->jsmquery->where('p.country LIKE ' . $this->jsmdb->Quote('' . $this->getState('filter.search_nation') . ''));
				}

				if ($this->getState('filter.search'))
				{
								$this->jsmquery->where(
									'(LOWER(p.lastname) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') .
									'OR LOWER(p.firstname) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') .
									'OR LOWER(p.nickname) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') .
									'OR LOWER(p.info) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') .
									')'
								);
				}

				// $order = 'p.lastname';
			break;

			default:
				$this->jsmquery->clear();

				// Select some fields
				$this->jsmquery->select(implode(",", $this->filter_fields));
				$this->jsmquery->select('uc.name AS editor');

				// From the seasons table
				$this->jsmquery->from('#__sportsmanagement_season as s');
				$this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = s.checked_out');

				if ($this->getState('filter.search'))
				{
					$this->jsmquery->where(' LOWER(s.name) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
				}

				if (is_numeric($this->getState('filter.state')))
				{
					$this->jsmquery->where('s.published = ' . $this->getState('filter.state'));
				}
			break;
		}

			 $this->jsmquery->order(
				 $this->jsmdb->escape($this->getState('list.ordering', $this->_order)) . ' ' .
				 $this->jsmdb->escape($this->getState('list.direction', 'ASC'))
			 );

		return $this->jsmquery;
	}

	/**
	 * sportsmanagementModelSeasons::getSeasonTeams()
	 *
	 * @param   integer $season_id
	 * @return void
	 */
	public function getSeasonTeams($season_id=0)
	{
		  $this->jsmquery->clear();
		 $this->jsmquery->select('t.id as value, t.name as text');
		 $this->jsmquery->from('#__sportsmanagement_team as t');
		$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
		$this->jsmquery->where('st.season_id = ' . $season_id);

		try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->loadObjectList();

			return $result;
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');

			return false;
		}
	}

		  /**
		   * sportsmanagementModelSeasons::getSeasonName()
		   *
		   * @param   integer $season_id
		   * @return void
		   */
	function getSeasonName($season_id=0)
	{
		  $this->jsmquery->clear();
		  $this->jsmquery->select('name');
		  $this->jsmquery->from('#__sportsmanagement_season');
		  $this->jsmquery->where('id = ' . $season_id);

		try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->loadResult();

			return $result;
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');

			return false;
		}
	}

		  /**
		   * sportsmanagementModelSeasons::getSeasons()
		   *
		   * @param   bool $selectoptions
		   * @return
		   */
	function getSeasons($selectoptions = false)
	{
		$this->jsmquery->clear();

		if ($selectoptions)
		{
			$this->jsmquery->select(array('id as value', 'name as text'))
				->from('#__sportsmanagement_season')
				->order('name DESC');
		}
		else
		{
			$this->jsmquery->select(array('id', 'name'))
				->from('#__sportsmanagement_season')
				->order('name DESC');
		}

		try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->loadObjectList();

			foreach ($result as $season)
			{
				if ($selectoptions)
				{
					$season->text = Text::_($season->text);
				}
				else
				{
					$season->name = Text::_($season->name);
				}
			}

			return $result;
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');

			return false;
		}
	}




}
