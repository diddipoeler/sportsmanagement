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
	$maxImportMemory='350M';
}
if ((int)ini_get('memory_limit') < (int)$maxImportMemory){@ini_set('memory_limit',$maxImportMemory);}

//require_once( JPATH_COMPONENT_ADMINISTRATOR . DS. 'helpers' . DS . 'XMLParser.class.php' );
//require_once( JPATH_COMPONENT_ADMINISTRATOR . DS. 'helpers' . DS . 'crXml.php' );
require_once( JPATH_COMPONENT_ADMINISTRATOR . DS. 'helpers' . DS . 'SofeeXmlParser.php' );
//require_once( JPATH_COMPONENT_ADMINISTRATOR . DS. 'helpers' . DS . 'xml_parser.php' );
//require_once( JPATH_COMPONENT_ADMINISTRATOR . DS. 'helpers' . DS . 'parser_php5.php' );

jimport('joomla.application.component.model');
jimport('joomla.html.pane');
jimport('joomla.utilities.array');
jimport('joomla.utilities.arrayhelper') ;
// import JFile
jimport('joomla.filesystem.file');
jimport( 'joomla.utilities.utility' );
//require_once (JPATH_COMPONENT.DS.'models'.DS.'item.php');


class sportsmanagementModeljlextprofleagimport extends JModel
{
  var $_datas=array();
	var $_league_id=0;
	var $_season_id=0;
	var $_sportstype_id=0;
	var $import_version='';
  var $debug_info = false;

function __construct( )
	{
	   $option = JRequest::getCmd('option');
	$show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0);
  if ( $show_debug_info )
  {
  $this->debug_info = true;
  }
  else
  {
  $this->debug_info = false;
  }

		parent::__construct( );
	
	}

private function dump_header($text)
	{
		echo "<h1>$text</h1>";
	}

	private function dump_variable($description, $variable)
	{
		echo "<b>$description</b><pre>".print_r($variable,true)."</pre>";
	}
    



	



 function _getXml()
	{
		if (JFile::exists(JPATH_SITE.DS.'tmp'.DS.'joomleague_import.xml'))
		{
			if (function_exists('simplexml_load_file'))
			{
				return @simplexml_load_file(JPATH_SITE.DS.'tmp'.DS.'joomleague_import.xml','SimpleXMLElement',LIBXML_NOCDATA);
			}
			else
			{
				JError::raiseWarning(500,JText::_('<a href="http://php.net/manual/en/book.simplexml.php" target="_blank">SimpleXML</a> does not exist on your system!'));
			}
		}
		else
		{
			JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_LMO_ERROR','Missing import file'));
			echo "<script> alert('".JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_LMO_ERROR','Missing import file')."'); window.history.go(-1); </script>\n";
		}
	}

/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.7
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_joomleague.'.$this->name, $this->name,
				array('load_data' => $loadData) );
		if (empty($form))
		{
			return false;
		}
		return $form;
	}

/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.7
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_joomleague.edit.'.$this->name.'.data', array());
		if (empty($data))
		{
			$data = $this->getData();
		}
		return $data;
	}

function xml2assoc($xml, array &$target = array()) { 
        while ($xml->read()) { 
            switch ($xml->nodeType) { 
                case XMLReader::END_ELEMENT: 
                    return $target; 
                case XMLReader::ELEMENT: 
                    $name = $xml->name; 
                    $target[$name] = $xml->hasAttributes ? array() : ''; 
                    if (!$xml->isEmptyElement) { 
                        $target[$name] = array(); 
                        self::xml2assoc($xml, $target[$name]); 
                    } 

                    if ($xml->hasAttributes) 
                        while($xml->moveToNextAttribute()) 
                            $target[$name]['@'.$xml->name] = $xml->value; 
                    break; 
                case XMLReader::TEXT: 
                case XMLReader::CDATA: 
                    $target = $xml->value; 
            } 
        } 
        return $target; 
    } 


