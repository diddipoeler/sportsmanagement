<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      person.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage tables
 */


defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Filter\OutputFilter;

/**
 * sportsmanagementTablePerson
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementTablePerson extends JSMTable
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
		parent::__construct( '#__sportsmanagement_person', 'id', $db );
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
		if ( empty( $this->firstname ) && empty( $this->lastname ) )
		{
			$this->setError( Text::_( 'ERROR FIRSTNAME OR LASTNAME REQUIRED' ) );
			return false;
		}
		$parts = array( trim( $this->firstname ), trim( $this->lastname ) );
		$alias = OutputFilter::stringURLSafe( implode( ' ', $parts ) );
	
		// setting alias
		if ( empty( $this->alias ) )
		{
			$this->alias = $alias;
		}
		else {
			$this->alias = OutputFilter::stringURLSafe( $this->alias ); // make sure the user didn't modify it to something illegal...
		}
		//should check name unicity
		return true;
	}
	
	
    
   	

}
?>