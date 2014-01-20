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

/**
 * Search plugin
 * 1) onContentSearch($keyword,$match,$ordering,$areas)
 * 2) onContentSearchAreas()
 * These events are triggered in 'SearchModelSearch' class in file 'search.php' at location
 * 'Joomla_base\components\com_search\models'.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// Import library dependencies
jimport( 'joomla.plugin.plugin' );
require_once(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'helpers'.DS.'countries.php' );

class plgSearchsearch_sportsmanagement extends JPlugin
{



	public function plgSearchsearch_sportsmanagement(&$subject, $params)
	{
		parent::__construct($subject, $params);
		// load language file for frontend
		JPlugin::loadLanguage( 'plg_search_sportsmanagement', JPATH_ADMINISTRATOR );
	}

	function onContentSearchAreas()
	{
		static $areas = array(
				'search_sportsmanagement' => 'search_sportsmanagement'
		);
		return $areas;
	}

	function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		$db 	= JFactory::getDBO();
		$user	= JFactory::getUser();

		// load plugin params info
		$plugin			= JPluginHelper::getPlugin('search', 'search_sportsmanagement');
		$search_clubs 		= $this->params->def( 'search_clubs', 		1 );
		$search_teams 		= $this->params->def( 'search_teams', 		1 );
		$search_players 	= $this->params->def( 'search_players', 	1 );
		$search_playgrounds	= $this->params->def( 'search_playgrounds', 1 );
		$search_staffs	 	= $this->params->def( 'search_staffs',	 	1 );
		$search_referees	= $this->params->def( 'search_referees',	1 );
        $search_projects	= $this->params->def( 'search_projects',	1 );
		$text = trim( $text );
		if ($text == '') {
			return array();
		}

		$wheres = array();

		switch ($phrase)
		{

			case 'any':
			default:
				$words = explode( ' ', $text );
				$wheres = array();
				$wheresteam = array();
				$wheresperson = array();
				$wheresplayground = array();
                $wheresproject = array();

				if ( $search_clubs )
				{
					foreach ($words as $word)
					{
						$word		= $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
						$wheres2 	= array();
						$wheres2[] 	= 'c.name LIKE '.$word;
						$wheres2[] 	= 'c.alias LIKE '.$word;
						$wheres2[] 	= 'c.location LIKE '.$word;
                        $wheres2[] 	= 'c.unique_id LIKE '.$word;

						$wheres[] 	= implode( ' OR ', $wheres2 );
					}
				}


				if ( $search_teams )
				{
					foreach ($words as $word)
					{
						$word		= $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
						$wheres2 	= array();
						$wheres2[] 	= 't.name LIKE '.$word;
						$wheresteam[] 	= implode( ' OR ', $wheres2 );
					}
				}

				if ( $search_players || $search_referees || $search_staffs)
				{
					foreach ($words as $word)
					{
						$word		= $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
						$wheres2 	= array();
						$wheres2[] 	= 'pe.firstname LIKE '.$word;
						$wheres2[] 	= 'pe.lastname LIKE '.$word;
						$wheres2[] 	= 'pe.nickname LIKE '.$word;
						$wheresperson[] 	= implode( ' OR ', $wheres2 );
					}
				}

				if ( $search_playgrounds )
				{
					foreach ($words as $word)
					{
						$word		= $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
						$wheres2 	= array();
						$wheres2[] 	= 'pl.name LIKE '.$word;
						$wheres2[] 	= 'pl.city LIKE '.$word;


						$wheresplayground[] 	= implode( ' OR ', $wheres2 );
					}
				}
                
                if ( $search_projects )
				{
					foreach ($words as $word)
					{
						$word		= $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
						$wheres2 	= array();
						$wheres2[] 	= 'pro.name LIKE '.$word;
						$wheres2[] 	= 'pro.staffel_id LIKE '.$word;


						$wheresproject[] 	= implode( ' OR ', $wheres2 );
					}
				}


				$where = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
				$whereteam = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheresteam ) . ')';
				$wheresperson = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheresperson ) . ')';
				$wheresplayground = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheresplayground ) . ')';
                $wheresproject = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheresproject ) . ')';
				break;


		}


		$rows = array();

		if ( $search_clubs )
		{
			$query = "SELECT 'Club' as section, c.name AS title,"
			." c.founded AS created,"
			." c.country,"
			." c.logo_big AS picture,"
			." CONCAT( 'Address: ',c.address,' ',c.zipcode,' ',c.location,' Phone: ',c.phone,' Fax: ',c.fax,' E-Mail: ',c.email,' Vereinsnummer: ',c.unique_id ) AS text,"
			." pt.project_id AS project,"
			." CONCAT( 'index.php?option=com_sportsmanagement"
			."&view=clubinfo&cid=', CONCAT_WS(':',c.id,c.alias) ,'&p=', CONCAT_WS(':',pro.id,pro.alias) ) AS href,"
			." '2' AS browsernav"
			." FROM #__sportsmanagement_club AS c"
			." LEFT JOIN #__sportsmanagement_team AS t"
			." ON c.id = t.club_id"
			." LEFT JOIN #__sportsmanagement_project_team AS pt"
			." ON pt.team_id = t.id"
            ." LEFT JOIN #__sportsmanagement_project AS pro"
			." ON pt.project_id = pro.id"
			." WHERE ( ".$where." ) "
			." GROUP BY c.name ORDER BY c.name";

			$db->setQuery( $query );
			$list = $db->loadObjectList();
			$rows[] = $list;
		}

		if ( $search_teams )
		{
			$query = "SELECT 'Team' as section, t.name AS title,"
			." t.checked_out_time AS created,"
			//." t.notes AS text,"
            ." CONCAT( 'Teamart:',t.info , ' Notes:', t.notes ) AS text,"
			." pt.project_id AS project, "
			." pt.picture AS picture, "
			." CONCAT( 'index.php?option=com_sportsmanagement"
			."&view=teaminfo&tid=', CONCAT_WS(':',t.id,t.alias) ,'&p=', CONCAT_WS(':',pro.id,pro.alias) ) AS href,"
			." '2' AS browsernav"
			." FROM #__sportsmanagement_team AS t"
			." LEFT JOIN #__sportsmanagement_project_team AS pt"
			." ON pt.team_id = t.id"
            ." LEFT JOIN #__sportsmanagement_project AS pro"
			." ON pt.project_id = pro.id"
			." WHERE ( ".$whereteam." ) "
			." GROUP BY t.name ORDER BY t.name";

			$db->setQuery( $query );
			$list = $db->loadObjectList();
			$rows[] = $list;
		}


		if ( $search_players )
		{

			$query = "SELECT 'Person' as section, REPLACE(CONCAT(pe.firstname, ' \'', pe.nickname, '\' ' , pe.lastname ),'\'\'','') AS title,"
			." pe.birthday AS created,"
			." pe.country,"
			." pe.picture AS picture, "
			." CONCAT( 'Birthday:',pe.birthday , ' Notes:', pe.notes ) AS text,"
			." pt.project_id AS project,"
			." CONCAT( 'index.php?option=com_sportsmanagement"
			."&view=player&pid=', CONCAT_WS(':',pe.id,pe.alias) ,'&p=', CONCAT_WS(':',pro.id,pro.alias) , '&tid=', CONCAT_WS(':',t.id,t.alias) ) AS href,"
			." '2' AS browsernav"
			." FROM #__sportsmanagement_person AS pe"
			." LEFT JOIN #__sportsmanagement_team_player AS tp"
			." ON tp.person_id = pe.id"
			." LEFT JOIN #__sportsmanagement_project_team AS pt"
			." ON pt.id = tp.projectteam_id"
            ." LEFT JOIN #__sportsmanagement_team AS t"
			." ON t.id = pt.team_id"
            ." LEFT JOIN #__sportsmanagement_project AS pro"
			." ON pt.project_id = pro.id"
			." WHERE ( ".$wheresperson." ) "
			." AND pe.published = '1' "
			." GROUP BY pe.lastname, pe.firstname, pe.nickname ORDER BY pe.lastname,pe.firstname,pe.nickname";

			$db->setQuery( $query );
			$list = $db->loadObjectList();
			$rows[] = $list;

		}

		if ( $search_staffs )
		{

			$query = "SELECT 'Staff' as section, REPLACE(CONCAT(pe.firstname, ' \'', pe.nickname, '\' ' , pe.lastname ),'\'\'','') AS title,"
			." pe.birthday AS created,"
			." pe.country,"
			." pe.picture AS picture, "
			." CONCAT( 'Birthday:',pe.birthday , ' Notes:', pe.notes ) AS text,"
			." pt.project_id AS project,"
			." CONCAT( 'index.php?option=com_sportsmanagement"
			."&view=staff&pid=', CONCAT_WS(':',pe.id,pe.alias) ,'&p=', CONCAT_WS(':',pro.id,pro.alias), '&tid=', CONCAT_WS(':',t.id,t.alias) ) AS href,"
			." '2' AS browsernav"
			." FROM #__sportsmanagement_person AS pe"
			." LEFT JOIN #__sportsmanagement_team_staff AS ts"
			." ON ts.person_id = pe.id"
			." LEFT JOIN #__sportsmanagement_project_team AS pt"
			." ON pt.id = tp.projectteam_id"
            ." LEFT JOIN #__sportsmanagement_team AS t"
			." ON t.id = pt.team_id"
            ." LEFT JOIN #__sportsmanagement_project AS pro"
			." ON pt.project_id = pro.id"
			." WHERE ( ".$wheresperson." ) "
			." AND pe.published = '1' "
			." GROUP BY pe.lastname, pe.firstname, pe.nickname ORDER BY pe.lastname,pe.firstname,pe.nickname";

			$db->setQuery( $query );
			$list = $db->loadObjectList();
			$rows[] = $list;

		}

		if ( $search_referees )
		{

			$query = "SELECT 'Referee' as section, REPLACE(CONCAT(pe.firstname, ' \'', pe.nickname, '\' ' , pe.lastname ),'\'\'','') AS title,"
			." pe.birthday AS created,"
			." pe.country,"
			." pe.picture AS picture, "
			." CONCAT( 'Birthday:', pe.birthday, ' Notes:', pe.notes ) AS text,"
			." pr.project_id AS project,"
			." CONCAT( 'index.php?option=com_sportsmanagement"
			."&view=referee&pid=', CONCAT_WS(':',pe.id,pe.alias) ,'&p=', CONCAT_WS(':',pro.id,pro.alias) ) AS href,"
			." '2' AS browsernav"
			." FROM #__sportsmanagement_person AS pe"
			." LEFT JOIN #__sportsmanagement_project_referee AS pr"
			." ON pr.person_id = pe.id"
            ." LEFT JOIN #__sportsmanagement_project AS pro"
			." ON pr.project_id = pro.id"
			." WHERE ( ".$wheresperson." ) "
			." AND pe.published = '1' "
			." GROUP BY pe.lastname, pe.firstname, pe.nickname ORDER BY pe.lastname,pe.firstname,pe.nickname";
			
			$db->setQuery( $query );
			$list = $db->loadObjectList();
			$rows[] = $list;

		}

		if ( $search_playgrounds )
		{

			$query = "SELECT 'Playground' as section, pl.name AS title,"
			." pl.checked_out_time AS created,"
			." pl.country,"
			." pl.picture AS picture, "
			." pl.notes AS text,"
			." r.project_id AS project,"
			." CONCAT( 'index.php?option=com_sportsmanagement"
			."&view=playground&pgid=', CONCAT_WS(':',pl.id,pl.alias) ,'&p=', CONCAT_WS(':',pro.id,pro.alias) ) AS href,"
			." '2' AS browsernav"
			." FROM #__sportsmanagement_playground AS pl"
			." LEFT JOIN #__sportsmanagement_club AS c"
			." ON c.id = pl.club_id"
			." LEFT JOIN #__sportsmanagement_match AS m"
			." ON m.playground_id = pl.id"
			." LEFT JOIN #__sportsmanagement_round AS r"
			." ON m.round_id = r.id"
            ." LEFT JOIN #__sportsmanagement_project AS pro"
			." ON r.project_id = pro.id"
			." WHERE ( ".$wheresplayground." ) "
			." GROUP BY pl.name ORDER BY pl.name ";

			$db->setQuery( $query );
			$list = $db->loadObjectList();
			$rows[] = $list;
		}
        
        if ( $search_projects )
		{
        $query = "SELECT 'Project' as section, pro.name AS title,"
			." pro.checked_out_time AS created,"
			." l.country,"
			." pro.picture AS picture, "
			." CONCAT( pro.name, ' Staffel-ID (', pro.staffel_id, ')' ) AS text,"
			." pro.id AS project,"
			." CONCAT( 'index.php?option=com_sportsmanagement"
			."&view=ranking&type=', '0','&p=', CONCAT_WS(':',pro.id,pro.alias) ) AS href,"
			." '2' AS browsernav"
			." FROM #__sportsmanagement_project AS pro"
			." LEFT JOIN #__sportsmanagement_league AS l"
			." ON l.id = pro.league_id"
			." WHERE ( ".$wheresproject." ) "
			." GROUP BY pro.name ORDER BY pro.name ";

			$db->setQuery( $query );
			$list = $db->loadObjectList();
			$rows[] = $list;
        }

		$results = array();

		if(count($rows))
		{
		  foreach($rows as $row)
			{
			if ( $row )
			{
			foreach($row as $output )
			{
			//echo 'country<pre>'.print_r($output->country,true).'</pre><br>';
			//echo 'picture<pre>'.print_r($output->picture,true).'</pre><br>';
            $output->href = JRoute::_($output->href);
			if ( $output->country)
			{
			$flag = Countries::getCountryFlag($output->country);
			$output->flag = $flag;
			$output->text = $flag.' '.$output->text ;
			}
      if ( $output->picture )
			{
			$output->text = '<p><img style="float: left;" src="'.$output->picture.'" alt="" width="50" height="" >'.$output->text.'</p>';
			}
			}
			}
			}
			
			foreach($rows as $row)
			{
			// diddipoeler
			// testausgabe
			//echo '<pre>'.print_r($row,true).'</pre><br>';
				$results = array_merge($results, (array) $row);
			}
		}
		return $results;
	}
}
?>