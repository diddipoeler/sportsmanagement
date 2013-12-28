<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                jlextsisimport.php
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

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
$option = JRequest::getCmd('option');

$maxImportTime=JComponentHelper::getParams($option)->get('max_import_time',0);
if (empty($maxImportTime))
{
	$maxImportTime=480;
}
if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}

$maxImportMemory=JComponentHelper::getParams($option)->get('max_import_memory',0);
if (empty($maxImportMemory))
{
	$maxImportMemory='150M';
}
if ((int)ini_get('memory_limit') < (int)$maxImportMemory){@ini_set('memory_limit',$maxImportMemory);}


jimport( 'joomla.application.component.model' );
jimport('joomla.html.pane');

require_once( JPATH_ADMINISTRATOR . DS. 'components'.DS.$option. DS. 'helpers' . DS . 'csvhelper.php' );
require_once( JPATH_ADMINISTRATOR . DS. 'components'.DS.$option. DS. 'helpers' . DS . 'ical.php' );
require_once(JPATH_ROOT.DS.'components'.DS.$option.DS. 'helpers' . DS . 'countries.php');


// import JArrayHelper
jimport( 'joomla.utilities.array' );
jimport( 'joomla.utilities.arrayhelper' ) ;

// import JFile
jimport('joomla.filesystem.file');
jimport( 'joomla.utilities.utility' );



