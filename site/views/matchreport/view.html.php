<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined('_JEXEC') or die('Restricted access');

//jimport('joomla.html.pane');

require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'player.php');

/**
 * sportsmanagementViewMatchReport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewMatchReport extends sportsmanagementView
{

    /**
     * sportsmanagementViewMatchReport::init()
     * 
     * @return void
     */
    public function init ()
	{
        // diddipoeler
        $css = 'components/com_sportsmanagement/assets/css/tooltipstyle.css';
        $this->document->addStyleSheet($css);
        $css = 'components/com_sportsmanagement/assets/css/jquery-easy-tooltip.css';
        $this->document->addStyleSheet($css);
        $this->document->addScript( JURI::base(true).'/components/com_sportsmanagement/assets/js/tooltipscript.js');

        //$this->model->checkMatchPlayerProjectPositionID();
        $this->model->matchid = $this->jinput->getInt('mid',0);
        sportsmanagementModelProject::setProjectID($this->jinput->getInt('p',0));
        $project = sportsmanagementModelProject::getProject(sportsmanagementModelProject::$cfg_which_database);
		$match = sportsmanagementModelMatch::getMatchData($this->jinput->getInt( "mid", 0 ),sportsmanagementModelProject::$cfg_which_database);
        $matchsingle = sportsmanagementModelMatch::getMatchSingleData($this->jinput->getInt( "mid", 0 ));
        
		$this->match = $match;
        
        $this->matchsingle = $matchsingle;
        
		if ( $ret = sportsmanagementModelMatch::getMatchText($match->new_match_id,sportsmanagementModelProject::$cfg_which_database) )
        {
		$this->newmatchtext = $ret->text;
        }
		if ( $ret = sportsmanagementModelMatch::getMatchText($match->old_match_id,sportsmanagementModelProject::$cfg_which_database) )
        {
		$this->oldmatchtext = $ret->text;
        }
        
        $this->match_article = $this->model->getMatchArticle($this->match->content_id,$this->model->matchid,$project->category_id);

		$this->round = $this->model->getRound();
		$this->team1 = sportsmanagementModelProject::getTeaminfo($this->match->projectteam1_id,sportsmanagementModelProject::$cfg_which_database);
		$this->team2 = sportsmanagementModelProject::getTeaminfo($this->match->projectteam2_id,sportsmanagementModelProject::$cfg_which_database);
		$this->team1_club = $this->model->getClubinfo($this->team1->club_id);
		$this->team2_club = $this->model->getClubinfo($this->team2->club_id);
        $this->matchplayerpositions = $this->model->getMatchPositions('player');
        $this->matchplayers = $this->model->getMatchPersons('player');
        $this->matchstaffpositions = $this->model->getMatchPositions('staff');
        $this->matchstaffs = $this->model->getMatchPersons('staff');
        $this->matchrefereepositions = $this->model->getMatchPositions('referee');
		$this->matchreferees = $this->model->getMatchReferees();
        $this->matchcommentary = sportsmanagementModelMatch::getMatchCommentary($this->match->id);
        $this->substitutes = sportsmanagementModelProject::getMatchSubstitutions($this->model->matchid,sportsmanagementModelProject::$cfg_which_database);
		$this->eventtypes = $this->model->getEventTypes();
		$sortEventsDesc = isset($this->config['sort_events_desc']) ? $this->config['sort_events_desc'] : '1';
		$this->matchevents = sportsmanagementModelProject::getMatchEvents($this->match->id,1,$sortEventsDesc,sportsmanagementModelProject::$cfg_which_database);
		$this->playground = sportsmanagementModelPlayground::getPlayground($this->match->playground_id);
        $this->stats = sportsmanagementModelProject::getProjectStats(0,0,sportsmanagementModelProject::$cfg_which_database);
		$this->playerstats = $this->model->getMatchStats();
		$this->staffstats = $this->model->getMatchStaffStats();
        
$xmlfile=JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'extended'.DS.'match.xml';
		$jRegistry = new JRegistry;
		$jRegistry->loadString($match->extended, 'ini');
		$extended = JForm::getInstance('extended', $xmlfile, array('control'=> 'extended'), false, '/config');
		$extended->bind($jRegistry);
		
		$this->extended = $extended;

    $this->extended2 = sportsmanagementHelper::getExtended($match->extended, 'match');
    $this->formation1 = $this->extended2->getValue('formation1');
    $this->formation2 = $this->extended2->getValue('formation2');
    
    if ( !$this->formation1 )
    {
        $this->formation1 = '4231';
    }
    if ( !$this->formation2 )
    {
        $this->formation2 = '4231';
    }
   
    $schemahome = $this->schemahome = $this->model->getPlaygroundSchema($this->formation1,'heim');
    $schemaaway = $this->schemaaway = $this->model->getPlaygroundSchema($this->formation2,'gast');
    
if ( $this->config['show_pictures'] )
	  {
/**
 * die bilder zum spiel
 */
		$dest = JPATH_ROOT.'/images/com_sportsmanagement/database/matchreport/'.$this->match->id;
		$folder = 'matchreport/'.$this->match->id;
		$images = $this->model->getMatchPictures($folder);
		if ( $images )
		{
    $this->matchimages = $images;
		}
		
	  }    

/**
 * Set page title
 */
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_PAGE_TITLE' );
		if (( isset( $this->team1 ) ) AND (isset( $this->team1 )))
		{
			$pageTitle .= ": ".$this->team1->name." ".JText::_( "COM_SPORTSMANAGEMENT_NEXTMATCH_VS" )." ".$this->team2->name;
		}
		$this->document->setTitle( $pageTitle );
        $view = $this->jinput->getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$this->option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $this->document->addCustomTag($stylelink);

	}

	/**
	 * sportsmanagementViewMatchReport::showLegresult()
	 * 
	 * @param integer $team
	 * @return
	 */
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

	/**
	 * sportsmanagementViewMatchReport::showOvertimeResult()
	 * 
	 * @return
	 */
	function showOvertimeResult()
	{
		if(isset($this->match->team1_result_ot) || isset($this->match->team2_result_ot))
		{
			$result=$this->match->team1_result_ot.' : '.$this->match->team2_result_ot;
			return $result;
		}
		return false;
	}

	/**
	 * sportsmanagementViewMatchReport::showShotoutResult()
	 * 
	 * @return
	 */
	function showShotoutResult()
	{
		if(isset($this->match->team1_result_so) || isset($this->match->team2_result_so))
		{
			$result=$this->match->team1_result_so.' : '.$this->match->team2_result_so;
			return $result;
		}
		return false;
	}

	/**
	 * sportsmanagementViewMatchReport::showMatchresult()
	 * 
	 * @param mixed $decision
	 * @param mixed $team
	 * @return
	 */
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

	/**
	 * sportsmanagementViewMatchReport::showSubstitution()
	 * 
	 * @param mixed $sub
	 * @return
	 */
	function showSubstitution($sub)
	{
		$pic_time='images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/playtime.gif';
		$pic_out='images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/out.png';
		$pic_in='images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/in.png';

		$result='<b>'.$sub->in_out_time.'. '. JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_MINUTE') .'</b>';
		$result .= '<br />';
		$outName = sportsmanagementHelper::formatName(null, $sub->out_firstname, $sub->out_nickname, $sub->out_lastname, $this->config["name_format"]);
		if($outName != '') {
			$imgTitle=JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_WENT_OUT');
			$imgTitle2=array(' title' => $imgTitle);
			$result .= JHtml::image($pic_out,$imgTitle,$imgTitle2).'&nbsp;';

			$isFavTeam = in_array( $sub->team_id, explode(",",$this->project->fav_team));

			if ( ($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)) )
			{
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->id;
$routeparameter['tid'] = $sub->team_id;
$routeparameter['pid'] = $sub->out_person_id;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter);			
                $result .= JHtml::link($link,$outName);
			} 
            else 
            {
			    $result .= $outName;
			}

			if($sub->out_position!='') {
				$result .= '&nbsp;('.JText::_($sub->out_position).')';
			}
			$result .= '<br />';
		}
		$inName = sportsmanagementHelper::formatName(null, $sub->firstname, $sub->nickname, $sub->lastname, $this->config["name_format"]);
		if($inName!='') {
			$imgTitle=JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_CAME_IN');
			$imgTitle2=array(' title' => $imgTitle);
			$result .= JHtml::image($pic_in,$imgTitle,$imgTitle2).'&nbsp;';

			$isFavTeam = in_array( $sub->team_id, explode(",",$this->project->fav_team));

			if ( ($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)) )
			{
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->id;
$routeparameter['tid'] = $sub->team_id;
$routeparameter['pid'] = $sub->person_id;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter);			    
                $result .= JHtml::link($link,$inName);
			} 
            else 
            {
			    $result .= $inName;
			}

			if($sub->in_position!='') {
				$result .= '&nbsp;('.JText::_($sub->in_position).')';
			}
			$result .= '<br /><br />';
		}
		return $result;
	}

	/**
	 * sportsmanagementViewMatchReport::showEvents()
	 * 
	 * @param integer $eventid
	 * @param integer $projectteamid
	 * @return
	 */
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

                    $match_player = sportsmanagementHelper::formatName($prefix, $me->firstname1, $me->nickname1, $me->lastname1, $this->config["name_format"]);
                        if ($this->config['event_link_player'] == 1 && $me->playerid != 0)
                        {
                            $player_link=sportsmanagementHelperRoute::getPlayerRoute($this->project->slug,$me->team_id,$me->playerid);
                            $match_player = JHtml::link($player_link,$match_player);
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

	/**
	 * sportsmanagementViewMatchReport::getTimelineMatchTime()
	 * 
	 * @return
	 */
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

	/**
	 * sportsmanagementViewMatchReport::getEventsTimes()
	 * 
	 * @return
	 */
	function getEventsTimes()
	{
		$eventstimecounter = array();

		foreach ($this->matchevents AS $me)
		{
			$eventstimecounter[] = $me->event_time;
		}
		return $eventstimecounter;
	}

	/**
	 * sportsmanagementViewMatchReport::showSubstitution_Timelines1()
	 * 
	 * @param integer $sub
	 * @return
	 */
	function showSubstitution_Timelines1($sub=0)
	{
		$result='';
		$substitutioncounter = array();
		$eventstimecounter = $this->getEventsTimes();
		foreach ( $this->substitutes as $sub ) {
			if ($sub->ptid == $this->match->projectteam1_id) {
				if (in_array($sub->in_out_time, $eventstimecounter) || in_array($sub->in_out_time, $substitutioncounter))
				{
					$result .= sportsmanagementViewMatchReport::_formatTimelineSubstitution($sub,$sub->firstname,$sub->nickname,$sub->lastname,$sub->out_firstname,$sub->out_nickname,$sub->out_lastname,1);
				}
				else {
					$result .= sportsmanagementViewMatchReport::_formatTimelineSubstitution($sub,$sub->firstname,$sub->nickname,$sub->lastname,$sub->out_firstname,$sub->out_nickname,$sub->out_lastname,0);
				}
				$substitutioncounter[] = $sub->in_out_time;
			}
		}
		return $result;
	}

	/**
	 * sportsmanagementViewMatchReport::showSubstitution_Timelines2()
	 * 
	 * @param integer $sub
	 * @return
	 */
	function showSubstitution_Timelines2($sub=0)
	{
		$result='';
		$substitutioncounter = array();
		$eventstimecounter = $this->getEventsTimes();
		foreach ( $this->substitutes as $sub ) {
			if ($sub->ptid == $this->match->projectteam2_id) {
				if (in_array($sub->in_out_time, $eventstimecounter) || in_array($sub->in_out_time, $substitutioncounter))
				{
					$result .= sportsmanagementViewMatchReport::_formatTimelineSubstitution($sub,$sub->firstname,$sub->nickname,$sub->lastname,$sub->out_firstname,$sub->out_nickname,$sub->out_lastname,2);
				}
				else {
					$result .= sportsmanagementViewMatchReport::_formatTimelineSubstitution($sub,$sub->firstname,$sub->nickname,$sub->lastname,$sub->out_firstname,$sub->out_nickname,$sub->out_lastname,0);
				}
				$substitutioncounter[] = $sub->in_out_time;
			}
		}
		return $result;
	}

	/**
	 * sportsmanagementViewMatchReport::_formatTimelineSubstitution()
	 * 
	 * @param mixed $sub
	 * @param mixed $firstname
	 * @param mixed $nickname
	 * @param mixed $lastname
	 * @param mixed $out_firstname
	 * @param mixed $out_nickname
	 * @param mixed $out_lastname
	 * @param integer $two_substitutions_per_minute
	 * @return
	 */
	function _formatTimelineSubstitution($sub,$firstname,$nickname,$lastname,$out_firstname,$out_nickname,$out_lastname,$two_substitutions_per_minute=0)
	{

		$pic_out = 'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/out.png';
		$pic_in = 'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/in.png';
		$pic_time = 'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/change.png';

		$time = $sub->in_out_time;
        $matchtime = $this->getTimelineMatchTime();
        $time2 = ($time / $matchtime) *100;
		$tiptext = JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TIMELINE_SUBSTITUTION_MIN').' ';
		$tiptext .= $time;
		$tiptext .= ' ::';
		$tiptext .= sportsmanagementViewMatchReport::getHtmlImageForTips($pic_in);
		$tiptext .= sportsmanagementHelper::formatName(null, $firstname, $nickname, $lastname, $this->config["name_format"]);
		$tiptext .= ' &lt;br&gt; ';
		$tiptext .= sportsmanagementViewMatchReport::getHtmlImageForTips($pic_out);
		$tiptext .= sportsmanagementHelper::formatName(null, $out_firstname, $out_nickname, $out_lastname, $this->config["name_format"]);
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

	/**
	 * sportsmanagementViewMatchReport::showEvents_Timelines1()
	 * 
	 * @param integer $eventid
	 * @param integer $projectteamid
	 * @return
	 */
	function showEvents_Timelines1($eventid=0,$projectteamid=0)
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
	
        $result = '';
		$eventcounter = array();
		foreach ($this->eventtypes AS $event)
		{
			foreach ($this->matchevents AS $me)
			{
				if ( $me->event_type_id == $event->id && $me->ptid == $this->match->projectteam1_id )
				{
                    
                    $placeholder = sportsmanagementHelper::getDefaultPlaceholder("player");
/**
 * set teamplayer picture
 */
					if ( ($me->tppicture1 != $placeholder) && (!empty($me->tppicture1)) )
					{
						$picture = $me->tppicture1;
                        if ( !JFile::exists(JPATH_SITE.DS.$picture) )
				        {
                        $picture = $placeholder;
                        }
                        
					}
/**
 * when teamplayer picture is empty or a placeholder icon look for the general player picture
 */
					elseif
					(	( ($me->tppicture1 == $placeholder) || (empty($me->tppicture1)) ) &&
						( ($me->picture1 != $placeholder) && (!empty($me->picture1)) )
					)
					{
						$picture = $me->picture1;
                        if ( !JFile::exists(JPATH_SITE.DS.$picture) )
				        {
                        $picture = $placeholder;
                        }
					}
					else {
                        $picture = $placeholder;
					}

					if (in_array($me->event_time, $eventcounter))
					{
						$result .= sportsmanagementViewMatchReport::_formatTimelineEvent($me,$event,$me->firstname1,$me->nickname1,$me->lastname1,$picture,1);
					}
					else {
						$result .= sportsmanagementViewMatchReport::_formatTimelineEvent($me,$event,$me->firstname1,$me->nickname1,$me->lastname1,$picture,0);
					}
					$eventcounter[] = $me->event_time;
				}
			}
		}
		return $result;
	}

	/**
	 * sportsmanagementViewMatchReport::showEvents_Timelines2()
	 * 
	 * @param integer $eventid
	 * @param integer $projectteamid
	 * @return
	 */
	function showEvents_Timelines2($eventid=0,$projectteamid=0)
	{
		$result = '';
		$eventcounter = array();
		foreach ($this->eventtypes AS $event)
		{
			foreach ($this->matchevents AS $me)
			{
				if ( $me->event_type_id == $event->id && $me->ptid == $this->match->projectteam2_id )
				{
					$placeholder = sportsmanagementHelper::getDefaultPlaceholder("player");
/**
 * set teamplayer picture
 */
					if ( ($me->tppicture1 != $placeholder) && (!empty($me->tppicture1)) )
					{
						$picture = $me->tppicture1;
                        if ( !JFile::exists(JPATH_SITE.DS.$picture) )
				        {
                        $picture = $placeholder;
                        }
                        
					}
/**
 * when teamplayer picture is empty or a placeholder icon look for the general player picture
 */
					elseif
					(	( ($me->tppicture1 == $placeholder) || (empty($me->tppicture1)) ) &&
						( ($me->picture1 != $placeholder) && (!empty($me->picture1)) )
					)
					{
						$picture = $me->picture1;
                        if ( !JFile::exists(JPATH_SITE.DS.$picture) )
				        {
                        $picture = $placeholder;
                        }
					}
					else {
                        $picture = $placeholder;
					}

					if (in_array($me->event_time, $eventcounter))
					{
						$result .= sportsmanagementViewMatchReport::_formatTimelineEvent($me,$event,$me->firstname1,$me->nickname1,$me->lastname1,$picture,2);
					}
					else {
						$result .= sportsmanagementViewMatchReport::_formatTimelineEvent($me,$event,$me->firstname1,$me->nickname1,$me->lastname1,$picture,0);
					}
					$eventcounter[] = $me->event_time;
				}
			}
		}
		return $result;
	}

	/**
	 * sportsmanagementViewMatchReport::_formatTimelineEvent()
	 * 
	 * @param mixed $matchEvent
	 * @param mixed $event
	 * @param mixed $firstname
	 * @param mixed $nickname
	 * @param mixed $lastname
	 * @param mixed $picture
	 * @param integer $two_events_per_minute
	 * @return
	 */
	function _formatTimelineEvent($matchEvent,$event,$firstname,$nickname,$lastname,$picture,$two_events_per_minute=0)
	{
		$result='';
		if(empty($event->icon)) {
			$event->icon = JUri::Base() . "media/com_sportsmanagement/jl_images/same.png";
		}
		$tiptext=JText::_($event->name).' '.JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_MINUTE_SHORT').' '.$matchEvent->event_time;
		$tiptext .= ' ::';
		if (file_exists($picture))
		{
			$tiptext .= sportsmanagementViewMatchReport::getHtmlImageForTips($picture,
																		$this->config['player_picture_width'],
																		'auto');
		}
		$tiptext .= '&lt;br /&gt;'.sportsmanagementHelper::formatName(null, $firstname, $nickname, $lastname, $this->config["name_format"]);
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

	/**
	 * sportsmanagementViewMatchReport::getHtmlImageForTips()
	 * 
	 * @param mixed $picture
	 * @param integer $width
	 * @param integer $height
	 * @return
	 */
	function getHtmlImageForTips($picture,$width=0,$height=0)
	{
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
