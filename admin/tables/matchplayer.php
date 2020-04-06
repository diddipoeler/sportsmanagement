<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       matchplayer.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage tables
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementTableMatchplayer
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementTableMatchplayer extends JSMTable
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
        parent::__construct('#__sportsmanagement_match_player', 'id', $db);
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
        if (! ( $this->match_id && $this->teamplayer_id ) ) {
            $this->setError(Text::_('CHECK FAILED'));
            return false;
        }
        return true;
    }
}
?>