class sportsmanagementModeljlextsisimport extends JModel
{

var $_datas=array();
var $_league_id=0;
var $_season_id=0;
var $_sportstype_id=0;
var $import_version='';
var $debug_info = false;
var $_project_id = 0;
var $_sis_art = 1;

function getData()
	{
  //global $mainframe, $option;
  $option = JRequest::getCmd('option');
  $mainframe = JFactory::getApplication();
  $document	= JFactory::getDocument();

$exportpositioneventtype = array();  
$exportplayer = array();
$exportpersons = array();
$exportpersonstemp = array();
$exportclubs = array();
$exportclubsstandardplayground = array();
$exportplaygroundclubib = array();
$exportteams = array();
$exportteamstemp = array();
$exportteamplayer = array();
$exportprojectteam = array();
$exportprojectteams = array();
$exportreferee = array();
$exportprojectposition = array();
$exportposition = array();
$exportparentposition = array();
$exportplayground = array();
$exportplaygroundtemp = array();

$exportteamplaygroundtemp = array();

$exportround = array();
$exportmatch = array();
$exportmatchplayer = array();
$exportmatchevent = array();
$exportevent = array();  
$exportpositiontemp = array(); 

$exportposition = array();
$exportparentposition = array();
$exportprojectposition = array();

$exportmatchreferee = array();

$exportmatchplan = array();

$params = JComponentHelper::getParams( $option );
        $sis_xmllink	= $params->get( 'sis_xmllink' );
        $sis_nummer	= $params->get( 'sis_meinevereinsnummer' );
        $sis_passwort	= $params->get( 'sis_meinvereinspasswort' );
		$mainframe->enqueueMessage(JText::_('sis_xmllink<br><pre>'.print_r($sis_xmllink,true).'</pre>'   ),'');
        $mainframe->enqueueMessage(JText::_('sis_meinevereinsnummer<br><pre>'.print_r($sis_nummer,true).'</pre>'   ),'');
        $mainframe->enqueueMessage(JText::_('sis_meinvereinspasswort<br><pre>'.print_r($sis_passwort,true).'</pre>'   ),'');
        $liganummer = '001514505501506501000000000000000003000';
        
        $linkresults = self::getLink($sis_nummer,$sis_passwort,$liganummer,$this->_sis_art,$sis_xmllink);
        //$mainframe->enqueueMessage(JText::_('linkresults<br><pre>'.print_r($linkresults,true).'</pre>'   ),'');
        
        
        $linkspielplan = self::getSpielplan($linkresults,$liganummer,$this->_sis_art);
        $mainframe->enqueueMessage(JText::_('linkspielplan<br><pre>'.print_r($linkspielplan,true).'</pre>'   ),'');


  
  $temp = new stdClass();
  $temp->name = '';
  $this->_datas['season'] = $temp;

  $temp = new stdClass();
  $temp->id = 1;
  $temp->name = 'COM_SPORTSMANAGEMENT_ST_HANDBALL';
  $this->_datas['sportstype'] = $temp;

  
  
//foreach ($linkspielplan->Spielklasse as $tempklasse) 
//        {
//        $projectname = $tempklasse->Name;    
//		}

$mainframe->enqueueMessage(JText::_('linkspielplan<br><pre>'.print_r($linkspielplan->Spielklasse->Name,true).'</pre>'   ),'');

        $projectname = $linkspielplan->Spielklasse->Name[0];        
        
$temp = new stdClass();
  $temp->name = $projectname;
  $temp->exportRoutine = '2010-09-19 23:00:00';  
  $this->_datas['exportversion'] = $temp;
$temp = new stdClass();
  $temp->name = $projectname;
  $temp->alias = $projectname;
  $temp->short_name = $projectname;
  $temp->middle_name = $projectname;
  $temp->country = '';
  $this->_datas['league'] = $temp;
  
  $temp = new stdClass();
  $temp->name = $projectname;
  $temp->serveroffset = 0;
  $temp->sports_type_id = 1;
  $temp->project_type = 'SIMPLE_LEAGUE';
  $temp->current_round_auto = '2';
  $temp->auto_time = '2880';
  $temp->start_date = '';  
  $temp->start_time = '';
  $temp->game_regular_time = '60';
  $temp->game_parts = '2';
  $temp->halftime = '15';
  $temp->points_after_regular_time = '2,1,0';
  $temp->use_legs = '0';
  $temp->allow_add_time = '0';
  $temp->add_time = '30';
  $temp->points_after_add_time = '2,1,0';
  $temp->points_after_penalty = '2,1,0';
  $this->_datas['project'] = $temp;

foreach ($linkspielplan->Spiel as $tempspiel) 
{

// heimmannschaft
if (  array_key_exists($tempspiel->Heim, $exportteamstemp) ) 
{
}
else
{
$exportteamstemp[$tempspiel->Heim] = $tempspiel->HeimNr;
}

// gastmannschaft
if (  array_key_exists($tempspiel->Gast, $exportteamstemp) ) 
{
}
else
{
$exportteamstemp[$tempspiel->Gast] = $tempspiel->GastNr;
}



			
}


// teams verarbeiten
foreach ( $exportteamstemp as $key => $value )
{
// team
$temp = new stdClass();
$temp->id = $value;
$temp->club_id = $value;
$temp->name = $key;
$temp->middle_name = $key;
$temp->short_name = $key;
//$temp->info = '';
$temp->info = 'Herren';
$temp->extended = '';
$exportteams[] = $temp;

//$standard_playground = $exportteamplaygroundtemp[$key];
//$standard_playground_nummer = $exportplaygroundtemp[$standard_playground];

// club
$temp = new stdClass();
$temp->id = $value;
$temp->name = $key;
$temp->country = $country;
$temp->extended = '';
//$temp->standard_playground = $standard_playground_nummer;
$exportclubs[] = $temp;

// projektteam
$temp = new stdClass();
$temp->id = $value;
$temp->team_id = $value;
$temp->project_team_id = $value;
$temp->is_in_score = 1;
//$temp->standard_playground = $standard_playground_nummer;
$exportprojectteams[] = $temp;

}

$mainframe->enqueueMessage(JText::_('exportclubs<br><pre>'.print_r($exportclubs,true).'</pre>'   ),'');

$this->_datas['team'] = array_merge($exportteams);
$this->_datas['projectteam'] = array_merge($exportprojectteams);
$this->_datas['club'] = array_merge($exportclubs);


/**
 * das ganze für den standardimport aufbereiten
 */
$output = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
// open the project
$output .= "<project>\n";
// set the version of JoomLeague
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setJoomLeagueVersion());
// set the project datas
if ( isset($this->_datas['project']) )
{
$mainframe->enqueueMessage(JText::_('project Daten '.'generiert'),'');
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setProjectData($this->_datas['project']));
}
// set league data of project
if ( isset($this->_datas['league']) )
{
$mainframe->enqueueMessage(JText::_('league Daten '.'generiert'),'');
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setLeagueData($this->_datas['league']));
}
// set season data of project
if ( isset($this->_datas['season']) )
{
$mainframe->enqueueMessage(JText::_('season Daten '.'generiert'),'');
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSeasonData($this->_datas['season']));
}
// set season data of project
if ( isset($this->_datas['sportstype']) )
{
$mainframe->enqueueMessage(JText::_('sportstype Daten '.'generiert'),'');
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSportsType($this->_datas['sportstype']));
}
// set the rounds data
if ( isset($this->_datas['round']) )
{
$mainframe->enqueueMessage(JText::_('round Daten '.'generiert'),'');
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['round'], 'Round') );
}
// set the teams data
if ( isset($this->_datas['team']) )
{
$mainframe->enqueueMessage(JText::_('team Daten '.'generiert'),'');
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['team'], 'JL_Team'));
}
// set the clubs data
if ( isset($this->_datas['club']) )
{
$mainframe->enqueueMessage(JText::_('club Daten '.'generiert'),'');
$mainframe->enqueueMessage(JText::_('club<br><pre>'.print_r($this->_datas['club'],true).'</pre>'   ),'');
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['club'], 'Club'));
}
// set the matches data
if ( isset($this->_datas['match']) )
{
$mainframe->enqueueMessage(JText::_('match Daten '.'generiert'),'');
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['match'], 'Match'));
}
// set the positions data
if ( isset($this->_datas['position']) )
{
$mainframe->enqueueMessage(JText::_('position Daten '.'generiert'),'');
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['position'], 'Position'));
}
// set the positions parent data
if ( isset($this->_datas['parentposition']) )
{
$mainframe->enqueueMessage(JText::_('parentposition Daten '.'generiert'),'');
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['parentposition'], 'ParentPosition'));
}
// set position data of project
if ( isset($this->_datas['projectposition']) )
{
$mainframe->enqueueMessage(JText::_('projectposition Daten '.'generiert'),'');
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectposition'], 'ProjectPosition'));
}
// set the matchreferee data
if ( isset($this->_datas['matchreferee']) )
{
$mainframe->enqueueMessage(JText::_('matchreferee Daten '.'generiert'),'');
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['matchreferee'], 'MatchReferee'));
}
// set the person data
if ( isset($this->_datas['person']) )
{
$mainframe->enqueueMessage(JText::_('person Daten '.'generiert'),'');
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['person'], 'Person'));
}
// set the projectreferee data
if ( isset($this->_datas['projectreferee']) )
{
$mainframe->enqueueMessage(JText::_('projectreferee Daten '.'generiert'),'');
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectreferee'], 'ProjectReferee'));
}
// set the projectteam data
if ( isset($this->_datas['projectteam']) )
{
$mainframe->enqueueMessage(JText::_('projectteam Daten '.'generiert'),'');
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectteam'], 'ProjectTeam'));
}
// set playground data of project
if ( isset($this->_datas['playground']) )
{
$mainframe->enqueueMessage(JText::_('playground Daten '.'generiert'),'');
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['playground'], 'Playground'));
}            


            
// close the project
$output .= '</project>';

