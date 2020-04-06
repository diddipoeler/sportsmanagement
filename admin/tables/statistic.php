<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       statistic.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage tables
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filter\OutputFilter;

/**
 * sportsmanagementTableStatistic
 * 
 * @package 
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementTableStatistic extends JSMTable
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
        parent::__construct('#__sportsmanagement_statistic', 'id', $db);
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
        if (empty($this->name)) {
            $this->setError(Text::_('NAME REQUIRED'));
            return false;
        }
        
        if (empty($this->short)) {
            $this->short = strtoupper(substr($this->name, 0, 4));
        }
    
        // setting alias
        if (empty($this->alias) ) {
            $this->alias = OutputFilter::stringURLSafe($this->name);
        }
        else {
            $this->alias = OutputFilter::stringURLSafe($this->alias); // make sure the user didn't modify it to something illegal...
        }
        
        return true;
    }
    
    
}
