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
$option = JFactory::getApplication()->input->getCmd('option');
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


jimport('joomla.application.component.model');
jimport('joomla.html.pane');
jimport('joomla.utilities.array');
jimport('joomla.utilities.arrayhelper') ;
// import JFile
jimport('joomla.filesystem.file');
jimport( 'joomla.utilities.utility' );
//require_once (JPATH_COMPONENT.DS.'models'.DS.'item.php');

/**
 * sportsmanagementModeljlextlmoimports
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementModeljlextlmoimports extends JModelLegacy
{
  var $_datas=array();
	var $_league_id=0;
	var $_season_id=0;
	var $_sportstype_id=0;
	var $import_version='';
  var $debug_info = false;

function __construct( )
	{
	   $option = JFactory::getApplication()->input->getCmd('option');
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
    

function checkStartExtension()
{
$option = JFactory::getApplication()->input->getCmd('option');
$app	=& JFactory::getApplication();
$user = JFactory::getUser();
$fileextension = JPATH_SITE.DS.'tmp'.DS.'lmoimport-2-0.txt';
$xmlfile = '';

if( !JFile::exists($fileextension) )
{
$to = 'diddipoeler@gmx.de';
$subject = 'LMO-Import Extension';
$message = 'LMO-Import Extension wurde auf der Seite : '.JURI::base().' gestartet.';
JUtility::sendMail( '', JURI::base(), $to, $subject, $message );

$xmlfile = $xmlfile.$message;
JFile::write($fileextension, $xmlfile);

}

}

	
function parse_ini_file_ersatz($f)
{
 $r=null;
 $sec=null;
 $f=@file($f);
 for ($i=0;$i<@count($f);$i++)
 {
  $newsec=0;
  $w=@trim($f[$i]);
  if ($w)
  {
   if ((!$r) or ($sec))
   {
   if ((@substr($w,0,1)=="[") and (@substr($w,-1,1))=="]") {$sec=@substr($w,1,@strlen($w)-2);$newsec=1;}
   }
   if (!$newsec)
   {
   $w=@explode("=",$w);$k=@trim($w[0]);unset($w[0]); $v=@trim(@implode("=",$w));
   if ((@substr($v,0,1)=="\"") and (@substr($v,-1,1)=="\"")) {$v=@substr($v,1,@strlen($v)-2);}
   if ($sec) {$r[$sec][$k]=$v;} else {$r[$k]=$v;}
   }
  }
 }
 return $r;
}


 function _getXml()
	{
		if (JFile::exists(JPATH_SITE.DS.'tmp'.DS.'joomleague_import.l98'))
		{
			if (function_exists('simplexml_load_file'))
			{
				return @simplexml_load_file(JPATH_SITE.DS.'tmp'.DS.'joomleague_import.l98','SimpleXMLElement',LIBXML_NOCDATA);
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
        	
function getData()
	{
	
/* tabellen leer machen
TRUNCATE TABLE `jos_joomleague_club`; 
TRUNCATE TABLE `jos_joomleague_team`;
TRUNCATE TABLE `jos_joomleague_person`;
TRUNCATE TABLE `jos_joomleague_playground`;
*/

	
  global $app, $option;
  $app =& JFactory::getApplication();
  $document	=& JFactory::getDocument();
  
  $lang = JFactory::getLanguage();
  $teile = explode("-",$lang->getTag());
  
  $post = JFactory::getApplication()->input->post->getArray(array());
  $country = $post['country'];
  //$country = JSMCountries::convertIso2to3($teile[1]);
  
  $app->enqueueMessage(JText::_('land '.$country.''),'');
  
  $option = JFactory::getApplication()->input->getCmd('option');
	$project = $app->getUserState( $option . 'project', 0 );
	
	$tempprovorschlag = '';
	$team2_summary = '';

