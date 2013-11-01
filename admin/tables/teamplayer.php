<?php
/**
 * @copyright	Copyright (C) 2006-2013 JoomLeague.net. All rights reserved.
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
 * Joomleague Person Table class
 *
 * @package	JoomLeague
 * @since 	1.50a
 */
class sportsmanagementTableTeamPlayer extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct(& $db)
	{
		parent::__construct( '#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player', 'id', $db );
	}

	/**
	 * Default delete method
	 **
	 * @access public
	 * @return true if successful otherwise returns and error message
	 */
	function delete( $oid=null )
	{
		//TODO: check that there are no events and and matches associated to this player

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}

		$query = 'DELETE FROM '.$this->getDbo()->nameQuote( $this->_tbl ).
				' WHERE '.$this->_tbl_key.' = '. $this->getDbo()->Quote($this->$k);
		$this->getDbo()->setQuery( $query );

		if ($this->getDbo()->query())
		{
			return true;
		}
		else
		{
			$this->setError($this->getDbo()->getErrorMsg());
			return false;
		}
	}

	function canDelete($id)
	{
		// cannot be deleted if assigned to games
		$query = ' SELECT COUNT(id) FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player '
		       . ' WHERE teamplayer_id = '. $this->getDbo()->Quote($id)
		       . ' GROUP BY teamplayer_id ';
		$this->getDbo()->setQuery($query, 0, 1);
		$res = $this->getDbo()->loadResult();
		
		if ($res) {
			$this->setError(Jtext::sprintf('PLAYER ASSIGNED TO %d GAMES', $res));
			return false;
		}
		
		// cannot be deleted if has events
		$query = ' SELECT COUNT(id) FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event '
		       . ' WHERE teamplayer_id = '. $this->getDbo()->Quote($id)
		       . ' GROUP BY teamplayer_id ';
		$this->getDbo()->setQuery($query, 0, 1);
		$res = $this->getDbo()->loadResult();
		
		if ($res) {
			$this->setError(JText::sprintf('%d EVENTS ASSIGNED TO PLAYER', $res));
			return false;
		}
		
		// cannot be deleted if has stats
		$query = ' SELECT COUNT(id) FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic '
		       . ' WHERE teamplayer_id = '. $this->getDbo()->Quote($id)
		       . ' GROUP BY teamplayer_id ';
		$this->getDbo()->setQuery($query, 0, 1);
		$res = $this->getDbo()->loadResult();
		
		if ($res) {
			$this->setError(JText::sprintf('%d STATS ASSIGNED TO PLAYER', $res));
			return false;
		}
		
		return true;
	}
}
?>