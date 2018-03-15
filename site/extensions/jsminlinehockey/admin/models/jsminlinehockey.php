<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
defined( '_JEXEC' ) or die( 'Restricted access' );
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
jimport( 'joomla.application.component.model' );


$maxImportTime = 480;

if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}

$maxImportMemory = '350M';
if ((int)ini_get('memory_limit') < (int)$maxImportMemory){@ini_set('memory_limit',$maxImportMemory);}

/**
 * sportsmanagementModeljsminlinehockey
 * 
 * @package 
 * @author Dieter Pl�
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementModeljsminlinehockey extends JModelLegacy
{

var $storeFailedColor = 'red';
	var $storeSuccessColor = 'green';
	var $existingInDbColor = 'orange';
     static public $success_text = '';
    var $success_text_teams = '';
    var $success_text_results = '';
    
    var $teamart = '';
    var $country = '';
    var $project_type = '';
    var $season_id = 0;
    var $teams = array();
    var $rounds = array();
    var $divisions = array();
    var $matches = array();
    var $projectteams = array();




/**
 * sportsmanagementModeljsminlinehockey::__construct()
 * 
 * @return void
 */
function __construct()
	{
		$mainframe = JFactory::getApplication();
        
        if($mainframe->isAdmin()) 
{

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'libraries'.DS.'PHPExcel'.DS.'PHPExcel.php');
}
        parent::__construct();
        }


/**
 * sportsmanagementModeljsminlinehockey::checkProjectTeam()
 * 
 * @param mixed $team_id
 * @param mixed $project_id
 * @param mixed $season_id
 * @return
 */
function checkProjectTeam($team_id,$project_id,$season_id)
{
$option = JFactory::getApplication()->input->getCmd('option');
$mainframe = JFactory::getApplication();
$db = JFactory::getDBO();
$query = $db->getQuery(true);

$query->clear();
$query->select('id');
$query->from('#__sportsmanagement_season_team_id');
$query->where('team_id = '.$team_id);
$query->where('season_id = '.$season_id);
$db->setQuery( $query );
$season_team_id = $db->loadResult();
// team ist der saison zugeordnet
if ( $season_team_id )
{
//$temp->season_team_id = $season_team_id;

$query->clear();
$query->select('id');
$query->from('#__sportsmanagement_project_team');
$query->where('team_id = '.$season_team_id);
$query->where('project_id = '.$project_id);
$db->setQuery( $query );
$projectteam_id = $db->loadResult();

// dem project zugeordnet
if ( $projectteam_id )
{
return $projectteam_id;
}
else
{
    
// und dem projekt hinzuf?gen
$temp_project_team = new stdClass();
$temp_project_team->team_id = $season_team_id;
$temp_project_team->project_id = $project_id;
// Insert the object into the table.
$result_project_team = JFactory::getDbo()->insertObject('#__sportsmanagement_project_team', $temp_project_team);

if ( $result_project_team )
{
return $db->insertid();
}
else
{
return 0;     
}    

}    

}
else
{
// team ist nicht der saison zugeordnet  
// Create and populate an object.
$temp_season_team_id = new stdClass();
$temp_season_team_id->team_id = $team_id;
$temp_season_team_id->season_id = $season_id;
// Insert the object into the table.
$result_season_team_id = JFactory::getDbo()->insertObject('#__sportsmanagement_season_team_id', $temp_season_team_id);
if ( $result_season_team_id )
{
$season_team_id = $db->insertid();
// und dem projekt hinzuf?gen
$temp_project_team = new stdClass();
$temp_project_team->team_id = $season_team_id;
$temp_project_team->project_id = $project_id;
// Insert the object into the table.
$result_project_team = JFactory::getDbo()->insertObject('#__sportsmanagement_project_team', $temp_project_team);

if ( $result_project_team )
{
return $db->insertid();
}
else
{
return 0;     
}

}
else
{
return 0;                   
}    
    
}

}



/**
 * sportsmanagementModeljsminlinehockey::getmatches()
 * 
 * @param integer $projectid
 * @param string $username
 * @param string $password
 * @return void
 */
function getmatches($projectid=0,$username='',$password='')
{
$app = JFactory::getApplication ();
$jinput = $app->input;
$option = $jinput->getCmd('option');
$db = JFactory::getDbo();
$query = $db->getQuery(true);
//set the target directory
$base_Dir = JPATH_SITE . DS . 'images' . DS . $option . DS .'database'.DS. 'clubs/large' . DS;
$post = $jinput->post->getArray();
//$app->enqueueMessage(__METHOD__.' '.__LINE__.'post <br><pre>'.print_r($post, true).'</pre><br>','Notice');
$matchlink = '';

if ( $post )
{
$matchlink = $post['matchlink'];
}

if ( !$projectid )
{
    $projectid = $post['projectid'];
}

if ( !$matchlink )
{
    $matchlink = $this->getMatchLink($projectid);
}

if($app->isAdmin()) 
{
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectid -> '.$projectid.''),'');
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' matchlink -> '.$matchlink.''),'');
}

$teams = array();

$query->clear();
$query->select('season_id');
$query->from('#__sportsmanagement_project');
$query->where('id = '.$projectid);
$db->setQuery( $query );
$season_id = $db->loadResult();

$spieltag = 1;
$query->clear();
$query->select('id');
$query->from('#__sportsmanagement_round');
$query->where('roundcode LIKE '.$db->Quote(''.$spieltag.''));
$query->where('project_id = '.$projectid);
$db->setQuery( $query );
$round_id = $db->loadResult();


if ( empty($round_id) )
{

// wenn nichts gefunden wurde neue runde anlegen
$newround = new stdClass();
$newround->roundcode = $spieltag;
$newround->name = $spieltag.'.Spieltag';
$newround->project_id = $projectid;

// Insert the object
$result = JFactory::getDbo()->insertObject('#__sportsmanagement_round', $newround);
// runde angelegt
if ( $result )
{
$round_id = $db->insertid();      
}   

}

