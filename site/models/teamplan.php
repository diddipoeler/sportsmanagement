<?php 

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );
//require_once('results.php');

class sportsmanagementModelTeamPlan extends JModel
{
	var $projectid=0;
	var $teamid=0;
	var $team=null;
	var $club=null;
	var $divisionid=0;
	var $joomleague=null;
	var $mode=0;

	function __construct()
	{
		

		$this->projectid=JRequest::getInt('p',0);
		$this->teamid=JRequest::getInt('tid',0);
		$this->divisionid=JRequest::getInt('division',0);
		$this->mode=JRequest::getInt("mode",0);
        parent::__construct();
	}

	function getDivisionID()
	{
		return $this->divisionid;
	}

	function getMode()
	{
		return $this->mode;
	}

	function getDivision()
	{
		$division=null;
		if ($this->divisionid > 0)
		{
			$query='	SELECT	*,
								CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(\':\',id,alias) ELSE id END AS slug
						FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS d
						WHERE d.id='.$this->_db->Quote($this->divisionid);
			$this->_db->setQuery($query,0,1);
			$division=$this->_db->loadObject();
		}
		return $division;
	}

	function getProjectTeamId()
	{
		$query='	SELECT	id
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team
					WHERE	team_id='.$this->teamid.' AND
							project_id='.$this->projectid;
		$this->_db->setQuery($query,0,1);
		if (! $result=$this->_db->loadResult())
		{
			return 0;
		}
		return $result;
	}

	function getMatchesPerRound($config,$rounds)
	{
		$rm=array();

		$ordering='DESC';
		if ($config['plan_order'])
		{
			$ordering=$config['plan_order'];
		}
		foreach ($rounds as $round)
		{
			$matches=$this->_getResultsRows($round->roundcode,$this->teamid,$ordering,0,1,$config['show_referee']);
			$rm[$round->roundcode]=$matches;
		}
		return $rm;
	}

	function getMatches($config)
	{
		$ordering='DESC';
		if ($config['plan_order'])
		{
			$ordering=$config['plan_order'];
		}
		return $this->_getResultsPlan($this->teamid,$ordering,0,1,$config['show_referee']);
	}

	function getMatchesRefering($config)
	{
		$ordering='DESC';
		if ($config['plan_order'])
		{
			$ordering=$config['plan_order'];
		}
		return $this->_getResultsPlan(0,$ordering,$this->teamid,1,$config['show_referee']);
	}

	function _getResultsPlan($team=0,$ordering='ASC',$referee=0,$getplayground=0,$getreferee=0)
	{
		$mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
        $matches=array();
		$joomleague=$mdlProject->getProject();

		if ($this->divisionid > 0)
		{
			$query='	SELECT id
					  	FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_division
					  	WHERE parent_id='.(int)$this->divisionid;
			$this->_db->setquery($query);
			$div_for_teams=$this->_db->loadResultArray();
			$div_for_teams[]=$this->getDivision()->id;
		}

		$query_SELECT=' SELECT m.*,DATE_FORMAT(m.time_present,"%H:%i") time_present,t1.id AS team1,t2.id AS team2,r.roundcode,r.id roundid,r.project_id,r.name ';
		$query_FROM  =' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_round r ON m.round_id=r.id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON m.projectteam1_id=pt1.id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id=pt1.team_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON m.projectteam2_id=pt2.id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id=pt2.team_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id=r.project_id '
		;
		$query_WHERE=' WHERE m.published=1 '
		;

//win matches
		if (($this->mode)== 1)
		{
		$query_WHERE .= ' AND ((t1.id= ' .$this->teamid 
						. ' AND m.team1_result > m.team2_result)'
						.' OR (t2.id= ' .$this->teamid 
						. ' AND m.team1_result < m.team2_result))';						
		}
//draw matches
		if (($this->mode)== 2)
		{
		$query_WHERE .= ' AND m.team1_result = m.team2_result';
		}
//lost matches
		if (($this->mode)== 3)
		{
		$query_WHERE .= ' AND ((t1.id= ' .$this->teamid 
						. ' AND m.team1_result < m.team2_result)'
						.' OR (t2.id= ' .$this->teamid 
						. ' AND m.team1_result > m.team2_result))';	
		}
	
		if ($this->divisionid > 0)
		{
			$query_WHERE .= ' AND (pt1.division_id IN ('.(implode(',',$div_for_teams)).') OR pt2.division_id IN ('.(implode(',',$div_for_teams)).'))';
		}

		if ($referee != 0)
		{
			$query_SELECT .= ',p.name AS project_name ';
			$query_FROM .= ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mref ON mref.match_id=m.id ';
			$query_WHERE .= ' AND mref.project_referee_id='.$referee
			. ' AND p.season_id='.$joomleague->season_id;
		}
		else
		{
			$query_WHERE .= " AND r.project_id='".$this->projectid."'";
		}

		if ($this->teamid != 0)
		{
			$query_WHERE .= " AND (t1.id=".$this->teamid." OR t2.id=".$this->teamid.")";
		}

		$query_END=" GROUP BY m.id";
		$query_END .=" ORDER BY r.roundcode ".$ordering.",m.match_date,m.match_number";

		if ($getplayground)
		{
			$query_SELECT .= ",playground.name AS playground_name,playground.short_name AS playground_short_name";
			$query_FROM .= " LEFT JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_playground AS playground ON playground.id=m.playground_id";
		}

		$query=$query_SELECT.$query_FROM.$query_WHERE.$query_END;
		$this->_db->setQuery($query);

		$matches=$this->_db->loadObjectList();

		if ($getreferee)
		{
			$this->_getRefereesByMatch($matches,$joomleague);
		}

		return $matches;
	}

	function _getResultsRows($roundcode=0,$teamId=0,$ordering='ASC',$unpublished=0,$getplayground=0,$getreferee=0)
	{
		$mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
        $matches=array();

		$joomleague=$mdlProject->getProject();

		$query_SELECT=' SELECT matches.* ';
		$query_FROM  =' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS matches '
		. '	INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON matches.round_id=r.id'
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON matches.projectteam1_id=pt1.id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id=pt1.team_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON matches.projectteam2_id=pt2.id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id=pt2.team_id '
		;
		$query_WHERE=' WHERE r.project_id='.(int)$this->projectid
		. ' AND r.roundcode='.$roundcode;
		if ($teamId)
		{
			$query_WHERE .= " AND (t1.id=".$teamId." OR t2.id=".$teamId.")";
		}
		$query_END=" GROUP BY matches.id
					   ORDER BY matches.match_date ".$ordering.",matches.match_number";

		if ($this->divisionid > 0)
		{
			$query_FROM .= "
							 LEFT JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_division AS d1 ON pt1.division_id=d1.id
							 LEFT JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_division AS d2 ON pt2.division_id=d2.id";
			$query_WHERE .= " AND (d1.id=".$this->divisionid." OR d1.parent_id=".$this->divisionid."
							  OR d2.id=".$this->divisionid." OR d2.parent_id=".$this->divisionid.")";
		}

		if ($unpublished != 1)
		{
			$query_WHERE .=" AND matches.published=1";
		}

		if ($getplayground)
		{
			$query_SELECT .= ",playground.name AS playground_name,playground.short_name AS playground_short_name";
			$query_FROM .= " LEFT JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_playground AS playground ON playground.id=matches.playground_id";
		}

		$this->_db->setQuery($query_SELECT.$query_FROM.$query_WHERE.$query_END);
		if (!$matches=$this->_db->loadObjectList())
		{
			echo $this->_db->getErrorMsg();
		}

		if ($getreferee)
		{
			$this->_getRefereesByMatch($matches,$joomleague);
		}

		return $matches;
	}

