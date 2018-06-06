<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage projectteams
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewprojectteams
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewprojectteams extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewprojectteams::init()
	 * 
	 * @return void
	 */
	public function init ()
	{

		$this->state = $this->get('State'); 
		$this->sortDirection = $this->state->get('list.direction');
		$this->sortColumn = $this->state->get('list.ordering');
        
        $this->division = $this->jinput->request->get('division', 0, 'INT');
		$this->project_id = $this->jinput->request->get('pid', 0, 'INT');
		if ( !$this->project_id )
		{
			$this->project_id = $this->app->getUserState( "$this->option.pid", '0' );
		}
       
		$mdlProject = JModelLegacy::getInstance('Project', 'sportsmanagementModel');
		$project = $mdlProject->getProject($this->project_id);
       
		$this->project_art_id = $project->project_art_id;
		$this->season_id = $project->season_id;
		$this->sports_type_id = $project->sports_type_id;
		
		$this->app->setUserState( "$this->option.pid", $project->id );
		$this->app->setUserState( "$this->option.season_id", $project->season_id );
		$this->app->setUserState( "$this->option.project_art_id", $project->project_art_id );
		$this->app->setUserState( "$this->option.sports_type_id", $project->sports_type_id );
        
		$starttime = microtime(); 
		$items = $this->get('Items');
        
		if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
		{
			$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
		}
        
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        $table = JTable::getInstance('projectteam', 'sportsmanagementTable');
		$this->table = $table;
        
		if ( $this->project_art_id == 3 )
		{
			$filter_order = $this->app->getUserStateFromRequest($this->option.'.'.$this->model->_identifier.'.tl_filter_order', 'filter_order', 't.lastname', 'cmd');
		} 
		else
		{
			$filter_order = $this->app->getUserStateFromRequest($this->option.'.'.$this->model->_identifier.'.tl_filter_order', 'filter_order', 't.name', 'cmd');
		}
		
		
		$mdlDivisions = JModelLegacy::getInstance("divisions", "sportsmanagementModel");
        $projectdivisions = array();
		$projectdivisions = $mdlDivisions->getDivisions($this->project_id);
        
        
		$divisionsList[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION'));
		
		if ($projectdivisions)
		{ 
			$projectdivisions = array_merge($divisionsList,$projectdivisions);
		}
		
		$lists['divisions'] = $projectdivisions;
        
        //build the html select list for project assigned teams
		$ress = array();
		$res1 = array();
		$notusedteams = array();

		if ($ress = $this->model->getProjectTeams($this->project_id, FALSE))
		{
			$teamslist = array();
			foreach($ress as $res)
			{
				if(empty($res1->info))
				{
					$project_teamslist[] = JHtmlSelect::option($res->season_team_id, $res->text);
				}
				else
				{
					$project_teamslist[] = JHtmlSelect::option($res->season_team_id, $res->text.' ('.$res->info.')');
				}
			}

			$lists['project_teams'] = JHtmlSelect::genericlist($project_teamslist, 'project_teamslist[]', 
					' style="width:250px; height:300px;" class="inputbox" multiple="true" size="'.min(30, count($ress)).'"', 
					'value', 
					'text');
		}
		else
		{
			$lists['project_teams'] = '<select name="project_teamslist[]" id="project_teamslist" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
		}

		if ($ress1 = $this->model->getTeams())
		{
			if ($ress = $this->model->getProjectTeams($this->project_id,FALSE))
			{
				foreach ($ress1 as $res1)
				{
					$used = 0;
					foreach ($ress as $res)
					{
						if ($res1->value == $res->season_team_id)
                        {
                            $used = 1;
                        }
					}

					if ($used == 0 && !empty($res1->info)){
						$notusedteams[] = JHtmlSelect::option($res1->value, $res1->text.' ('.$res1->info.')');
					}
					elseif($used == 0 && empty($res1->info))
					{
						$notusedteams[] = JHtmlSelect::option($res1->value, $res1->text);
					}
				}
			}
			else
			{
				foreach ($ress1 as $res1)
				{
					if(empty($res1->info))
					{
						$notusedteams[] = JHtmlSelect::option($res1->value, $res1->text);
					}
					else
					{
						$notusedteams[] = JHtmlSelect::option($res1->value, $res1->text.' ('.$res1->info.')');
					}
				}
			}
		}
		else
		{
		//JError::raiseWarning('ERROR_CODE','<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ADD_TEAM').'<br /><br />');
		$this->app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ADD_TEAM'),'Notice');
		}

		//build the html select list for teams
		if (count($notusedteams) > 0)
		{
			$lists['teams'] = JHtmlSelect::genericlist( $notusedteams, 
				'teamslist[]', 
				' style="width:250px; height:300px;" class="inputbox" multiple="true" size="'.min(30, count($notusedteams)).'"', 
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
        
        //build the html options for nation
		$nation[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ($res = JSMCountries::getCountryOptions())
		{
			$nation = array_merge($nation, $res);
			$this->search_nation = $res;
		}
		
		$lists['nation'] = $nation;
		$lists['nationpt'] = JHtmlSelect::genericlist(	$nation,
				'filter_search_nation',
				'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
				'value',
				'text',
				$this->state->get('filter.search_nation'));
        
        if ( JComponentHelper::getParams($this->option)->get('show_option_projectteams_quickadd',0) )
        {
        $lists['country_teams'] = $this->model->getCountryTeams();
        $lists['country_teams_picture'] = $this->model->getCountryTeamsPicture();
        }
        
        //build the html select list for all teams
		$allTeams = array();
		$all_teams[] = JHTML::_( 'select.option', '0', JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM' ) );
		if( $allTeams = $this->model->getAllTeams($this->project_id) ) 
    {
			$all_teams=array_merge($all_teams,$allTeams);
		}
		$lists['all_teams']=$all_teams;
		unset($all_teams);
        
        $myoptions = array();
		$myoptions[] = JHtml::_( 'select.option', '0', JText::_( 'JNO' ) );
		$myoptions[] = JHtml::_( 'select.option', '1', JText::_( 'JYES' ) );
		$lists['is_in_score'] = $myoptions;
        $lists['use_finally'] = $myoptions;

		$this->config = JFactory::getConfig();
		$this->lists = $lists;
        $this->divisions = $projectdivisions;
		$this->projectteam = $items;
		$this->pagination = $pagination;
        $this->project = $project;
        $this->project_art_id = $this->project_art_id;
        $this->lists = $lists;
       
        switch ( $this->getLayout() )
        {
        case 'editlist';
        case 'editlist_3';
        case 'editlist_4';
        $this->setLayout('editlist');
        break;
        case 'changeteams';
        case 'changeteams_3';
        case 'changeteams_4';
        $this->setLayout('changeteams');
        break;
        }
		
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.7
	 */
	protected function addToolbar()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');

	$app->setUserState( "$option.pid", $this->project_id );
        $app->setUserState( "$option.season_id", $this->season_id );
        $app->setUserState( "$option.project_art_id", $this->project_art_id );
        $app->setUserState( "$option.sports_type_id", $this->sports_type_id );
        
        // Set toolbar items for the page
        if ( $this->project_art_id != 3 )
        {
            $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_TITLE');
        }
        else
        {
            $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTPERSONS_TITLE');
        }
        
        JToolbarHelper::custom('projectteams.setseasonid', 'purge.png', 'purge_f2.png', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_SEASON_ID'), true);
		JToolbarHelper::custom('projectteams.matchgroups', 'purge.png', 'purge_f2.png', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_CHANGE_MATCH_GROUPS'), true);
        JToolbarHelper::deleteList('', 'projectteams.delete');

		JToolbarHelper::apply('projectteams.saveshort');
        sportsmanagementHelper::ToolbarButton('changeteams', 'move', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_CHANGE_TEAMS'));
		sportsmanagementHelper::ToolbarButton('editlist', 'upload', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_ASSIGN'));
        JToolbarHelper::custom('projectteam.copy', 'copy', 'copy', JText::_('JTOOLBAR_DUPLICATE'), true);
		JToolbarHelper::checkin('projectteams.checkin');
        
        JToolbarHelper::publish('projectteams.use_table_yes', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_USE_TABLE_YES', true);
        JToolbarHelper::unpublish('projectteams.use_table_no', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_USE_TABLE_NO', true);
        
        JToolbarHelper::publish('projectteams.use_table_points_yes', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_USE_TABLE_POINTS_YES', true);
        JToolbarHelper::unpublish('projectteams.use_table_points_no', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_USE_TABLE_POINTS_NO', true);
            
        parent::addToolbar();  

	}
}
?>
