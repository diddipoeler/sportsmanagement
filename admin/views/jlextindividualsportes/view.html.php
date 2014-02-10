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
 * sportsmanagementViewjlextindividualsportes
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewjlextindividualsportes extends JView
{
	function display($tpl=null)
	{
		$mainframe = JFactory::getApplication();

		if ($this->getLayout()=='default')
		{
			$this->_displayDefault($tpl);
			return;
		}

		parent::display($tpl);
	}

	function _displayDefault($tpl)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $model = $this->getModel();
		$uri = JFactory::getURI();

    $cid = JRequest::getVar('cid', null, 'request', 'array');
    
		$filter_state		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'mc_filter_state',	'filter_state', 	'', 'word');
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'mc_filter_order',	'filter_order', 	'mc.match_number', 'cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'mc_filter_order_Dir','filter_order_Dir', '', 'word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'mc_search', 'search',					'', 'string');
		$search_mode		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'mc_search_mode',		'search_mode',		'', 'string');
		$division			= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'mc_division',		'division',			'',	'string');
		
        $project_id			= $mainframe->getUserState( "$option.pid", '0' );
		$match_id		= JRequest::getvar('id', 0);
        $rid		= JRequest::getvar('rid', 0);
		$projectteam1_id		= JRequest::getvar('team1', 0);
		$projectteam2_id		= JRequest::getvar('team2', 0);
        
        $matches = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        $mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
	    $projectws = $mdlProject->getProject($project_id);
        $mdlRound = JModel::getInstance("Round", "sportsmanagementModel");
		$roundws = $mdlRound->getRound($rid);
        
        $teams[]=JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM_PLAYER'));
        if ($projectteams = $model->getPlayer($projectteam1_id,$project_id))
      {
				$teams=array_merge($teams,$projectteams);
			}
			$lists['homeplayer'] = $teams;
			unset($teams);
            
            
            
         $teams[]=JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM_PLAYER'));
         if ($projectteams = $model->getPlayer($projectteam2_id,$project_id))
      {
				$teams=array_merge($teams,$projectteams);
			}

			$lists['awayplayer'] = $teams;
			unset($teams);    
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' ' .  ' lists<br><pre>'.print_r($lists,true).'</pre>'),'');
        }
        
        $this->assignRef('matches',$matches);
        $this->assignRef('pagination',$pagination);
        $this->assign('request_url',$uri->toString());
        
        $this->assign('ProjectTeams',$model->getProjectTeams($project_id));
        
        $this->assign('match_id',$match_id);
        $this->assign('rid',$rid);
        
        $this->assign('projectteam1_id',$projectteam1_id);
        $this->assign('projectteam2_id',$projectteam2_id);
        
        $this->assignRef('projectws',$projectws);
        $this->assignRef('roundws',$roundws);
        $this->assign('getHomePlayer',$model->getPlayer($projectteam1_id,$project_id));
        $this->assign('getAwayPlayer',$model->getPlayer($projectteam2_id,$project_id));
        
        $this->assignRef('lists',$lists);