$url_clubs = $matchlink;
$curl = curl_init($url_clubs);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//curl_setopt($curl, CURLOPT_HEADER, 1);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
if ( $username && $password )
{
curl_setopt($curl, CURLOPT_USERPWD, $username.':'.$password );
}
$result = curl_exec($curl);
$code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);

// Will dump a beauty json :3
$json_object_matches = json_decode($result);
$json_array = json_decode($result,true);
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result object<br><pre>'.print_r($result,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result object<br><pre>'.print_r($json_object_matches,true).'</pre>'),'');
$pages = $json_object_matches->pages;
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result object<br><pre>'.print_r($pages ,true).'</pre>'),'');

for( $page =1; $page <= $pages ; $page++  )
{
$url_clubs = $matchlink.'?page='.$page;
$curl = curl_init($url_clubs);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//curl_setopt($curl, CURLOPT_HEADER, 1);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
if ( $username && $password )
{
curl_setopt($curl, CURLOPT_USERPWD, $username.':'.$password );
}
$result = curl_exec($curl);
$code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);

// Will dump a beauty json :3
$json_object_matches = json_decode($result);
$json_array = json_decode($result,true);
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result object<br><pre>'.print_r($result,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result object<br><pre>'.print_r($json_object_matches,true).'</pre>'),'');

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' schedule<br><pre>'.print_r( sizeof($json_object_matches->_embedded->schedule) ,true).'</pre>'),'');


for($a=0; $a < sizeof($json_object_matches->_embedded->schedule)  ;$a++ )
{

$value_match = $json_object_matches->_embedded->schedule[$a];

if ( $a == 0 )
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result object<br><pre>'.print_r($value_match,true).'</pre>'),'');
}

$temp = new stdClass();
$temp->round_id = $round_id;
$temp->match_id = $value_match->id;
$temp->match_date = substr($value_match->date_time,0,10).' '.substr($value_match->date_time,11,8);
$temp->club_name_home = $value_match->home_team->club->name;
$temp->club_id_home = $value_match->home_team->club->id;
$temp->club_website_home = $value_match->home_team->club->website->url;
$temp->club_infosite_home = $value_match->home_team->club->_links->self->href;
$temp->club_logo_home = $value_match->home_team->club->_links->logo->href;
$temp->team_name_home = $value_match->home_team->full_name;
$temp->team_id_home = $value_match->home_team->team_id;
$temp->team_info_home = $value_match->home_team->alternate_team_name;

/**
 * hier überprüfen wir den heimverein
 */
// Select some fields 
$query->clear(); 
$query->select('id'); 
// From the table 
$query->from('#__sportsmanagement_club'); 
$query->where('id = '.$temp->club_id_home ); 
$db->setQuery($query); 

try {
$club_id_heim = $db->loadResult();
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$temp->club_name_home, 'error');
}


if ( !$club_id_heim ) 
{
$filepath = $base_Dir . $temp->club_id_home.'_'.basename($temp->club_logo_home);
$linkaddress = 'https://www.ishd.de'.$temp->club_logo_home;
if ( !copy($linkaddress,$filepath) )
{
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' fehler beim kopieren<br><pre>'.print_r($linkaddress,true).'</pre>'),'Error');    
}
else
{
}
    
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' filepath -> '.$filepath.''),'');
// Create and populate an object.
$profile = new stdClass();
$profile->id = $temp->club_id_home;
$profile->name = $temp->club_name_home;
$profile->country = 'DEU';
$profile->website = $temp->club_website_home;
$profile->alias = JFilterOutput::stringURLSafe( $temp->club_name_home );;
// Insert the object into the user profile table.
$result = JFactory::getDbo()->insertObject('#__sportsmanagement_club', $profile);
}


if ( $temp->team_id_home )
{
/**
 * hier überprüfen wir die heimmannschaft
 */     
// Select some fields 
$query->clear(); 
$query->select('id'); 
// From the table 
$query->from('#__sportsmanagement_team'); 
$query->where('id = '.$temp->team_id_home ); 
$db->setQuery($query); 
if ( !$db->loadResult() ) 
{
// Create and populate an object.
$profile = new stdClass();
$profile->id = $temp->team_id_home;
$profile->club_id = $temp->club_id_home;
$profile->name = $temp->team_name_home;
$profile->short_name = $temp->team_name_home;
$profile->middle_name = $temp->team_name_home;
$profile->info = $temp->team_info_home;
$profile->sports_type_id = $sports_type_id;
$profile->alias = JFilterOutput::stringURLSafe( $temp->team_name_home );;
 
// Insert the object into the user profile table.
$result = JFactory::getDbo()->insertObject('#__sportsmanagement_team', $profile);
}

$teams[$temp->team_name_home] = $temp->team_id_home;
}
else
{
if ( $temp->club_id_home )
{
$query->clear();
$query->select('id');
$query->from('#__sportsmanagement_team');
$query->where('info LIKE '.$db->Quote(''.$temp->team_info_home.''));
$query->where('club_id = '.$temp->club_id_home);
$db->setQuery( $query );
$temp->team_id_home = $db->loadResult();
$teams[$temp->team_name_home] = $temp->team_id_home;
}
}

if ( $temp->team_id_home )
{
$temp->projectteam1_id = $this->checkProjectTeam($temp->team_id_home,$projectid,$season_id);
}


$temp->club_name_away = $value_match->away_team->club->name;
$temp->club_id_away = $value_match->away_team->club->id;
$temp->club_website_away = $value_match->away_team->club->website->url;
$temp->club_infosite_away = $value_match->away_team->club->_links->self->href;
$temp->club_logo_away = $value_match->away_team->club->_links->logo->href;
$temp->team_name_away = $value_match->away_team->full_name;
$temp->team_id_away = $value_match->away_team->team_id;
$temp->team_info_away = $value_match->away_team->alternate_team_name;

