<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage league
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');


/**
 * sportsmanagementViewLeague
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewLeague extends sportsmanagementView
{
	

	/**
	 * sportsmanagementViewLeague::init()
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
				
        $this->form->setValue('sports_type_id', 'request', $this->item->sports_type_id);
        $this->form->setValue('agegroup_id', 'request', $this->item->agegroup_id);
        
		$extended = sportsmanagementHelper::getExtended($this->item->extended, 'league');
		$this->extended	= $extended;
        $extendeduser = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'league');		
		$this->extendeduser	= $extendeduser;
		
	}
 
	
	/**
	 * sportsmanagementViewLeague::addToolBar()
	 * 
	 * @return void
	 */
	protected function addToolBar() 
	{
        $jinput = JFactory::getApplication()->input;
        $jinput->set('hidemainmenu', true);
        
		$isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_ADD_NEW');
        $this->icon = 'league';

        parent::addToolbar();
	}
    
	
}
