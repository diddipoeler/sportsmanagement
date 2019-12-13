<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      helper.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_birthday
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$mainframe = Factory::getApplication();
$database = sportsmanagementHelper::getDBConnection();
$players = array();
$crew = array();

if (!function_exists('jsm_birthday_sort')) {

    /**
     * jsm_birthday_sort()
     * 
     * @param mixed $array
     * @param mixed $arguments
     * @param bool $keys
     * @return
     */
    function jsm_birthday_sort($array, $arguments = '-', $keys = true) {
     $mainframe = Factory::getApplication(); 

// Hole eine Liste von Spalten
foreach ($array as $key => $row) {
    $days_to_birthday[$key] = $row['days_to_birthday'];
    $age[$key] = $row['age'];
}

$sort_age = ( $arguments == '-' ) ? array_multisort($days_to_birthday, SORT_ASC, $age, SORT_ASC, $array )  : array_multisort($days_to_birthday, SORT_ASC, $age, SORT_DESC, $array );
    
        return $array;
    }
 
}

$usedp = $params->get('projects', '0');
$p = (is_array($usedp)) ? implode(",", array_map('intval', $usedp)) : (int) $usedp;

$usedteams = "";

/**
 * get favorite team(s), we have to make a function for this
 */
if ( $params->get('use_fav') ) {
    $query = $database->getQuery(true);
    $query->select('fav_team');
    $query->from('#__sportsmanagement_project');

    if ($p != '' && $p > 0) {
        $query->where('id IN (' . $p . ')');
    }

    $database->setQuery($query);

    $temp = $database->loadResultArray();

    if (!$temp) {

    }

    if (!empty($temp)) {
        $usedteams = join(',', array_filter($temp));
    }
} else {
    $usedp = $params->get('teams', '0');
    $usedteams = (is_array($usedp)) ? implode(",", array_map('intval', $usedp)) : (int) $usedp;
}

$birthdaytext = '';

/** get player info, we have to make a function for this */
$dateformat = "DATE_FORMAT(p.birthday,'%Y-%m-%d') AS date_of_birth";

if ( $params->get('use_which') <= 1 ) {
    $query = $database->getQuery(true);
    $query->clear();
    $subquery1 = $database->getQuery(true);
    $subquery1->clear();

    $query->select('p.id, p.birthday, p.firstname, p.nickname, p.lastname,p.picture AS default_picture, p.country,DATE_FORMAT(p.birthday, \'%m-%d\')AS daymonth');
    $query->select('YEAR( CURRENT_DATE( ) ) as year');
    $query->select('(YEAR( CURRENT_DATE( ) ) - YEAR( p.birthday ) +	IF(DATE_FORMAT(CURDATE(), \'%m.%d\') > DATE_FORMAT(p.birthday, \'%m.%d\'), 1, 0)) AS age');
    $query->select($dateformat);
    $query->select('stp.picture');
    $query->select('(TO_DAYS(DATE_ADD(p.birthday, INTERVAL (YEAR(CURDATE()) - YEAR(p.birthday) + IF(DATE_FORMAT(CURDATE(), \'%m.%d\') >	DATE_FORMAT(p.birthday, \'%m.%d\'), 1, 0)) YEAR)) - TO_DAYS( CURDATE())+0) AS days_to_birthday');
    $query->select('\'person\' AS func_to_call, \'\' project_id, \'\' team_id');
    $query->select('\'pid\' AS id_to_append, 1 AS type');
    $query->select('st.team_id, pt.project_id');

    $query->select('CONCAT_WS(\':\',pro.id,pro.alias) AS project_slug');
    $query->select('CONCAT_WS(\':\',p.id,p.alias) AS person_slug');
    $query->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
 $query->select('CONCAT_WS(\':\',s.id,s.alias) AS season_slug');
    $query->select('t.name as team_name');

    $query->from('#__sportsmanagement_person AS p ');
    $query->join('INNER', '#__sportsmanagement_season_team_person_id as stp ON stp.person_id = p.id ');
    $query->join('INNER', '#__sportsmanagement_season_team_id as st ON st.team_id = stp.team_id and st.season_id = stp.season_id ');
    $query->join('INNER', '#__sportsmanagement_project_team as pt ON st.id = pt.team_id ');
    $query->join('INNER', '#__sportsmanagement_project AS pro ON pro.id = pt.project_id and pro.season_id = st.season_id');
 $query->join('INNER', '#__sportsmanagement_season AS s ON s.id = pro.season_id');
    $query->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id');
    $query->where('p.published = 1 AND p.birthday != \'0000-00-00\'');
 if ( $params->get('use_which') == 1 )
 {
    $query->where('stp.persontype = 1');
 }
 elseif ( $params->get('use_which') == 0 )
 {
    $query->where(' ( stp.persontype = 1 OR stp.persontype = 2 ) ');
 }
    $subquery1->select('pos.name');
    $subquery1->from('#__sportsmanagement_position AS pos ');
    $subquery1->join('INNER', '#__sportsmanagement_project_position as ppos ON ppos.position_id = pos.id ');
    $subquery1->join('INNER', '#__sportsmanagement_person_project_position as pppos ON pppos.project_position_id = ppos.id ');
    $subquery1->where('pppos.person_id = p.id');
    $subquery1->where('pppos.project_id = pro.id');
    $subquery1->setLimit(1);
    $query->select('(' . $subquery1 . ') AS position_name');


    if ($params->get('agegrouplist')) {
        $query->where('p.agegroup_id = ' . $params->get('agegrouplist'));
    }

    if ($usedteams != '') {
        $query->where('st.team_id IN (' . $usedteams . ')');
    }

    if ($p != '' && $p > 0) {
        $query->where('pt.project_id IN (' . $p . ')');
    }

    $query->group('p.id');

    $maxDays = $params->get('maxdays');
    if ((strlen($maxDays) > 0) && (intval($maxDays) >= 0)) {
        $query->having('days_to_birthday <= ' . intval($maxDays));
    }

    $query->order('days_to_birthday ASC');

    if ($params->get('limit') > 0) {
        $database->setQuery($query, 0, $params->get('limit'));
    } else {
        $database->setQuery($query);
    }
    
 try{
    $players = $database->loadAssocList();
 } catch (Exception $e) {
                $mainframe->enqueueMessage(__FILE__.' '. __LINE__ . Text::_($e->getMessage()), 'Error');
             }
 
}

