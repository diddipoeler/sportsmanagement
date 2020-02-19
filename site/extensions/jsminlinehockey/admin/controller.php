<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      controller.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage extension jsminlinehockey controllers
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\AdminController;

/**
 * sportsmanagementController
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2019
 * @version $Id$
 * @access public
 */
class sportsmanagementController extends AdminController
{

	/**
	 * sportsmanagementController::display()
	 * 
	 * @param bool $cachable
	 * @param bool $urlparams
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false)
	{

		parent::display($cachable, $urlparams);

	}
	
}

/**
 *
 * just to display the cpanel
 * @author 
 *
 */
class sportsmanagementControllerControllersportsmanagementController extends sportsmanagementController {
}