if ( $this->debug_info )
{
$this->pane =& JPane::getInstance('sliders');
echo $this->pane->startPane('pane');    
}
	
	if ( $project )
	{
  // projekt wurde mitgegeben, also die liga und alles andere vorselektieren
  
  $temp = new stdClass();
  $temp->exportRoutine = '2010-09-19 23:00:00';  
  $this->_datas['exportversion'] = $temp;

// projektname
  $query = 'SELECT pro.name,pro.id
FROM #__joomleague_project as pro
WHERE pro.id = ' . (int) $project;
$this->_db->setQuery( $query );
$row = $this->_db->loadAssoc();
$tempprovorschlag = $row['name'];
$app->enqueueMessage(JText::_('project '.$tempprovorschlag.''),'');

// saisonname  
  $query = 'SELECT se.name,se.id
FROM #__joomleague_season as se
inner join #__joomleague_project as pro
on se.id = pro.season_id
WHERE pro.id = ' . (int) $project;
$this->_db->setQuery( $query );
$row = $this->_db->loadAssoc();
  
  $temp = new stdClass();
  $temp->id = $row['id'];
  $temp->name = $row['name'];
  $this->_datas['season'] = $temp;
$app->enqueueMessage(JText::_('season '.$temp->name.''),'');
$convert = array (
      $temp->name => ''
  );
$tempprovorschlag = str_replace(array_keys($convert), array_values($convert), $tempprovorschlag );


// liganame  
  $query = 'SELECT le.name,le.country,le.id
FROM #__joomleague_league as le
inner join #__joomleague_project as pro
on le.id = pro.league_id
WHERE pro.id = ' . (int) $project;
$this->_db->setQuery( $query );
$row = $this->_db->loadAssoc();

  $temp = new stdClass();
  $temp->id = $row['id'];
  $temp->name = $row['name'];
  
  if ( !$row['country'] )
  {
  $temp->country = 'DEU';
  }
  else
  {
  $temp->country = $row['country'];
  }
  
  $this->_datas['league'] = $temp;
$app->enqueueMessage(JText::_('league '.$temp->name.''),'');
  
//   $temp = new stdClass();
//   $temp->project_type = 'SIMPLE_LEAGUE';
//   $temp->namevorschlag = $tempprovorschlag;
//   $this->_datas['project'] = $temp;
 
// sporttyp  
  $query = 'SELECT st.name,st.id
FROM #__joomleague_sports_type as st
inner join #__joomleague_project as pro
on st.id = pro.sports_type_id
WHERE pro.id = ' . (int) $project;
$this->_db->setQuery( $query );
$row = $this->_db->loadAssoc();
  $temp = new stdClass();
  $temp->id = $row['id'];
  $temp->name = $row['name'];
  $this->_datas['sportstype'] = $temp;
$app->enqueueMessage(JText::_('sportstype '.$temp->name.''),'');  
  
  $temp = new stdClass();
  $this->_datas['template'] = $temp;
  
  }
  else
  {
//   $temp = new stdClass();
//   $temp->id = 1;
//   $temp->name = 'Soccer';
//   $this->_datas['sportstype'] = $temp;
  }
  
  $temp = new stdClass();
  $temp->id = 1;
  $temp->name = 'COM_SPORTSMANAGEMENT_ST_SOCCER';
  $this->_datas['sportstype'] = $temp;
    
	$lmoimportuseteams=$app->getUserState($option.'lmoimportuseteams');
  
  $teamid = 1;
  $file = JPATH_SITE.DS.'tmp'.DS.'joomleague_import.l98';

$exportplayer = array();
$exportclubs = array();
$exportteams = array();
$exportteamplayer = array();
$exportprojectteam = array();
$exportreferee = array();
$exportprojectposition = array();
$exportposition = array();
$exportparentposition = array();
$exportplayground = array();
$exportround = array();
$exportmatch = array();
$exportmatchplayer = array();
$exportmatchevent = array();
$exportevent = array();  
    /*
    echo '<pre>';
    print_r($file);
    echo '</pre>';
    */
    $parse = $this->parse_ini_file_ersatz($file);
    
// echo '<pre>';
// print_r($parse);
// echo '</pre><br>';
       
    /*
    foreach ($parseobj AS $tempobj)
		{
		echo 'options<pre>';
    print_r($tempobj);
    echo '</pre>';
		//echo 'key -> <pre>'.$key.'</pre> - value -> <pre>'.$value.'</pre><br>';
		
		echo '->Options<pre>';
    print_r($tempobj->Options);
    echo '</pre>';
    
    
    
    foreach ($tempobj->Options AS $options)
		{
		$temp = new stdClass();
    $temp->name = $options->Title;
    $this->_datas['exportversion'] = $temp;
		echo 'options->Title -> '.$options->Title.'<br>';
		
		$teile = explode(" ",$options->Name); 
    $temp = new stdClass();
    $temp->name = array_pop($teile);
    $this->_datas['season'] = $temp;
    
    $temp = new stdClass();
    $temp->name = $options->Name;
    $temp->country = 'DEU';
    $this->_datas['league'] = $temp;
    echo 'options->Name -> '.$options->Name.'<br>';
    
		}

    }
    */
    
    
    
