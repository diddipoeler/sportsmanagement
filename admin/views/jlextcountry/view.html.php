<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jlextcountry
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewJlextcountry
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewJlextcountry extends sportsmanagementView
{

    /**
     * sportsmanagementViewJlextcountry::init()
     * 
     * @return
     */
    public function init ()
	{
	//	// get the Data
//		$form = $this->get('Form');
//		$item = $this->get('Item');
//		$script = $this->get('Script');
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		//// Assign the Data
//		$this->form = $form;
//		$this->item = $item;
//		$this->script = $script;
	
	}
 
	
	/**
	 * sportsmanagementViewJlextcountry::addToolBar()
	 * 
	 * @return void
	 */
	protected function addToolBar() 
	{
		$this->jinput->setVar('hidemainmenu', true);
        parent::addToolbar();
	}
    

}
