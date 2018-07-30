<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      treeto.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage tables
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
// import Joomla table library
jimport('joomla.database.table');
// Include library dependencies
jimport('joomla.filter.input');

/**
 * sportsmanagementTableTreeto
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementTableTreeto extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	public function __construct(& $db) {
	   $db = sportsmanagementHelper::getDBConnection();
		parent::__construct('#__sportsmanagement_treeto', 'id', $db);
	}

	/**
	 * sportsmanagementTableTreeto::bind()
	 * 
	 * @param mixed $array
	 * @param string $ignore
	 * @return
	 */
	public function bind( $array, $ignore = '' )
	{
		if ( key_exists( 'params', $array ) && is_array( $array['params'] ) )
		{
			$registry = new JRegistry();
			$registry->loadArray( $array['params'] );
			$array['params'] = $registry->toString();
		}
		if ( key_exists( 'comp_params', $array ) && is_array( $array['comp_params'] ) )
		{
			$registry = new JRegistry();
			$registry->loadArray( $array['comp_params'] );
			$array['comp_params'] = $registry->toString();
		}
    	//print_r( $array );exit;
		return parent::bind( $array, $ignore );
	}
}
?>