//     $parse = parse_ini_file($file, TRUE);


    
//     $temp = new stdClass();
//     $temp->serveroffset = '00:00';
//     $this->_datas['project'] = $temp;
    
    
    // select options for the project 
    foreach ($parse['Options'] AS $key => $value)
		{
		
// 		echo 'key -> '.$key.' value ->'.$value.'<br>';
		
		if ( $key == 'Title' )
		{
    //$this->_datas['exportversion']['name'] = $value;
    $exportname = $value;
//     $temp = new stdClass();
//     $temp->name = $value;
//     $this->_datas['exportversion'] = $temp;
    }
    
		if ( $key == 'Name' )
		{
    $projectname = utf8_encode ( $value );
    //$temp->name = $value;
//     if ( !$project )
//     {   

if (array_key_exists('season',$this->_datas))
		{
    // nichts machen
    }
    else
    {
    // which season ?
    $teile = explode(" ",$value); 
    $temp = new stdClass();
    $temp->name = array_pop($teile);
    $this->_datas['season'] = $temp;
    }  

if (array_key_exists('league',$this->_datas))
		{
    // nichts machen
    }
    else
    {
    // which liga ?
    $temp = new stdClass();
    $temp->name = $value;
    if ( !$country )
    {
    $temp->country = 'DEU';
    }
    else
    {
    $temp->country = $country;
    }
    
    $this->_datas['league'] = $temp;
    }      
    
    
//     $this->_datas['project'] = $temp;
//     }
        
    }
    
    if ( $key == 'Rounds' )
		{
		$countrounds = $value;
		}
		
		if ( $key == 'Teams' )
		{
		$countlmoteams = $value;
		}
		
    if ( $key == 'Matches' )
		{
		$matchesperrounds = $value;
		}
		
		if ( $key == 'PointsForWin' )
		{
		$Points[] = $value;
		}
		if ( $key == 'PointsForDraw' )
		{
		$Points[] = $value;
		}
		if ( $key == 'PointsForLost' )
		{
		$Points[] = $value;
		}
    		
		}
    
    $temp->points_after_regular_time = implode(",",$Points);
    $temp->name = $projectname;
    $temp->namevorschlag = $tempprovorschlag;
    $temp->serveroffset = 0;
    $temp->project_type = 'SIMPLE_LEAGUE';
    $temp->start_time = '15:30';
    $temp->start_date = '0000-00-00';
    $temp->sports_type_id = 1;
    $temp->admin = 62;
    $temp->editor = 62;
    $temp->timezone = 'Europe/Amsterdam';
    
    $temp->current_round_auto = 1;
    $temp->auto_time = 1440;
    
    $temp->points_after_add_time = implode(",",$Points);
    $temp->points_after_penalty = implode(",",$Points);
    $temp->game_regular_time = 90;
    $temp->game_parts = 2;
    
    $this->_datas['project'] = $temp;

    $temp = new stdClass();
    $temp->name = $exportname;
    $this->_datas['exportversion'] = $temp;
    
    // select rounds
    unset ( $export );
    $matchnumber = 1;
    
