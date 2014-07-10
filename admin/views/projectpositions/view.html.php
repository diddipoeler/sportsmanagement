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


/**
 * sportsmanagementViewprojectpositions
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewprojectpositions extends JViewLegacy
{

	/**
	 * sportsmanagementViewprojectpositions::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$uri = JFactory::getURI();
        $model	= $this->getModel();
        $starttime = microtime(); 
        
        if ($this->getLayout()=='editlist')
		{
			$this->_displayEditlist($tpl);
			return;
		}

//		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.po_filter_order','filter_order','po.ordering','cmd');
//		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.po_filter_order_Dir','filter_order_Dir','','word');
		
        $this->state = $this->get('State'); 
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');
        
		$items = $this->get('Items');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
                
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);

//		// table ordering
//		$lists['order_Dir']=$filter_order_Dir;
//		$lists['order']=$filter_order;
		
		$this->assign('user',JFactory::getUser());
		$this->assign('config',JFactory::getConfig());
		$this->assignRef('lists',$lists);
		$this->assignRef('positiontool',$items);
		$this->assignRef('pagination',$pagination);
		$this->assign('request_url',$uri->toString());
        $this->assignRef('project',$project);
		$this->addToolbar();
		parent::display($tpl);
	}
    
    /**
     * sportsmanagementViewprojectpositions::_displayEditlist()
     * 
     * @param mixed $tpl
     * @return void
     */
    function _displayEditlist($tpl)
	{
		$mainframe = JFactory::getApplication();
		$uri = JFactory::getURI();
		$model = $this->getModel();
        $option = JRequest::getCmd('option');
        $document = JFactory::getDocument();
        $starttime = microtime(); 
        
        $items = $this->get('Items');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
        //build the html select list for project assigned positions
		$ress = array();
		$res1 = array();
		$notusedpositions = array();
        
        if ( $items )
        {
        foreach($items as $item)
        {
            $project_positionslist[] = JHtml::_('select.option',$item->id,JText::_($item->name));
        }
        $lists['project_positions']=JHtml::_(	'select.genericlist',
													$project_positionslist,
													'project_positionslist[]',
													'style="width:250px; height:250px;" class="inputbox" multiple="true" size="'.max(15,count($items)).'"',
													'value',
													'text');
        }
        else
        {
            $lists['project_positions']='<select name="project_positionslist[]" id="project_positionslist" style="width:250px; height:250px;" class="inputbox" multiple="true" size="10"></select>';
        } 
        
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id); 
        
        if ($ress1 = $model->getSubPositions($project->sports_type_id))
		{
			if ($ress)
			{
				foreach ($ress1 as $res1)
				{
					if (!in_array($res1,$ress))
					{
						$res1->text=JText::_($res1->text);
						$notusedpositions[]=$res1;
					}
				}
			}
			else
			{
				foreach ($ress1 as $res1)
				{
					$res1->text=JText::_($res1->text);
					$notusedpositions[]=$res1;
				}
			}
		}
		else
		{
			JError::raiseWarning('ERROR_CODE','<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_ASSIGN_POSITIONS_FIRST').'<br /><br />');
		}

		//build the html select list for positions
		if (count ($notusedpositions) > 0)
		{
			$lists['positions']=JHtml::_(	'select.genericlist',
											$notusedpositions,
											'positionslist[]',
											' style="width:250px; height:250px;" class="inputbox" multiple="true" size="'.min(15,count($notusedpositions)).'"',
											'value',
											'text');
		}
		else
		{
			$lists['positions']='<select name="positionslist[]" id="positionslist" style="width:250px; height:250px;" class="inputbox" multiple="true" size="10"></select>';
		}
                                                  

/*
		$projectws =& $this->get('Data','projectws');

		//build the html select list for project assigned positions
		$ress=array();
		$res1=array();
		$notusedpositions=array();

		if ($ress =& $model->getProjectPositions())
		{ // select all already assigned positions to the project
			foreach($ress as $res){$project_positionslist[]=JHtml::_('select.option',$res->value,JText::_($res->text));}
			$lists['project_positions']=JHtml::_(	'select.genericlist',
													$project_positionslist,
													'project_positionslist[]',
													' style="width:250px; height:250px;" class="inputbox" multiple="true" size="'.max(15,count($ress)).'"',
													'value',
													'text');
		}
		else
		{
			$lists['project_positions']='<select name="project_positionslist[]" id="project_positionslist" style="width:250px; height:250px;" class="inputbox" multiple="true" size="10"></select>';
		}

		if ($ress1 =& $model->getSubPositions($projectws->sports_type_id))
		{
			if ($ress)
			{
				foreach ($ress1 as $res1)
				{
					if (!in_array($res1,$ress))
					{
						$res1->text=JText::_($res1->text);
						$notusedpositions[]=$res1;
					}
				}
			}
			else
			{
				foreach ($ress1 as $res1)
				{
					$res1->text=JText::_($res1->text);
					$notusedpositions[]=$res1;
				}
			}
		}
		else
		{
			JError::raiseWarning('ERROR_CODE','<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_ASSIGN_POSITIONS_FIRST').'<br /><br />');
		}

		//build the html select list for positions
		if (count ($notusedpositions) > 0)
		{
			$lists['positions']=JHtml::_(	'select.genericlist',
											$notusedpositions,
											'positionslist[]',
											' style="width:250px; height:250px;" class="inputbox" multiple="true" size="'.min(15,count($notusedpositions)).'"',
											'value',
											'text');
		}
		else
		{
			$lists['positions']='<select name="positionslist[]" id="positionslist" style="width:250px; height:250px;" class="inputbox" multiple="true" size="10"></select>';
		}
		unset($ress);
		unset($ress1);
		unset($notusedpositions);

		$this->assignRef('user',JFactory::getUser());
		$this->assignRef('lists',$lists);
		$this->assignRef('positiontool',$positiontool);
		$this->assignRef('projectws',$projectws);
		$this->assignRef('pagination',$pagination);
		$this->assignRef('request_url',$uri->toString());
*/
		
        $document->addScript(JUri::base().'components/com_sportsmanagement/assets/js/sm_functions.js');
        $this->assign('request_url',$uri->toString());
        $this->assign('user',JFactory::getUser());
        $this->assignRef('project',$project);
        $this->assignRef('lists',$lists);
        $this->addToolbar_Editlist();		
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
        $stylelink = '<link rel="stylesheet" href="'.JUri::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
		// Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_TITLE'),'projectpositions');
        
        sportsmanagementHelper::ToolbarButton('editlist','upload',JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_BUTTON_UN_ASSIGN'));

		//JToolBarHelper::custom('projectposition.assign','upload.png','upload_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_BUTTON_UN_ASSIGN'),false);
		JToolBarHelper::divider();

		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd('option'));

	}
    
    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar_Editlist()
	{ 
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_EDIT_TITLE'),'Positions');
		JToolBarHelper::save('projectposition.save_positionslist');
		JToolBarHelper::cancel('projectposition.cancel',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_CLOSE'));
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
	}
    
}
?>