/**
 * hier überprüfen wir den auswärtsverein
 */
// Select some fields 
$query->clear(); 
$query->select('id'); 
// From the table 
$query->from('#__sportsmanagement_club'); 
$query->where('id = '.$temp->club_id_away ); 
$db->setQuery($query); 

try {
$club_id_away = $db->loadResult();
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error

JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$temp->club_name_away, 'error');
}

if ( !$club_id_away ) 
{
$filepath = $base_Dir . $temp->club_id_home.'_'.basename($temp->club_logo_away);    
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_name nicht vorhanden -> '.$club_name.''),'');
// Create and populate an object.
$profile = new stdClass();
$profile->id = $temp->club_id_away;
$profile->name = $temp->club_name_away;
$profile->country = 'DEU';
$profile->website = $temp->club_website_away;
$profile->alias = JFilterOutput::stringURLSafe( $temp->club_name_away );;
// Insert the object into the user profile table.
$result = JFactory::getDbo()->insertObject('#__sportsmanagement_club', $profile);
}


if ( $temp->team_id_away)
{
/**
 * hier überprüfen wir die auswärtsmannschaft
 */    
// Select some fields 
$query->clear(); 
$query->select('id'); 
// From the table 
$query->from('#__sportsmanagement_team'); 
$query->where('id = '.$temp->team_id_away ); 
$db->setQuery($query); 
if ( !$db->loadResult() ) 
{
// Create and populate an object.
$profile = new stdClass();
$profile->id = $temp->team_id_away;
$profile->club_id = $temp->club_id_away;
$profile->name = $temp->team_name_away;
$profile->short_name = $temp->team_name_away;
$profile->middle_name = $temp->team_name_away;
$profile->info = $temp->team_info_away;
$profile->sports_type_id = $sports_type_id;
$profile->alias = JFilterOutput::stringURLSafe( $temp->team_name_away );;
 
// Insert the object into the table.
$result = JFactory::getDbo()->insertObject('#__sportsmanagement_team', $profile);
}

$teams[$temp->team_name_away] = $temp->team_id_away;
}
else
{
if ( $temp->club_id_away)
{
$query->clear();
$query->select('id');
$query->from('#__sportsmanagement_team');
$query->where('info LIKE '.$db->Quote(''.$temp->team_info_away.''));
$query->where('club_id = '.$temp->club_id_away);
$db->setQuery( $query );
$temp->team_id_away = $db->loadResult();
$teams[$temp->team_name_away] = $temp->team_id_away;
}
}
if ( $temp->team_id_away)
{
$temp->projectteam2_id = $this->checkProjectTeam($temp->team_id_away,$projectid,$season_id);
}

/**
 * hier gibt es einen status, der uns sagt, welche art
 * das ergebnis hat
 * is_regular_result
 * is_after_overtime
 * is_after_penalty_shoot_out
 */

//$temp->match_result_type = 0;
//if ( $value_match->is_regular_result )
//{
$temp->team1_result = $value_match->home_goals;
$temp->team2_result = $value_match->away_goals;
$temp->match_result_type = 0;
//}
if ( $value_match->is_after_overtime )
{
$temp->team1_result_ot = $value_match->home_goals;
$temp->team2_result_ot = $value_match->away_goals;
$temp->match_result_type = 1;
}
if ( $value_match->is_after_penalty_shoot_out )
{
$temp->team1_result_so = $value_match->home_goals;
$temp->team2_result_so = $value_match->away_goals;
$temp->match_result_type = 2;
}

if ( $value_match->id == 1324 )
{
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' temp<br><pre>'.print_r($temp,true).'</pre>'),'');    
}

/**
 * hier steht der link zum spiel: $value_match->_links->self->href
 */

$exportmatches[] = $temp;
}

}

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($teams,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' exportmatches<br><pre>'.print_r($exportmatches,true).'</pre>'),'');


/**
 * spiele einfügen anfang
 */
foreach ( $exportmatches as $i => $match)
{
if ( $match->projectteam1_id && $match->projectteam2_id && $match->round_id )
{
// spielpaarung suchen
// gibt es das spiel ?
$query->clear();
$query->select('id');
$query->from('#__sportsmanagement_match');
$query->where('import_match_id = '.$match->match_id);
$query->where('projectteam1_id = '.$match->projectteam1_id);
$query->where('projectteam2_id = '.$match->projectteam2_id);
$db->setQuery( $query );
$match_id  = $db->loadResult();        

if ( $match_id )
{

if ( is_numeric($match->team1_result) && is_numeric($match->team2_result) )
{    
$row = new stdClass();    
$row->id = $match_id;    
$row->team1_result = $match->team1_result;
$row->team2_result = $match->team2_result;
$row->team1_result_split = $match->team1_result_split;
$row->team2_result_split = $match->team2_result_split;

$row->team1_result_ot = $match->team1_result_ot;
$row->team2_result_ot = $match->team2_result_ot;
$row->team1_result_so = $match->team1_result_so;
$row->team2_result_so = $match->team2_result_so;
$row->match_result_type = $match->match_result_type;

$row->match_date = $match->match_date;
$row->division_id = $match->division_id;
$row->import_match_id = $match->match_id;
$row->match_number = $match->match_id;
$row->match_timestamp = sportsmanagementHelper::getTimestamp($match->match_date);
$row->published = 1;

// update the object into the table.
$result = JFactory::getDbo()->updateObject('#__sportsmanagement_match', $row, 'id');


}
else
{
// aber das datum updaten
$row = new stdClass(); 
$row->id = $match_id;     
$row->match_date = $match->match_date;
$row->match_timestamp = sportsmanagementHelper::getTimestamp($match->match_date);
$row->division_id = $match->division_id;
$row->published = 1;
$row->import_match_id = $match->match_id;
$row->match_number = $match->match_id;
// update the object into the table.
$result = JFactory::getDbo()->updateObject('#__sportsmanagement_match', $row, 'id');
    
}
    
}    
else
{
// bei einer normalen liga und vorhandener runde
// kann das spiel angelegt werden
if ( $match->round_id && $match->projectteam1_id && $match->projectteam2_id )
{    
$rowInsert = new stdClass();    
$rowInsert->import_match_id = $match->match_id;
$rowInsert->match_number = $match->match_id;
$rowInsert->round_id = $match->round_id;
$rowInsert->projectteam1_id = $match->projectteam1_id;
$rowInsert->projectteam2_id = $match->projectteam2_id;

if ( is_numeric($match->team1_result) && is_numeric($match->team2_result) )
{ 
$rowInsert->team1_result = $match->team1_result;
$rowInsert->team2_result = $match->team2_result;
$rowInsert->team1_result_ot = $match->team1_result_ot;
$rowInsert->team2_result_ot = $match->team2_result_ot;
$rowInsert->team1_result_so = $match->team1_result_so;
$rowInsert->team2_result_so = $match->team2_result_so;
$rowInsert->match_result_type = $match->match_result_type;
}

$rowInsert->match_date = $match->match_date;
$rowInsert->match_timestamp = sportsmanagementHelper::getTimestamp($match->match_date);
$rowInsert->published = 1;

// update the object into the table.
$result = JFactory::getDbo()->insertObject('#__sportsmanagement_match', $rowInsert);
   
    
}    
    
}
    
}    



}