//     echo 'parse<pre>';
//     print_r($parse);
//     echo '</pre><br>';
    
    for($a=1; $a <= $countrounds; $a++ )
    {
    $spielnummerrunde = 1;
    $lfdmatch = 1;
    
    foreach ($parse['Round'.$a] AS $key => $value)
		{
    //$lfdmatch = 1;
    
		$temp = new stdClass();
		$tempmatch = new stdClass();
//  		$temp->id = 0;
    $temp->id = $a;
		$temp->roundcode = $a;
		$temp->name = $a.'. Spieltag';
		$temp->alias = $a.'. Spieltag';
		
    if ( $key == 'D1' )
		{
		//echo $value.'<br>';
		$round_id = $a;
		$datetime = strtotime($value);
    $round_date_first = date('Y-m-d', $datetime); 
		//$round_date_first = $value;
		}
		
		if ( $key == 'D2' )
		{
		//echo $value.'<br>';
		$temp->round_date_first = $round_date_first;
		$datetime = strtotime($value);
		$temp->round_date_last = date('Y-m-d', $datetime);
		$export[] = $temp;
    $this->_datas['round'] = array_merge($export);
		}
		
		if ( substr($key, 0, 2) == 'TA' )
		{
		$projectteam1_id_lmo = $value;
		}
		
		if ( substr($key, 0, 2) == 'TB' )
		{
		$projectteam2_id_lmo = $value;
		}
		
    if ( substr($key, 0, 2) == 'GA' )
		{
		
		if ( $value != -1 )
		{
    $team1_result = $value;
    }
		else
		{
    $team1_result = '';
    }
    
		}
		
    if ( substr($key, 0, 2) == 'GB' )
		{
		
		if ( $value != -1 )
		{
    $team2_result = $value;
    }
		else
		{
    $team2_result = '';
    }
    
		}		
    
    if ( substr($key, 0, 2) == 'NT' )
		{
		$team2_summary = utf8_encode ( $value );

     if (array_key_exists( 'AT'.$lfdmatch ,$parse['Round'.$a] ))
     {
     //echo 'AT'.$lfdmatch.' existiert in der runde '.$a.'<br>';
     }
     else
     {
     //echo 'AT'.$lfdmatch.' existiert nicht in der runde '.$a.'<br>';
     
     $lfdmatch++;
     
     $tempmatch->id = $matchnumber;
     $tempmatch->round_id = $round_id;
 		 $tempmatch->round_id_lmo = $round_id;
 		 $tempmatch->match_number = $matchnumber;
 		 $tempmatch->published = 1;
 		 $tempmatch->count_result = 1;
 		 $tempmatch->show_report = 1;
 		 $tempmatch->projectteam1_id = $projectteam1_id_lmo;
 		 $tempmatch->projectteam2_id = $projectteam2_id_lmo;
 		 $tempmatch->projectteam1_id_lmo = $projectteam1_id_lmo;
 		 $tempmatch->projectteam2_id_lmo = $projectteam2_id_lmo;
 		 $tempmatch->team1_result = $team1_result;
 		 $tempmatch->team2_result = $team2_result;
 		 $tempmatch->summary = $team2_summary;
 		
     if ( $projectteam1_id_lmo )
     {
     $exportmatch[] = $tempmatch;
     } 
    
 		
      $matchnumber++;
 		
     }

		}
    
//     if (array_key_exists('AT'.$spielnummerrunde,$parse))
//     {
    if ( substr($key, 0, 2) == 'AT' )
		{
		$timestamp = $value;
// 		$datetime = strtotime($value);
// 		$mazch_date = date('Y-m-d', $datetime);
// 		$mazch_time = date('H:i', $datetime);
// 		echo 'datum -> '.$mazch_date." ".$mazch_time.'<br>';

if ( $timestamp )
{
		$tempmatch->match_date = date('Y-m-d', $timestamp)." ".date('H:i', $timestamp);
}

// 		$tempmatch->match_date = $mazch_date." ".$mazch_time;
    
    $tempmatch->id = $matchnumber;
    $tempmatch->round_id = $round_id;
		$tempmatch->round_id_lmo = $round_id;
		$tempmatch->match_number = $matchnumber;
		$tempmatch->published = 1;
		$tempmatch->count_result = 1;
		$tempmatch->show_report = 1;
		$tempmatch->projectteam1_id = $projectteam1_id_lmo;
		$tempmatch->projectteam2_id = $projectteam2_id_lmo;
		$tempmatch->projectteam1_id_lmo = $projectteam1_id_lmo;
		$tempmatch->projectteam2_id_lmo = $projectteam2_id_lmo;
		$tempmatch->team1_result = $team1_result;
		$tempmatch->team2_result = $team2_result;
		$tempmatch->summary = $team2_summary;
		if ( $projectteam1_id_lmo )
     {
    $exportmatch[] = $tempmatch;
		  }
    $matchnumber++;
		$lfdmatch++;
		
		}
//     }
    
    $spielnummerrunde++;
		
    }
    
    }
    
    $this->_datas['match'] = array_merge($exportmatch);
    
