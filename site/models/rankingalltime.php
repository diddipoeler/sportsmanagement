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


jimport('joomla.application.component.model');
jimport('joomla.utilities.array');
jimport('joomla.utilities.arrayhelper');

//require_once( JLG_PATH_SITE . DS . 'extensions' . DS . 'rankingalltime' . DS. 'helpers' . DS . 'rankingalltime.php' );

// require_once (JLG_PATH_SITE . DS . 'models' . DS . 'project.php');

//$classname = 'sportsmanagementModelRanking';
//if (!class_exists($classname))
//{
//$file = JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'ranking.php';
//if (file_exists($file))
//{
//require_once($file);
//}
//}


$maxImportTime = 480;
error_reporting(0);
if ((int)ini_get('max_execution_time') < $maxImportTime) {
    @set_time_limit($maxImportTime);
}

/**
 * sportsmanagementModelRankingAllTime
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementModelRankingAllTime extends JModelLegacy
{

    var $teams = array();
    var $_teams = array();
    var $_matches = array();
    var $alltimepoints = '';
    var $debug_info = false;
    var $projectid = 0;
    var $leagueid = 0;
    var $classname = 'sportsmanagementModelRanking';
    
    /**
     * ranking parameters
     * @var array
     */
    var $_params = null;
    /**
     * criteria for ranking order
     * @var array
     */
    var $_criteria = null;


    /**
     * sportsmanagementModelRankingAllTime::__construct()
     * 
     * @return void
     */
    function __construct()
    {
        $app = JFactory::getApplication();
        $this->alltimepoints = JRequest::getVar("points", 0);
        
        if (!class_exists($this->classname))
        {
        $file = JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'ranking.php';
        if (file_exists($file))
        {
        require_once($file);
        }
        }
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' classname<br><pre>'.print_r($this->classname,true).'</pre>'),'Notice');
        
        $menu = JMenu::getInstance('site');
        $item = $menu->getActive();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' menu<br><pre>'.print_r($menu,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' item<br><pre>'.print_r($item,true).'</pre>'),'Notice');
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' item<br><pre>'.print_r($item->query['view'],true).'</pre>'),'Notice');
        
        $params = $menu->getParams($item->id);

        //$menu = &JSite::getMenu();
        //$show_debug_info = JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0);
		
        if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $this->debug_info = true;
        } else {
            $this->debug_info = false;
        }
        
        if ( $item->query['view'] == 'rankingalltime' )
        {
        // diddipoeler
        // menueeintrag vorhanden    

$registry = new JRegistry();
$registry->loadArray($params);
//$newparams = $registry->toString('ini');
$newparams = $registry->toArray();
//echo "<b>menue newparams</b><pre>" . print_r($newparams, true) . "</pre>";  
foreach ($newparams['data'] as $key => $value ) {
            
            $this->_params[$key] = $value;
        }
        
        }
        else
        {
        //$strXmlFile = JPATH_SITE.DS.JSM_PATH.DS.'settings'.DS.'default'.DS.'rankingalltime.xml';    
        $strXmlFile = JPATH_SITE.DS.JSM_PATH.DS.'views'.DS.'rankingalltime'.DS.'tmpl'.DS.'default.xml';
        //$xml = simplexml_load_file($strXmlFile);
        
        $xml = JFactory::getXMLParser( 'Simple' );
        $xml->loadFile($strXmlFile);
        $children = $xml->document->children();
        
        //$positions = $xml->document->field;
        
        // We can now step through each element of the file 
foreach( $xml->document->fields as $field ) 
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' field<br><pre>'.print_r($field->attributes() ,true).'</pre>'),'Notice');
foreach( $field->fieldset  as $fieldset ) 
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' fieldset<br><pre>'.print_r($fieldset->attributes(),true).'</pre>'),'Notice');

foreach( $fieldset->field  as $param ) 
{
$attributes = $param->attributes();
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' fieldset<br><pre>'.print_r($param->attributes(),true).'</pre>'),'Notice');
$this->_params[$attributes['name']] = $attributes['default'];

}

}    

   }
        
}        


