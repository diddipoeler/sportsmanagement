<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
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
class sportsmanagementModelProjectReferees extends ListModel
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
		// Reference global application object
		$app = Factory::getApplication();
		$db  = sportsmanagementHelper::getDBConnection();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$query  = Factory::getDbo()->getQuery(true);

		if (!count($cid))
		{
			return 0;
		}

		// Select some fields
		$query->select('pt.id');

		// From the table
		$query->from('#__sportsmanagement_person AS pt');
		$query->join('INNER', '#__sportsmanagement_project_referee AS r ON r.person_id = pt.id');
		$query->where('r.project_id = ' . $project_id);
		$query->where('pt.published = 1');

		$db->setQuery($query);

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			// Joomla! 3.0 code here
			$current = $db->loadColumn();
		}
		elseif (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			// Joomla! 2.5 code here
			$current = $db->loadResultArray();
		}

		//		$current = Factory::getDbo()->loadResultArray();
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

				// Get data from person
				// Select some fields
				$query->clear();
				$query->select('pl.picture');

				// From the table
				$query->from('#__sportsmanagement_person AS pl');
				$query->where('pl.id = ' . $pid);
				$query->where('pl.published = 1');

				Factory::getDbo()->setQuery($query);
				$player = Factory::getDbo()->loadObject();

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
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Initialise variables.
		// $app = Factory::getApplication('administrator');

		if (ComponentHelper::getParams($option)->get('show_debug_info_backend'))
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' context -> ' . $this->context . ''), '');
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' identifier -> ' . $this->_identifier . ''), '');
		}

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		$temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);

		$temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.project_position_id', 'filter_project_position_id', '');
		$this->setState('filter.project_position_id', $temp_user_request);

		$value = Factory::getApplication()->input->getUInt('limitstart', 0);
		$this->setState('list.start', $value);

		// List state information.
		parent::populateState('p.lastname', 'asc');
	}

	/**
	 * sportsmanagementModelProjectReferees::getListQuery()
	 *
	 * @return
	 */
	protected function getListQuery()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		$this->_project_id      = $app->getUserState("$option.pid", '0');
		$this->_season_id       = $app->getUserState("$option.season_id", '0');
		$this->_team_id         = Factory::getApplication()->input->getVar('team_id');
		$this->_project_team_id = Factory::getApplication()->input->getVar('project_team_id');

		if (!$this->_team_id)
		{
			$this->_team_id = $app->getUserState("$option.team_id", '0');
		}

		if (!$this->_project_team_id)
		{
			$this->_project_team_id = $app->getUserState("$option.project_team_id", '0');
		}

		// Create a new query object.
		$query = Factory::getDbo()->getQuery(true);

		// Select some fields
		$query->select(implode(",", $this->filter_fields) . ',tp.person_id as person_id');

		// From the club table
		$query->from('#__sportsmanagement_person AS p');
		$query->join('INNER', '#__sportsmanagement_season_person_id AS tp on tp.person_id = p.id');
		$query->join('INNER', '#__sportsmanagement_project_referee AS pref on pref.person_id = tp.id');
		$query->join('LEFT', '#__users AS u ON u.id = pref.checked_out');
		$query->where('tp.persontype = 3');
		$query->where('p.published = 1');
		$query->where('tp.season_id = ' . $this->_season_id);
		$query->where('pref.project_id = ' . $this->_project_id);

		if ($this->getState('filter.project_position_id'))
		{
			$query->where('pref.project_position_id = ' . $this->getState('filter.project_position_id'));
		}

		if ($this->getState('filter.search'))
		{
			$query->where(
				'(LOWER(p.lastname) LIKE ' . Factory::getDbo()->Quote('%' . $this->getState('filter.search') . '%') .
				'OR LOWER(p.firstname) LIKE ' . Factory::getDbo()->Quote('%' . $this->getState('filter.search') . '%') .
				'OR LOWER(p.nickname) LIKE ' . Factory::getDbo()->Quote('%' . $this->getState('filter.search') . '%') . ')'
			);
		}

		$query->order(
			Factory::getDbo()->escape($this->getState('list.ordering', 'p.lastname')) . ' ' .
			Factory::getDbo()->escape($this->getState('list.direction', 'ASC'))
		);

		return $query;

	}


}
