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


// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

//jimport( 'joomla.application.component.model' );
jimport('joomla.application.component.modelitem');
jimport('joomla.filesystem.file');
jimport('joomla.utilities.array');
jimport('joomla.utilities.arrayhelper') ;
jimport( 'joomla.utilities.utility' );



require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'project.php' );
require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'prediction.php' );

/**
 * sportsmanagementModelPredictionRanking
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPredictionRanking extends JModel
//class JoomleagueModelPredictionRanking extends JoomleagueModel
{
	var $_roundNames = null;
    var $predictionGameID = 0;
    
   /**
   * Items total
   * @var integer
   */
  var $_total = null;
 
  /**
   * Pagination object
   * @var object
   */
  var $_pagination = null;
  
  
	function __construct()
	{
	   $option = JRequest::getCmd('option');    
    $mainframe = JFactory::getApplication();
    
		parent::__construct();
        
        $this->predictionGameID		= JRequest::getInt('prediction_id',		0);
		$this->predictionMemberID	= JRequest::getInt('uid',	0);
		$this->joomlaUserID			= JRequest::getInt('juid',	0);
		$this->roundID				= JRequest::getInt('r',		0);
        $this->pggroup				= JRequest::getInt('pggroup',		0);
        $this->pggrouprank			= JRequest::getInt('pggrouprank',		0);
		$this->pjID					= JRequest::getInt('p',		0);
		$this->isNewMember			= JRequest::getInt('s',		0);
		$this->tippEntryDone		= JRequest::getInt('eok',	0);

		$this->from  				= JRequest::getInt('from',	$this->roundID);
		$this->to	 				= JRequest::getInt('to',	$this->roundID);
		$this->type  				= JRequest::getInt('type',	0);

		$this->page  				= JRequest::getInt('page',	1);
        
        //$prediction = JModel::getInstance("Prediction","sportsmanagementModel");
        $prediction = new sportsmanagementModelPrediction();  
        //$prediction->predictionGameID = $this->predictionGameID	;
        sportsmanagementModelPrediction::$predictionGameID = $this->predictionGameID;
        
        sportsmanagementModelPrediction::$predictionMemberID = $this->predictionMemberID;
        sportsmanagementModelPrediction::$joomlaUserID = $this->joomlaUserID;
        sportsmanagementModelPrediction::$roundID = $this->roundID;
        sportsmanagementModelPrediction::$pggroup = $this->pggroup;
        sportsmanagementModelPrediction::$pggrouprank = $this->pggrouprank;
        sportsmanagementModelPrediction::$pjID = $this->pjID;
        sportsmanagementModelPrediction::$isNewMember = $this->isNewMember;
        sportsmanagementModelPrediction::$tippEntryDone = $this->tippEntryDone;
        sportsmanagementModelPrediction::$from = $this->from;
        sportsmanagementModelPrediction::$to = $this->to;
        sportsmanagementModelPrediction::$type = $this->type;
        sportsmanagementModelPrediction::$page = $this->page;
        
	   //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' predictionGameID<br><pre>'.print_r($this->predictionGameID,true).'</pre>'),'');
       
        //$this->pggrouprank			= JRequest::getInt('pggrouprank',		0);
  
//    $option = JRequest::getCmd('option');    
//    $mainframe = JFactory::getApplication();
//    $this->predictionGameID	= JRequest::getInt('prediction_id',0);

if ( JRequest::getVar( "view") == 'predictionranking' )
{
	// Get pagination request variables
	$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
	$limitstart = JRequest::getVar('limitstart', 0, '', 'int');

	// In case limit has been changed, adjust it
	$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
 
	$this->setState('limit', $limit);
	$this->setState('limitstart', $limitstart);
}

//$mainframe->enqueueMessage(JText::_('PredictionRanking __construct limit -> '.'<pre>'.print_r($limit ,true).'</pre>' ),'');
//$mainframe->enqueueMessage(JText::_('PredictionRanking__construct view-> '.'<pre>'.print_r(JRequest::getVar( "view"),true).'</pre>' ),'');   
    
	}

function _buildQuery()
{
    $option = JRequest::getCmd('option');    
    $mainframe = JFactory::getApplication();
    // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
    
    // Select some fields
    $query->select('pm.id AS pmID,pm.user_id AS user_id,pm.picture AS avatar,pm.group_id,pm.show_profile AS show_profile,pm.champ_tipp AS champ_tipp,pm.aliasName as aliasName');
    $query->select('u.name AS name');
    $query->select('pg.id as pg_group_id,pg.name as pg_group_name');
    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_member AS pm');
    $query->join('INNER', '#__users AS u ON u.id = pm.user_id');
    $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_groups as pg on pg.id = pm.group_id');
    $query->where('pm.prediction_id = '.$this->predictionGameID);
            
//	$query=	"	SELECT	pm.id AS pmID,
//				pm.user_id AS user_id,
//				pm.picture AS avatar,
//                pm.group_id,
//				pm.show_profile AS show_profile,
//				pm.champ_tipp AS champ_tipp,
//        pm.aliasName as aliasName,
//				u.name AS name,
//                pg.id as pg_group_id,
//                pg.name as pg_group_name
//				FROM #__sportsmanagement_prediction_member AS pm
//				INNER JOIN #__users AS u ON u.id = pm.user_id
//                left join #__sportsmanagement_prediction_groups as pg
//                on pg.id = pm.group_id
//				WHERE pm.prediction_id = $this->predictionGameID";
    if ( $this->pggrouprank )
    {
   // $query .= " GROUP BY pm.group_id ";    
//    $query .= " ORDER BY pm.group_id ASC";  
    $query->group('pm.group_id');
    $query->order('pm.group_id ASC');  
    }   
    else
    {
    //$query .=	" ORDER BY pm.id ASC";
    $query->order('pm.id ASC');     
    }         
	
                
return $query;                            
}

