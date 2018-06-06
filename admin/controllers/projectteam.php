<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      projectteam.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
//jimport('joomla.application.component.controllerform');
 

/**
 * sportsmanagementControllerprojectteam
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerprojectteam extends JSMControllerForm
{

/**
 * sportsmanagementControllerprojectteam::storechangeteams()
 * 
 * @return void
 */
function storechangeteams()
	{
        $model = $this->getModel ('projectteams');
        $model->setNewTeamID();
        $msg = '';
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
    }




}