Function DumpStructure(&$structure,&$positions,$path)
{
    echo "[".$positions[$path]["Line"].",".$positions[$path]["Column"].",".$positions[$path]["Byte"]."]";
    if(GetType($structure[$path])=="array")
    {
        echo "&lt;".$structure[$path]["Tag"];
        if(IsSet($structure[$path]["Attributes"]))
        {
            $attributes = $structure[$path]["Attributes"];
            $ta = count($attributes);
            for(Reset($attributes), $a = 0; $a < $ta; Next($attributes), ++$a)
            {
                $attribute = Key($attributes);
                echo " ", $attribute, "=\"", HtmlSpecialChars($attributes[$attribute]), "\"";
            }
        }
        echo "&gt;";
        for($element=0;$element<$structure[$path]["Elements"];$element++)
            self::DumpStructure($structure,$positions,$path.",$element");
        echo "&lt;/".$structure[$path]["Tag"]."&gt;";
    }
    else
        echo $structure[$path];
}




        	
function getData()
	{
$option = JRequest::getCmd('option');

  $mainframe = JFactory::getApplication();
  $document	= JFactory::getDocument();
  
  $lang = JFactory::getLanguage();
  $teile = explode("-",$lang->getTag());
  
  $post = JRequest::get('post');
  $country = $post['country'];
  //$country = Countries::convertIso2to3($teile[1]);
  
  $mainframe->enqueueMessage(JText::_('land '.$country.''),'');
  
  $option = JRequest::getCmd('option');
	$project = $mainframe->getUserState( $option . 'project', 0 );
	
		
	
  
  $temp = new stdClass();
  $temp->id = 1;
  $temp->name = 'COM_SPORTSMANAGEMENT_ST_SOCCER';
  $this->_datas['sportstype'] = $temp;

$file = JPATH_SITE.DS.'tmp'.DS.'joomleague_import.xml';

    



$xml = new SofeeXmlParser(); 
$xml->parseFile($file); 
$tree = $xml->getTree(); 
unset($xml); 
$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' season<br><pre>'.print_r($tree[tournament][season][value],true).'</pre>'),'Notice');

$temp = new stdClass();
$temp->name = $tree[tournament][title][value];
$this->_datas['exportversion'] = $temp;
$temp = new stdClass();
$temp->name = $tree[tournament][season][value];
$this->_datas['season'] = $temp;
$temp = new stdClass();
$temp->name = $tree[tournament][title][value];
$this->_datas['league'] = $temp;
$temp = new stdClass();
$temp->name = $tree[tournament][title][value].' '.$tree[tournament][season][value];
$temp->project_type = 'SIMPLE_LEAGUE';
$this->_datas['project'] = $temp;

// spieler als personen anlegen
$lfdnummerperson = 1;
for($a=0; $a < sizeof($tree[tournament][player]); $a++ )
{
//echo $tree[tournament][player][$a][id].'<br>';
$temp = new stdClass();
$tempexportplayer[$tree[tournament][player][$a][id]] = $lfdnummerperson;
//$temp->id = preg_replace ( "![^0-9]+!", "", $tree[tournament][player][$a][id] );
$temp->id = $lfdnummerperson;
$temp->plid = $tree[tournament][player][$a][id];
$temp->lastname = $tree[tournament][player][$a][lastName];
$temp->firstname = $tree[tournament][player][$a][firstName];
$temp->birthday = $tree[tournament][player][$a][dateOfBirth][date][value];
$temp->country = $country;
$temp->show_persdata = 1;
$temp->show_teamdata = 1;
$temp->show_on_frontend = 1;
$exportplayer[] = $temp;
$this->_checkplayer[] = $temp;
$lfdnummerperson++;
}

// trainer als personen anlegen
for($a=0; $a < sizeof($tree[tournament][coach]); $a++ )
{
//echo $tree[tournament][player][$a][id].'<br>';
$temp = new stdClass();
$tempexportplayer[$tree[tournament][coach][$a][id]] = $lfdnummerperson;
//$temp->id = preg_replace ( "![^0-9]+!", "", $tree[tournament][player][$a][id] );
$temp->id = $lfdnummerperson;
$temp->plid = $tree[tournament][coach][$a][id];
$temp->lastname = $tree[tournament][coach][$a][lastName];
$temp->firstname = $tree[tournament][coach][$a][firstName];
$temp->birthday = $tree[tournament][coach][$a][dateOfBirth][date][value];
$temp->country = $country;
$temp->show_persdata = 1;
$temp->show_teamdata = 1;
$temp->show_on_frontend = 1;
$exportplayer[] = $temp;
$exportpositiontemp[2000] = 2000;
$lfdnummerperson++;
}

// schiedsrichter als personen anlegen
for($a=0; $a < sizeof($tree[tournament][referee]); $a++ )
{
//echo $tree[tournament][player][$a][id].'<br>';
$temp = new stdClass();
//$temp->id = preg_replace ( "![^0-9]+!", "", $tree[tournament][player][$a][id] );
$temp->id = $lfdnummerperson;
$temp->plid = $tree[tournament][referee][$a][id];
$temp->lastname = $tree[tournament][referee][$a][lastName];
$temp->firstname = $tree[tournament][referee][$a][firstName];
$temp->location = $tree[tournament][referee][$a][location];
$temp->birthday = $tree[tournament][referee][$a][dateOfBirth][date][value];
$temp->country = $country;
$temp->show_persdata = 1;
$temp->show_teamdata = 1;
$temp->show_on_frontend = 1;
$exportplayer[] = $temp;

$temp = new stdClass();
//$temp->id = preg_replace ( "![^0-9]+!", "", $tree[tournament][referee][$a][id] );
$tempexportreferee[$tree[tournament][referee][$a][id]] = $lfdnummerperson; 
$temp->id = $lfdnummerperson;
$temp->plid = $tree[tournament][referee][$a][id];
$temp->person_id = $lfdnummerperson;
$temp->project_position_id = 1000;
$temp->published = 1;
$exportreferee[] = $temp;
$exportpositiontemp[1000] = 1000;
$lfdnummerperson++;
}

