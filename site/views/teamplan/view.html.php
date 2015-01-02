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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.html.pane');
jimport('joomla.functions');
JHtml::_('behavior.tooltip');

//require_once( JPATH_COMPONENT_SITE . DS . 'helpers' . DS . 'route.php' );
//require_once( JPATH_COMPONENT_SITE . DS . 'models' . DS . 'project.php' );
//require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'models'.DS.'divisions.php');
//require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'models'.DS.'rounds.php');
//require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'models'.DS.'teams.php');
//require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'models'.DS.'projectteams.php');
//require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'helpers'.DS.'sportsmanagement.php');

/**
 * sportsmanagementViewTeamPlan
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewTeamPlan extends JViewLegacy
{
	/**
	 * sportsmanagementViewTeamPlan::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        
		$model = $this->getModel();
        $model::$cfg_which_database = $jinput->getInt('cfg_which_database',0);
        
        $document->addScript ( JUri::root(true).'/components/'.$option.'/assets/js/smsportsmanagement.js' );
        
        //$mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
        sportsmanagementModelProject::setProjectID($jinput->getInt('p',0),$model::$cfg_which_database);
		$project = sportsmanagementModelProject::getProject($model::$cfg_which_database);
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName(),$model::$cfg_which_database);
		
		if (isset($project))
		{
			$this->assignRef('project',$project);
			$rounds = sportsmanagementModelProject::getRounds($config['plan_order'],$model::$cfg_which_database);

			$this->assign('overallconfig',sportsmanagementModelProject::getOverallConfig($model::$cfg_which_database));
			$this->assign('config',array_merge($this->overallconfig,$config));
			$this->assignRef('rounds',$rounds);
			$this->assign('teams',sportsmanagementModelProject::getTeamsIndexedByPtid(0,'name',$model::$cfg_which_database));
			$this->assignRef('match',$match);
			$this->assign('favteams',sportsmanagementModelProject::getFavTeams($model::$cfg_which_database));
			$this->assign('division',$model->getDivision());
			$this->assign('ptid',$model->getProjectTeamId());
			$this->assign('projectevents',sportsmanagementModelProject::getProjectEvents(0,$model::$cfg_which_database));
			$this->assign('matches',$model->getMatches($config));
			$this->assign('matches_refering',$model->getMatchesRefering($config));
			$this->assign('matchesperround',$model->getMatchesPerRound($config,$rounds));
			$this->assignRef('model',$model);

		}
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' project<br><pre>'.print_r($project,true).'</pre>'),'');
        
    //$this->assign('show_debug_info', JComponentHelper::getParams($option)->get('show_debug_info',0) );
    
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
        
        $view = $jinput->getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);

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
					$tab_content = sportsmanagementHelper::getPictureThumb($event->icon, $imgTitle, $width, $height, $type);
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
					$tab_content = sportsmanagementHelper::getPictureThumb($pic_tab, $imgTitle, $width, $height, $type);
				}
				else
				{
					$tab_content = JText::_('COM_SPORTSMANAGEMENT_IN_OUT');
				}

				$pic_time	= JURI::root().'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/playtime.gif';
				$pic_out	= JURI::root().'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/out.png';
				$pic_in		= JURI::root().'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/in.png';
				$imgTime = JHtml::image($pic_time,JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE'),array(' title' => JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE')));
				$imgOut  = JHtml::image($pic_out,JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_WENT_OUT'),array(' title' => JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_WENT_OUT')));
				$imgIn   = JHtml::image($pic_in,JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_CAME_IN'),array(' title' => JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_CAME_IN')));

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
	
	
	/**
	 * sportsmanagementViewTeamPlan::_formatEventContainerInResults()
	 * 
	 * @param mixed $matchevent
	 * @param mixed $event
	 * @param mixed $projectteamId
	 * @param mixed $showEventInfo
	 * @return
	 */
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
				$icon = sportsmanagementHelper::getPictureThumb($event->icon, $imgTitle, $width, $height, $type);

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
				$output .= sportsmanagementHelper::formatName(null, $matchevent->firstname1, $matchevent->nickname1, $matchevent->lastname1, $this->config["name_format"]);
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

	/**
	 * sportsmanagementViewTeamPlan::_formatSubstitutionContainerInResults()
	 * 
	 * @param mixed $subs
	 * @param mixed $projectteamId
	 * @param mixed $imgTime
	 * @param mixed $imgOut
	 * @param mixed $imgIn
	 * @return
	 */
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
