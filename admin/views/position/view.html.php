<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage position
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
		
		
        $starttime = microtime(); 
        
        if ( JPluginHelper::isEnabled( 'system', 'jqueryeasy' ) )
        {
            $this->app->enqueueMessage(JText::_('jqueryeasy ist installiert'),'Notice');
            $this->jquery = true;
        }
        else
        {
            $this->app->enqueueMessage(JText::_('jqueryeasy ist nicht installiert'),'Error');
            $this->jquery = false;
        }
        
        
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		
        
        //build the html options for parent position
        		$parent_id = array();
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
		
	$lists = array();
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
			$notusedevents = $res1;
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
                        
		$this->document->addScript(JURI::base().'components/com_sportsmanagement/assets/js/sm_functions.js');
        
		$this->lists = $lists;
	unset($lists);
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
