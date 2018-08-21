<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      githubinstall.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text; 
use Joomla\CMS\Session\Session;
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 
/**
 * SportsManagement Controller
 */
/**
 * sportsmanagementControllergithubinstall
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllergithubinstall extends JControllerForm
{

/**
	 * Method to store 
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
  function store()
	{
        // Check for request forgeries
		Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));
$msg = '';

        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
	}

}