// teams, teamplayer, playgrounds bearbeiten
$lfdnummerteamperson = 1;
$lfdnummerteam = 1;
for($a=0; $a < sizeof($tree[tournament][team]); $a++ )
{
$temp = new stdClass();
$temp->id = preg_replace ( "![^0-9]+!", "", $tree[tournament][team][$a][id] );
$temp->plid = $tree[tournament][team][$a][id];
$temp->name = $tree[tournament][team][$a][name];
$temp->country = $country;
$temp->standard_playground = preg_replace ( "![^0-9]+!", "", $tree[tournament][team][$a][id] );
$exportclubs[] = $temp;

$temp = new stdClass();
$temp->id = preg_replace ( "![^0-9]+!", "", $tree[tournament][team][$a][id] );
$temp->plid = $tree[tournament][team][$a][id];
$temp->club_id = preg_replace ( "![^0-9]+!", "", $tree[tournament][team][$a][id] );
$temp->name = $tree[tournament][team][$a][name];
$temp->middle_name = $tree[tournament][team][$a][name];
$temp->short_name = $tree[tournament][team][$a][name];
$exportteams[] = $temp;

$temp = new stdClass();
$temp->id = preg_replace ( "![^0-9]+!", "", $tree[tournament][team][$a][id] );
$temp->plid = $tree[tournament][team][$a][id];
$exportprojectteamtemp[$tree[tournament][team][$a][id]] = $temp->id;
$temp->team_id = preg_replace ( "![^0-9]+!", "", $tree[tournament][team][$a][id] );
$temp->project_team_id = preg_replace ( "![^0-9]+!", "", $tree[tournament][team][$a][id] );
$temp->is_in_score = 1;
$temp->standard_playground = preg_replace ( "![^0-9]+!", "", $tree[tournament][team][$a][id] );
$exportprojectteam[] = $temp;

// teamplayer
for($b=0; $b < sizeof($tree[tournament][team][$a][player]); $b++ )
{
$temp = new stdClass();
$tempexportteamplayer[$tree[tournament][team][$a][player][$b][id]] = $lfdnummerteamperson;
$temp->id= $lfdnummerteamperson;
$temp->plid= $tree[tournament][team][$a][player][$b][id];
$temp->projectteam_id = preg_replace ( "![^0-9]+!", "", $tree[tournament][team][$a][id] );
$temp->active = 1;
$temp->person_id = $tempexportplayer[$tree[tournament][team][$a][player][$b][playerRef]];
$temp->jerseynumber = $tree[tournament][team][$a][player][$b][shirtNumber];
$temp->plposition = $tree[tournament][team][$a][player][$b][posCode];

if ( !empty($tree[tournament][team][$a][player][$b][posCode]) )
{
$temp->project_position_id = $this->getProfLeagPosition($tree[tournament][team][$a][player][$b][posCode],$tree[tournament][team][$a][player][$b][playerRef]);
}
else
{
$temp->project_position_id = $this->getProfLeagPosition(900,$tree[tournament][team][$a][player][$b][playerRef]);
}

$exportteamplayer[] = $temp;

// temp player position
// temp positionen
if ( !empty($tree[tournament][team][$a][player][$b][posCode]) )
{
$exportplayerpositiontemp[(string) $tree[tournament][team][$a][player][$b][playerRef]] = (string) $tree[tournament][team][$a][player][$b][posCode];
$exportpositiontemp[(string) $tree[tournament][team][$a][player][$b][posCode] ] = (string) $tree[tournament][team][$a][player][$b][posCode];
}
else
{
$exportplayerpositiontemp[(string) $tree[tournament][team][$a][player][$b][playerRef]] = 900;
$exportpositiontemp[900] = 900;
}
// ist die position schon in der tabelle
$profleagpos = $this->getProfLeagPosition((string) $tree[tournament][team][$a][player][$b][posCode],(string) $tree[tournament][team][$a][player][$b][playerRef]);

$lfdnummerteamperson++;
}

// playground
$temp = new stdClass();
$temp->id = preg_replace ( "![^0-9]+!", "", $tree[tournament][team][$a][id] );
$temp->plid = $tree[tournament][team][$a][id];
$temp->name = $tree[tournament][team][$a][venue];
$temp->country = $country;
$temp->max_visitors = $tree[tournament][team][$a][capacity];
$exportplayground[] = $temp;

}


// jetzt die spiele
$lfdnumbermatch = 1;
$lfdnumberlineup = 1;
$lfdnumbermatchevent = 1;
$countgoals = 0;

