<?php 

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;

jimport('joomla.application.component.view');
jimport( 'joomla.filesystem.file' );

require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'pagination.php');

class sportsmanagementViewResults extends JViewLegacy
{

	public function display($tpl = null)
	{
		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        $option = JFactory::getApplication()->input->getCmd('option');
        $app = JFactory::getApplication();
        $roundcode = 0;
//		$css		= 'components/com_sportsmanagement/assets/css/tabs.css';
//		$document->addStyleSheet($css);
//		$css		= 'components/com_sportsmanagement/assets/css/joomleague.css';
//		$document->addStyleSheet($css);
//		$css		= 'components/com_sportsmanagement/assets/css/results.css';
//		$document->addStyleSheet($css);
		
		
		$model	= $this->getModel();
				
		$matches = $model->getMatches();
		
		
		$config	= sportsmanagementModelProject::getTemplateConfig($this->getName());
		$project = sportsmanagementModelProject::getProject();
		$roundcode = sportsmanagementModelRound::getRoundcode((int)$model::$roundid);
		$rounds = sportsmanagementHelper::getRoundsOptions($project->id, 'ASC', true);
			
		$this->project = $project;
		$lists = array();
		
		if (isset($this->project))
		{
			$this->overallconfig = sportsmanagementModelProject::getOverallConfig();
			$this->config = array_merge($this->overallconfig, $config);
			$this->teams = sportsmanagementModelProject::getTeamsIndexedByPtid();
			$this->showediticon = $model->getShowEditIcon();
			$this->division = $model->getDivision();
			$this->matches = $matches;
			$this->roundid = $model::$roundid;
			$this->roundcode = $roundcode;
			$this->rounds = sportsmanagementModelProject::getRounds();
			$this->favteams = sportsmanagementModelProject::getFavTeams($project);
			$this->projectevents = sportsmanagementModelProject::getProjectEvents();
			$this->model = $model;
			$this->isAllowed = $model->isAllowed();

			$lists['rounds'] = JHtml::_('select.genericlist',$rounds,'current_round','class="inputbox" size="1" onchange="joomleague_changedoc(this);','value','text',$project->current_round);
			$this->lists = $lists;
		
			if (!isset($this->config['switch_home_guest'])){$this->config['switch_home_guest']=0;}
			if (!isset($this->config['show_dnp_teams_icons'])){$this->config['show_dnp_teams_icons']=0;}
			if (!isset($this->config['show_results_ranking'])){$this->config['show_results_ranking']=0;}
		}
		
		// Set page title
		$pageTitle = Text::_('COM_SPORTSMANAGEMENT_RESULTS_PAGE_TITLE');
		if ( isset( $this->project->name ) )
		{
			$pageTitle .= ': ' . $this->project->name;
		}
		$document->setTitle($pageTitle);

		//build feed links
		$feed = 'index.php?option=com_sportsmanagement&view=results&p='.$this->project->id.'&format=feed';
		$rss = array('type' => 'application/rss+xml', 'title' => Text::_('COM_SPORTSMANAGEMENT_RESULTS_RSSFEED'));

		$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' layout'.'<pre>'.print_r($this->getLayout(),true).'</pre>' ),'');
        
        // add the links
		//$document->addHeadLink(JRoute::_($feed.'&type=rss'), 'alternate', 'rel', $rss);

		parent::display($tpl);
	}

	/**
	 * return html code for not playing teams
	 * 
	 * @param array $games
	 * @param array $teams
	 * @param array $config
	 * @param array $favteams
	 * @param object $project
	 * @return string html
	 */
	public function showNotPlayingTeams(&$games,&$teams,&$config,&$favteams,&$project)
	{
		$output='';
		$playing_teams=array();

		foreach($games as $game)
		{
			self::addPlayingTeams($playing_teams,$game->projectteam1_id,$game->projectteam2_id,$game->published);
		}
		$x=0;
		$not_playing=count($teams) - count($playing_teams);
		if ($not_playing > 0)
		{
			$output .= '<b>'.Text::sprintf('COM_SPORTSMANAGEMENT_RESULTS_TEAMS_NOT_PLAYING',$not_playing).'</b> ';
			foreach ($teams AS $id => $team)
			{
				if (!isset($team->$config['names'])) continue;
				if (isset($team->projectteamid) && in_array($team->projectteamid,$playing_teams))
				{
					continue; //if team is playing,go to next
				}
				if ($x > 0)
				{
					$output .= ', ';
				}
				if ($config['show_logo_small'] > 0 && $config['show_dnp_teams_icons'])
				{
					$output .= self::getTeamClubIcon($team,$config['show_logo_small']).'&nbsp;';
				}
				if ($config['highlight_fav'] > 0 && isset($team->id) && (in_array($team->id,$favteams)) ? 1 : 0)
				{
					//$output .= '<b style="font-weight:normal;padding:2px;color:'.$project->fav_team_text_color.';background-color:'.$project->fav_team_color.'">';
					$output .= sportsmanagementHelper::formatTeamName($team, 't'.$team->id, $config, $isFavTeam );
				}
				$output .= str_replace(' ','&nbsp;', $team->$config['names']);
				if ($config['highlight_fav'] > 0 && (isset($team->id) && in_array($team->id,$favteams)) ? 1 : 0){$output .=  '</b>';}
				$x++;
			}
		}
		return $output;
	}

	public function addPlayingTeams(&$playing_teams,$hometeam,$awayteam,$published=false)
	{
		if ($hometeam>0 && !in_array($hometeam,$playing_teams) && $published){$playing_teams[]=$hometeam;}
		if ($awayteam>0 && !in_array($awayteam,$playing_teams) && $published){$playing_teams[]=$awayteam;}
	}
	
	/**
	 * returns html <img> for club assigned to team
	 * @param object team
	 * @param int type=1 for club small image,or 2 for club country
	 * @param boolean $with_space
	 * @return unknown_type
	 */
	public function getTeamClubIcon($team,$type=1,$attribs=array())
	{
		if(!isset($team->name)) return "";
		$title=$team->name;
		$attribs=array_merge(array('title' => $title,$attribs));
		if ($type==1)
		{
			if (!empty($team->logo_small) && JFile::exists($team->logo_small))
			{
				$image=JHtml::image($team->logo_small,$title,$attribs);
			}
			else
			{
				$image=JHtml::image(JURI::root().sportsmanagementHelper::getDefaultPlaceholder("clublogosmall"),$title,$attribs);
			}
		}
		elseif ($type==2 && !empty($team->country))
		{
			$image=JSMCountries::getCountryFlag($team->country);
			if (empty($image))
			{
				$image=JHtml::image(JURI::root().sportsmanagementHelper::getDefaultPlaceholder("clublogosmall"),$title,$attribs);
			}
		}
		else
		{
			$image='';
		}

		return $image;
	}
	

	/**
	 * return an array of matches indexed by date
	 *
	 * @return array
	 */
	public function sortByDate()
	{
		$dates=array();
		foreach ((array) $this->matches as $m)
		{
			$date=substr($m->match_date,0,10);
			if (!isset($dates[$date]))
			{
				$dates[$date]=array($m);
			}
			else
			{
				$dates[$date][]=$m;
			}
		}
		return $dates;
	}
	
	/**
	 * used in form row template
	 * TODO: move to own template file
	 * 
	 * @param int $i
	 * @param object $match
	 * @param boolean $backend
	 */
	public function editPartResults($i,$match,$backend=false)
	{
		$link="javascript:void(0)";
		$params=array("onclick" => "switchMenu('part".$match->id."')");
		$imgTitle=Text::_('COM_SPORTSMANAGEMENT_ADMIN_EDIT_MATRIX_ROUNDS_PART_RESULT');
		$desc=JHtml::image(	JURI::root()."media/com_sportsmanagement/jl_images/sort01.gif",
		$imgTitle,array("border" => 0,"title" => $imgTitle));
		echo JHtml::link($link,$desc,$params);

		echo '<span id="part'.$match->id.'" style="display:none">';
		echo '<br />';

		$partresults1= explode(";",$match->team1_result_split);
		$partresults2= explode(";",$match->team2_result_split);

		for ($x=0; $x < ($this->project->game_parts); $x++)
		{
			echo ($x+1).".:";
			echo '<input type="text" style="font-size:9px;" name="team1_result_split';
			echo (! is_null($i)) ? $match->id : '';
			echo '[]" value="';
			echo (isset($partresults1[$x])) ? $partresults1[$x] : '';
			echo '" size="2" tabindex="1" class="inputbox"';
			if (! is_null($i))
			{
				echo ' onchange="';
				if ($backend)
				{
					echo 'document.adminForm.cb'.$i.'.checked=true; isChecked(this.checked);';
				}
				else
				{
					echo '$(\'cb'.$i.'\').checked=true;';
				}
				echo '"';
			}
			echo ' />';
			echo ':';
			echo '<input type="text" style="font-size:9px;"';
			echo ' name="team2_result_split';
			echo (! is_null($i)) ? $match->id : '';
			echo '[]" value="';
			echo (isset($partresults2[$x])) ? $partresults2[$x] : '';
			echo '" size="2" tabindex="1" class="inputbox" ';
			if (! is_null($i))
			{
				echo 'onchange="';
				if ($backend)
				{
					echo 'document.adminForm.cb'.$i.'.checked=true; isChecked(this.checked);';
				}
				else
				{
					echo '$(\'cb'.$i.'\').checked=true;';
				}
				echo '"';
			}
			echo '/>';
			if (($x) < ($this->project->game_parts)){echo '<br />';}
		}

		if ($this->project->allow_add_time)
		{
			if($match->match_result_type >0){
				echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME').':';
				echo '<input type="text" style="font-size:9px;"';
				echo ' name="team1_result_ot'.$match->id.'"';
				echo ' value="';
				echo isset($match->team1_result_ot) ? ''.$match->team1_result_ot : '';
				echo '"';
				echo ' size="2" tabindex="1" class="inputbox" onchange="$(\'cb'.$i.'\').checked=true;" />';
				echo ':';
				echo '<input type="text" style="font-size:9px;"';
				echo ' name="team2_result_ot'.$match->id.'"';
				echo ' value="';
				echo isset($match->team2_result_ot) ? ''.$match->team2_result_ot : '';
				echo '"';
				echo ' size="2" tabindex="1" class="inputbox" onchange="$(\'cb'.$i.'\').checked=true;" />';
			}
			if($match->match_result_type == 2){
				echo '<br />';
				echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT').':';
				echo '<input type="text" style="font-size:9px;"';
				echo ' name="team1_result_so'.$match->id.'"';
				echo ' value="';
				echo isset($match->team1_result_so) ? ''.$match->team1_result_so : '';
				echo '"';
				echo ' size="2" tabindex="1" class="inputbox" onchange="$(\'cb'.$i.'\').checked=true;" />';
				echo ':';
				echo '<input type="text" style="font-size:9px;"';
				echo ' name="team2_result_so'.$match->id.'"';
				echo ' value="';
				echo isset($match->team2_result_so) ? ''.$match->team2_result_so : '';
				echo '"';
				echo ' size="2" tabindex="1" class="inputbox" onchange="$(\'cb'.$i.'\').checked=true;" />';
			}
		}
		echo "</span>";
	}
	
	
	/**
	* formats the score according to settings
	*
	* @param object $game
	* @return string
	*/
	function formatScoreInline($game,&$config)
	{
		if ($config['switch_home_guest'])
		{
			$homeResult	= $game->team2_result;
			$awayResult	= $game->team1_result;
			$homeResultOT	= $game->team2_result_ot;
			$awayResultOT	= $game->team1_result_ot;
			$homeResultSO	= $game->team2_result_so;
			$awayResultSO	= $game->team1_result_so;
			$homeResultDEC	= $game->team2_result_decision;
			$awayResultDEC	= $game->team1_result_decision;
		}
		else
		{
			$homeResult	= $game->team1_result;
			$awayResult	= $game->team2_result;
			$homeResultOT	= $game->team1_result_ot;
			$awayResultOT	= $game->team2_result_ot;
			$homeResultSO	= $game->team1_result_so;
			$awayResultSO	= $game->team2_result_so;
			$homeResultDEC	= $game->team1_result_decision;
			$awayResultDEC	= $game->team2_result_decision;
		}
	
		$result=$homeResult.'&nbsp;'.$config['seperator'].'&nbsp;'.$awayResult;
		if ($game->alt_decision)
		{
			$result='<b style="color:red;">';
			$result .= $homeResultDEC.'&nbsp;'.$config['seperator'].'&nbsp;'.$awayResultDEC;
			$result .= '</b>';
	
		}
		if (isset($homeResultSO) || isset($formatScoreawayResultSO))
		{
			if ($this->config['result_style']==1){
				$result .= '<br />';
			}else{$result .= ' ';
			}
			$result .= '('.Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT').' ';
			$result .= $homeResultSO.'&nbsp;'.$config['seperator'].'&nbsp;'.$awayResultSO;
			$result .= ')';
		}
		else
		{
			if ($game->match_result_type==2)
			{
				if ($this->config['result_style']==1){
					$result .= '<br />';
				}else{$result .= ' ';
				}
				$result .= '('.Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT');
				$result .= ')';
			}
		}
		if (isset($homeResultOT) || isset($awayResultOT))
		{
			if ($this->config['result_style']==1){
				$result .= '<br />';
			}else{$result .= ' ';
			}
			$result .= '('.Text::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME').' ';
			$result .= $homeResultOT.'&nbsp;'.$config['seperator'].'&nbsp;'.$awayResultOT;
			$result .= ')';
		}
		else
		{
			if ($game->match_result_type==1)
			{
				if ($this->config['result_style']==1){
					$result .= '<br />';
				}else{$result .= ' ';
				}
				$result .= '('.Text::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME');
				$result .= ')';
			}
		}

		return $result;
	}
	
	/**
	 * return match state html code
	 * @param $game
	 * @param $config
	 * @return unknown_type
	 */
	function showMatchState(&$game,&$config)
	{
		$output='';

		if ($game->cancel > 0)
		{
			$output .= $game->cancel_reason;
		}
		else
		{
			$output .= self::formatScoreInline($game,$config);
		}

		return $output;
	}

	/**
	 * returns html link code to display game events
	 * @param $are_there_events
	 * @param $game
	 * @param $config
	 * @return unknown_type
	 */
	function prepareEventsOutput($match_id)
	{
		$imgTitle=Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOW_EVENTS_OF_MATCH');
		$attribs=array(	"title" => $imgTitle,
		 		"id" => 'events-'. $match_id,
		 		"class" => "eventstoggle");
		$img=JHtml::image(JURI::root().'media/com_sportsmanagement/jl_images/events.png',$imgTitle,$attribs);
		return $img;
	}

	function showMatchRefereesAsTooltip(&$game)
	{
		if ($this->config['show_referee'])
		{
			if ($this->project->teams_as_referees)
			{
				$referees=$this->model->getMatchRefereeTeams($game->id);
			}
			else
			{
				$referees=$this->model->getMatchReferees($game->id);
			}

			if (!empty($referees))
			{
				$toolTipTitle	= Text::_('Match Referees');
				$toolTipText	= '';

				foreach ($referees as $ref)
				{
					if ($this->project->teams_as_referees)
					{
						$toolTipText .= $ref->teamname.' ('.$ref->position_name.')'.'&lt;br /&gt;';
					}
					else
					{
						$toolTipText .= ($ref->firstname ? $ref->firstname.' '.$ref->lastname : $ref->lastname).' ('.$ref->position_name.')'.'&lt;br /&gt;';
					}
				}

				?>
			<!-- Referee tooltip -->
			<span class="hasTip"
				title="<?php echo $toolTipTitle; ?> :: <?php echo $toolTipText; ?>"> <img
				src="<?php echo JURI::root(); ?>media/com_sportsmanagement/jl_images/icon-16-Referees.png"
				alt="" title="" /> </span>
			
				<?php
			}
			else
			{
				?>&nbsp;<?php
			}
		}
	}
	
	function showReportDecisionIcons(&$game)
	{
		//echo '<br /><pre>~'.print_r($game,true).'~</pre><br />';

		$output = '';
		//$report_link = sportsmanagementHelper::getDefaultPlaceholder("clublogosmall"),$title,$attribs);

		if ((($game->show_report) && (trim($game->summary) != '')) || ($game->alt_decision) || ($game->match_result_type > 0))
		{
			if ($game->alt_decision)
			{
				$imgTitle = Text::_($game->decision_info);
				$img = 'media/com_sportsmanagement/jl_images/court.gif';
			}
			else
			{
				$imgTitle=Text::_('Has match summary');
				$img='media/com_sportsmanagement/jl_images/zoom.png';
			}
			$output .= JHtml::_(	'link',
			$report_link,
			JHtml::image(JURI::root().$img,$imgTitle,array("border" => 0,"title" => $imgTitle)),
			array("title" => $imgTitle));
		}
		else
		{
			$output .= '&nbsp;';
		}

		return $output;
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
			$cnt	= 0;

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
				// I think its better to show the event name,the the event image(gives some probs with tabs)
				$pic_tab	= JURI::root().$event->icon;
				$imgTitle	= Text::_($event->name); $imgTitle2=array(' title' => $imgTitle);
				$txt_tab	= JHtml::image($pic_tab,$imgTitle,$imgTitle2);

				$backgroundStyle="background: url(".$pic_tab.") no-repeat transparent";
				if(empty($pic_tab))
				{
					$backgroundStyle="";
				}

				$output .= $result->startPanel($txt_tab,$event->id);
				//$output .= $result->startPanel($event->name,$event->id);
				$output .= '<table class="matchreport" border="0">';
				$output .= '<tr>';
				$output .= '<td class="list">';
				$output .= '<ul>';
				foreach ($matchevents AS $me)
				{
					$output .= self::_formatEventContainerInResults($me,$event,$matchInfo->projectteam1_id,$backgroundStyle);
				}
				$output .= '</ul>';
				$output .= '</td>';
				$output .= '<td class="list">';
				$output .= '<ul>';
				foreach ($matchevents AS $me)
				{
					$output .= self::_formatEventContainerInResults($me,$event,$matchInfo->projectteam2_id,$backgroundStyle);
				}
				$output .= '</ul>';
				$output .= '</td>';
				$output .= '</tr>';
				$output .= '</table>';
				$output .= $result->endPanel();
			}

			if (!empty($substitutions))
			{
				$pic_time	= JURI::root().'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/playtime.gif';
				$pic_out	= JURI::root().'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/out.png';
				$pic_in		= JURI::root().'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/in.png';
				$pic_tab	= JURI::root().'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/subst.png';

				$imgTitle	= Text::_('COM_SPORTSMANAGEMENT_IN_OUT'); $imgTitle2=array(' title' => $imgTitle);
				$txt_tab	= JHtml::image($pic_tab,$imgTitle,$imgTitle2);

				$imgTime=JHtml::image($pic_time,Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE'),array(' title' => Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE')));
				$imgOut=JHtml::image($pic_out,Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_WENT_OUT'),array(' title' => Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_WENT_OUT')));
				$imgIn=JHtml::image($pic_in,Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_CAME_IN'),array(' title' => Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_CAME_IN')));

				$output .= $result->startPanel($txt_tab,'0');
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
			$output .= '<table class="matchreport" border="0">';
			$output .= '<tr>';
			$output .= '<td class="list-left">';
			$output .= '<ul>';
			foreach ((array) $matchevents AS $me)
			{
				if ($me->ptid == $matchInfo->projectteam1_id)
				{
				    if ($this->config['show_events_with_icons'] == 1 ) 
				    { 
				    	$event_icon = JURI::root().$projectevents[$me->event_type_id]->icon;
					
					$backgroundStyle="background: url(".$event_icon.") no-repeat transparent";
					if(empty($event_icon)) { $backgroundStyle="";}
						
					$output .= self::_formatEventContainerInResults($me,$projectevents[$me->event_type_id],$matchInfo->projectteam1_id,$backgroundStyle);
					
				    } else {
					$output .= '<li class="list">';
					if (!strlen($me->firstname1.$me->lastname1))
					{
						$output .= $me->event_time.'\' '.Text::_($projectevents[$me->event_type_id]->name). ' '.Text::_('Unknown Person');
					}
					else
					{
						$output .= $me->event_time.'\' '.Text::_($projectevents[$me->event_type_id]->name). ' '.$me->firstname1.' '.$me->lastname1;
					}
					
					// only show event sum and match notice when set to on in template cofig
					if($this->config['show_event_sum'] == 1 || $this->config['show_event_notice'] == 1)
					{
						if (($this->config['show_event_sum'] == 1 && $me->event_sum > 0) || ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0))
						{
							$output .= ' (';
							if ($this->config['show_event_sum'] == 1 && $me->event_sum > 0)
							{
								$output .= $me->event_sum;
							}
							if (($this->config['show_event_sum'] == 1 && $me->event_sum > 0) && ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0))
							{
								$output .= ' | ';
							}
							if ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0)
							{
								$output .= $me->notice;
							}
							$output .= ')';
						}
					}
					
					$output .= '</li>';
				    }
				}
			}
			$output .= '</ul>';
			$output .= '</td>';
			$output .= '<td class="list-right">';
			$output .= '<ul>';
			foreach ($matchevents AS $me)
			{
				if ($me->ptid == $matchInfo->projectteam2_id)
				{
				    if ($this->config['show_events_with_icons'] == 1 ) 
				    { 
				    	$event_icon = JURI::root().$projectevents[$me->event_type_id]->icon;
					
					$backgroundStyle="background: url(".$event_icon.") no-repeat transparent";
					if(empty($event_icon)) { $backgroundStyle="";}
						
					$output .= self::_formatEventContainerInResults($me,$projectevents[$me->event_type_id],$matchInfo->projectteam2_id,$backgroundStyle);
					
				    } else {

					$output .= '<li class="list">';
					if (!strlen($me->firstname1.$me->lastname1))
					{
						$output .= $me->event_time.'\' '.Text::_($projectevents[$me->event_type_id]->name). ' '.Text::_('Unknown Person');
					}
					else
					{
						$output .= $me->event_time.'\' '.Text::_($projectevents[$me->event_type_id]->name). ' '.$me->firstname1.' '.$me->lastname1;
					}
					
					// only show event sum and match notice when set to on in template cofig
					if($this->config['show_event_sum'] == 1 || $this->config['show_event_notice'] == 1)
					{
						if (($this->config['show_event_sum'] == 1 && $me->event_sum > 0) || ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0))
						{
							$output .= ' (';
							if ($this->config['show_event_sum'] == 1 && $me->event_sum > 0)
							{
								$output .= $me->event_sum;
							}
							if (($this->config['show_event_sum'] == 1 && $me->event_sum > 0) && ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0))
							{
								$output .= ' | ';
							}
							if ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0)
							{
								$output .= $me->notice;
							}
							$output .= ')';
						}
					}
					
					$output .= '</li>';
				}
			    }
			}
			$output .= '</ul>';
			$output .= '</td>';
			$output .= '</tr>';
			$output .= '</table>';
		}

		return $output;
	}
	
	function formatResult(&$team1,&$team2,&$game,&$reportLink)
	{
		$output			= '';
		// check home and away team for favorite team
		$fav 			= isset($team1->id) && in_array($team1->id,$this->favteams) ? 1 : 0;
		if (!$fav)
		{
			$fav		= isset($team2->id) && in_array($team2->id,$this->favteams) ? 1 : 0;
		}
		// 0=no links
		// 1=For all teams
		// 2=For favorite team(s) only
		if ($this->config['show_link_matchreport'] == 1 || ($this->config['show_link_matchreport'] == 2 && $fav))
		{
			$output = JHtml::_(	'link', $reportLink,
					'<span class="score0">'.$this->showMatchState($game,$this->config).'</span>',
			array("title" => Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOW_MATCHREPORT')));
		}
		else
		{
			$output = $this->showMatchState($game,$this->config);
		}

		return $output;

	}
	
	function _formatEventContainerInResults($matchevent,$event,$projectteamId,$backgroundStyle)
	{
		$output = '';
		if ($matchevent->event_type_id == $event->id && $matchevent->ptid == $projectteamId)
		{
			$output .= '<li class="events" style="'.$backgroundStyle.'">';
			if (!strlen($matchevent->firstname1.$matchevent->lastname1))
			{
				$output .= $matchevent->event_time.'\' '.Text :: _('Unknown Person');
			}
			else
			{
				$output .= $matchevent->event_time.'\' '.$matchevent->firstname1.' '.$matchevent->lastname1;
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
		$output = '';
		if ($subs->ptid == $projectteamId)
		{
			$output .= '<li class="events">';
			// $output .= $imgTime;
			$output .= '&nbsp;'.$subs->in_out_time.'. '.Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE');
			$output .= '<br />';

			$output .= $imgOut;
			$output .= '&nbsp;'.$subs->out_firstname.' '.$subs->out_lastname;
			$output .= '&nbsp;('.Text :: _($subs->out_position).')';
			$output .= '<br />';

			$output .= $imgIn;
			$output .= '&nbsp;'.$subs->firstname.' '.$subs->lastname;
			$output .= '&nbsp;('.Text :: _($subs->in_position).')';
			$output .= '<br /><br />';
			$output .= '</li>';
		}
		return $output;
	}
}
?>
