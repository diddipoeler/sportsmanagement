<?php 

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.html.pane');

//require_once(JPATH_COMPONENT_ADMINISTRATOR .DS.'models'.DS.'match.php');
//require_once(JPATH_COMPONENT.DS.'models'.DS.'results.php');

class sportsmanagementViewMatchReport extends JView
{

	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
        $option = JRequest::getCmd('option');
//		$version = urlencode(JoomleagueHelper::getVersion());
//		$css='components/com_joomleague/assets/css/tabs.css?v='.$version;
//		$document->addStyleSheet($css);
        
        // diddipoeler
        $css='components/com_joomleague/assets/css/tooltipstyle.css';
        $document->addStyleSheet($css);
        $css='components/com_joomleague/assets/css/jquery-easy-tooltip.css';
        $document->addStyleSheet($css);
        $document->addScript( JURI::base(true).'/components/com_joomleague/assets/js/tooltipscript.js');

		$model = $this->getModel();
		$config=sportsmanagementModelProject::getTemplateConfig($this->getName());
		$project=sportsmanagementModelProject::getProject();
		$match=sportsmanagementModelMatch::getMatchData(JRequest::getInt( "mid", 0 ));

		$this->assignRef('project',$project);
		$this->assign('overallconfig',sportsmanagementModelProject::getOverallConfig());
		$this->assignRef('config',$config);
		$this->assignRef('match',$match);
		$ret=$model->getMatchText($match->new_match_id);
		$this->assignRef('newmatchtext',$ret->text);
		$ret=$model->getMatchText($match->old_match_id);
		$this->assignRef('oldmatchtext',$ret->text);

		$this->assign('round',$model->getRound());
		$this->assign('team1',sportsmanagementModelProject::getTeaminfo($this->match->projectteam1_id));
		$this->assign('team2',sportsmanagementModelProject::getTeaminfo($this->match->projectteam2_id));
		$this->assign('team1_club',$model->getClubinfo($this->team1->club_id));
		$this->assign('team2_club',$model->getClubinfo($this->team2->club_id));
        $this->assign('matchplayerpositions',$model->getMatchPlayerPositions());
		$this->assign('matchplayers',$model->getMatchPlayers());
		$this->assign('matchstaffpositions',$model->getMatchStaffPositions());
		$this->assign('matchstaffs',$model->getMatchStaff());
		$this->assign('matchrefereepositions',$model->getMatchRefereePositions());
		$this->assign('matchreferees',$model->getMatchReferees());
        $this->assign('matchcommentary',$model->getMatchCommentary());
		$this->assign('substitutes',$model->getSubstitutes());
		$this->assign('eventtypes',$model->getEventTypes());
		$sortEventsDesc = isset($this->config['sort_events_desc']) ? $this->config['sort_events_desc'] : '1';
		$this->assign('matchevents',sportsmanagementModelProject::getMatchEvents($this->match->id,1,$sortEventsDesc));
		$this->assign('playground',$model->getPlayground($this->match->playground_id));
        $this->assignRef('stats',sportsmanagementModelProject::getProjectStats());
		$this->assign('playerstats',$model->getMatchStats());
		$this->assign('staffstats',$model->getMatchStaffStats());
		$this->assignRef('model',$model);



$xmlfile=JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'extended'.DS.'match.xml';
		$jRegistry = new JRegistry;
		$jRegistry->loadString($match->extended, 'ini');
		$extended = JForm::getInstance('extended', $xmlfile, array('control'=> 'extended'), false, '/config');
		$extended->bind($jRegistry);
		