//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _params<br><pre>'.print_r($this->_params,true).'</pre>'),'Notice');
        
        parent::__construct();

    }

   
    /**
     * sportsmanagementModelRankingAllTime::getAllTeamsIndexedByPtid()
     * 
     * @param mixed $project_ids
     * @return
     */
    function getAllTeamsIndexedByPtid($project_ids)
    {
        $app = JFactory::getApplication();
        $result = $this->getAllTeams($project_ids);
        
        $count_teams = count($result);
    $app->enqueueMessage(JText::_('Wir verarbeiten '.$count_teams.' Vereine !'),'');

        if (count($result)) {
            foreach ($result as $r) {
                $this->teams[$r->team_id] = $r;
                $this->teams[$r->team_id]->cnt_matches = 0;
                $this->teams[$r->team_id]->sum_points = $r->points_finally;
                $this->teams[$r->team_id]->neg_points = $r->neg_points_finally;

                $this->teams[$r->team_id]->cnt_won_home = 0;
                $this->teams[$r->team_id]->cnt_draw_home = 0;
                $this->teams[$r->team_id]->cnt_lost_home = 0;

                $this->teams[$r->team_id]->cnt_won = 0;
                $this->teams[$r->team_id]->cnt_draw = 0;
                $this->teams[$r->team_id]->cnt_lost = 0;

                $this->teams[$r->team_id]->sum_team1_result = 0;
                $this->teams[$r->team_id]->sum_team2_result = 0;
                $this->teams[$r->team_id]->sum_away_for = 0;
                $this->teams[$r->team_id]->diff_team_results = 0;

                $this->teams[$r->team_id]->round = 0;
                $this->teams[$r->team_id]->rank = 0;

            }
        }
       

        return $this->teams;
    }
    
    /**
     * sportsmanagementModelRankingAllTime::getAllTeams()
     * 
     * @param mixed $project_ids
     * @return
     */
    function getAllTeams($project_ids)
    {
$option = JRequest::getCmd('option');
	$app = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->clear();
        
        
        $query->select('tl.id AS projectteamid,tl.division_id');
        $query->select('tl.standard_playground,tl.admin,tl.start_points');
        $query->select('tl.use_finally,tl.points_finally,tl.neg_points_finally,tl.matches_finally,tl.won_finally,tl.draws_finally,tl.lost_finally,tl.homegoals_finally,tl.guestgoals_finally,tl.diffgoals_finally');
        $query->select('tl.is_in_score,tl.info,st.team_id,tl.checked_out,tl.checked_out_time');
        $query->select('tl.picture,tl.project_id');
        $query->select('t.id,t.name,t.short_name,t.middle_name,t.notes,t.club_id');
        $query->select('c.email as club_email,c.logo_small,c.logo_middle,c.logo_big,c.country, c.website');
        $query->select('d.name AS division_name,d.shortname AS division_shortname,d.parent_id AS parent_division_id');
        $query->select('plg.name AS playground_name,plg.short_name AS playground_short_name');
        
        $query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
        $query->select('CONCAT_WS( \':\', t.id, t.alias ) AS team_slug');
        $query->select('CONCAT_WS( \':\', d.id, d.alias ) AS division_slug');
        $query->select('CONCAT_WS( \':\', c.id, c.alias ) AS club_slug');

$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team tl' );
$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.id = tl.team_id'); 
$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team t ON st.team_id = t.id' );
$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_club c ON t.club_id = c.id' );
$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_division d ON d.id = tl.division_id' );
$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_playground plg ON plg.id = tl.standard_playground' );
$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = tl.project_id' );
            
$query->where('tl.project_id IN (' . $project_ids . ')' );
//$query->group('tl.team_id' );
$query->group('st.team_id' );
            

        $db->setQuery($query);
        $this->_teams = $db->loadObjectList();
        
        if ( !$this->_teams )
        {
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        }
        
        
        
        //foreach ( $this->_teams as $team )
//    {
//    if ( $team->use_finally ) 
//			{
//				$team->sum_points = $team->points_finally;
//				$team->neg_points = $team->neg_points_finally;
//				$team->cnt_matches = $team->matches_finally;
//				$team->cnt_won = $team->won_finally;
//				$team->cnt_draw = $team->draws_finally;
//				$team->cnt_lost = $team->lost_finally;
//				$team->sum_team1_result = $team->homegoals_finally;
//				$team->sum_team2_result = $team->guestgoals_finally;
//				$team->diff_team_results = $team->diffgoals_finally;
//			}    
//        
//        
//    }

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->_teams,true).'</pre>'),'Error');

        return $this->_teams;

    }
    
    /**
     * sportsmanagementModelRankingAllTime::getAllMatches()
     * 
     * @param mixed $projects
     * @return
     */
    function getAllMatches($projects)
    {
        $option = JRequest::getCmd('option');
	$app = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->clear();
        
        $query->select('m.id,m.projectteam1_id,m.projectteam2_id,m.team1_result AS home_score,m.team2_result AS away_score,m.team1_bonus AS home_bonus,m.team2_bonus AS away_bonus');
		$query->select('m.team1_legs AS l1,m.team2_legs AS l2,m.match_result_type AS match_result_type,m.alt_decision as decision,m.team1_result_decision AS home_score_decision');
		$query->select('m.team2_result_decision AS away_score_decision,m.team1_result_ot AS home_score_ot,m.team2_result_ot AS away_score_ot,m.team1_result_so AS home_score_so,m.team2_result_so AS away_score_so');
        $query->select('t1.id AS team1_id,t2.id AS team2_id');
		$query->select('r.id as roundid, m.team_won, r.roundcode');
        
        
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match m');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON m.projectteam1_id = pt1.id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st1 ON st1.id = pt1.team_id'); 
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team t1 ON st1.team_id = t1.id '); 
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON m.projectteam2_id = pt2.id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st2 ON st2.id = pt2.team_id'); 
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team t2 ON st2.team_id = t2.id '); 
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON m.round_id = r.id ');
        
        $query->where('((m.team1_result IS NOT NULL AND m.team2_result IS NOT NULL) OR (m.alt_decision=1))');
        $query->where('m.published = 1');
        $query->where('r.published = 1');
        $query->where('pt1.project_id IN ('.$projects.')');
        $query->where('(m.cancel IS NULL OR m.cancel = 0)');
        $query->where('m.projectteam1_id > 0 AND m.projectteam2_id > 0');
    
    $db->setQuery($query);
    $res = $db->loadObjectList();  
    
    if ( !$res )
        {
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        }
              
    $this->_matches = $res;
    
    $count_matches = count($res);
    $app->enqueueMessage(JText::_('Wir verarbeiten '.$count_matches.' Spiele !'),'');
           
    return $res;    
    }
    
    /**
     * sportsmanagementModelRankingAllTime::getAllTimeRanking()
     * 
     * @return
     */
    function getAllTimeRanking()
    {
        $option = JRequest::getCmd('option');
	$app = JFactory::getApplication();
    
    $arr = explode(",",$this->alltimepoints);
    
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' alltimepoints<br><pre>'.print_r($arr,true).'</pre>'),'');
    
    foreach ((array)$this->_matches as $match)
		{
		$resultType = $match->match_result_type;
		$decision = $match->decision;
		if ($decision == 0)
			{
				$home_score = $match->home_score;
				$away_score = $match->away_score;
				$leg1 = $match->l1;
				$leg2 = $match->l2;
			}
			else
			{
				$home_score = $match->home_score_decision;
				$away_score = $match->away_score_decision;
				$leg1 = 0;
				$leg2 = 0;
			}
            
		$homeId = $match->team1_id;
		$awayId = $match->team2_id;
    $home = &$this->teams[$homeId];
    $away = &$this->teams[$awayId];
    
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' home<br><pre>'.print_r($home,true).'</pre>'),'');
    
    $home->cnt_matches++;
		$away->cnt_matches++;
    
    $win_points  = (isset($arr[0])) ? $arr[0] : 3;
			$draw_points = (isset($arr[1])) ? $arr[1] : 1;
			$loss_points = (isset($arr[2])) ? $arr[2] : 0;
    
    if ( $loss_points )
    {
    $shownegpoints = 1;
    }
    else
    {
    $shownegpoints = 0;
    }
    
    
			$home_ot = $match->home_score_ot;
			$away_ot = $match->away_score_ot;			
			$home_so = $match->home_score_so;
			$away_so = $match->away_score_so;
		
    if ($decision!=1) {
				if( $home_score > $away_score )
				{
					switch ($resultType) 
					{
					case 0: 
						$home->cnt_won++; 
						$home->cnt_won_home++; 
						
						$away->cnt_lost++; 
						$away->cnt_lost_away++;	
						break;
					case 1: 
						$home->cnt_wot++; 
						$home->cnt_wot_home++; 
						$home->cnt_won++; 
						$home->cnt_won_home++; 
						
						$away->cnt_lot++; 
						$away->cnt_lot_away++; 
						//When LOT, LOT=1 but No LOSS Count(Hockey)
						//$away->cnt_lost++; 
						//$away->cnt_lost_home++; 						
						break;
					case 2: 
						$home->cnt_wso++; 
						$home->cnt_wso_home++;
						$home->cnt_won++; 
						$home->cnt_won_home++; 
						
						$away->cnt_lso++; 
						$away->cnt_lso_away++; 
						$away->cnt_lot++; 
						$away->cnt_lot_away++; 		
						//When LSO ,LSO=1 and LOT=1 but No LOSS Count (Hockey)
						//$away->cnt_lost++; 
						//$away->cnt_lost_home++; 							
						break;
					}				
					
					$home->sum_points += $win_points; //home_score can't be null...						
					$away->sum_points += ( $decision == 0 || isset($away_score) ? $loss_points : 0);

					if ( $shownegpoints == 1 )
					{
						$home->neg_points += $loss_points;
						$away->neg_points += ( $decision == 0 || isset($away_score) ? $win_points : 0);
					}		
				}
				else if ( $home_score == $away_score )
				{
					switch ($resultType) 
					{
					case 0: 				
						$home->cnt_draw++;
						$home->cnt_draw_home++;

						$away->cnt_draw++;
						$away->cnt_draw_away++;
					break;	
					case 1: 	
						if ( $home_ot > $away_ot)
						{
							$home->cnt_won++;
							$home->cnt_won_home++;
							$home->cnt_wot++; 
							$home->cnt_wot_home++;							

							$away->cnt_lost++;
							$away->cnt_lost_away++;
							$away->cnt_lot++; 
							$away->cnt_lot_away++;							
						}
						if ( $home_ot < $away_ot)
						{
							$away->cnt_won++;
							$away->cnt_won_home++;
							$away->cnt_wot++; 
							$away->cnt_wot_home++;							

							$home->cnt_lost++;
							$home->cnt_lost_away++;
							$home->cnt_lot++; 
							$home->cnt_lot_away++;							
						}						
					break;						
					case 2: 	
						if ( $home_so > $away_so)
						{
							$home->cnt_won++;
							$home->cnt_won_home++;
							$home->cnt_wso++; 
							$home->cnt_wso_home++;							

							$away->cnt_lost++;
							$away->cnt_lost_away++;
							$away->cnt_lso++; 
							$away->cnt_lso_away++;							
						}
						if ( $home_so < $away_so)
						{
							$away->cnt_won++;
							$away->cnt_won_home++;
							$away->cnt_wso++; 
							$away->cnt_wso_home++;							

							$home->cnt_lost++;
							$home->cnt_lost_away++;
							$home->cnt_lso++; 
							$home->cnt_lso_away++;							
						}						
					break;					
					}
					$home->sum_points += ( $decision == 0 || isset($home_score) ? $draw_points : 0);
					$away->sum_points += ( $decision == 0 || isset($away_score) ? $draw_points : 0);

					if ($shownegpoints==1)
					{
						$home->neg_points += ( $decision == 0 || isset($home_score) ? ($win_points-$draw_points): 0); // bug fixed, timoline 250709
						$away->neg_points += ( $decision == 0 || isset($away_score) ? ($win_points-$draw_points) : 0);// ex. for soccer, your loss = 2 points not 1 point
					}
				}
				else if ( $home_score < $away_score )
				{
					switch ($resultType) {
					case 0:   
						$home->cnt_lost++; 
						$home->cnt_lost_home++;
						
						$away->cnt_won++; 
						$away->cnt_won_away++; 
						break;
					case 1:   
						$home->cnt_lot++; 
						$home->cnt_lot_home++; 
						//When LOT, LOT=1 but No LOSS Count(Hockey)
						//$home->cnt_lost++; 
						//$home->cnt_lost_home++; 
						
						$away->cnt_wot++; 
						$away->cnt_wot_away++; 
						$away->cnt_won++; 
						$away->cnt_won_away++; 						
						break;
					case 2:   
						$home->cnt_lso++; 
						$home->cnt_lso_home++; 
						$home->cnt_lot++; 
						$home->cnt_lot_home++; 
						//When LSO ,LSO=1 and LOT=1 but No LOSS Count (Hockey)
						//$home->cnt_lost++; 
						//$home->cnt_lost_home++; 	
						
						$away->cnt_wso++; 
						$away->cnt_wso_away++;
						$away->cnt_won++; 
						$away->cnt_won_away++; 						
						break;
					}					
									
					$home->sum_points += ( $decision == 0 || isset($home_score) ? $loss_points : 0);			
					$away->sum_points += $win_points;

					if ( $shownegpoints==1)
					{
						$home->neg_points += ( $decision == 0 || isset($home_score) ? $win_points : 0);
						$away->neg_points += $loss_points;
					}
				}
			} else {
				if ($shownegpoints==1)
				{
					$home->neg_points += $loss_points;
					$away->neg_points += $loss_points;
				}
				//Final Win/Loss Decision
				if($match->team_won==0) {
					$home->cnt_lost++;
					$away->cnt_lost++; 
				//record a won on the home team
				} else if($match->team_won==1) {
					$home->cnt_won++;
					$away->cnt_lost++;
					$home->sum_points += $win_points;
					$away->cnt_lost_home++;					
				//record a won on the away team
				} else if($match->team_won==2) {
					$away->cnt_won++;
					$home->cnt_lost++;
					$away->sum_points += $win_points;
					$home->cnt_lost_home++;
					//record a loss on both teams
				} else if($match->team_won==3) {
					$home->cnt_lost++;
					$away->cnt_lost++;
					$away->cnt_lost_home++;
					$home->cnt_lost_home++;
					//record a won on both teams
				} else if($match->team_won==4) {
					$home->cnt_won++;
					$away->cnt_won++;
					$home->sum_points += $win_points;
					$away->sum_points += $win_points;
						
				}
			}
			/*winpoints*/
			$home->winpoints=$win_points;
			
			/* bonus points */
			$home->sum_points += $match->home_bonus;
			$home->bonus_points += $match->home_bonus;

			$away->sum_points += $match->away_bonus;
			$away->bonus_points += $match->away_bonus;

			/* goals for/against/diff */
			$home->sum_team1_result += $home_score;
			$home->sum_team2_result += $away_score;
			$home->diff_team_results = $home->sum_team1_result - $home->sum_team2_result;
			$home->sum_team1_legs   += $leg1;
			$home->sum_team2_legs   += $leg2;
			$home->diff_team_legs    = $home->sum_team1_legs - $home->sum_team2_legs;

			$away->sum_team1_result += $away_score;
			$away->sum_team2_result += $home_score;
			$away->diff_team_results = $away->sum_team1_result - $away->sum_team2_result;
			$away->sum_team1_legs   += $leg2;
			$away->sum_team2_legs   += $leg1;
			$away->diff_team_legs    = $away->sum_team1_legs - $away->sum_team2_legs;

			$away->sum_away_for += $away_score;	
    
    
    }
    
    
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _teams<br><pre>'.print_r($this->_teams,true).'</pre>'),'');
    
    foreach ( $this->_teams as $team )
    {
    if ( $team->use_finally ) 
			{
				$this->teams[$team->team_id]->sum_points += $team->points_finally;
				$this->teams[$team->team_id]->neg_points += $team->neg_points_finally;
				$this->teams[$team->team_id]->cnt_matches += $team->matches_finally;
				$this->teams[$team->team_id]->cnt_won += $team->won_finally;
				$this->teams[$team->team_id]->cnt_draw += $team->draws_finally;
				$this->teams[$team->team_id]->cnt_lost += $team->lost_finally;
				$this->teams[$team->team_id]->sum_team1_result += $team->homegoals_finally;
				$this->teams[$team->team_id]->sum_team2_result += $team->guestgoals_finally;
				$this->teams[$team->team_id]->diff_team_results += $team->diffgoals_finally;
			}    
        
        
    }
    
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($this->teams,true).'</pre>'),'');
    
    return $this->teams;
     
    }    
    
    /**
     * sportsmanagementModelRankingAllTime::getColors()
     * 
     * @param string $configcolors
     * @return
     */
    function getColors($configcolors='')
	{
		$s=substr($configcolors,0,-1);

		$arr1=array();
		if(trim($s) != "")
		{
			$arr1=explode(";",$s);
		}

		$colors=array();

		$colors[0]["from"]="";
		$colors[0]["to"]="";
		$colors[0]["color"]="";
		$colors[0]["description"]="";

		for($i=0; $i < count($arr1); $i++)
		{
			$arr2=explode(",",$arr1[$i]);
			if(count($arr2) != 4)
			{
				break;
			}

			$colors[$i]["from"]=$arr2[0];
			$colors[$i]["to"]=$arr2[1];
			$colors[$i]["color"]=$arr2[2];
			$colors[$i]["description"]=$arr2[3];
		}
		return $colors;
	}
    
    /**
     * sportsmanagementModelRankingAllTime::getAllProject()
     * 
     * @return
     */
    function getAllProject()
    {
        $app = JFactory::getApplication();
        $league = JRequest::getInt("l", 0);

        if (!$league) {
            $projekt = JRequest::getInt("p", 0);
            $query = 'select league_id 
  from #__sportsmanagement_project
  where id = ' . $projekt . ' order by name ';
            $this->_db->setQuery($query);
            $league = $this->_db->loadResult();

        }

        $query = 'select id 
  from #__sportsmanagement_project
  where league_id = ' . $league . ' order by name ';
        $this->_db->setQuery($query);
        //$result = $this->_db->loadObjectList();
        $result = $this->_db->loadResultArray();
        $this->project_ids = implode(",", $result);
        $this->project_ids_array = $result;
        
        $count_project = count($result);
    $app->enqueueMessage(JText::_('Wir verarbeiten '.$count_project.' Projekte/Saisons !'),'');
    
        return $result;

    }

    /**
     * sportsmanagementModelRankingAllTime::getAllTimeParams()
     * 
     * @return
     */
    function getAllTimeParams()
    {
        return $this->_params;
    }
    
    /**
     * sportsmanagementModelRankingAllTime::getCurrentRanking()
     * 
     * @return
     */
    function getCurrentRanking()
    {
        $option = JRequest::getCmd('option');
	$app = JFactory::getApplication();
    
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($this->teams,true).'</pre>'),'');
    
        $newranking = array();


        foreach ($this->teams as $key) 
        {
            //$new = new JSMRankingalltimeTeam(0);
            $new = new JSMRankingTeam(0);
            $new->cnt_matches = $key->cnt_matches;
            $new->sum_points = $key->sum_points;
            $new->neg_points = $key->neg_points;

            $new->cnt_won_home = $key->cnt_won_home;
            $new->cnt_draw_home = $key->cnt_draw_home;
            $new->cnt_lost_home = $key->cnt_lost_home;

            $new->cnt_won = $key->cnt_won;
            $new->cnt_draw = $key->cnt_draw;
            $new->cnt_lost = $key->cnt_lost;

            $new->sum_team1_result = $key->sum_team1_result;
            $new->sum_team2_result = $key->sum_team2_result;
            $new->sum_away_for = $key->sum_away_for;
            $new->diff_team_results = $key->diff_team_results;


            $new->_is_in_score = $key->is_in_score;
            $new->_teamid = $key->team_id;
            $new->_name = $key->name;

            $new->_ptid = $key->projectteamid;
            $new->_pid = $key->project_id;

            $newranking[0][$key->team_id] = $new;

        }
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' newranking<br><pre>'.print_r($newranking,true).'</pre>'),'');

        $newranking[0] = $this->_sortRanking($newranking[0]);

        $oldpoints = 0;
        $rank = 0;
        foreach ($newranking[0] as $teamid => $row) {

            if ($oldpoints == $row->sum_points) {
                $row->rank = $rank;
                $oldpoints = $row->sum_points;
            } else {
                $rank++;
                $row->rank = $rank;
                $oldpoints = $row->sum_points;

            }


        }
