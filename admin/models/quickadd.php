<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage seasons
 * @file       seasons.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelQuickAdd
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2019
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelQuickAdd extends JSMModelList
{
	var $_identifier = "quickadd";


	/**
	 * sportsmanagementModelQuickAdd::getNotAssignedPlayers()
	 *
	 * @param   mixed $searchterm
	 * @param   mixed $projectteam_id
	 * @param   mixed $searchinfo
	 * @return
	 */
	function getNotAssignedPlayers($searchterm, $projectteam_id,$searchinfo = null )
	{
		$query  = "	SELECT pl.*, pl.id as id2
					FROM #__sportsmanagement_person AS pl
					WHERE	(	LOWER( CONCAT(pl.firstname, ' ', pl.lastname) ) LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " OR
								alias LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " OR
								nickname LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " OR
								id = " . $this->_db->Quote($searchterm) . ")
								AND pl.published = '1'
								AND pl.id NOT IN ( SELECT person_id
								FROM #__sportsmanagement_team_player AS tp
								WHERE	projectteam_id = " . $this->_db->Quote($projectteam_id) . " AND
										tp.person_id = pl.id ) ";

		if ($searchinfo)
		{
			$query .= " AND pl.info LIKE '" . $searchinfo . "' ";
		}

			  $option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		$filter_order        = $app->getUserStateFromRequest($option . 'pl_filter_order', 'filter_order', 'pl.lastname', 'cmd');
		$filter_order_Dir    = $app->getUserStateFromRequest($option . 'pl_filter_order_Dir',    'filter_order_Dir', '',    'word');

		if ($filter_order == 'pl.lastname')
		{
			$orderby     = ' ORDER BY pl.lastname ' . $filter_order_Dir;
		}
		else
		{
			$orderby     = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . ' , pl.lastname ';
		}

		$query = $query . $orderby;
		$this->_db->setQuery($query);

		if (!$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit')))
		{
			echo $this->_db->getErrorMsg();
		}

			$this->_total = $this->_getListCount($query);

			return $this->_data;
	}



	/**
	 * sportsmanagementModelQuickAdd::getNotAssignedStaff()
	 *
	 * @param   mixed $searchterm
	 * @param   mixed $projectteam_id
	 * @param   mixed $searchinfo
	 * @return
	 */
	function getNotAssignedStaff($searchterm, $projectteam_id,$searchinfo = null)
	{
		$query  = "SELECT pl.* ";
		$query .= "FROM #__sportsmanagement_person AS pl ";
		$query .= "WHERE (LOWER( CONCAT(pl.firstname, ' ', pl.lastname) ) LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " ";
		$query .= "   OR alias LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " ";
		$query .= "   OR nickname LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " ";
		$query .= "   OR pl.id = " . $this->_db->Quote($searchterm) . ") ";
		$query .= "   AND pl.published = '1'";
		$query .= "   AND pl.id NOT IN ( SELECT person_id ";
		$query .= "                     FROM #__sportsmanagement_team_staff AS ts ";
		$query .= "                     WHERE projectteam_id = " . $this->_db->Quote($projectteam_id);
		$query .= "                     AND ts.person_id = pl.id ) ";

		if ($searchinfo)
		{
			$query .= " AND pl.info LIKE '" . $searchinfo . "' ";
		}

			  $option = Factory::getApplication()->input->getCmd('option');
			$app    = Factory::getApplication();

			$filter_order        = $app->getUserStateFromRequest($option . 'pl_filter_order', 'filter_order', 'pl.lastname', 'cmd');
			$filter_order_Dir    = $app->getUserStateFromRequest($option . 'pl_filter_order_Dir',    'filter_order_Dir', '',    'word');

		if ($filter_order == 'pl.lastname')
		{
			$orderby     = ' ORDER BY pl.lastname ' . $filter_order_Dir;
		}
		else
		{
			$orderby     = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . ' , pl.lastname ';
		}

			$query = $query . $orderby;
			$this->_db->setQuery($query);

		if (!$this->_data = $this->_getList(
			$query,
			$this->getState('limitstart'),
			$this->getState('limit')
		)
		)
		{
			echo $this->_db->getErrorMsg();
		}

			$this->_total = $this->_getListCount($query);

			return $this->_data;
	}


	/**
	 * sportsmanagementModelQuickAdd::getNotAssignedReferees()
	 *
	 * @param   mixed $searchterm
	 * @param   mixed $projectid
	 * @param   mixed $searchinfo
	 * @return
	 */
	function getNotAssignedReferees($searchterm, $projectid,$searchinfo = null)
	{
		$query  = "SELECT pl.* ";
		$query .= "FROM #__sportsmanagement_person AS pl ";
		$query .= "WHERE (LOWER( CONCAT(pl.firstname, ' ', pl.lastname) ) LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " ";
		$query .= "   OR alias LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " ";
		$query .= "   OR nickname LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " ";
		$query .= "   OR pl.id = " . $this->_db->Quote($searchterm) . ") ";
		$query .= "   AND pl.published = '1'";
		$query .= "   AND pl.id NOT IN ( SELECT person_id ";
		$query .= "                     FROM #__sportsmanagement_project_referee AS pr ";
		$query .= "                     WHERE project_id = " . $this->_db->Quote($projectid);
		$query .= "                     AND pr.person_id = pl.id ) ";

		if ($searchinfo)
		{
			$query .= " AND pl.info LIKE '" . $searchinfo . "' ";
		}

			  $option = Factory::getApplication()->input->getCmd('option');
			$app    = Factory::getApplication();

			$filter_order        = $app->getUserStateFromRequest($option . 'pl_filter_order', 'filter_order', 'pl.lastname', 'cmd');
			$filter_order_Dir    = $app->getUserStateFromRequest($option . 'pl_filter_order_Dir',    'filter_order_Dir', '',    'word');

		if ($filter_order == 'pl.lastname')
		{
			$orderby     = ' ORDER BY pl.lastname ' . $filter_order_Dir;
		}
		else
		{
			$orderby     = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . ' , pl.lastname ';
		}

			$query = $query . $orderby;

			  $this->_db->setQuery($query);

		if (!$this->_data = $this->_getList(
			$query,
			$this->getState('limitstart'),
			$this->getState('limit')
		)
		)
		{
			echo $this->_db->getErrorMsg();
		}

			$this->_total = $this->_getListCount($query);

			return $this->_data;
	}


	/**
	 * sportsmanagementModelQuickAdd::getNotAssignedTeams()
	 *
	 * @param   mixed $searchterm
	 * @param   mixed $projectid
	 * @return
	 */
	function getNotAssignedTeams($searchterm, $projectid)
	{
		$query  = "SELECT t.* ";
		$query .= "FROM #__sportsmanagement_team AS t ";
		$query .= "WHERE (LOWER( t.name ) LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " ";
		$query .= "   OR alias LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " ";
		$query .= "   OR LOWER( short_name ) LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " ";
		$query .= "   OR LOWER( middle_name ) LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " ";
		$query .= "   OR id = " . $this->_db->Quote($searchterm) . ") ";
		$query .= "   AND t.id NOT IN ( SELECT team_id ";
		$query .= "                     FROM #__sportsmanagement_project_team AS pt ";
		$query .= "                     WHERE project_id = " . $this->_db->Quote($projectid);
		$query .= ") ";

		$this->_db->setQuery($query);

		if (!$this->_data = $this->_getList(
			$query,
			$this->getState('limitstart'),
			$this->getState('limit')
		)
		)
		{
			echo $this->_db->getErrorMsg();
		}

		$this->_total = $this->_getListCount($query);

		return $this->_data;
	}

	/**
	 * sportsmanagementModelQuickAdd::addPlayer()
	 *
	 * @param   mixed $projectteam_id
	 * @param   mixed $personid
	 * @param   mixed $name
	 * @return
	 */
	function addPlayer($projectteam_id, $personid, $name = null)
	{
		if (!$personid && empty($name))
		{
			$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_QUICKADD_CTRL_ADD_PLAYER_REQUIRES_ID_OR_NAME'));

			return false;
		}

		// Add the new individual as their name was sent through.
		if (!$personid)
		{
			$mdlPerson = BaseDatabaseModel::getInstance('Person', 'sportsmanagementModel');
			$name = explode(" ", $name);
			$firstname = '';
			$nickname = '';
			$lastname = '';

			if (count($name) == 1)
			{
				$firstname = ucfirst($name[0]);
				$nickname = $name[0];
				$lastname = ".";
			}

			if (count($name) == 2)
			{
				$firstname = ucfirst($name[0]);
				$nickname = $name[1];
				$lastname = ucfirst($name[1]);
			}

			if (count($name) == 3)
			{
				$firstname = ucfirst($name[0]);
				$nickname = $name[1];
				$lastname = ucfirst($name[2]);
			}

			$data = array(
			  "firstname" => $firstname,
			  "nickname" => $nickname,
			  "lastname" => $lastname,
			  "published" => 1
			);
			$mdlPerson->store($data);
			$personid = $mdlPerson->_db->insertid();
		}

		if (!$personid)
		{
			$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_QUICKADD_CTRL_FAILED_ADDING_PERSON'));

			return false;
		}

		/**
 * muss noch programmiert werden
 */

		return true;
	}
}