for($a=0; $a < sizeof($tree[tournament][tournamentElement][group][groupRound]); $a++ )
{

// spieltage
$temp = new stdClass();
$temp->id = $tree[tournament][tournamentElement][group][groupRound][$a][header][shortcut];
$round_id = $tree[tournament][tournamentElement][group][groupRound][$a][header][shortcut];
$temp->roundcode = $tree[tournament][tournamentElement][group][groupRound][$a][header][shortcut];
$temp->name = $tree[tournament][tournamentElement][group][groupRound][$a][header][title];
$exportround[] = $temp;

// paarung
for($b=0; $b < sizeof($tree[tournament][tournamentElement][group][groupRound][$a][round][match]); $b++ )
{
$temp = new stdClass();
$temp->id = $lfdnumbermatch;
$temp->round_id = $round_id;
$temp->match_number = $lfdnumbermatch;
$temp->match_date = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][date] . ' ' . $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][kickoff];
$temp->summary = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][note][value];
$temp->count_result = 1;
$temp->published = 1;
$temp->show_report = 1;
$temp->projectteam1_id = preg_replace ( "![^0-9]+!", "", $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][teamRef][ref] );
if ( $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][coachRef] )
{
$tempexportteamstaff[$tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][teamRef][ref]] = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][coachRef];
}
$temp->projectteam2_id = preg_replace ( "![^0-9]+!", "", $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][teamRef][ref] );
if ( $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][coachRef] )
{
$tempexportteamstaff[$tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][teamRef][ref]] = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][coachRef];
}
$temp->team1_result = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][result][home];
$temp->team2_result = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][result][away];
$exportmatch[] = $temp;

$countgoals = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][result][home] +
              $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][result][away];


// startaufstellung oder auswechselung
// heimmannschaft
for($c=0; $c < sizeof($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry] ); $c++ )
{

// startaufstellung heimmannschaft
if ( empty( $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][type] ) )
{
$temp = new stdClass();
$temp->id = $lfdnumberlineup;
$temp->match_id = $lfdnumbermatch;
$temp->teamplayer_id = $tempexportteamplayer[(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef]];

$teile = explode("_",(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef]);
$playerposition = $exportplayerpositiontemp[$teile[1]];

if ( empty($playerposition) )
{
$playerposition = 900;
}

$temp->project_position_id = $this->getProfLeagPosition( $playerposition ,'' );

$exportmatchplayer[] = $temp;
$lfdnumberlineup++;

// hat der spieler karten bekommen ?
// gelbe karte ?
if ( $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][penalty][type][value] == 'Y' )
{
$temp = new stdClass();
$temp->id = $lfdnumbermatchevent;
$temp->match_id = $lfdnumbermatch;

$tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef];

foreach ( $exportteamplayer as $teamplayer)
{
if ( $teamplayer->plid == $tempplid )
{
$temp->projectteam_id = $teamplayer->projectteam_id;
$temp->teamplayer_id = $teamplayer->id;
}
}

$temp->event_type_id = 2;
$temp->event_sum = 1;
$exportmatchevent[] = $temp;

$lfdnumbermatchevent++;

}

// gelb/rote karte ?
if ( $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][penalty][type][value] == 'YR' )
{
$temp = new stdClass();
$temp->id = $lfdnumbermatchevent;
$temp->match_id = $lfdnumbermatch;

$tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef];

foreach ( $exportteamplayer as $teamplayer)
{
if ( $teamplayer->plid == $tempplid )
{
$temp->projectteam_id = $teamplayer->projectteam_id;
$temp->teamplayer_id = $teamplayer->id;
}
}

$temp->event_time = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][score][$d][time][min];
$temp->event_type_id = 3;
$temp->event_sum = 1;
$exportmatchevent[] = $temp;

$lfdnumbermatchevent++;

}

// rote karte ?
if ( $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][penalty][type][value] == 'R' )
{
$temp = new stdClass();
$temp->id = $lfdnumbermatchevent;
$temp->match_id = $lfdnumbermatch;

$tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef];

foreach ( $exportteamplayer as $teamplayer)
{
if ( $teamplayer->plid == $tempplid )
{
$temp->projectteam_id = $teamplayer->projectteam_id;
$temp->teamplayer_id = $teamplayer->id;
}
}

//$temp->event_time = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][score][$d][time][min];
$temp->event_type_id = 4;
$temp->event_sum = 1;
$exportmatchevent[] = $temp;

$lfdnumbermatchevent++;

}

}
else
{
// auswechselung heimmannschaft
$temp = new stdClass();
$temp->id = $lfdnumberlineup;
$temp->match_id = $lfdnumbermatch;
$temp->teamplayer_id = $tempexportteamplayer[(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef]];
$inplayer = $temp->teamplayer_id;

$teile = explode("_",(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef]);
$playerposition = $exportplayerpositiontemp[$teile[1]];

if ( empty($playerposition) )
{
$playerposition = 900;
}

$temp->project_position_id = $this->getProfLeagPosition( $playerposition ,'' );
$temp->came_in = 1;
$temp->out = 0;
$temp->in_for = $tempexportteamplayer[ (string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][substitution][substRef] ];
$outplayer = $temp->in_for;
$temp->in_out_time  = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][substitution][time][min];
$exportmatchplayer[] = $temp;
$lfdnumberlineup++;

