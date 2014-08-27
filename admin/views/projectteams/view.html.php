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
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$uri = JFactory::getURI();
        $model	= $this->getModel();

		$this->state = $this->get('State'); 
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');
        
        
        $this->project_id = JRequest::getVar('pid');
        if ( !$this->project_id )
        {
        $this->project_id = $mainframe->getUserState( "$option.pid", '0' );
        }
       
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        
        $this->project_art_id = $project->project_art_id;
        $mainframe->setUserState( "$option.pid", $project->id );
        $mainframe->setUserState( "$option.season_id", $project->season_id );
        $mainframe->setUserState( "$option.project_art_id", $project->project_art_id );
        $mainframe->setUserState( "$option.sports_type_id", $project->sports_type_id );
        
        $starttime = microtime(); 
        $items = $this->get('Items');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        if ( $this->project_art_id == 3 )
        {
            $filter_order = $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.tl_filter_order','filter_order','t.lastname','cmd');
        } 
        else
        {
            $filter_order = $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.tl_filter_order','filter_order','t.name','cmd');
        }
        
        
        
        $mdlDivisions = JModelLegacy::getInstance("divisions", "sportsmanagementModel");
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
        
        if ( COM_SPORTSMANAGEMENT_JOOMLAVERSION != '2.5' )
        {
        sportsmanagementHelper::addSubmenu('menu');
        }
		
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
//        $document->addScript(JURI::base().'components/com_sportsmanagement/assets/js/sm_functions.js');
//        // Set toolbar items for the page
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
		// Set toolbar items for the page
        if ( $this->project_art_id != 3 )
        {
            JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_TITLE'),'projectteams');
        }
        else
        {
            JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTPERSONS_TITLE'),'projectpersons');
        }
        
        JToolBarHelper::custom('projectteams.setseasonid','purge.png','purge_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_SET_SEASON_ID'),true);
		JToolBarHelper::custom('projectteams.matchgroups','purge.png','purge_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_CHANGE_MATCH_GROUPS'),true);
        JToolBarHelper::deleteList('', 'projectteams.delete');

		JToolBarHelper::apply('projectteams.saveshort');
        sportsmanagementHelper::ToolbarButton('changeteams','move',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_CHANGE_TEAMS'));
		sportsmanagementHelper::ToolbarButton('editlist','upload',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_ASSIGN'));
        JToolBarHelper::custom('projectteam.copy','copy','copy', JText::_('JTOOLBAR_DUPLICATE'), true);
		
        parent::addToolbar();  

	}
}
?>
