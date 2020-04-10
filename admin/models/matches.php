<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       matches.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelMatches
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelMatches extends JSMModelList
{
	var $_identifier = "matches";

	var $_rid = 0;

	var $_season_id = 0;

	/**
	 * sportsmanagementModelMatches::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		$config['filter_fields'] = array(
			'mc.match_number',
			'mc.match_date',
			'mc.id',
			'mc.ordering'
		);
		parent::__construct($config);
		$getDBConnection = sportsmanagementHelper::getDBConnection();
		parent::setDbo($getDBConnection);
	}

	/**
	 * sportsmanagementModelMatches::prepareItems()
	 *
	 * @param   mixed  $items
	 *
	 * @return
	 */
	function prepareItems($items)
	{
		foreach ($items as $item)
		{
			$item->homeplayers_count = 0;
			$item->homestaff_count   = 0;
			$item->awayplayers_count = 0;
			$item->awaystaff_count   = 0;
			$item->referees_count    = 0;

			$this->jsmquery->clear();
			$this->jsmquery->select('tp.id');
			$this->jsmquery->from('#__sportsmanagement_season_team_person_id AS tp ');
			$this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id and st.season_id = tp.season_id');
			$this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pthome ON pthome.team_id = st.id');
			$this->jsmquery->where('pthome.id =' . $item->projectteam1_id);
			$this->jsmquery->where('tp.season_id = ' . $this->_season_id);
			$this->jsmquery->where('tp.persontype = 1');
			$this->jsmdb->setQuery($this->jsmquery);

			$result = $this->jsmdb->loadColumn();

			if ($result)
			{
				$players = implode(",", $result);

				// Count match homeplayers
				$this->jsmquery->clear();
				$this->jsmquery->select('count(mp.id)');
				$this->jsmquery->from('#__sportsmanagement_match_player AS mp  ');
				$this->jsmquery->where('mp.match_id = ' . $item->id . ' AND (came_in=0 OR came_in=1) AND mp.teamplayer_id in (' . $players . ')');
				$this->jsmdb->setQuery($this->jsmquery);
				$item->homeplayers_count = $this->jsmdb->loadResult();
			}

			$this->jsmquery->clear();
			$this->jsmquery->select('tp.id');
			$this->jsmquery->from('#__sportsmanagement_season_team_person_id AS tp ');
			$this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id and st.season_id = tp.season_id');
			$this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pthome ON pthome.team_id = st.id');
			$this->jsmquery->where('pthome.id =' . $item->projectteam1_id);
			$this->jsmquery->where('tp.season_id = ' . $this->_season_id);
			$this->jsmquery->where('tp.persontype = 2');
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->loadColumn();

			if ($result)
			{
				$players = implode(",", $result);

				// Count match homeplayers
				$this->jsmquery->clear();
				$this->jsmquery->select('count(mp.id)');
				$this->jsmquery->from('#__sportsmanagement_match_player AS mp  ');
				$this->jsmquery->where('mp.match_id = ' . $item->id . ' AND (came_in=0 OR came_in=1) AND mp.teamplayer_id in (' . $players . ')');
				$this->jsmdb->setQuery($this->jsmquery);
				$item->homestaff_count = $this->jsmdb->loadResult();
			}

			$this->jsmquery->clear();
			$this->jsmquery->select('tp.id');
			$this->jsmquery->from('#__sportsmanagement_season_team_person_id AS tp ');
			$this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id and st.season_id = tp.season_id');
			$this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pthome ON pthome.team_id = st.id');
			$this->jsmquery->where('pthome.id =' . $item->projectteam2_id);
			$this->jsmquery->where('tp.season_id = ' . $this->_season_id);
			$this->jsmquery->where('tp.persontype = 1');
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->loadColumn();

			if ($result)
			{
				$players = implode(",", $result);

				// Count match homeplayers
				$this->jsmquery->clear();
				$this->jsmquery->select('count(mp.id)');
				$this->jsmquery->from('#__sportsmanagement_match_player AS mp  ');
				$this->jsmquery->where('mp.match_id = ' . $item->id . ' AND (came_in=0 OR came_in=1) AND mp.teamplayer_id in (' . $players . ')');
				$this->jsmdb->setQuery($this->jsmquery);
				$item->awayplayers_count = $this->jsmdb->loadResult();
			}

			$this->jsmquery->clear();
			$this->jsmquery->select('tp.id');
			$this->jsmquery->from('#__sportsmanagement_season_team_person_id AS tp ');
			$this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id and st.season_id = tp.season_id');
			$this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pthome ON pthome.team_id = st.id');
			$this->jsmquery->where('pthome.id =' . $item->projectteam2_id);
			$this->jsmquery->where('tp.season_id = ' . $this->_season_id);
			$this->jsmquery->where('tp.persontype = 2');
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->loadColumn();

			if ($result)
			{
				$players = implode(",", $result);

				// Count match homeplayers
				$this->jsmquery->clear();
				$this->jsmquery->select('count(mp.id)');
				$this->jsmquery->from('#__sportsmanagement_match_player AS mp  ');
				$this->jsmquery->where('mp.match_id = ' . $item->id . ' AND (came_in=0 OR came_in=1) AND mp.teamplayer_id in (' . $players . ')');
				$this->jsmdb->setQuery($this->jsmquery);
				$item->awaystaff_count = $this->jsmdb->loadResult();
			}

			$this->jsmquery->clear();

			// Count match referee
			$this->jsmquery->select('count(mr.id)');
			$this->jsmquery->from('#__sportsmanagement_match_referee AS mr ');
			$this->jsmquery->where('mr.match_id = ' . $item->id);
			$this->jsmdb->setQuery($this->jsmquery);
			$item->referees_count = $this->jsmdb->loadResult();
		}

		return $items;
	}

	/**
	 * sportsmanagementModelMatches::checkMatchPicturePath()
	 *
	 * @param   mixed  $match_id
	 *
	 * @return
	 */
	function checkMatchPicturePath($match_id)
	{
		$dest   = JPATH_ROOT . '/images/com_sportsmanagement/database/matchreport/' . $match_id;
		$folder = 'matchreport/' . $match_id;
		$this->setState('folder', $folder);

		if (Folder::exists($dest))
		{
		}
		else
		{
			Folder::create($dest);
		}

	}

	/**
	 * sportsmanagementModelMatches::getMatchesCount()
	 *
	 * @param   mixed  $project_id
	 *
	 * @return
	 */
	function getMatchesCount($project_id)
	{
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		$query->select('count(m.id)');
		$query->from('#__sportsmanagement_match as m');
		$query->join('INNER', '#__sportsmanagement_round as r ON r.id = m.round_id');
		$query->where('r.project_id = ' . $project_id);

		$db->setQuery($query);

		return $db->loadResult();

	}

	/**
	 * sportsmanagementModelMatches::getMatchesByRound()
	 *
	 * @param   mixed  $roundId
	 *
	 * @return
	 */
	function getMatchesByRound($roundId)
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		// Create a new query object.
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__sportsmanagement_match as m');
		$query->where('round_id = ' . $roundId);

		try
		{
			$db->setQuery($query);
			$result = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . Text::_($e->getMessage()), 'Error');
			$result = false;
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
	protected function populateState($ordering = null, $direction = null)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info'))
		{
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' context -> ' . $this->context . ''), '');
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' identifier -> ' . $this->_identifier . ''), '');
		}

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
		$temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.division', 'filter_division', '');
		$this->setState('filter.division', $temp_user_request);
		$value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $app->get('list_limit'), 'int');
		$this->setState('list.limit', $value);

		// List state information.
		parent::populateState('mc.match_date', 'asc');
		$value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);
	}

	/**
	 * sportsmanagementModelMatches::getListQuery()
	 *
	 * @return
	 */
	protected function getListQuery()
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput           = $app->input;
		$option           = $jinput->getCmd('option');
		$this->_season_id = $app->getUserState("$option.season_id", '0');

		// $search_division  = $this->getState('filter.division');

		$this->_rid         = Factory::getApplication()->input->getvar('rid', 0);
		$this->_projectteam = Factory::getApplication()->input->getvar('projectteam', 0);

		if (!$this->_rid)
		{
			$this->_rid = $app->getUserState("$option.rid", '0');
		}

		if ($this->_projectteam)
		{
			$this->_rid = '';
		}

		// Create a new query object.
		$db                 = sportsmanagementHelper::getDBConnection();
		$query              = $db->getQuery(true);
		$subQueryPlayerHome = $db->getQuery(true);
		$subQueryStaffHome  = $db->getQuery(true);
		$subQueryPlayerAway = $db->getQuery(true);
		$subQueryStaffAway  = $db->getQuery(true);
		$subQuery1          = $db->getQuery(true);
		$subQuery2          = $db->getQuery(true);
		$subQuery3          = $db->getQuery(true);
		$subQuery4          = $db->getQuery(true);
		$subQuery5          = $db->getQuery(true);

		$query->clear();
		$query->select('mc.*');

		// From the match table
		$query->from('#__sportsmanagement_match AS mc');

		// Join over the users for the checked out user.
		$query->select('u.name AS editor');
		$query->join('LEFT', '#__users AS u on mc.checked_out = u.id');
		$query->join('LEFT', '#__sportsmanagement_project_team AS pthome ON pthome.id = mc.projectteam1_id');
		$query->join('LEFT', '#__sportsmanagement_project_team AS ptaway ON ptaway.id = mc.projectteam2_id');
		$query->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = pthome.id');
		$query->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = ptaway.id');
		$query->join('LEFT', '#__sportsmanagement_round AS r ON r.id = mc.round_id ');
		$query->select('divaway.id as divawayid');
		$query->join('LEFT', '#__sportsmanagement_division AS divaway ON divaway.id = ptaway.division_id');
		$query->select('divhome.id as divhomeid');
		$query->join('LEFT', '#__sportsmanagement_division AS divhome ON divhome.id = pthome.division_id');

		if ($this->_rid)
		{
			$query->where(' mc.round_id = ' . $this->_rid);
		}

		if ($this->_projectteam)
		{
			$query->where('( mc.projectteam1_id = ' . $this->_projectteam . ' OR mc.projectteam2_id = ' . $this->_projectteam . ' )');
		}

		if ($this->getState('filter.division'))
		{
			$query->where(' divhome.id = ' . $this->_db->Quote($this->getState('filter.division')));
		}

		$query->order(
			$db->escape($this->getState('list.ordering', 'mc.match_date')) . ' ' .
			$db->escape($this->getState('list.direction', 'ASC'))
		);

		return $query;

	}

}