/**
 * sportanlagen
 */
foreach ( $exportmatches as $i => $match)
{




if ( $match->club_id_home )
{
$url_clubs = 'https://www.ishd.de'.$match->club_infosite_home.'.json';
$curl = curl_init($url_clubs);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//curl_setopt($curl, CURLOPT_HEADER, 1);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl, CURLOPT_USERPWD, $username.':'.$password );
$result = curl_exec($curl);
$code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);

// Will dump a beauty json :3
$json_object_club = json_decode($result);
$json_array = json_decode($result,true);
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result object<br><pre>'.print_r($result,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result object<br><pre>'.print_r($json_object_club,true).'</pre>'),'');

$playground_id = $json_object_club->venue->venue->id;
$playground_short_name = $json_object_club->venue->venue->name;
$playground_name = $json_object_club->venue->venue->full_name;
$playground_club_id = $json_object_club->id;
$playground_street = $json_object_club->venue->venue->address->street ;
$playground_postal_code = $json_object_club->venue->venue->address->postal_code;
$playground_city = $json_object_club->venue->venue->address->city ;

/**
 * verein mit dem spielort updaten
 */
// Create and populate an object. 
$profile = new stdClass(); 
$profile->id = $playground_club_id;
$profile->standard_playground = $playground_id;
// update the object into the table. 
$result = JFactory::getDbo()->updateObject('#__sportsmanagement_club', $profile,'id');

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result object<br><pre>'.print_r($playground_id,true).'</pre>'),'');

// Select some fields  
$query->clear();  
$query->select('id');  
// From the table  
$query->from('#__sportsmanagement_playground');  
$query->where('id = '.$playground_id );  
$db->setQuery($query);  

if ( !$db->loadResult() ) 
{
// Create and populate an object. 
$profile = new stdClass(); 
$profile->id = $playground_id; 
$profile->club_id = $playground_club_id; 
$profile->name = $playground_name; 
$profile->short_name = $playground_short_name; 
$profile->address = $playground_street; 
$profile->zipcode = $playground_postal_code; 
$profile->city = $playground_city; 
$profile->country = 'DEU';
$profile->alias = JFilterOutput::stringURLSafe( $playground_name );; 
// Insert the object into the table. 
$result = JFactory::getDbo()->insertObject('#__sportsmanagement_playground', $profile); 
}



}

}

/**
 * datum in den runden
 */
$query->clear();
$query->select('*');
$query->from('#__sportsmanagement_round');
$query->where('project_id = '.$projectid);
$db->setQuery( $query );
$rounds = $db->loadObjectList(); 
foreach( $rounds as $round )
{
$query->clear();
$query->select('min(match_date)');
$query->from('#__sportsmanagement_match ');   
$query->where('round_id = '.$round->id);
$db->setQuery( $query );
$von = $db->loadResult();
$teile = explode(" ",$von);
$von = $teile[0];

$query->clear();
$query->select('max(match_date)');
$query->from('#__sportsmanagement_match ');   
$query->where('round_id = '.$round->id);
$db->setQuery( $query );
$bis = $db->loadResult();
$teile = explode(" ",$bis);
$bis = $teile[0];

// Create an object for the record we are going to update.
$object = new stdClass();
 
// Must be a valid primary key value.
$object->id = $round->id;
$object->round_date_first = $von;
$object->round_date_last = $bis;
 
// Update their details in the users table using id as the primary key.
$result = JFactory::getDbo()->updateObject('#__sportsmanagement_round', $object, 'id');     

} 



}


/**
 * sportsmanagementModeljsminlinehockey::getMatchLink()
 * 
 * @param mixed $projectid
 * @return
 */
function getMatchLink($projectid)
{
$option = JFactory::getApplication()->input->getCmd('option');
$app = JFactory::getApplication();
$post = JFactory::getApplication()->input->post->getArray(array());

if ( $app->isAdmin() )
{ 
$view = JFactory::getApplication()->input->getVar('view');
}
else
{
$view = 'jsminlinehockey';    
}
//$project_id = $mainframe->getUserState( "$option.pid", '0' );
$db = JFactory::getDBO();
$query = $db->getQuery(true);    

$query->select('ev.fieldvalue');
$query->from('#__sportsmanagement_user_extra_fields_values as ev ');
$query->join('INNER','#__sportsmanagement_user_extra_fields as ef ON ef.id = ev.field_id');
$query->where('ev.jl_id = '.$projectid);
$query->where('ef.name LIKE '.$db->Quote(''.$view.''));
$query->where('ef.template_backend LIKE '.$db->Quote(''.'project'.''));
$query->where('ef.field_type LIKE '.$db->Quote(''.'link'.''));
$db->setQuery( $query );
$derlink  = $db->loadResult();
    
if($app ->isAdmin()) 
{
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');    
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' link -> '.$derlink.''),'Notice');
}    