function getData() 
  {
 	// if data hasn't already been obtained, load it
 	if (empty($this->_data)) 
     {
 	    $query = $this->_buildQuery();
        //$query = $this->getPredictionMember();
 	    $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));	
 	}
 	return $this->_data;
  }
  
function getTotal()
  {
 	// Load the content if it doesn't already exist
 	if (empty($this->_total)) 
     {
 	    $query = $this->_buildQuery();
        //$query = $this->getPredictionMember();
 	    $this->_total = $this->_getListCount($query);	
 	}
 	return $this->_total;
  }

function getPagination()
  {
 	// Load the content if it doesn't already exist
 	if (empty($this->_pagination)) 
     {
 	    jimport('joomla.html.pagination');
 	    $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
 	}
 	return $this->_pagination;
  }    



  function getDebugInfo()
  {
  $show_debug_info = JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0);
  if ( $show_debug_info )
  {
  return true;
  }
  else
  {
  return false;
  }
  
  }
  
	
    function getChampLogo($ProjectID,$champ_tipp)
    {
    $option = JRequest::getCmd('option');
	$mainframe	=& JFactory::getApplication();
    
    $sChampTeamsList=explode(';',$champ_tipp);
	foreach ($sChampTeamsList AS $key => $value){$dChampTeamsList[]=explode(',',$value);}
	foreach ($dChampTeamsList AS $key => $value){$champTeamsList[$value[0]]=$value[1];}    
    
    //$mainframe->enqueueMessage(JText::_('champTeamsList -> '.'<pre>'.print_r($champTeamsList,true).'</pre>' ),'');
    
    $projectteamid = $champTeamsList[$ProjectID];  
    $teaminfo = JoomleagueModelProject::getTeaminfo($projectteamid);
    //$mainframe->enqueueMessage(JText::_('champTeamsList -> '.'<pre>'.print_r($teaminfo->logo_big,true).'</pre>' ),'');
    return $teaminfo;
      
    }
    
    function getMatches($roundID,$project_id)
	{
		if ($roundID==0){$roundID=1;}
		$query = 	"	SELECT	m.id AS mID,
								m.match_date,
								m.team1_result AS homeResult,
								m.team2_result AS awayResult,
								m.team1_result_decision AS homeDecision,
								m.team2_result_decision AS awayDecision,
								t1.name AS homeName,
								t2.name AS awayName,
								c1.logo_small AS homeLogo,
								c2.logo_small AS awayLogo

						FROM #__joomleague_match AS m

						INNER JOIN #__joomleague_round AS r ON	r.id=m.round_id AND
																r.project_id=$project_id AND
																r.id=$roundID
						LEFT JOIN #__joomleague_project_team AS pt1 ON pt1.id=m.projectteam1_id
						LEFT JOIN #__joomleague_project_team AS pt2 ON pt2.id=m.projectteam2_id
						LEFT JOIN #__joomleague_team AS t1 ON t1.id=pt1.team_id
						LEFT JOIN #__joomleague_team AS t2 ON t2.id=pt2.team_id
						LEFT JOIN #__joomleague_club AS c1 ON c1.id=t1.club_id
						LEFT JOIN #__joomleague_club AS c2 ON c2.id=t2.club_id
						WHERE (m.cancel IS NULL OR m.cancel = 0)
						ORDER BY m.match_date, m.id ASC";
		$this->_db->setQuery( $query );
		//echo($this->_db->getQuery( ));
		$results = $this->_db->loadObjectList();
		return $results;
	}

	function createFromMatchdayList($project_id)
	{
		$from_matchday=array();
		$from_matchday[]= JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_RANKING_FROM_MATCHDAY'));
		$from_matchday=array_merge($from_matchday,sportsmanagementModelPrediction::getRoundNames($project_id));
		return $from_matchday;
	}

	function createToMatchdayList($project_id)
	{
		$to_matchday=array();
		$to_matchday[]=JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_RANKING_TO_MATCHDAY'));
		$to_matchday=array_merge($to_matchday,sportsmanagementModelPrediction::getRoundNames($project_id));
		return $to_matchday;
	}
	


}
?>