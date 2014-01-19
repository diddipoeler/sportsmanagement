<?php
/**
 * @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
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
 * sportsmanagementViewprojectteams
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewprojectteams extends JView
{

	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$uri = JFactory::getURI();
        $model	= $this->getModel();

		
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.tl_filter_order_Dir','filter_order_Dir','','word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.tl_search','search','','string');
		$search_mode		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.tl_search_mode','search_mode','','string');
        $division			= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.tl_division','division','','string');
		$search				= JString::strtolower($search);

		$items = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        $this->project_art_id	= $mainframe->getUserState( "$option.project_art_id", '0' );
        if ( $this->project_art_id == 3 )
        {
            $filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.tl_filter_order','filter_order','t.lastname','cmd');
        } 
        else
        {
            $filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.tl_filter_order','filter_order','t.name','cmd');
        }
        
        $mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        $mdlDivisions = JModel::getInstance("divisions", "sportsmanagementModel");
	    $projectdivisions = $mdlDivisions->getDivisions($this->project_id);
        
        
        $divisionsList[]=JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION'));
        if ($projectdivisions){ $projectdivisions=array_merge($divisionsList,$projectdivisions);}
        $lists['divisions'] = $projectdivisions;
        
        //build the html select list for project assigned teams
		$ress = array();
		$res1 = array();
		$notusedteams = array();

		if ($ress = $model->getProjectTeams($this->project_id))
		{
			$teamslist=array();
			foreach($ress as $res)
			{
				if(empty($res1->info))
				{
					$project_teamslist[] = JHTMLSelect::option($res->value,$res->text);
				}
				else
				{
					$project_teamslist[] = JHTMLSelect::option($res->value,$res->text.' ('.$res->info.')');
				}
			}

			$lists['project_teams'] = JHTMLSelect::genericlist($project_teamslist, 'project_teamslist[]',
																' style="width:250px; height:300px;" class="inputbox" multiple="true" size="'.min(30,count($ress)).'"',
																'value',
																'text');
		}
		else
		{
			$lists['project_teams']= '<select name="project_teamslist[]" id="project_teamslist" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
		}

		if ($ress1 = $model->getTeams())
		{
			if ($ress = $model->getProjectTeams($this->project_id))
			{
				foreach ($ress1 as $res1)
				{
					$used=0;
					foreach ($ress as $res)
					{
						if ($res1->value == $res->value){$used=1;}
					}

					if ($used == 0 && !empty($res1->info)){
						$notusedteams[]=JHTMLSelect::option($res1->value,$res1->text.' ('.$res1->info.')');
					}
					elseif($used == 0 && empty($res1->info))
					{
						$notusedteams[] = JHTMLSelect::option($res1->value,$res1->text);
					}
				}
			}
			else
			{
				foreach ($ress1 as $res1)
				{
					if(empty($res1->info))
					{
						$notusedteams[] = JHTMLSelect::option($res1->value,$res1->text);
					}
					else
					{
						$notusedteams[] = JHTMLSelect::option($res1->value,$res1->text.' ('.$res1->info.')');
					}
				}
			}
		}
		else
		{
			JError::raiseWarning('ERROR_CODE','<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ADD_TEAM').'<br /><br />');
		}

		//build the html select list for teams
		if (count($notusedteams) > 0)
		{
			$lists['teams'] = JHTMLSelect::genericlist( $notusedteams,
														'teamslist[]',
														' style="width:250px; height:300px;" class="inputbox" multiple="true" size="'.min(30,count($notusedteams)).'"',
														'value',
														'text');
		}
		else
		{
			$lists['teams'] = '<select name="teamslist[]" id="teamslist" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
		}

		unset($res);
		unset($res1);
		unset($notusedteams);
        
        
        
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' tpl<br><pre>'.print_r($tpl,true).'</pre>'   ),'');
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' items<br><pre>'.print_r($items,true).'</pre>'   ),'');

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		// search filter
		$lists['search'] = $search;
		$lists['search_mode'] = $search_mode;
        
        $myoptions = array();
		$myoptions[] = JHtml::_( 'select.option', '0', JText::_( 'JNO' ) );
		$myoptions[] = JHtml::_( 'select.option', '1', JText::_( 'JYES' ) );
		$lists['is_in_score'] = $myoptions;
        $lists['use_finally'] = $myoptions;

		$this->assign('user',JFactory::getUser());
		$this->assign('config',JFactory::getConfig());
		$this->assignRef('lists',$lists);
        $this->assignRef('divisions',$projectdivisions);
        $this->assignRef('division',$division);
		$this->assignRef('projectteam',$items);
		$this->assignRef('pagination',$pagination);
		$this->assign('request_url',$uri->toString());
        $this->assignRef('project',$project);
        $this->assignRef('project_art_id',$this->project_art_id);
		$this->addToolbar();
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
        $document->addScript(JURI::base().'components/com_sportsmanagement/assets/js/sm_functions.js');
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
		// Set toolbar items for the page
        if ( $this->project_art_id != 3 )
        {
            JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_TITLE'),'projectteams');
        }
        else
        {
            JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTPERSONS_TITLE'),'projectpersons');
        }
		
        JToolBarHelper::deleteList('', 'projectteam.remove');

		JToolBarHelper::apply('projectteams.saveshort');
		//JToolBarHelper::custom('projectteam.changeteams','move.png','move_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_CHANGE_TEAMS'),false);
        sportsmanagementHelper::ToolbarButton('changeteams','move',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_CHANGE_TEAMS'));
		//JToolBarHelper::custom('projectteam.editlist','upload.png','upload_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_ASSIGN'),false);
		sportsmanagementHelper::ToolbarButton('editlist','upload',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_ASSIGN'));
        JToolBarHelper::custom('projectteam.copy','copy','copy', JText::_('JTOOLBAR_DUPLICATE'), true);
		JToolBarHelper::divider();

		sportsmanagementHelper::ToolbarButtonOnlineHelp();
    JToolBarHelper::preferences(JRequest::getCmd('option'));
    
        

		//JToolBarHelper::onlinehelp();
	}
}
?>
