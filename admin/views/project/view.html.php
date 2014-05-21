<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.html.parameter.element.timezones');

require_once(JPATH_COMPONENT.DS.'models'.DS.'sportstypes.php');
require_once(JPATH_COMPONENT.DS.'models'.DS.'leagues.php');


/**
 * sportsmanagementViewProject
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewProject extends JView
{
	/**
	 * sportsmanagementViewProject::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$uri = JFactory::getURI();
		$user = JFactory::getUser();
        $starttime = microtime(); 
        
        if ($this->getLayout() == 'panel')
		{
			$this->_displayPanel($tpl);
			return;
		}
        

		// get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$script = $this->get('Script');
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign the Data
		$this->form = $form;
		$this->item = $item;
		$this->script = $script;
        
        $this->form->setValue('sports_type_id', 'request', $this->item->sports_type_id);
        $this->form->setValue('agegroup_id', 'request', $this->item->agegroup_id);
        
        $extended = sportsmanagementHelper::getExtended($this->item->extended, 'project');		
		$this->assignRef( 'extended', $extended );
        
        $extendeduser = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'project');		
		$this->assignRef( 'extendeduser', $extendeduser );
        
               
        //$this->assign('cfg_which_media_tool', JComponentHelper::getParams($option)->get('cfg_which_media_tool',0) );
        
        $isNew = $this->item->id == 0;
        if ( $isNew )
        {
            $this->form->setValue('start_date', null, date("Y-m-d"));
            $this->form->setValue('start_time', null, '18:00');
            $this->form->setValue('admin', null, $user->id);
            $this->form->setValue('editor', null, $user->id);
            $user->id ;
        }
        
        $this->assign( 'checkextrafields', sportsmanagementHelper::checkUserExtraFields() );
        if ( $this->checkextrafields )
        {
            $lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($this->item->id);
            //$mainframe->enqueueMessage(JText::_('view -> '.'<pre>'.print_r($lists['ext_fields'],true).'</pre>' ),'');
        }
        
        $this->form->setValue('fav_team', null, explode(',',$this->item->fav_team) );
        
        $this->assignRef('lists',$lists);
 
		// Set the toolbar
		$this->addToolBar();
 
		// Display the template
		parent::display($tpl);
 
		// Set the document
		$this->setDocument();
	}
	
	
    
	/**
	 * sportsmanagementViewProject::_displayPanel()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function _displayPanel($tpl)
	{
	$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
	$uri = JFactory::getURI();
	$user = JFactory::getUser();
    $starttime = microtime(); 
           
	$this->project = $this->get('Item');
    
    if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
    
    
       
	$iProjectDivisionsCount = 0;
	$mdlProjectDivisions = JModel::getInstance("divisions", "sportsmanagementModel");
	$iProjectDivisionsCount = $mdlProjectDivisions->getProjectDivisionsCount($this->project->id);
	
    if ( $this->project->project_art_id != 3 )
    {	
	$iProjectPositionsCount = 0;
	$mdlProjectPositions = JModel::getInstance("Projectpositions", "sportsmanagementModel");
	$iProjectPositionsCount = $mdlProjectPositions->getProjectPositionsCount($this->project->id);
	}
    	
	$iProjectRefereesCount = 0;
	$mdlProjectReferees = JModel::getInstance("Projectreferees", "sportsmanagementModel");
	$iProjectRefereesCount = $mdlProjectReferees->getProjectRefereesCount($this->project->id);
		
	$iProjectTeamsCount = 0;
	$mdlProjecteams = JModel::getInstance("Projectteams", "sportsmanagementModel");
	$iProjectTeamsCount = $mdlProjecteams->getProjectTeamsCount($this->project->id);
		
	$iMatchDaysCount = 0;
	$mdlRounds = JModel::getInstance("Rounds", "sportsmanagementModel");
	$iMatchDaysCount = $mdlRounds->getRoundsCount($this->project->id);
		
	$this->assignRef('project',$this->project);
	$this->assignRef('count_projectdivisions',$iProjectDivisionsCount);
	$this->assignRef('count_projectpositions',$iProjectPositionsCount);
	$this->assignRef('count_projectreferees', $iProjectRefereesCount);
	$this->assignRef('count_projectteams', $iProjectTeamsCount );
	$this->assignRef('count_matchdays', $iMatchDaysCount);  
    
    // store the variable that we would like to keep for next time
    // function syntax is setUserState( $key, $value );
    $mainframe->setUserState( "$option.pid", $this->project->id);
    $mainframe->setUserState( "$option.season_id", $this->project->season_id);  
    $mainframe->setUserState( "$option.project_art_id", $this->project->project_art_id);
    $mainframe->setUserState( "$option.sports_type_id", $this->project->sports_type_id);  
    
    
    //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r(JComponentHelper::getParams($option)->get('which_article_component'),true).'</pre>'),'Notice');
    
    $bar = JToolBar::getInstance('toolbar');
    
    switch ( JComponentHelper::getParams($option)->get('which_article_component') )
    {
        case 'com_content':
        $bar->appendButton('Link', 'featured', 'Kategorie', 'index.php?option=com_categories&view=categories&extension=com_content');
        break;
        case 'com_k2':
        $bar->appendButton('Link', 'featured', 'Kategorie', 'index.php?option=com_k2&view=categories');
        break;
    }
    
        
    JToolBarHelper::divider();
	sportsmanagementHelper::ToolbarButtonOnlineHelp();
	JToolBarHelper::preferences(JRequest::getCmd('option'));
           
    parent::display($tpl);   
    }
       
    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
	// Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
	   $option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
    
    
    JRequest::setVar('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;
		$canDo = sportsmanagementHelper::getActions($this->item->id);
		JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_ADD_NEW') : JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_EDIT'), 'project');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			//$this->project->name = $this->item->name ;
            // For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('project.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('project.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('project.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('project.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			$mainframe->setUserState( "$option.pid", $this->item->id);
            
            if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('project.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('project.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('project.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom('project.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('project.cancel', 'JTOOLBAR_CLOSE');
		}
        
        $bar = JToolBar::getInstance('toolbar');
        switch ( JComponentHelper::getParams($option)->get('which_article_component') )
    {
        case 'com_content':
        $bar->appendButton('Link', 'featured', 'Kategorie', 'index.php?option=com_categories&view=categories&extension=com_content');
        break;
        case 'com_k2':
        $bar->appendButton('Link', 'featured', 'Kategorie', 'index.php?option=com_k2&view=categories');
        break;
    }
        
        JToolBarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
		JToolBarHelper::preferences(JRequest::getCmd('option'));
	}
    
    /**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_SPORTSMANAGEMENT_ADMINISTRATION'));
	}
    
}
?>
