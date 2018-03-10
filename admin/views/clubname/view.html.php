<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage clubname
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');


/**
 * sportsmanagementViewclubname
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementViewclubname extends sportsmanagementView
{

	/**
	 * sportsmanagementViewclubname::init()
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
    
//    $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' item <br><pre>'.print_r($this->item,true).'</pre>'),'');
//    $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getLayout -> '.$this->getLayout().''),'');
        
	}
 
	
	/**
	 * sportsmanagementViewagegroup::addToolBar()
	 * 
	 * @return void
	 */
	protected function addToolBar() 
	{ 
        
		JFactory::getApplication()->input->setVar('hidemainmenu', true);
		$isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_CLUBNAME_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_CLUBNAME_NEW');
        $this->icon = 'clubname';
        		
        parent::addToolbar();
	}
	
}
