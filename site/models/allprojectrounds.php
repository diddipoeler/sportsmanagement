<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

jimport('joomla.utilities.array');
jimport('joomla.utilities.arrayhelper') ;


//require_once( JLG_PATH_SITE . DS . 'extensions' .DS . 'jlallprojectrounds' .DS . 'helpers' . DS . 'jlallprojectrounds.php' );
//require_once( JLG_PATH_SITE . DS . 'models' . DS .'project.php' );

/**
 * sportsmanagementModelallprojectrounds
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelallprojectrounds extends JModelLegacy
{
	var $projectid = 0;
	var $project_ids = 0;
	var $project_ids_array = array();
	var $round = 0;
	var $rounds = array(0);
// 	var $part = 0;
// 	var $type = 0;
// 	var $from = 0;
// 	var $to = 0;
// 	var $divLevel = 0;
	var $ProjectTeams = array();
	var $previousRanking = array();
// 	var $homeRank = array();
// 	var $awayRank = array();
	var $colors = array();
	var $result = array();
 	
  var $projectteam_id = 0;
 	var $matchid = 0;
 	
 	var $_playersevents = array();
  /**
     * parameters
     * @var array
     */
    var $_params = null;
    
	/**
	 * sportsmanagementModelallprojectrounds::__construct()
	 * 
	 * @return void
	 */
	function __construct( )
	{
		$mainframe = JFactory::getApplication();
    $this->projectid = JRequest::getInt( "p", 0 );
    sportsmanagementModelProject::$projectid = $this->projectid;

//    $menu = JMenu::getInstance('site');
//        $item = $menu->getActive();
//        $params = $menu->getParams($item->id);
//       $registry = new JRegistry();
//$registry->loadArray($params);

//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'item->id<pre>'.print_r($item->id,true).'</pre>' ),'Error');
//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'params<pre>'.print_r($params,true).'</pre>' ),'Error');

//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'request<pre>'.print_r($_REQUEST,true).'</pre>' ),'Error');

//$newparams = $registry->toString('ini');
//$newparams = $registry->toArray();
//echo "<b>menue newparams</b><pre>" . print_r($newparams, true) . "</pre>";  
foreach ($_REQUEST as $key => $value ) {
            
            $this->_params[$key] = $value;
        }

//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'params<pre>'.print_r($this->_params,true).'</pre>' ),'Error');

// 		$this->round = JRequest::getInt( "r", $this->current_round);
// 		$this->part  = JRequest::getInt( "part", 0);
// 		$this->from  = JRequest::getInt( 'from', $this->round );
// 		$this->to	 = JRequest::getInt( 'to', $this->round);
// 		$this->type  = JRequest::getInt( 'type', 0 );
// 		$this->last  = JRequest::getInt( 'last', 0 );

// 		$this->selDivision = JRequest::getInt( 'division', 0 );

		parent::__construct( );
	}