return $derlink;
}

        
/**
 * sportsmanagementModeljsminlinehockey::getClubs()
 * 
 * @return void
 */
function getClubs()
{
$app = JFactory::getApplication ();
$jinput = $app->input;
$option = $jinput->getCmd('option');
$db = JFactory::getDbo();
$query = $db->getQuery(true);

$post = $jinput->post->getArray();
//$app->enqueueMessage(__METHOD__.' '.__LINE__.'post <br><pre>'.print_r($post, true).'</pre><br>','Notice');
ini_set('max_execution_time', 300);
$app->enqueueMessage(__METHOD__.' '.__LINE__.'memory_limit <br><pre>'.print_r(ini_get('memory_limit'), true).'</pre><br>','Notice');
$app->enqueueMessage(__METHOD__.' '.__LINE__.'max_execution_time <br><pre>'.print_r(ini_get('max_execution_time'), true).'</pre><br>','Notice');

$username = JComponentHelper::getParams($option)->get('ishd_benutzername');
$password = JComponentHelper::getParams($option)->get('ishd_kennwort');
$stammverein = JComponentHelper::getParams($option)->get('ishd_stammverein');
$current_season = JComponentHelper::getParams($option)->get('current_season');
//$url_clubs = 'https://www.ishd.de/api/licenses/clubs.xml';
//$url_clubs = 'https://www.ishd.de/api/licenses/clubs.json';

// Select some fields 
$query->clear(); 
$query->select('id'); 
// From the table 
$query->from('#__sportsmanagement_sports_type'); 
$query->where('name LIKE '.$db->Quote(''.'COM_SPORTSMANAGEMENT_ST_SKATER_HOCKEY'.'') ); 
$db->setQuery($query); 
$sports_type_id = $db->loadResult();
if ( !$sports_type_id ) 
{
// Create and populate an object.
$profile = new stdClass();
$profile->name = 'COM_SPORTSMANAGEMENT_ST_SKATER_HOCKEY';

// Insert the object into the user profile table.
$result = JFactory::getDbo()->insertObject('#__sportsmanagement_sports_type', $profile);
$sports_type_id = $db->insertid();
}



switch ( $post['check'] )
{
case 'clubs':
//$url_clubs = 'https://www.ishd.de/api/licenses/clubs.json';
$url_clubs = 'https://www.ishd.de/vereine.json';
$curl = curl_init($url_clubs);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//curl_setopt($curl, CURLOPT_HEADER, 1);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl, CURLOPT_USERPWD, $username.':'.$password );
$result = curl_exec($curl);
$code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);

// Will dump a beauty json :3
$json_object_clubs = json_decode($result);
$json_array = json_decode($result,true);

$seiten = $json_object_clubs->pages;
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result object<br><pre>'.print_r($json_object_clubs,true).'</pre>'),'');
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' seiten <br><pre>'.print_r($seiten ,true).'</pre>'),'');

for ($seite=1;$seite <= $seiten;$seite++)
{
$url_clubs = 'https://www.ishd.de/vereine.json?page='.$seite;
$curl = curl_init($url_clubs);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//curl_setopt($curl, CURLOPT_HEADER, 1);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl, CURLOPT_USERPWD, $username.':'.$password );
$result = curl_exec($curl);
$code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);

// Will dump a beauty json :3
$json_object_clubs = json_decode($result);
//$json_array = json_decode($result,true);
foreach( $json_object_clubs->_embedded->clubs as $key_club => $value_club )
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_id<br><pre>'.print_r($value_club->id,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_name<br><pre>'.print_r($value_club->name,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' value<br><pre>'.print_r($value_club,true).'</pre>'),'');

$club_id = $value_club->id;
$club_name = $value_club->name;

// Select some fields 
$query->clear(); 
$query->select('id'); 
// From the table 
$query->from('#__sportsmanagement_club'); 
$query->where('id = '.$club_id ); 
$db->setQuery($query); 

try {
$club_id_db = $db->loadResult();
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error

JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$temp->club_name_away, 'error');
}



if ( !$club_id_db ) 
{
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_name nicht vorhanden -> '.$club_name.''),'');
// Create and populate an object.
$profile = new stdClass();
$profile->id = $club_id;
$profile->name = $club_name;
$profile->country = 'DEU';
$profile->alias = JFilterOutput::stringURLSafe( $club_name );;
 
// Insert the object into the user profile table.
//$result = JFactory::getDbo()->insertObject('#__sportsmanagement_club', $profile);
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_name angelegt -> '.$club_name.''),'');
}
else
{
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_name vorhanden -> '.$club_name.'-'.$club_id),'Notice');
}
}
}
break;

case 'teams':
// Select some fields 
$query->clear(); 
$query->select('id'); 
// From the table 
$query->from('#__sportsmanagement_club'); 
$db->setQuery($query); 

$db_clubs = $db->loadObjectList();
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result object<br><pre>'.print_r($db_clubs,true).'</pre>'),'');

foreach( $db_clubs as $row )
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result object<br><pre>'.print_r($row->id,true).'</pre>'),'');
// ################ anfang ############
// jetzt holen wir uns die mannschaften
$url_teams = 'https://www.ishd.de/api/licenses/clubs/'.$row->id.'/teams.json';
$curl = curl_init($url_teams);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//curl_setopt($curl, CURLOPT_HEADER, 1);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl, CURLOPT_USERPWD, $username.':'.$password );
$result = curl_exec($curl);
$code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);
if ( $stammverein == $row->id  )
{
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result teams <br><pre>'.print_r($result ,true).'</pre>'),'Notice');
}
$json_object_teams = json_decode($result);
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result object<br><pre>'.print_r($json_object_teams,true).'</pre>'),'');

