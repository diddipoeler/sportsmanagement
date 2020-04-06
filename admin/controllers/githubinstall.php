<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       githubinstall.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage controllers
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text; 
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\FormController;
 
/**
 * SportsManagement Controller
 */
/**
 * sportsmanagementControllergithubinstall
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllergithubinstall extends FormController
{

    /**
     * Method to store 
     *
     * @access public
     * @return boolean    True on success
     */
    function store()
    {
        // Check for request forgeries
        Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));
        $msg = '';

        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $msg);
    }

}