		$this->assignRef( 'extended', $extended);


$extended2 = sportsmanagementHelper::getExtended($match->extended, 'match');
    $this->assignRef( 'extended2', $extended2);
		//$rssfeedlink = $this->extended2->getValue('formation1');
    $this->assign( 'formation1', $this->extended2->getValue('formation1'));
    $this->assign( 'formation2', $this->extended2->getValue('formation2'));
    $schemahome = $this->assign('schemahome',$model->getSchemaHome($this->formation1));
    $schemaaway = $this->assign('schemaaway',$model->getSchemaAway($this->formation2));

$this->assign('show_debug_info', JComponentHelper::getParams($option)->get('show_debug_info',0) );
    $this->assign('use_joomlaworks', JComponentHelper::getParams($option)->get('use_joomlaworks',0) );
    
if ( $this->config['show_pictures'] == 1 )
	  {
		// die bilder zum spiel
		$dest = JPATH_ROOT.'/images/com_joomleague/database/matchreport/'.$this->match->id;
		$folder = 'matchreport/'.$this->match->id;
		$images = $model->getMatchPictures($folder);
		if ( $images )
		{
    $this->assignRef( 'matchimages', $images);
		}
		
	  }    

		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_PAGE_TITLE' );
		if (( isset( $this->team1 ) ) AND (isset( $this->team1 )))
		{
			$pageTitle .= ": ".$this->team1->name." ".JText::_( "COM_SPORTSMANAGEMENT_NEXTMATCH_VS" )." ".$this->team2->name;
		}
		$document->setTitle( $pageTitle );

    $document->addScript( JURI::base(true).'/components/com_joomleague/assets/js/highslide.js');
		$document->addStyleSheet( JURI::base(true) . '/components/com_joomleague/assets/css/highslide/highslide.css' );
    
    $js = "hs.graphicsDir = '".JURI::base(true) . "/components/com_joomleague/assets/css/highslide/graphics/"."';\n";
    $js .= "hs.outlineType = 'rounded-white';\n";
    $js .= "
    hs.lang = {
   cssDirection:     'ltr',
   loadingText :     'Lade...',
   loadingTitle :    'Klick zum Abbrechen',
   focusTitle :      'Klick um nach vorn zu bringen',
   fullExpandTitle : 'Zur Originalgr&ouml;&szlig;e erweitern',
   fullExpandText :  'Vollbild',
   creditsText :     '',
   creditsTitle :    '',
   previousText :    'Voriges',
   previousTitle :   'Voriges (Pfeiltaste links)',
   nextText :        'N&auml;chstes',
   nextTitle :       'N&auml;chstes (Pfeiltaste rechts)',
   moveTitle :       'Verschieben',
   moveText :        'Verschieben',
   closeText :       'Schlie&szlig;en',
   closeTitle :      'Schlie&szlig;en (Esc)',
   resizeTitle :     'Gr&ouml;&szlig;e wiederherstellen',
   playText :        'Abspielen',
   playTitle :       'Slideshow abspielen (Leertaste)',
   pauseText :       'Pause',
   pauseTitle :      'Pausiere Slideshow (Leertaste)',
   number :          'Bild %1/%2',
   restoreTitle :    'Klick um das Bild zu schlie&szlig;en, klick und ziehe um zu verschieben. Benutze Pfeiltasten für vor und zurück.'
};

    
    \n";
    
