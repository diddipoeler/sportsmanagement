<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage databasetools
 */
 

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

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
        
    }

    /**
     * sportsmanagementViewDatabaseTools::addToolbar()
     * 
     * @return void
     */
    protected function addToolbar() {

//		// Set toolbar items for the page
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TITLE');
        $this->icon = 'databases';


        parent::addToolbar();
    }

}

?>