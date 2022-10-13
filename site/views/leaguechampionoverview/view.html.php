<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage leaguechampionoverview
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementViewleaguechampionoverview
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2020
 * @version $Id$
 * @access public
 */
class sportsmanagementViewleaguechampionoverview extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewleaguechampionoverview::init()
	 * 
	 * @return void
	 */
	function init()
	{
	   $this->leaguechampions = array();
       $this->teamseason = array();
       $this->leagueteamchampions = array();
       
       $mdlRankingAllTime = BaseDatabaseModel::getInstance("RankingAllTime", "sportsmanagementModel");
       $mdlProject = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
       $mdlTeaminfo = BaseDatabaseModel::getInstance("TeamInfo", "sportsmanagementModel");
       $mdlClubinfo = BaseDatabaseModel::getInstance("ClubInfo", "sportsmanagementModel");
       
		$this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');
		$this->projectids     = $mdlRankingAllTime->getAllProject(1);
		$this->projectnames   = $mdlRankingAllTime->getAllProjectNames(1);
        
        foreach ($this->projectids as $this->count_i => $this->project_id)
		{
		$mdlProject::$projectid = $this->project_id;
        $project = $mdlProject::getProject();
        /** aus performancegründen lesen wir den tabellenplatz direkt vom projektteam aus */
          if ( ComponentHelper::getParams('com_sportsmanagement')->get('force_ranking_cache', 0) )
			{
			$this->currentRanking = $this->model->getProjectWinner($this->project_id); 
             }
             else
             {
    	$rankinghelper = JSMRanking::getInstance($project, 0);
		$rankinghelper->setProjectId($project->id, 0);
          //echo '<pre>'.print_r($project,true).'</pre>';
          
        $mdlRanking = BaseDatabaseModel::getInstance("Ranking", "sportsmanagementModel");
		//echo '<pre>'.print_r($this->project_id,true).'</pre>';
        $mdlRanking::$projectid = $this->project_id;
        $mdlRanking::$round = $project->current_round;
        $mdlRanking::$currentRanking = array();
        $mdlRanking::computeRanking(0);
        $currentRanking = $mdlRanking::$currentRanking;
        $this->currentRanking = $this->model->_sortRanking($currentRanking[0]);            
                }
        
	
        foreach ($this->currentRanking as $this->count_i => $this->champion)
		{
        
          switch ( $this->champion->rank )
          {
          case 1:
//        echo '<pre>'.print_r($project->season_name,true).'</pre>';    
//        echo '<pre>'.print_r($this->champion->_name,true).'</pre>';
        $object = new stdClass;
		$object->teamname = $this->champion->_name;
        $object->ptid_slug = $this->champion->ptid_slug;
        $object->ptid = $this->champion->_ptid;
        $object->teamid = $this->champion->_teamid;
		$object->project_id = $project->slug;
        $object->project_count_matches = $mdlProject::getProjectCountMatches($project->id);
        
        if ( $this->champion->club_id )
        {
        $object->clubid = $this->champion->club_id;    
        }
        else
        {
        $mdlTeaminfo::$team = null;
        $teaminfo = $mdlTeaminfo::getTeam(0,$this->champion->_teamid);
        $object->clubid = $teaminfo->club_id;
        }
        
        if ( $this->champion->logo_big )
        {
        $object->logo_big = $this->champion->logo_big;    
        }
        else
        {
        $mdlClubinfo::$club = null;
        $clubinfo = $mdlClubinfo::getClub(0,$teaminfo->club_id);
		$object->logo_big = $clubinfo->logo_big;
        }
			  
        
			  
        /** welche saison zu welchem team */
        $this->teamseason[$object->teamid]['season'][] = $project->season_name;
        /** wenn nötig, array variabel initialisieren */
        if (empty($this->teamseason[$object->teamid]['title'])) {
        $this->teamseason[$object->teamid]['title'] = 0;
        }
        $this->teamseason[$object->teamid]['title'] += 1;
        /** in welcher saison hat welches team gewonnen */
        $this->leaguechampions[$project->season_name] = $object;
        /** team details */
        if ( !array_key_exists($object->teamid, $this->leagueteamchampions) ) {
        $this->leagueteamchampions[$object->teamid] = $object;
        }
         break;
        }
        
        }
       
         
        }
        
      
      //echo '<pre>'.print_r($this->projectnames,true).'</pre>';
      
        /** jetzt noch die saisons, bei denen der sieger nicht bekannt ist */
        foreach ($this->projectnames as $this->count_i => $this->project_id)
		{
          
		if ( !array_key_exists($this->project_id->seasonname, $this->leaguechampions) ) {
		$object = new stdClass;
		$object->teamname = $this->project_id->projectinfo;
        $object->ptid_slug = '';
        $object->ptid = 0;
        $object->teamid = 0;
		$object->project_id = $this->project_id->project_slug;  
        $object->project_count_matches = $mdlProject::getProjectCountMatches($this->project_id->id);
        $this->leaguechampions[$this->project_id->seasonname] = $object;
        }  

        }
      
      
      //echo '<pre>'.print_r($this->leaguechampions,true).'</pre>';
      
      
      

        $this->teamstotal = array();
		$total = array();

		foreach ((array) $this->teamseason as $rows => $value)
		{
				$this->teamstotal[$rows]['team_id'] = $rows;
				$this->teamstotal[$rows]['total']   = $value['title'];
		}
        /** Hole eine Liste von Spalten */
		foreach ($this->teamstotal as $key => $row)
		{
			$total[$key] = $row['total'];
		}

		array_multisort($total, SORT_DESC, $this->teamstotal);
        //echo '<pre>'.print_r($this->teamstotal,true).'</pre>';      
      
        // ksort($this->leaguechampions);
		krsort($this->leaguechampions);
		

		/** Set page title */
		$pageTitle = Text::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE');
		$this->document->setTitle($pageTitle);
		$this->warnings = $mdlProject::$projectwarnings;
        $this->tips = $mdlProject::$projecttips;
        $this->notes = $mdlProject::$projectnotes;
$this->notes = array_merge($this->notes, $mdlRankingAllTime::$rankingalltimenotes);
      $this->tips = array_merge($this->tips, $mdlRankingAllTime::$rankingalltimetips);
      $this->warnings = array_merge($this->warnings, $mdlRankingAllTime::$rankingalltimewarnings);
	}

}