foreach( $json_object_teams->teams as $key_team => $value_team )
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_id<br><pre>'.print_r($value_club->club_id,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_name<br><pre>'.print_r($value_club->club_name,true).'</pre>'),'');

$team_id = $value_team->team_id;
$team = $value_team->team;
$team_name = $value_team->team_name;
$team_number = $value_team->team_number;
$team_number_roman = $value_team->team_number_roman;
$team_age_group = $value_team->team_age_group;
$team_league = $value_team->team_league;

// Select some fields 
$query->clear(); 
$query->select('id'); 
// From the table 
$query->from('#__sportsmanagement_team'); 
$query->where('id = '.$team_id ); 
$db->setQuery($query); 

if ( !$db->loadResult() ) 
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team_name nicht vorhanden -> '.$team_name.'-'.$team.'-'.$club_id),'Notice');

// Create and populate an object.
$profile = new stdClass();
$profile->id = $team_id;
$profile->club_id = $club_id;
$profile->name = $team;
$profile->short_name = $team;
$profile->middle_name = $team;
$profile->info = $team_name;
$profile->sports_type_id = $sports_type_id;
$profile->alias = JFilterOutput::stringURLSafe( $team );;
 
// Insert the object into the user profile table.
$result = JFactory::getDbo()->insertObject('#__sportsmanagement_team', $profile);

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team_name angelegt -> '.$team_id.'-'.$team_name.'-'.$team.'-'.$club_id),'Notice');

}
else
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team_name vorhanden -> '.$team_id.'-'.$team_name.'-'.$team.'-'.$club_id),'Notice');
// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $team_id;
$object->name = $team;
$object->short_name = $team;
$object->middle_name = $team;
$object->info= $team_name;
$object->sports_type_id = $sports_type_id;
$object->alias = JFilterOutput::stringURLSafe( $team );;
// Update their details in the users table using id as the primary key.
$result = JFactory::getDbo()->updateObject('#__sportsmanagement_team', $object, 'id');
	
}
}

}
break;

case 'players':
// Select some fields 
$query->clear(); 
$query->select('id,club_id'); 
// From the table 
$query->from('#__sportsmanagement_team'); 
$db->setQuery($query); 

$db_teams = $db->loadObjectList();

foreach( $db_teams as $row )
{
// ################ anfang ############
// jetzt holen wir uns die spieler
$url_players = 'https://www.ishd.de/api/licenses/clubs/'.$row->club_id.'/teams/'.$row->id.'.json';
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' url_players <br><pre>'.print_r($url_players,true).'</pre>'),'Notice');

$curl = curl_init($url_players);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//curl_setopt($curl, CURLOPT_HEADER, 1);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl, CURLOPT_USERPWD, $username.':'.$password );
$result = curl_exec($curl);
$code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);
if ( $stammverein == $row->club_id  )
{
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result teams <br><pre>'.print_r($result ,true).'</pre>'),'Notice');
}
$json_object_players = json_decode($result);

foreach( $json_object_players->players as $key_player => $value_player )
{
$player_id = $value_player->player_id;
$player_last_name = $value_player->last_name;
$player_first_name = $value_player->first_name;
$player_date_of_birth = $value_player->date_of_birth;
$player_full_face_req = $value_player->full_face_req;
$player_license_number = $value_player->license_number;
$player_remarks = $value_player->remarks;
$player_approved = $value_player->approved;
$player_nationality = $value_player->nationality;
$player_last_modifcation = $value_player->last_modifcation;


$parts = array();
// Select some fields 
$query->clear(); 
$query->select('id'); 
// From the table 
$query->from('#__sportsmanagement_person'); 
$query->where('id = '.$player_id ); 
$db->setQuery($query); 

if ( !$db->loadResult() ) 
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' spieler nicht vorhanden -> '.$player_last_name.''),'Notice');
// Create and populate an object.
$profile = new stdClass();
$profile->id = $player_id;
$profile->firstname = $player_first_name;
$profile->lastname = $player_last_name;
$profile->country = 'DEU';
$profile->birthday = $player_date_of_birth;
$profile->knvbnr = $player_license_number;
$profile->published = 1;
$parts = array( trim( $player_first_name ), trim( $player_last_name ) );
$profile->alias = JFilterOutput::stringURLSafe( implode( ' ', $parts ) );
 
// Insert the object into the user profile table.
$result = JFactory::getDbo()->insertObject('#__sportsmanagement_person', $profile);
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' spieler angelegt -> '.$player_last_name.''),'Notice');
}
else
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' spieler vorhanden -> '.$player_last_name.'-'.$player_id),'Notice');
// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $player_id;
$object->knvbnr = $player_license_number;
// Update their details in the users table using id as the primary key.
$result = JFactory::getDbo()->updateObject('#__sportsmanagement_person', $object, 'id');
}



}



}
break;
}





if ( $xml )
{
foreach( $xml->children() as $quote )  
{ 
$club_id = (string)$quote->club_id;
$club_name = (string)$quote->club_name;
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_name<br><pre>'.print_r($club_name,true).'</pre>'),'');

// Select some fields 
$query->clear(); 
$query->select('id'); 
// From the table 
$query->from('#__sportsmanagement_club'); 
$query->where('id = '.$club_id ); 
$db->setQuery($query); 
if ( !$db->loadResult() ) 
{
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_name nicht vorhanden -> '.$club_name.''),'');
// Create and populate an object.
$profile = new stdClass();
$profile->id = $club_id;
$profile->name = $club_name;
$profile->country = 'DEU';
$profile->alias = JFilterOutput::stringURLSafe( $club_name );;
 
// Insert the object into the user profile table.
$result = JFactory::getDbo()->insertObject('#__sportsmanagement_club', $profile);
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_name angelegt<br><pre>'.print_r($club_name,true).'</pre>'),'');
}
else
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_name vorhanden<br><pre>'.print_r($club_name,true).'</pre>'),'Notice');
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_name vorhanden -> '.$club_name.'-'.$club_id),'Notice');
}

// ################ anfang ############
// jetzt holen wir uns die mannschaften
$url_teams = 'https://www.ishd.de/api/licenses/clubs/'.$club_id.'/teams.xml';
$curl = curl_init($url_teams);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//curl_setopt($curl, CURLOPT_HEADER, 1);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl, CURLOPT_USERPWD, $username.':'.$password );
$result = curl_exec($curl);
$code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);

