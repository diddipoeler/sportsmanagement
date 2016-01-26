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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
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

/**
 * sportsmanagementViewPosition
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementViewPosition extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewPosition::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	public function init ()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$uri = JFactory::getURI();
        $model = $this->getModel();
        $document = JFactory::getDocument();
        $starttime = microtime(); 
        
        if ( JPluginHelper::isEnabled( 'system', 'jqueryeasy' ) )
        {
            $app->enqueueMessage(JText::_('jqueryeasy ist installiert'),'Notice');
            $this->jquery = true;
        }
        else
        {
            $app->enqueueMessage(JText::_('jqueryeasy ist nicht installiert'),'Error');
            $this->jquery = false;
        }
        
        // get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
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
		$parent_id[] = JHtml::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_IS_P_POSITION'));
		$mdlPositions = JModelLegacy::getInstance('Positions', 'sportsmanagementModel');
	    
        if ($res = $mdlPositions->getParentsPositions())
		{
			foreach ($res as $re)
            {
                $re->text = JText::_($re->text);
            }
			$parent_id = array_merge($parent_id,$res);
		}
		
        $lists['parents'] = JHtml::_('select.genericlist', $parent_id, 'parent_id', 'class="inputbox" size="1"', 'value', 'text', $this->item->parent_id);
        
		unset($parent_id);
        
        $mdlEventtypes = JModelLegacy::getInstance('Eventtypes', 'sportsmanagementModel');
        
        //build the html select list for events
		$res = array();
		$res1 = array();
		$notusedevents = array();
        
        // nur wenn die position angelegt ist, hat sie auch events
        if ( $this->item->id )
        {
		if ($res = $mdlEventtypes->getEventsPosition($this->item->id) )
		{
			$lists['position_events'] = JHtml::_(	'select.genericlist',$res,'position_eventslist[]', 
								' style="width:250px; height:300px;" class="inputbox" multiple="true" size="'.max(10,count($res)).'"', 
								'value','text');
		}
		else
		{
			$lists['position_events'] = '<select name="position_eventslist[]" id="position_eventslist" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
		}
        }
        else
        {
            $lists['position_events'] = '<select name="position_eventslist[]" id="position_eventslist" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
        }
        
		$res1 = $mdlEventtypes->getEvents($this->item->sports_type_id);
		if ($res = $mdlEventtypes->getEventsPosition($this->item->id) )
		{
			if ($res1 != "")
			foreach ($res1 as $miores1)
			{
				$used = 0;
				foreach ($res as $miores)
				{
					if ($miores1->text == $miores->text)
					{
						$used = 1;
					}
				}
				if ($used == 0)
				{
					$notusedevents[] = $miores1;
				}
			}
		}
		else
		{
			$notusedevents=$res1;
		}

    
	if ( $this->item->id )
        {
        //build the html select list for events
		if (($notusedevents) && (count($notusedevents) > 0))
		{
			$lists['events'] = JHtml::_(	'select.genericlist', $notusedevents, 'eventslist[]', 
											' style="width:250px; height:300px;" class="inputbox" multiple="true" size="'.max(10,count($notusedevents)).'"', 
											'value', 'text');
		}
			else
			{
				$lists['events'] = '<select name="eventslist[]" id="eventslist" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
			}
        }
		
		else
        {
            $lists['events'] = JHtml::_(	'select.genericlist', $res1, 'eventslist[]', 
											' style="width:250px; height:300px;" class="inputbox" multiple="true" size="'.max(10, count($res1)).'"', 
											'value', 'text');
        }

		unset($res);
		unset($res1);
		unset($notusedevents);
        
        // position statistics
        $mdlStatistics = JModelLegacy::getInstance('Statistics', 'sportsmanagementModel');
        
		$position_stats = $mdlStatistics->getPositionStatsOptions($this->item->id);
		
        if (!empty($position_stats)) 
        {
        $lists['position_statistic'] = JHtml::_(	'select.genericlist',$position_stats,'position_statistic[]', 
													' style="width:250px; height:300px;" class="inputbox" id="position_statistic" multiple="true" size="'.max(10, count($position_stats)).'"', 
													'value','text');
        }
        else
		{
			$lists['position_statistic'] = '<select name="position_statistic[]" id="position_statistic" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
		}
        
        $available_stats = $mdlStatistics->getAvailablePositionStatsOptions($this->item->id);
        if (!empty($available_stats)) 
        {
			$lists['statistic'] = JHtml::_(	'select.genericlist', $available_stats, 'statistic[]', 
						' style="width:250px; height:300px;" class="inputbox" id="statistic" multiple="true" size="'.max(10, count($available_stats)).'"', 
						'value','text');
        }                
        else
		{
		      $lists['statistic'] = '<select name="statistic[]" id="statistic" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
		}
                        
        $document->addScript(JURI::base().'components/com_sportsmanagement/assets/js/sm_functions.js');
        
        $this->lists	= $lists;
        //$this->assign('cfg_which_media_tool', JComponentHelper::getParams($option)->get('cfg_which_media_tool',0) );
 
	
	}
 
	
	/**
	 * sportsmanagementViewPosition::addToolBar()
	 * 
	 * @return void
	 */
	protected function addToolBar() 
	{
	
		$jinput = JFactory::getApplication()->input;
        $jinput->set('hidemainmenu', true);
        
        $isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_POSITION_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_POSITION_NEW');
        $this->icon = 'position';
        

		parent::addToolbar();
        
	}
    

}