// $temp = new stdClass();
// $temp->id = $lfdnumberlineup;
// $temp->match_id = $lfdnumbermatch;
// $temp->teamplayer_id = $outplayer;
// $teile = explode("_",(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][substitution][substRef]);
// $playerposition = $exportplayerpositiontemp[$teile[1]];
// 
// if ( empty($playerposition) )
// {
// $playerposition = 900;
// }
// 
// $temp->project_position_id = $this->getProfLeagPosition( $playerposition ,'' );
// 
// $temp->came_in = 2;
// $temp->out = 1;
// $temp->in_out_time  = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][substitution][time][min];
// $exportmatchplayer[] = $temp;
// $lfdnumberlineup++;


// hat der spieler karten bekommen ?
// gelbe karte ?
if ( $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][penalty][type][value] == 'Y' )
{
$temp = new stdClass();
$temp->id = $lfdnumbermatchevent;
$temp->match_id = $lfdnumbermatch;

$tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef];

foreach ( $exportteamplayer as $teamplayer)
{
if ( $teamplayer->plid == $tempplid )
{
$temp->projectteam_id = $teamplayer->projectteam_id;
$temp->teamplayer_id = $teamplayer->id;
}
}

$temp->event_type_id = 2;
$temp->event_sum = 1;
$exportmatchevent[] = $temp;

$lfdnumbermatchevent++;

}

// gelb/rote karte ?
if ( $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][penalty][type][value] == 'YR' )
{
$temp = new stdClass();
$temp->id = $lfdnumbermatchevent;
$temp->match_id = $lfdnumbermatch;

$tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef];

foreach ( $exportteamplayer as $teamplayer)
{
if ( $teamplayer->plid == $tempplid )
{
$temp->projectteam_id = $teamplayer->projectteam_id;
$temp->teamplayer_id = $teamplayer->id;
}
}

$temp->event_type_id = 3;
$temp->event_sum = 1;
$exportmatchevent[] = $temp;

$lfdnumbermatchevent++;

}

// rote karte ?
if ( $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][penalty][type][value] == 'R' )
{
$temp = new stdClass();
$temp->id = $lfdnumbermatchevent;
$temp->match_id = $lfdnumbermatch;

$tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef];

foreach ( $exportteamplayer as $teamplayer)
{
if ( $teamplayer->plid == $tempplid )
{
$temp->projectteam_id = $teamplayer->projectteam_id;
$temp->teamplayer_id = $teamplayer->id;
}
}

$temp->event_type_id = 4;
$temp->event_sum = 1;
$exportmatchevent[] = $temp;

$lfdnumbermatchevent++;

}

}

$lfdnumberlineup++;



}

// gastmannschaft
for($c=0; $c < sizeof($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry] ); $c++ )
{

// startaufstellung gastmannschaft
if ( empty( $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][type] ) )
{
$temp = new stdClass();
$temp->id = $lfdnumberlineup;
$temp->match_id = $lfdnumbermatch;
$temp->teamplayer_id = $tempexportteamplayer[(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef]];

$teile = explode("_",(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef]);
$playerposition = $exportplayerpositiontemp[$teile[1]];
if ( empty($playerposition) )
{
$playerposition = 900;
}
$temp->project_position_id = $this->getProfLeagPosition( $playerposition ,'' );

$exportmatchplayer[] = $temp;
$lfdnumberlineup++;

// hat der spieler karten bekommen ?
// gelbe karte ?
if ( $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][penalty][type][value] == 'Y' )
{
$temp = new stdClass();
$temp->id = $lfdnumbermatchevent;
$temp->match_id = $lfdnumbermatch;

$tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef];

foreach ( $exportteamplayer as $teamplayer)
{
if ( $teamplayer->plid == $tempplid )
{
$temp->projectteam_id = $teamplayer->projectteam_id;
$temp->teamplayer_id = $teamplayer->id;
}
}

$temp->event_type_id = 2;
$temp->event_sum = 1;
$exportmatchevent[] = $temp;

$lfdnumbermatchevent++;

}

// gelb/rote karte ?
if ( $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][penalty][type][value] == 'YR' )
{
$temp = new stdClass();
$temp->id = $lfdnumbermatchevent;
$temp->match_id = $lfdnumbermatch;

$tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef];

foreach ( $exportteamplayer as $teamplayer)
{
if ( $teamplayer->plid == $tempplid )
{
$temp->projectteam_id = $teamplayer->projectteam_id;
$temp->teamplayer_id = $teamplayer->id;
}
}


$temp->event_type_id = 3;
$temp->event_sum = 1;
$exportmatchevent[] = $temp;

$lfdnumbermatchevent++;

}