//
//        if ($this->debug_info) {
//            $this->dump_header("models function getCurrentRanking");
//            $this->dump_variable("newranking", $newranking);
//        }


        // return $this->teams;
        return $newranking;

    }
    
    
    
    
    /**
     * sportsmanagementModelRankingAllTime::_sortRanking()
     * 
     * @param mixed $ranking
     * @return
     */
    function _sortRanking(&$ranking)
    {


        $order = JRequest::getVar('order', '');
        $order_dir = JRequest::getVar('dir', 'DESC');

        if (!$order) {
            $order_dir = 'DESC';
            $sortarray = array();
            

            foreach ($this->_getRankingCriteria() as $c) {

                if ($this->debug_info) {
                    $this->dump_header("models function _sortRanking");
                    $this->dump_variable("c", $c);
                }

                switch ($c) {
                    case '_cmpPoints':
                        $sortarray[sum_points] = SORT_DESC;
                        break;
                    case '_cmpPLAYED':
                        $sortarray[cnt_matches] = SORT_DESC;
                        break;
                    case '_cmpDiff':
                        $sortarray[diff_team_results] = SORT_DESC;
                        break;
                    case '_cmpFor':
                        $sortarray[sum_team1_result] = SORT_DESC;
                        break;
                    case '_cmpPlayedasc':
                        $sortarray[cnt_matches] = SORT_ASC;
                        break;
                }
                //uasort( $ranking, array("JoomleagueModelRankingalltime",$c ));

            }

            if ($this->debug_info) {
                $this->dump_header("models function _sortRanking");
                $this->dump_variable("sortarray", $sortarray);
            }

            foreach ($ranking as $row) {
                $arr2[$row->_teamid] = JArrayHelper::fromObject($row);
            }
            //$arr2 = $this->array_msort($arr2, array('sum_points'=>SORT_DESC,  'diff_team_results'=>SORT_DESC ) );
            //$sortarray2 = implode (",", $sortarray);
            //$arr2 = $this->array_msort($arr2, array($sortarray2) );
            $arr2 = $this->array_msort($arr2, $sortarray);

            if ($this->debug_info) {
                $this->dump_header("models function _sortRanking");
                $this->dump_variable("sortarray2", $sortarray2);
            }

            unset($ranking);

            foreach ($arr2 as $key => $row) 
            {
                //$ranking2[$key] = JArrayHelper::toObject($row, 'JSMRankingalltimeTeam');
                //$ranking[$key] = JArrayHelper::toObject($row, 'JSMRankingalltimeTeam');
                $ranking2[$key] = JArrayHelper::toObject($row, 'JSMRankingTeam');
                $ranking[$key] = JArrayHelper::toObject($row, 'JSMRankingTeam');
            }

            if ($this->debug_info) {

                $this->dump_header("models function _sortRanking");
                $this->dump_variable("arr2", $arr2);


                $this->dump_header("models function _sortRanking");
                $this->dump_variable("ranking2", $ranking2);

                $this->dump_header("models function _sortRanking");
                $this->dump_variable("ranking", $ranking);


            }


        } else //     if ( !$order_dir)
        {
            //     $order_dir = 'DESC';
            //     }
            switch ($order) {
                case 'played':
                    uasort($ranking, array($this->classname, "playedCmp"));
                    break;
                case 'name':
                    uasort($ranking, array($this->classname, "teamNameCmp"));
                    break;
                case 'rank':
                    break;
                case 'won':
                    uasort($ranking, array($this->classname, "wonCmp"));
                    break;
                case 'draw':
                    uasort($ranking, array($this->classname, "drawCmp"));
                    break;
                case 'loss':
                    uasort($ranking, array($this->classname, "lossCmp"));
                    break;
                case 'winpct':
                    uasort($ranking, array($this->classname, "winpctCmp"));
                    break;
                case 'quot':
                    uasort($ranking, array($this->classname, "quotCmp"));
                    break;
                case 'goalsp':
                    uasort($ranking, array($this->classname, "goalspCmp"));
                    break;
                case 'goalsfor':
                    uasort($ranking, array($this->classname, "goalsforCmp"));
                    break;
                case 'goalsagainst':
                    uasort($ranking, array($this->classname, "goalsagainstCmp"));
                    break;
                case 'legsdiff':
                    uasort($ranking, array($this->classname, "legsdiffCmp"));
                    break;
                case 'legsratio':
                    uasort($ranking, array($this->classname, "legsratioCmp"));
                    break;
                case 'diff':
                    uasort($ranking, array($this->classname, "diffCmp"));
                    break;
                case 'points':
                    uasort($ranking, array($this->classname, "pointsCmp"));
                    break;
                case 'start':
                    uasort($ranking, array($this->classname, "startCmp"));
                    break;
                case 'bonus':
                    uasort($ranking, array($this->classname, "bonusCmp"));
                    break;
                case 'negpoints':
                    uasort($ranking, array($this->classname, "negpointsCmp"));
                    break;
                case 'pointsratio':
                    uasort($ranking, array($this->classname, "pointsratioCmp"));
                    break;

                default:
                    if (method_exists($this, $order . 'Cmp')) {
                        uasort($ranking, array($this, $order . 'Cmp'));
                    }
                    break;
            }

            if ($order_dir == 'DESC') {
                $ranking = array_reverse($ranking, true);
            }

        }

        return $ranking;
        
    }

