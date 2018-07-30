<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      predictiontemplate.php
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
 * sportsmanagementTablePredictionTemplate
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementTablePredictionTemplate extends JTable
{
  
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.5
	 */
	function __construct(& $db)
	{
	   $db = sportsmanagementHelper::getDBConnection();
		parent::__construct('#__sportsmanagement_prediction_template', 'id', $db);
	}

	/**
	* Overloaded bind function
	*
	* @acces public
	* @param array $hash named array
	* @return null|string	null is operation was satisfactory, otherwise returns an error
	* @see JTable:bind
	* @since 1.5
	*/
//	function bind( $array, $ignore = '' )
//	{
//		if (key_exists( 'params', $array ) && is_array( $array['params'] )) {
//			$registry = new JRegistry();
//			$registry->loadArray($array['params']);
//			$array['params'] = $registry->toString('ini');
//			$array['params'] = ereg_replace('"', '', $array['params']);
//		}
//		return parent::bind($array, $ignore);
//	}

	/**
	 * Overloaded check method to ensure data integrity
	 *
	 * @access public
	 * @return boolean True on success
	 * @since 1.0
	 */
//	function check()
//	{
//		return true;
//	}

}
?>
