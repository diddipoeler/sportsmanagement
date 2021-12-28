<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_firstleagueoverview
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;


/**
 * modjsmfirstleagueoverview
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2021
 * @version $Id$
 * @access public
 */
class modjsmfirstleagueoverview
{

	 /**
	  * modjsmfirstleagueoverview::getSeasonNames()
	  * 
	  * @param mixed $params
	  * @return
	  */
	 public static function getSeasonNames($params)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        $query->clear();

$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
      return $result;
    
  }
	

	/**
	 * modjsmfirstleagueoverview::getData()
	 * 
	 * @param mixed $params
	 * @return
	 */
	public static function getData($params)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        
        $query->select('l.id,l.country, l.name as league_name');
        $query->from('#__sportsmanagement_league AS l');
        $query->where('l.champions_complete = 1');
        $query->where('l.league_level = 1');
        $db->setQuery($query);
		$result_league = $db->loadObjectList();
        echo '<pre>'.print_r($result_league,true).'</pre>';
        
//        $query->select('p.*, l.country, st.id AS sport_type_id, st.name AS sport_type_name');
//		$query->select('st.icon AS sport_type_picture, st.eventtime as useeventtime, l.picture as leaguepicture, l.name as league_name, s.name as season_name,r.name as round_name');
//		$query->select('LOWER(SUBSTR(st.name, CHAR_LENGTH( "COM_SPORTSMANAGEMENT_ST_")+1)) AS fs_sport_type_name');
//		$query->select('CONCAT_WS( \':\', p.id, p.alias ) AS slug');
//		$query->select('CONCAT_WS( \':\', l.id, l.alias ) AS league_slug');
//		$query->select('CONCAT_WS( \':\', s.id, s.alias ) AS season_slug');
//		$query->select('CONCAT_WS( \':\', r.id, r.alias ) AS round_slug');
//		$query->select('l.cr_picture as cr_leaguepicture,l.champions_complete');
//		$query->from('#__sportsmanagement_project AS p ');
//		$query->join('INNER', '#__sportsmanagement_sports_type AS st ON p.sports_type_id = st.id ');
//		$query->join('LEFT', '#__sportsmanagement_league AS l ON p.league_id = l.id ');
//		$query->join('LEFT', '#__sportsmanagement_season AS s ON p.season_id = s.id ');
//		$query->join('LEFT', '#__sportsmanagement_round AS r ON p.current_round = r.id ');
//		$query->where('l.champions_complete = 1');
//        $query->where('l.league_level = 1');

//		$db->setQuery($query);
//		$result = $db->loadObjectList();
      


		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;

	}

}
