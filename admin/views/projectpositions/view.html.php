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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');




/**
 * sportsmanagementViewprojectpositions
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewprojectpositions extends sportsmanagementView
{

	
	/**
	 * sportsmanagementViewprojectpositions::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		$app		= JFactory::getApplication();
		$jinput		= $app->input;
		$option		= $jinput->getCmd('option');
		$uri		= JFactory::getURI();
		$model		= $this->getModel();
		$starttime	= microtime();
		$tpl 		= '';
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'getLayout<br><pre>'.print_r($this->getLayout(),true).'</pre>'),'');
        
		if ( $this->getLayout() == 'editlist' || $this->getLayout() == 'editlist_3' )
		{
			$this->_displayEditlist($tpl);
			return;
		}

//		$filter_order		= $app->getUserStateFromRequest($option.'.'.$model->_identifier.'.po_filter_order','filter_order','po.ordering','cmd');
//		$filter_order_Dir	= $app->getUserStateFromRequest($option.'.'.$model->_identifier.'.po_filter_order_Dir','filter_order_Dir','','word');
		
		$this->state = $this->get('State'); 
		$this->sortDirection = $this->state->get('list.direction');
		$this->sortColumn = $this->state->get('list.ordering');
        
		$items = $this->get('Items');
        
		if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
		{
			$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
		}
        
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
		$table = JTable::getInstance('projectposition', 'sportsmanagementTable');
		$this->table	= $table;
                
		$this->project_id	= $app->getUserState( "$option.pid", '0' );
        
		$mdlProject	= JModelLegacy::getInstance('Project', 'sportsmanagementModel');
		$project	= $mdlProject->getProject($this->project_id);

//		// table ordering
//		$lists['order_Dir']=$filter_order_Dir;
//		$lists['order']=$filter_order;
		
		$this->user	= JFactory::getUser();
		$this->config	= JFactory::getConfig();
		$this->lists	= $lists;
		$this->positiontool	= $items;
		$this->pagination	= $pagination;
		$this->request_url	= $uri->toString();
		$this->project	= $project;

	}
    
    /**
     * sportsmanagementViewprojectpositions::_displayEditlist()
     * 
     * @param mixed $tpl
     * @return void
     */
    function _displayEditlist($tpl)
	{
		$app		= JFactory::getApplication();
		$jinput		= $app->input;
		$option		= $jinput->getCmd('option');
		$uri		= JFactory::getURI();
		$model		= $this->getModel();
		$document	= JFactory::getDocument();
		$starttime	= microtime();
        
	$items = $this->get('Items');
        
	if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
	{
		$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
	}
        
        //build the html select list for project assigned positions
		$ress = array();
		$res1 = array();
		$notusedpositions = array();
		$project_positionslist = array();
		$lists = array();
	if ( $items )
	{
		foreach($items as $item)
	{
	$project_positionslist[] = JHtml::_('select.option' , $item->id, JText::_($item->name));
		}
		$lists['project_positions'] = JHtml::_(	'select.genericlist', 
								$project_positionslist, 
								'project_positionslist[]', 
								'style="width:250px; height:250px;" class="inputbox" multiple="true" size="'.max(15,count($items)).'"', 
								'value', 
								'text');
		}
		else
	{
		$lists['project_positions'] = '<select name="project_positionslist[]" id="project_positionslist" style="width:250px; height:250px;" class="inputbox" multiple="true" size="10"></select>';
	} 
        
	$this->project_id	= $app->getUserState( "$option.pid", '0' );

	$mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
	$project = $mdlProject->getProject($this->project_id); 
        
	if ($ress1 = $model->getSubPositions($project->sports_type_id))
	{
		if ($ress)
			{
				foreach ($ress1 as $res1)
				{
					if ( !in_array($res1, $ress) )
					{
						$res1->text=JText::_($res1->text);
						$notusedpositions[] = $res1;
					}
				}
			}
			else
			{
				foreach ($ress1 as $res1)
				{
					$res1->text=JText::_($res1->text);
					$notusedpositions[] = $res1;
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
			$lists['positions'] = JHtml::_(	'select.genericlist', 
											$notusedpositions, 
											'positionslist[]', 
											' style="width:250px; height:250px;" class="inputbox" multiple="true" size="'.min(15,count($notusedpositions)).'"', 
											'value', 
											'text');
		}
		else
		{
			$lists['positions'] = '<select name="positionslist[]" id="positionslist" style="width:250px; height:250px;" class="inputbox" multiple="true" size="10"></select>';
		}
                                                  

		
	$document->addScript(JURI::base().'components/com_sportsmanagement/assets/js/sm_functions.js');
	$this->request_url	= $uri->toString();
	$this->user	= JFactory::getUser();
	$this->project	= $project;
	$this->lists	= $lists;
	$this->addToolbar_Editlist();		
		
	$this->setLayout('editlist');
	
	unset ($ress);
	unset ($res1);
	unset ($notusedpositions);
	unset ($project_positionslist);
	unset ($lists);
	
	}
    

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.7
	 */
	protected function addToolbar()
	{
	//// Get a refrence of the page instance in joomla
//        $document = JFactory::getDocument();
//        // Set toolbar items for the page
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
//		// Set toolbar items for the page
	$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_TITLE');
        
	sportsmanagementHelper::ToolbarButton('editlist', 'upload', JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_BUTTON_UN_ASSIGN'));

	parent::addToolbar();

	}
    
    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar_Editlist()
	{ 
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_EDIT_TITLE');
		JToolBarHelper::save('projectposition.save_positionslist');
		JToolBarHelper::cancel('projectposition.cancel',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_CLOSE'));
		parent::addToolbar();
	}
    
}
?>
