<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       sportstypes.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

if (!class_exists('sportsmanagementHelper'))
{
	// Add the classes for handling
	$classpath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components/com_sportsmanagement' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';
	JLoader::register('sportsmanagementHelper', $classpath);
}

/**
 * sportsmanagementModelSportsTypes
 *
 * @package
 * @author    abcde
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelSportsTypes extends JSMModelList
{
	var $_identifier = "sportstypes";

	static $setError = '';

	/**
	 * sportsmanagementModelSportsTypes::__construct()
	 *
	 * @param   mixed $config
	 * @return void
	 */
	public function __construct($config = array())
	{
				$config['filter_fields'] = array(
						's.name',
						's.icon',
						's.sportsart',
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
	 * sportsmanagementModelSportsTypes::getListQuery()
	 *
	 * @return
	 */
	function getListQuery()
	{
		$this->jsmquery->clear();

		// Select some fields
		$this->jsmquery->select(implode(",", $this->filter_fields));

		// From table
		$this->jsmquery->from('#__sportsmanagement_sports_type AS s');
		$this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = s.checked_out');

		if ($this->getState('filter.search'))
		{
			$this->jsmquery->where('LOWER(s.name) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
		}

		if (is_numeric($this->getState('filter.state')))
		{
			$this->jsmquery->where('s.published = ' . $this->getState('filter.state'));
		}

			  $this->jsmquery->order(
				  $this->jsmdb->escape($this->getState('list.ordering', 's.name')) . ' ' .
				  $this->jsmdb->escape($this->getState('list.direction', 'ASC'))
			  );

					  return $this->jsmquery;

	}




	/**
	 * Method to return a sportsTypes array (id,name)
	 *
	 * @access public
	 * @return array
	 * @since  1.5.0a
	 */
	public function getSportsTypes()
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('id, name, name AS text,icon');
		$this->jsmquery->from('#__sportsmanagement_sports_type');
		$this->jsmquery->order('name ASC');
		$this->jsmdb->setQuery($this->jsmquery);

		if (!$result = $this->jsmdb->loadObjectList())
		{
			$this->jsmapp->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_SPORTSTYPES_NO_RESULT'), 'Error');

			return array();
		}

		foreach ($result as $sportstype)
		{
			   $sportstype->name = Text::_($sportstype->name);
		}

		return $result;
	}


	/**
	 * sportsmanagementModelSportsTypes::getProjectsCount()
	 *
	 * @param   integer $sporttypeid
	 * @return
	 */
	public function getProjectsCount($sporttypeid=0)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('count(*) AS count');
		$this->jsmquery->from('#__sportsmanagement_sports_type AS st');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project AS p ON p.sports_type_id = st.id');
		$this->jsmquery->where('st.id = ' . $sporttypeid);

		$this->jsmdb->setQuery($this->jsmquery);

		if (!$this->jsmdb->execute())
		{
			$this->setError($this->jsmdb->getErrorMsg());

			return false;
		}

		return $this->jsmdb->loadObject()->count;
	}



	/**
	 * sportsmanagementModelSportsTypes::getPlaygroundsOnlyCount()
	 *
	 * @param   integer $sporttypeid
	 * @return
	 */
	public function getPlaygroundsOnlyCount($sporttypeid=0)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('count(*) AS count');
		$this->jsmquery->from('#__sportsmanagement_playground AS p ');

		$this->jsmdb->setQuery($this->jsmquery);

		if (!$this->jsmdb->execute())
		{
			$this->setError($this->jsmdb->getErrorMsg());

			return false;
		}

		return $this->jsmdb->loadObject()->count;
	}


	/**
	 * sportsmanagementModelSportsTypes::getLeaguesOnlyCount()
	 *
	 * @param   integer $sporttypeid
	 * @return
	 */
	public function getLeaguesOnlyCount($sporttypeid=0)
	{
		  $this->jsmquery->clear();
		$this->jsmquery->select('count(*) AS count');
		$this->jsmquery->from('#__sportsmanagement_league AS l');

		$this->jsmdb->setQuery($this->jsmquery);

		if (!$this->jsmdb->execute())
		{
			$this->setError($this->jsmdb->getErrorMsg());

			return false;
		}

		return $this->jsmdb->loadObject()->count;
	}


	/**
	 * sportsmanagementModelSportsTypes::getPersonsOnlyCount()
	 *
	 * @param   integer $sporttypeid
	 * @return
	 */
	public function getPersonsOnlyCount($sporttypeid=0)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('count(*) AS count');
		$this->jsmquery->from('#__sportsmanagement_person AS c');

		$this->jsmdb->setQuery($this->jsmquery);

		if (!$this->jsmdb->execute())
		{
			$this->setError($this->jsmdb->getErrorMsg());

			return false;
		}

			  $result = $this->jsmdb->loadObject()->count;
		$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}


	/**
	 * sportsmanagementModelSportsTypes::getClubsOnlyCount()
	 *
	 * @param   integer $sporttypeid
	 * @return
	 */
	public function getClubsOnlyCount($sporttypeid=0)
	{
		  $this->jsmquery->clear();
		$this->jsmquery->select('count(*) AS count');
		$this->jsmquery->from('#__sportsmanagement_club AS c');

		$this->jsmdb->setQuery($this->jsmquery);

		if (!$this->jsmdb->execute())
		{
			$this->setError($this->jsmdb->getErrorMsg());

			return false;
		}

		$result = $this->jsmdb->loadObject()->count;
		$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}


	/**
	 * sportsmanagementModelSportsTypes::getLeaguesCount()
	 *
	 * @param   integer $sporttypeid
	 * @return
	 */
	public function getLeaguesCount($sporttypeid=0)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('count(*) AS count');
		$this->jsmquery->from('#__sportsmanagement_sports_type AS st');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project AS p ON p.sports_type_id = st.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_league AS l ON l.id = p.league_id');
		$this->jsmquery->where('st.id = ' . $sporttypeid);

		$this->jsmdb->setQuery($this->jsmquery);

		if (!$this->jsmdb->execute())
		{
			$this->setError($this->jsmdb->getErrorMsg());

			return false;
		}

		$result = $this->jsmdb->loadObject()->count;
		$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}


	/**
	 * sportsmanagementModelSportsTypes::getSeasonsOnlyCount()
	 *
	 * @param   integer $sporttypeid
	 * @return
	 */
	public function getSeasonsOnlyCount($sporttypeid=0)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('count(*) AS count');
		$this->jsmquery->from('#__sportsmanagement_season AS s ');

		$this->jsmdb->setQuery($this->jsmquery);

		if (!$this->jsmdb->execute())
		{
			$this->setError($this->jsmdb->getErrorMsg());

			return false;
		}

		$result = $this->jsmdb->loadObject()->count;
		$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}


	/**
	 * sportsmanagementModelSportsTypes::getSeasonsCount()
	 *
	 * @param   integer $sporttypeid
	 * @return
	 */
	public function getSeasonsCount($sporttypeid=0)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('count(*) AS count');
		$this->jsmquery->from('#__sportsmanagement_sports_type AS st');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project AS p ON p.sports_type_id = st.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_season AS s ON s.id = p.season_id');
		$this->jsmquery->where('st.id = ' . $sporttypeid);

		$this->jsmdb->setQuery($this->jsmquery);

		if (!$this->jsmdb->execute())
		{
			$this->setError($this->jsmdb->getErrorMsg());

			return false;
		}

		$result = $this->jsmdb->loadObject()->count;
		$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}


	/**
	 * sportsmanagementModelSportsTypes::getProjectTeamsCount()
	 *
	 * @param   integer $sporttypeid
	 * @return
	 */
	public function getProjectTeamsCount($sporttypeid=0)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('count(*) AS count');
		$this->jsmquery->from('#__sportsmanagement_sports_type AS st');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project AS p ON p.sports_type_id = st.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project_team AS ptt ON ptt.project_id = p.id');
		$this->jsmquery->where('st.id = ' . $sporttypeid);

		$this->jsmdb->setQuery($this->jsmquery);

		if (!$this->jsmdb->execute())
		{
			$this->setError($this->jsmdb->getErrorMsg());

			return false;
		}

		$result = $this->jsmdb->loadObject()->count;
		$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}


	/**
	 * sportsmanagementModelSportsTypes::getProjectTeamsPlayersCount()
	 *
	 * @param   integer $sporttypeid
	 * @return
	 */
	public function getProjectTeamsPlayersCount($sporttypeid=0)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('count(*) AS count');
		$this->jsmquery->from('#__sportsmanagement_sports_type AS st');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project AS p ON p.sports_type_id = st.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project_team AS ptt ON ptt.project_id = p.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id as st2 ON st2.id = ptt.team_id ');
		$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_person_id AS tp1 ON tp1.team_id = st2.team_id');

			  $this->jsmquery->where('st.id = ' . $sporttypeid);

			  $this->jsmdb->setQuery($this->jsmquery);

		if (!$this->jsmdb->execute())
		{
			$this->setError($this->jsmdb->getErrorMsg());

			return false;
		}

		$result = $this->jsmdb->loadObject()->count;
		$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}


	/**
	 * sportsmanagementModelSportsTypes::getProjectDivisionsCount()
	 *
	 * @param   integer $sporttypeid
	 * @return
	 */
	public function getProjectDivisionsCount($sporttypeid=0)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('count(*) AS count');
		$this->jsmquery->from('#__sportsmanagement_sports_type AS st');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project AS p ON p.sports_type_id = st.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_division AS d ON d.project_id = p.id');
		$this->jsmquery->where('st.id = ' . $sporttypeid);

		$this->jsmdb->setQuery($this->jsmquery);

		if (!$this->jsmdb->execute())
		{
			$this->setError($this->jsmdb->getErrorMsg());

			return false;
		}

		$result = $this->jsmdb->loadObject()->count;
		$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}


	/**
	 * sportsmanagementModelSportsTypes::getProjectRoundsCount()
	 *
	 * @param   integer $sporttypeid
	 * @return
	 */
	public function getProjectRoundsCount($sporttypeid=0)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('count(*) AS count');
		$this->jsmquery->from('#__sportsmanagement_sports_type AS st');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project AS p ON p.sports_type_id = st.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_round AS r ON r.project_id = p.id');
		$this->jsmquery->where('st.id = ' . $sporttypeid);

			  $this->jsmdb->setQuery($this->jsmquery);

		if (!$this->jsmdb->execute())
		{
			$this->setError($this->jsmdb->getErrorMsg());

			return false;
		}

		$result = $this->jsmdb->loadObject()->count;
		$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}


	/**
	 * sportsmanagementModelSportsTypes::getProjectMatchesCount()
	 *
	 * @param   integer $sporttypeid
	 * @return
	 */
	public function getProjectMatchesCount($sporttypeid=0)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('count(*) AS count');
		$this->jsmquery->from('#__sportsmanagement_sports_type AS st');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project AS p ON p.sports_type_id = st.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_round AS r ON r.project_id = p.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_match AS m ON m.round_id = r.id');
		$this->jsmquery->where('st.id = ' . $sporttypeid);

			  $this->jsmdb->setQuery($this->jsmquery);

		if (!$this->jsmdb->execute())
		{
			$this->setError($this->jsmdb->getErrorMsg());

			return false;
		}

		$result = $this->jsmdb->loadObject()->count;
		$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}



	/**
	 * sportsmanagementModelSportsTypes::getProjectMatchesEventsNameCount()
	 *
	 * @param   integer $sporttypeid
	 * @return
	 */
	public function getProjectMatchesEventsNameCount($sporttypeid=0)
	{
		  $this->jsmquery->clear();
		$this->jsmquery->select('count( me.id ) as total');
		$this->jsmquery->select('me.event_type_id,p.sports_type_id,et.name,et.icon');
		$this->jsmquery->from('#__sportsmanagement_match_event as me');
		$this->jsmquery->join('INNER', '#__sportsmanagement_match AS m ON me.match_id= m.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_round AS r ON m.round_id = r.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project AS p ON r.project_id = p.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_eventtype AS et ON me.event_type_id = et.id');
		$this->jsmquery->where('st.id = ' . $sporttypeid);
		$this->jsmquery->group('me.event_type_id');

		$this->jsmdb->setQuery($this->jsmquery);

		if (!$result = $this->jsmdb->loadObjectList())
		{
			$this->setError($this->jsmdb->getErrorMsg());

			return false;
		}

		return $result;
	}


	/**
	 * sportsmanagementModelSportsTypes::getProjectMatchesEventsCount()
	 *
	 * @param   integer $sporttypeid
	 * @return
	 */
	public function getProjectMatchesEventsCount($sporttypeid=0)
	{
		 $this->jsmquery->clear();
		$this->jsmquery->select('count(*) AS count');
		$this->jsmquery->from('#__sportsmanagement_sports_type AS st');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project AS p ON p.sports_type_id = st.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_round AS r ON r.project_id = p.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_match AS m ON m.round_id = r.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_match_event AS me ON me.match_id = m.id');
		$this->jsmquery->where('st.id = ' . $sporttypeid);

			  $this->jsmdb->setQuery($this->jsmquery);

		if (!$this->jsmdb->execute())
		{
			$this->setError($this->jsmdb->getErrorMsg());

			return false;
		}

		$result = $this->jsmdb->loadObject()->count;
		$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}


	/**
	 * sportsmanagementModelSportsTypes::getProjectMatchesStatsCount()
	 *
	 * @param   integer $sporttypeid
	 * @return
	 */
	public function getProjectMatchesStatsCount($sporttypeid=0)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('count(*) AS count');
		$this->jsmquery->from('#__sportsmanagement_sports_type AS st');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project AS p ON p.sports_type_id = st.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_round AS r ON r.project_id = p.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_match AS m ON m.round_id = r.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_match_statistic AS ms ON ms.match_id = m.id');
		$this->jsmquery->where('st.id = ' . $sporttypeid);

			  $this->jsmdb->setQuery($this->jsmquery);

		if (!$this->jsmdb->execute())
		{
			$this->setError($this->jsmdb->getErrorMsg());

			return false;
		}

		$result = $this->jsmdb->loadObject()->count;
		$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}



}
