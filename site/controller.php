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

/**
 * No direct access to this file
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * sportsmanagementController
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementController extends BaseController {

    /**
     * sportsmanagementController::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    function __construct($config = array()) {
        parent::__construct($config);
    }

    /**
     * sportsmanagementController::display()
     * 
     * @param bool $cachable
     * @param bool $urlparams
     * @return void
     */
    public function display($cachable = false, $urlparams = false) {
        parent::display($cachable, $urlparams);
    }

}

?>