// rote karte ?
if ( $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][penalty][type][value] == 'R' )
{
$temp = new stdClass();
$temp->id = $lfdnumbermatchevent;
$temp->match_id = $lfdnumbermatch;

$tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef];

foreach ( $exportteamplayer as $teamplayer)
{
if ( $teamplayer->plid == $tempplid )
{
$temp->projectteam_id = $teamplayer->projectteam_id;
$temp->teamplayer_id = $teamplayer->id;
}
}

$temp->event_type_id = 4;
$temp->event_sum = 1;
$exportmatchevent[] = $temp;

$lfdnumbermatchevent++;

}

}
else
{
// auswechselung gastmannschaft
$temp = new stdClass();
$temp->id = $lfdnumberlineup;
$temp->match_id = $lfdnumbermatch;
$temp->teamplayer_id = $tempexportteamplayer[(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef]];
$inplayer = $temp->teamplayer_id;

$teile = explode("_",(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef]);
$playerposition = $exportplayerpositiontemp[$teile[1]];

if ( empty($playerposition) )
{
$playerposition = 900;
}

$temp->project_position_id = $this->getProfLeagPosition( $playerposition ,'' );
$temp->came_in = 1;
$temp->out = 0;
$temp->in_for = $tempexportteamplayer[ (string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][substitution][substRef] ];
$outplayer = $temp->in_for;
$temp->in_out_time  = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][substitution][time][min];
$exportmatchplayer[] = $temp;
$lfdnumberlineup++;

// $temp = new stdClass();
// $temp->id = $lfdnumberlineup;
// $temp->match_id = $lfdnumbermatch;
// $temp->teamplayer_id = $outplayer;
// $teile = explode("_",(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][substitution][substRef]);
// $playerposition = $exportplayerpositiontemp[$teile[1]];
// 
// if ( empty($playerposition) )
// {
// $playerposition = 900;
// }
// 
// $temp->project_position_id = $this->getProfLeagPosition( $playerposition ,'' );
// 
// $temp->came_in = 2;
// $temp->out = 1;
// $temp->in_out_time  = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][substitution][time][min];
// $exportmatchplayer[] = $temp;
// $lfdnumberlineup++;

// hat der spieler karten bekommen ?
// gelbe karte ?
if ( $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][penalty][type][value] == 'Y' )
{
$temp = new stdClass();
$temp->id = $lfdnumbermatchevent;
$temp->match_id = $lfdnumbermatch;

$tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef];

foreach ( $exportteamplayer as $teamplayer)
{
if ( $teamplayer->plid == $tempplid )
{
$temp->projectteam_id = $teamplayer->projectteam_id;
$temp->teamplayer_id = $teamplayer->id;
}
}

$temp->event_type_id = 2;
$temp->event_sum = 1;
$exportmatchevent[] = $temp;

$lfdnumbermatchevent++;

}

// gelb/rote karte ?
if ( $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][penalty][type][value] == 'YR' )
{
$temp = new stdClass();
$temp->id = $lfdnumbermatchevent;
$temp->match_id = $lfdnumbermatch;

$tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef];

foreach ( $exportteamplayer as $teamplayer)
{
if ( $teamplayer->plid == $tempplid )
{
$temp->projectteam_id = $teamplayer->projectteam_id;
$temp->teamplayer_id = $teamplayer->id;
}
}

$temp->event_type_id = 3;
$temp->event_sum = 1;
$exportmatchevent[] = $temp;

$lfdnumbermatchevent++;

}

// rote karte ?
if ( $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][penalty][type][value] == 'R' )
{
$temp = new stdClass();
$temp->id = $lfdnumbermatchevent;
$temp->match_id = $lfdnumbermatch;

$tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef];

foreach ( $exportteamplayer as $teamplayer)
{
if ( $teamplayer->plid == $tempplid )
{
$temp->projectteam_id = $teamplayer->projectteam_id;
$temp->teamplayer_id = $teamplayer->id;
}
}

$temp->event_type_id = 4;
$temp->event_sum = 1;
$exportmatchevent[] = $temp;

$lfdnumbermatchevent++;

}

}

$lfdnumberlineup++;

}

// torschuetzen ?
if ( $countgoals == 1 )
{
$temp = new stdClass();
$temp->id = $lfdnumbermatchevent;
$temp->match_id = $lfdnumbermatch;
$tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][score][scorerRef];

foreach ( $exportteamplayer as $teamplayer)
{
if ( $teamplayer->plid == $tempplid )
{
$temp->projectteam_id = $teamplayer->projectteam_id;
$temp->teamplayer_id = $teamplayer->id;
}
}

$temp->event_time = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][score][time][min];
$temp->event_type_id = 1;
$temp->event_sum = 1;
$exportmatchevent[] = $temp;

$lfdnumbermatchevent++;
}

if ( $countgoals > 1 )
{

for($d=0; $d < sizeof($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][score] ); $d++ )
{
$temp = new stdClass();
$temp->id = $lfdnumbermatchevent;
$temp->match_id = $lfdnumbermatch;
$tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][score][$d][scorerRef];

