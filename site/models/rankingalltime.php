<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

if (! defined('JSM_PATH'))
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

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
    static $classname = 'sportsmanagementModelRanking';
    
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
        // Reference global application object
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $this->alltimepoints = $jinput->request->get('points', '3,1,0', 'STR');
        /*
        if (!class_exists(self::$classname))
        {
        $file = JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'ranking.php';
        if (file_exists($file))
        {
        require_once($file);
        }
        }
        */
        $file = JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'ranking.php';
        require_once($file);
        
        $menu = JMenu::getInstance('site');
        $item = $menu->getActive();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' menu<br><pre>'.print_r($menu,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' item<br><pre>'.print_r($item,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' classname<br><pre>'.print_r(self::$classname,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' file<br><pre>'.print_r($file,true).'</pre>'),'Notice');
        
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
        
//        $xml = JFactory::getXMLParser( 'Simple' );
//        $xml->loadFile($strXmlFile);
        $xml=JFactory::getXML($strXmlFile);
        $children = $xml->document->children();

 // We can now step through each element of the file 
foreach( $xml->children() as $field ) 
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' field<br><pre>'.print_r($field ,true).'</pre>'),'Notice');
//$attr = $field->field->attributes();
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' attr <br><pre>'.print_r($attr ,true).'</pre>'),'Notice'); 
foreach( $field->fieldset  as $fieldset ) 
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' fieldset<br><pre>'.print_r($fieldset,true).'</pre>'),'Notice');

foreach( $fieldset->field  as $param ) 
{
$attributes = $param->attributes();
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' attributes<br><pre>'.print_r($attributes,true).'</pre>'),'Notice');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' attributes name<br><pre>'.print_r($attributes['name'],true).'</pre>'),'Notice');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' attributes default<br><pre>'.print_r($attributes['default'],true).'</pre>'),'Notice');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' attributes name<br><pre>'.print_r((string)$param->attributes()->name[0],true).'</pre>'),'Notice');