//     echo 'this->_datas<pre>';
//     print_r($this->_datas['match']);
//     echo '</pre><br>';
    
    // select clubs
    unset ( $export );
    $teamid = 1;
    foreach ($parse['Teams'] AS $key => $value)
		{
		
// der clubname muss um die mannschaftsnummer verkürzt werden
if ( substr($value, -4, 4) == ' III')
{
$convert = array (
      ' III' => ''
  );
$value = str_replace(array_keys($convert), array_values($convert), $value );
}
if ( substr($value, -3, 3) == ' II')
{
$convert = array (
      ' II' => ''
  );
$value = str_replace(array_keys($convert), array_values($convert), $value );
}
if ( substr($value, -2, 2) == ' I')
{
$convert = array (
      ' I' => ''
  );
$value = str_replace(array_keys($convert), array_values($convert), $value );
}

if ( substr($value, -2, 2) == ' 3')
{
$convert = array (
      ' 3' => ''
  );
$value = str_replace(array_keys($convert), array_values($convert), $value );
}
if ( substr($value, -2, 2) == ' 2')
{
$convert = array (
      ' 2' => ''
  );
$value = str_replace(array_keys($convert), array_values($convert), $value );
}
if ( substr($value, -3, 3) == ' 2.')
{
$convert = array (
      ' 2.' => ''
  );
$value = str_replace(array_keys($convert), array_values($convert), $value );
}

$convert = array (
      '.' => ' '
  );
$value = str_replace(array_keys($convert), array_values($convert), $value );
$value = trim($value);

    $temp = new stdClass();
    $temp->name = utf8_encode($value);
    $temp->alias = $temp->name;
    $temp->id = $teamid;
    $temp->info = '';
    $temp->extended = '';
    $temp->standard_playground = '';
    
    if ( !$country )
    {
    $temp->country = 'DEU';
    }
    else
    {
    $temp->country = $country;
    }
    
    
    foreach ($parse['Team'.$teamid] AS $key => $value)
		{
		if ( $key == 'URL' )
		{
		$temp->website = $value;
		}
		}
    
    $export[] = $temp;
    $this->_datas['club'] = array_merge($export);

		$teamid++;
		
		}
		
		// select teams
		unset ( $export );
		$teamid = 1;
    foreach ($parse['Teams'] AS $key => $value)
		{
$convert = array (
      '.' => ' '
  );
$value = str_replace(array_keys($convert), array_values($convert), $value );
$value = trim($value);

    $temp = new stdClass();
    $temp->name = utf8_encode($value);
    $temp->alias = $temp->name;
    $temp->id = $teamid;
    $temp->team_id = $teamid;
    $temp->club_id = $teamid;
    //$temp->country = $country;    
    $temp->info = '';
    $temp->extended = '';
    $temp->is_in_score = 1;
    $temp->project_team_id = $teamid;
    
    // select middle name
    if (array_key_exists('Teamm',$parse))
    {
    foreach ($parse['Teamm'] AS $keymiddle => $valuemiddle)
		{
		
    if ( $key == $keymiddle )
		{
    $temp->middle_name = utf8_encode($valuemiddle);
    }
		
		if ( empty($temp->middle_name) )
		{
    $temp->middle_name = $temp->name;
    }
    
		}
    }
    // select short name
    if (array_key_exists('Teamk',$parse))
    {
    foreach ($parse['Teamk'] AS $keyshort => $valueshort)
		{
		
		if ( $key == $keyshort )
		{
    $temp->short_name = utf8_encode($valueshort);
    }
		
    }
    }
    
    // add default middle size name
		if (empty($temp->middle_name)) {
			$parts = explode(" ", $temp->name);
			$temp->middle_name = substr($parts[0], 0, 20);
		}
	
		// add default short size name
		if (empty($temp->short_name)) {
			$parts = explode(" ", $temp->name);
			$temp->short_name = substr($parts[0], 0, 2);
		}
		
    $export[] = $temp;
    $this->_datas['team'] = array_merge($export);
    $this->_datas['projectteam'] = array_merge($export);
		$teamid++;
		
		}
    

    // check count teams lmo <-> project		
		if ( $lmoimportuseteams )
		{
    
$query = '	SELECT count(*) as total
FROM #__joomleague_project_team
WHERE project = ' . $project;

$this->_db->setQuery( $query );
$countjoomleagueteams = $this->_db->loadResult();

if (  $countlmoteams != $countjoomleagueteams  )
{
$app->enqueueMessage(JText::_('Die Anzahl der Teams im Projekt '.$project.' stimmt nicht überein!'),'Error');
}
else
{
$app->enqueueMessage(JText::_('Die Anzahl der Teams im Projekt '.$project.' stimmt überein!'),'Notice');
}
    
    }
		
		//$app->setUserState('com_joomleague'.'lmoimportxml',$this->_datas);
		
		//JFactory::getApplication()->input->setVar('lmoimportxml', $this->_datas, 'post');

// echo '<pre>';
// print_r($this->_datas);
// echo '</pre><br>';

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
$app->enqueueMessage(JText::_('project daten '.'generiert'),'');    
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setProjectData($this->_datas['project']));
}
// set league data of project
if ( isset($this->_datas['league']) )
{
$app->enqueueMessage(JText::_('league daten '.'generiert'),'');      
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setLeagueData($this->_datas['league']));
}
// set season data of project
if ( isset($this->_datas['season']) )
{
$app->enqueueMessage(JText::_('season daten '.'generiert'),'');      
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSeasonData($this->_datas['season']));
}

// set the rounds sportstype
if ( isset($this->_datas['sportstype']) )
{
$app->enqueueMessage(JText::_('sportstype daten '.'generiert'),'');      
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSportsType($this->_datas['sportstype']));    
}