//    /**
//     * sportsmanagementModelRankingAllTime::playedCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function playedCmp(&$a, &$b)
//    {
//        $res = $a->cnt_matches - $b->cnt_matches;
//        return $res;
//    }

    
//    /**
//     * sportsmanagementModelRankingAllTime::teamNameCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function teamNameCmp(&$a, &$b)
//    {
//        return strcasecmp($a->_name, $b->_name);
//    }

//    /**
//     * sportsmanagementModelRankingAllTime::wonCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function wonCmp(&$a, &$b)
//    {
//        $res = $a->cnt_won - $b->cnt_won;
//        return $res;
//    }
//
//    /**
//     * sportsmanagementModelRankingAllTime::drawCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function drawCmp(&$a, &$b)
//    {
//        $res = ($a->cnt_draw - $b->cnt_draw);
//        return $res;
//    }
//
//    /**
//     * sportsmanagementModelRankingAllTime::lossCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function lossCmp(&$a, &$b)
//    {
//        $res = ($a->cnt_lost - $b->cnt_lost);
//        return $res;
//    }
//
//    /**
//     * sportsmanagementModelRankingAllTime::winpctCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function winpctCmp(&$a, &$b)
//    {
//        $pct_a = $a->cnt_won / ($a->cnt_won + $a->cnt_lost + $a->cnt_draw);
//        $pct_b = $b->cnt_won / ($b->cnt_won + $b->cnt_lost + $b->cnt_draw);
//        $res = ($pct_a < $pct_b);
//        return $res;
//    }
//
//    /**
//     * sportsmanagementModelRankingAllTime::quotCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function quotCmp(&$a, &$b)
//    {
//        $pct_a = $a->cnt_won / ($a->cnt_won + $a->cnt_lost + $a->cnt_draw);
//        $pct_b = $b->cnt_won / ($b->cnt_won + $b->cnt_lost + $b->cnt_draw);
//        $res = ($pct_a < $pct_b);
//        return $res;
//    }
//
//    /**
//     * sportsmanagementModelRankingAllTime::goalspCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function goalspCmp(&$a, &$b)
//    {
//        $res = ($a->sum_team1_result - $b->sum_team1_result);
//        return $res;
//    }
//
//    /**
//     * sportsmanagementModelRankingAllTime::goalsforCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function goalsforCmp(&$a, &$b)
//    {
//        $res = ($a->sum_team1_result - $b->sum_team1_result);
//        return $res;
//    }
//
//    /**
//     * sportsmanagementModelRankingAllTime::goalsagainstCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function goalsagainstCmp(&$a, &$b)
//    {
//        $res = ($a->sum_team2_result - $b->sum_team2_result);
//        return $res;
//    }
//
//    /**
//     * sportsmanagementModelRankingAllTime::legsdiffCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function legsdiffCmp(&$a, &$b)
//    {
//        $res = ($a->diff_team_legs - $b->diff_team_legs);
//        return $res;
//    }
//
//    /**
//     * sportsmanagementModelRankingAllTime::legsratioCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function legsratioCmp(&$a, &$b)
//    {
//        $res = ($a->legsRatio - $b->legsRatio);
//        return $res;
//    }
//
//    /**
//     * sportsmanagementModelRankingAllTime::diffCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function diffCmp(&$a, &$b)
//    {
//        $res = ($a->diff_team_results - $b->diff_team_results);
//        return $res;
//    }
//
//    /**
//     * sportsmanagementModelRankingAllTime::pointsCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function pointsCmp(&$a, &$b)
//    {
//        $res = ($a->getPoints() - $b->getPoints());
//        return $res;
//    }
//
//    /**
//     * sportsmanagementModelRankingAllTime::startCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function startCmp(&$a, &$b)
//    {
//        $res = ($a->team->start_points * $b->team->start_points);
//        return $res;
//    }
//
//    /**
//     * sportsmanagementModelRankingAllTime::bonusCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function bonusCmp(&$a, &$b)
//    {
//        $res = ($a->bonus_points - $b->bonus_points);
//        return $res;
//    }
//
//    /**
//     * sportsmanagementModelRankingAllTime::negpointsCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function negpointsCmp(&$a, &$b)
//    {
//        $res = ($a->neg_points - $b->neg_points);
//        return $res;
//    }
//
//    /**
//     * sportsmanagementModelRankingAllTime::pointsratioCmp()
//     * 
//     * @param mixed $a
//     * @param mixed $b
//     * @return
//     */
//    function pointsratioCmp(&$a, &$b)
//    {
//        $res = ($a->pointsRatio - $b->pointsRatio);
//        return $res;
//    }

    /**
     * sportsmanagementModelRankingAllTime::array_msort()
     * 
     * @param mixed $array
     * @param mixed $cols
     * @return
     */
    function array_msort($array, $cols)
    {
        $colarr = array();
        foreach ($cols as $col => $order) {
            $colarr[$col] = array();
            foreach ($array as $k => $row) {
                $colarr[$col]['_' . $k] = strtolower($row[$col]);
            }
        }
        $params = array();
        foreach ($cols as $col => $order) {
            $params[] = &$colarr[$col];
            $params = array_merge($params, (array )$order);
        }
        call_user_func_array('array_multisort', $params);
        $ret = array();
        $keys = array();
        $first = true;
        foreach ($colarr as $col => $arr) {
            foreach ($arr as $k => $v) {
                if ($first) {
                    $keys[$k] = substr($k, 1);
                }
                $k = $keys[$k];
                if (!isset($ret[$k]))
                    $ret[$k] = $array[$k];
                $ret[$k][$col] = $array[$k][$col];
            }
            $first = false;
        }
        return $ret;

    }

