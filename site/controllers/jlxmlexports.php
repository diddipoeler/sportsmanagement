<?php

/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jlxmlexports.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jlxmlexports
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * sportsmanagementControllerjlxmlexports
 * 
 * @package 
 * @author Dieter Pl�ger
 * @copyright 2016
 * @version $Id$
 * @access public
 */

class sportsmanagementControllerjlxmlexports extends JControllerLegacy {

    /**
     * sportsmanagementControllerjlxmlexports::display()
     * 
     * @return void
     */
    function display() {

        $this->showranking();
    }

    /**
     * sportsmanagementControllerjlxmlexports::showranking()
     * 
     * @return void
     */
    function showranking() {
        
    }

}