foreach ( $exportteamplayer as $teamplayer)
{
if ( $teamplayer->plid == $tempplid )
{
$temp->projectteam_id = $teamplayer->projectteam_id;
$temp->teamplayer_id = $teamplayer->id;
}
}

$temp->event_time = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][score][$d][time][min];
$temp->event_type_id = 1;
$temp->event_sum = 1;
$exportmatchevent[] = $temp;

$lfdnumbermatchevent++;
}

}

$countgoals = 0;
$lfdnumbermatch++;

}


}


// print "tempexportteamstaff<pre>"; 
// print_r($tempexportteamstaff); 
// print "</pre>";

foreach ( $tempexportteamstaff as $key => $value )
{
// echo $key.'-'.$value.'<br>';
$temp = new stdClass();
$temp->id = $tempexportplayer[$value];
$temp->projectteam_id = $exportprojectteamtemp[$key];
$temp->person_id = $tempexportplayer[$value];
$temp->project_position_id = 2000;
$exportteamstaff[] = $temp;


}

// print "exportmatchevent<pre>"; 
// print_r($exportmatchevent); 
// print "</pre>";

// print "exportpositiontemp<pre>"; 
// print_r($exportpositiontemp); 
// print "</pre>";
// print "exportplayerpositiontemp<pre>"; 
// print_r($exportplayerpositiontemp); 
// print "</pre>";
// print "tempexportteamplayer<pre>"; 
// print_r($tempexportteamplayer); 
// print "</pre>";
// print "tempexportplayer<pre>"; 
// print_r($tempexportplayer); 
// print "</pre>";
// print "tempexportreferee<pre>"; 
// print_r($tempexportreferee); 
// print "</pre>";


// parentposition
$temp = new stdClass();
$temp->id = 1;
$temp->name = 'Spieler';
$temp->alias = 'Spieler';
$temp->published = 1;
$exportparentposition[] = $temp;
$temp = new stdClass();
$temp->id = 2;
$temp->name = 'Trainer';
$temp->alias = 'Trainer';
$temp->published = 1;
$exportparentposition[] = $temp;
$temp = new stdClass();
$temp->id = 3;
$temp->name = 'Schiedsrichter';
$temp->alias = 'Schiedsrichter';
$temp->published = 1;
$exportparentposition[] = $temp;

// ereignisse
$temp = new stdClass();
$temp->id = 1;
$temp->name = 'Tor';
$temp->alias = 'Tor';
$temp->published = 1;
$exportevent[] = $temp;
$temp = new stdClass();
$temp->id = 2;
$temp->name = 'gelbe Karte';
$temp->alias = 'gelbe Karte';
$temp->published = 1;
$exportevent[] = $temp;
$temp = new stdClass();
$temp->id = 3;
$temp->name = 'gelb/rote Karte';
$temp->alias = 'gelb/rote Karte';
$temp->published = 1;
$exportevent[] = $temp;
$temp = new stdClass();
$temp->id = 4;
$temp->name = 'rote Karte';
$temp->alias = 'rote Karte';
$temp->published = 1;
$exportevent[] = $temp;

//$selectposition = implode(",",$exportpositiontemp);
//$exportposition = $this->getPlPosition($selectposition);

$temp = new stdClass();
$temp->id = 1000;
$temp->name = 'Spielleitung';
$temp->alias = 'Spielleitung';
$temp->parent_id = 3;
$temp->published = 1;
$temp->persontype = 3;
$exportposition[] = $temp;

$temp = new stdClass();
$temp->id = 1001;
$temp->name = 'Assistent 1';
$temp->alias = 'Assistent 1';
$temp->parent_id = 3;
$temp->published = 1;
$temp->persontype = 3;
$exportposition[] = $temp;

$temp = new stdClass();
$temp->id = 1002;
$temp->name = 'Assistent 2';
$temp->alias = 'Assistent 2';
$temp->parent_id = 3;
$temp->published = 1;
$temp->persontype = 3;
$exportposition[] = $temp;


$temp = new stdClass();
$temp->id = 1000;
$temp->position_id = 1000;
$exportprojectposition[] = $temp;
$temp = new stdClass();
$temp->id = 1001;
$temp->position_id = 1001;
$exportprojectposition[] = $temp;
$temp = new stdClass();
$temp->id = 1002;
$temp->position_id = 1002;
$exportprojectposition[] = $temp;


//$exportprojectposition = $this->getPlProjectPosition($selectposition);

foreach ($exportplayer as $row)
{
$playerposition = $exportplayerpositiontemp[ (string) $row->plid];
$row->position_id = $this->getProfLeagPosition( $playerposition ,'' );
}

//$exportpositioneventtype = $this->getPlProjectEventPosition($selectposition);

