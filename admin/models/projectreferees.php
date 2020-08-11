<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage projectreferees
 * @file       projectreferees.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelProjectReferees
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelProjectReferees extends JSMModelList
{
	var $_identifier = "preferees";
	var $_project_id = 0;

	/**
	 * sportsmanagementModelProjectReferees::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		$config['filter_fields'] = array(
			'p.firstname',
			'p.lastname',
			'p.nickname',
			'p.phone',
			'p.email',
			'p.mobile',
			'pref.*',
			'pref.project_position_id',
			'u.name AS editor',
			'pref.picture'
		);
		parent::__construct($config);
		$getDBConnection = sportsmanagementHelper::getDBConnection();
		parent::setDbo($getDBConnection);
	}

	/**
	 * add the specified persons to team
	 *
	 * @param   array int person ids
	 *
	 * @return integer number of row inserted
	 */
	function storeAssigned($cid, $project_id)
	{
		if (!count($cid))
		{
			return 0;
		}

		$this->jsmquery->clear();
		$this->jsmquery->select('pt.id');
		$this->jsmquery->from('#__sportsmanagement_person AS pt');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project_referee AS r ON r.person_id = pt.id');
		$this->jsmquery->where('r.project_id = ' . $project_id);
		$this->jsmquery->where('pt.published = 1');

		$this->jsmdb->setQuery($this->jsmquery);

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			// Joomla! 3.0 code here
			$current = $this->jsmdb->loadColumn();
		}

		$added = 0;

		foreach ($cid AS $pid)
		{
			if ((!isset($current)) || (!in_array($pid, $current)))
			{
				$new             = Table::getInstance('ProjectReferee', 'Table');
				$new->person_id  = $pid;
				$new->project_id = $this->_project_id;

				if (!$new->check())
				{
					$this->setError($new->getError());
					continue;
				}

				$this->jsmquery->clear();
				$this->jsmquery->select('pl.picture');
				$this->jsmquery->from('#__sportsmanagement_person AS pl');
				$this->jsmquery->where('pl.id = ' . $pid);
				$this->jsmquery->where('pl.published = 1');

				$this->jsmdb->setQuery($this->jsmquery);
				$player = $this->jsmdb->loadObject();

				if ($player)
				{
					$new->picture = $player->picture;
				}

				if (!$new->store())
				{
					$this->setError($new->getError());
					continue;
				}

				$added++;
			}
		}

		return $added;
	}

	/**
	 * remove the specified projectreferees from project
	 *
	 * @param   array projectreferee ids
	 *
	 * @return integer number of row removed
	 */
	function unassign($cid)
	{
		if (!count($cid))
		{
			return 0;
		}

		$removed = 0;

		for ($x = 0; $x < count($cid); $x++)
		{
			$query = 'DELETE FROM #__sportsmanagement_project_referee WHERE id=' . $cid[$x];
			Factory::getDbo()->setQuery($query);

			if (!Factory::getDbo()->execute())
			{
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, Factory::getDbo()->getErrorMsg(), __LINE__);
				continue;
			}

			$removed++;
		}

		return $removed;
	}

	/**
	 * return count of projectreferees
	 *
	 * @param   int project_id
	 *
	 * @return integer
	 */
	function getProjectRefereesCount($project_id)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$query  = Factory::getDbo()->getQuery(true);

		// Select some fields
		$query->select('count(*) AS count');

		// From the table
		$query->from('#__sportsmanagement_project_referee AS pr');
		$query->join('INNER', '#__sportsmanagement_project AS p on p.id = pr.project_id');
		$query->where('p.id = ' . $project_id);

		Factory::getDbo()->setQuery($query);

		return Factory::getDbo()->loadResult();
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
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		if (ComponentHelper::getParams($option)->get('show_debug_info_backend'))
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' context -> ' . $this->context . ''), '');
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' identifier -> ' . $this->_identifier . ''), '');
		}
        
        $list = $this->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array');

		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));
		$this->setState('filter.search_nation', $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', ''));
		$this->setState('filter.project_position_id', $this->getUserStateFromRequest($this->context . '.filter.project_position_id', 'filter_project_position_id', ''));
		$this->setState('list.limit', $this->getUserStateFromRequest($this->context . '.list.limit', 'list_limit', $this->jsmapp->get('list_limit'), 'int'));
		$this->setState('list.start', $this->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int'));

		$orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'p.lastname';
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
	 * sportsmanagementModelProjectReferees::getListQuery()
	 *
	 * @return
	 */
	protected function getListQuery()
	{
		$this->_project_id      = $this->jsmapp->getUserState("$this->jsmoption.pid", '0');
		$this->_season_id       = $this->jsmapp->getUserState("$this->jsmoption.season_id", '0');
		$this->_team_id         = Factory::getApplication()->input->getVar('team_id');
		$this->_project_team_id = Factory::getApplication()->input->getVar('project_team_id');

		if (!$this->_team_id)
		{
			$this->_team_id = $this->jsmapp->getUserState("$this->jsmoption.team_id", '0');
		}

		if (!$this->_project_team_id)
		{
			$this->_project_team_id = $this->jsmapp->getUserState("$this->jsmoption.project_team_id", '0');
		}

		$this->jsmquery->clear();
		$this->jsmquery->select(implode(",", $this->filter_fields) . ',tp.person_id as person_id,tp.persontype,tp.season_id');
		$this->jsmquery->select('tp.id as season_person_id');
		$this->jsmquery->from('#__sportsmanagement_person AS p');
		$this->jsmquery->join('INNER', '#__sportsmanagement_season_person_id AS tp on tp.person_id = p.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project_referee AS pref on pref.person_id = tp.id');
		$this->jsmquery->join('LEFT', '#__users AS u ON u.id = pref.checked_out');
		$this->jsmquery->where('tp.persontype = 3');
		$this->jsmquery->where('p.published = 1');
		$this->jsmquery->where('tp.season_id = ' . $this->_season_id);
		$this->jsmquery->where('pref.project_id = ' . $this->_project_id);

		if ($this->getState('filter.project_position_id'))
		{
			$this->jsmquery->where('pref.project_position_id = ' . $this->getState('filter.project_position_id'));
		}

		if ($this->getState('filter.search'))
		{
			$this->jsmquery->where(
				'(LOWER(p.lastname) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') .
				'OR LOWER(p.firstname) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') .
				'OR LOWER(p.nickname) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') . ')'
			);
		}

		$this->jsmquery->order(
			$this->jsmdb->escape($this->getState('list.ordering', 'p.lastname')) . ' ' .
			$this->jsmdb->escape($this->getState('list.direction', 'ASC'))
		);

		return $this->jsmquery;

	}


}
