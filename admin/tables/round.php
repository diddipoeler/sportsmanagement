<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      round.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage tables
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
// import Joomla table library
jimport('joomla.database.table');
// Include library dependencies
jimport('joomla.filter.input');

/**
 * sportsmanagementTableRound
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementTableRound extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct( & $db )
	{
	   $db = sportsmanagementHelper::getDBConnection();
		parent::__construct( '#__sportsmanagement_round', 'id', $db );
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
		// setting alias
		if ( empty( $this->alias ) )
		{
			$this->alias = JFilterOutput::stringURLSafe( $this->name );
		}
		else {
			$this->alias = JFilterOutput::stringURLSafe( $this->alias ); // make sure the user didn't modify it to something illegal...
		}
		//should check name unicity
		return true;
	}
    
    /**
	 * Method to determine if a row is checked out and therefore uneditable by
	 * a user. If the row is checked out by the same user, then it is considered
	 * not checked out -- as the user can still edit it.
	 *
	 * @param   integer  $with     The userid to preform the match with, if an item is checked
	 * out by this user the function will return false.
	 * @param   integer  $against  The userid to perform the match against when the function
	 * is used as a static function.
	 *
	 * @return  boolean  True if checked out.
	 *
	 * @link    http://docs.joomla.org/JTable/isCheckedOut
	 * @since   11.1
	 * @todo    This either needs to be static or not.
	 */
	//public function isCheckedOut($with = 0, $against = null)
    public static function _isCheckedOut($with = 0, $against = null)
	{
	   $app = JFactory::getApplication();
		// Handle the non-static case.
		if (isset($this) && ($this instanceof JTable) && is_null($against))
		{
			$against = $this->get('checked_out');
		}

		// The item is not checked out or is checked out by the same user.
		if (!$against || ($against == $with))
		{
			return false;
		}

		$db = sportsmanagementHelper::getDBConnection();
		$db->setQuery('SELECT COUNT(userid)' . ' FROM ' . $db->quoteName('#__session') . ' WHERE ' . $db->quoteName('userid') . ' = ' . (int) $against);
		$checkedOut = (boolean) $db->loadResult();
        
        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' checkedOut<br><pre>'.print_r($checkedOut,true).'</pre>'),'');

		// If a session exists for the user then it is checked out.
		return $checkedOut;
	}

}
?>