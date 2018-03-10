<?php

/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      controller.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controller library
jimport('joomla.application.component.controller');
//jimport('joomla.application.component.controllerlegacy');

/**
 * sportsmanagementController
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
//class sportsmanagementController extends JController
class sportsmanagementController extends JControllerLegacy {

    function __construct($config = array()) {
        parent::__construct($config);
    }

    public function display($cachable = false, $urlparams = false) {
        parent::display($cachable, $urlparams);
    }

}

?>