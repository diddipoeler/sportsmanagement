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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model' );

/**
 * sportsmanagementModelMatrix
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelMatrix extends JModel
{
	
    var $divisionid= 0;
    var $roundid= 0;
    var $projectid= 0;
    
    /**
	 * sportsmanagementModelMatrix::__construct()
	 * 
	 * @return
	 */
	function __construct( )
	{
		parent::__construct( );

		$this->divisionid = JRequest::getInt( 'division', 0 );
		$this->roundid = JRequest::getInt( 'r', 0 );
		$this->projectid = JRequest::getInt( 'p', 0 );
		sportsmanagementModelProject::$projectid = $this->projectid;
	}

	/**
	 * sportsmanagementModelMatrix::getDivisionID()
	 * 
	 * @return
	 */
	function getDivisionID( )
	{
		return $this->divisionid;
	}

	/**
	 * sportsmanagementModelMatrix::getRoundID()
	 * 
	 * @return
	 */
	function getRoundID( )
	{
		return $this->roundid;
	}

	/**
	 * sportsmanagementModelMatrix::getDivision()
	 * 
	 * @return
	 */
	function getDivision( )
	{
		$division = null;
		if ( $this->divisionid > 0 )
		{
			$division = & $this->getTable( "Division", "Table" );
			$division->load( $this->divisionid );
		}
		return $division;
	}

	/**
	 * sportsmanagementModelMatrix::getRound()
	 * 
	 * @return
	 */
	function getRound( )
	{
		$round = null;
		if ( $this->roundid > 0 )
		{
			$round = & $this->getTable( "Round", "Table" );
			$round->load( $this->roundid );
		}
		return $round;
	}

	
    /**
     * sportsmanagementModelMatrix::getRussiaMatrixResults()
     * 
     * @param mixed $teams
     * @param mixed $results
     * @return
     */
    function getRussiaMatrixResults($teams, $results )
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        //$russiamatrix = array();
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($teams,true).'</pre>'),'Notice');
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' results<br><pre>'.print_r($results,true).'</pre>'),'Notice');
        
        foreach ($teams as $team_row_id => $team_row) 
        {
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team_row_id<br><pre>'.print_r($team_row_id,true).'</pre>'),'Notice');
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team_row<br><pre>'.print_r($team_row,true).'</pre>'),'Notice');
        
        foreach ($teams as $team_col_id => $team_col) 
        {
            //$team_row->first = array();
        foreach ($results as $result) 
            {
            if (($result->projectteam1_id == $team_row->projectteamid) && ($result->projectteam2_id == $team_col->projectteamid)) 
                {
                //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team_col->projectteamid<br><pre>'.print_r($team_col->projectteamid,true).'</pre>'),'Notice');
                if ( isset($team_row->first[(int)$team_col->projectteamid]) )
                {
                $team_row->second[(int)$team_col->projectteamid] = new stdClass();     
                $team_row->second[(int)$team_col->projectteamid]->e1 = $result->e1;    
                $team_row->second[(int)$team_col->projectteamid]->e2 = $result->e2;
                
                $teams[$team_col->projectteamid]->second[(int)$team_row->projectteamid] = new stdClass();  
                $teams[$team_col->projectteamid]->second[(int)$team_row->projectteamid]->e1 = $result->e2;    
                $teams[$team_col->projectteamid]->second[(int)$team_row->projectteamid]->e2 = $result->e1;
                
                $team_row->second[(int)$team_col->projectteamid]->v1 = $result->v1;    
                $team_row->second[(int)$team_col->projectteamid]->v2 = $result->v2;
                $teams[$team_col->projectteamid]->second[(int)$team_row->projectteamid]->v1 = $result->v2;    
                $teams[$team_col->projectteamid]->second[(int)$team_row->projectteamid]->v2 = $result->v1;
                
                $teams[$team_col->projectteamid]->second[(int)$team_row->projectteamid]->decision = $result->decision;
                $team_row->second[(int)$team_col->projectteamid]->decision = $result->decision;
                
                $teams[$team_col->projectteamid]->second[(int)$team_row->projectteamid]->rtype = $result->rtype;
                $team_row->second[(int)$team_col->projectteamid]->rtype = $result->rtype;
                
                $teams[$team_col->projectteamid]->second[(int)$team_row->projectteamid]->id = $result->id;
                $team_row->second[(int)$team_col->projectteamid]->id = $result->id;
                
                $teams[$team_col->projectteamid]->second[(int)$team_row->projectteamid]->roundid = $result->roundid;
                $team_row->second[(int)$team_col->projectteamid]->roundid = $result->roundid;
                
                $teams[$team_col->projectteamid]->second[(int)$team_row->projectteamid]->new_match_id = $result->new_match_id;
                $team_row->second[(int)$team_col->projectteamid]->new_match_id = $result->new_match_id;
                
                $teams[$team_col->projectteamid]->second[(int)$team_row->projectteamid]->show_report = $result->show_report;
                $team_row->second[(int)$team_col->projectteamid]->show_report = $result->show_report;        
                            
                }
                else
                {
                $team_row->first[(int)$team_col->projectteamid] = new stdClass();    
                $team_row->first[(int)$team_col->projectteamid]->e1 = $result->e1;    
                $team_row->first[(int)$team_col->projectteamid]->e2 = $result->e2;
                
                $teams[$team_col->projectteamid]->first[(int)$team_row->projectteamid] = new stdClass();
                $teams[$team_col->projectteamid]->first[(int)$team_row->projectteamid]->e1 = $result->e2;    
                $teams[$team_col->projectteamid]->first[(int)$team_row->projectteamid]->e2 = $result->e1;
                
                $team_row->first[(int)$team_col->projectteamid]->v1 = $result->v1;    
                $team_row->first[(int)$team_col->projectteamid]->v2 = $result->v2;
                $teams[$team_col->projectteamid]->first[(int)$team_row->projectteamid]->v1 = $result->v2;    
                $teams[$team_col->projectteamid]->first[(int)$team_row->projectteamid]->v2 = $result->v1;
                
                $teams[$team_col->projectteamid]->first[(int)$team_row->projectteamid]->decision = $result->decision;
                $team_row->first[(int)$team_col->projectteamid]->decision = $result->decision;
                
                $teams[$team_col->projectteamid]->first[(int)$team_row->projectteamid]->rtype = $result->rtype;
                $team_row->first[(int)$team_col->projectteamid]->rtype = $result->rtype;
                
                $teams[$team_col->projectteamid]->first[(int)$team_row->projectteamid]->id = $result->id;
                $team_row->first[(int)$team_col->projectteamid]->id = $result->id;
                
                $teams[$team_col->projectteamid]->first[(int)$team_row->projectteamid]->roundid = $result->roundid;
                $team_row->first[(int)$team_col->projectteamid]->roundid = $result->roundid;
                
                $teams[$team_col->projectteamid]->first[(int)$team_row->projectteamid]->new_match_id = $result->new_match_id;
                $team_row->first[(int)$team_col->projectteamid]->new_match_id = $result->new_match_id;
                
                $teams[$team_col->projectteamid]->first[(int)$team_row->projectteamid]->show_report = $result->show_report;
                $team_row->first[(int)$team_col->projectteamid]->show_report = $result->show_report;
                
                }
                
                }    
            }    
        }
        }    
            
    //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($teams,true).'</pre>'),'Notice');
    
    
    return $teams;    
    }
    
	/**
	 * sportsmanagementModelMatrix::getMatrixResults()
	 * 
	 * @param mixed $project_id
	 * @param integer $unpublished
	 * @return
	 */
	function getMatrixResults( $project_id, $unpublished = 0 )
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        $starttime = microtime(); 
        
		//$query_WHERE = "";
		//$query_END	 = " ORDER BY roundcode";
        
        // Select some fields
        $query->select('DISTINCT(m.id),m.show_report,m.cancel,m.division_id AS division_id,m.cancel_reason,m.projectteam1_id,m.projectteam2_id');
		$query->select('m.team1_result as e1,m.team2_result as e2,m.match_result_type as rtype,m.alt_decision as decision,m.team1_result_decision AS v1');
        $query->select('m.team2_result_decision AS v2,m.new_match_id, m.old_match_id');
        $query->select('r.name AS roundname,r.id AS roundid,r.roundcode');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON r.id = m.round_id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS tt1 ON m.projectteam1_id = tt1.id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS tt2 ON m.projectteam2_id = tt2.id');
                
		if ( $this->divisionid > 0 )
		{
		  $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS d1 ON m.division_id = d1.id AND (	d1.id = '.$db->Quote($this->divisionid).' OR d1.parent_id = ' . $db->Quote($this->divisionid) . ' )');

		}


        $query->where('r.project_id = '.$project_id);

		if ( $this->roundid > 0 )
		{
            $query->where('m.round_id = ' . $this->_db->Quote($this->roundid));
		}
		if ( $unpublished != 1 )
		{
		  $query->where('m.published = 1');
		}
        
        $query->order('roundcode');

		$db->setQuery( $query );
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectid<br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
        
		if ( !$result = $db->loadObjectList() )
		{
		  $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
          $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
			//echo $db->getErrorMsg();
		}
		return $result;
	}

}
?>
