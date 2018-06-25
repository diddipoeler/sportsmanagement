<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage ical
 */

defined('_JEXEC') or die('Restricted access');
//require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'results.php');
require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'nextmatch.php');

/**
 * sportsmanagementViewical
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class sportsmanagementViewical extends sportsmanagementView
{

	/**
	 * sportsmanagementViewTeamPlan::init()
	 * 
	 * @return void
	 */
	function init()
	{
	//$mdlJSMResults = JModelLegacy::getInstance("results", "sportsmanagementModel");
	//$this->matches = $mdlJSMResults->getMatches($this->jinput->getInt('cfg_which_database',0)); 
		
	$this->matches = $this->model->getResultsPlan($this->jinput->getInt('p',0),$this->jinput->getInt('tid',0),0,0,'ASC',$this->jinput->getInt('cfg_which_database',0)); 	
    $mdlJSMNextMatch = JModelLegacy::getInstance("nextmatch", "sportsmanagementModel");
    $this->teams = $mdlJSMNextMatch->getTeamsFromMatches( $this->matches );;


$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' matches<br><pre>'.print_r($this->matches,true).'</pre>'),'');
$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($this->teams,true).'</pre>'),'');

    
    // create a new calendar instance
	$v = new vcalendar();
    
    foreach($this->matches as $match)
		{
			$hometeam = $this->teams[$match->projectteam1_id];
			$home = sprintf('%s', $hometeam->name);
			$guestteam = $this->teams[$match->projectteam2_id];
			$guest = sprintf('%s', $guestteam->name);
			$summary =  $match->project_name.': '.$home.' - '.$guest;
			//  check if match gots a date, if not it will not be included in ical
			if ($match->match_date)
			{
				$totalMatchTime = isset( $this->project ) ? ($this->project->halftime * ($this->project->game_parts - 1)) + $this->project->game_regular_time : 90;
				sportsmanagementHelper::convertMatchDateToTimezone($match);
				$start = sportsmanagementHelper::getMatchStartTimestamp($match, 'Y-m-d H:i:s');
				$end = sportsmanagementHelper::getMatchEndTimestamp($match, $totalMatchTime, 'Y-m-d H:i:s');
				// check if exist a playground in match or team or club
				$stringlocation	= '';
				$stringname	= '';
				if ( !empty($match->playground_id) )
				{
					$stringlocation = $match->playground_address.", ".$match->playground_zipcode." ".$match->playground_city;
					$stringname = $match->playground_name;
				}
				elseif ( !empty($match->team_playground_id) )
				{
					$stringlocation = $match->team_playground_address.", ".$match->team_playground_zipcode." ".$match->team_playground_city;
					$stringname = $match->team_playground_name;
				}
				elseif ( !empty($match->club_playground_id) )
				{
					$stringlocation= $match->club_playground_address.", ".$match->club_playground_zipcode." ".$match->club_playground_city;
					$stringname= $match->club_playground_name;
				}
				$location = $stringlocation;
				//if someone want to insert more in description here is the place
				$description = $stringname;
				// create an event and insert it in calendar
				$vevent = new vevent();
				$timezone = $this->project->timezone;
				$vevent->setProperty( "dtstart", $start, array( "TZID" => $timezone ));
				$vevent->setProperty( "dtend", $end, array( "TZID" => $timezone ));
				$vevent->setProperty( 'LOCATION', $location );
				$vevent->setProperty( 'summary', $summary );
				$vevent->setProperty( 'description', $description );
				$v->setComponent ( $vevent );
			}
    
    }
		$v->setProperty( "X-WR-TIMEZONE", $timezone );
		$xprops = array( "X-LIC-LOCATION" => $timezone );
		iCalUtilityFunctions::createTimezone( $v, $timezone, $xprops );
		$v->returnCalendar();
  
           
    }   

}
?>
