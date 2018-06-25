<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage about
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * sportsmanagementViewAbout
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewAbout extends sportsmanagementView {

    /**
     * sportsmanagementViewAbout::init()
     * 
     * @return void
     */
    function init() {

        $about = $this->model->getAbout();
        $this->about = $about;

        // Set page title
        $this->document->setTitle(JText::_('COM_SPORTSMANAGEMENT_ABOUT_PAGE_TITLE'));
    }

}

?>