$this->_datas['person'] = array_merge($exportplayer);
$this->_datas['club'] = array_merge($exportclubs);
$this->_datas['team'] = array_merge($exportteams);
$this->_datas['teamplayer'] = array_merge($exportteamplayer);
$this->_datas['teamstaff'] = array_merge($exportteamstaff);
$this->_datas['projectteam'] = array_merge($exportprojectteam);
$this->_datas['projectreferee'] = array_merge($exportreferee);

$this->_datas['projectposition'] = array_merge($exportprojectposition);

$this->_datas['position'] = array_merge($exportposition);
//$this->_datas['positioneventtype'] = array_merge($exportpositioneventtype);
$this->_datas['parentposition'] = array_merge($exportparentposition);

$this->_datas['playground'] = array_merge($exportplayground);
$this->_datas['round'] = array_merge($exportround);
$this->_datas['match'] = array_merge($exportmatch);
$this->_datas['matchplayer'] = array_merge($exportmatchplayer);
$this->_datas['matchevent'] = array_merge($exportmatchevent);
$this->_datas['event'] = array_merge($exportevent);





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
$mainframe->enqueueMessage(JText::_('project daten '.'generiert'),'');    
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setProjectData($this->_datas['project']));
}
// set league data of project
if ( isset($this->_datas['league']) )
{
$mainframe->enqueueMessage(JText::_('league daten '.'generiert'),'');      
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setLeagueData($this->_datas['league']));
}
// set season data of project
if ( isset($this->_datas['season']) )
{
$mainframe->enqueueMessage(JText::_('season daten '.'generiert'),'');      
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSeasonData($this->_datas['season']));
}

// set the rounds sportstype
if ( isset($this->_datas['sportstype']) )
{
$mainframe->enqueueMessage(JText::_('sportstype daten '.'generiert'),'');      
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSportsType($this->_datas['sportstype']));    
}

// set the rounds data
if ( isset($this->_datas['round']) )
{
$mainframe->enqueueMessage(JText::_('round daten '.'generiert'),'');      
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['round'], 'Round') );
}
// set the teams data
if ( isset($this->_datas['team']) )
{
$mainframe->enqueueMessage(JText::_('team daten '.'generiert'),'');    
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['team'], 'JL_Team'));
}
// set the clubs data
if ( isset($this->_datas['club']) )
{
$mainframe->enqueueMessage(JText::_('club daten '.'generiert'),'');    
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['club'], 'Club'));
}
// set the matches data
if ( isset($this->_datas['match']) )
{
$mainframe->enqueueMessage(JText::_('match daten '.'generiert'),'');    
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['match'], 'Match'));
}
// set the positions data
if ( isset($this->_datas['position']) )
{
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['position'], 'Position'));
}
// set the positions parent data
if ( isset($this->_datas['parentposition']) )
{
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['parentposition'], 'ParentPosition'));
}
// set position data of project
if ( isset($this->_datas['projectposition']) )
{
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectposition'], 'ProjectPosition'));
}
// set the matchreferee data
if ( isset($this->_datas['matchreferee']) )
{
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['matchreferee'], 'MatchReferee'));
}
// set the person data
if ( isset($this->_datas['person']) )
{
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['person'], 'Person'));
}
// set the projectreferee data
if ( isset($this->_datas['projectreferee']) )
{
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectreferee'], 'ProjectReferee'));
}
// set the projectteam data
if ( isset($this->_datas['projectteam']) )
{
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectteam'], 'ProjectTeam'));
}
// set playground data of project
if ( isset($this->_datas['playground']) )
{
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['playground'], 'Playground'));
}            
            
// close the project
$output .= '</project>';
// mal als test
$xmlfile = $output;
$file = JPATH_SITE.DS.'tmp'.DS.'joomleague_import.jlg';
JFile::write($file, $xmlfile);


if ( $this->debug_info )
{
echo $this->pane->endPane();    
}
  
    $this->import_version='NEW';
    //$this->import_version='';
    return $this->_datas;
    
}

function getProfLeagPosition($val,$posplayer)
{

/**
 * Torwart - Nr. 1-15
 * Abwehr - Nr. 16-95
 * Mittelfeld - Nr. 96-175
 * Sturm - Nr. 176-255
 */

// Torwart
if ( ($val >= 1 && $val <= 15) )
{
    return 1;
}
// Abwehr
if ( ($val >= 16 && $val <= 95) )
{
    return 2;
}
// Mittelfeld
if ( ($val >= 96 && $val <= 175) )
{
    return 3;
}
// Sturm
if ( ($val >= 176 && $val <= 255) )
{
    return 4;
}
    
}    
    

    
          	
function _loadData()
	{
  global $mainframe, $option;
  $this->_data =  $mainframe->getUserState( $option . 'project', 0 );
   
  return $this->_data;
	}

function _initData()
	{
	global $mainframe, $option;
  $this->_data =  $mainframe->getUserState( $option . 'project', 0 );
  return $this->_data;
	}




}


?>

