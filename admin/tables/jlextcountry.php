<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      jlextcountry.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage tables
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// import Joomla table library
jimport('joomla.database.table');


/**
 * sportsmanagementTablejlextcountry
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementTablejlextcountry extends JTable 
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct(& $db)
	{
	   $db = sportsmanagementHelper::getDBConnection();
		parent :: __construct( '#__sportsmanagement_countries', 'id', $db );
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
		/*
    // setting alias
		if ( empty( $this->alias ) )
		{
			$this->alias = JFilterOutput::stringURLSafe( $this->name );
		}
		else {
			$this->alias = JFilterOutput::stringURLSafe( $this->alias ); // make sure the user didn't modify it to something illegal...
		}
		*/
		//should check name unicity
		return true;
	}
	
	/**
	 * Overloaded bind function
	 *
	 * @param       array           named array
	 * @return      null|string     null is operation was satisfactory, otherwise returns an error
	 * @see JTable:bind
	 * @since 1.5
	 */
	function bind($array, $ignore = '')
	{
		if (key_exists( 'extended', $array ) && is_array( $array['extended'] ))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['extended']);
			$array['extended'] = (string) $registry;
		}
		if (key_exists( 'extendeduser', $array ) && is_array( $array['extendeduser'] ))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['extendeduser']);
			$array['extendeduser'] = (string) $registry;
		}
		return parent::bind($array, $ignore);
	}
	
}
?>