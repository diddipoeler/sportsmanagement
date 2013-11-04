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
 * @author	Marco Vaninetti <martizva@tiscali.it>
 * @package	JoomLeague
 * @since	0.1
*/

class sportsmanagementViewMatches extends JView
{
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$uri = JFactory::getURI();
        $model		= $this->getModel();
		$params =& JComponentHelper::getParams( $option );

		$filter_state		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier. '.mc_filter_state',	'filter_state', 	'', 'word');
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier. '.mc_filter_order',	'filter_order', 	'mc.match_number', 'cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier. '.mc_filter_order_Dir','filter_order_Dir', '', 'word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier. '.mc_search', 'search',					'', 'string');
		$search_mode		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier. '.mc_search_mode',		'search_mode',		'', 'string');
		$division			= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier. '.mc_division',		'division',			'',	'string');
		
		$search				= JString::strtolower($search);
        
        $items =& $this->get('Items');
		$total =& $this->get('Total');
		$pagination =& $this->get('Pagination');
        
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        $mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
	    $projectws = $mdlProject->getProject($this->project_id);
        
		$roundws =& $this->get('Data','roundws');
        
        //build the html selectlist for rounds
		$ress = sportsmanagementHelper::getRoundsOptions($this->project_id, 'ASC', true);

		foreach ($ress as $res)
		{
			$datum = sportsmanagementHelper::convertDate($res->round_date_first, 1).' - '.sportsmanagementHelper::convertDate($res->round_date_last, 1);
			$project_roundslist[]=JHTML::_('select.option',$res->id,sprintf("%s (%s)",$res->name,$datum));
		}
		$lists['project_rounds']=JHTML::_(	'select.genericList',$project_roundslist,'rid[]',
				'class="inputbox" ' .
				'onChange="document.getElementById(\'short_act\').value=\'rounds\';' .
				'document.roundForm.submit();" ',
				'value','text',JRequest::getvar('rid', 0));

		$lists['project_rounds2']=JHTML::_('select.genericList',$project_roundslist,'rid','class="inputbox" ','value','text',JRequest::getvar('rid', 0));