$xmlfile = $output;
$file = JPATH_SITE.DS.'tmp'.DS.'joomleague_import.jlg';
JFile::write($file, $xmlfile);



}  

	//get sis link
	function getLink($vereinsnummer,$vereinspasswort,$liganummer,$sis_art,$sis_xmllink) 
    {
		$sislink = $sis_xmllink.'/xmlexport/xml_dyn.aspx?user=%s&pass=%s&art=%s&auf=%s';
		$link = sprintf($sislink, $vereinsnummer, $vereinspasswort, $sis_art, $liganummer );	
		return $link;
	}
    
    
    //get sis spielplan
	function getSpielplan($linkresults,$liganummer,$sis_art) 
    {
        $option = JRequest::getCmd('option');
  $mainframe = JFactory::getApplication();
		// XML File
		$filepath='components/'.$option.'/sisdata/';
        
        $mainframe->enqueueMessage(JText::_('filepath<br><pre>'.print_r($filepath,true).'</pre>'   ),'');
        
		//File laden
		$datei = ($filepath.'sp_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
		if (file_exists($datei)) 
        {
			$LetzteAenderung = filemtime($datei);
			if ( (time() - $LetzteAenderung) > 1800) {
				if(file_get_contents($linkresults)) {
			 		//Laden
					$content = file_get_contents($linkresults);
					//Parsen
					$doc = DOMDocument::loadXML($content);
					//Altes File löschen
					unlink($datei);
					//Speichern
					$doc->save($filepath.'sp_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
				}
			}
		} 
        else 
        {
			//Laden
			$content = file_get_contents($linkresults);
            //$mainframe->enqueueMessage(JText::_('content<br><pre>'.print_r($content,true).'</pre>'   ),'');
            
			//Parsen
			$doc = DOMDocument::loadXML($content);
            //$mainframe->enqueueMessage(JText::_('doc<br><pre>'.print_r($doc,true).'</pre>'   ),'');
			//Speichern
			$doc->save($filepath.'sp_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
		}
		$result = simplexml_load_file($datei);
		// XML File end
		foreach ($result->Spiel as $temp) 
        {
			$nummer = substr( $temp->Liga , -3);
			$datum = substr( $temp->SpielVon , 0, 10);
			$datum_en = date("d.m.Y", strToTime($datum));
			$temp->Datum = $datum_en;
			$temp->vonUhrzeit = substr( $temp->SpielVon , 11, 8);
			$temp->bisUhrzeit = substr( $temp->SpielBis , 11, 8);
		}
		return $result;
	}
    



}

?>