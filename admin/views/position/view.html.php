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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 

/**
 * sportsmanagementViewPosition
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementViewPosition extends JView
{
	
	/**
	 * sportsmanagementViewPosition::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	public function display($tpl = null) 
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$uri = JFactory::getURI();
        $model = $this->getModel();
        $document = JFactory::getDocument();
        $starttime = microtime(); 
        
        if ( JPluginHelper::isEnabled( 'system', 'jqueryeasy' ) )
        {
            $mainframe->enqueueMessage(JText::_('jqueryeasy ist installiert'),'Notice');
            $this->jquery = true;
        }
        else
        {
            $mainframe->enqueueMessage(JText::_('jqueryeasy ist nicht installiert'),'Error');
            $this->jquery = false;
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
        
        //build the html options for parent position
		$parent_id[]=JHtml::_('select.option','',JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_IS_P_POSITION'));
		$mdlPositions = JModel::getInstance("Positions", "sportsmanagementModel");
	    
        if ($res = $mdlPositions->getParentsPositions())
		{
			foreach ($res as $re)
            {
                $re->text = JText::_($re->text);
            }
			$parent_id = array_merge($parent_id,$res);
		}
		$lists['parents'] = $parent_id;
        
		unset($parent_id);
        
        $mdlEventtypes = JModel::getInstance("Eventtypes", "sportsmanagementModel");
        
        //build the html select list for events
		$res = array();
		$res1 = array();
		$notusedevents = array();
		if ($res = $mdlEventtypes->getEventsPosition($this->item->id))
		{
			$lists['position_events']=JHtml::_(	'select.genericlist',$res,'position_eventslist[]',
								' style="width:250px; height:300px;" class="inputbox" multiple="true" size="'.max(10,count($res)).'"',
								'value','text');
		}
		else
		{
			$lists['position_events']='<select name="position_eventslist[]" id="position_eventslist" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
		}
		$res1 = $mdlEventtypes->getEvents($this->item->sports_type_id);
		if ($res = $mdlEventtypes->getEventsPosition($this->item->id))
		{
			if($res1!="")
			foreach ($res1 as $miores1)
			{
				$used=0;
				foreach ($res as $miores)
				{
					if ($miores1->text == $miores->text){$used=1;}
				}
				if ($used == 0){$notusedevents[]=$miores1;}
			}
		}
		else
		{
			$notusedevents=$res1;
		}

		//build the html select list for events
		if (($notusedevents) && (count($notusedevents) > 0))
		{
			$lists['events']=JHtml::_(	'select.genericlist',$notusedevents,'eventslist[]',
							' style="width:250px; height:300px;" class="inputbox" multiple="true" size="'.max(10,count($notusedevents)).'"',
							'value','text');
		}
		else
		{
			$lists['events']='<select name="eventslist[]" id="eventslist" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
		}
		unset($res);
		unset($res1);
		unset($notusedevents);
        
        // position statistics
        $mdlStatistics = JModel::getInstance("Statistics", "sportsmanagementModel");
		$position_stats = $mdlStatistics->getPositionStatsOptions($this->item->id);
		$lists['position_statistic']=JHtml::_(	'select.genericlist',$position_stats,'position_statistic[]',
							' style="width:250px; height:300px;" class="inputbox" id="position_statistic" multiple="true" size="'.max(10,count($position_stats)).'"',
							'value','text');
        $available_stats = $mdlStatistics->getAvailablePositionStatsOptions($this->item->id);
		$lists['statistic']=JHtml::_(	'select.genericlist',$available_stats,'statistic[]',
						' style="width:250px; height:300px;" class="inputbox" id="statistic" multiple="true" size="'.max(10,count($available_stats)).'"',
						'value','text');
                        
                        
        $document->addScript(JURI::base().'components/com_sportsmanagement/assets/js/sm_functions.js');
        
        $this->assignRef('lists',$lists);
        //$this->assign('cfg_which_media_tool', JComponentHelper::getParams($option)->get('cfg_which_media_tool',0) );
 
		// Set the toolbar
		$this->addToolBar();
 
		// Display the template
		parent::display($tpl);
 
		// Set the document
		$this->setDocument();
	}
 
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
	// Get a refrence of the page instance in joomla
	$document =& JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        // Set toolbar items for the page
		JRequest::setVar('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;
		$canDo = sportsmanagementHelper::getActions($this->item->id);
		JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_POSITION_NEW') : JText::_('COM_SPORTSMANAGEMENT_POSITION_EDIT'), 'position');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('position.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('position.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('position.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('position.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('position.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('position.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('position.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom('position.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('position.cancel', 'JTOOLBAR_CLOSE');
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
		$isNew = $this->item->id == 0;
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('COM_SPORTSMANAGEMENT_POSITION_NEW') : JText::_('COM_SPORTSMANAGEMENT_POSITION_EDIT'));
		$document->addScript(JURI::root() . $this->script);
		$document->addScript(JURI::root() . "/administrator/components/com_sportsmanagement/views/sportsmanagement/submitbutton.js");
		JText::script('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE');
	}
}
