<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       teamplayers.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * sportsmanagementModelTeamPlayers
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class sportsmanagementModelTeamPlayers extends ListModel
{
	var $_identifier = "teamplayers";

	var $_project_id = 0;

	var $_season_id = 0;

	var $_team_id = 0;

	var $_project_team_id = 0;

	function getListQuery()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();

		// Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

					  $this->_project_id    = $app->getUserState("$option.pid", '0');
		$this->_season_id    = $app->getUserState("$option.season_id", '0');
		$this->_team_id = Factory::getApplication()->input->getVar('team_id');
		$this->_project_team_id = Factory::getApplication()->input->getVar('project_team_id');

		if (!$this->_team_id)
		{
			$this->_team_id    = $app->getUserState("$option.team_id", '0');
		}

		if (!$this->_project_team_id)
		{
					$this->_project_team_id    = $app->getUserState("$option.project_team_id", '0');
		}

			  // Get the WHERE and ORDER BY clauses for the query
			$where = self::_buildContentWhere();
			$orderby = self::_buildContentOrderBy();

		if (COM_SPORTSMANAGEMENT_USE_NEW_TABLE)
		{
			$query->select(
				array('ppl.firstname',
				'ppl.lastname',
				'ppl.nickname',
					  'ppl.height',
					  'ppl.weight',

												'ppl.injury',
					  'ppl.suspension',
					  'ppl.away',

												'ppl.id',
					  'ppl.id AS person_id',
				'tp.*',
					  'tp.id as tpid',
				'u.name AS editor')
			)
				->from('#__sportsmanagement_person AS ppl')
				->join('INNER', '#__sportsmanagement_season_team_person_id AS tp on tp.person_id = ppl.id')
				->join('LEFT', '#__users AS u ON u.id = tp.checked_out');
		}
		else
		{
			$query->select(
				array('ppl.firstname',
				'ppl.lastname',
				'ppl.nickname',
						'ppl.height',
						'ppl.weight',
						'ppl.id',
						'ppl.id AS person_id',
				'tp.*',
						'tp.id as tpid',
				'u.name AS editor')
			)
				->from('#__sportsmanagement_person AS ppl')
				->join('INNER', '#__sportsmanagement_team_player AS tp on tp.person_id = ppl.id')
				->join('LEFT', '#__users AS u ON u.id = tp.checked_out');
		}

		if ($where)
		{
			$query->where($where);
		}

		if ($orderby)
		{
					$query->order($orderby);
		}

			return $query;
	}

	function _buildContentOrderBy()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
		$filter_order        = $app->getUserStateFromRequest($option . '.' . $this->_identifier . '.tp_filter_order', 'filter_order', 'tp.ordering', 'cmd');
		$filter_order_Dir    = $app->getUserStateFromRequest($option . '.' . $this->_identifier . '.tp_filter_order_Dir', 'filter_order_Dir', '', 'word');

		if ($filter_order == 'ppl.lastname')
		{
			$orderby = ' ppl.lastname ' . $filter_order_Dir;
		}
		else
		{
			$orderby = ' ' . $filter_order . ' ' . $filter_order_Dir . ',ppl.lastname ';
		}

		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();

			  // $project_id=$app->getUserState($option.'project');
		// $team_id=$app->getUserState($option.'project_team_id');

			  $filter_state    = $app->getUserStateFromRequest($option . '.' . $this->_identifier . '.tp_filter_state', 'filter_state', '', 'word');
		$search            = $app->getUserStateFromRequest($option . '.' . $this->_identifier . '.tp_search', 'search', '', 'string');
		$search_mode    = $app->getUserStateFromRequest($option . '.' . $this->_identifier . '.tp_search_mode', 'search_mode', '', 'string');
		$search = JString::strtolower($search);
		$where = array();

		if (COM_SPORTSMANAGEMENT_USE_NEW_TABLE)
		{
			$where[] = "ppl.published = '1'";
			$where[] = 'tp.team_id=' . $this->_team_id;
			$where[] = 'tp.season_id=' . $this->_season_id;
			$where[] = 'tp.persontype=1';
		}
		else
		{
			$where[] = 'tp.projectteam_id= ' . $this->_project_team_id;
			$where[] = "ppl.published = '1'";
		}

		if ($search)
		{
			if ($search_mode)
			{
				$where[] = '(LOWER(ppl.lastname) LIKE ' . $this->_db->Quote($search . '%') .
				'OR LOWER(ppl.firstname) LIKE ' . $this->_db->Quote($search . '%') .
				'OR LOWER(ppl.nickname) LIKE ' . $this->_db->Quote($search . '%') . ')';
			}
			else
			{
				$where[] = '(LOWER(ppl.lastname) LIKE ' . $this->_db->Quote('%' . $search . '%') .
				'OR LOWER(ppl.firstname) LIKE ' . $this->_db->Quote('%' . $search . '%') .
				'OR LOWER(ppl.nickname) LIKE ' . $this->_db->Quote('%' . $search . '%') . ')';
			}
		}

		if ($filter_state)
		{
			if ($filter_state == 'P')
			{
				$where[] = 'tp.published = 1';
			}
			elseif ($filter_state == 'U')
			{
				$where[] = 'tp.published = 0';
			}
		}

			$where = (count($where) ? ' ' . implode(' AND ', $where) : '');

			return $where;
	}

	/**
	 * sportsmanagementModelTeamPlayers::getProjectTeamplayers()
	 *
	 * @param   integer $project_team_id
	 * @return
	 */
	function getProjectTeamplayers($project_team_id = 0)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();

		// Create a new query object.
		$db        = sportsmanagementHelper::getDBConnection();
		$query    = $db->getQuery(true);
		$user    = Factory::getUser();

		// Select some fields
		$query->select('pl.*');

		// From table
		$query->from('#__sportsmanagement_person as pl');
		$query->join('INNER', '#__sportsmanagement_team_player as tpl on tpl.person_id = pl.id');
		$query->join('INNER', '#__sportsmanagement_project_team as pt on pt.id = tpl.projectteam_id');
		$query->where('pt.team_id = ' . $project_team_id);
		$db->setQuery($query);
		$result = $db->loadObjectList();

		if (!$result)
		{
			return false;
		}

					return $result;
	}

	/**
	 * remove specified players from team
	 *
	 * @param  $cids player ids
	 * @return integer count of removed
	 */
	function remove($cids)
	{
		$count = 0;

		foreach ($cids as $cid)
		{
			$object=&$this->getTable('teamplayer');

			if ($object->canDelete($cid) && $object->delete($cid))
			{
				$count++;
			}
			else
			{
				$this->setError(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFFS_MODEL_ERROR_REMOVE_TEAMPLAYER', $object->getError()));
			}
		}

		return $count;
	}

}
