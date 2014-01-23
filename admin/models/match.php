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

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');




/**
 * sportsmanagementModelMatch
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelMatch extends JModelAdmin
{

	const MATCH_ROSTER_STARTER			= 0;
	const MATCH_ROSTER_SUBSTITUTE_IN	= 1;
	const MATCH_ROSTER_SUBSTITUTE_OUT	= 2;
	const MATCH_ROSTER_RESERVE			= 3;

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_sportsmanagement.message.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}
    
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'match', $prefix = 'sportsmanagementTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
    
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_sportsmanagement.match', 'match', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
		return $form;
	}
    
	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string	Script files
	 */
	public function getScript() 
	{
		return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
	}
    
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.match.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
	}
	
	/**
	 * Method to save item order
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function saveorder($pks = NULL, $order = NULL)
	{
		$row =& $this->getTable();
		
		// update ordering values
		for ($i=0; $i < count($pks); $i++)
		{
			$row->load((int) $pks[$i]);
			if ($row->ordering != $order[$i])
			{
				$row->ordering=$order[$i];
				if (!$row->store())
				{
					sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
					return false;
				}
			}
		}
		return true;
	}
    
    /**
	 * Method to update checked project rounds
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function saveshort()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        // Get the input
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        $post = JRequest::get('post');
        
        
        //$mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.' pks<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
        //$mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.' post<br><pre>'.print_r($post, true).'</pre><br>','Notice');
        
        
        $result=true;
		for ($x=0; $x < count($pks); $x++)
		{
			// änderungen im datum oder der uhrzeit
            $tbl = $this->getTable();;
            $tbl->load((int) $pks[$x]);
            
            list($date,$time) = explode(" ",$tbl->match_date);
            $this->_match_time_new = $post['match_time'.$pks[$x]].':00';
            $this->_match_date_new = $post['match_date'.$pks[$x]];
            $this->_match_time_old = $time;
            $this->_match_date_old = sportsmanagementHelper::convertDate($date);
            
            $post['match_date'.$pks[$x]] = sportsmanagementHelper::convertDate($post['match_date'.$pks[$x]],0);
            $post['match_date'.$pks[$x]] = $post['match_date'.$pks[$x]].' '.$post['match_time'.$pks[$x]].':00';
            
            
              
            //$mainframe->enqueueMessage($post['match_date'.$pks[$x]],'Notice');
            //$mainframe->enqueueMessage($tbl->match_date,'Notice');
            
            if ( $post['match_date'.$pks[$x]] != $tbl->match_date )
            {
                $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_ADMIN_CHANGE'),'Notice');
                self::sendEmailtoPlayers();
                
            }
            
            $tblMatch = & $this->getTable();
			$tblMatch->id = $pks[$x];
			$tblMatch->match_number	= $post['match_number'.$pks[$x]];
            $tblMatch->match_date = $post['match_date'.$pks[$x]];
            //$tblMatch->match_time = $post['match_time'.$pks[$x]];
            $tblMatch->crowd = $post['crowd'.$pks[$x]];
            $tblMatch->round_id	= $post['round_id'.$pks[$x]];
            $tblMatch->division_id	= $post['division_id'.$pks[$x]];
            $tblMatch->projectteam1_id = $post['projectteam1_id'.$pks[$x]];
            $tblMatch->projectteam2_id = $post['projectteam2_id'.$pks[$x]];
            
            if ( $post['use_legs'] )
            {
                $tblMatch->team1_result	= '';
                $tblMatch->team2_result	= '';   
                foreach ( $post['team1_result_split'.$pks[$x]] as $key => $value )
                {
                    if ( $post['team1_result_split'.$pks[$x]][$key] != '' )
                    {
                        if ( $post['team1_result_split'.$pks[$x]][$key] > $post['team2_result_split'.$pks[$x]][$key] )
                        {
                            $tblMatch->team1_result	+= 1;
                            $tblMatch->team2_result	+= 0; 
                        }
                        if ( $post['team1_result_split'.$pks[$x]][$key] < $post['team2_result_split'.$pks[$x]][$key] )
                        {
                            $tblMatch->team1_result	+= 0;
                            $tblMatch->team2_result	+= 1; 
                        }
                        if ( $post['team1_result_split'.$pks[$x]][$key] == $post['team2_result_split'.$pks[$x]][$key] )
                        {
                            $tblMatch->team1_result	+= 1;
                            $tblMatch->team2_result	+= 1; 
                        }
                    }
                }
            }
            else
            {
            
            if ( $post['team1_result'.$pks[$x]] != '' && $post['team2_result'.$pks[$x]] != '' )    
            {    
            $tblMatch->team1_result	= $post['team1_result'.$pks[$x]];
            $tblMatch->team2_result	= $post['team2_result'.$pks[$x]];
            }
                
            }
            
            
            $tblMatch->team1_result_split	= implode(";",$post['team1_result_split'.$pks[$x]]);
            $tblMatch->team2_result_split	= implode(";",$post['team2_result_split'.$pks[$x]]);

			if(!$tblMatch->store()) 
            {
				//$this->setError($this->_db->getErrorMsg());
                $mainframe->enqueueMessage('sportsmanagementModelMatch saveshort<br><pre>'.print_r($this->_db->getErrorMsg(), true).'</pre><br>','Error');
				$result = false;
			}
		}
		return $result;
	}
    
    /**
	 * Method to remove a matchday
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function delete($pk=array())
	{
	$mainframe =& JFactory::getApplication();
    /* Ein Datenbankobjekt beziehen */
    $db = JFactory::getDbo();
    /* Ein JDatabaseQuery Objekt beziehen */
    $query = $db->getQuery(true);
    
    //$mainframe->enqueueMessage(JText::_('match delete pk<br><pre>'.print_r($pk,true).'</pre>'   ),'');
    
	$result = false;
    if (count($pk))
		{
			//JArrayHelper::toInteger($cid);
			$cids = implode(',',$pk);
            // wir löschen mit join
            $query = 'DELETE ms,mss,mst,mev,mre,mpl
            FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match as m    
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic as ms
            ON ms.match_id = m.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff_statistic as mss
            ON mss.match_id = m.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff as mst
            ON mst.match_id = m.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event as mev
            ON mev.match_id = m.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee as mre
            ON mre.match_id = m.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player as mpl
            ON mpl.match_id = m.id
            WHERE m.id IN ('.$cids.')';
            $db->setQuery($query);
            $db->query();
            if (!$db->query()) 
            {
                $mainframe->enqueueMessage(JText::_('match delete query getErrorMsg<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            }
            
            //$mainframe->enqueueMessage(JText::_('match delete query<br><pre>'.print_r($query,true).'</pre>'   ),'');
            
            return parent::delete($pk);
        }    
   return true;     
   }
   
   
    
    /**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
	   $mainframe = JFactory::getApplication();
       $post=JRequest::get('post');
       
       //$mainframe->enqueueMessage(JText::_('sportsmanagementModelplayground save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
       //$mainframe->enqueueMessage(JText::_('sportsmanagementModelplayground post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       if (isset($post['extended']) && is_array($post['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string)$parameter;
		}
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelplayground save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        // Proceed with the save
		return parent::save($data);   
    }
      
    
     /**
	 * Method to load content matchday data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function getMatchData($match_id)
	{
		$option = JRequest::getCmd('option');
	   $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        // Select some fields
        $query->select('m.*,CASE m.time_present	when NULL then NULL	else DATE_FORMAT(m.time_present, "%H:%i") END AS time_present,m.extended as matchextended');
        $query->select('t1.name AS hometeam, t1.id AS t1id');
        $query->select('t2.name as awayteam, t2.id AS t2id');
        $query->select('pt1.project_id');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON pt1.id = m.projectteam1_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st1 ON st1.id = pt1.team_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id = st1.team_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON pt2.id = m.projectteam2_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st2 ON st2.id = pt2.team_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id = st2.team_id ');
        // Where
        $query->where('m.id = '.(int) $match_id );
        
/**
 * 			$query=' SELECT	m.*,
 * 							CASE m.time_present
 * 							when NULL then NULL
 * 							else DATE_FORMAT(m.time_present, "%H:%i")
 * 							END AS time_present,
 * 							t1.name AS hometeam, t1.id AS t1id,
 * 							t2.name as awayteam, t2.id AS t2id,
 * 							pt1.project_id,
 * 							m.extended as matchextended
 * 						FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m
 * 						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON pt1.id=m.projectteam1_id
 * 						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id=pt1.team_id
 * 						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON pt2.id=m.projectteam2_id
 * 						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id=pt2.team_id
 * 						WHERE m.id='.(int) $match_id;
 */
                        
			$db->setQuery($query);
            $result = $db->loadObject();
            if ( !$result )
		    {
			$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
		    }
			//$this->_data=$this->_db->loadObject();
			return $result;
		
	}
    
    /**
	 * Returns the team players
	 * @param in project team id
	 * @param array teamplayer_id to exclude
	 * @return array
	 */
	function getTeamPlayers($projectteam_id,$filter=false)
	{
		$query='	SELECT	tpl.id AS value,
							pl.firstname,
							pl.nickname,
							pl.lastname,
							tpl.projectteam_id,
							tpl.jerseynumber,
							pl.info,
							pos.name AS positionname,
							ppos.position_id,
							ppos.id AS pposid,
							tpl.ordering
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pl
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tpl ON tpl.person_id=pl.id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id=tpl.project_position_id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id=ppos.position_id
					WHERE tpl.projectteam_id='.  $projectteam_id.'
					AND pl.published = 1
					AND tpl.published = 1';
		if (is_array($filter) && count($filter) > 0)
		{
			$query .= " AND tpl.id NOT IN (".implode(',',$filter).")";
		}
		$query .= " ORDER BY pos.ordering, pl.lastname ASC ";
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
    
    /**
	 * Method to return substitutions made by a team during a match
	 * if no team id is passed,all substitutions should be returned (to be done!!)
	 * @access	public
	 * @return	array of substitutions
	 *
	 */
	function getSubstitutions($tid=0,$match_id)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$project_id=$mainframe->getUserState($option.'project');
		$in_out=array();
		$query='SELECT mp.*,'
		.' p1.firstname AS firstname, p1.nickname AS nickname, p1.lastname AS lastname,'
		.' p2.firstname AS out_firstname,p2.nickname AS out_nickname, p2.lastname AS out_lastname,'
		.' pos.name AS in_position, came_in'
		.' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp '
		.' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp1 ON tp1.id=mp.teamplayer_id '
		.' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p1 ON tp1.person_id=p1.id '
		.'   AND p1.published = 1'
		.' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp2 ON tp2.id=mp.in_for '
		.' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p2 ON tp2.person_id=p2.id '
		.'   AND p2.published = 1'
		.' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON mp.project_position_id=ppos.id '
		.' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id=pos.id '
		.' WHERE mp.match_id='.$match_id
		.'   AND came_in>0 '
		.'   AND tp1.projectteam_id='.$tid
		.' ORDER by (mp.in_out_time+0) ';
		$this->_db->setQuery($query);
		$in_out[$tid]=$this->_db->loadObjectList();
		return $in_out;
	}
    
    
    /**
	 * returns starters player id for the specified team
	 *
	 * @param int $team_id
	 * @param int $project_position_id
	 * @return array of player ids
	 */
	function getRoster($team_id, $project_position_id=0, $match_id)
	{
		$query='SELECT	mp.id AS table_id,mp.teamplayer_id AS value,mp.trikot_number AS trikot_number,'
		.' pl.firstname,'
		.' pl.nickname,'
		.' pl.lastname,'
		.' tpl.jerseynumber'
		.' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp '
		.' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tpl ON tpl.id=mp.teamplayer_id '
		.' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pl ON pl.id=tpl.person_id '
		.' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position as ppos ON ppos.id = tpl.project_position_id '
		.' WHERE mp.match_id='.$match_id
		.' AND tpl.projectteam_id='.$team_id
		.' AND mp.came_in='.self::MATCH_ROSTER_STARTER
		.' AND pl.published = 1 '
		.' AND tpl.published = 1 '
		.' AND tpl.published = 1 ';
		if ($project_position_id > 0)
		{
			$query .= " AND mp.project_position_id='".$project_position_id."' ";
		}
		$query .= " ORDER BY ppos.position_id, mp.ordering ASC";
		$this->_db->setQuery($query);
		$result=$this->_db->loadObjectList('value');
		return $result;
	}
    
    
    /**
	 * Method to return teams and match data
		*
		* @access	public
		* @return	array
		* @since 0.1
		*/
	function getMatchTeams($match_id)
	{
		$query='	SELECT	mc.*,'
		.' t1.name AS team1,'
		.' t2.name AS team2,'
		.' u.name AS editor '
		.' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS mc '
		.' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON pt1.id=mc.projectteam1_id '
		.' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id=pt1.team_id '
		.' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON pt2.id=mc.projectteam2_id '
		.' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id=pt2.team_id '
		.' LEFT JOIN #__users u ON u.id=mc.checked_out '
		.' WHERE mc.id='.(int) $match_id;
		$this->_db->setQuery($query);
		return	$this->_db->loadObject();
        
        /* diddi test
       	SELECT	mc.*,
		t1.name AS team1,
		t2.name AS team2,
		u.name AS editor 
		FROM jos_sportsmanagement_match AS mc 
		INNER JOIN jos_sportsmanagement_project_team AS pt1 ON pt1.id=mc.projectteam1_id 
		INNER JOIN jos_sportsmanagement_team AS t1 ON t1.id=pt1.team_id 
		INNER JOIN jos_sportsmanagement_project_team AS pt2 ON pt2.id=mc.projectteam2_id 
		INNER JOIN jos_sportsmanagement_team AS t2 ON t2.id=pt2.team_id 
		LEFT JOIN jos_users u ON u.id=mc.checked_out 
		WHERE mc.id= 8
        */
        
	}
    
    /**
	 * returns starters referees id for the specified team
	 *
	 * @param int $project_position_id
	 * @return array of referee ids
	 */
	function getRefereeRoster($project_position_id=0,$match_id=0)
	{
		$query='	SELECT	pref.id AS value,
							pr.firstname,
							pr.nickname,
							pr.lastname
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mr
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pref ON mr.project_referee_id=pref.id
					 AND pref.published = 1
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pr ON pref.person_id=pr.id
					 AND pr.published = 1
					WHERE mr.match_id='. $match_id;
		if ($project_position_id > 0)
		{
			$query .= ' AND mr.project_position_id='.$project_position_id;
		}
		$query .= ' ORDER BY mr.project_position_id, mr.ordering ASC';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList('value');
	}
    
    /**
	 * Method to return the projects referees array
	 *
	 * @access	public
	 * @return	array
	 * @since 0.1
	 */
	function getProjectReferees($already_sel=false, $project_id)
	{
		$query=' SELECT	pref.id AS value,'
		.' pl.firstname,'
		.' pl.nickname,'
		.' pl.lastname,'
		.' pl.info,'
		.' pos.name AS positionname '
		.' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pl '
		.' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pref ON pref.person_id=pl.id '
		.' AND pref.published=1 '
		.' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id=pref.project_position_id '
		.' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id=ppos.position_id '
		.' WHERE pref.project_id='.$project_id
		.' AND pl.published = 1';
		if (is_array($already_sel) && count($already_sel) > 0)
		{
			$query .= " AND pref.id NOT IN (".implode(',',$already_sel).")";
		}
		$query .= " ORDER BY pl.lastname ASC ";
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList('value');
	}
    
    
    /**
	 * Returns the team players
	 * @param in project team id
	 * @param array teamplayer_id to exclude
	 * @return array
	 */
	function getTeamStaffs($projectteam_id,$filter=false)
	{
		$query='	SELECT	ts.id AS value,
							pl.firstname,
							pl.nickname,
							pl.lastname,
							ts.projectteam_id,
							pl.info,
							pos.name AS positionname,
							ppos.position_id
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pl
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff AS ts ON ts.person_id=pl.id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id=ts.project_position_id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id=ppos.position_id
					WHERE ts.projectteam_id='.$this->_db->Quote($projectteam_id)
					.' AND pl.published = 1'
					.' AND ts.published = 1';
		if (is_array($filter) && count($filter) > 0)
		{
			$query .= " AND ts.id NOT IN (".implode(',',$filter).")";
		}
		$query .= " ORDER BY pl.lastname ASC ";
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
    
    
    /**
	 * returns players who played for the specified team
	 *
	 * @param int $team_id
	 * @param int $project_position_id
	 * @return array of players
	 */
	function getMatchPersons($projectteam_id,$project_position_id=0,$match_id, $table='player')
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $this->_season_id	= $mainframe->getUserState( "$option.season_id", '0' );
        
        if ( COM_SPORTSMANAGEMENT_USE_NEW_TABLE )
        {
            
            
        }
        else
        {
        switch($table)
        {
            case 'player':
            $id = 'teamplayer_id';
            break;
            case 'staff':
            $id = 'team_staff_id';
            break;
        }
        
        $query='	SELECT	mp.'.$id.',
        tpl.id AS value,pos.name AS positionname,
							pl.firstname,
							pl.nickname,
							pl.lastname,
							mp.project_position_id,
							tpl.projectteam_id,
							ppos.position_id,
							ppos.id AS pposid
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_'.$table.' AS mp
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_'.$table.' AS tpl ON tpl.id = mp.'.$id.'
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pl ON pl.id = tpl.person_id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position as ppos ON ppos.id = tpl.project_position_id
                    INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id
					WHERE mp.match_id='.$match_id.'
					AND pl.published = 1
					AND tpl.published = 1
					AND tpl.projectteam_id='.$projectteam_id;
                    

		if ($project_position_id > 0)
		{
			$query .= " AND mp.project_position_id='".$project_position_id."'";
		}
		$query .= " ORDER BY mp.project_position_id, mp.ordering,
					pl.lastname, pl.firstname ASC";
		$this->_db->setQuery($query);
        }
        
		return $this->_db->loadObjectList($id);
        //return $this->_db->loadObjectList();
	}
    
    /**
	 * returns players who played for the specified team
	 *
	 * @param int $team_id
	 * @param int $project_position_id
	 * @return array of players
	 */
/*
	function getMatchPlayers($projectteam_id,$project_position_id=0,$match_id)
	{
		$query='	SELECT	mp.teamplayer_id,
        tpl.id AS value,pos.name AS positionname,
							pl.firstname,
							pl.nickname,
							pl.lastname,
							mp.project_position_id,
							tpl.projectteam_id,
							ppos.position_id,
							ppos.id AS pposid
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tpl ON tpl.id = mp.teamplayer_id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pl ON pl.id = tpl.person_id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position as ppos ON ppos.id = tpl.project_position_id
                    INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id
					WHERE mp.match_id='.$match_id.'
					AND pl.published = 1
					AND tpl.published = 1
					AND tpl.projectteam_id='.$projectteam_id;
                    

		if ($project_position_id > 0)
		{
			$query .= " AND mp.project_position_id='".$project_position_id."'";
		}
		$query .= " ORDER BY mp.project_position_id, mp.ordering,
					pl.lastname, pl.firstname ASC";
		$this->_db->setQuery($query);
		//return $this->_db->loadObjectList('team_staff_id');
        return $this->_db->loadObjectList();
	}
*/
    
    /**
	 * returns players who played for the specified team
	 *
	 * @param int $team_id
	 * @param int $project_position_id
	 * @return array of players
	 */
/*
	function getMatchStaffs($projectteam_id,$project_position_id=0,$match_id)
	{
		$query='	SELECT	mp.team_staff_id,
							pl.firstname,
							pl.nickname,
							pl.lastname,
							mp.project_position_id,
							tpl.projectteam_id,
							ppos.position_id,
							ppos.id AS pposid
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff AS mp
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff AS tpl ON tpl.id=mp.team_staff_id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pl ON pl.id=tpl.person_id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position as ppos ON ppos.id = mp.project_position_id
					WHERE mp.match_id='.$match_id.'
					AND pl.published = 1
					AND tpl.published = 1
					AND tpl.projectteam_id='.$projectteam_id;
		if ($project_position_id > 0)
		{
			$query .= " AND mp.project_position_id='".$project_position_id."'";
		}
		$query .= " ORDER BY mp.project_position_id, mp.ordering,
					pl.lastname, pl.firstname ASC";
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList('team_staff_id');
	}
*/    
    
    function getInputStats($project_id)
	{
		require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'statistics'.DS.'base.php');
		//$match =& $this->getData();
		//$project_id=$match->project_id;
		$query=' SELECT stat.id,stat.name,stat.short,stat.class,stat.icon,stat.calculated,ppos.position_id AS posid'
		.' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_statistic AS stat '
		.' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_statistic AS ps ON ps.statistic_id=stat.id '
		.' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.position_id=ps.position_id '
		.' WHERE ppos.project_id='.  $project_id
		.' ORDER BY stat.ordering, ps.ordering ';
		$this->_db->setQuery($query);
		$res=$this->_db->loadObjectList();
		$stats=array();
		foreach ($res as $k => $row)
		{
			$stat=&SMStatistic::getInstance($row->class);
			$stat->bind($row);
			$stat->set('position_id',$row->posid);
			$stats[]=$stat;
		}
		return $stats;
	}
    
    function getMatchStatsInput($match_id,$projectteam1_id,$projectteam2_id)
	{
		//$match =& $this->getData();
		$query='SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic  WHERE match_id='.$match_id;
		$this->_db->setQuery($query);
		$res=$this->_db->loadObjectList();
		$stats=array(	$projectteam1_id => array(),
		$projectteam2_id => array());
		foreach ($res as $stat)
		{
			@$stats[$stat->projectteam_id][$stat->teamplayer_id][$stat->statistic_id]=$stat->value;
		}
		return $stats;
	}

	function getMatchStaffStatsInput($match_id,$projectteam1_id,$projectteam2_id)
	{
		//$match =& $this->getData();
		$query='SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff_statistic WHERE match_id='.$match_id;
		$this->_db->setQuery($query);
		$res=$this->_db->loadObjectList();
		$stats=array($projectteam1_id => array(),$projectteam2_id => array());
		foreach ((array)$res as $stat)
		{
			@$stats[$stat->projectteam_id][$stat->team_staff_id][$stat->statistic_id]=$stat->value;
		}
		return $stats;
	}
    
    
    
    
    /**
	 * Method to return the project positions array (id,name)
	 *
	 * @access	public
	 * @return	array
	 * @since 1.5
	 */
	function getProjectPositionsOptions($id=0, $person_type=1,$project_id)
	{
		//$option = JRequest::getCmd('option');
		//$mainframe = JFactory::getApplication();
		//$project_id=$mainframe->getUserState($option.'project');
		$query='	SELECT	ppos.id AS value,
							pos.name AS text,
							pos.id AS posid,
                            pos.id AS pposid
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.position_id=pos.id
					WHERE ppos.project_id='.$project_id.'
					  AND pos.persontype='.$person_type;
		if ($id > 0)
		{
			$query .= ' AND ppos.position_id='.$id;
		}
		$query .= ' ORDER BY pos.ordering';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList('value'))
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		return $result;
	}
    
    function getEventsOptions($project_id,$match_id)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $query='	SELECT DISTINCT	et.id AS value,
									et.name AS text,
									et.icon AS icon
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.project_id='.$project_id.'
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pet ON pet.position_id=ppos.position_id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et ON et.id=pet.eventtype_id
					WHERE m.id='.$match_id.'
                    AND et.published=1
					ORDER BY pet.ordering, et.ordering';
		$this->_db->setQuery($query);
		$result=$this->_db->loadObjectList();
        if ( !$result )
        {
            $mainframe->enqueueMessage(JText::_('COM_JOOMLEAGUE_ADMIN_MATCH_NO_EVENTS_POS'),'Error');
        }
		foreach ($result as $event){$event->text=JText::_($event->text);}
		return $result;
	}
    
    /**
	 * Method to update starting lineup list
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function updateReferees($post)
	{
		$mainframe =& JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $mid = $post['id'];
		$peid = array();
		$result = true;
		$positions = $post['positions'];
        
        $mainframe->enqueueMessage('sportsmanagementModelMatch updateReferees<br><pre>'.print_r($post, true).'</pre><br>','Notice');
        
		//$project_id=$post['project'];
		foreach ($positions AS $key => $pos)
		{
			if (isset($post['position'.$key])) { $peid=array_merge((array) $post['position'.$key],$peid); }
		}
		if ($peid == null)
		{ // Delete all referees assigned to this match
			$query='DELETE FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee WHERE match_id='.$post['id'];
		}
		else
		{ // Delete all referees which are not selected anymore from this match
			JArrayHelper::toInteger($peid);
			$peids=implode(',',$peid);
			$query="	DELETE FROM #__".COM_SPORTSMANAGEMENT_TABLE."_match_referee
						WHERE match_id=".$post['id']." AND project_referee_id NOT IN (".$peids.")";
		}
		$this->_db->setQuery($query);
		if (!$this->_db->query()) { $this->setError($this->_db->getErrorMsg()); $result=false; }
		foreach ($positions AS $key=>$pos)
		{
			if (isset($post['position'.$key]))
			{
				for ($x=0; $x < count($post['position'.$key]); $x++)
				{
					$project_referee_id=$post['position'.$key][$x];
					$query="	SELECT *
								FROM #__".COM_SPORTSMANAGEMENT_TABLE."_match_referee
								WHERE match_id=$mid AND project_referee_id=$project_referee_id";

					$this->_db->setQuery($query);
					if ($result=$this->_db->loadResult())
					{
						$query="	UPDATE #__".COM_SPORTSMANAGEMENT_TABLE."_match_referee
									SET project_position_id='$key',ordering= '$x'
									WHERE id='$result' AND match_id='$mid' AND project_referee_id='$project_referee_id'";
					}
					else
					{
						$query="	INSERT INTO #__".COM_SPORTSMANAGEMENT_TABLE."_match_referee
										(match_id,project_referee_id, project_position_id, ordering) VALUES
										('$mid','$project_referee_id','$key','$x')";
					}
					$this->_db->setQuery($query);
					if (!$this->_db->query())
					{
						sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
						$result=false;
					}
				}
			}
		}
		return true;
	}
    
    
    /**
	 * Method to update starting lineup list
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function updateRoster($data)
	{
		$mainframe =& JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $result = true;
		$positions = $data['positions'];
		$mid = $data['id'];
		$team = $data['team'];
        
        //$mainframe->enqueueMessage('sportsmanagementModelMatch updateRoster<br><pre>'.print_r($data, true).'</pre><br>','Notice');
        
		// we first remove the records of starter for this team and this game then add them again from updated data.
		$query='	DELETE	mp
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp ON tp.id=mp.teamplayer_id
					WHERE	came_in='.self::MATCH_ROSTER_STARTER.' AND
							mp.match_id='.$this->_db->Quote($mid).' AND
							tp.projectteam_id='.$this->_db->Quote($team);
		$this->_db->setQuery($query);
		if (!$this->_db->query())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			$result=false;
		}
		foreach ($positions AS $project_position_id => $pos)
		{
			if (isset($data['position'.$project_position_id]))
			{
				foreach ($data['position'.$project_position_id] AS $ordering => $player_id)
				{
					$record = JTable::getInstance('Matchplayer','sportsmanagementTable');
					$record->match_id			= $mid;
					$record->teamplayer_id		= $player_id;
					$record->project_position_id= $pos->pposid;
					$record->came_in			= self::MATCH_ROSTER_STARTER;
					$record->ordering			= $ordering;
					/*
                    if (!$record->check())
					{
						$this->setError($record->getError());
						$result=false;
					}
                    */
					if (!$record->store())
					{
						$this->setError($record->getError());
						$result=false;
					}
				}
			}
		}
		return $result;
	}
    
    /**
	 * Method to update starting lineup list
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function updateStaff($data)
	{
		$mainframe =& JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $result = true;
		$positions = $data['staffpositions'];
		$mid = $data['id'];
		$team = $data['team'];
        
        //$mainframe->enqueueMessage('sportsmanagementModelMatch updateStaff<br><pre>'.print_r($data, true).'</pre><br>','Notice');
        
		// we first remove the records of starter for this team and this game,then add them again from updated data.
		$query='	DELETE mp
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff AS mp
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff AS tp ON tp.id=mp.team_staff_id
					WHERE	mp.match_id='.$this->_db->Quote($mid).' AND
							tp.projectteam_id='.$this->_db->Quote($team);
		$this->_db->setQuery($query);
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			$result=false;
		}
		foreach ($positions AS $project_position_id => $pos)
		{
			if (isset($data['staffposition'.$project_position_id]))
			{
				foreach ($data['staffposition'.$project_position_id] AS $ordering => $player_id)
				{
					$record = JTable::getInstance('Matchstaff','sportsmanagementTable');
					$record->match_id = $mid;
					$record->team_staff_id = $player_id;
					$record->project_position_id = $pos->pposid;
					$record->ordering = $ordering;
                    
                    //$mainframe->enqueueMessage('sportsmanagementModelMatch updateStaff match_id<br><pre>'.print_r($mid, true).'</pre><br>','');
                    //$mainframe->enqueueMessage('sportsmanagementModelMatch updateStaff team_staff_id<br><pre>'.print_r($player_id, true).'</pre><br>','');
                    //$mainframe->enqueueMessage('sportsmanagementModelMatch updateStaff project_position_id<br><pre>'.print_r($pos->pposid, true).'</pre><br>','');
                    //$mainframe->enqueueMessage('sportsmanagementModelMatch updateStaff ordering<br><pre>'.print_r($ordering, true).'</pre><br>','');
                    
					/*
                    if (!$record->check())
					{
						$this->setError($record->getError());
						$result = false;
					}
                    */
					if (!$record->store())
					{
						$this->setError($record->getError());
						$result = false;
					}
				}
			}
		}
		//die();
		return true;
	}
    
    
    /**
	 * save the submitted substitution
	 *
	 * @param array $data
	 * @return boolean
	 */
	function savesubstitution($data)
	{
		
        if ( empty($data['project_position_id'])  )
		{
		$this->setError(JText::_('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_NO_SUBST_POSITION_ID'));
		return false;
		}
        
        if ( empty($data['in_out_time'])  )
		{
		$this->setError(JText::_('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_NO_SUBST_TIME'));
		return false;
		}
        
        if ( empty($data['in'])  )
		{
		$this->setError(JText::_('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_NO_SUBST_IN'));
		return false;
		}
        
        if ( empty($data['out'])  )
		{
		$this->setError(JText::_('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_NO_SUBST_OUT'));
		return false;
		}
        
        if ( (int)$data['in_out_time'] > (int)$data['projecttime'] )
		{
		$this->setError(JText::sprintf('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_SUBST_TIME_OVER_PROJECTTIME',$data['in_out_time'],$data['projecttime']));
		return false;
		}
        
        if (! ($data['matchid']))
		{
			$this->setError("in: " . $data['in'].
							", out: " . $data['out'].
							", matchid: " . $data['matchid'].
							", project_position_id: " . $data['project_position_id']);
			return false;
		}
		$player_in				= (int) $data['in'];
		$player_out				= (int) $data['out'];
		$match_id				= (int) $data['matchid'];
		$in_out_time			= $data['in_out_time'];
		$project_position_id 	= $data['project_position_id'];

		if ($project_position_id == 0 && $player_in>0)
		{
			// retrieve normal position of player getting in
			$query='	SELECT project_position_id '
			.' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS pt '
			.' WHERE pt.player_id='.$this->_db->Quote($player_in)
			;
			$this->_db->setQuery($query);
			$project_position_id = $this->_db->loadResult();
		}
		if($player_in>0) {
			$in_player_record = JTable::getInstance('Matchplayer','sportsmanagementTable');
			$in_player_record->match_id				= $match_id;
			$in_player_record->came_in				= self::MATCH_ROSTER_SUBSTITUTE_IN; //1 //1=came in, 2=went out
			$in_player_record->teamplayer_id		= $player_in;
			$in_player_record->in_for				= ($player_out>0) ? $player_out : 0;
			$in_player_record->in_out_time			= $in_out_time;
			$in_player_record->project_position_id	= $project_position_id;
			if (!$in_player_record->store()) {
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				return false;
			}
		}
		if($player_out>0 && $player_in==0) {
			$out_player_record = JTable::getInstance('Matchplayer','sportsmanagementTable');
			$out_player_record->match_id			= $match_id;
			$out_player_record->came_in				= self::MATCH_ROSTER_SUBSTITUTE_OUT; //2; //0=starting lineup
			$out_player_record->teamplayer_id		= $player_out;
			$out_player_record->in_out_time			= $in_out_time;
			$out_player_record->project_position_id	= $project_position_id;
			$out_player_record->out					= 1;
			if (!$out_player_record->store()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
	}
    
    /**
	 * remove specified subsitution
	 *
	 * @param int $substitution_id
	 * @return boolean
	 */
	function removeSubstitution($substitution_id)
	{
		// the subsitute isn't getting in so we delete the substitution
		$query="	DELETE
					FROM #__".COM_SPORTSMANAGEMENT_TABLE."_match_player
					WHERE id=".$this->_db->Quote($substitution_id). "
					 OR id=".$this->_db->Quote($substitution_id + 1);
		$this->_db->setQuery($query);
		if (!$this->_db->query())
		{
			$this->setError(JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_ERROR_DELETING_SUBST'));
			return false;
		}
		return true;
	}
    
    
    
    function deleteevent($event_id)
	{
		$object = JTable::getInstance('MatchEvent','sportsmanagementTable');
		//if (!$object->canDelete($event_id))
//		{
//			$this->setError('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_ERROR_DELETE');
//			return false;
//		}
		if (!$object->delete($event_id))
		{
			$this->setError('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_DELETE_FAILED');
			return false;
		}
		return true;
	}
    
    
    function deletecommentary($event_id)
	{
	   $db = JFactory::getDbo();
 
$query = $db->getQuery(true);
 
// delete all custom keys
$conditions = array(
    $db->quoteName('id') . '='.$event_id
);
 
$query->delete($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_commentary'));
$query->where($conditions);
 
$db->setQuery($query);    
if (!$db->query())
		{
			
            $this->setError('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_DELETE_FAILED_COMMENTARY');
			return false;
		}

/*
		$object = JTable::getInstance('MatchCommentary','sportsmanagementTable');
		//if (!$object->canDelete($event_id))
//		{
//			$this->setError('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_ERROR_DELETE_COMMENTARY');
//			return false;
//		}
		if (!$object->delete($event_id))
		{
			$this->setError('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_DELETE_FAILED_COMMENTARY');
			return false;
		}
*/        
		return true;
	}
    
    
    function savecomment($data)
	{
		
        // live kommentar speichern
        if ( empty($data['event_time']) )
		{
		$this->setError(JText::_('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_COMMENT_NO_TIME'));
		return false;
		}

        
        if ( empty($data['notes']) )
		{
		$this->setError(JText::_('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_COMMENT_NO_COMMENT'));
		return false;
		}
            
        if ( (int)$data['event_time'] > (int)$data['projecttime'] )
		{
		$this->setError(JText::sprintf('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_COMMENT_TIME_OVER_PROJECTTIME',$data['event_time'],$data['projecttime']));
		return false;
		}
        
        // Get a db connection.
        $db = JFactory::getDbo();
        // Create a new query object.
        $query = $db->getQuery(true);
        // Insert columns.
        $columns = array('event_time','match_id','type','notes');
        // Insert values.
        $values = array($data['event_time'],$data['match_id'],$data['type'],'\''.$data['notes'].'\'');
        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_commentary'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        // Set the query using our newly populated query object and execute it.
        $db->setQuery($query);
        if (!$db->query())
		{
			$result = false;
            $object->id = $db->getErrorMsg();
		}
        else
        {
            $object->id = $db->insertid();
        }
        
        //$object = JTable::getInstance('MatchCommentary','sportsmanagementTable');
//        $object->event_time = $data['event_time'];
//		$object->match_id = $data['match_id'];
//		$object->type = $data['type'];
//		$object->notes = $data['notes'];
		//$object->bind($data);
		//if (!$object->check())
//		{
//			$this->setError(JText::_('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_CHECK_FAILED'));
//			return false;
//		}
		
        //if (!$object->store())
//		{
//			//$this->setError($this->_db->getErrorMsg());
//			return false;
//		}
//        else
//        {
//            $object->id = $this->_db->insertid();
//        }
        
		return $object->id;
	}
    
    function saveevent($data)
	{

        if ( empty($data['event_time']) )
		{
		$this->setError(JText::_('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_EVENT_NO_TIME'));
		return false;
		}
        
        if ( empty($data['event_sum']) )
		{
		$this->setError(JText::_('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_EVENT_NO_EVENT_SUM'));
		return false;
		}
        
        if ( (int)$data['event_time'] > (int)$data['projecttime'] )
		{
		$this->setError(JText::sprintf('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_EVENT_TIME_OVER_PROJECTTIME',$data['event_time'],$data['projecttime']));
		return false;
		}
   
        $object = JTable::getInstance('MatchEvent','sportsmanagementTable');
		$object->bind($data);
		//if (!$object->check())
//		{
//			$this->setError(JText::_('COM_JOOMLEAGUE_ADMIN_MATCH_MODEL_CHECK_FAILED'));
//			return false;
//		}
		if (!$object->store())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
        else
        {
            $object->id = $this->_db->insertid();
        }
		return $object->id;
	}
    
    
    /**
	 * get match commentary
	 *
	 * @return array
	 */
	function getMatchCommentary($match_id)
	{
		$query=' SELECT	me.*'
        .' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_commentary AS me '
		.' WHERE me.match_id='.$match_id
		.' ORDER BY me.event_time ASC ';
		$this->_db->setQuery($query);
		return ($this->_db->loadObjectList());
	}
    
    
    function sendEmailtoPlayers()
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $mailer = JFactory::getMailer();
        $user	= JFactory::getUser();
        // get settings from com_issuetracker parameters
        $params = JComponentHelper::getParams($option);
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        $mdl = JModel::getInstance("Project", "sportsmanagementModel");
	    $project = $mdl->getProject($this->project_id);
        
        //$mainframe->enqueueMessage('sportsmanagementModelMatch sendEmailtoPlayers project<br><pre>'.print_r($project, true).'</pre><br>','Notice');
        //$mainframe->enqueueMessage('sportsmanagementModelMatch sendEmailtoPlayers user<br><pre>'.print_r($user, true).'</pre><br>','Notice');
        
        $mdl = JModel::getInstance("TeamPlayers", "sportsmanagementModel");
	    $teamplayer = $mdl->getProjectTeamplayers($project->fav_team);
        
        //$mainframe->enqueueMessage('sportsmanagementModelMatch sendEmailtoPlayers teamplayer<br><pre>'.print_r($teamplayer, true).'</pre><br>','Notice');
        
        //$mainframe->enqueueMessage('sportsmanagementModelMatch sendEmailtoPlayers _match_time_new<br><pre>'.print_r($this->_match_time_new, true).'</pre><br>','Notice');
        //$mainframe->enqueueMessage('sportsmanagementModelMatch sendEmailtoPlayers _match_date_new<br><pre>'.print_r($this->_match_date_new, true).'</pre><br>','Notice');
        //$mainframe->enqueueMessage('sportsmanagementModelMatch sendEmailtoPlayers _match_time_old<br><pre>'.print_r($this->_match_time_old, true).'</pre><br>','Notice');
        //$mainframe->enqueueMessage('sportsmanagementModelMatch sendEmailtoPlayers _match_date_old<br><pre>'.print_r($this->_match_date_old, true).'</pre><br>','Notice');
        
        foreach ( $teamplayer as $player )
        {
        if( $player->email )
        {
        //add the sender Information.
$sender = array( 
    $user->email,
    $user->name
);
$mailer->setSender($sender); 
//add the recipient. $recipient = $user_email;
$mailer->addRecipient($player->email);
//add the subject
$mailer->setSubject($params->get('match_mail_header', ''));
//add body
//$fcontent = JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_ADMIN_MAIL',$this->_match_date_old,$this->_match_time_old,$this->_match_date_new,$this->_match_time_new,$user->name);
$fcontent = $params->get('match_mail_text', '');

$fcontent    = str_replace('[FROMDATE]', $this->_match_date_old, $fcontent);
$fcontent    = str_replace('[FROMTIME]', $this->_match_time_old, $fcontent);
$fcontent    = str_replace('[TODATE]', $this->_match_date_new, $fcontent);
$fcontent    = str_replace('[TOTIME]', $this->_match_time_new, $fcontent);
$fcontent    = str_replace('[MAILFROM]', $user->name, $fcontent);

$mailer->setBody($fcontent);
$mailer->isHTML(true); 
$send =& $mailer->Send();
if ( $send !== true ) 
{
    //echo 'Error sending email: ' . $send->message;
    $mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_ADMIN_MAIL_SEND_ERROR',$send->message),'Error');
} 
else 
{
    $mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_ADMIN_MAIL_SEND_SUCCESS',$player->firstname,$player->lastname ),'Notice');
}
    
        }    
            
            
        }
                
        
        
        
    }
         
    
}
?>
