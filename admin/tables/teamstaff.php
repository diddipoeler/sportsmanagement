<?php
/**
 * @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
// import Joomla table library
jimport('joomla.database.table');
// Include library dependencies
jimport( 'joomla.filter.input' );

/**
 * Joomleague TeamStaff Table class
 *
 * @package	Sportsmanagement
 * @since	1.50a
 */
class sportsmanagementTableTeamStaff extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct(& $db)
	{
		parent::__construct( '#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff', 'id', $db );
	}

	function canDelete($id)
	{
		// the staff cannot be deleted if assigned to games
		$query = ' SELECT COUNT(id) FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff '
		       . ' WHERE team_staff_id = '. $this->getDbo()->Quote($id)
		       . ' GROUP BY team_staff_id ';
		$this->getDbo()->setQuery($query, 0, 1);
		$res = $this->getDbo()->loadResult();
		
		if ($res) {
			$this->setError(Jtext::sprintf('STAFF ASSIGNED TO %d GAMES', $res));
			return false;
		}
		
		// the staff cannot be deleted if has stats
		$query = ' SELECT COUNT(id) FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff_statistic '
		       . ' WHERE team_staff_id = '. $this->getDbo()->Quote($id)
		       . ' GROUP BY team_staff_id ';
		$this->getDbo()->setQuery($query, 0, 1);
		$res = $this->getDbo()->loadResult();
		
		if ($res) {
			$this->setError(JText::sprintf('%d STATS ASSIGNED TO STAFF', $res));
			return false;
		}
		
		return true;
	}
}
?>