/**
 * sportsmanagementModelRankingAllTime::_getRankingCriteria()
 * 
 * @return
 */
function _getRankingCriteria()
    {
$app = JFactory::getApplication();

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _params<br><pre>'.print_r($this->_params,true).'</pre>'),'');

        if (empty($this->_criteria)) {
            // get the values from ranking template setting
            $values = explode(',', $this->_params['ranking_order']);
            $crit = array();
            foreach ($values as $v) {
                $v = ucfirst(str_replace("jl_", "", strtolower(trim($v))));
                if (method_exists($this, '_cmp' . $v)) {
                    $crit[] = '_cmp' . $v;
                } else {
                    JError::raiseWarning(0, JText::_('COM_SPORTSMANAGEMENT_RANKING_NOT_VALID_CRITERIA') . ': ' . $v);
                }
            }
            // set a default criteria if empty
            if (!count($crit)) {
                $crit[] = '_cmpPoints';
            }
            $this->_criteria = $crit;
        }

        if ($this->debug_info) {
            $this->dump_header("models function _getRankingCriteria");
            $this->dump_variable("this->_criteria", $this->_criteria);
        }

        return $this->_criteria;
    }
    
    
/*****************************************************************************
    *
    * Compare functions (callbacks for uasort)
    *
    * You can add more criteria by just adding more _cmpXxxx functions, with Xxxx
    * being the name of your criteria to be set in ranking template setting
    *
    *****************************************************************************/

    /**
     * alphanumerical comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpAlpha($a, $b)
    {
        $res = strcasecmp($a->getName(), $b->getName());
        return $res;
    }

    /**
     * Point comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpPoints($a, $b)
    {
        $res = -($a->getPoints() - $b->getPoints());
        return (int)$res;
    }

    /**
     * Bonus points comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpBonus($a, $b)
    {
        $res = -($a->bonus_points - $b->bonus_points);
        return $res;
    }

    /**
     * Score difference comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpDiff($a, $b)
    {
        $res = -($a->diff_team_results - $b->diff_team_results);
        return $res;
    }

    /**
     * Score for comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpFor($a, $b)
    {
        $res = -($a->sum_team1_result - $b->sum_team1_result);
        return $res;
    }

    /**
     * Score against comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpAgainst($a, $b)
    {
        $res = ($a->sum_team2_result - $b->sum_team2_result);
        return $res;
    }

    /**
     * Scoring average comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpScoreAvg($a, $b)
    {
        $res = -($a->scoreAvg() - $b->scoreAvg());
        return $res;
    }

    /**
     * Scoring percentage comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpScorePct($a, $b)
    {
        $res = -($a->scorePct() - $b->scorePct());
        return $res;
    }


    /**
     * Winning percentage comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpWinpct($a, $b)
    {
        $res = -($a->winPct() - $b->winPct());
        if ($res != 0)
            $res = ($res >= 0 ? 1 : -1);
        return $res;
    }

    /**
     * Gameback comparison (US sports)
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpGb($a, $b)
    {
        $res = -(($a->cnt_won - $b->cnt_won) + ($b->cnt_lost - $a->cnt_lost));
        return $res;
    }

    /**
     * Score away comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpAwayfor($a, $b)
    {
        $res = -($a->sum_away_for - $b->sum_away_for);
        return $res;
    }

    /**
     * Head to Head points comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpH2h($a, $b)
    {
        $teams = $this->_geth2h();
        // we do not include start points in h2h comparison
        $res = -($teams[$a->_ptid]->getPoints(false) - $teams[$b->_ptid]->getPoints(false));
        return $res;
    }

    /**
     * Head to Head score difference comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpH2h_diff($a, $b)
    {
        $teams = $this->_geth2h();
        return $this->_cmpDiff($teams[$a->_ptid], $teams[$b->_ptid]);
    }

    /**
     * Head to Head score for comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpH2h_for($a, $b)
    {
        $teams = $this->_geth2h();
        return $this->_cmpFor($teams[$a->_ptid], $teams[$b->_ptid]);
    }

    /**
     * Head to Head scored away comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpH2h_away($a, $b)
    {
        $teams = $this->_geth2h();
        return $this->_cmpAwayfor($teams[$a->_ptid], $teams[$b->_ptid]);
    }

    /**
     * Legs diff comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpLegs_diff($a, $b)
    {
        $res = -($a->diff_team_legs - $b->diff_team_legs);
        return $res;
    }

    /**
     * Legs ratio comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpLegs_ratio($a, $b)
    {
        $res = -($a->legsRatio() - $b->legsRatio());
        if ($res != 0)
            $res = ($res >= 0 ? 1 : -1);
        return $res;
    }

    /**
     * Legs wins comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpLegs_win($a, $b)
    {
        $res = -($a->sum_team1_legs - $b->sum_team1_legs);
        return $res;
    }

    /**
     * Total wins comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpWins($a, $b)
    {
        $res = -($a->cnt_won - $b->cnt_won);
        return $res;
    }

    /**
     * Games played comparison, more games played, higher in rank
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpPlayed($a, $b)
    {
        $res = -($a->cnt_matches - $b->cnt_matches);
        return $res;
    }

    /**
     * Games played ASC comparison, less games played, higher in rank
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpPlayedasc($a, $b)
    {
        $res = -($this->_cmpPlayed($a, $b));
        return $res;
    }
    /**
     * Points ratio comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpPoints_ratio($a, $b)
    {
        $res = -($a->pointsRatio() - $b->pointsRatio());
        if ($res != 0)
            $res = ($res >= 0 ? 1 : -1);
        return $res;
    }
    /**
     * OT_wins comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpWOT($a, $b)
    {
        $res = -($a->cnt_wot - $b->cnt_wot);
        return $res;
    }

    /**
     * SO_wins comparison
     * @param JLGRankingTeam a
     * @param JLGRankingTeam b
     * @return int
     */
    function _cmpWSO($a, $b)
    {
        $res = -($a->cnt_wso - $b->cnt_wso);
        return $res;
    }
    
    
}

