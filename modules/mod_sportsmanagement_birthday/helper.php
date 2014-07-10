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

// no direct access
defined('_JEXEC') or die('Restricted access');
$mainframe = JFactory::getApplication();
$database = JFactory::getDBO();
$players = array();
$crew = array();

//$mainframe->enqueueMessage(JText::_(__FILE__.' '.__LINE__.' params<br><pre>'.print_r($params,true).'</pre>'),'');

if(!function_exists('jl_birthday_sort'))
{
	// snippet taken from http://de3.php.net/manual/en/function.uasort.php
	/**
	 * jl_birthday_sort()
	 * 
	 * @param mixed $array
	 * @param mixed $arguments
	 * @param bool $keys
	 * @return
	 */
	function jl_birthday_sort ($array, $arguments = array(), $keys = true) {
		$code = "\$result=0;";
		foreach ($arguments as $argument) {
			$field = substr($argument, 2, strlen($argument));
			$type = $argument[0];
			$order = $argument[1];
			$code .= "if (!Is_Numeric(\$result) || \$result == 0) ";
			if (strtolower($type) == "n") $code .= $order == "-" ? "\$result = (intval(\$a['{$field}']) > intval(\$b['{$field}']) ? -1 : (intval(\$a['{$field}']) < intval(\$b['{$field}']) ? 1 : 0));" : "\$result = (intval(\$a['{$field}']) > intval(\$b['{$field}']) ? 1 : (intval(\$a['{$field}']) < intval(\$b['{$field}']) ? -1 : 0));";
			else $code .= $order == "-" ? "\$result = strcoll(\$a['{$field}'], \$b['{$field}']) * -1;" : "\$result = strcoll(\$a['{$field}'], \$b['{$field}']);";
		}
		$code .= "return \$result;";
		$compare = create_function('$a, $b', $code);
		if ($keys) uasort($array, $compare);
		else usort($array, $compare);
		return $array;
	}
}

$usedp = $params->get('projects','0');
$p = (is_array($usedp)) ? implode(",", $usedp) : $usedp;

//$usedp = $params->get('teams','0');
//$usedteams = (is_array($usedp)) ? implode(",", $usedp) : $usedp;

//$mainframe->enqueueMessage(JText::_(__FILE__.' '.__LINE__.' usedteams<br><pre>'.print_r($usedteams,true).'</pre>'),'');

$usedteams = "";

// get favorite team(s), we have to make a function for this
if ($params->get('use_fav')==1)
{
    $query = $database->getQuery(true);
    $query->select('fav_team');
    $query->from('#__sportsmanagement_project');
            
	if ($p!='' && $p>0) 
    $query->where('id IN ('.$p.')');

    ;

	$database->setQuery($query);
    
    //$mainframe->enqueueMessage(JText::_(__FILE__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
    
	$temp = $database->loadColumn();
    
    if ( !$temp )
    {
        //$mainframe->enqueueMessage(JText::_(__FILE__.' '.__LINE__.' <br><pre>'.print_r($database->getErrorMsg(),true).'</pre>'),'Error');
    }

	if ( count($temp) > 0 )
	{
		$usedteams = join(',', array_filter($temp));
	}
}
else
{
	//$usedteams = $params->get('teams');
    $usedp = $params->get('teams','0');
    $usedteams = (is_array($usedp)) ? implode(",", $usedp) : $usedp;
}

$birthdaytext='';

// get player info, we have to make a function for this
$dateformat = "DATE_FORMAT(p.birthday,'%Y-%m-%d') AS date_of_birth";