//$xml = JFactory::getXML($result,true); 
$xml_team = simplexml_load_string($result );
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' xml_team <br><pre>'.print_r($xml_team ,true).'</pre>'),'');

foreach( $xml_team->children() as $quote_team )  
{ 
$team = (string)$quote_team->team;
$team_name = (string)$quote_team->team_name;
$team_number= (string)$quote_team->team_number;
$team_number_roman = (string)$quote_team->team_number_roman;
$team_age_group = (string)$quote_team->team_age_group;
$team_league = (string)$quote_team->team_league;

// Select some fields 
$query->clear(); 
$query->select('t.id'); 
// From the table 
$query->from('#__sportsmanagement_team as t'); 
$query->join('INNER','#__sportsmanagement_club as c ON c.id = t.club_id ');
$query->where('c.id = '.$club_id ); 
$query->where('LOWER(t.name) LIKE '.$db->Quote(''.strtolower($team_name).''));
$query->where('LOWER(t.info) LIKE '.$db->Quote(''.strtolower($team).''));
$db->setQuery($query); 

if ( !$db->loadResult() ) 
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team_name nicht vorhanden<br><pre>'.print_r($team_name ,true).'</pre>'),'');
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team_name nicht vorhanden -> '.$team_name.'-'.$team.'-'.$club_id),'');

// Create and populate an object.
$profile = new stdClass();
$profile->club_id = $club_id;
$profile->name = $team_name;
$profile->info= $team;
$profile->alias = JFilterOutput::stringURLSafe( $team_name );;
 
// Insert the object into the user profile table.
$result = JFactory::getDbo()->insertObject('#__sportsmanagement_team', $profile);

$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team_name angelegt -> '.$team_name.'-'.$team.'-'.$club_id),'');


}
else
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team_name vorhanden<br><pre>'.print_r($team_name ,true).'</pre>'),'');
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team_name vorhanden -> '.$team_name.'-'.$team.'-'.$club_id),'');


if ( $stammverein == $club_id  )
{
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team spieler lesen -> '.$team_name.'-'.$team.'-'.$club_id),'');

// jetzt holen wir uns die spieler
$url_player = 'https://www.ishd.de/api/licenses/clubs/'.$club_id.'/teams/'.rawurlencode($team);
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' url_player <br><pre>'.print_r($url_player ,true).'</pre>'),'');
$curl = curl_init($url_player);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//curl_setopt($curl, CURLOPT_HEADER, 1);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl, CURLOPT_USERPWD, $username.':'.$password );
$result = curl_exec($curl);
$code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);

//$xml = JFactory::getXML($result,true); 
$xml_player = simplexml_load_string($result );
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' xml_player <br><pre>'.print_r($xml_player ,true).'</pre>'),'');


foreach( $xml_player->children() as $quote_player )  
{ 
$player_id = (string)$quote_player->player_id;
$player_last_name = (string)$quote_player->last_name;
$player_first_name = (string)$quote_player->first_name;
$player_date_of_birth = (string)$quote_player->date_of_birth;
$player_full_face_req = (string)$quote_player->full_face_req;
$player_licence_number = (string)$quote_player->licence_number;

$player_remarks = (string)$quote_player->remarks;
$player_approved = (string)$quote_player->approved;
$player_nationality = (string)$quote_player->nationality;
$player_last_modifcation = (string)$quote_player->last_modifcation;

$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team spieler -> '.$player_id.'-'.$player_last_name.'-'.$player_first_name.'-'.$player_date_of_birth),'');

$parts = array();
// Select some fields 
$query->clear(); 
$query->select('id'); 
// From the table 
$query->from('#__sportsmanagement_person'); 
$query->where('id = '.$player_id ); 
$db->setQuery($query); 

if ( !$db->loadResult() ) 
{
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' spieler nicht vorhanden -> '.$player_last_name.''),'Notice');
// Create and populate an object.
$profile = new stdClass();
$profile->id = $player_id;
$profile->firstname = $player_first_name;
$profile->lastname = $player_last_name;
$profile->country = 'DEU';
$profile->birthday = $player_date_of_birth;
$profile->knvbnr = $player_licence_number;
$profile->published = 1;
$parts = array( trim( $player_first_name ), trim( $player_last_name ) );
$profile->alias = JFilterOutput::stringURLSafe( implode( ' ', $parts ) );
 
// Insert the object into the user profile table.
$result = JFactory::getDbo()->insertObject('#__sportsmanagement_person', $profile);
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' spieler angelegt -> '.$player_last_name.''),'Notice');
}
else
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_name vorhanden<br><pre>'.print_r($club_name,true).'</pre>'),'Notice');
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' spieler vorhanden -> '.$player_last_name.'-'.$player_id),'Notice');
}






}

}


}




}
// ################ ende ############


}
}
}


