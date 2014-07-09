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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'prediction.php' );


/**
 * sportsmanagementModelPredictionResults
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPredictionResults extends JModel
{

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
  
  var $config = array();
  var $configavatar = array();

  
	/**
	 * sportsmanagementModelPredictionResults::__construct()
	 * 
	 * @return
	 */
	function __construct()
	{
	   $option = JRequest::getCmd('option');    
    $mainframe = JFactory::getApplication();
    
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
        
        $prediction = new sportsmanagementModelPrediction();  

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
       
		parent::__construct();
		
        //$this->pggrouprank			= JRequest::getInt('pggrouprank',		0);
		$option = JRequest::getCmd('option');    
    $mainframe = JFactory::getApplication();
    $this->predictionGameID	= JRequest::getInt('prediction_id',0);

if ( JRequest::getVar( "view") == 'predictionresults' )
{    
	// Get pagination request variables
	$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
	$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
 
	// In case limit has been changed, adjust it
	$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
 
	$this->setState('limit', $limit);
	$this->setState('limitstart', $limitstart);
}

    
	}

  /**
   * sportsmanagementModelPredictionResults::getPagination()
   * 
   * @return
   */
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
  
  
  /**
   * sportsmanagementModelPredictionResults::getTotal()
   * 
   * @return
   */
  function getTotal()
  {
 	// Load the content if it doesn't already exist
 	if (empty($this->_total)) 
     {
 	    //$query = $this->_buildQuery();
        $query = sportsmanagementModelPrediction::getPredictionMembersList($this->config,$this->configavatar,true);
 	    $this->_total = $this->_getListCount($query);	
 	}
 	return $this->_total;
  }
  
  /**
   * sportsmanagementModelPredictionResults::getData()
   * 
   * @return
   */
  function getData() 
  {
 	// if data hasn't already been obtained, load it
 	if (empty($this->_data)) 
     {
 	    //$query = $this->_buildQuery();
        $query = sportsmanagementModelPrediction::getPredictionMembersList($this->config,$this->configavatar,true);
 	    $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));	
 	}
 	return $this->_data;
  }

  
	
	/**
	 * sportsmanagementModelPredictionResults::getMatches()
	 * 
	 * @param mixed $roundID
	 * @param mixed $project_id
	 * @param mixed $match_ids
	 * @param mixed $round_ids
	 * @param mixed $proteams_ids
	 * @return
	 */
	function getMatches($roundID,$project_id,$match_ids,$round_ids,$proteams_ids)
	{
	  //global $mainframe, $option;
      $option = JRequest::getCmd('option'); 
    $document	= JFactory::getDocument();
    $mainframe	= JFactory::getApplication();
    
//    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' roundID<br><pre>'.print_r($roundID,true).'</pre>'),'');
//    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_id<br><pre>'.print_r($project_id,true).'</pre>'),'');
//    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' match_ids<br><pre>'.print_r($match_ids,true).'</pre>'),'');
//    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' round_ids<br><pre>'.print_r($round_ids,true).'</pre>'),'');
//    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' proteams_ids<br><pre>'.print_r($proteams_ids,true).'</pre>'),'');
    
    // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
    
		if ($roundID==0)
        {
			$roundID=1;
		}
        
        $query->select('m.id AS mID,m.match_date,m.team1_result AS homeResult,m.team2_result AS awayResult,m.team1_result_decision AS homeDecision,m.team2_result_decision AS awayDecision');
        $query->select('t1.name AS homeName,t1.short_name AS homeShortName');
        $query->select('t2.name AS awayName,t2.short_name AS awayShortName');
        $query->select('c1.logo_small AS homeLogo,c1.country AS homeCountry');
        $query->select('c2.logo_small AS awayLogo,c2.country AS awayCountry');
        
        // das grosse logo muss auch noch selektiert werden
        $query->select('c1.logo_big AS homeLogobig');
        $query->select('c2.logo_big AS awayLogobig');
        
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m');
        $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON r.id = m.round_id');
        
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON pt1.id = m.projectteam1_id');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON pt2.id = m.projectteam2_id');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st1 on st1.id = pt1.team_id');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st2 on st2.id = pt2.team_id');
        
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id = st1.team_id');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id = st2.team_id');
        
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_club AS c1 ON c1.id = t1.club_id');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_club AS c2 ON c2.id = t2.club_id');
        
        
        $query->where('r.project_id = '.$project_id);
        //$query->where('r.id = '.$roundID);
        
        $query->where('(m.cancel IS NULL OR m.cancel = 0)');
        $query->where('m.published = 1');
    
    // bestimmte mannschaften selektieren
    if ( $proteams_ids )
    {
    $query->where('( m.projectteam1_id IN (' . implode(',', $proteams_ids) . ')'.' OR '.'m.projectteam2_id IN (' . implode(',', $proteams_ids) . ') )' );    
    }
    
    // bestimmte runden selektieren
    if ( $round_ids )
    {
    $query->where('r.id IN (' . implode(',', $round_ids) . ')');    
    }
    else
    {
    $query->where('r.id = '.$roundID);
    }
        
    // bestimmte spiele selektieren
    if ( $match_ids )
    {
    $query->where('m.id IN (' . implode(',', $match_ids) . ')');    
    }
    
    $query->order('m.match_date, m.id ASC');
						
		$db->setQuery( $query );
		$results = $db->loadObjectList();
		return $results;
	}

	/**
	 * sportsmanagementModelPredictionResults::showClubLogo()
	 * 
	 * @param mixed $clubLogo
	 * @param mixed $teamName
	 * @return
	 */
	function showClubLogo($clubLogo,$teamName)
	{
	  $mainframe = JFactory::getApplication();
		$document	= JFactory::getDocument();
		$uri = JFactory::getURI();
    $option = JRequest::getCmd('option');
    
		$output = '';
		if ((!isset($clubLogo)) || ($clubLogo=='') || (!file_exists($clubLogo)))
		{
			$clubLogo='images/com_sportsmanagement/database/placeholders/placeholder_small.gif';
		}
		$imgTitle = JText::sprintf('COM_SPORTSMANAGEMENT_PRED_RESULTS_LOGO_OF',$teamName);
		$output .= JHTML::image($clubLogo,$imgTitle,array(' width' => 20, ' title' => $imgTitle));
		return $output;
	}

}
?>