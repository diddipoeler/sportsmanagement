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

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'sportsmanagement.php');
require_once(dirname(__FILE__).DS.'helper.php');

$document = JFactory::getDocument();
$show_debug_info = JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0) ;

//add css file
$document->addStyleSheet(JURI::base().'modules/mod_sportsmanagement_club_birthday/css/mod_sportsmanagement_club_birthday.css');

$mode = $params->def("mode");
$results = $params->get('limit');
$limit = $params->get('limit');
$refresh = $params->def("refresh");
$minute = $params->def("minute");
$height = $params->def("height");
$width  = $params->def("width");
// Prevent that result is null when either $players or $crew is null by casting each to an array.
//$persons = array_merge((array)$players, (array)$crew);
$clubs = modSportsmanagementClubBirthdayHelper::getClubs($limit);
if(count($clubs)>1)   $clubs = modSportsmanagementClubBirthdayHelper::jl_birthday_sort($clubs,$params->def("sort_order"));

if ( $show_debug_info )
{
echo 'this->mod_sportsmanagement_club_birthday clubs<br /><pre>~' . print_r($clubs,true) . '~</pre><br />';
echo 'this->mod_sportsmanagement_club_birthday params<br /><pre>~' . print_r($params,true) . '~</pre><br />';
}

$k=0;
$counter=0;

//echo 'mode -> '.$mode.'<br>';
//echo 'refresh -> '.$refresh.'<br>';
//echo 'minute -> '.$minute.'<br>';


if(count($clubs) > 0) {

if (count($clubs)<$results)
	{
		$results=count($clubs);
	}
        
$tickerpause = $params->def("tickerpause");
	$scrollspeed = $params->def("scrollspeed");
	$scrollpause = $params->def("scrollpause");

	switch ($mode)
	{
		case 'T':
			include(dirname(__FILE__).DS.'js'.DS.'ticker.js');
			break;
		case 'V':
			include(dirname(__FILE__).DS.'js'.DS.'qscrollerv.js');
			$document->addScript(JURI::base().'modules/mod_sportsmanagement_club_birthday/js/qscroller.js');
			break;
		case 'H':
			include(dirname(__FILE__).DS.'js'.DS.'qscrollerh.js');
			$document->addScript(JURI::base().'modules/mod_sportsmanagement_club_birthday/js/qscroller.js');
			break;
	}
    
}




require(JModuleHelper::getLayoutPath('mod_sportsmanagement_club_birthday'));
?>