if ($params->get('use_which') <= 1)
{
    $query = $database->getQuery(true);
    $query->clear();
         
    $query->select('p.id, p.birthday, p.firstname, p.nickname, p.lastname,p.picture AS default_picture, p.country,DATE_FORMAT(p.birthday, \'%m-%d\')AS daymonth');        
	$query->select('YEAR( CURRENT_DATE( ) ) as year');
    $query->select('(YEAR( CURRENT_DATE( ) ) - YEAR( p.birthday ) +	IF(DATE_FORMAT(CURDATE(), \'%m.%d\') > DATE_FORMAT(p.birthday, \'%m.%d\'), 1, 0)) AS age');
    $query->select($dateformat);
    $query->select('stp.picture');
    $query->select('(TO_DAYS(DATE_ADD(p.birthday, INTERVAL (YEAR(CURDATE()) - YEAR(p.birthday) + IF(DATE_FORMAT(CURDATE(), \'%m.%d\') >	DATE_FORMAT(p.birthday, \'%m.%d\'), 1, 0)) YEAR)) - TO_DAYS( CURDATE())+0) AS days_to_birthday');
    $query->select('\'person\' AS func_to_call, \'\' project_id, \'\' team_id');
    $query->select('\'pid\' AS id_to_append, 1 AS type');
    $query->select('pt.team_id, pt.project_id');
    
    $query->from('#__sportsmanagement_person AS p ');
    $query->join('INNER',' #__sportsmanagement_season_team_person_id as stp ON stp.person_id = p.id ');
    $query->join('INNER',' #__sportsmanagement_season_team_id as st ON st.team_id = stp.team_id ');
    $query->join('INNER',' #__sportsmanagement_project_team as pt ON st.id = pt.team_id ');
    
    $query->where('p.published = 1 AND p.birthday != \'0000-00-00\'');
    $query->where('stp.persontype = 1');

    		
	if ($usedteams!='') 
    $query->where('st.team_id IN ('.$usedteams.')');


	if ($p!='' && $p>0) 
    $query->where('pt.project_id IN ('.$p.')');


	$query->group('p.id');

	$maxDays = $params->get('maxdays');
	if ((strlen($maxDays) > 0) && (intval($maxDays) >= 0))
	{
        $query->having('days_to_birthday <= '. intval($maxDays));
	}

	$query->order('days_to_birthday ASC');

	if ($params->get('limit') > 0) 
    $query->setLimit($params->get('limit'));


	$database->setQuery($query);
	//echo("<hr>".$database->getQuery($query));
    
    //$mainframe->enqueueMessage(JText::_(__FILE__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
    
	$players = $database->loadColumn();
}

//get staff info, we have to make a function for this
if ($params->get('use_which') == 2 || $params->get('use_which') == 0)
{
    $query = $database->getQuery(true);
    $query->clear();

	$query->select('p.id, p.birthday, p.firstname, p.nickname, p.lastname,p.picture AS default_picture, p.country,DATE_FORMAT(p.birthday, \'%m-%d\')AS daymonth');        
	$query->select('YEAR( CURRENT_DATE( ) ) as year');
    $query->select('(YEAR( CURRENT_DATE( ) ) - YEAR( p.birthday ) +	IF(DATE_FORMAT(CURDATE(), \'%m.%d\') > DATE_FORMAT(p.birthday, \'%m.%d\'), 1, 0)) AS age');
    $query->select($dateformat);
    $query->select('stp.picture');
    $query->select('(TO_DAYS(DATE_ADD(p.birthday, INTERVAL (YEAR(CURDATE()) - YEAR(p.birthday) + IF(DATE_FORMAT(CURDATE(), \'%m.%d\') >	DATE_FORMAT(p.birthday, \'%m.%d\'), 1, 0)) YEAR)) - TO_DAYS( CURDATE())+0) AS days_to_birthday');
    $query->select('\'staff\' AS func_to_call, \'\' project_id, \'\' team_id');
    $query->select('pt.team_id, pt.project_id');
    $query->select('\'tsid\' AS id_to_append, 2 AS type');
    
    $query->from('#__sportsmanagement_person AS p ');
    $query->join('INNER',' #__sportsmanagement_season_team_person_id as stp ON stp.person_id = p.id ');
    $query->join('INNER',' #__sportsmanagement_season_team_id as st ON st.team_id = stp.team_id ');
    $query->join('INNER',' #__sportsmanagement_project_team as pt ON st.id = pt.team_id ');
    
    $query->where('p.published = 1 AND p.birthday != \'0000-00-00\'');
    $query->where('stp.persontype = 2');
    
    // Exclude players from the staff query to avoid duplicate persons (if a person is both player and staff)
	if(count($players) > 0)
	{
		$ids = "0";
		foreach ($players AS $player)
		{
			$ids .= "," . $player['id'];
		}
        $query->where('p.id NOT IN ('.$ids.')');
	}

	if ($usedteams!='') 
    $query->where('st.team_id IN ('.$usedteams.')');

	if ($p!='' && $p>0) 
    $query->where('pt.project_id IN ('.$p.')');

	$query->group('p.id');

	$maxDays = $params->get('maxdays');
	if ((strlen($maxDays) > 0) && (intval($maxDays) >= 0))
	{
        $query->having('days_to_birthday <= '. intval($maxDays));
	}

	$query->order('days_to_birthday ASC');

	if ($params->get('limit') > 0) 
    $query->setLimit($params->get('limit'));

	$database->setQuery($query);
    
    //$mainframe->enqueueMessage(JText::_(__FILE__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
    
	//echo("<hr>".$database->getQuery($query));
	$crew = $database->loadColumn();
}
?>