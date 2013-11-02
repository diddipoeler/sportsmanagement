<?php

/**
 * @copyright	Copyright (C) 2006-2009 fussballineuropa.de. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// import Joomla table library
jimport('joomla.database.table');

/**
* Season Table class
*
* @package		Joomleague
* @since 0.1
*/
class sportsmanagementTablejlextassociation extends JTable 
{
	
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct(& $db)
	{
		parent :: __construct( '#__'.COM_SPORTSMANAGEMENT_TABLE.'_associations', 'id', $db );
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
		$alias = JFilterOutput::stringURLSafe($this->name);

		if ( empty( $this->alias ) || $this->alias === $alias )
		{
			$this->alias = $alias;
		}
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