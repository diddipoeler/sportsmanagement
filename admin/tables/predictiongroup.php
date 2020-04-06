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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filter\OutputFilter;

/**
 * sportsmanagementTablePredictionGroup
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class sportsmanagementTablePredictionGroup extends JSMTable
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
        parent::__construct('#__sportsmanagement_prediction_groups', 'id', $db);
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
        if (trim($this->name) == '' ) {
            $this->setError(Text::_('CHECK FAILED - Empty name of prediction game'));
            return false;
        }

        $alias = OutputFilter::stringURLSafe($this->name);
        if (empty($this->alias) || $this->alias === $alias ) {
            $this->alias = $alias;
        }

        return true;
    }

}
?>