///**
// * Ranking team class
// * Support class for ranking helper
// */
//class JSMRankingalltimeTeam
//{
//
//    // new for use_finally
//    var $_use_finally = 0;
//    var $_points_finally = 0;
//    var $_neg_points_finally = 0;
//    var $_matches_finally = 0;
//    var $_won_finally = 0;
//    var $_draws_finally = 0;
//    var $_lost_finally = 0;
//    var $_homegoals_finally = 0;
//    var $_guestgoals_finally = 0;
//    var $_diffgoals_finally = 0;
//
//    // new for is_in_score
//    var $_is_in_score = 0;
//
//    /**
//     * project team id
//     * @var int
//     */
//    var $_ptid = 0;
//    /**
//     * team id
//     * @var int
//     */
//    var $_teamid = 0;
//    /**
//     * division id
//     * @var int
//     */
//    var $_divisionid = 0;
//    /**
//     * start point / penalty
//     * @var int
//     */
//    var $_startpoints = 0;
//    /**
//     * team name
//     * @var string
//     */
//    var $_name = null;
//
//    var $cnt_matches = 0;
//    var $cnt_won = 0;
//    var $cnt_draw = 0;
//    var $cnt_lost = 0;
//    var $cnt_won_home = 0;
//    var $cnt_draw_home = 0;
//    var $cnt_lost_home = 0;
//    var $sum_points = 0;
//    var $neg_points = 0;
//    var $bonus_points = 0;
//    var $sum_team1_result = 0;
//    var $sum_team2_result = 0;
//    var $sum_away_for = 0;
//    var $sum_team1_legs = 0;
//    var $sum_team2_legs = 0;
//    var $diff_team_results = 0;
//    var $diff_team_legs = 0;
//    var $round = 0;
//    var $rank = 0;
//
//    /**
//     * contructor requires ptid
//     * @param int $ptid
//     */
//    function JSMRankingalltimeTeam($ptid)
//    {
//        $this->setPtid($ptid);
//    }
//
//    // new for is_in_score
//    function setis_in_score($val)
//    {
//        $this->_is_in_score = (int)$val;
//    }
//
//    // new for use finally
//    function setuse_finally($val)
//    {
//        $this->_use_finally = (int)$val;
//    }
//    function setpoints_finally($val)
//    {
//        $this->_points_finally = (int)$val;
//    }
//    function setneg_points_finally($val)
//    {
//        $this->_neg_points_finally = (int)$val;
//    }
//    function setmatches_finally($val)
//    {
//        $this->_matches_finally = (int)$val;
//    }
//    function setwon_finally($val)
//    {
//        $this->_won_finally = (int)$val;
//    }
//    function setdraws_finally($val)
//    {
//        $this->_draws_finally = (int)$val;
//    }
//    function setlost_finally($val)
//    {
//        $this->_lost_finally = (int)$val;
//    }
//    function sethomegoals_finally($val)
//    {
//        $this->_homegoals_finally = (int)$val;
//    }
//    function setguestgoals_finally($val)
//    {
//        $this->_guestgoals_finally = (int)$val;
//    }
//    function setdiffgoals_finally($val)
//    {
//        $this->_diffgoals_finally = (int)$val;
//    }
//
//    /**
//     * set project team id
//     * @param int ptid
//     */
//    function setPtid($ptid)
//    {
//        $this->_ptid = (int)$ptid;
//    }
//
//    /**
//     * set team id
//     * @param int id
//     */
//    function setTeamid($id)
//    {
//        $this->_teamid = (int)$id;
//    }
//
//    /**
//     * returns project team id
//     * @return int id
//     */
//    function getPtid()
//    {
//        return $this->_ptid;
//    }
//
//    /**
//     * returns team id
//     * @return int id
//     */
//    function getTeamid()
//    {
//        return $this->_teamid;
//    }
//
//    /**
//     * set team division id
//     * @param int val
//     */
//    function setDivisionid($val)
//    {
//        $this->_divisionid = (int)$val;
//    }
//
//    /**
//     * return team division id
//     * @return int id
//     */
//    function getDivisionid()
//    {
//        return $this->_divisionid;
//    }
//
//    /**
//     * set team start points
//     * @param int val
//     */
//    function setStartpoints($val)
//    {
//        $this->_startpoints = $val;
//    }
//
//    /**
//     * set team neg points
//     * @param int val
//     */
//    function setNegpoints($val)
//    {
//        $this->neg_points = $val;
//    }
//
//    /**
//     * set team name
//     * @param string val
//     */
//    function setName($val)
//    {
//        $this->_name = $val;
//    }
//
//    /**
//     * return winning percentage
//     *
//     * @return float
//     */
//    function winPct()
//    {
//        if ($this->cnt_won + $this->cnt_lost + $this->cnt_draw == 0) {
//            return 0;
//        } else {
//            return ($this->cnt_won / ($this->cnt_won + $this->cnt_lost + $this->cnt_draw)) *
//                100;
//        }
//    }
//
//
//    /**
//     * return scoring average
//     *
//     * @return float
//     */
//    function goalAvg()
//    {
//        if ($this->sum_team2_result == 0) {
//            return $this->sum_team1_result / 1;
//        } else {
//            return $this->sum_team1_result / $this->sum_team2_result;
//        }
//    }
//
//    /**
//     * return scoring percentage
//     *
//     * @return float
//     */
//    function goalPct()
//    {
//        $result = $this->goalAvg() * 100;
//        return $result;
//    }
//
//
//    /**
//     * return leg ratio
//     *
//     * @return float
//     */
//    function legsRatio()
//    {
//        if ($this->sum_team2_legs == 0) {
//            return $this->sum_team1_legs / 1;
//        } else {
//            return $this->sum_team1_legs / $this->sum_team2_legs;
//        }
//    }
//
//    /**
//     * return points ratio
//     *
//     * @return float
//     */
//    function pointsRatio()
//    {
//        if ($this->neg_points == 0) {
//            // we do not include start points
//            return $this->getPoints(false) / 1;
//        } else {
//            // we do not include start points
//            return $this->getPoints(false) / $this->neg_points;
//        }
//    }
//
//    /**
//     * return points quot
//     *
//     * @return float
//     */
//    function pointsQuot()
//    {
//        if ($this->cnt_matches == 0) {
//            // we do not include start points
//            return $this->getPoints(false) / 1;
//        } else {
//            // we do not include start points
//            return $this->getPoints(false) / $this->cnt_matches;
//        }
//    }
//
//
//    function getName()
//    {
//        return $this->_name;
//    }
//
//    /**
//     * return points total
//     *
//     * @param boolean include start points, default true
//     */
//    function getPoints($include_start = true)
//    {
//        if ($include_start) {
//            return $this->sum_points + $this->_startpoints;
//        } else {
//            return $this->sum_points;
//        }
//    }
//
//
//}


?>