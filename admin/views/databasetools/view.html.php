<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage databasetools
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewDatabaseTools
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewDatabaseTools extends sportsmanagementView {

    /**
     * sportsmanagementViewDatabaseTools::init()
     * 
     * @return void
     */
    public function init() {
        $db = sportsmanagementHelper::getDBConnection();
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = JUri::getInstance();
        } else {
            $uri = JFactory::getURI();
        }

        $this->assign('request_url', $uri->toString());
    }

    /**
     * sportsmanagementViewDatabaseTools::addToolbar()
     * 
     * @return void
     */
    protected function addToolbar() {

//		// Set toolbar items for the page
        $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TITLE');
        $this->icon = 'databases';


        parent::addToolbar();
    }

}

?>