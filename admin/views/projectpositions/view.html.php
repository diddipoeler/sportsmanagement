<?php
/**
 * @copyright	Copyright (C) 2006-2013 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the Joomleague component
 *
 * @static
 * @package	JoomLeague
 * @since	0.1
 */
class sportsmanagementViewprojectpositions extends JView
{

	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$uri = JFactory::getURI();
        
        if ($this->getLayout()=='editlist')
		{
			$this->_displayEditlist($tpl);
			return;
		}

		$filter_order		= $mainframe->getUserStateFromRequest($option.'v_filter_order',		'filter_order',		'v.ordering',	'cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'v_filter_order_Dir',	'filter_order_Dir',	'',				'word');
		$search				= $mainframe->getUserStateFromRequest($option.'v_search',			'search',			'',				'string');
		$search_mode		= $mainframe->getUserStateFromRequest($option.'t_search_mode',		'search_mode',		'',				'string');
		$search				= JString::strtolower($search);

		$items =& $this->get('Items');
		$total =& $this->get('Total');
		$pagination =& $this->get('Pagination');
        
        //$project_id	= JRequest::getVar('pid');
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        
        $mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);

		// table ordering
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_order;

		// search filter
		$lists['search']=$search;
		$lists['search_mode']=$search_mode;

		$this->assignRef('user',JFactory::getUser());
		$this->assignRef('config',JFactory::getConfig());
		$this->assignRef('lists',$lists);
		$this->assignRef('positiontool',$items);
		$this->assignRef('pagination',$pagination);
		$this->assignRef('request_url',$uri->toString());
        $this->assignRef('project',$project);
		$this->addToolbar();
		parent::display($tpl);
	}
    
    function _displayEditlist($tpl)
	{
		$mainframe = JFactory::getApplication();
		$uri = JFactory::getURI();
		$model = $this->getModel();

/*
		$projectws =& $this->get('Data','projectws');

		//build the html select list for project assigned positions
		$ress=array();
		$res1=array();
		$notusedpositions=array();

		if ($ress =& $model->getProjectPositions())
		{ // select all already assigned positions to the project
			foreach($ress as $res){$project_positionslist[]=JHTML::_('select.option',$res->value,JText::_($res->text));}
			$lists['project_positions']=JHTML::_(	'select.genericlist',
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
			JError::raiseWarning('ERROR_CODE','<br />'.JText::_('COM_JOOMLEAGUE_ADMIN_P_POSITION_ASSIGN_POSITIONS_FIRST').'<br /><br />');
		}

		//build the html select list for positions
		if (count ($notusedpositions) > 0)
		{
			$lists['positions']=JHTML::_(	'select.genericlist',
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
		// Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_TITLE'),'Positions');

		JToolBarHelper::custom('projectposition.assign','upload.png','upload_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_BUTTON_UN_ASSIGN'),false);
		JToolBarHelper::divider();

		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        sportsmanagementHelper::ToolbarButton('editlist');

	}
    
    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar_Editlist()
	{ 
		JToolBarHelper::title(JText::_('COM_JOOMLEAGUE_ADMIN_P_POSITION_EDIT_TITLE'),'Positions');
		JToolBarHelper::save('projectposition.save_positionslist');
		JToolBarHelper::cancel('projectposition.cancel',JText::_('COM_JOOMLEAGUE_GLOBAL_CLOSE'));
		//JLToolBarHelper::onlinehelp();
	}
    
}
?>