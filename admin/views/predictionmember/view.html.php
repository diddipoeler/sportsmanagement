<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionmember
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 

/**
 * sportsmanagementViewpredictionmember
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewpredictionmember extends sportsmanagementView
{
	
	
	/**
	 * sportsmanagementViewpredictionmember::init()
	 * 
	 * @return
	 */
	public function init ()
	{
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

	}
 
	
	/**
	 * sportsmanagementViewpredictionmember::addToolBar()
	 * 
	 * @return void
	 */
	protected function addToolBar() 
	{
		
		$jinput = JFactory::getApplication()->input;
        $jinput->set('hidemainmenu', true);

        $isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_ADD_NEW');
        $this->icon = 'pmember';
        
        $this->item->name = '';

		parent::addToolbar();  	
		
	}
    
	
}