/*
		$matches	=& $this->get('Data');
		$total		=& $this->get('Total');
		$pagination	=& $this->get('Pagination');
		

		// state filter
		$lists['state']=JHTML::_('grid.state',$filter_state);

		// table ordering
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_order;

		// search filter
		$lists['search']=$search;
		$lists['search_mode']=$search_mode;

		$projectws =& $this->get('Data','projectws');
		$roundws =& $this->get('Data','roundws');

		//build the html options for teams
		foreach ($matches as $row)
		{
			if ( $row->divhomeid == '' )
            {
                $row->divhomeid = 0;
            }
            if ( $row->divawayid == '' )
            {
                $row->divawayid = 0;
            }
            
            
            $teams[]=JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM'));
			$divhomeid = 0;
			//apply the filter only if both teams are from the same division
			//teams are not from the same division in tournament mode with divisions
			if($row->divhomeid==$row->divawayid) {
				$divhomeid = $row->divhomeid;
			} else {
				$row->divhomeid =0;
				$row->divawayid =0;
			}
			if ($projectteams =& $model->getProjectTeamsOptions($divhomeid)){
				$teams=array_merge($teams,$projectteams);
			}
			$lists['teams_'+$divhomeid] = $teams;
			unset($teams);
		}
        
        
		//build the html selectlist for rounds
		$model = $this->getModel('projectws');
		$ress = sportsmanagementHelper::getRoundsOptions($model->_id, 'ASC', true);

		foreach ($ress as $res)
		{
			$datum = sportsmanagementHelper::convertDate($res->round_date_first, 1).' - '.sportsmanagementHelper::convertDate($res->round_date_last, 1);
			$project_roundslist[]=JHTML::_('select.option',$res->id,sprintf("%s (%s)",$res->name,$datum));
		}
		$lists['project_rounds']=JHTML::_(	'select.genericList',$project_roundslist,'rid[]',
				'class="inputbox" ' .
				'onChange="document.getElementById(\'short_act\').value=\'rounds\';' .
				'document.roundForm.submit();" ',
				'value','text',$roundws->id);

		$lists['project_rounds2']=JHTML::_('select.genericList',$project_roundslist,'rid','class="inputbox" ','value','text',$roundws->id);

  
    // diddipoeler rounds for change in match
    $project_change_roundslist = array();
    if ( $ress =& sportsmanagementHelper::getRoundsOptions($model->_id, 'ASC', true) )
    {
			$project_change_roundslist = array_merge($project_change_roundslist,$ress);
		}
		$lists['project_change_rounds'] = $project_change_roundslist;
		unset($project_change_roundslist);
	
		
		//build the html selectlist for matches
		$overall_config=$model->getTemplateConfig('overall');
		if ((isset($overall_config['use_jl_substitution']) && $overall_config['use_jl_substitution']) ||
				(isset($overall_config['use_jl_events']) && $overall_config['use_jl_events']))
		{
			$match_list=array();
			$mdd[]=JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_MATCH'));

			foreach ($matches as $row)
			{
				$mdd[]=JHTML::_('select.option','index3.php?option=com_joomleague&controller=match&task=editEvents&cid[0]='.$row->id,$row->team1.'-'.$row->team2);
			}
			$RosterEventMessage=(isset($overall_config['use_jl_substitution']) && $overall_config['use_jl_substitution']) ? JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_LINEUP') : '';
			if (isset($overall_config['use_jl_events']) && $overall_config['use_jl_events'])
			{
				if (isset($overall_config['use_jl_events']) && $overall_config['use_jl_substitution']){
					$RosterEventMessage .= ' / ';
				}
				$RosterEventMessage .= JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EVENTS');
			}
			$RosterEventMessage .= ($RosterEventMessage != '') ? ':' : '';
			$lists['RosterEventMessage']=$RosterEventMessage;

			$lists['round_matches']=JHTML::_(	'select.genericList',$mdd,'mdd',
					'id="mdd" class="inputbox" onchange="jl_load_new_match_events(this,\'eventscontainer\')"',
					'value','text','0');
		}

		//build the html options for extratime
		$match_result_type[]=JHTMLSelect::option('0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RT'));
		$match_result_type[]=JHTMLSelect::option('1',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_OT'));
		$match_result_type[]=JHTMLSelect::option('2',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SO'));
		$lists['match_result_type']=$match_result_type;
		unset($match_result_type);

		//build the html options for massadd create type
		$createTypes=array(	0 => JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD'),
				1 => JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_1'),
				2 => JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_2')
		);
		$ctOptions=array();
		foreach($createTypes AS $key => $value){
			$ctOptions[]=JHTMLSelect::option($key,$value);
		}
		$lists['createTypes']=JHTMLSelect::genericlist($ctOptions,'ct[]','class="inputbox" onchange="javascript:displayTypeView();"','value','text',1,'ct');
		unset($createTypes);

		// build the html radio for adding into one round / all rounds
		$createYesNo=array(0 => JText::_('JNO'),1 => JText::_('JYES'));
		$ynOptions=array();
		foreach($createYesNo AS $key => $value){
			$ynOptions[]=JHTMLSelect::option($key,$value);
		}
		$lists['addToRound']=JHTMLSelect::radiolist($ynOptions,'addToRound','class="inputbox"','value','text',0);

		// build the html radio for auto publish new matches
		$lists['autoPublish']=JHTMLSelect::radiolist($ynOptions,'autoPublish','class="inputbox"','value','text',0);
		//build the html options for divisions
		$divisions[]=JHTMLSelect::option('0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION'));
		$mdlDivisions = JModel::getInstance("divisions", "JoomLeagueModel");
		if ($res =& $mdlDivisions->getDivisions($project_id)){
			$divisions=array_merge($divisions,$res);
		}
		$lists['divisions']=$divisions;
		unset($divisions);
        */
        
        
        
        
        // state filter
		$lists['state']=JHTML::_('grid.state',$filter_state);

		// table ordering
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_order;

		// search filter
		$lists['search']=$search;
		$lists['search_mode']=$search_mode;
        
		//$this->assignRef('division',$division);

		$this->assignRef('user',JFactory::getUser());
		$this->assignRef('lists',$lists);
		$this->assignRef('matches',$items);
		//$this->assignRef('ress',$ress);
		$this->assignRef('projectws',$projectws);
		$this->assignRef('roundws',$roundws);
		$this->assignRef('pagination',$pagination);
		$this->assignRef('request_url',$uri->toString());
		//$this->assignRef('prefill', $params->get('use_prefilled_match_roster',0));
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
		$massadd=JRequest::getInt('massadd',0);

		// Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TITLE'),'Matchdays');

		if (!$massadd)
		{
			JToolBarHelper::publishList('match.publish');
			JToolBarHelper::unpublishList('match.unpublish');
			JToolBarHelper::divider();

			JToolBarHelper::apply('match.saveshort');
			JToolBarHelper::divider();

			JToolBarHelper::custom('match.massadd','new.png','new_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_MATCHES'),false);
			JToolBarHelper::addNewX('match.addmatch',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_ADD_MATCH'));
			JToolBarHelper::deleteList(JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_WARNING'), 'match.remove');
			JToolBarHelper::divider();

			JToolBarHelper::back('JPREV','index.php?option=com_sportsmanagement&view=rounds');
		}
		else
		{
			JToolBarHelper::custom('match.cancelmassadd','cancel.png','cancel_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_CANCEL_MATCHADD'),false);
		}
		//JLToolBarHelper::onlinehelp();
		JToolBarHelper::preferences(JRequest::getCmd('option'));
	}
}
?>
