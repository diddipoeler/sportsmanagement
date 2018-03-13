<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      currentseason.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');

/**
 * sportsmanagementControllercurrentseason
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllercurrentseason extends JController
{
	protected $view_list = 'currentseasons';
    
    /**
     * sportsmanagementControllercurrentseason::__construct()
     * 
     * @return void
     */
    function __construct()
	{
		parent::__construct();


	}
    
    /**
     * sportsmanagementControllercurrentseason::display()
     * 
     * @return void
     */
    function display()
	{
	
		parent::display();
	}

}

?>    