<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      matchstatistic.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage tables
 */


defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementTableMatchStatistic
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementTableMatchStatistic extends JSMTable
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
		parent::__construct( '#__sportsmanagement_match_statistic', 'id', $db );
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
		if ( ! ( $this->statistic_id && $this->projectteam_id && $this->match_id) )
		{
			$this->setError( Text::_( 'CHECK FAILED' ) );
			return false;
		}
		*/
		return true;
	}
}
?>
