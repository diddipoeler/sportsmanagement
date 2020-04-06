<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage sportsmanagement
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Log\Log;

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * HTML View class for the HelloWorld Component
 */
class sportsmanagementViewsportsmanagement extends JViewLegacy
{
    // Overwriting JViewLegacy display method
    function display($tpl = null)
    {
        // Assign data to the view
        $this->item = $this->get('Item');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            Log::add(implode('<br />', $errors));
            return false;
        }
        // Display the view
        parent::display($tpl);
    }
}
