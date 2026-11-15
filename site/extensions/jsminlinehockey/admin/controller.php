<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage extension jsminlinehockey controllers
 * @file       controller.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Keep the historical Inline Hockey extension controller name available.
 *
 * This bridge continues to delegate rendering to Joomla's namespaced
 * controller base while older extension installers still load the file.
 */
class sportsmanagementController extends AdminController
{

    /**
     * Display the selected Inline Hockey administrator view through Joomla MVC.
     *
     * Accepts the historical boolean URL argument from old integrations,
     * converts it to Joomla 5/6's expected array and preserves the parent
     * controller's result for callers that chain display operations.
     *
     * @param bool $cachable Whether Joomla may cache the rendered view.
     * @param array|bool $urlparams Cacheable URL parameters or legacy false.
     * @return mixed Parent MVC controller display result.
     */
    public function display($cachable = false, $urlparams = [])
    {
        return parent::display($cachable, is_array($urlparams) ? $urlparams : []);
    }

}

/**
 * Retain the legacy Inline Hockey dashboard controller alias.
 *
 * Some installations still reference this original class name when
 * rendering the extension control panel instead of using Joomla namespaces.
 */
class sportsmanagementControllerControllersportsmanagementController extends sportsmanagementController
{
}
