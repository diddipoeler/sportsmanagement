<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die(JText('Restricted access'));

jimport('joomla.application.component.model');
jimport('joomla.html.pane');
JHtml::_('behavior.tooltip');

//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

/**
 * sportsmanagementModelResults
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelResults extends JModel
{
	var $projectid	= 0;
	var $divisionid	= 0;
	var $roundid = 0;
	var $rounds = array(0);
	var $mode = 0;
	var $order = 0;
	var $config = 0;
	var $project = null;
	var $matches = null;

	/**
	 * sportsmanagementModelResults::__construct()
	 * 
	 * @return
	 */
	function __construct()
	{
		parent::__construct();

		$this->divisionid = JRequest::getInt('division',0);
		$this->mode = JRequest::getInt('mode',0);
		$this->order = JRequest::getInt('order',0);
		$round = JRequest::getInt('r', -1);
		$roundid = $round;
		if($round) {
			$roundid = $round;
		} else {
			$roundid = $this->getCurrentRound();
		}
		$this->roundid = $roundid;
		$this->config = sportsmanagementModelProject::getTemplateConfig('results');
	}

	/**
	 * sportsmanagementModelResults::getDivisionID()
	 * 
	 * @return
	 */
	function getDivisionID()
	{
		return $this->divisionid;
	}

	/**
	 * sportsmanagementModelResults::getDivision()
	 * 
	 * @return
	 */
	function getDivision()
	{
		$division=null;
		if ($this->divisionid > 0)
		{
			$division = $this->getTable('Division','sportsmanagementTable');
			$division->load($this->divisionid);
		}

		return $division;
	}
    
    // limit count word
	/**
	 * sportsmanagementModelResults::limitText()
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
     * sportsmanagementModelResults::getRssFeeds()
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
	 * sportsmanagementModelResults::getMatches()
	 * 
	 * @return
	 */
	function getMatches()
	{
		if (is_null($this->matches))
		{
			$this->matches = self::getResultsRows($this->roundid,$this->divisionid,$this->config);
		}
		
		$allowed = $this->isAllowed();
		$user = JFactory::getUser();

		if (count($this->matches)>0)
		{
			foreach ($this->matches as $k => $match)
			{
				if (($match->checked_out==0)||($match->checked_out==$user->id))
				{
					if (($allowed)||($this->isMatchAdmin($match->id,$user->id)))
					{
						$this->matches[$k]->allowed=true;
					}
				}
			}
		}
		return $this->matches;
	}

	
	/**
	 * sportsmanagementModelResults::getResultsRows()
	 * 
	 * @param mixed $round
	 * @param mixed $division
	 * @param mixed $config
	 * @return
	 */
	function getResultsRows($round,$division,&$config)
	{
		$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $project = sportsmanagementModelProject::getProject();

		if (!$round) 
        {
			$round = sportsmanagementModelProject::getCurrentRound();
		}

		$result = array();
        // select some fields
        $query->select('m.*,DATE_FORMAT(m.time_present,"%H:%i") time_present');
        $query->select('playground.name AS playground_name,playground.short_name AS playground_short_name');
        $query->select('pt1.project_id');
        $query->select('d1.name as divhome');
        $query->select('d2.name as divaway');
        $query->select('CASE WHEN CHAR_LENGTH(t1.alias) AND CHAR_LENGTH(t2.alias) THEN CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) ELSE m.id END AS slug ');
        // from 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m');
        // join
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON m.round_id=r.id ');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON m.projectteam1_id = pt1.id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON m.projectteam2_id = pt2.id');
        
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st1 ON st1.id = pt1.team_id ');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st2 ON st2.id = pt2.team_id ');
        
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id = st1.team_id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id = st2.team_id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS d1 ON pt1.division_id = d1.id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS d2 ON pt2.division_id = d2.id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_playground AS playground ON playground.id = m.playground_id');
		
        // where
        $query->where('m.published = 1');
        $query->where('r.id='.$round);
        $query->where('r.project_id = '.(int)$project->id);
        // group
        $query->group('m.id ');
        // order
        $query->order('m.match_date ASC,m.match_number');    

		if ($division>0)
		{
		  $query->where('(d1.id = '.$division.' OR d1.parent_id = '.$division.' OR d2.id = '.$division.' OR d2.parent_id = '.$division.')');
		}

		if (!is_null($round)) 
        {
			$db->setQuery($query);
            $result = $db->loadObjectList();
		}
		
        if ( !$result )
	    {
		$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
	    }
        
		return $result;
	}

	
	/**
	 * sportsmanagementModelResults::getMatchReferees()
	 * 
	 * @param mixed $match_id
	 * @return
	 */
	function getMatchReferees($match_id)
	{
		$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query='	SELECT	pref.id AS person_id,
								p.firstname,
								p.lastname,
								pos.name AS position_name
						FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mr
							LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pref ON mr.project_referee_id=pref.id
							INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON pref.person_id=p.id
							LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON mr.project_position_id=ppos.id
							LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id=pos.id
						WHERE mr.match_id='.(int)$match_id.' AND p.published = 1 ORDER BY pos.name,mr.ordering ';
		$this->_db->setQuery($query);
        
        $result = $this->_db->loadObjectList();
        if ( !$result )
	    {
		$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
	    }
        
		return $result;
		
	}

	
	/**
	 * sportsmanagementModelResults::getMatchRefereeTeams()
	 * 
	 * @param mixed $match_id
	 * @return
	 */
	function getMatchRefereeTeams($match_id)
	{
		$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query='	SELECT	mr.project_referee_id AS value,
								t.name AS teamname,
								pos.name AS position_name

						FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mr
							LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.id=mr.project_referee_id
							LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id=pt.team_id
							LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON mr.project_position_id=pos.id

						WHERE mr.match_id='.(int) $match_id.' ORDER BY pos.name,mr.ordering ASC ';

		$this->_db->setQuery($query);
        
        $result = $this->_db->loadObjectList('value');
        if ( !$result )
	    {
		$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
	    }
        
		return $result;

	}

	/**
	 * sportsmanagementModelResults::isTeamEditor()
	 * 
	 * @param mixed $userid
	 * @return
	 */
	function isTeamEditor($userid)
	{
		if ($userid > 0)
		{
			$query=' SELECT tt.admin '
			. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS tt '
			. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ON (m.projectteam1_id=tt.id OR m.projectteam2_id=tt.id)'
			. ' WHERE tt.project_id='. $this->_db->Quote($this->projectid)
			. '   AND tt.admin= '. $this->_db->Quote($userid)
			;
			$this->_db->setQuery($query);
			if ($this->_db->loadResult()) return true;
		}
		return false;
	}

	/**
	 * sportsmanagementModelResults::isMatchAdmin()
	 * 
	 * @param mixed $matchid
	 * @param mixed $userid
	 * @return
	 */
	function isMatchAdmin($matchid,$userid)
	{
		$query=' SELECT COUNT(*) FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON m.projectteam1_id=pt1.id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON m.projectteam2_id=pt2.id '
		. ' WHERE m.id='.$matchid
		. ' AND (pt1.admin='.$userid.' OR pt2.admin='.$userid.')'
		;
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}

	/**
	 * sportsmanagementModelResults::isAllowed()
	 * 
	 * @return
	 */
	function isAllowed()
	{
		$allowed = false;
		$user = JFactory::getUser();
		if ($user->id != 0)
		{
			$project =& $this->getProject();
			$hasACLPermssion = $user->authorise('results.saveshort', 'com_sportsmanagement');
			$isProjectAdmin = $user->id == $project->admin;
			$isProjectEditor = $user->id == $project->editor;
			
			if( $hasACLPermssion && ($isProjectAdmin || $isProjectEditor) )
			{
				$allowed = true;
			}
		}
		return $allowed;
	}

	/**
	 * sportsmanagementModelResults::getShowEditIcon()
	 * 
	 * @return
	 */
	function getShowEditIcon()
	{
		$allowed = $this->isAllowed();
		$showediticon = false;

		$user = JFactory::getUser();
		if ($user->id != 0)
		{
			if ($allowed || ($this->isTeamEditor($user->id)))
			{
				$showediticon=true;
			}
		}
		return $showediticon;
	}

	/**
	 * sportsmanagementModelResults::save_array()
	 * 
	 * @param mixed $cid
	 * @param mixed $post
	 * @param bool $zusatz
	 * @param mixed $project_id
	 * @return
	 */
	function save_array($cid=null,$post=null,$zusatz=false,$project_id)
	{
		$datatable[0]='#__'.COM_SPORTSMANAGEMENT_TABLE.'_match';
		$fields=$this->_db->getTableFields($datatable);

		foreach($fields as $field)
		{
			$query='';
			$datafield=array_keys($field);
			if ($zusatz){$fieldzusatz=$cid;}

			foreach($datafield as $keys)
			{
				if (isset($post[$keys.$fieldzusatz]))
				{
					$result=$post[$keys.$fieldzusatz];

					if ($keys=='match_date')
					{
						if(strpos($post['match_time'.$fieldzusatz],":")!==false)
						{
							$result .= ' '.$post['match_time'.$fieldzusatz];
						}
						// to support short time inputs
						// for example 2158 is used instead of 21:58
						else {
							$result .= ' '.substr($post['match_time'.$fieldzusatz], 0, -2) . ':' . substr($post['match_time'.$fieldzusatz], -2);
						}
					}
					if ($keys=='team1_result_split' || $keys=='team2_result_split' || $keys=='homeroster' || $keys=='awayroster')
					{
						$result=trim(join(';',$result));
					}
					if ($keys=='alt_decision' && $post[$keys.$fieldzusatz]==0)
					{
						$query.=",team1_result_decision=NULL,team2_result_decision=NULL,decision_info=''";
					}
					if ($keys=='team1_result_decision' && strtoupper($post[$keys.$fieldzusatz])=='X' && $post['alt_decision'.$fieldzusatz]==1)
					{
						$result='';
					}
					if ($keys=='team2_result_decision' && strtoupper($post[$keys.$fieldzusatz])=='X' && $post['alt_decision'.$fieldzusatz]==1)
					{
						$result='';
					}
					if (!is_numeric($result) || ($keys == 'match_number') || ($keys == 'time_present'))
					{
						$vorzeichen="'";
					}
					else
					{
						$vorzeichen='';
					}
					if (strstr("crowd,formation1,formation2,homeroster,awayroster,show_report,team1_result,team1_bonus,team1_legs,
									team2_result,team2_bonus,team2_legs,team1_result_decision,team2_result_decision,team1_result_split,
			 						team2_result_split,team1_result_ot,team2_result_ot,published,",$keys.',') &&
					$result=='' && isset($post[$keys.$fieldzusatz]))
					{
						$result='NULL';
						$vorzeichen='';
					}
					if ($keys=='crowd' && $post['crowd'.$fieldzusatz]==''){$result='0';}

					if ($result!='' || $keys=='summary' || $keys=='match_result_detail')
					{
						if ($query){$query .= ',';}
						$query .= $keys.'='.$vorzeichen.$result.$vorzeichen;
					}
					if ($result=='' && $keys=='time_present')
					{
						if ($query){$query .= ',';}
						$query .= $keys.'=null';
					}
					if ($result=='' && $keys=='match_number')
					{
						if ($query){$query .= ',';}
						$query .= $keys.'=null';
					}
				}
			}
		}

		$user 	= JFactory::getUser();
		$query	= 'UPDATE #__'.COM_SPORTSMANAGEMENT_TABLE.'_match SET '.$query.',`modified`=NOW(),`modified_by`='.$user->id.' WHERE id='.$cid;
//		echo '<br /><pre>'.print_r($query,true).'</pre><br />';
		$this->_db->setQuery($query);

		return $this->_db->query($query);
	}

	/**
	 * sportsmanagementModelResults::getFavTeams()
	 * 
	 * @param mixed $project
	 * @return
	 */
	function getFavTeams(&$project)
	{
		$favteams=explode(',',$project->fav_team);
		return $favteams;
	}
}
?>