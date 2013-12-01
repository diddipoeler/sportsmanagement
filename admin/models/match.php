<?php
/**
 * @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');



/**
 * Sportsmanagement Component Match Model
 *
 * @author	diddipoeler
 * @package	Sportsmanagement
 * @since	0.1
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
					$this->setError($this->_db->getErrorMsg());
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
		$mainframe =& JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        // Get the input
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        $post = JRequest::get('post');
        
        if ( $show_debug_info )
        {
        $mainframe->enqueueMessage('sportsmanagementModelMatch saveshort pks<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
        $mainframe->enqueueMessage('sportsmanagementModelMatch saveshort post<br><pre>'.print_r($post, true).'</pre><br>','Notice');
        }
        
        $result=true;
		for ($x=0; $x < count($pks); $x++)
		{
			$tblMatch = & $this->getTable();
			$tblMatch->id = $pks[$x];
			$tblMatch->match_number	= $post['match_number'.$pks[$x]];
            
            $tblMatch->match_date = $post['match_date'.$pks[$x]];
            $tblMatch->match_time = $post['match_time'.$pks[$x]];
            $tblMatch->crowd = $post['crowd'.$pks[$x]];
            $tblMatch->round_id	= $post['round_id'.$pks[$x]];
            $tblMatch->projectteam1_id = $post['projectteam1_id'.$pks[$x]];
            $tblMatch->projectteam2_id = $post['projectteam2_id'.$pks[$x]];
            $tblMatch->team1_result	= $post['team1_result'.$pks[$x]];
            $tblMatch->team2_result	= $post['team2_result'.$pks[$x]];

			if(!$tblMatch->store()) {
				$this->setError($this->_db->getErrorMsg());
				$result=false;
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
		
			$query=' SELECT	m.*,
							CASE m.time_present
							when NULL then NULL
							else DATE_FORMAT(m.time_present, "%H:%i")
							END AS time_present,
							t1.name AS hometeam, t1.id AS t1id,
							t2.name as awayteam, t2.id AS t2id,
							pt1.project_id,
							m.extended as matchextended
						FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON pt1.id=m.projectteam1_id
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id=pt1.team_id
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON pt2.id=m.projectteam2_id
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id=pt2.team_id
						WHERE m.id='.(int) $match_id;
			$this->_db->setQuery($query);
			//$this->_data=$this->_db->loadObject();
			return $this->_db->loadObject();
		
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
							pos.id AS posid
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
			$this->setError($this->_db->getErrorMsg());
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
    
    
    
         
    
}
?>
