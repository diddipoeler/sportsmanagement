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
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

//require_once( JPATH_COMPONENT_SITE . DS . 'helpers' . DS . 'ranking.php' );
//require_once( JPATH_COMPONENT_SITE . DS . 'models' . DS . 'project.php' );

/**
 * sportsmanagementModelRanking
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelRanking extends JModelLegacy
{
	static $projectid = 0;
	var $round = 0;
	var $rounds = array(0);
	var $part = 0;
	var $type = 0;
	
    var $from = 0;
	var $to = 0;
    
	var $divLevel = 0;
	var $currentRanking = array();
	var $previousRanking = array();
	var $homeRank = array();
	var $awayRank = array();
	var $colors = array();
	var $result = array();
	var $pageNav = array();
	var $pageNav2 = array();
	var $current_round = 0;
	var $viewName = '';

	/**
	 * sportsmanagementModelRanking::__construct()
	 * 
	 * @return
	 */
	function __construct( )
	{
	   $mainframe = JFactory::getApplication();
		self::$projectid = JRequest::getInt( "p", 0 );
		$this->round = JRequest::getInt( "r", $this->current_round);
		$this->part  = JRequest::getInt( "part", 0);
		$this->from  = JRequest::getInt( 'from', 0 );
		$this->to	 = JRequest::getInt( 'to', $this->round);
		$this->type  = JRequest::getInt( 'type', 0 );
		$this->last  = JRequest::getInt( 'last', 0 );
        $this->viewName = JRequest::getVar( "view");
    	$this->selDivision = JRequest::getInt( 'division', 0 );
        
        sportsmanagementModelProject::$projectid = self::$projectid; 
        
        if ( empty($this->from)  )
        {
        $from	= sportsmanagementModelRounds::getFirstRound(self::$projectid);
        $this->from	= $from['id'];
        }
        
        if ( empty($this->to)  )
        {
		$to	= sportsmanagementModelRounds::getLastRound(self::$projectid);
        $this->to	= $to['id'];
        }
        else
        {
        $this->round = $this->to;    
        }
        
        
        if ( empty($this->round) )
        {
            $this->round = sportsmanagementModelProject::getCurrentRound();
            $this->current_round = $this->round;
        }
        
        sportsmanagementModelProject::$_round_from = $this->from; 
        sportsmanagementModelProject::$_round_to = $this->round;
        
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' from<br><pre>'.print_r($from,true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' to<br><pre>'.print_r($to,true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectid<br><pre>'.print_r($this->projectid,true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' round<br><pre>'.print_r($this->round,true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' current_round<br><pre>'.print_r($this->current_round,true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' from<br><pre>'.print_r($this->from,true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' to<br><pre>'.print_r($this->to,true).'</pre>'),'');

		parent::__construct( );
	}

	
	/**
	 * sportsmanagementModelRanking::limitText()
	 * 
	 * @param mixed $text
	 * @param mixed $wordcount
	 * @return
	 */
	function limitText($text, $wordcount)
	{
		if(!$wordcount) {
			return $text;
		}

		$texts = explode( ' ', $text );
		$count = count( $texts );

		if ( $count > $wordcount )
		{
			$texts = array_slice($texts,0, $wordcount ) ;
			$text = implode(' ' , $texts);
			$text .= '...';
		}

		return $text;
	}
    
    /**
     * sportsmanagementModelRanking::getRssFeeds()
     * 
     * @param mixed $rssfeedlink
     * @param mixed $rssitems
     * @return
     */
    function getRssFeeds($rssfeedlink,$rssitems)
    {
    $rssIds	= array();    
    $rssIds = explode(',',$rssfeedlink);    
    //  get RSS parsed object
		$options = array();
        $options['cache_time'] = null;
        
        $lists = array();
		foreach ($rssIds as $rssId)
		{
		$options['rssUrl'] 		= $rssId; 
        
        $rssDoc =& JFactory::getXMLparser('RSS', $options);
		$feed = new stdclass();
        if ($rssDoc != false)
			{
				// channel header and link
				$feed->title = $rssDoc->get_title();
				$feed->link = $rssDoc->get_link();
				$feed->description = $rssDoc->get_description();
	
				// channel image if exists
				$feed->image->url = $rssDoc->get_image_url();
				$feed->image->title = $rssDoc->get_image_title();
	
				// items
				$items = $rssDoc->get_items();
				// feed elements
				$feed->items = array_slice($items, 0, $rssitems);
				$lists[] = $feed;
			}
        
         
        }  
    //var_dump($lists);
    //echo 'getRssFeeds lists<pre>',print_r($lists,true),'</pre><br>';
    return $lists;         
    }
    
    
	
	/**
	 * sportsmanagementModelRanking::getPreviousGames()
	 * 
	 * @return
	 */
	function getPreviousGames()
	{
	   $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, $mainframe->getUserState( "com_sportsmanagement.cfg_which_database", FALSE ) );
	   $query = $db->getQuery(true);
       $starttime = microtime(); 
       
       //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' round<br><pre>'.print_r($this->round,true).'</pre>'),'');
       
       if ( !$this->round )
        {
            $this->round = sportsmanagementModelProject::getCurrentRound(__METHOD__.' '.$this->viewName);
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' round<br><pre>'.print_r($this->round,true).'</pre>'),'');
        }
        
       
       
		if (!$this->round) 
        {
			return false;
		}
		
		// current round roundcode
		$rounds = sportsmanagementModelProject::getRounds();
		$current = null;
		foreach ($rounds as $r)
		{
			if ($r->id == $this->round) 
            {
			$current = $r;
				break;
			}
		}
			if (!$current) 
            {
			return false;
		}
        
        $query->select('m.*, r.roundcode');
		$query->select('CASE WHEN CHAR_LENGTH(t1.alias) AND CHAR_LENGTH(t2.alias) THEN CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) ELSE m.id END AS slug');
		$query->select('CONCAT_WS(\':\',p.id,p.alias) AS project_slug');
        
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON r.id = m.round_id ');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = r.project_id ');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON m.projectteam1_id = pt1.id ');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON m.projectteam2_id = pt2.id ');
        
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st1 ON st1.id = pt1.team_id');        
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st2 ON st2.id = pt2.team_id'); 
               
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON st1.team_id = t1.id ');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON st2.team_id = t2.id ');

        
        $query->where('r.project_id = ' . $db->Quote(self::$projectid));
        $query->where('r.roundcode <= ' . $db->Quote($current->roundcode));
        $query->where('m.team1_result IS NOT NULL');
        $query->order('r.roundcode ASC ');
               
		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$games = $db->loadObjectList();
        
        if ( !$games && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        } 
        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');


		$teams = sportsmanagementModelProject::getTeamsIndexedByPtid();

		// get games per team
		$res = array();
		foreach ($teams as $ptid =>$team)
		{
			$teamgames = array();
			foreach ((array) $games as $g)
			{
				if ($g->projectteam1_id == $team->projectteamid || $g->projectteam2_id == $team->projectteamid) {
					$teamgames[] = $g;
				}
			}

			if (!count($teamgames)) {
				$res[$ptid] = array();
				continue;
			}
				
			// get last x games
			//$nb_games = 5;
			$config = sportsmanagementModelProject::getTemplateConfig('ranking');
			$nb_games = $config['nb_previous'];			
			$res[$ptid] = array_slice($teamgames, -$nb_games);
		}
		return $res;
	}
		
	/*
    function getFirstRank($part)
    {
        $this->part = 1;
        self::computeRanking();
    }
    */
    
    /*
    function getSecondRank($part)
    {
        $this->part = 2;
        self::computeRanking();
    }
    */
    
    
	/**
	 * sportsmanagementModelRanking::computeRanking()
	 * 
	 * @return
	 */
	function computeRanking()
	{
		$mainframe	= JFactory::getApplication();
        
        //$mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
        //$mdlRound = JModelLegacy::getInstance("Round", "sportsmanagementModel");
		//$mdlRounds = JModelLegacy::getInstance("Rounds", "sportsmanagementModel");
        
		$project = sportsmanagementModelProject::getProject();

		sportsmanagementModelRounds::$_project_id = $project->id;
		
		$firstRound	= sportsmanagementModelRounds::getFirstRound($project->id);
		$lastRound	= sportsmanagementModelRounds::getLastRound($project->id);

		// url if no sef link comes along (ranking form)
		$url = sportsmanagementHelperRoute::getRankingRoute( self::$projectid );
		$tableconfig= sportsmanagementModelProject::getTemplateConfig( "ranking" );

		$this->round = ($this->round == 0 || $this->round == '' ) ? sportsmanagementModelProject::getCurrentRound(__METHOD__.' '.$this->viewName) : $this->round;

//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' firstRound<br><pre>'.print_r($firstRound,true).'</pre>'),'');
//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' lastRound<br><pre>'.print_r($lastRound,true).'</pre>'),'');
//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' round<br><pre>'.print_r($this->round,true).'</pre>'),'');


		$this->rounds = sportsmanagementModelProject::getRounds();
        
		if ( $this->part == 1 )
		{
			$this->from = $firstRound['id'];
            // diddipoeler: das ist ein bug
			//$this->to = $this->rounds[intval(count($this->rounds)/2)]->id;
            $this->to = $this->rounds[intval(count($this->rounds)/2)-1]->id;
		}
		elseif ( $this->part == 2 )
		{
		  // diddipoeler: das ist ein bug
			//$this->from = $this->rounds[intval(count($this->rounds)/2)+1]->id;
            $this->from = $this->rounds[intval(count($this->rounds)/2)]->id;
			$this->to = $lastRound['id'];
		}
		else
		{
			$this->from = JRequest::getInt( 'from', $firstRound['id'] );
			$this->to   = JRequest::getInt( 'to', $this->round, $lastRound['id'] );
		}
		if( $this->part > 0 )
		{
			$url.='&amp;part='.$this->part;
		}
		elseif ( $this->from != 1 || $this->to != $this->round )
		{
			$url.='&amp;from='.$this->from.'&amp;to='.$this->to;
		}
		$this->type = JRequest::getInt( 'type', 0 );
		if ( $this->type > 0 )
		{
			$url.='&amp;type='.$this->type;
		}

		$this->divLevel = 0;




//$mainframe->enqueueMessage(JText::_('computeRanking this->part -> '.'<pre>'.print_r($this->part,true).'</pre>' ),'');
//$mainframe->enqueueMessage(JText::_('computeRanking this->from -> '.'<pre>'.print_r($this->from,true).'</pre>' ),'');
//$mainframe->enqueueMessage(JText::_('computeRanking this->to-> '.'<pre>'.print_r($this->to,true).'</pre>' ),'');
//$mainframe->enqueueMessage(JText::_('computeRanking this->rounds -> '.'<pre>'.print_r($this->rounds,true).'</pre>' ),'');


		//for sub division ranking tables
		if ( $project->project_type=='DIVISIONS_LEAGUE' )
		{
			$selDivision = JRequest::getInt( 'division', 0 );
			$this->divLevel = JRequest::getInt( 'divLevel', $tableconfig['default_division_view'] );

			if ( $selDivision > 0 )
			{
				$url .= '&amp;division='.$selDivision;
				$divisions = array( $selDivision );
			}
			else
			{
				// check if division level view is allowed. if not, replace with default
				if ( ( $this->divLevel == 0 && $tableconfig['show_project_table']==0 ) ||
					 ( $this->divLevel == 1 && $tableconfig['show_level1_table']== 0 ) ||
					 ( $this->divLevel == 2 && $tableconfig['show_level2_table']==0  ) )
				{
					$this->divLevel = $tableconfig['default_division_view'];
				}
				$url .= '&amp;divLevel='.$this->divLevel;
				if ( $this->divLevel )
				{
					$divisions = sportsmanagementModelProject::getDivisionsId( $this->divLevel );
				//	print_r( $divisions);
				}
				else
				{
					$divisions = array(0);
				}
			}
		}
		else
		{
			$divisions = array(0); //project
		}
		$selectedvalue = 0;

		$last = JRequest::getInt( 'last', 0 );
		if ($last > 0)
		{
			$url .= '&amp;last='.$last;
		}
		if ( JRequest::getInt( 'sef', 0) == 1 )
		{
			$mainframe->redirect( JRoute::_( $url ) );
		}

		/**
		* create ranking object	
		*
		*/
		$ranking = JSMRanking::getInstance($project);
		$ranking->setProjectId( self::$projectid );
		
		foreach ( $divisions as $division )
		{

			//away rank
			if ($this->type == 2) {
				$this->currentRanking[$division] = $ranking->getRankingAway($this->from, $this->to,	$division);
			}
			//home rank
			else if ($this->type == 1) {
				$this->currentRanking[$division] = $ranking->getRankingHome($this->from, $this->to,	$division);
			}
			//total rank
			else {
				$this->currentRanking[$division] = $ranking->getRanking($this->from, $this->to,	$division);
				$this->homeRank[$division] = $ranking->getRankingHome($this->from, $this->to,	$division);
				$this->awayRank[$division] = $ranking->getRankingAway($this->from, $this->to,	$division);
			}
			$this->_sortRanking($this->currentRanking[$division]);

			
			//previous rank
			if( $tableconfig['last_ranking']==1 )
			{
				if ( $this->to == 1 || ( $this->to == $this->from ) )
				{
					$this->previousRanking[$division] = &$this->currentRanking[$division];
				}
				else
				{	
					//away rank
					if ($this->type == 2) {
						$this->previousRanking[$division] = $ranking->getRankingAway($this->from, $this->_getPreviousRoundId($this->to),	$division);
					}
					//home rank
					else if ($this->type == 1) {
						$this->previousRanking[$division] = $ranking->getRankingHome($this->from, $this->_getPreviousRoundId($this->to),	$division);
					}
					//total rank
					else {
						$this->previousRanking[$division] = $ranking->getRanking($this->from, $this->_getPreviousRoundId($this->to),	$division);
					}
					$this->_sortRanking($this->previousRanking[$division]);
				}
			}
		}
		$this->current_round = $this->round;
		return ;
	}
	
	
	/**
	 * sportsmanagementModelRanking::_getPreviousRoundId()
	 * 
	 * @param mixed $round_id
	 * @return
	 */
	function _getPreviousRoundId($round_id)
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection(TRUE, $mainframe->getUserState( "com_sportsmanagement.cfg_which_database", FALSE ) );
		$query = $db->getQuery(true);
        $starttime = microtime(); 
        
        // Select some fields
		$query->select('id');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
        $query->where('project_id = ' . self::$projectid);
        $query->order('roundcode ASC');

		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$res = $db->loadResultArray();
		
		if (!$res) {
			return $round_id;
		}
		
		$index = array_search($round_id, $res);
		if ($index && $index > 0) {
			return $res[$index - 1];
		}
		// if not found, return same round
		return $round_id;
	}

	/**************************************
	 * Compare functions for ordering     *
	 **************************************/

	/**
	 * sportsmanagementModelRanking::_sortRanking()
	 * 
	 * @param mixed $ranking
	 * @return
	 */
	function _sortRanking(&$ranking)
	{
		$order     = JRequest::getVar( 'order', '' );
		$order_dir = JRequest::getVar( 'dir', 'ASC' );

		switch ($order)
		{
			case 'played':
			uasort( $ranking, array("sportsmanagementModelRanking","playedCmp" ));
			break;				
			case 'name':
			uasort( $ranking, array("sportsmanagementModelRanking","teamNameCmp" ));
			break;
			case 'rank':
			break;
			case 'won':
			uasort( $ranking, array("sportsmanagementModelRanking","wonCmp" ));
			break;
			case 'draw':
			uasort( $ranking, array("sportsmanagementModelRanking","drawCmp" ));
			break;
			case 'loss':
			uasort( $ranking, array("sportsmanagementModelRanking","lossCmp" ));
			break;
			case 'wot':
			uasort( $ranking, array("sportsmanagementModelRanking","wotCmp" ));
			break;		
			case 'wso':
			uasort( $ranking, array("sportsmanagementModelRanking","wsoCmp" ));
			break;	
			case 'lot':
			uasort( $ranking, array("sportsmanagementModelRanking","lotCmp" ));
			break;		
			case 'lso':
			uasort( $ranking, array("sportsmanagementModelRanking","lsoCmp" ));
			break;			
			case 'winpct':
			uasort( $ranking, array("sportsmanagementModelRanking","winpctCmp" ));
			break;
			case 'quot':
			uasort( $ranking, array("sportsmanagementModelRanking","quotCmp" ));
			break;
			case 'goalsp':
			uasort( $ranking, array("sportsmanagementModelRanking","goalspCmp" ));
			break;
			case 'goalsfor':
			uasort( $ranking, array("sportsmanagementModelRanking","goalsforCmp" ));
			break;
			case 'goalsagainst':
			uasort( $ranking, array("sportsmanagementModelRanking","goalsagainstCmp" ));
			break;
			case 'legsdiff':
			uasort( $ranking, array("sportsmanagementModelRanking","legsdiffCmp" ));
			break;
			case 'legsratio':
			uasort( $ranking, array("sportsmanagementModelRanking","legsratioCmp" ));
			break;				
			case 'diff':
			uasort( $ranking, array("sportsmanagementModelRanking","diffCmp" ));
			break;
			case 'points':
			uasort( $ranking, array("sportsmanagementModelRanking","pointsCmp" ));
			break;
            
            case 'penaltypoints':
			uasort( $ranking, array("sportsmanagementModelRanking","penaltypointsCmp" ));
			break;
            
			case 'start':
			uasort( $ranking, array("sportsmanagementModelRanking","startCmp" ));
			break;
			case 'bonus':
			uasort( $ranking, array("sportsmanagementModelRanking","bonusCmp" ));
			break;
			case 'negpoints':
			uasort( $ranking, array("sportsmanagementModelRanking","negpointsCmp" ));
			break;
			case 'pointsratio':
			uasort( $ranking, array("sportsmanagementModelRanking","pointsratioCmp" ));
			break;			

			default:
				if (method_exists($this, $order.'Cmp')) {
					uasort( $ranking, array($this, $order.'Cmp'));
				}
				break;
		}
		if ($order_dir == 'DESC')
		{
			$ranking = array_reverse( $ranking, true );
		}
		return true;
	}

	/**
	 * sportsmanagementModelRanking::playedCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function playedCmp( &$a, &$b){
	  $res = $a->cnt_matches - $b->cnt_matches;
	  return $res;
	}	
	
	/**
	 * sportsmanagementModelRanking::teamNameCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function teamNameCmp( &$a, &$b){
	  return strcasecmp ($a->_name, $b->_name);
	}

	/**
	 * sportsmanagementModelRanking::wonCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function wonCmp( &$a, &$b){
	  $res = $a->cnt_won - $b->cnt_won;
	  return $res;
	}

	/**
	 * sportsmanagementModelRanking::drawCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function drawCmp( &$a, &$b){
	  $res = ($a->cnt_draw - $b->cnt_draw);
	  return $res;
	}

	/**
	 * sportsmanagementModelRanking::lossCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function lossCmp( &$a, &$b){
	  $res = ($a->cnt_lost - $b->cnt_lost);
	  return $res;
	}

	/**
	 * sportsmanagementModelRanking::wotCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function wotCmp( &$a, &$b){
	  $res = $a->cnt_wot - $b->cnt_wot;
	  return $res;
	}

	/**
	 * sportsmanagementModelRanking::wsoCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function wsoCmp( &$a, &$b){
	  $res = $a->cnt_wso - $b->cnt_wso;
	  return $res;
	}		
	
	/**
	 * sportsmanagementModelRanking::lotCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function lotCmp( &$a, &$b){
	  $res = $a->cnt_lot - $b->cnt_lot;
	  return $res;
	}
	
	/**
	 * sportsmanagementModelRanking::lsoCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function lsoCmp( &$a, &$b){
	  $res = $a->cnt_lso - $b->cnt_lso;
	  return $res;
	}	
	
	/**
	 * sportsmanagementModelRanking::winpctCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function winpctCmp( &$a, &$b){
	  $pct_a = $a->cnt_won/($a->cnt_won+$a->cnt_lost+$a->cnt_draw);
	  $pct_b = $b->cnt_won/($b->cnt_won+$b->cnt_lost+$b->cnt_draw);
	  $res =($pct_a < $pct_b);
	  return $res;
	}

	/**
	 * sportsmanagementModelRanking::quotCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function quotCmp( &$a, &$b){
	  $pct_a = $a->cnt_won/($a->cnt_won+$a->cnt_lost+$a->cnt_draw);
	  $pct_b = $b->cnt_won/($b->cnt_won+$b->cnt_lost+$b->cnt_draw);
	  $res =($pct_a < $pct_b);
	  return $res;
	}

	/**
	 * sportsmanagementModelRanking::goalspCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function goalspCmp( &$a, &$b){
	  $res = ($a->sum_team1_result - $b->sum_team1_result);
	  return $res;
	}

	/**
	 * sportsmanagementModelRanking::goalsforCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function goalsforCmp( &$a, &$b){
	  $res = ($a->sum_team1_result - $b->sum_team1_result);
	  return $res;
	}

	/**
	 * sportsmanagementModelRanking::goalsagainstCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function goalsagainstCmp( &$a, &$b){
	  $res = ($a->sum_team2_result - $b->sum_team2_result);
	  return $res;
	}	
	
	/**
	 * sportsmanagementModelRanking::legsdiffCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function legsdiffCmp( &$a, &$b){
	  $res = ($a->diff_team_legs - $b->diff_team_legs);
	  return $res;
	}

	/**
	 * sportsmanagementModelRanking::legsratioCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function legsratioCmp( &$a, &$b){
	  $res = ($a->legsRatio - $b->legsRatio);
	  return $res;
	}
	
	/**
	 * sportsmanagementModelRanking::diffCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function diffCmp( &$a, &$b){
	  $res = ($a->diff_team_results - $b->diff_team_results);
	  return $res;
	}

	/**
	 * sportsmanagementModelRanking::pointsCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function pointsCmp( &$a, &$b){
	  $res = ($a->getPoints() - $b->getPoints());
	  return $res;
	}
		
	/**
	 * sportsmanagementModelRanking::startCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function startCmp( &$a, &$b){
	  $res = ($a->team->start_points * $b->team->start_points);
	  return $res;
	}
	
	/**
	 * sportsmanagementModelRanking::bonusCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function bonusCmp( &$a, &$b){
	  $res = ($a->bonus_points - $b->bonus_points);
	  return $res;
	}
    
    /**
     * sportsmanagementModelRanking::penaltypointsCmp()
     * 
     * @param mixed $a
     * @param mixed $b
     * @return
     */
    function penaltypointsCmp( &$a, &$b){
	  $res = ($a->penalty_points - $b->penalty_points);
	  return $res;
	}

	/**
	 * sportsmanagementModelRanking::negpointsCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function negpointsCmp( &$a, &$b){
	  $res = ($a->neg_points - $b->neg_points);
	  return $res;
	}	

	/**
	 * sportsmanagementModelRanking::pointsratioCmp()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function pointsratioCmp( &$a, &$b){
	  $res = ($a->pointsRatio - $b->pointsRatio);
	  return $res;
	}	
	
}
?>