//   function getProjectID()
//   {
//   echo 'project ->'.$this->projectid.'<br>';
//   }
  
  /**
   * sportsmanagementModelallprojectrounds::getProjectMatches()
   * 
   * @return
   */
  function getProjectMatches()
  {
    $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
  
  $result = array();
  
  $query->select('m.*,DATE_FORMAT(m.time_present,"%H:%i") time_present');
  $query->select('playground.name AS playground_name');
  $query->select('playground.short_name AS playground_short_name');
  $query->select('r.name as round_name,r.roundcode as roundcode');
  $query->select('t1.name as home_name,t1.short_name as home_short_name,t1.middle_name as home_middle_name');
  $query->select('t2.name as away_name,t2.short_name as away_short_name,t2.middle_name as away_middle_name');
  $query->select('tt1.project_id, d1.name as divhome, d2.name as divaway');
  $query->select('CASE WHEN CHAR_LENGTH(t1.alias) AND CHAR_LENGTH(t2.alias) THEN CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) ELSE m.id END AS slug ');
  $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ');
  $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON m.round_id = r.id ');
  
  $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS tt1 ON m.projectteam1_id = tt1.id');
  $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS tt2 ON m.projectteam2_id = tt2.id ');
  
  $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st1 ON st1.id = tt1.team_id ');
  $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st2 ON st2.id = tt2.team_id ');
  
  $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id = st1.team_id');
  $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id = st2.team_id');
  $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS d1 ON m.division_id = d1.id');
  $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS d2 ON m.division_id = d2.id');
  $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_playground AS playground ON playground.id = m.playground_id');
  
  $query->where('m.published = 1');
  $query->where('r.project_id ='. (int)$this->projectid);
  $query->group('m.id ');
  $query->order('r.roundcode ASC,m.match_date ASC,m.match_number');

//		$query_SELECT='
//		SELECT	,
//			,
//			,
//			,
//			,
//			,
//			,
//			';
//
//		$query_FROM='
//		FROM #__joomleague_match AS m
//			INNER JOIN #__joomleague_round AS r ON m.round_id=r.id
//			LEFT JOIN #__joomleague_project_team AS tt1 ON m.projectteam1_id=tt1.id
//			LEFT JOIN #__joomleague_project_team AS tt2 ON m.projectteam2_id=tt2.id

//			LEFT JOIN #__joomleague_team AS t1 ON t1.id=tt1.team_id
//			LEFT JOIN #__joomleague_team AS t2 ON t2.id=tt2.team_id
//			LEFT JOIN #__joomleague_division AS d1 ON tt1.division_id=d1.id

//			LEFT JOIN #__joomleague_division AS d2 ON tt2.division_id=d2.id
//			LEFT JOIN #__joomleague_playground AS playground ON playground.id=m.playground_id';
//
//		$query_WHERE	= ' WHERE m.published=1 
//							   
//							  AND r.project_id='.(int)$this->projectid;
//		$query_END		= ' GROUP BY m.id ORDER BY r.roundcode ASC,m.match_date ASC,m.match_number';
//				
//		$query=$query_SELECT.$query_FROM.$query_WHERE.$query_END;
		
		
		$db->setQuery($query);
    if (!$result = $db->loadObjectList()) 
    {
			JError::raiseWarning(0, $db->getErrorMsg());
		}

// 		echo '<br /><pre>~'.print_r(count($result),true).'~</pre><br />';
// 		echo '<br /><pre>~'.print_r($result,true).'~</pre><br />';
		$this->result = $result;
		return $result;
		
  }
  
  /**
   * sportsmanagementModelallprojectrounds::getProjectTeamID()
   * 
   * @param mixed $favteams
   * @return
   */
  function getProjectTeamID($favteams)
  {
    $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
  
  foreach ( $favteams as $key => $value )
  {
    $query->clear();
    $query->select('id');
    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team ');
    $query->where('project_id ='. $this->projectid);
    $query->where('team_id ='. $value);
    
//  $query = '	SELECT id
//FROM #__joomleague_project_team
//WHERE project_id = ' . $this->projectid .' and team_id = '.$value;

$db->setQuery( $query );
$this->ProjectTeams[$value] = $db->loadResult();
  
  }

//   echo '<br />ProjectTeams<pre>~'.print_r($this->ProjectTeams,true).'~</pre><br />';
  
  return $this->ProjectTeams;
  }
  
  /**
   * sportsmanagementModelallprojectrounds::getSubstitutes()
   * 
   * @return
   */
  function getSubstitutes()
	{
	   $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
	$projectteamplayer = array();
    
    $query->select('mp.in_out_time,mp.teamplayer_id,mp.in_for');
    $query->select('pt.team_id,pt.id AS ptid');
    $query->select('p.firstname,p.nickname,p.lastname');
    $query->select('tp.person_id,tp.jerseynumber');
    $query->select('tp2.person_id AS out_person_id');
    $query->select('p2.id AS out_ptid,p2.firstname AS out_firstname,p2.nickname AS out_nickname,p2.lastname AS out_lastname');
    $query->select('pos.name AS in_position');
    $query->select('pos2.name AS out_position');
    $query->select('ppos.id AS pposid1');
    $query->select('ppos2.id AS pposid2');
    $query->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
    $query->select('CONCAT_WS(\':\',p.id,p.alias) AS person_slug');
    
    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp ');
    $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id as stp1 ON stp1.id = mp.teamplayer_id');
    $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st1 ON st1.team_id = stp1.team_id ');
    $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st1.id ');
    $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON stp1.person_id = p.id AND p.published = 1 ');
    
    $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id as stp2 ON stp2.id = mp.in_for');
    $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st2 ON st2.team_id = stp2.team_id ');
    $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p2 ON stp2.person_id = p2.id AND p2.published = 1 ');
    
    $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = mp.project_position_id');
    $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id = pos.id');
    $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp2 ON mp.match_id = mp2.match_id and mp.in_for = mp2.teamplayer_id');
    $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos2 ON ppos2.id = mp2.project_position_id');
    $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos2 ON ppos2.position_id = pos2.id');
    
    $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = st1.team_id');
    
    $query->where('mp.match_id = '.(int)$this->matchid);
    $query->where('pt.id = '.$this->projectteam_id );
    $query->where('mp.came_in > 0');
    
    $query->group('mp.in_out_time+mp.teamplayer_id+pt.team_id');
    $query->order('(mp.in_out_time+0)');

		$db->setQuery($query);
		//echo($this->_db->getQuery());
		$result = $db->loadObjectList();
		//return $result;
		
        if ( !$result )
        {
        JError::raiseWarning(0, 'Keine Auswechselungen vorhanden');      
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');    
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($query->dump(),true).'</pre>' ),'Error');
        }
     else
     {   
    foreach ( $result as $row )
		{
    $projectteamplayer[] = $row->firstname.' '.$row->lastname.' ('.$row->in_out_time.')';
    }
    }
		return $projectteamplayer;
		
	}
	
	/**
	 * sportsmanagementModelallprojectrounds::getPlayersEvents()
	 * 
	 * @return
	 */
	function getPlayersEvents()
	{
	   $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
	$playersevents = array();
    
    $query->select('ev.*');
    $query->select('p.firstname,p.nickname,p.lastname');
    $query->select('et.name as etname,et.icon as eticon');
    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event as ev  ');
    $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et ON et.id = ev.event_type_id ');
    $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id as stp1 ON stp1.id = ev.teamplayer_id');
    $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st1 ON st1.team_id = stp1.team_id ');
    $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st1.id ');
    $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON stp1.person_id = p.id AND p.published = 1 ');
    
   	$query->where('ev.match_id = ' . (int)$this->matchid);
    $query->where('ev.projectteam_id = ' . $this->projectteam_id);
	
            
            $db->setQuery($query);
			$res = $db->loadObjectList();

if ( !$res )
        {
        JError::raiseWarning(0, 'Keine Ereignisse vorhanden');     
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');    
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($query->dump(),true).'</pre>' ),'Error');
        }
        
		foreach ( $res as $row )
		{
    $playersevents[] = JHTML::_( 'image', $row->eticon, JText::_($row->eticon ), NULL ) . JText::_($row->etname).' '.$row->notice.' '.$row->firstname.' '.$row->lastname.' ('.$row->event_time.')';
    }
    