    $document->addScriptDeclaration( $js );
/*    
    $startfade = 1000;
    
    $js = "jQuery(document).ready(function() {\n ";
    foreach ($this->matchplayers as $player)
    {
    $js .= ' jQuery("#'.$player->person_id.'").slideToggle("'.$startfade.'");\n';
    $startfade += 1000;
    }
    $js .= "});\n";
    $document->addScriptDeclaration( $js );    
*/
		parent::display($tpl);

	}

	function showLegresult($team=0)
	{
		if($this->match->alt_decision==0) {
			if ($team!=0)
			{
				if ($team == 1)
				{
					$result = $this->match->team1_result_split;
				}
				else
				{
					$result = $this->match->team2_result_split;
				}
				$legresult = explode(";",$result);
				//$string = " (";
				if ($legresult)
                {
                if ($team == 1)
				{
					$string = "(".$legresult[0];
				}
				else
				{
					$string = $legresult[0].")";
				}
                }
                /*
                foreach ($legresult as $temp)
                {
                    $string .= $temp.' : ';
                }
				$string = substr_replace($string,'',-2);
				$string .= ') ';
                */
				return $string;
			}
			else
			{
				$legresult1 = str_replace(";","",$this->match->team1_result_split);
				$legresult2 = str_replace(";","",$this->match->team2_result_split);
				if (( $legresult1 == "" ) && ( $legresult2 == "" ))
				{
					return false;
				}
				return true;
		}
		}
	}

	function showOvertimeResult()
	{
		if(isset($this->match->team1_result_ot) || isset($this->match->team2_result_ot))
		{
			$result=$this->match->team1_result_ot.' : '.$this->match->team2_result_ot;
			return $result;
		}
		return false;
	}

	function showShotoutResult()
	{
		if(isset($this->match->team1_result_so) || isset($this->match->team2_result_so))
		{
			$result=$this->match->team1_result_so.' : '.$this->match->team2_result_so;
			return $result;
		}
		return false;
	}

	function showMatchresult($decision,$team)
	{
		if ($decision==1)
		{
			if ($team==1)
			{
				$result=$this->match->team1_result_decision;
			}
			else
			{
				$result=$this->match->team2_result_decision;
			}
		}
		else
		if ($team==1)
		{
			$result=$this->match->team1_result;
		}
		else
		{
			$result=$this->match->team2_result;
		}
		return $result;
	}

	function showSubstitution($sub)
	{
		$pic_time='images/com_joomleague/database/events/'.$this->project->fs_sport_type_name.'/playtime.gif';
		$pic_out='images/com_joomleague/database/events/'.$this->project->fs_sport_type_name.'/out.png';
		$pic_in='images/com_joomleague/database/events/'.$this->project->fs_sport_type_name.'/in.png';

		//$imgTitle=JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_MINUTE');
		//$imgTitle2=array(' title' => $imgTitle);
		//$result=JHTML::image($pic_time,$imgTitle,$imgTitle2).'&nbsp;'.$sub->in_out_time;
		$result='<b>'.$sub->in_out_time.'. '. JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_MINUTE') .'</b>';
		$result .= '<br />';
		$outName = JoomleagueHelper::formatName(null, $sub->out_firstname, $sub->out_nickname, $sub->out_lastname, $this->config["name_format"]);
		if($outName != '') {
			$imgTitle=JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_WENT_OUT');
			$imgTitle2=array(' title' => $imgTitle);
			$result .= JHTML::image($pic_out,$imgTitle,$imgTitle2).'&nbsp;';

			$isFavTeam = in_array( $sub->team_id, explode(",",$this->project->fav_team));

			if ( ($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)) )
			{
			    $result .= JHTML::link(JoomleagueHelperRoute::getPlayerRoute($this->project->id,$sub->team_id,$sub->out_person_id),$outName);
			} else {
			    $result .= $outName;
			}

			if($sub->out_position!='') {
				$result .= '&nbsp;('.JText::_($sub->out_position).')';
			}
			$result .= '<br />';
		}
		$inName = JoomleagueHelper::formatName(null, $sub->firstname, $sub->nickname, $sub->lastname, $this->config["name_format"]);
		if($inName!='') {
			$imgTitle=JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_CAME_IN');
			$imgTitle2=array(' title' => $imgTitle);
			$result .= JHTML::image($pic_in,$imgTitle,$imgTitle2).'&nbsp;';

			$isFavTeam = in_array( $sub->team_id, explode(",",$this->project->fav_team));

			if ( ($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)) )
			{
			    $result .= JHTML::link(JoomleagueHelperRoute::getPlayerRoute($this->project->id,$sub->team_id,$sub->person_id),$inName);
			} else {
			    $result .= $inName;
			}

			if($sub->in_position!='') {
				$result .= '&nbsp;('.JText::_($sub->in_position).')';
			}
			$result .= '<br /><br />';
		}
		return $result;
	}

	function showEvents($eventid=0,$projectteamid=0)
	{
		$result='';
		$txt_tab='';
		// Make event table
			foreach ($this->matchevents AS $me)
			{
				if ($me->event_type_id==$eventid && $me->ptid==$projectteamid)
				{
					$result .= '<dt class="list">';

					if ($this->config['show_event_minute'] == 1 && $me->event_time > 0)
					{
					    $prefix = str_pad($me->event_time, 2 ,'0', STR_PAD_LEFT)."' ";
					} else {
					    $prefix = null;
					}

                    $match_player = JoomleagueHelper::formatName($prefix, $me->firstname1, $me->nickname1, $me->lastname1, $this->config["name_format"]);
                        if ($this->config['event_link_player'] == 1 && $me->playerid != 0)
                        {
                            $player_link=JoomleagueHelperRoute::getPlayerRoute($this->project->slug,$me->team_id,$me->playerid);
                            $match_player = JHTML::link($player_link,$match_player);
                        }
					$result .= $match_player;

					// only show event sum and match notice when set to on in template cofig
					if($this->config['show_event_sum'] == 1 || $this->config['show_event_notice'] == 1)
					{
					    if (($this->config['show_event_sum'] == 1 && $me->event_sum > 0) || ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0))
						{
							$result .= ' (';
								if ($this->config['show_event_sum'] == 1 && $me->event_sum > 0)
								{
									$result .= $me->event_sum;
								}
								if (($this->config['show_event_sum'] == 1 && $me->event_sum > 0) && ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0))
								{
									$result .= ' | ';
								}
								if ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0)
								{
									$result .= $me->notice;
								}
							$result .= ')';
						}
					}

					$result .= '</dt>';

				}
			}
		return $result;
	}

	/** timeline */


	function getTimelineMatchTime()
	{
		$result_type=$this->match->match_result_type;
		switch ($result_type) {
    		case 0:
        		/** Ordinary time */
        		$matchtime=$this->project->game_regular_time;
        		break;
    		case 1:
        		/** Overtime time */
        		$matchtime=$this->project->game_regular_time + $this->project->add_time;
       			break;
    		case 2:
        		/** Shotout time */
        		if ( $this->showOvertimeResult() ) {
        		/** First overtime, then Shotout? */
        		$matchtime=$this->project->game_regular_time + $this->project->add_time; }
        		else {
        		$matchtime=$this->project->game_regular_time; }
        		break;
		}
		return $matchtime;
	}

	function getEventsTimes()
	{
		$eventstimecounter = array();

		foreach ($this->matchevents AS $me)
		{
			$eventstimecounter[] = $me->event_time;
		}
		return $eventstimecounter;
	}

	function showSubstitution_Timelines1($sub=0)
	{
		$result='';
		$substitutioncounter = array();
		$eventstimecounter = $this->getEventsTimes();
		foreach ( $this->substitutes as $sub ) {
			if ($sub->ptid == $this->match->projectteam1_id) {
				if (in_array($sub->in_out_time, $eventstimecounter) || in_array($sub->in_out_time, $substitutioncounter))
				{
					$result .= JoomleagueViewMatchReport::_formatTimelineSubstitution($sub,$sub->firstname,$sub->nickname,$sub->lastname,$sub->out_firstname,$sub->out_nickname,$sub->out_lastname,1);
				}
				else {
					$result .= JoomleagueViewMatchReport::_formatTimelineSubstitution($sub,$sub->firstname,$sub->nickname,$sub->lastname,$sub->out_firstname,$sub->out_nickname,$sub->out_lastname,0);
				}
				$substitutioncounter[] = $sub->in_out_time;
			}
		}
		return $result;
	}

	function showSubstitution_Timelines2($sub=0)
	{
		$result='';
		$substitutioncounter = array();
		$eventstimecounter = $this->getEventsTimes();
		foreach ( $this->substitutes as $sub ) {
			if ($sub->ptid == $this->match->projectteam2_id) {
				if (in_array($sub->in_out_time, $eventstimecounter) || in_array($sub->in_out_time, $substitutioncounter))
				{
					$result .= JoomleagueViewMatchReport::_formatTimelineSubstitution($sub,$sub->firstname,$sub->nickname,$sub->lastname,$sub->out_firstname,$sub->out_nickname,$sub->out_lastname,2);
				}
				else {
					$result .= JoomleagueViewMatchReport::_formatTimelineSubstitution($sub,$sub->firstname,$sub->nickname,$sub->lastname,$sub->out_firstname,$sub->out_nickname,$sub->out_lastname,0);
				}
				$substitutioncounter[] = $sub->in_out_time;
			}
		}
		return $result;
	}

	function _formatTimelineSubstitution($sub,$firstname,$nickname,$lastname,$out_firstname,$out_nickname,$out_lastname,$two_substitutions_per_minute=0)
	{

		$pic_out='images/com_joomleague/database/events/'.$this->project->fs_sport_type_name.'/out.png';
		$pic_in='images/com_joomleague/database/events/'.$this->project->fs_sport_type_name.'/in.png';
		$pic_time='images/com_joomleague/database/events/'.$this->project->fs_sport_type_name.'/change.png';

		$time=$sub->in_out_time;
                $matchtime=$this->getTimelineMatchTime();
                $time2=($time / $matchtime) *100;
		$tiptext=JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TIMELINE_SUBSTITUTION_MIN').' ';
		$tiptext .= $time;
		$tiptext .= ' ::';
		$tiptext .= JoomleagueViewMatchReport::getHtmlImageForTips($pic_in);
		$tiptext .= JoomleagueHelper::formatName(null, $firstname, $nickname, $lastname, $this->config["name_format"]);
		$tiptext .= ' &lt;br&gt; ';
		$tiptext .= JoomleagueViewMatchReport::getHtmlImageForTips($pic_out);
		$tiptext .= JoomleagueHelper::formatName(null, $out_firstname, $out_nickname, $out_lastname, $this->config["name_format"]);
		$result='';

		if ($two_substitutions_per_minute == 1) // there were two substitutions in one minute in timelinetop
		{
			$result .= "\n".'<img class="hasTip" style="position: absolute; left: '.$time2.'%; top: -25px;"';
		}
		elseif ($two_substitutions_per_minute == 2) // there were two substitutions in one minute in timelinebottom
		{
			$result .= "\n".'<img class="hasTip" style="position: absolute; left: '.$time2.'%; top: 25px;"';
		}
		else
		{
			$result .= "\n".'<img class="hasTip" style="position: absolute; left: '.$time2.'%;"';
		}

		$result .= ' src="'.$pic_time.'" alt="'.$tiptext.'" title="'.$tiptext;
		$result .= '" />';

		return $result;
	}

	function showEvents_Timelines1($eventid=0,$projectteamid=0)
	{
		$result='';
		$eventcounter = array();
		foreach ($this->eventtypes AS $event)
		{
			foreach ($this->matchevents AS $me)
			{
				if ($me->event_type_id==$event->id && $me->ptid==$this->match->projectteam1_id)
				{
					$placeholder = JoomleagueHelper::getDefaultPlaceholder("player");
					// set teamplayer picture
					if ( ($me->tppicture1 != $placeholder) && (!empty($me->tppicture1)) )
					{
						$picture = $me->tppicture1;
					}
					// when teamplayer picture is empty or a placeholder icon look for the general player picture
					elseif
					(	( ($me->tppicture1 == $placeholder) || (empty($me->tppicture1)) ) &&
						( ($me->picture1 != $placeholder) && (!empty($me->picture1)) )
					)
					{
						$picture = $me->picture1;
					}
					else {
						$picture = '';
					}

					if (in_array($me->event_time, $eventcounter))
					{
						$result .= JoomleagueViewMatchReport::_formatTimelineEvent($me,$event,$me->firstname1,$me->nickname1,$me->lastname1,$picture,1);
					}
					else {
						$result .= JoomleagueViewMatchReport::_formatTimelineEvent($me,$event,$me->firstname1,$me->nickname1,$me->lastname1,$picture,0);
					}
					$eventcounter[] = $me->event_time;
				}
			}
		}
		return $result;
	}

	function showEvents_Timelines2($eventid=0,$projectteamid=0)
	{
		$result='';
		$eventcounter = array();
		foreach ($this->eventtypes AS $event)
		{
			foreach ($this->matchevents AS $me)
			{
				if ($me->event_type_id==$event->id && $me->ptid==$this->match->projectteam2_id)
				{
					$placeholder = JoomleagueHelper::getDefaultPlaceholder("player");
					// set teamplayer picture
					if ( ($me->tppicture1 != $placeholder) && (!empty($me->tppicture1)) )
					{
						$picture = $me->tppicture1;
					}
					// when teamplayer picture is empty or a placeholder icon look for the general player picture
					elseif
					(	( ($me->tppicture1 == $placeholder) || (empty($me->tppicture1)) ) &&
						( ($me->picture1 != $placeholder) && (!empty($me->picture1)) )
					)
					{
						$picture = $me->picture1;
					}
					else {
						$picture = '';
					}

					if (in_array($me->event_time, $eventcounter))
					{
						$result .= JoomleagueViewMatchReport::_formatTimelineEvent($me,$event,$me->firstname1,$me->nickname1,$me->lastname1,$picture,2);
					}
					else {
						$result .= JoomleagueViewMatchReport::_formatTimelineEvent($me,$event,$me->firstname1,$me->nickname1,$me->lastname1,$picture,0);
					}
					$eventcounter[] = $me->event_time;
				}
			}
		}
		return $result;
	}

	function _formatTimelineEvent($matchEvent,$event,$firstname,$nickname,$lastname,$picture,$two_events_per_minute=0)
	{
		$result='';
		if(empty($event->icon)) {
			$event->icon = JUri::Base() . "media/com_joomleague/jl_images/same.png";
		}
		$tiptext=JText::_($event->name).' '.JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_MINUTE_SHORT').' '.$matchEvent->event_time;
		$tiptext .= ' ::';
		if (file_exists($picture))
		{
			$tiptext .= JoomleagueViewMatchReport::getHtmlImageForTips($picture,
																		$this->config['player_picture_width'],
																		$this->config['player_picture_height']);
		}
		$tiptext .= '&lt;br /&gt;'.JoomleagueHelper::formatName(null, $firstname, $nickname, $lastname, $this->config["name_format"]);
		$time=($matchEvent->event_time / $this->getTimelineMatchTime()) *100;
		if ($two_events_per_minute == 1) // there were two events in one minute in timelinetop
		{
			$result .= "\n".'<img class="hasTip" style="position: absolute;left: '.$time.'%; top: -25px;" src="'.$event->icon.'" alt="'.$tiptext.'" title="'.$tiptext;
		}
		elseif ($two_events_per_minute == 2) // there were two events in one minute in timelinebottom
		{
			$result .= "\n".'<img class="hasTip" style="position: absolute;left: '.$time.'%; top: 25px;" src="'.$event->icon.'" alt="'.$tiptext.'" title="'.$tiptext;
		}
		else
		{
			$result .= "\n".'<img class="hasTip" style="position: absolute;left: '.$time.'%;" src="'.$event->icon.'" alt="'.$tiptext.'" title="'.$tiptext;
		}

		if ($this->config['use_tabs_events'] == 2) {
		$result.= '" onclick="gotoevent('.$matchEvent->event_id .')';
		}
		$result.= '" />';
		return $result;
	}

	function getHtmlImageForTips($picture,$width=0,$height=0)
	{
		// diddipoeler
    //$picture = JURI::root(true).'/'.str_replace(JPATH_SITE.DS, "", $picture);
		$picture = JURI::root().$picture;
		if($width > 0 && $height==0) {
			return '&lt;img src=&quot;'.$picture.'&quot; width=&quot;'.$width.'&quot; /&gt;';
		}
		if($height>0 && $width==0) {
			return '&lt;img src=&quot;'.$picture.'&quot;height=&quot;'.$height.'&quot;/&gt;';
		}
		if($height > 0 && $width > 0) {
			return '&lt;img src=&quot;'.$picture.'&quot;height=&quot;'.$height.'&quot; width=&quot;'.$width.'&quot; /&gt;';
		}
		return '&lt;img src=&quot;'.$picture.'&quot; /&gt;';
	}

}
?>