function getdata()
{
    	$app = JFactory::getApplication ();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
		$document = JFactory::getDocument ();
		// Check for request forgeries
		//JFactory::getApplication()->input->checkToken () or die ( 'COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN' );
		$msg = '';
        $post = $jinput->post->getArray(array());
        
        	if (JFile::exists(JPATH_SITE.DS.'tmp'.DS.'ish_bw_import.xls'))
		{
		  echo date('H:i:s') , " Load workbook from Excel5 file" , EOL;
$callStartTime = microtime(true);
		  $objPHPExcel = PHPExcel_IOFactory::load(JPATH_SITE.DS.'tmp'.DS.'ish_bw_import.xls');
          //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' objPHPExcel<br><pre>'.print_r($objPHPExcel,true).'</pre>'),'');
          
/**
* heimmannschaft auslesen
*/          
          for($a=5;$a <= 22;$a++)
          {
            $temp = new stdClass();
            
    $temp->nummer = $objPHPExcel->getActiveSheet()->getCell('H'.$a)->getValue();
		$temp->name = $objPHPExcel->getActiveSheet()->getCell('J'.$a)->getValue();
		$temp->pass = $objPHPExcel->getActiveSheet()->getCell('N'.$a)->getValue();
		$export[] = $temp;
          //echo 'getCellValue <pre>'.print_r($objPHPExcel->getActiveSheet()->getCell('J'.$a)->getValue(),true).'</pre>';
         }
         
         $this->_datas['homeplayer'] = array_merge($export);
          
          unset($export);

/**
* gastmannschaft auslesen
*/
          
          for($a=34;$a <= 51;$a++)
          {
            $temp = new stdClass();
            
    $temp->nummer = $objPHPExcel->getActiveSheet()->getCell('H'.$a)->getValue();
		$temp->name = $objPHPExcel->getActiveSheet()->getCell('J'.$a)->getValue();
		$temp->pass = $objPHPExcel->getActiveSheet()->getCell('N'.$a)->getValue();
		$export[] = $temp;
          //echo 'getCellValue <pre>'.print_r($objPHPExcel->getActiveSheet()->getCell('J'.$a)->getValue(),true).'</pre>';
         }
          $this->_datas['awayplayer'] = array_merge($export);
          
/**
* schiedsrichter auslesen
*/          
          
          unset($export);
//          for($a=22;$a <= 31;$a + 3)
//          {
            $a=22;
            $temp = new stdClass();
            
    $temp->nummer = $objPHPExcel->getActiveSheet()->getCell('B'.$a)->getValue();
		$temp->name = $objPHPExcel->getActiveSheet()->getCell('D'.$a)->getValue();
		$temp->vorname = $objPHPExcel->getActiveSheet()->getCell('E'.$a)->getValue();
		$export[] = $temp;
            $a=25;
            $temp = new stdClass();
            
    $temp->nummer = $objPHPExcel->getActiveSheet()->getCell('B'.$a)->getValue();
		$temp->name = $objPHPExcel->getActiveSheet()->getCell('D'.$a)->getValue();
		$temp->vorname = $objPHPExcel->getActiveSheet()->getCell('E'.$a)->getValue();
		$export[] = $temp;
        
        $a=28;
            $temp = new stdClass();
            
    $temp->nummer = $objPHPExcel->getActiveSheet()->getCell('B'.$a)->getValue();
		$temp->name = $objPHPExcel->getActiveSheet()->getCell('D'.$a)->getValue();
		$temp->vorname = $objPHPExcel->getActiveSheet()->getCell('E'.$a)->getValue();
		$export[] = $temp;
        
        $a=31;
            $temp = new stdClass();
            
    $temp->nummer = $objPHPExcel->getActiveSheet()->getCell('B'.$a)->getValue();
		$temp->name = $objPHPExcel->getActiveSheet()->getCell('D'.$a)->getValue();
		$temp->vorname = $objPHPExcel->getActiveSheet()->getCell('E'.$a)->getValue();
		$export[] = $temp;
          //echo 'getCellValue <pre>'.print_r($objPHPExcel->getActiveSheet()->getCell('J'.$a)->getValue(),true).'</pre>';
//         }
         
         $this->_datas['referees'] = array_merge($export);
          
         unset($export);
          for($a=5;$a <= 25;$a++)
          {
            $temp = new stdClass();
            
    $temp->time = $objPHPExcel->getActiveSheet()->getCell('O'.$a)->getValue();
		$temp->g_nummer = $objPHPExcel->getActiveSheet()->getCell('P'.$a)->getValue();
		$temp->a_nummer = $objPHPExcel->getActiveSheet()->getCell('Q'.$a)->getValue();
        $temp->t_nummer = $objPHPExcel->getActiveSheet()->getCell('R'.$a)->getValue();
		$export[] = $temp;
          //echo 'getCellValue <pre>'.print_r($objPHPExcel->getActiveSheet()->getCell('J'.$a)->getValue(),true).'</pre>';
         }
          $this->_datas['homegoals'] = array_merge($export);
          
           unset($export);
          for($a=34;$a <= 51;$a++)
          {
            $temp = new stdClass();
            
    $temp->time = $objPHPExcel->getActiveSheet()->getCell('O'.$a)->getValue();
		$temp->g_nummer = $objPHPExcel->getActiveSheet()->getCell('P'.$a)->getValue();
		$temp->a_nummer = $objPHPExcel->getActiveSheet()->getCell('Q'.$a)->getValue();
        $temp->t_nummer = $objPHPExcel->getActiveSheet()->getCell('R'.$a)->getValue();
		$export[] = $temp;
          //echo 'getCellValue <pre>'.print_r($objPHPExcel->getActiveSheet()->getCell('J'.$a)->getValue(),true).'</pre>';
         }
          $this->_datas['awaygoals'] = array_merge($export);
          
          $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _datas<br><pre>'.print_r($this->_datas,true).'</pre>'),'');
          
          echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;


// Echo memory peak usage
echo date('H:i:s') , " Peak memory usage: " , (memory_get_peak_usage(true) / 1024 / 1024) , " MB" , EOL;

// Echo done
echo date('H:i:s') , " Done reading file" , EOL;
          
          }
    
}




}


?>
