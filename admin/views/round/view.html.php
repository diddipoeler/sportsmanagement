<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage round
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewRound
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewRound extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewRound::init()
	 * 
	 * @return
	 */
	public function init ()
	{
        
        $this->project_id	= $this->app->getUserState( "$this->option.pid", '0' );
        $this->project_art_id	= $this->app->getUserState( "$this->option.project_art_id", '0' );

/**
 * Check for errors.
 */
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
        
        //$project_id	= $this->item->project_id;
        $mdlProject = JModelLegacy::getInstance('Project', 'sportsmanagementModel');
	    $project = $mdlProject->getProject($this->project_id);
        $this->project = $this->project_id;
        
	if ( $this->item->id )
	{
            // alles ok
	}
	else
	{
		$this->form->setValue('round_date_first', null, '0000-00-00');
		$this->form->setValue('round_date_last', null, '0000-00-00');
		$this->form->setValue('project_id', null, $this->project_id);
 
	}

	}
    
	/**
	* Add the page title and toolbar.
	*
	* @since	1.6
	*/
	protected function addToolBar() 
	{
	
		$jinput = JFactory::getApplication()->input;
        $jinput->set('hidemainmenu', true);
        $jinput->set('pid', $this->project_id);
        
        $isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_ROUND_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_ROUND_NEW');
        $this->icon = 'round';
        
	parent::addToolbar();
    

	}
    
    
}
?>
