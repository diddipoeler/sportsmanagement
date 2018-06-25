<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      helper.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_matches
 */

defined('_JEXEC') or die('Restricted access');

/**
 * modMatchesHelper
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class modMatchesSportsmanagementHelper {

	/**
	 * modMatchesHelper::__construct()
	 * 
	 * @param mixed $params
	 * @param mixed $id
	 * @param integer $match_id
	 * @return
	 */
	public function __construct(& $params, $id, $match_id = 0) {
		$this->module_id = $id;
		$this->params = $params;
        $this->app = JFactory::getApplication();
		$itemid = $this->params->get('Itemid');
		$this->itemid = (!empty ($itemid)) ? '&amp;Itemid=' . $itemid : '';
		$this->id = $match_id;
		$this->usedteams = array (
			0 => array ()
		);
		$this->addusedprojects();
		$this->iconpath = ($params->get('use_icons') != '-1') ? _JSMMATCHLISTMODURL . 'assets/images/' .
		$params->get('use_icons') . '/' : false;
	}

	/**
	 * modMatchesHelper::arrStrToClean()
	 * 
	 * @param mixed $string
	 * @param string $sep
	 * @return
	 */
	public function arrStrToClean(& $string, $sep = ',') {
		if (empty ($string)) {
			return $string;
		}
		$ids = explode($sep, $string);
		JArrayHelper :: toInteger($ids);
		$string = implode($sep, $ids);
		return $string;
	}

	/**
	 * modMatchesHelper::getFromDB()
	 * 
	 * @param mixed $query
	 * @param string $key
	 * @param string $type
	 * @return
	 */
	public function getFromDB($query, $key = "", $type = "objlist") 
    {
		$db = sportsmanagementHelper::getDBConnection(); 
		$db->setQuery($query);
		switch ($type) {
			case "obj" :
				$result = $db->loadObject($key);
				break;
			case 'arr' :
				$result = $db->loadResultArray($key);
				break;
			case 'assc' :
				$result = $db->loadAssocList($key);
				break;
			default :
				$result = $db->loadObjectList($key);
				break;
		}
		if ($this->params->get('debug', 0) == 1) {
			echo $query . '<br />';
		}
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $result;
	}

	/**
	 * modMatchesHelper::jl_utf8_convert()
	 * 
	 * @param mixed $text
	 * @param string $fromenc
	 * @param string $toenc
	 * @return
	 */
	public function jl_utf8_convert($text, $fromenc = 'iso-8859-1', $toenc = 'UTF-8') {
		if (!defined('_MODJLML_SPORTSMANAGEMENT_JC'))
		return $text;
		if (strtolower($fromenc) == strtolower($toenc) || $this->params->get('convert', 1) == 0)
		return $text;

		elseif (function_exists('iconv')) {
			return iconv($fromenc, $toenc, $text);
		}
		elseif (strtolower($fromenc) == 'iso-8859-1' && strtolower($toenc) == 'utf-8') {
			return utf8_encode($text);
		}
		elseif (strtolower($fromenc) == 'utf-8' && strtolower($toenc) == 'iso-8859-1') {
			return utf8_decode($text);
		} else
		return $text;
	}

	/**
	 * modMatchesHelper::sortObject()
	 * 
	 * @param mixed $array
	 * @param mixed $comparefunction
	 * @param string $property
	 * @return
	 */
	public function sortObject($array, $comparefunction, $property = '') {
		$zcount = count($array);
		for ($i = 1; $i < $zcount; $i++) {
			for ($a = $zcount -1; $a >= $i; $a--) {
				if ($this-> $comparefunction ($array[$a -1]-> $property, $array[$a]-> $property) > 0) {
					$tempzal = $array[$a -1];
					$array[$a -1] = $array[$a];
					$array[$a] = $tempzal;
				}
			}
		}
		return $array;
	}
	
	/**
	 * modMatchesHelper::sortArray()
	 * 
	 * @param mixed $array
	 * @param mixed $comparefunction
	 * @param string $property
	 * @return
	 */
	public function sortArray($array, $comparefunction, $property = '') {
		$zcount = count($array);
		for ($i = 1; $i < $zcount; $i++) {
			for ($a = $zcount -1; $a >= $i; $a--) {
				if ($this-> $comparefunction ($array[$a -1][$property], $array[$a][$property]) > 0) {
					$tempzal = $array[$a -1];
					$array[$a -1] = $array[$a];
					$array[$a] = $tempzal;
				}
			}
		}
		return $array;
	}

	/**
	 * modMatchesHelper::asc()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	private function asc($a, $b) {
		if ($a < $b)
		return -1;
		if ($a == $b)
		return 0;
		return 1;
	}

	/**
	 * modMatchesHelper::desc()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	private function desc($a, $b) {
		if ($a > $b)
		return -1;
		if ($a == $b)
		return 0;
		return 1;
	}
	
	/**
	 * modMatchesHelper::array_trim()
	 * 
	 * @param mixed $a
	 * @return
	 */
	public function array_trim($a) {
		$j = 0;
		$b = array ();
		for ($i = 0; $i < count($a); $i++) {
			if (trim($a[$i]) != "") {
				$b[$j++] = $a[$i];
			} else
			$b[$j++] = '-';
		}
		return $b;
	}
	
	/**
	 * modMatchesHelper::addusedprojects()
	 * 
	 * @return
	 */
	private function addusedprojects() {
		$usedp = $this->params->get('p');
		if (is_array($usedp)) 
        {
			foreach ($usedp AS $p) 
            {
                if (is_array($this->params->get('teams')))
                {
				$this->usedteams[(int)$p] = array_map('intval',$this->params->get('teams'));
                }
			}
		}
		elseif (!empty ($usedp)) 
        {
            if (is_array($this->params->get('teams')))
            {
			$this->usedteams[(int)$usedp] = array_map('intval',$this->params->get('teams'));
            }
		}
        
        if ( JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info_frontend') )
        {
        //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($this->params->get('teams'),true).'</pre>'),'Notice');    
        echo __METHOD__.' '.__LINE__.' projekte<br><pre>'.print_r($this->params->get('p'),true).'</pre>'; 
        echo __METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($this->params->get('teams'),true).'</pre>';      
        }    
	}
	
	/**
	 * modMatchesHelper::usedteamscheck()
	 * 
	 * @param mixed $team_id
	 * @param mixed $project_id
	 * @return
	 */
	public function usedteamscheck($team_id, $project_id) {
		return ((isset ($this->usedteams[0]) AND is_array($this->usedteams[0]) AND in_array($team_id, $this->usedteams[0])) OR (isset ($this->usedteams[$project_id]) AND is_array($this->usedteams[$project_id]) AND in_array($team_id, $this->usedteams[$project_id]))) ? 1 : 0;
	}
	
	/**
	 * modMatchesHelper::addteamicon()
	 * 
	 * @param mixed $which
	 * @return
	 */
	public function addteamicon($which) {
		$path = ($this->iconpath) ? $this->iconpath . 'teamlinks/' : false;
		if ($path) {
			return JHtml :: _('image', $path . $which . '.png', $this->params->get($which . '_text'), 'title="' . $this->params->get($which . '_text') . '"');
		} else {
			return $this->params->get($which . '_text') . '<br />';
		}
	}
	
	/**
	 * modMatchesHelper::getTeamDetails()
	 * 
	 * @param mixed $team
	 * @param mixed $nr
	 * @return
	 */
	public function getTeamDetails(& $team, $nr) {
		if (!is_object($team))
		return false;
		$usedname = $this->params->get('team_names');
		$class = 'jlmlTeamname';
		$isusedteam = $this->usedteamscheck($team->id, $team->project_id);
		if ($isusedteam) {
			$class .= ' jlmlusedteam';
		}
		if (($this->params->get('link_teams', 0) + $isusedteam) >= 2)
		$class .= " jlmhaslinks";
		$teamdetails['logo'] = $this->getpix($team); //returns false if logos are not to be shown
		$teamdetails['name'] = '<span class="' . $class . '" id="jlmlTeamname' . $team->id . 'mod' . $this->module_id . 'nr' . $nr . '">';
		switch ($usedname) {
			case "short_name" :
				$teamdetails['name'] .= '<acronym title="' .
				$this->jl_utf8_convert($team->name, 'iso-8859-1', 'utf-8') . '">' .
				$this->jl_utf8_convert($team->short_name, 'iso-8859-1', 'utf-8') .
				'</acronym>';
				break;
			default :
				$teamdetails['name'] .= ($team-> $usedname != '') ? $this->jl_utf8_convert($team-> $usedname, 'iso-8859-1', 'utf-8') : $this->jl_utf8_convert($team->name, 'iso-8859-1', 'utf-8');
				break;
		}
		$teamdetails['name'] .= '</span>';
		return $teamdetails;
	}
	
	/**
	 * modMatchesHelper::getpix()
	 * 
	 * @param mixed $team
	 * @return
	 */
	public function getpix($team) 
    {
		$pt = $this->params->get('picture_type');
		if ($this->params->get('show_picture') == 0)
		return false;
		$appendimage = ' align="middle" ';
		$countrycode = $team->country;
		if($pt=="country" && strlen($countrycode)==3) 
        {
			$pic['src'] = JSMCountries::getIso3Flag($countrycode);
			$pic['alt'] = JSMCountries::getCountryName($countrycode);
		} 
        else 
        {
         
			$defaultlogos = $this->getDefaultLogos();
			//$matchpart_pic = (!empty ($team->$pt) AND curl_init($team->$pt)) ? $team->$pt : $defaultlogos[$pt];
            $matchpart_pic = (!empty ($team->$pt) AND sportsmanagementHelper::existPicture($team->$pt)) ? $team->$pt : $defaultlogos[$pt];
          

            
			if ( JFile::exists(JPATH_ROOT.$matchpart_pic) )
            {
				$size = getimagesize($matchpart_pic);
				$pic_width = $size[0];
				$pic_height = $size[1];
				$whichparam = ($pic_width > $pic_height) ? ' width' : ' height';
				if ($this->params->get('xsize') > 0)
				$appendimage .= $whichparam . '="' . $this->params->get('xsize') . '"';
				elseif ($this->params->get('ysize') > 0) $appendimage .= $whichparam . '="' . $this->params->get('ysize') . '"';
			}
		else
		{
		$appendimage .= 'width="' . $this->params->get('xsize') . '"';	
		}
			$pic['src'] = (trim($matchpart_pic) != "" && curl_init(trim($matchpart_pic))) ? $matchpart_pic : $defaultlogos[$pt];
			$pic['alt'] = $this->jl_utf8_convert($team->name, 'iso-8859-1', 'utf-8');
		}
		$pic['append'] = $appendimage;
		return $pic;
	}
	
	/**
	 * modMatchesHelper::formatResults()
	 * 
	 * @param mixed $row
	 * @param mixed $match
	 * @return
	 */
	public function formatResults(& $row, & $match) {
		$live = ($match->live == 'z') ? 0 : 1;
		$mrt = array (
		0 => array (
		0 => '',
		1 => ''
		),
		1 => array (
		0 => JText :: _('AET'),
		1 => JText :: _('IET')
		),
		2 => array (
		0 => JText :: _('ONP'),
		1 => JText :: _('INP')
		)
		);
		$partresults = '';
		if ( $this->params->get('part_result') == 1 AND trim(str_replace(';', '', $match->team1_result_split)) != '' ) 
        {
			$homepartresults = $this->array_trim(explode(";", $match->team1_result_split));
			$guestpartresults = $this->array_trim(explode(";", $match->team2_result_split));
			$cntmp = ($match->game_parts + $this->params->get('part_result_count'));
			for ($x = 0; $x < $cntmp; $x++) {
				$highlight_parts = false;
				if ($x == ($match->game_parts + $this->params->get('part_result_count')))
				break;
				if (isset ($homepartresults[$x]) && isset ($guestpartresults[$x])) 
                {
					if (is_null($match->team1_result) AND ($x +1) <= $cntmp AND (!isset ($homepartresults[$x +1]) OR $homepartresults[$x +1] == '-') AND $homepartresults[$x] != '-' AND $match->match_result_type == 0) {
						$highlight_parts = true;
					}
					if ($x == 0)
					$partresults .= '(';
					if ($x != 0)
					$partresults .= $this->params->get('part_results_separator');
					if ($live != 'z' AND $highlight_parts)
					$partresults .= '<span class="jlml_livescore">';
					$partresults .= $homepartresults[$x] . ':' . $guestpartresults[$x];
					if ($live != 'z' AND $highlight_parts)
					$partresults .= '</span>';

				} 
                else
				break;
			}

			if (!is_null($match->team1_result_ot)) 
            {
				array_push($homepartresults, $match->team1_result_ot);
				array_push($guestpartresults, $match->team2_result_ot);
				$partresults .= ' - ';
				if (is_null($match->team1_result))
				$partresults .= '<span class="jlml_livescore">';
				$partresults .= JText :: _('IET') . ' ' . $match->team1_result_ot . ':' . $match->team2_result_ot;
				if (is_null($match->team1_result))
				$partresults .= '</span>';
			}
			if (trim($partresults) != '')
			$partresults .= ')';
		}
		if ($live != 'z' AND trim(str_replace(';', '', $match->team1_result_split)) != '' AND is_null($match->team1_result)) {
			if (isset ($homepartresults) AND count($homepartresults) > 0) {
				$row['homescore'] = array_sum($homepartresults);
			}
			if (isset ($guestpartresults) AND count($guestpartresults) > 0) {
				$row['awayscore'] = array_sum($guestpartresults);
			}
			$row['homescore'] = '<span class="jlml_livescore">' . $row['homescore'] . '</span>';
			$row['awayscore'] = '<span class="jlml_livescore">' . $row['awayscore'] . '</span>';
		}
		$row['partresults'] = $partresults;

		$row['result'] = $row['homescore'] . $this->params->get('team_separator') . $row['awayscore'];

/**
 * verlängerung
 */
        if ( $this->params->get('show_text_overtime') && $match->team1_result_ot ) 
        {
        $row['resultovertime'] = JText :: _('IET') . ' ' .$match->team1_result_ot . $this->params->get('team_separator') . $match->team2_result_ot;
        }
/**
 * elfmeter/penalty
 */
        if ( $this->params->get('show_text_penalty') && $match->team1_result_so ) 
        {
        $row['resultpenalty'] = JText :: _('INP') . ' ' .$match->team1_result_so . $this->params->get('team_separator') . $match->team2_result_so;
        }
        
        if ($live == 0)
		$row['result'] .= $mrt[$match->match_result_type][$live];
	}
	
	/**
	 * modMatchesHelper::createMatchInfo()
	 * 
	 * @param mixed $row
	 * @param mixed $match
	 * @return
	 */
	public function createMatchInfo(& $row, $match) {
		$row['notice'] = ($match->match_result_detail != '' AND $this->params->get('show_match_notice') == 1) ? $match->match_result_detail : '';
		if ($this->params->get('show_referee', 1) == 1 AND $match->refname != '') {
			$row['referee'] = '<span style="float:right;">';
			$row['referee'] .= ($this->iconpath) ? JHtml :: _('image', $this->iconpath . 'referee.png', JText::_('MOD_SPORTSMANAGEMENT_MATCHES_REFEREE'), array (
				'title' => JText::_('MOD_SPORTSMANAGEMENT_MATCHES_REFEREE'),
				'height' => '16',
				'width' => '16'
				)) : JText::_('MOD_SPORTSMANAGEMENT_MATCHES_REFEREE').': ';
				$row['referee'] .= $this->jl_utf8_convert($match->refname, 'iso-8859-1', 'utf-8') . '</span>';
		} else {
			$row['referee'] = '';
		}
		if ($this->params->get('show_spectators', 1) == 1 AND $match->crowd > 0) {
			$row['spectators'] = '<span style="float:left;">';
			$row['spectators'] .= ($this->iconpath) ? JHtml :: _('image', $this->iconpath . 'spectators.png', JText::_('MOD_SPORTSMANAGEMENT_MATCHES_SPECTATORS'), array (
				'title' => JText::_('MOD_SPORTSMANAGEMENT_MATCHES_SPECTATORS'),
				'height' => '16',
				'width' => '16'
				)) : JText::_('MOD_SPORTSMANAGEMENT_MATCHES_SPECTATORS').': ';
				$row['spectators'] .= number_format($match->crowd, 0, ',', '.') . '</span>';
				;
		} else {
			$row['spectators'] = '';
		}
	}
	
	/**
	 * modMatchesHelper::formatMatches()
	 * 
	 * @param mixed $matches
	 * @return
	 */
	public function formatMatches(& $matches) 
    {
        // Reference global application object
        $app = JFactory::getApplication();
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' iconpath<br><pre>'.print_r($this->iconpath,true).'</pre>'),'Notice');
        
        
		if ($this->params->get('lastsortorder') == 'desc') {
			$matches = $this->sortObject($matches, 'desc', 'alreadyplayed');
		}
		if ($this->params->get('upcoming_first', 1) == 1) {
			$matches = $this->sortObject($matches, 'asc', 'upcoming');
		}

		$matches = $this->sortObject($matches, 'asc', 'actplaying');
		$matches = $this->sortObject($matches, 'asc', 'live');
		$teams = $this->getTeamsFromMatches($matches);
		$rows = array ();
		$useicons = $this->iconpath;
        $cnt = $app->input->post->get('nr', 0);