$this->_params[(string)$param->attributes()->name[0]] = (string)$param->attributes()->default[0];

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
        $result = self::getAllTeams($project_ids);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_ids<br><pre>'.print_r($project_ids,true).'</pre>'),'Notice');
        
        $count_teams = count($result);
    $app->enqueueMessage(JText::_('Wir verarbeiten '.$count_teams.' Vereine !'),'');

        if (count($result)) {
            foreach ($result as $r) {
                $this->teams[$r->team_id] = $r;
                $this->teams[$r->team_id]->cnt_matches = 0;
/**
 * das ist hier nicht richtig
 * $this->teams[$r->team_id]->sum_points = $r->points_finally;
 * $this->teams[$r->team_id]->neg_points = $r->neg_points_finally;
 */
                $this->teams[$r->team_id]->sum_points = 0;
                $this->teams[$r->team_id]->neg_points = 0;

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
$option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->clear();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_ids<br><pre>'.print_r($project_ids,true).'</pre>'),'');
        
if ( $project_ids )
{        
        $query->select('tl.id AS projectteamid,tl.division_id');
        $query->select('tl.standard_playground,tl.admin,tl.start_points');
        $query->select('tl.use_finally,tl.points_finally,tl.neg_points_finally,tl.matches_finally,tl.won_finally,tl.draws_finally,tl.lost_finally,tl.homegoals_finally,tl.guestgoals_finally,tl.diffgoals_finally');
        $query->select('tl.is_in_score,tl.info,st.team_id,tl.checked_out,tl.checked_out_time');
        $query->select('tl.picture,tl.project_id');
        $query->select('t.id,t.name,t.short_name,t.middle_name,t.notes,t.club_id');
        $query->select('c.unique_id,c.email as club_email,c.logo_small,c.logo_middle,c.logo_big,c.country, c.website');
        $query->select('d.name AS division_name,d.shortname AS division_shortname,d.parent_id AS parent_division_id');
        $query->select('plg.name AS playground_name,plg.short_name AS playground_short_name');
        
        $query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
        $query->select('CONCAT_WS( \':\', t.id, t.alias ) AS team_slug');
        $query->select('CONCAT_WS( \':\', d.id, d.alias ) AS division_slug');
        $query->select('CONCAT_WS( \':\', c.id, c.alias ) AS club_slug');

$query->from('#__sportsmanagement_project_team as tl' );
$query->join('LEFT','#__sportsmanagement_season_team_id AS st ON st.id = tl.team_id'); 
$query->join('LEFT','#__sportsmanagement_team as t ON st.team_id = t.id' );
$query->join('LEFT','#__sportsmanagement_club as c ON t.club_id = c.id' );
$query->join('LEFT','#__sportsmanagement_division as d ON d.id = tl.division_id' );
$query->join('LEFT','#__sportsmanagement_playground as plg ON plg.id = tl.standard_playground' );
$query->join('LEFT','#__sportsmanagement_project AS p ON p.id = tl.project_id' );
            
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
$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return $this->_teams;
}
else
{
JError::raiseWarning(0, __METHOD__.' '.__LINE__.' '.JText::_('COM_SPORTSMANAGEMENT_NO_RANKING_PROJECTINFO') );
			return false;    
}

    }
    
    /**
     * sportsmanagementModelRankingAllTime::getAllMatches()
     * 
     * @param mixed $projects
     * @return
     */
    function getAllMatches($projects)
    {
        $option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
    $res = '';
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->clear();
   
   //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projects<br><pre>'.print_r($projects,true).'</pre>'),'');
   
   if ( $projects )
   {     
        $query->select('m.id,m.projectteam1_id,m.projectteam2_id,m.team1_result AS home_score,m.team2_result AS away_score,m.team1_bonus AS home_bonus,m.team2_bonus AS away_bonus');
	$query->select('m.team1_legs AS l1,m.team2_legs AS l2,m.match_result_type AS match_result_type,m.alt_decision as decision,m.team1_result_decision AS home_score_decision');
	$query->select('m.team2_result_decision AS away_score_decision,m.team1_result_ot AS home_score_ot,m.team2_result_ot AS away_score_ot,m.team1_result_so AS home_score_so,m.team2_result_so AS away_score_so');
        $query->select('t1.id AS team1_id,t2.id AS team2_id');
	$query->select('r.id as roundid, m.team_won, r.roundcode');
        
        
        $query->from('#__sportsmanagement_match m');
	$query->join('INNER','#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id ');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id'); 
	$query->join('INNER','#__sportsmanagement_team t1 ON st1.team_id = t1.id '); 
	$query->join('INNER','#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id ');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id'); 
	$query->join('INNER','#__sportsmanagement_team t2 ON st2.team_id = t2.id '); 
	$query->join('INNER','#__sportsmanagement_round AS r ON m.round_id = r.id ');
        
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
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
	    $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_NO_RANKINGALL_MATCHES'),'Error');
        }
              
    $this->_matches = $res;
    
    $count_matches = count($res);
    $app->enqueueMessage(JText::_('Wir verarbeiten '.$count_matches.' Spiele !'),'');
    }
    else
    {
    JError::raiseWarning(0, __METHOD__.' '.__LINE__.' '.JText::_('COM_SPORTSMANAGEMENT_NO_RANKING_PROJECTINFO') );
			return false;    
    }  
    $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect     
    return $res;
        
    }
    
    /**
     * sportsmanagementModelRankingAllTime::getAllTimeRanking()
     * 
     * @return
     */
    function getAllTimeRanking()
    {
        $option = JFactory::getApplication()->input->getCmd('option');
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
			$home->winpoints = $win_points;
			
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

/**
 * hier werden die werte aus dem projekt dem team dazu addiert
 * wenn die werte des projektteams auch genutzt werden sollen
 * dazu müssen die endpunkte neu berechnet werden, da die punkteverteilung
 * über den request anders übergeben werden. z.b. 2 punkte oder 3 punkte für
 * einen sieg
 * $win_points $draw_points $loss_points
 * 
 * 
 */
    
    foreach ( $this->_teams as $team )
    {
    if ( $team->use_finally ) 
			{
			 $team->points_finally = ( $win_points * $team->won_finally ) + ( $draw_points * $team->draws_finally );
             $team->neg_points_finally = ( $loss_points * $team->lost_finally ) + ( $draw_points * $team->draws_finally );
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
        $option = JFactory::getApplication()->input->getCmd('option');
        $jinput = $app->input;
        $league = $jinput->request->get('l', 0, 'INT');
        
        //$search	= $this->getState('filter.search');
        
        //$this->_project_id	= JFactory::getApplication()->input->getVar('pid');
        //$this->_project_id	= $app->getUserState( "$option.pid", '0' );
        
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = JFactory::getDbo()->getQuery(true);

        //$league = JFactory::getApplication()->input->getInt("l", 0);

        if (!$league) 
        {
            $projekt = $jinput->request->get('p', 0, 'INT');
            $query->clear();
            // Select some fields
		$query->select('league_id');
		// From the rounds table
		$query->from('#__sportsmanagement_project');
        $query->where('id = ' . $projekt);
        $query->order('name ');
        
//            $query = 'select league_id 
//  from #__sportsmanagement_project
//  where id = ' . $projekt . ' order by name ';
            $db->setQuery($query);
            $league = $db->loadResult();

        }

$query->clear();
// Select some fields
		$query->select('id');
		// From the rounds table
		$query->from('#__sportsmanagement_project');
        $query->where('league_id = ' . $league);
        $query->order('name ');
        
//        $query = 'select id 
//  from #__sportsmanagement_project
//  where league_id = ' . $league . ' order by name ';
  
        $db->setQuery($query);
        //$result = $this->_db->loadObjectList();
        //$result = $db->loadResultArray();
        
         if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        // Joomla! 3.0 code here
        $result = $db->loadColumn(0);
        }
        elseif(version_compare(JVERSION,'2.5.0','ge')) 
        {
        // Joomla! 2.5 code here
        $result = $db->loadResultArray(0);
        } 
        
        $this->project_ids = implode(",", $result);
        $this->project_ids_array = $result;
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' league<br><pre>'.print_r($league,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projekt<br><pre>'.print_r($projekt,true).'</pre>'),'Notice');
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'Notice');
        
        $count_project = count($result);
    $app->enqueueMessage(JText::_('Wir verarbeiten '.$count_project.' Projekte/Saisons !'),'');
    $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
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
        $option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
    
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($this->teams,true).'</pre>'),'');
    
        $newranking = array();


        foreach ($this->teams as $key) 
        {
//            $new = new stdclass(0);
            $new = new JSMRankingTeamClass(0);
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


        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team_id<br><pre>'.print_r($key->team_id,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' new<br><pre>'.print_r($new,true).'</pre>'),'');
        
        }
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' newranking<br><pre>'.print_r($newranking,true).'</pre>'),'');
        
        $newranking[0] = self::_sortRanking($newranking[0]);

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
        // Reference global application object
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $order = $jinput->request->get('order', '', 'STR');
        $order_dir = $jinput->request->get('dir', 'DESC', 'STR');
        
        $arr2 = array();
        
        foreach ($ranking as $row) {
                $arr2[$row->_teamid] = JArrayHelper::fromObject($row);
            }


        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' order<br><pre>'.print_r($order,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' order_dir<br><pre>'.print_r($order_dir,true).'</pre>'),'Notice');
        
        //$order = JFactory::getApplication()->input->getVar('order', '');
        //$order_dir = JFactory::getApplication()->input->getVar('dir', 'DESC');

        if (!$order) 
        {
            $order_dir = 'DESC';
            $sortarray = array();
            

            foreach ($this->_getRankingCriteria() as $c) {

                switch ($c) {
                    case '_cmpPoints':
                        $sortarray['sum_points'] = SORT_DESC;
                        break;
                    case '_cmpPLAYED':
                        $sortarray['cnt_matches'] = SORT_DESC;
                        break;
                    case '_cmpDiff':
                        $sortarray['diff_team_results'] = SORT_DESC;
                        break;
                    case '_cmpFor':
                        $sortarray['sum_team1_result'] = SORT_DESC;
                        break;
                    case '_cmpPlayedasc':
                        $sortarray['cnt_matches'] = SORT_ASC;
                        break;
                }

            }


            $arr2 = $this->array_msort($arr2, $sortarray);

            unset($ranking);

            foreach ($arr2 as $key => $row) 
            {
                //$ranking2[$key] = JArrayHelper::toObject($row, 'JSMRankingalltimeTeam');
                //$ranking[$key] = JArrayHelper::toObject($row, 'JSMRankingalltimeTeam');
                //$ranking2[$key] = JArrayHelper::toObject($row, 'JSMRankingTeam');
                $ranking[$key] = JArrayHelper::toObject($row, 'JSMRankingTeamClass');
            }

        } else //     if ( !$order_dir)
        {
            //     $order_dir = 'DESC';
            //     }
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' order<br><pre>'.print_r($order,true).'</pre>'),'Notice');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' classname<br><pre>'.print_r(self::$classname,true).'</pre>'),'Notice');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ranking<br><pre>'.print_r($ranking,true).'</pre>'),'Notice');
            
            switch ($order) {
                case 'played':
                    //uasort($ranking, array(self::$classname, "playedCmp"));
                    $sortarray['cnt_matches'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;
                case 'name':
                    //uasort($ranking, array(self::$classname, "teamNameCmp"));
                    $sortarray['_name'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;
                case 'rank':
                    break;
                case 'won':
                    //uasort($ranking, array(self::$classname, "wonCmp"));
                    $sortarray['cnt_won'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;
                case 'draw':
                    //uasort($ranking, array(self::$classname, "drawCmp"));
                    $sortarray['cnt_draw'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;
                case 'loss':
                    //uasort($ranking, array(self::$classname, "lossCmp"));
                    $sortarray['cnt_lost'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;
                case 'winpct':
                    uasort($ranking, array(self::$classname, "_winpctCmp"));
                    //$sortarray['_name'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;
                case 'quot':
                    uasort($ranking, array(self::$classname, "_quotCmp"));
                    //$sortarray['_name'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;
                case 'goalsp':
                    //uasort($ranking, array(self::$classname, "goalspCmp"));
                    $sortarray['sum_team1_result'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;
                case 'goalsfor':
                    //uasort($ranking, array(self::$classname, "goalsforCmp"));
                    $sortarray['sum_team1_result'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;
                case 'goalsagainst':
                    //uasort($ranking, array(self::$classname, "goalsagainstCmp"));
                    $sortarray['sum_team2_result'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;
                case 'legsdiff':
                    //uasort($ranking, array(self::$classname, "legsdiffCmp"));
                    $sortarray['diff_team_legs'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;
                case 'legsratio':
                    //uasort($ranking, array(self::$classname, "legsratioCmp"));
                    $sortarray['legsRatio'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;
                case 'diff':
                    //uasort($ranking, array(self::$classname, "diffCmp"));
                    $sortarray['diff_team_legs'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;
                case 'points':
                    //uasort($ranking, array(self::$classname, "_pointsCmp"));
                    $sortarray['sum_points'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    //$sortarray['_name'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;
                case 'start':
                    //uasort($ranking, array(self::$classname, "startCmp"));
                    $sortarray['start_points'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;
                case 'bonus':
                    //uasort($ranking, array(self::$classname, "bonusCmp"));
                    $sortarray['bonus_points'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;
                case 'negpoints':
                    //uasort($ranking, array(self::$classname, "negpointsCmp"));
                    $sortarray['neg_points'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;
                case 'pointsratio':
                    //uasort($ranking, array(self::$classname, "pointsratioCmp"));
                    $sortarray['pointsRatio'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
                    break;

                default:
                    if (method_exists($this, $order . 'Cmp')) {
                        uasort($ranking, array($this, $order . 'Cmp'));
                    }
                    break;
            }
            
            $arr2 = $this->array_msort($arr2, $sortarray);

            unset($ranking);

            foreach ($arr2 as $key => $row) 
            {
                //$ranking2[$key] = JArrayHelper::toObject($row, 'JSMRankingalltimeTeam');
                //$ranking[$key] = JArrayHelper::toObject($row, 'JSMRankingalltimeTeam');
                //$ranking2[$key] = JArrayHelper::toObject($row, 'JSMRankingTeam');
                $ranking[$key] = JArrayHelper::toObject($row, 'JSMRankingTeamClass');
            }
            
//            if ($order_dir == 'DESC') {
//                $ranking = array_reverse($ranking, true);
//            }
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ranking<br><pre>'.print_r($ranking,true).'</pre>'),'Notice');

        }

        return $ranking;
        
    }

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

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _criteria<br><pre>'.print_r($this->_criteria,true).'</pre>'),'');

        return $this->_criteria;
    }
    
   
}

?>
