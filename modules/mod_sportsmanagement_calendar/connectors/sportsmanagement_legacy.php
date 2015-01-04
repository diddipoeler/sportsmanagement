<?php

class SportsmanagementConnector extends JSMCalendar
{
  
  var $xparams;
  var $prefix;
  function getEntries ( &$caldates, &$params, &$matches ) {
    $m = array();
    $b = array();
    $this->xparams = $params;
    $this->prefix = $params->prefix;
    if($this->xparams->get('joomleague_use_favteams', 0) == 1){
      $this->favteams = JoomleagueConnector::getFavs();
    }
    if ($this->xparams->get('jlmatches', 0) == 1) {
      $rows = JoomleagueConnector::getMatches($caldates);
      $m = JoomleagueConnector::formatMatches($rows, $matches);
    }
    if ($this->xparams->get('jlbirthdays', 1) == 1) {
      $birthdays = JoomleagueConnector::getBirthdays (  $caldates, $this->params, $this->matches  );
      $b = JoomleagueConnector::formatBirthdays($birthdays, $matches, $caldates);
    }
    
    
    return array_merge($m, $b);
  }
  function getFavs() {
      $query = "SELECT fav_team FROM #__joomleague WHERE fav_team != ''";
      $projectid    = $this->xparams->get('project_ids') ;
      if ($projectid != '') {
        $projectids = explode( ',', $projectid );
        JArrayHelper::toInteger( $projectids );
        $query .= " AND id IN(".implode(', ', $projectids).")";
      }
      $query = ($this->prefix != '') ? str_replace('#__', $this->prefix, $query) : $query;
      $database = JFactory::getDbo();
      $database->setQuery($query);
      $fav=$database->loadColumn();
      return implode(',', $fav);
  }
    
