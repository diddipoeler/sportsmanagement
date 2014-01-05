<?php 
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.html.pane');
jimport('joomla.functions');
JHTML::_('behavior.tooltip');

//require_once( JPATH_COMPONENT_SITE . DS . 'helpers' . DS . 'route.php' );
//require_once( JPATH_COMPONENT_SITE . DS . 'models' . DS . 'project.php' );
//require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'models'.DS.'divisions.php');
//require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'models'.DS.'rounds.php');
//require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'models'.DS.'teams.php');
//require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'models'.DS.'projectteams.php');
//require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'helpers'.DS.'sportsmanagement.php');

class sportsmanagementViewTeamPlan extends JView
{
	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
        $option = JRequest::getCmd('option');
		$model = $this->getModel();
        
        $mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
		$project = $mdlProject->getProject();
		$config = $mdlProject->getTemplateConfig($this->getName());
		
		if (isset($project))
		{
			$this->assignRef('project',$project);
			$rounds = $mdlProject->getRounds($config['plan_order']);

			$this->assign('overallconfig',$mdlProject->getOverallConfig());
			$this->assign('config',array_merge($this->overallconfig,$config));
			$this->assignRef('rounds',$rounds);
			$this->assign('teams',$mdlProject->getTeamsIndexedByPtid());
			$this->assignRef('match',$match);
			$this->assign('favteams',$mdlProject->getFavTeams());
			$this->assign('division',$model->getDivision());
			$this->assign('ptid',$model->getProjectTeamId());
			$this->assign('projectevents',$mdlProject->getProjectEvents());
			$this->assign('matches',$model->getMatches($config));
			$this->assign('matches_refering',$model->getMatchesRefering($config));
			$this->assign('matchesperround',$model->getMatchesPerRound($config,$rounds));
			$this->assignRef('model',$model);

		}
    $this->assign('show_debug_info', JComponentHelper::getParams($option)->get('show_debug_info',0) );
		// Set page title
		if (empty($this->ptid))
		{
			$pageTitle=(!empty($this->project->id)) ? $this->project->name : '';
		}
		else
		{
			if ((isset($this->project)) && $this->ptid){$pageTitle=$this->teams[$this->ptid]->name;}else{$pageTitle='';}
		}
		$document->setTitle(JText::sprintf('COM_SPORTSMANAGEMENT_TEAMPLAN_PAGE_TITLE',$pageTitle));