/** get staff info, we have to make a function for this */
if ( $params->get('use_which') == 2 ) {
    $query = $database->getQuery(true);
    $query->clear();

    $query->select('p.id, p.birthday, p.firstname, p.nickname, p.lastname,p.picture AS default_picture, p.country,DATE_FORMAT(p.birthday, \'%m-%d\')AS daymonth');
    $query->select('YEAR( CURRENT_DATE( ) ) as year');
    $query->select('(YEAR( CURRENT_DATE( ) ) - YEAR( p.birthday ) +	IF(DATE_FORMAT(CURDATE(), \'%m.%d\') > DATE_FORMAT(p.birthday, \'%m.%d\'), 1, 0)) AS age');
    $query->select($dateformat);
    $query->select('stp.picture');
    $query->select('(TO_DAYS(DATE_ADD(p.birthday, INTERVAL (YEAR(CURDATE()) - YEAR(p.birthday) + IF(DATE_FORMAT(CURDATE(), \'%m.%d\') >	DATE_FORMAT(p.birthday, \'%m.%d\'), 1, 0)) YEAR)) - TO_DAYS( CURDATE())+0) AS days_to_birthday');
    $query->select('\'staff\' AS func_to_call, \'\' project_id, \'\' team_id');
    $query->select('st.team_id, pt.project_id');
    $query->select('\'tsid\' AS id_to_append, 2 AS type');

    $query->select('CONCAT_WS(\':\',pro.id,pro.alias) AS project_slug');
    $query->select('CONCAT_WS(\':\',p.id,p.alias) AS person_slug');
    $query->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');

    $query->from('#__sportsmanagement_person AS p ');
    $query->join('INNER', '#__sportsmanagement_season_team_person_id as stp ON stp.person_id = p.id ');
    $query->join('INNER', '#__sportsmanagement_season_team_id as st ON st.team_id = stp.team_id and st.season_id = stp.season_id ');
    $query->join('INNER', '#__sportsmanagement_project_team as pt ON st.id = pt.team_id ');
    $query->join('INNER', '#__sportsmanagement_project AS pro ON pro.id = pt.project_id and pro.season_id = st.season_id');
    $query->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id');

    $query->where('p.published = 1 AND p.birthday != \'0000-00-00\'');
    $query->where('stp.persontype = 2');
    if ($params->get('agegrouplist')) {
        $query->where('p.agegroup_id = ' . $params->get('agegrouplist'));
    }
    /** Exclude players from the staff query to avoid duplicate persons (if a person is both player and staff) */
    if (count($players) > 0) {
        $ids = "0";
        foreach ($players AS $player) {
            $ids .= "," . $player['id'];
        }
        $query->where('p.id NOT IN (' . $ids . ')');
    }

    if ($usedteams != '') {
        $query->where('st.team_id IN (' . $usedteams . ')');
    }
    if ($p != '' && $p > 0) {
        $query->where('pt.project_id IN (' . $p . ')');
    }
    $query->group('p.id');

    $maxDays = $params->get('maxdays');
    if ((strlen($maxDays) > 0) && (intval($maxDays) >= 0)) {
        $query->having('days_to_birthday <= ' . intval($maxDays));
    }

    $query->order('days_to_birthday ASC');

    if ($params->get('limit') > 0) {
        $database->setQuery($query, 0, $params->get('limit'));
    } else {
        $database->setQuery($query);
    }

try{
    $crew = $database->loadAssocList();
 } catch (Exception $e) {
                $mainframe->enqueueMessage(__METHOD__ . ' ' . __LINE__ . Text::_($e->getMessage()), 'Error');
             }

}
?>
