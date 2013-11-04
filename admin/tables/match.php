<?php
/**
 * @copyright	Copyright (C) 2013 fussballineuropa.de All rights reserved.
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
 * Match Table class
 *
 * @author Marco Vaninetti <martizva@tiscali.it>
 * @package	SportsManagement
 * @since	0.1
 */

class sportsmanagementTableMatch extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct(& $db)
	{
		parent::__construct( '#__'.COM_SPORTSMANAGEMENT_TABLE.'_match', 'id', $db );
	}

	/**
	 * Overloaded check method to ensure data integrity
	 *
	 * @access public
	 * @return boolean True on success
	 * @since 1.0
	 */
	function check()
	{
		if (!is_numeric($this->team1_result_decision)) {
			$this->team1_result_decision = null;
		}
		if (!is_numeric($this->team2_result_decision)) {
			$this->team2_result_decision = null;
		}
		
		return true;
	}

}
?>