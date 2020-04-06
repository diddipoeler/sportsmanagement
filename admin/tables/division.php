<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       division.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage tables
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Filter\OutputFilter;

/**
 * sportsmanagementTableDivision
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementTableDivision extends JSMTable
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
        parent::__construct('#__sportsmanagement_division', 'id', $db);
    }

    /**
     * Overloaded check method to ensure data integrity
     *
     * @access public
     * @return boolean True on success
     * @since  1.0
     */
    function check()
    {
        // setting alias
        $this->alias = OutputFilter::stringURLSafe($this->name);
      
        //should check name unicity
        return true;
    }
}
?>