// 			$this->_playersevents = $events;

		return $playersevents;

	}
	
  /**
   * sportsmanagementModelallprojectrounds::getMatchPlayers()
   * 
   * @return
   */
  function getMatchPlayers()
	{
	    $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
	$projectteamplayer = array();
	
    $query->select('pt.id');
    $query->select('stp1.person_id,stp1.jerseynumber,stp1.picture');
    $query->select('p.firstname,p.nickname,p.lastname');
    $query->select('ppos.position_id,ppos.id AS pposid,p.picture AS ppic');
    $query->select('pt.team_id,pt.id as ptid');
    $query->select('mp.teamplayer_id,mp.out,mp.in_out_time');
    $query->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
    $query->select('CONCAT_WS(\':\',p.id,p.alias) AS person_slug');
    
    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp ');
    $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id as stp1 ON stp1.id = mp.teamplayer_id');
    $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st1 ON st1.team_id = stp1.team_id ');
    $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st1.id ');
    $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON stp1.person_id = p.id AND p.published = 1 ');
    
    $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = mp.project_position_id');
    $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id = pos.id');
    
    $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = st1.team_id');

    $query->where('mp.match_id = '.(int)$this->matchid );
    $query->where('mp.came_in = 0');
    $query->where('pt.id = '.$this->projectteam_id );
    $query->where('p.published = 1');
    
    $query->order('mp.ordering, stp1.jerseynumber, p.lastname');
    
//		$query=' SELECT	,'
//		      .' ,'
//		      .' ,'
//		      .' ,'
//		      .' '
//		      .' ,'
//		      .' '
//		      .' pt.id as ptid,'
//		      .' mp.teamplayer_id,'
//		      .' mp.out,'
//		      .' mp.in_out_time,'
//		      .' ,'
//			  .' ,'
//		      .' CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\',t.id,t.alias) ELSE t.id END AS team_slug,'
//		      .' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS person_slug '
//		      .' FROM #__joomleague_match_player AS mp '
//		      .' INNER JOIN	#__joomleague_team_player AS tp ON tp.id=mp.teamplayer_id '
//		      .' INNER JOIN	#__joomleague_project_team AS pt ON pt.id=tp.projectteam_id '
//		      .' INNER JOIN	#__joomleague_team AS t ON t.id=pt.team_id '
//		      .' INNER JOIN	#__joomleague_person AS p ON tp.person_id=p.id '
//		      .' LEFT JOIN #__joomleague_project_position AS ppos ON ppos.id=mp.project_position_id '
//		      .' LEFT JOIN #__joomleague_position AS pos ON ppos.position_id=pos.id '
//		      .' WHERE mp.match_id='.(int)$this->matchid
//		      .' AND mp.came_in=0 '
//		      .' AND pt.id='.$this->projectteam_id
//		      .' AND p.published = 1 '
//		      .' ORDER BY mp.ordering, tp.jerseynumber, p.lastname ';
              
              
		$db->setQuery($query);
		$matchplayers = $db->loadObjectList();
        
        if ( !$matchplayers )
        {
        JError::raiseWarning(0, 'Keine Spieler vorhanden');    
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');    
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($query->dump(),true).'</pre>' ),'Error');
        }
		