/*		
		$search				= JString::strtolower($search);

		$matches	=& $this->get('Data');
		$total		=& $this->get('Total');
		$pagination	=& $this->get('Pagination');
		$model		=& $this->getModel();

		// state filter
		$lists['state']=JHTML::_('grid.state',$filter_state);

		// table ordering
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_order;

		// search filter
		$lists['search']=$search;
		$lists['search_mode']=$search_mode;

		$projectws =& $this->get('Data','projectws');
		
    $projectws->projectteam1_id	= $projectteam1_id;
 		$projectws->projectteam2_id	= $projectteam2_id;
 		$projectws->match_id	= $match_id;
		
		$sporttype =& $model->getSportType($projectws->sports_type_id);
		$mainframe->setUserState($option.'game_parts',$projectws->game_parts);
		
// echo 'projectws<br><pre>';
// print_r($projectws);
// echo '</pre>';

		$roundws =& $this->get('Data','roundws');

		//build the html options for teams
		foreach ($matches as $row)
		{
			$teams[]=JHTML::_('select.option','0',JText::_('JL_GLOBAL_SELECT_TEAM_PLAYER'));
			$divhomeid = 0;
			//apply the filter only if both teams are from the same division
			//teams are not from the same division in tournament mode with divisions
			
            
            
			if ($projectteams =& $model->getHomePlayer())
      {
				$teams=array_merge($teams,$projectteams);
			}
			$lists['homeplayer'] = $teams;
			unset($teams);
			
      $teams[]=JHTML::_('select.option','0',JText::_('JL_GLOBAL_SELECT_TEAM_PLAYER'));
			if ($projectteams =& $model->getAwayPlayer())
      {
				$teams=array_merge($teams,$projectteams);
			}
			$lists['awayplayer'] = $teams;
			unset($teams);
			
		}
		//build the html selectlist for rounds
		$model =& $this->getModel('projectws');
		$ress =& JoomleagueHelper::getRoundsOptions($model->_id);

		foreach ($ress as $res)
		{
			$datum=JHTML::_('date',$res->round_date_first,'%d.%m.%Y').' - '.JHTML::_('date',$res->round_date_last,'%d.%m.%Y');
			$project_roundslist[]=JHTML::_('select.option',$res->id,sprintf("%s (%s)",$res->name,$datum));
		}
		$lists['project_rounds']=JHTML::_(	'select.genericList',$project_roundslist,'rid[]',
											'class="inputbox" ' .
											'onChange="document.getElementById(\'short_act\').value=\'rounds\';' .
											'document.roundForm.submit();" ',
											'value','text',$roundws->id);

		$lists['project_rounds2']=JHTML::_('select.genericList',$project_roundslist,'rid','class="inputbox" ','value','text',$roundws->id);

		//build the html selectlist for matches
		$overall_config = $model->getTemplateConfig('overall');
		if ((isset($overall_config['use_jl_substitution']) && $overall_config['use_jl_substitution']) ||
			(isset($overall_config['use_jl_events']) && $overall_config['use_jl_events']))
		{
 			$match_list=array();
			$mdd[]=JHTML::_('select.option','0',JText::_('JL_GLOBAL_SELECT_MATCH'));

			foreach ($matches as $row)
			{
				$mdd[]=JHTML::_('select.option','index3.php?option=com_joomleague&controller=match&task=editEvents&cid[0]='.$row->id,$row->team1.'-'.$row->team2);
			}
			$RosterEventMessage=(isset($overall_config['use_jl_substitution']) && $overall_config['use_jl_substitution']) ? JText::_('JL_ADMIN_MATCHES_LINEUP') : '';
			if (isset($overall_config['use_jl_events']) && $overall_config['use_jl_events'])
			{
				if (isset($overall_config['use_jl_events']) && $overall_config['use_jl_substitution']){$RosterEventMessage .= ' / ';}
				$RosterEventMessage .= JText::_('JL_ADMIN_MATCHES_EVENTS');
			}
			$RosterEventMessage .= ($RosterEventMessage != '') ? ':' : '';
			$lists['RosterEventMessage']=$RosterEventMessage;

			$lists['round_matches']=JHTML::_(	'select.genericList',$mdd,'mdd',
												'id="mdd" class="inputbox" onchange="jl_load_new_match_events(this,\'eventscontainer\')"',
												'value','text','0');
		}

		//build the html options for extratime
		$match_result_type[]=JHTMLSelect::option('0',JText::_('JL_ADMIN_MATCHES_RT'));
		$match_result_type[]=JHTMLSelect::option('1',JText::_('JL_ADMIN_MATCHES_OT'));
		$match_result_type[]=JHTMLSelect::option('2',JText::_('JL_ADMIN_MATCHES_SO'));
		$lists['match_result_type']=$match_result_type;
		unset($match_result_type);

		//build the html options for massadd create type
		$createTypes=array(	0 => JText::_('JL_ADMIN_MATCHES_MASSADD'),
							1 => JText::_('JL_ADMIN_MATCHES_MASSADD_1'),
							2 => JText::_('JL_ADMIN_MATCHES_MASSADD_2')
							);
		$ctOptions=array();
		foreach($createTypes AS $key => $value){$ctOptions[]=JHTMLSelect::option($key,$value);}
		$lists['createTypes']=JHTMLSelect::genericlist($ctOptions,'ct[]','class="inputbox" onchange="javascript:displayTypeView();"','value','text',1,'ct');
		unset($createTypes);

		// build the html radio for adding into one round / all rounds
		$createYesNo=array(0 => JText::_('JL_GLOBAL_NO'),1 => JText::_('JL_GLOBAL_YES'));
		$ynOptions=array();
		foreach($createYesNo AS $key => $value){$ynOptions[]=JHTMLSelect::option($key,$value);}
		$lists['addToRound']=JHTMLSelect::radiolist($ynOptions,'addToRound','class="inputbox"','value','text',0);

		// build the html radio for auto publish new matches
		$lists['autoPublish']=JHTMLSelect::radiolist($ynOptions,'autoPublish','class="inputbox"','value','text',0);
		//build the html options for divisions
		$divisions[]=JHTMLSelect::option('0',JText::_('JL_GLOBAL_SELECT_DIVISION'));
		$mdlDivisions = JModel::getInstance("divisions", "JoomLeagueModel");
		if ($res =& $mdlDivisions->getDivisions($project_id)){
			$divisions=array_merge($divisions,$res);
		}
		$lists['divisions']=$divisions;
		unset($divisions);
		$this->assignRef('division',$division);
		
		
		
		
		$this->assignRef('user',JFactory::getUser());
		$this->assignRef('lists',$lists);
		$this->assignRef('matches',$matches);
		$this->assignRef('sporttype',$sporttype);
		$this->assignRef('ress',$ress);
		$this->assignRef('projectws',$projectws);
		$this->assignRef('roundws',$roundws);
		$this->assignRef('pagination',$pagination);
		$this->assignRef('request_url',$uri->toString());

*/



		parent::display($tpl);
	}

}
?>