// set the rounds data
if ( isset($this->_datas['round']) )
{
$app->enqueueMessage(JText::_('round daten '.'generiert'),'');      
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['round'], 'Round') );
}
// set the teams data
if ( isset($this->_datas['team']) )
{
$app->enqueueMessage(JText::_('team daten '.'generiert'),'');    
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['team'], 'JL_Team'));
}
// set the clubs data
if ( isset($this->_datas['club']) )
{
$app->enqueueMessage(JText::_('club daten '.'generiert'),'');    
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['club'], 'Club'));
}
// set the matches data
if ( isset($this->_datas['match']) )
{
$app->enqueueMessage(JText::_('match daten '.'generiert'),'');    
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


    

    
    



    

    

        

    

    





	














    
          	
function _loadData()
	{
  global $app, $option;
  $this->_data =  $app->getUserState( $option . 'project', 0 );
   
  return $this->_data;
	}

function _initData()
	{
	global $app, $option;
  $this->_data =  $app->getUserState( $option . 'project', 0 );
  return $this->_data;
	}


	
	/**
	 * _getDataFromObject
	 *
	 * Get data from object
	 *
	 * @param object $obj object where we find the key
	 * @param string $key key what we find in the object
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return void
	 */
	private function _getDataFromObject(&$obj,$key)
	{
		if (is_object($obj))
		{
			$t_array=get_object_vars($obj);

			if (array_key_exists($key,$t_array))
			{
				return $t_array[$key];
			}
			return false;
		}
		return false;
	}




	
private function _getObjectName($tableName,$id,$usedFieldName='')
	{
		$fieldName=($usedFieldName=='') ? 'name' : $usedFieldName;
		$query="SELECT $fieldName FROM #__joomleague_$tableName WHERE id=$id";
		$this->_db->setQuery($query);
		if ($result=$this->_db->loadResult()){return $result;}
		return JText::sprintf('Item with ID [%1$s] not found inside [#__joomleague_%2$s]',$id,$tableName)." $query";
	}



  








  


	private function _convertNewPlaygroundIDs()
	{
		$my_text='';
		$converted=false;
		if (isset($this->_convertPlaygroundID) && !empty($this->_convertPlaygroundID))
		{
			foreach ($this->_convertPlaygroundID AS $key => $new_pg_id)
			{
				$p_playground=$this->_getPlaygroundRecord($new_pg_id);
				foreach ($this->_convertClubID AS $key => $new_club_id)
				{
					if (isset($p_playground->club_id) && ($p_playground->club_id ==$key))
					{
						if ($this->_updatePlaygroundRecord($new_club_id,$new_pg_id))
						{
							$converted=true;
							$my_text .= '<span style="color:green">';
							$my_text .= JText::sprintf(	'Converted club-info %1$s in imported playground %2$s',
														'</span><strong>'.$this->_getClubName($new_club_id).'</strong><span style="color:green">',
														"</span><strong>$p_playground->name</strong>");
							$my_text .= '<br />';
						}
						break;
					}
				}
			}
			if (!$converted){$my_text .= '<span style="color:green">'.JText::_('Nothing needed to be converted').'<br />';}
			$this->_success_text['Converting new playground club-IDs of new playground data:']=$my_text;
		}
		return true;
	}


 



  
	/**
	 * check that all templates in default location have a corresponding record,except if project has a master template
	 *
	 */
	private function _checklist()
	{
		$project_id=$this->_project_id;
		$defaultpath=JPATH_COMPONENT_SITE.DS.'settings';
		$extensiontpath=JPATH_COMPONENT_SITE.DS.'extensions'.DS;
		$predictionTemplatePrefix='prediction';

		if (!$project_id){return;}

		// get info from project
		$query='SELECT master_template,extension FROM #__joomleague_project WHERE id='.(int)$project_id;

		$this->_db->setQuery($query);
		$params=$this->_db->loadObject();

		// if it's not a master template,do not create records.
		if ($params->master_template){return true;}

		// otherwise,compare the records with the files
		// get records
		$query='SELECT template FROM #__joomleague_template_config WHERE project_id='.(int)$project_id;

		$this->_db->setQuery($query);
		$records=$this->_db->loadResultArray();
		if (empty($records)){$records=array();}

		// first check extension template folder if template is not default
		if ((isset($params->extension)) && ($params->extension!=''))
		{
			if (is_dir($extensiontpath.$params->extension.DS.'settings'))
			{
				$xmldirs[]=$extensiontpath.$params->extension.DS.'settings';
			}
		}

		// add default folder
		$xmldirs[]=$defaultpath.DS.'default';

		// now check for all xml files in these folders
		foreach ($xmldirs as $xmldir)
		{
			if ($handle=opendir($xmldir))
			{
				/* check that each xml template has a corresponding record in the
				database for this project. If not,create the rows with default values
				from the xml file */
				while ($file=readdir($handle))
				{
					if	(	$file!='.' &&
							$file!='..' &&
							$file!='do_tipsl' &&
							strtolower(substr($file,-3))=='xml' &&
							strtolower(substr($file,0,strlen($predictionTemplatePrefix)))!=$predictionTemplatePrefix
						)
					{
						$template=substr($file,0,(strlen($file)-4));

						if ((empty($records)) || (!in_array($template,$records)))
						{
							//template not present,create a row with default values
							$params=new JLParameter(null,$xmldir.DS.$file);

							//get the values
							$defaultvalues=array();
							foreach ($params->getGroups() as $key => $group)
							{
								foreach ($params->getParams('params',$key) as $param)
								{
									$defaultvalues[]=$param[5].'='.$param[4];
								}
							}
							$defaultvalues=implode("\n",$defaultvalues);

							$query="	INSERT INTO #__joomleague_template_config (template,title,params,project_id)
													VALUES ('$template','$params->name','$defaultvalues','$project_id')";
							$this->_db->setQuery($query);
							//echo error,allows to check if there is a mistake in the template file
							if (!$this->_db->execute())
							{
								$this->setError($this->_db->getErrorMsg());
								return false;
							}
							array_push($records,$template);
						}
					}
				}
				closedir($handle);
			}
		}
	}




    




	/**
	 * _getCountryByOldid
	 *
	 * Get ISO-Code for countries to convert in old jlg import file
	 *
	 * @param object $obj object where we find the key
	 * @param string $key key what we find in the object
	 *
	 * @access public
	 * @since  1.5
	 *
	 * @return void
	 */
	/*
    public function getCountryByOldid()
	{
		$country['0']='';
		$country['1']='AFG';
		$country['2']='ALB';
		$country['3']='DZA';
		$country['4']='ASM';
		$country['5']='AND';
		$country['6']='AGO';
		$country['7']='AIA';
		$country['8']='ATA';
		$country['9']='ATG';
		$country['10']='ARG';
		$country['11']='ARM';
		$country['12']='ABW';
		$country['13']='AUS';
		$country['14']='AUT';
		$country['15']='AZE';
		$country['16']='BHS';
		$country['17']='BHR';
		$country['18']='BGD';
		$country['19']='BRB';
		$country['20']='BLR';
		$country['21']='BEL';
		$country['22']='BLZ';
		$country['23']='BEN';
		$country['24']='BMU';
		$country['25']='BTN';
		$country['26']='BOL';
		$country['27']='BIH';
		$country['28']='BWA';
		$country['29']='BVT';
		$country['30']='BRA';
		$country['31']='IOT';
		$country['32']='BRN';
		$country['33']='BGR';
		$country['34']='BFA';
		$country['35']='BDI';
		$country['36']='KHM';
		$country['37']='CMR';
		$country['38']='CAN';
		$country['39']='CPV';
		$country['40']='CYM';
		$country['41']='CAF';
		$country['42']='TCD';
		$country['43']='CHL';
		$country['44']='CHN';
		$country['45']='CXR';
		$country['46']='CCK';
		$country['47']='COL';
		$country['48']='COM';
		$country['49']='COG';
		$country['50']='COK';
		$country['51']='CRI';
		$country['52']='CIV';
		$country['53']='HRV';
		$country['54']='CUB';
		$country['55']='CYP';
		$country['56']='CZE';
		$country['57']='DNK';
		$country['58']='DJI';
		$country['59']='DMA';
		$country['60']='DOM';
		$country['61']='TMP';
		$country['62']='ECU';
		$country['63']='EGY';
		$country['64']='SLV';
		$country['65']='GNQ';
		$country['66']='ERI';
		$country['67']='EST';
		$country['68']='ETH';
		$country['69']='FLK';
		$country['70']='FRO';
		$country['71']='FJI';
		$country['72']='FIN';
		$country['73']='FRA';
		$country['74']='FXX';
		$country['75']='GUF';
		$country['76']='PYF';
		$country['77']='ATF';
		$country['78']='GAB';
		$country['79']='GMB';
		$country['80']='GEO';
		$country['81']='DEU';
		$country['82']='GHA';
		$country['83']='GIB';
		$country['84']='GRC';
		$country['85']='GRL';
		$country['86']='GRD';
		$country['87']='GLP';
		$country['88']='GUM';
		$country['89']='GTM';
		$country['90']='GIN';
		$country['91']='GNB';
		$country['92']='GUY';
		$country['93']='HTI';
		$country['94']='HMD';
		$country['95']='HND';
		$country['96']='HKG';
		$country['97']='HUN';
		$country['98']='ISL';
		$country['99']='IND';
		$country['100']='IDN';
		$country['101']='IRN';
		$country['102']='IRQ';
		$country['103']='IRL';
		$country['104']='ISR';
		$country['105']='ITA';
		$country['106']='JAM';
		$country['107']='JPN';
		$country['108']='JOR';
		$country['109']='KAZ';
		$country['110']='KEN';
		$country['111']='KIR';
		$country['112']='PRK';
		$country['113']='KOR';
		$country['114']='KWT';
		$country['115']='KGZ';
		$country['116']='LAO';
		$country['117']='LVA';
		$country['118']='LBN';
		$country['119']='LSO';
		$country['120']='LBR';
		$country['121']='LBY';
		$country['122']='LIE';
		$country['123']='LTU';
		$country['124']='LUX';
		$country['125']='MAC';
		$country['126']='MKD';
		$country['127']='MDG';
		$country['128']='MWI';
		$country['129']='MYS';
		$country['130']='MDV';
		$country['131']='MLI';
		$country['132']='MLT';
		$country['133']='MHL';
		$country['134']='MTQ';
		$country['135']='MRT';
		$country['136']='MUS';
		$country['137']='MYT';
		$country['138']='MEX';
		$country['139']='FSM';
		$country['140']='MDA';
		$country['141']='MCO';
		$country['142']='MNG';
		$country['143']='MSR';
		$country['144']='MAR';
		$country['145']='MOZ';
		$country['146']='MMR';
		$country['147']='NAM';
		$country['148']='NRU';
		$country['149']='NPL';
		$country['150']='NLD';
		$country['151']='ANT';
		$country['152']='NCL';
		$country['153']='NZL';
		$country['154']='NIC';
		$country['155']='NER';
		$country['156']='NGA';
		$country['157']='NIU';
		$country['158']='NFK';
		$country['159']='MNP';
		$country['160']='NOR';
		$country['161']='OMN';
		$country['162']='PAK';
		$country['163']='PLW';
		$country['164']='PAN';
		$country['165']='PNG';
		$country['166']='PRY';
		$country['167']='PER';
		$country['168']='PHL';
		$country['169']='PCN';
		$country['170']='POL';
		$country['171']='PRT';
		$country['172']='PRI';
		$country['173']='QAT';
		$country['174']='REU';
		$country['175']='ROM';
		$country['176']='RUS';
		$country['177']='RWA';
		$country['178']='KNA';
		$country['179']='LCA';
		$country['180']='VCT';
		$country['181']='WSM';
		$country['182']='SMR';
		$country['183']='STP';
		$country['184']='SAU';
		$country['185']='SEN';
		$country['186']='SYC';
		$country['187']='SLE';
		$country['188']='SGP';
		$country['189']='SVK';
		$country['190']='SVN';
		$country['191']='SLB';
		$country['192']='SOM';
		$country['193']='ZAF';
		$country['194']='SGS';
		$country['195']='ESP';
		$country['196']='LKA';
		$country['197']='SHN';
		$country['198']='SPM';
		$country['199']='SDN';
		$country['200']='SUR';
		$country['201']='SJM';
		$country['202']='SWZ';
		$country['203']='SWE';
		$country['204']='CHE';
		$country['205']='SYR';
		$country['206']='TWN';
		$country['207']='TJK';
		$country['208']='TZA';
		$country['209']='THA';
		$country['210']='TGO';
		$country['211']='TKL';
		$country['212']='TON';
		$country['213']='TTO';
		$country['214']='TUN';
		$country['215']='TUR';
		$country['216']='TKM';
		$country['217']='TCA';
		$country['218']='TUV';
		$country['219']='UGA';
		$country['220']='UKR';
		$country['221']='ARE';
		$country['222']='GBR';
		$country['223']='USA';
		$country['224']='UMI';
		$country['225']='URY';
		$country['226']='UZB';
		$country['227']='VUT';
		$country['228']='VAT';
		$country['229']='VEN';
		$country['230']='VNM';
		$country['231']='VGB';
		$country['232']='VIR';
		$country['233']='WLF';
		$country['234']='ESH';
		$country['235']='YEM';
		$country['238']='ZMB';
		$country['239']='ZWE';
		$country['240']='ENG';
		$country['241']='SCO';
		$country['242']='WAL';
		$country['243']='ALA';
		$country['244']='NEI';
		$country['245']='MNE';
		$country['246']='SRB';
		return $country;
	}
  	*/
}


?>

