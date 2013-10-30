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
jimport('joomla.filter.input');

/**
* projectteam Table class
*
* @package		Joomleague
* @since 0.1
*/
class TableProjectteam extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct(& $db)
	{
		parent::__construct( '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team', 'id', $db );
	}

	/**
	 * Default delete method
	 **
	 * @access public
	 * @return true if successful otherwise returns and error message
	 */
	function delete( $oid=null )
	{
		//TODO: check that there are no associated players / matches / events / trainingdata

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

}
?>