	function _getRefereesByMatch($matches,$joomleague)
	{
		for ($index=0; $index < count($matches); $index++) {
			$referees=array();
			if ($joomleague->teams_as_referees)
			{
				$query="SELECT ref.name AS referee_name
							  FROM #__".COM_SPORTSMANAGEMENT_TABLE."_team ref
							  LEFT JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_match_referee link ON link.project_referee_id=ref.id
								  WHERE link.match_id=".$matches[$index]->id."
								  ORDER BY link.ordering";
			}
			else
			{
				$query="SELECT	ref.firstname AS referee_firstname,
											ref.lastname AS referee_lastname,
											ref.id as referee_id,
											ppos.position_id,
											pos.name AS referee_position_name
								FROM #__".COM_SPORTSMANAGEMENT_TABLE."_person ref
								LEFT JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project_referee AS pref ON pref.person_id=ref.id
								LEFT JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_match_referee link ON link.project_referee_id=pref.id
								INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project_position AS ppos ON ppos.id=link.project_position_id
								INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_position AS pos ON pos.id=ppos.position_id	
								WHERE link.match_id=".$matches[$index]->id."
								  AND ref.published = 1
								  ORDER BY link.ordering";
			}

			$this->_db->setQuery($query);
			if (! $referees=$this->_db->loadObjectList())
			{
				echo $this->_db->getErrorMsg();
			}
			$matches[$index]->referees=$referees;
		}
		return $matches;
	}

	function getEventTypes($match_id)
	{
		$query=' SELECT	et.id as etid,me.event_type_id as id,et.* '
		. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype as et '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event as me ON et.id=me.event_type_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match as m ON m.id=me.match_id '
		. ' WHERE me.match_id='.$match_id;

		$query .= " ORDER BY et.ordering";

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList('etid');
	}

}
?>