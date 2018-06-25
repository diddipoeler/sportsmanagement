<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage eventtype
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementVieweventtype
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementVieweventtype extends sportsmanagementView
{
	
	
	/**
	 * sportsmanagementVieweventtype::init()
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
		
		$this->cfg_which_media_tool	= JComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_media_tool', 0);

	}
 
	
	/**
	 * sportsmanagementVieweventtype::addToolBar()
	 * 
	 * @return void
	 */
	protected function addToolBar() 
	{
	$app	= JFactory::getApplication();
	$jinput	= $app->input;
	$jinput->set('hidemainmenu', true);
	$isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_EVENTTYPE_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_EVENTTYPE_NEW');
        $this->icon = 'quote';
        parent::addToolbar();
	}
    
	
}