  function getBirthdays ( $caldates, $ordering='ASC' ) {
    $teamCondition = '';
    $clubCondition = '';
    $favCondition = '';
    $limitingcondition = '';
    $limitingconditions = array();
    
    $database = JFactory::getDbo();

    $customteam = JRequest::getVar('jlcteam',0,'default','POST');
    $teamid   = $this->xparams->get('team_ids') ;
    $teamid = ($customteam > 0) ? $customteam : $teamid;
    if ($teamid) {
      $teamids = explode( ',', $teamid );
      JArrayHelper::toInteger( $teamids );
      $teamCondition = '(pt.team_id IN ('.implode(', ', $teamids).'))';
      $limitingconditions[] = $teamCondition;
    }
    if($this->xparams->get('joomleague_use_favteams', 0) == 1 && $customteam == 0) {
      $favCondition .= "(pt.team_id IN (".$this->favteams."))";
      $limitingconditions[] = $favCondition;
    }
    if (count($limitingconditions) > 0) {
      $limitingcondition .=' AND (';
      $limitingcondition .= implode(' OR ', $limitingconditions);
      $limitingcondition .=')';
    }


      $query="SELECT p.id, p.firstname, p.lastname, p.default_picture, p.nation,
                     DATE_FORMAT(p.birthday, '%m-%d') AS month_day,
                     YEAR( CURRENT_DATE( ) ) as year,
                     DATE_FORMAT('".$caldates['start']."', '%Y') - YEAR( p.birthday ) AS age,
                     DATE_FORMAT(p.birthday,'%Y-%m-%d') AS date_of_birth,
                     'showplayer' AS func_to_call,
                     pt.project_id,
                     'pid' AS id_to_append,
                     team.name AS teamname, team.short_name, pro.name AS pname
              FROM #__joomleague_players AS p
              LEFT JOIN #__joomleague_playertool AS pt ON (p.id = pt.player_id)
              LEFT JOIN #__joomleague_teams AS team ON (pt.team_id = team.id)";
      $query .= " LEFT JOIN #__joomleague AS pro ON (pro.id = pt.project_id)";
      //$query .= " LEFT JOIN #__joomleague AS pro ON (pro.id = pt.project_id OR pro.id = crew.project_id OR pro.season_id = pt.season_id OR pro.season_id = crew.season_id)";
      $query .= " WHERE p.birthday != '0000-00-00' and DATE_FORMAT(p.birthday, '%m') = DATE_FORMAT('".$caldates['start']."', '%m') ";

      $projectid    = $this->xparams->get('project_ids') ;
      if ($projectid != '') {
        $projectids = explode( ',', $projectid );
        JArrayHelper::toInteger( $projectids );
        $query .= " AND (pt.project_id IN (".implode(', ', $projectids).")";

        $query .= ") ";
      }
      $query .= $limitingcondition;
      $query .= " GROUP BY p.id ";
      $query .= " ORDER BY p.birthday ASC";
      $query = ($this->prefix != '') ? str_replace('#__', $this->prefix, $query) : $query;
      $database->setQuery($query);
      if (!$players=$database->loadObjectList()) $players = array();

      return $players;
  }  
  function formatBirthdays( $rows, &$matches, $dates ) {
    $newrows = array();
    $year = substr($dates['start'], 0, 4);
    foreach ($rows AS $key => $row) {
      $newrows[$key]['type'] = 'jlb';
      $newrows[$key]['homepic'] = '';
      $newrows[$key]['awaypic'] = '';
      $newrows[$key]['date'] = $year.'-'.$row->month_day;
      $newrows[$key]['age'] = '('.$row->age.')';
      $newrows[$key]['headingtitle'] = $this->xparams->get('birthday_text', 'Birthday');
      $newrows[$key]['name'] = '';
      $newrows[$key]['team'] = '';
      global $mainframe;
      if ($this->xparams->get('jlbirthdaypix', 0) == 1 AND $row->default_picture != '' AND file_exists($mainframe->getCfg('absolute_path').DS.str_replace('/', DS, $row->default_picture))) {
        $linkit = 1;
        $newrows[$key]['image'] = '<img src="'.(JUri::root(true).'/'.parent::jl_utf8_convert ($row->default_picture, 'iso-8859-1', 'utf-8'))
                                .'" alt="" style="height:40px; vertical-align:middle;margin:0 5px;" />';
      }
      JoomleagueConnector::build_url($row);
      $newrows[$key]['link'] = JRoute::_($row->link);
      $newrows[$key]['name'] = parent::jl_utf8_convert ($row->firstname, 'iso-8859-1', 'utf-8').' ';
      $newrows[$key]['name'] .= parent::jl_utf8_convert ($row->lastname, 'iso-8859-1', 'utf-8');
      if (isset($row->teamname)) {
        $newrows[$key]['team'] = '<acronym title="'.parent::jl_utf8_convert ($row->teamname.' '.$row->pname, 'iso-8859-1', 'utf-8').'">'
                               . parent::jl_utf8_convert ($row->short_name, 'iso-8859-1', 'utf-8').'</acronym>';
      }
      //$newrows[$key]['name'] .= ' ('..')';
      $newrows[$key]['matchcode'] = 0;
      $newrows[$key]['project_id'] = 0;
      $matches[] = $newrows[$key];
    }
    return $newrows;
  } 
  function formatMatches( $rows, &$matches ) {
    $newrows = array();
    $teamnames = $this->xparams->get('team_names', 'short_name');
    $teams = JoomleagueConnector::getTeamsFromMatches( $rows );
    $teams[0]->name = $teams[0]->$teamnames = $teams[0]->logo_small = $teams[0]->logo_middle = $teams[0]->logo_big =  '';
    foreach ($rows AS $key => $row) {
      $newrows[$key]['type'] = 'jlm';
      $newrows[$key]['homepic'] = (isset($teams[$row->matchpart1])) ? JoomleagueConnector::buildImage($teams[$row->matchpart1]) : '';
      $newrows[$key]['awaypic'] = (isset($teams[$row->matchpart2])) ? JoomleagueConnector::buildImage($teams[$row->matchpart2]) : '';
      $newrows[$key]['date'] = JoomleagueHelper::getMatchStartTimestamp($row);
      $newrows[$key]['result'] = (!is_null($row->matchpart1_result)) ? $row->matchpart1_result . ':' . $row->matchpart2_result : '-:-';
      $newrows[$key]['headingtitle'] = parent::jl_utf8_convert ($row->name.'-'.$row->roundname, 'iso-8859-1', 'utf-8');
      $newrows[$key]['homename'] = (isset($teams[$row->matchpart1])) ? JoomleagueConnector::formatTeamName($teams[$row->matchpart1]) : 'n/a';;
      $newrows[$key]['awayname'] = (isset($teams[$row->matchpart2])) ? JoomleagueConnector::formatTeamName($teams[$row->matchpart2]) : 'n/a';;
      $newrows[$key]['matchcode'] = $row->matchcode;
      $newrows[$key]['project_id'] = $row->project_id;
      $matches[] = $newrows[$key];
      if (isset($teams[$row->matchpart1])) {
        parent::addTeam($row->matchpart1, parent::jl_utf8_convert ($teams[$row->matchpart1]->name, 'iso-8859-1', 'utf-8'), $newrows[$key]['homepic']);
      }
      if (isset($teams[$row->matchpart2])) {
        parent::addTeam($row->matchpart2, parent::jl_utf8_convert ($teams[$row->matchpart2]->name, 'iso-8859-1', 'utf-8'),$newrows[$key]['awaypic']);
      }
    }
    return $newrows;
  }
  function formatTeamName($team) {
    $teamnames = $this->xparams->get('team_names', 'short_name');
    switch ($teamnames) {
      case '-':
        return '';
      break;
      case 'short_name':
        $teamname = '<acronym title="'.parent::jl_utf8_convert ($team->name, 'iso-8859-1', 'utf-8').'">'
        .parent::jl_utf8_convert ($team->short_name, 'iso-8859-1', 'utf-8')
        .'</acronym>';
      break;
      default:
        if (!isset($team->$teamnames) OR (is_null($team->$teamnames) OR trim($team->$teamnames)=='')) {
          $teamname = parent::jl_utf8_convert ($team->name, 'iso-8859-1', 'utf-8');
        }
        else {
          $teamname = parent::jl_utf8_convert ($team->$teamnames, 'iso-8859-1', 'utf-8');
        }
      break;
    }
    return $teamname;
  }
  function buildImage($team) {
    global $mainframe;
    $image = $this->xparams->get('team_logos', 'logo_small');
    if ($image == '-') { return ''; }
    $logo = '';
    if ($team->$image != '' && file_exists($mainframe->getCfg('absolute_path').'/'.$team->$image)){
      $h = $this->xparams->get('logo_height', 20);
      $logo = '<img src="'.JUri::root(true).'/'.$team->$image.'" alt="'
           .parent::jl_utf8_convert ($team->short_name, 'iso-8859-1', 'utf-8').'" title="'
           .parent::jl_utf8_convert ($team->name, 'iso-8859-1', 'utf-8').'"';
      if ($h > 0) {
        $logo .= ' style="height:'.$h.'px;"';
      }
      $logo .= ' />';
    }
    return $logo;
  }
  function getMatches($caldates, $ordering='ASC'){
    $database = JFactory::getDbo();
    
    $teamCondition = '';
    $clubCondition = '';
    $favCondition = '';
    $limitingcondition = '';
    $limitingconditions = array();
    
    $customteam = JRequest::getVar('jlcteam',0,'default','POST');
    $teamid   = $this->xparams->get('team_ids') ;
    $teamid = ($customteam > 0) ? $customteam : $teamid;
    if ($teamid) {
      $teamids = explode( ',', $teamid );
      JArrayHelper::toInteger( $teamids );
      $teamCondition = '(matchpart1 IN ('.implode(', ', $teamids).') or matchpart2 IN ('.implode(', ', $teamids).'))';
      $limitingconditions[] = $teamCondition;
    }
    $clubid   = $this->xparams->get('club_ids') ;
    if ($clubid && $customteam == 0) {
      $clubids = explode( ',', $clubid );
      JArrayHelper::toInteger( $clubids );
      $clubCondition = '(teams.club_id IN ('.implode(', ', $clubids).') AND (matchpart1 = teams.id or matchpart2 = teams.id))';
      $limitingconditions[] = $clubCondition;
    }
    if($this->xparams->get('joomleague_use_favteams', 0) == 1 && $customteam == 0) {
      $favCondition .= "(m.matchpart1 IN (".$this->favteams.") OR m.matchpart2 IN (".$this->favteams."))";
      $limitingconditions[] = $favCondition;
    }
    if (count($limitingconditions) > 0) {
      $limitingcondition .=' AND (';
      $limitingcondition .= implode(' OR ', $limitingconditions);
      $limitingcondition .=')';
    }
    
    $limit = (isset($caldates['limitstart'])&&isset($caldates['limitend'])) ? ' LIMIT '.$caldates['limitstart'].', '.$caldates['limitend'] :'';
    $query = "SELECT  m.*,p.*,
                      DATE_FORMAT(match_date, '%Y-%m-%d') AS caldate,
                      r.matchcode, r.name AS roundname, r.round_date_first, r.round_date_last
              FROM #__joomleague_matches m
              LEFT JOIN #__joomleague_rounds r ON m.round_id = r.id
              LEFT JOIN #__joomleague p ON p.id = m.project_id  
              LEFT JOIN #__joomleague_teams teams ON (teams.id = m.matchpart1 OR teams.id = m.matchpart2) "; 

    $where = " WHERE m.published = 1 
               AND p.published = 1 ";
    if (isset($caldates['start'])) $where .= " AND m.match_date >= '".$caldates['start']."'";
    if (isset($caldates['end'])) $where .= " AND m.match_date <= '".$caldates['end']."'";
    if (isset($caldates['matchcode'])) $where .= " AND r.matchcode = '".$caldates['matchcode']."'";
    $projectid    = $this->xparams->get('project_ids') ;
    if ($projectid) {
      $projectids = explode( ',', $projectid );
      JArrayHelper::toInteger( $projectids );
      $where .= " AND m.project_id IN (".implode(', ', $projectids).")";
    }
    if(isset($caldates['resultsonly']) && $caldates['resultsonly']== 1) $where .= " AND m.matchpart1_result IS NOT NULL";

    $where .= $limitingcondition;

    $where .= " GROUP BY m.match_id";
    $where .=" ORDER BY m.match_date ".$ordering;
    
    $query = ($this->prefix != '') ? str_replace('#__', $this->prefix, $query) : $query;
    $database->setQuery($query.$where.$limit);

    $result = $database->loadObjectList();
	if ($result)
	{
		foreach ($result as $match)
		{
			JoomleagueHelper::convertMatchDateToTimezone($match);
		}
	}
    return $result;
  }
  function getTeamsFromMatches( &$games ){
    $database = JFactory::getDbo();
    //if ($my->id == 62) explain_array($games);
    //$project_id = $games[0]->project_id;
       if ( !count ($games) ) return Array();
    foreach ( $games as $m ) {
      $teamsId[] = $m->matchpart1;
      $teamsId[] = $m->matchpart2;
    }
    $listTeamId = implode( ",", array_unique($teamsId) );

    $query = "SELECT tl.id AS teamtoolid, tl.division_id, tl.standard_playground, tl.admin, tl.start_points,
                     tl.info, tl.team_id, tl.checked_out, tl.checked_out_time, tl.picture, tl.project_id,
                     t.id, t.name, t.short_name, t.middle_name, t.description, t.club_id,
                     pg1.id AS tt_pg_id, pg1.name AS tt_pg_name, pg1.short_name AS tt_pg_short_name,
                     pg2.id AS club_pg_id, pg2.name AS club_pg_name, pg2.short_name AS club_pg_short_name,
                     u.username, u.email,
                     c.logo_small, c.logo_middle, c.logo_big, c.country,
                     CONCAT('images/joomleague/flags/', LOWER(ctry.countries_iso_code_2), '.png') AS logo_country,
                     d.name AS division_name, d.shortname AS division_shortname,
                     p.name AS project_name
                FROM #__joomleague_teams t 
                LEFT JOIN #__joomleague_team_joomleague tl on tl.team_id = t.id ";
           $query .= "LEFT JOIN #__users u on tl.admin = u.id
           LEFT JOIN #__joomleague_clubs c on t.club_id = c.id
           LEFT JOIN #__joomleague_countries ctry on ctry.countries_id = c.country
           LEFT JOIN #__joomleague_divisions d on d.id = tl.division_id
           LEFT JOIN #__joomleague p on p.id = tl.project_id
           LEFT JOIN #__joomleague_playgrounds pg1 ON pg1.id = tl.standard_playground
           LEFT JOIN #__joomleague_playgrounds pg2 ON pg2.id = c.standard_playground
               WHERE t.id IN (".$listTeamId.") AND tl.project_id = p.id";
    $query = ($this->prefix != '') ? str_replace('#__', $this->prefix, $query) : $query;
    $database->setQuery($query);
    if ( !$result = $database->loadObjectList('team_id') ) $result = Array();

    
    return $result;
  }
  function build_url( &$row ) {
    $row->link = ($this->xparams->get('linkbirthday', 0) == 1) ? 'index.php?option=com_joomleague&amp;p='.$row->project_id.'&amp;func='.$row->func_to_call.'&amp;pid='.$row->id : '';
  }
  function getGlobalTeams () {
    $teamnames = $this->xparams->get('team_names', 'short_name');
    $database = JFactory::getDbo();
    $query = "SELECT t.".$teamnames." AS name, t.id AS value
    FROM #__joomleague_teams t, #__joomleague p
    WHERE t.id IN(p.fav_team)";
    $database->setQuery($query);
    $result = $database->loadObjectList();
  }

    
} 