// 		echo '<br />matchplayers<pre>~'.print_r($matchplayers,true).'~</pre><br />';
		
		foreach ( $matchplayers as $row )
		{
        $query->clear();
        $query->select('in_out_time');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player');
        $query->where('match_id = ' . (int)$this->matchid);
        $query->where('in_for = ' . (int)$row->teamplayer_id);
        
//    $query = '	SELECT	in_out_time
//					FROM #__joomleague_match_player
//					WHERE match_id = ' . (int)$this->matchid . '
//					AND in_for = ' . (int)$row->teamplayer_id;

		$db->setQuery( $query );
		$row->in_out_time = $db->loadResult();
      
    
    
    if ( $row->in_out_time )
    {
    $projectteamplayer[] = $row->firstname.' '.$row->lastname.' ('.$row->in_out_time.')';
    }
    else
    {
    $projectteamplayer[] = $row->firstname.' '.$row->lastname;
    }
    
    }
		
    return $projectteamplayer;
		
	}
	
	/**
	 * sportsmanagementModelallprojectrounds::getAllRoundsParams()
	 * 
	 * @return
	 */
	function getAllRoundsParams()
    {
        return $this->_params;
    }
    
  /**
   * sportsmanagementModelallprojectrounds::getRoundsColumn()
   * 
   * @param mixed $rounds
   * @param mixed $config
   * @return
   */
  function getRoundsColumn($rounds,$config)
  {
  
  //$countrows = count($rounds) %2;
  //echo 'countrows -> '.$countrows.'<br>';
  
  if ( count($rounds) %2 ) 
  { 
//   echo "Zahl ist ungrade<br>"; 
  } 
  else 
  { 
//   echo "Zahl ist gerade<br>";
  $countrows = count($rounds) / 2;
//   echo 'wir haben '.$countrows.' spieltage pro spalte<br>';
  }

  if ( $config['show_columns'] == 1 )
  {
  }
  else
  {
  $countrows = count($rounds);
  }
  
  $lfdnumber = 0;
  $htmlcontent = array();
  $content = '<table width="100%" border="4" rules="none">';
  
  for($a=0; $a < $countrows;$a++)
  {
  
  if ( $config['show_columns'] == 1 )
  {
  // zwei spalten
  $secondcolumn = $a + $countrows;
  
  $htmlcontent[$a]['header'] = '';
  $htmlcontent[$a]['first'] = '<table width="100%" border="0" rules="rows">';
  $htmlcontent[$a]['second'] = '<table width="100%" border="0" rules="rows">';
  $htmlcontent[$a]['header'] = '<thead><tr><th colspan="" >'.$rounds[$a]->name.'</th><th colspan="" >'.$rounds[$secondcolumn]->name.'</th></tr></thead>';

  
  $roundcode = $a + 1;
  $secondroundcode = $a + 1 + $countrows;
 
  foreach ( $this->result as $match )
  {
    
  if ( (int)$match->roundcode === (int)$roundcode )
  {
  
  $htmlcontent[$a]['first'] .= '<tr><td>'.$match->home_name.'</td>';
  $htmlcontent[$a]['first'] .= '<td>'.$match->team1_result.'</td>';
  $htmlcontent[$a]['first'] .= '<td>'.$match->team2_result.'</td>';
  $htmlcontent[$a]['first'] .= '<td>'.$match->away_name.'</td></tr>';
  
  foreach ( $this->ProjectTeams as $key => $value )
  {
  
  if ( (int)$match->projectteam1_id === (int)$value || (int)$match->projectteam2_id === (int)$value )
  {
  
  if ( $config['show_firstroster'] )
  {
  $htmlcontent[$a]['firstroster'] = '<b>'.JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_LINE-UP').' : </b>';
  $this->matchid = $match->id;
  $this->projectteam_id = $value;
  $htmlcontent[$a]['firstroster'] .= implode(",",self::getMatchPlayers());
  $htmlcontent[$a]['firstroster'] .= '';
  }
  if ( $config['show_firstsubst'] )
  {
  $htmlcontent[$a]['firstsubst'] = '<b>'.JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTES').' : </b>';
  $this->matchid = $match->id;
  $this->projectteam_id = $value;
  $htmlcontent[$a]['firstsubst'] .= implode(",",self::getSubstitutes());
  $htmlcontent[$a]['firstsubst'] .= '';
  }
  if ( $config['show_firstevents'] )
  {
  $htmlcontent[$a]['firstevents'] = '<b>'.JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS').' : </b>';
  $this->matchid = $match->id;
  $this->projectteam_id = $value;
  $htmlcontent[$a]['firstevents'] .= implode(",",self::getPlayersEvents());
  $htmlcontent[$a]['firstevents'] .= '';
  }
  
  }
  
  }
  
  }
  
  if ( (int)$match->roundcode === (int)$secondroundcode )
  {
  
  $htmlcontent[$a]['second'] .= '<tr><td>'.$match->home_name.'</td>';
  $htmlcontent[$a]['second'] .= '<td>'.$match->team1_result.'</td>';
  $htmlcontent[$a]['second'] .= '<td>'.$match->team2_result.'</td>';
  $htmlcontent[$a]['second'] .= '<td>'.$match->away_name.'</td></tr>';
  
  foreach ( $this->ProjectTeams as $key => $value )
  {
  
  if ( (int)$match->projectteam1_id === (int)$value || (int)$match->projectteam2_id === (int)$value )
  {
  if ( $config['show_secondroster'] )
  {
  $htmlcontent[$a]['secondroster'] = '<b>'.JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_LINE-UP').' : </b>';
  $this->matchid = $match->id;
  $this->projectteam_id = $value;
  $htmlcontent[$a]['secondroster'] .= implode(",",$this->getMatchPlayers());
  $htmlcontent[$a]['secondroster'] .= '';
  }
  if ( $config['show_secondsubst'] )
  {
  $htmlcontent[$a]['secondsubst'] = '<b>'.JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTES').' : </b>';
  $this->matchid = $match->id;
  $this->projectteam_id = $value;
  $htmlcontent[$a]['secondsubst'] .= implode(",",$this->getSubstitutes());
  $htmlcontent[$a]['secondsubst'] .= '';
  }
  if ( $config['show_secondevents'] )
  {
  $htmlcontent[$a]['secondevents'] = '<b>'.JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS').' : </b>';
  $this->matchid = $match->id;
  $this->projectteam_id = $value;
  $htmlcontent[$a]['secondevents'] .= implode(",",$this->getPlayersEvents());
  $htmlcontent[$a]['secondevents'] .= '';
  }
  }
  
  }
    
  }
      
  }
  
  $htmlcontent[$a]['first'] .= '</table>';
  $htmlcontent[$a]['second'] .= '</table>';
  }
  else
  {
  // nur eine spalte
  $htmlcontent[$a]['header'] = '';
  $htmlcontent[$a]['first'] = '<table width="100%" border="0" rules="rows">';
  $htmlcontent[$a]['header'] = '<thead><tr><th colspan="" >'.$rounds[$a]->name.'</th></tr></thead>';
  $roundcode = $a + 1;
  
  foreach ( $this->result as $match )
  {
    
  if ( (int)$match->roundcode === (int)$roundcode )
  {
  
  $htmlcontent[$a]['first'] .= '<tr><td width="45%">'.$match->home_name.'</td>';
  $htmlcontent[$a]['first'] .= '<td width="5%">'.$match->team1_result.'</td>';
  $htmlcontent[$a]['first'] .= '<td width="5%">'.$match->team2_result.'</td>';
  $htmlcontent[$a]['first'] .= '<td width="45%">'.$match->away_name.'</td></tr>';
  
  foreach ( $this->ProjectTeams as $key => $value )
  {
  
  if ( (int)$match->projectteam1_id === (int)$value || (int)$match->projectteam2_id === (int)$value )
  {
  
  
  $htmlcontent[$a]['firstroster'] = '<b>'.JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_LINE-UP').' : </b>';
  $this->matchid = $match->id;
  $this->projectteam_id = $value;
  $htmlcontent[$a]['firstroster'] .= implode(",",$this->getMatchPlayers());
  $htmlcontent[$a]['firstroster'] .= '';
  
  $htmlcontent[$a]['firstsubst'] = '<b>'.JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTES').' : </b>';
  $this->matchid = $match->id;
  $this->projectteam_id = $value;
  $htmlcontent[$a]['firstsubst'] .= implode(",",$this->getSubstitutes());
  $htmlcontent[$a]['firstsubst'] .= '';
  
  $htmlcontent[$a]['firstevents'] = '<b>'.JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS').' : </b>';
  $this->matchid = $match->id;
  $this->projectteam_id = $value;
  $htmlcontent[$a]['firstevents'] .= implode(",",$this->getPlayersEvents());
  $htmlcontent[$a]['firstevents'] .= '';
  
  
  }
  
  }
  
  }
  
  }
  
  $htmlcontent[$a]['first'] .= '</table>';
  }
    
  }
  
  if ( $htmlcontent )
  {
  foreach ( $htmlcontent as $key => $value )
  {
  
  if ( $config['show_columns'] == 1 )
  {
  $content .= $value['header'];
  $content .= '<tr><td>'.$value['first'].'</td>';
  $content .= '<td>'.$value['second'].'</td></tr>';
  
  if (array_key_exists('firstroster', $value)) {
  $content .= '<tr><td>'.$value['firstroster'].'</td>';
  }
  if (array_key_exists('secondroster', $value)) {
  $content .= '<td>'.$value['secondroster'].'</td></tr>';
  }
  if (array_key_exists('firstsubst', $value)) {
  $content .= '<tr><td>'.$value['firstsubst'].'</td>';
  }
  if (array_key_exists('secondsubst', $value)) {
  $content .= '<td>'.$value['secondsubst'].'</td></tr>';
  }
  if (array_key_exists('firstevents', $value)) {
  $content .= '<tr><td>'.$value['firstevents'].'</td>';
  }
  if (array_key_exists('secondevents', $value)) {
  $content .= '<td>'.$value['secondevents'].'</td></tr>';
  }
  
  }
  else
  {
  $content .= $value['header'];
  $content .= '<tr><td>'.$value['first'].'</td></tr>';
  
  
  if (array_key_exists('firstroster', $value)) {
  $content .= '<tr><td>'.$value['firstroster'].'</td></tr>';
  }
  
  if (array_key_exists('firstsubst', $value)) {
  $content .= '<tr><td>'.$value['firstsubst'].'</td></tr>';
  }
  
  if (array_key_exists('firstevents', $value)) {
  $content .= '<tr><td>'.$value['firstevents'].'</td></tr>';
  }
  
  }
  
  }
  
  }
   
  $content .= '</table>';
  
  return $content;
  }
  	                              	
}
?>