//		$cnt = JRequest :: getVar('nr', 0, 'default', 'POST');
		$hteam = false;
		$ateam = false;
		foreach ((array) $matches AS $key => $match) {
			//echo 'NOW(): '.JHtml::_('date', $match->currenttime, $this->params->get('dateformat').' '.$this->params->get('timeformat'),0).'<br />';
			if ($match->projectteam1_id) {
				$hteam = $teams[$match->projectteam1_id];
			}
			if ($match->projectteam2_id) {
				$ateam = $teams[$match->projectteam2_id];
			}
			//$matches[$key]->live = false;
			$rows[$match->match_id]['type'] = 'undefined';
			if ($this->params->get('show_status_notice') == 1) {
				if ($match->live != 'z') {
					$rows[$match->match_id]['type'] = 'live';
				}
				else {
					$matches[$key]->live = false;
					if ($match->actplaying != 'z') {
						$rows[$match->match_id]['type'] = 'actplaying';
					}
					elseif ($match->alreadyplayed != 0) {
						$rows[$match->match_id]['type'] = 'alreadyplayed';
					}
					elseif ($match->upcoming != 'z') {
						$rows[$match->match_id]['type'] = 'upcoming';
					}
				}
			}
			$rows[$match->match_id]['date'] = JHtml::_('date', $match->match_date, $this->params->get('dateformat'), null);
			if ($useicons) {
				$rows[$match->match_id]['date'] = JHtml::_('image', $this->iconpath . 'date.png', JText::_('MOD_SPORTSMANAGEMENT_MATCHES_DATE'), array (
						'title' => JText::_('MOD_SPORTSMANAGEMENT_MATCHES_DATE'),
						'height' => '16',
						'width' => '16'
				)) .
				' ' . $rows[$match->match_id]['date'];
			}
			$rows[$match->match_id]['time'] = JHtml :: _('date', $match->match_date, $this->params->get('timeformat'), null);

			if ($useicons) {
				$rows[$match->match_id]['time'] = JHtml :: _('image', $this->iconpath . 'time.png', JText::_('MOD_SPORTSMANAGEMENT_MATCHES_TIME'), array (
						'title' => JText::_('MOD_SPORTSMANAGEMENT_MATCHES_TIME'),
						'height' => '16',
						'width' => '16'
				)) .
				' ' . $rows[$match->match_id]['time'];
			}

			if (isset ($match->meeting)) {
				$rows[$match->match_id]['meeting'] = JHtml :: _('date', $match->meetingtime, $this->params->get('timeformat'), null);
				if ($useicons) {
					$rows[$match->match_id]['meeting'] = JHtml :: _('image', $this->iconpath . 'time_go.png', JText::_('MOD_SPORTSMANAGEMENT_MATCHES_MEETING'), array (
							'title' => JText::_('MOD_SPORTSMANAGEMENT_MATCHES_MEETING'),
							'height' => '16',
							'width' => '16'
					)) .
					' ' . $rows[$match->match_id]['meeting'];
				}
			}
			$rows[$match->match_id]['project_id'] = $match->project_id;
			$rows[$match->match_id]['totaltime'] = $match->totaltime;
			$rows[$match->match_id]['round_id'] = $match->round_id;
			$rows[$match->match_id]['hometeam'] = $this->getTeamDetails($hteam, $cnt);
			$rows[$match->match_id]['homescore'] = (!is_null($match->team1_result)) ? $match->team1_result : '-';
			$rows[$match->match_id]['homeover'] = $this->buildTeamLinks($hteam, $cnt);
			$rows[$match->match_id]['awayteam'] = $this->getTeamDetails($ateam, $cnt);
			$rows[$match->match_id]['awayscore'] = (!is_null($match->team2_result)) ? $match->team2_result : '-';
			$rows[$match->match_id]['awayover'] = $this->buildTeamLinks($ateam, $cnt);
			$this->createMatchInfo($rows[$match->match_id], $match);
			$this->formatHeading($rows[$match->match_id], $match);
			$this->formatResults($rows[$match->match_id], $match);
			$this->createMatchLinks($rows[$match->match_id], $match);
			$this->createLocation($rows[$match->match_id], $match, $hteam);
			$this->createAjaxMenu($match, $cnt);
			$rows[$match->match_id]['ajax'] = $match->ajax;
			$cnt++;
		}
		return $rows;
	}
	
	/**
	 * modMatchesHelper::getTimeLimit()
	 * 
	 * @return
	 */
	public function getTimeLimit() {
		$livematchestime = "IF((p.allow_add_time > 0), ((p.game_regular_time+(p.game_parts * p.halftime)) + p.add_time), (p.game_regular_time+(p.game_parts * p.halftime)))";
		$timeforfirstmatch = "DATE_SUB(" . $this->getDateString() . ", INTERVAL $livematchestime MINUTE) > NOW()";
		//$timeforfirstmatch = $this->getDateString() . " > NOW()";
		if ($this->params->get('show_played', 0) == 1 AND ($this->params->get('result_add_time', 0)) > 0) {
			$timeforfirstmatch = $this->getDateString() . " > DATE_SUB(NOW(), INTERVAL " . intval($this->params->get('result_add_time', 0)) . " " . $this->params->get('result_add_unit') . ")";
		}
		$timeforlastmatch = ($this->params->get('period_int', 0) > 0) ? $this->getDateString() . " < DATE_ADD(NOW(), INTERVAL " . intval($this->params->get('period_int')) . " " . $this->params->get('period_string') . ")" : '';
		$wheretime = "(" . $timeforfirstmatch;
		if (!empty ($timeforlastmatch))
		$wheretime .= " AND " . $timeforlastmatch;
		$wheretime .= ")";
		return $wheretime;
	}
	
	/**
	 * modMatchesHelper::createAjaxMenu()
	 * 
	 * @param mixed $row
	 * @param mixed $cnt
	 * @return
	 */
	public function createAjaxMenu(& $row, $cnt) 
    {
        $app = JFactory::getApplication();  
		if ($this->params->get('next_last', 0) == 0) {
			$row->ajax = false;
			return false;
		}
		if ( $this->params->get('nextlast_from_same_project')== 0) {
			$this->next_last2($row);
		}
		else {
		$this->next_last($row);
		}
        $origin = $app->input->post->get('origin', $row->match_id);
		//$origin = JRequest :: getVar('origin', $row->match_id, 'default', 'POST');
		$jsfunc = "jlml_loadMatch('%s', '%s', '" . $this->module_id . "', '" . $cnt . "', '%s')";
		$options = array (
			'height' => '16',
			'width' => '16',
			'onclick' => 'alert(\'coming soon...\')',
			'style' => 'cursor:pointer;'
			);
			// start ajaxifying
			$showhome = (($this->params->get('next_last') + $this->usedteamscheck($row->team1_id, $row->project_id)) >= 2);
			$showaway = (($this->params->get('next_last') + $this->usedteamscheck($row->team2_id, $row->project_id)) >= 2);
			
//echo __METHOD__.' '.__LINE__.' showhome <pre>'.print_r($showhome,true).'</pre>';			
//echo __METHOD__.' '.__LINE__.' showaway <pre>'.print_r($showaway,true).'</pre>';			
		
		$temp = '<div class="jlmlext_ajaxmenu" style="text-align:center;width:100%;display:block;clear:both;margin-top:10px;">';
			if ($showhome AND ($row->lasthome OR $row->nexthome)) {
				$temp .= '<span style="float:left">';
				if ($row->lasthome) {
					$tmp = $options;
					$tmp['title'] = JText::_('MOD_SPORTSMANAGEMENT_MATCHES_PREVIOUS_TEAM_MATCH');
					$tmp['onclick'] = sprintf($jsfunc, $row->team1_id, $row->lasthome, $origin);
					$alt = $tmp['title'];
					if ($this->iconpath AND $this->params->get('icons_for_ajax') == 1)
					$temp .= JHtml :: _('image', $this->iconpath . 'page_prev.png', $alt, $tmp);
					else
					$temp .= '<input type="button" class="' . $this->params->get('reset_class') . '" value="' . $this->params->get('last_text') . '" style="cursor:pointer;" onclick="' . $tmp['onclick'] . '" />';
				}
				if ($row->nexthome) {
					$tmp = $options;
					$tmp['title'] = JText::_('MOD_SPORTSMANAGEMENT_MATCHES_NEXT_TEAM_MATCH');
					$tmp['onclick'] = sprintf($jsfunc, $row->team1_id, $row->nexthome, $origin);
					$alt = $tmp['title'];
					if ($this->iconpath AND $this->params->get('icons_for_ajax') == 1)
					$temp .= JHtml :: _('image', $this->iconpath . 'page_next.png', $alt, $tmp);
					else
					$temp .= '<input type="button" class="' . $this->params->get('reset_class') . '" value="' . $this->params->get('next_text') . '" style="cursor:pointer;" onclick="' . $tmp['onclick'] . '" />';
				}
				$temp .= '</span>';
			}
			if ($showaway AND ($row->lastaway OR $row->nextaway)) {
				$temp .= '<span style="float:right">';
				if ($row->lastaway) {
					$tmp = $options;
					$tmp['title'] = JText::_('MOD_SPORTSMANAGEMENT_MATCHES_PREVIOUS_TEAM_MATCH');
					$tmp['onclick'] = sprintf($jsfunc, $row->team2_id, $row->lastaway, $origin);
					$alt = $tmp['title'];
					if ($this->iconpath AND $this->params->get('icons_for_ajax') == 1)
					$temp .= JHtml :: _('image', $this->iconpath . 'page_prev.png', $alt, $tmp);
					else
					$temp .= '<input type="button" class="' . $this->params->get('reset_class') . '" value="' . $this->params->get('last_text') . '" style="cursor:pointer;" onclick="' . $tmp['onclick'] . '" />';
				}
				if ($row->nextaway) {
					$tmp = $options;
					$tmp['title'] = JText::_('MOD_SPORTSMANAGEMENT_MATCHES_NEXT_TEAM_MATCH');
					$tmp['onclick'] = sprintf($jsfunc, $row->team2_id, $row->nextaway, $origin);
					$alt = $tmp['title'];
					if ($this->iconpath AND $this->params->get('icons_for_ajax') == 1)
					$temp .= JHtml :: _('image', $this->iconpath . 'page_next.png', $alt, $tmp);
					else
					$temp .= '<input type="button" class="' . $this->params->get('reset_class') . '" value="' . $this->params->get('next_text') . '" style="cursor:pointer;" onclick="' . $tmp['onclick'] . '" />';
				}
				$temp .= '</span>';
			}
			if ($this->params->get('reset_start_match') == 1 AND $origin != $row->id) {
				$temp .= '<span style="float:none;">';
				$tmp = $options;
				$tmp['title'] = JText::_('MOD_SPORTSMANAGEMENT_MATCHES_RESET_TEAM_MATCH');
				$tmp['onclick'] = sprintf($jsfunc, '0', $origin, $origin);
				$alt = $tmp['title'];
				if ($this->iconpath AND $this->params->get('icons_for_ajax') == 1)
				$temp .= JHtml :: _('image', $this->iconpath . 'page_reset.png', $alt, $tmp);
				else
				$temp .= '<input type="button" class="' . $this->params->get('reset_class') . '" value="' . $this->params->get('reset_text') . '" style="cursor:pointer;" onclick="' . $tmp['onclick'] . '" />';
				$temp .= '</span>';
			}
			$temp .= '</div>';
			$row->ajax = $temp;
	}
	
	/**
	 * modMatchesHelper::arrayToUri()
	 * 
	 * @param mixed $arr
	 * @return
	 */
	public function arrayToUri(& $arr) {
		if (!is_array($arr))
		return false;
		$str = '';
		foreach ($arr AS $key => $val) {
			$str .= '&amp;' . $key . '=' . $val;
		}
		return $str;
	}
}
