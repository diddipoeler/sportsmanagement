<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      league.php
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
 * sportsmanagementTableLeague
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementTableLeague extends JTable 
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
		parent :: __construct( '#__sportsmanagement_league', 'id', $db );
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
        $this->alias = JFilterOutput::stringURLSafe( $this->name );
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
    
    ///**
//	 * Overloaded load function
//	 *
//	 * @param       int $pk primary key
//	 * @param       boolean $reset reset data
//	 * @return      boolean
//	 * @see JTable:load
//	 */
//	public function load($pk = null, $reset = true) 
//	{
//		if (parent::load($pk, $reset)) 
//		{
//			// Convert the params field to a registry.
//			$params = new JRegistry;
//			$params->loadJSON($this->extended);
//			//$params->toArray($this->extended);
//            $this->extended = $params->toArray($this->extended);
//            
//			return true;
//			
//		}
//		else
//		{
//			return false;
//		}
//	}
	
}
?>