		parent::display($tpl);
	}

	/**
	 * returns html for events in tabs
	 * @param object match
	 * @param array project events
	 * @param array match events
	 * @param aray match substitutions
	 * @param array $config
	 * @return string
	 */
	function showEventsContainerInResults($matchInfo,$projectevents,$matchevents,$substitutions=null,$config)
	{
		$output='';
		$result='';

		if ($this->config['use_tabs_events'])
		{
			// Make event tabs with JPane integrated function in Joomla 1.5 API
			$result	=& JPane::getInstance('tabs',array('startOffset'=>0));
			$output .= $result->startPane('pane');

			// Size of the event icons in the tabs (when used)
			$width = 20; $height = 20; $type = 4;
 			// Never show event text or icon for each event list item (info already available in tab)
			$showEventInfo = 0;

			$cnt = 0;
			foreach ($projectevents AS $event)
			{
				//display only tabs with events
				foreach ($matchevents AS $me)
				{
					$cnt=0;
					if ($me->event_type_id == $event->id)
					{
						$cnt++;
						break;
					}
				}
				if($cnt==0){continue;}
				
				if ($this->config['show_events_with_icons'] == 1)
				{
					// Event icon as thumbnail on the tab (a placeholder icon is used when the icon does not exist)
					$imgTitle = JText::_($event->name);
					$tab_content = JoomleagueHelper::getPictureThumb($event->icon, $imgTitle, $width, $height, $type);
				}
				else
				{
					$tab_content = JText::_($event->name);
				}

				$output .= $result->startPanel($tab_content, $event->id);
				$output .= '<table class="matchreport" border="0">';
				$output .= '<tr>';

				// Home team events
				$output .= '<td class="list">';
				$output .= '<ul>';
				foreach ($matchevents AS $me)
				{
					$output .= self::_formatEventContainerInResults($me, $event, $matchInfo->projectteam1_id, $showEventInfo);
				}
				$output .= '</ul>';
				$output .= '</td>';

				// Away team events
				$output .= '<td class="list">';
				$output .= '<ul>';
				foreach ($matchevents AS $me)
				{
					$output .= self::_formatEventContainerInResults($me, $event, $matchInfo->projectteam2_id, $showEventInfo);
				}
				$output .= '</ul>';
				$output .= '</td>';
				$output .= '</tr>';
				$output .= '</table>';
				$output .= $result->endPanel();
			}

			if (!empty($substitutions))
			{
				if ($this->config['show_events_with_icons'] == 1)
				{
					// Event icon as thumbnail on the tab (a placeholder icon is used when the icon does not exist)
					$imgTitle = JText::_('COM_SPORTSMANAGEMENT_IN_OUT');
					$pic_tab	= 'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/subst.png';
					$tab_content = JoomleagueHelper::getPictureThumb($pic_tab, $imgTitle, $width, $height, $type);
				}
				else
				{
					$tab_content = JText::_('COM_SPORTSMANAGEMENT_IN_OUT');
				}

				$pic_time	= JURI::root().'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/playtime.gif';
				$pic_out	= JURI::root().'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/out.png';
				$pic_in		= JURI::root().'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/in.png';
				$imgTime = JHTML::image($pic_time,JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE'),array(' title' => JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE')));
				$imgOut  = JHTML::image($pic_out,JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_WENT_OUT'),array(' title' => JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_WENT_OUT')));
				$imgIn   = JHTML::image($pic_in,JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_CAME_IN'),array(' title' => JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_CAME_IN')));

				$output .= $result->startPanel($tab_content,'0');
				$output .= '<table class="matchreport" border="0">';
				$output .= '<tr>';
				$output .= '<td class="list">';
				$output .= '<ul>';
				foreach ($substitutions AS $subs)
				{
					$output .= self::_formatSubstitutionContainerInResults($subs,$matchInfo->projectteam1_id,$imgTime,$imgOut,$imgIn);
				}
				$output .= '</ul>';
				$output .= '</td>';
				$output .= '<td class="list">';
				$output .= '<ul>';
				foreach ($substitutions AS $subs)
				{
					$output .= self::_formatSubstitutionContainerInResults($subs,$matchInfo->projectteam2_id,$imgTime,$imgOut,$imgIn);
				}
				$output .= '</ul>';
				$output .= '</td>';
				$output .= '</tr>';
				$output .= '</table>';
				$output .= $result->endPanel();
			}
			$output .= $result->endPane();
		}
		else
		{
			$showEventInfo = ($this->config['show_events_with_icons'] == 1) ? 1 : 2;
			$output .= '<table class="matchreport" border="0">';
			$output .= '<tr>';

			// Home team events
			$output .= '<td class="list-left">';
			$output .= '<ul>';
			foreach ((array) $matchevents AS $me)
			{
				if ($me->ptid == $matchInfo->projectteam1_id)
				{
					$output .= self::_formatEventContainerInResults($me, $projectevents[$me->event_type_id], $matchInfo->projectteam1_id, $showEventInfo);
				}
			}
			$output .= '</ul>';
			$output .= '</td>';

			// Away team events
			$output .= '<td class="list-right">';
			$output .= '<ul>';
			foreach ($matchevents AS $me)
			{
				if ($me->ptid == $matchInfo->projectteam2_id)
				{
					$output .= self::_formatEventContainerInResults($me, $projectevents[$me->event_type_id], $matchInfo->projectteam2_id, $showEventInfo);
			    }
			}
			$output .= '</ul>';
			$output .= '</td>';
			$output .= '</tr>';
			$output .= '</table>';
		}

		return $output;
	}
	
	
	function _formatEventContainerInResults($matchevent, $event, $projectteamId, $showEventInfo)
	{
		// Meaning of $showEventInfo:
		// 0 : do not show event as text or as icon in a list item
		// 1 : show event as icon in a list item (before the time)
		// 2 : show event as text in a list item (after the time)
		$output='';
		if ($matchevent->event_type_id == $event->id && $matchevent->ptid == $projectteamId)
		{
			$output .= '<li class="events">';
			if ($showEventInfo == 1)
			{
				// Size of the event icons in the tabs
				$width = 20; $height = 20; $type = 4;
				$imgTitle = JText::_($event->name);
				$icon = JoomleagueHelper::getPictureThumb($event->icon, $imgTitle, $width, $height, $type);

				$output .= $icon;
			}

			$event_minute = str_pad($matchevent->event_time, 2 ,'0', STR_PAD_LEFT);
			if ($this->config['show_event_minute'] == 1 && $matchevent->event_time > 0)
			{
				$output .= '<b>'.$event_minute.'\'</b> ';
			} 

			if ($showEventInfo == 2)
			{
				$output .= JText::_($event->name).' ';
			}

			if (strlen($matchevent->firstname1.$matchevent->lastname1) > 0)
			{
				$output .= JoomleagueHelper::formatName(null, $matchevent->firstname1, $matchevent->nickname1, $matchevent->lastname1, $this->config["name_format"]);
			}
			else
			{
				$output .= JText :: _('COM_SPORTSMANAGEMENT_UNKNOWN_PERSON');
			}
			
			// only show event sum and match notice when set to on in template cofig
			if($this->config['show_event_sum'] == 1 || $this->config['show_event_notice'] == 1)
			{
				if (($this->config['show_event_sum'] == 1 && $matchevent->event_sum > 0) || ($this->config['show_event_notice'] == 1 && strlen($matchevent->notice) > 0))
				{
					$output .= ' (';
						if ($this->config['show_event_sum'] == 1 && $matchevent->event_sum > 0)
						{
							$output .= $matchevent->event_sum;
						}
						if (($this->config['show_event_sum'] == 1 && $matchevent->event_sum > 0) && ($this->config['show_event_notice'] == 1 && strlen($matchevent->notice) > 0))
						{
							$output .= ' | ';
						}
						if ($this->config['show_event_notice'] == 1 && strlen($matchevent->notice) > 0)
						{
							$output .= $matchevent->notice;
						}
					$output .= ')';
				}
			}
			
			$output .= '</li>';
		}
		return $output;
	}

	function _formatSubstitutionContainerInResults($subs,$projectteamId,$imgTime,$imgOut,$imgIn)
	{
		$output='';
		if ($subs->ptid == $projectteamId)
		{
			$output .= '<li class="events">';
			// $output .= $imgTime;
			$output .= '&nbsp;'.$subs->in_out_time.'. '.JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE');
			$output .= '<br />';

			$output .= $imgOut;
			$output .= '&nbsp;'.sportsmanagementHelper::formatName(null, $subs->out_firstname, $subs->out_nickname, $subs->out_lastname, $this->config["name_format"]);
			$output .= '&nbsp;('.JText :: _($subs->out_position).')';
			$output .= '<br />';

			$output .= $imgIn;
			$output .= '&nbsp;'.sportsmanagementHelper::formatName(null, $subs->firstname, $subs->nickname, $subs->lastname, $this->config["name_format"]);
			$output .= '&nbsp;('.JText :: _($subs->in_position).')';
			$output .= '<br /><br />';
			$output .= '</li>';
		}
		return $output;
	}
}
?>
