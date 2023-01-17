<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlextdfbnetplayerimport
 * @file       jlextdfbnetplayerimport.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filter\OutputFilter;

$option = Factory::getApplication()->input->getCmd('option');

$maxImportTime = ComponentHelper::getParams($option)->get('max_import_time', 0);
if (empty($maxImportTime))
{
	$maxImportTime = 480;
}
if ((int) ini_get('max_execution_time') < $maxImportTime)
{
	@set_time_limit($maxImportTime);
}

$maxImportMemory = ComponentHelper::getParams($option)->get('max_import_memory', 0);
if (empty($maxImportMemory))
{
	$maxImportMemory = '150M';
}
if ((int) ini_get('memory_limit') < (int) $maxImportMemory)
{
	@ini_set('memory_limit', $maxImportMemory);
}

jimport('joomla.html.pane');

JLoader::import('components.com_sportsmanagement.models.seasons', JPATH_ADMINISTRATOR);
//JLoader::import('components.com_sportsmanagement.helpers.icaljsm', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.helpers.countries', JPATH_SITE);
//JLoader::register('icaljsm', 'administrator/components/com_sportsmanagement' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'icaljsm.php');


// Derive the class name from the driver.
$class_name = 'icaljsm';
$class_file = JPATH_COMPONENT_ADMINISTRATOR . '/helpers/' . 'icaljsm.php';
// Require the driver file
     if (File::exists($class_file)) {
         JLoader::register($class_name, $class_file);
       //throw new RuntimeException(sprintf('Driver not load: %s', $class_file));
     }

// If the class still doesn't exist we have nothing left to do but throw an exception.  We did our best.
     if (!class_exists($class_name)) {
         throw new RuntimeException(sprintf('Driver not load: %s', $class_name));
     }

// Derive the class name from the driver.
$class_name = 'Event';
$class_file = JPATH_COMPONENT_ADMINISTRATOR . '/helpers/' . 'Event.php';
// Require the driver file
     if (File::exists($class_file)) {
         JLoader::register($class_name, $class_file);
       //throw new RuntimeException(sprintf('Driver not load: %s', $class_file));
     }

// If the class still doesn't exist we have nothing left to do but throw an exception.  We did our best.
     if (!class_exists($class_name)) {
         throw new RuntimeException(sprintf('Driver not load: %s', $class_name));
     }















jimport('joomla.utilities.utility');


/**
 * sportsmanagementModeljlextdfbnetplayerimport
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class sportsmanagementModeljlextdfbnetplayerimport extends BaseDatabaseModel
{

	var $_datas = array();
	var $_league_id = 0;
	var $_season_id = 0;
	var $_sportstype_id = 0;
	var $import_version = '';
	var $debug_info = false;
	var $_project_id = 0;

	/**
	 * sportsmanagementModeljlextdfbnetplayerimport::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		$option          = Factory::getApplication()->input->getCmd('option');
		$show_debug_info = ComponentHelper::getParams($option)->get('show_debug_info', 0);
		if ($show_debug_info)
		{
			$this->debug_info = true;
		}
		else
		{
			$this->debug_info = false;
		}

		parent::__construct();

	}

	/**
	 * sportsmanagementModeljlextdfbnetplayerimport::multisort()
	 *
	 * @param   mixed  $array
	 * @param   mixed  $sort_by
	 * @param   mixed  $key1
	 * @param   mixed  $key2
	 * @param   mixed  $key3
	 * @param   mixed  $key4
	 * @param   mixed  $key5
	 * @param   mixed  $key6
	 *
	 * @return
	 */
	function multisort($array, $sort_by, $key1, $key2 = null, $key3 = null, $key4 = null, $key5 = null, $key6 = null)
	{
		// usage (only enter the keys you want sorted):
		// $sorted = multisort($array,'year','name','phone','address');
		// sort by ?
		foreach ($array as $pos => $val)
		{
			$tmp_array[$pos] = $val[$sort_by];
		}
		asort($tmp_array);

		// display however you want
		foreach ($tmp_array as $pos => $val)
		{
			$return_array[$pos][$sort_by] = $array[$pos][$sort_by];
			$return_array[$pos][$key1]    = $array[$pos][$key1];
			if (isset($key2))
			{
				$return_array[$pos][$key2] = $array[$pos][$key2];
			}
			if (isset($key3))
			{
				$return_array[$pos][$key3] = $array[$pos][$key3];
			}
			if (isset($key4))
			{
				$return_array[$pos][$key4] = $array[$pos][$key4];
			}
			if (isset($key5))
			{
				$return_array[$pos][$key5] = $array[$pos][$key5];
			}
			if (isset($key6))
			{
				$return_array[$pos][$key6] = $array[$pos][$key6];
			}
		}

		return $return_array;
	}


	/**
	 * sportsmanagementModeljlextdfbnetplayerimport::super_unique()
	 *
	 * @param   mixed  $array
	 *
	 * @return
	 */
	function super_unique($array)
	{
		$result = array_map("unserialize", array_unique(array_map("serialize", $array)));

		foreach ($result as $key => $value)
		{
			if (is_array($value))
			{
				$result[$key] = $this->super_unique($value);
			}
		}

		return $result;
	}

	/**
	 * sportsmanagementModeljlextdfbnetplayerimport::property_value_in_array()
	 *
	 * @param   mixed  $array
	 * @param   mixed  $property
	 * @param   mixed  $value
	 *
	 * @return
	 */
	function property_value_in_array($array, $property, $value)
	{
		$flag = false;

		foreach ($array[0] as $object)
		{

			if ($object->$property == $value)
			{
				$flag = true;
			}
			else
			{
				$flag = false;
			}
		}

		return $flag;
	}


	/**
	 * sportsmanagementModeljlextdfbnetplayerimport::getUpdateData()
	 *
	 * @return
	 */
	function getUpdateData()
	{
		$option   = Factory::getApplication()->input->getCmd('option');
		$app      = Factory::getApplication();
		$document = Factory::getDocument();
		$lang                = Factory::getLanguage();
		$this->_success_text = '';
		$my_text             = '';
$post = Factory::getApplication()->input->post->getArray(array());
		$country = $post['filter_nation'];
		//$country = "DEU"; // DFBNet gibt es nur in D, also ist die eingestellte Joomla Sprache nicht relevant

		$project = $app->getUserState("$option.pid", '0');

		if (!$project)
		{
			$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_NO_PROJECT'), 'Error');
		}
		else
		{

			$this->getData();

			$updatedata = $this->getProjectUpdateData($this->_datas['match'], $project);

			foreach ($updatedata as $row)
			{
				$mdl     = BaseDatabaseModel::getInstance("match", "sportsmanagementModel");
				$p_match = $mdl->getTable();


				// paarung ist nicht vorhanden ?
				if (!$row->id)
				{
					// sicherheitshalber nachschauen ob die paarung schon da ist
					$query = "SELECT ma.id
from #__sportsmanagement_match as ma
where ma.round_id = '$row->round_id'
and ma.projectteam1_id = '$row->projectteam1_id'
and ma.projectteam2_id = '$row->projectteam2_id'
";
					$this->_db->setQuery($query);
					$tempid = $this->_db->loadResult();

					if ($tempid)
					{
						$p_match->set('id', $tempid);
					}
					else
					{
						$p_match->set('round_id', $row->round_id);
					}

				}
				else
				{
					$p_match->set('id', $row->id);
				}

				// spiel wurde verlegt
				if ($row->match_date_verlegt)
				{
					$p_match->set('match_date', $row->match_date_verlegt);
				}
				else
				{
					$p_match->set('match_date', $row->match_date);
				}


				$p_match->set('published', $row->published);
				$p_match->set('count_result', $row->count_result);
				$p_match->set('show_report', $row->show_report);
				$p_match->set('summary', $row->summary);

				$p_match->set('projectteam1_id', $row->projectteam1_id);
				$p_match->set('projectteam2_id', $row->projectteam2_id);
				$p_match->set('match_number', $row->match_number);


				if (is_numeric($row->team1_result) && is_numeric($row->team2_result)
					&& isset($row->team1_result) && isset($row->team2_result)
				)
				{
					$my_text                                                                    .= '<span style="color:blue">';
					$my_text                                                                    .= Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_UPDATE_MATCH_RESULT_YES');
					$my_text                                                                    .= '</span><br />';
					$this->_success_text['COM_SPORTSMANAGEMENT_ADMIN_DFBNET_UPDATE_MATCH_DATA'] = $my_text;
				}
				else
				{
					$my_text                                                                    .= '<span style="color:red">';
					$my_text                                                                    .= Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_UPDATE_MATCH_RESULT_NO');
					$my_text                                                                    .= '</span><br />';
					$this->_success_text['COM_SPORTSMANAGEMENT_ADMIN_DFBNET_UPDATE_MATCH_DATA'] = $my_text;
				}

				if ($p_match->store() === false)
				{
					$my_text                                                                    .= 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_UPDATE_MATCH_DATA_ERROR';
					$my_text                                                                    .= $row->match_number;
					$this->_success_text['COM_SPORTSMANAGEMENT_ADMIN_DFBNET_UPDATE_MATCH_DATA'] = $my_text;

					return false;
				}
				else
				{
					$my_text .= '<span style="color:green">';
					$my_text .= Text::sprintf(
						'Update Spielnummer: %1$s / Paarung: %2$s - %3$s',
						'</span><strong>' . $row->match_number . '</strong><span style="color:green">',
						"</span><strong>$row->projectteam1_dfbnet</strong>",
						"<strong>$row->projectteam2_dfbnet</strong>"
					);
					$my_text .= '<br />';

					$this->_success_text['COM_SPORTSMANAGEMENT_ADMIN_DFBNET_UPDATE_MATCH_DATA'] = $my_text;

				}

			}


		}

		$this->_SetRoundDates($project);

		return $this->_success_text;
	}

	/**
	 * sportsmanagementModeljlextdfbnetplayerimport::getData()
	 *
	 * @return
	 */
	function getData()
	{
		$option   = Factory::getApplication()->input->getCmd('option');
		$app      = Factory::getApplication();
		$document = Factory::getDocument();
		$post = Factory::getApplication()->input->post->getArray(array());

		//$country = "DEU"; // DFBNet gibt es nur in D, also ist die eingestellte Joomla Sprache nicht relevant
		$project = $app->getUserState("$option.pid", '0');
		
		$country = $post['filter_nation'];
      $season_id = $post['filter_season'];

		$whichfile = $app->getUserState($option . 'whichfile');

		$app->enqueueMessage(Text::_('Welches Land? ' . $country), '');
		$app->enqueueMessage(Text::_('Welche Art von Datei? ' . $whichfile), '');

		

		$this->_league_new_country = $country;

		$exportpositioneventtype       = array();
		$exportplayer                  = array();
		$exportpersons                 = array();
		$exportpersonstemp             = array();
		$exportclubs                   = array();
		$exportclubsstandardplayground = array();
		$exportplaygroundclubib        = array();
		$exportteams                   = array();
		$exportteamstemp               = array();
		$exportteamplayer              = array();
		$exportprojectteam             = array();
		$exportprojectteams            = array();
		$exportreferee                 = array();
		$exportprojectposition         = array();
		$exportposition                = array();
		$exportparentposition          = array();
		$exportplayground              = array();
		$exportplaygroundtemp          = array();

		$exportteamplaygroundtemp = array();

		$exportround        = array();
		$exportmatch        = array();
		$exportmatchplayer  = array();
		$exportmatchevent   = array();
		$exportevent        = array();
		$exportpositiontemp = array();

		$exportposition        = array();
		$exportparentposition  = array();
		$exportprojectposition = array();

		$exportmatchreferee = array();

		$exportmatchplan = array();

		$temp_match_number = array();

		$startline = 0;

		if (isset($post['projects']))
		{
			$this->_project_id = $post['projects'];
		}

		$file = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'sportsmanagement_import.csv';
		$app->enqueueMessage(Text::_('Datei? ' . $file), '');

		if ($whichfile == 'playerfile')
		{

			/*
			* ##### structure of playerfile #####
			* Passnr.;
			* Name;
			* Vorname;
			* Altersklasse;
			* Geburtsdatum;
			* Spielrecht Pflicht / Verband;
			* Spielrecht Freundschaft / Privat;
			* Abmeldung;
			* Spielerstatus;
			* Gast/Zweitspielrecht;
			* Spielzeit;
			* Spielart;
			* Passdruck;
			* Einsetzbar;
			* Stammverein
			*/
			$startline = 9;

		}
		elseif ($whichfile == 'matchfile')
		{

			/*
			* ##### structure of matchfile #####
			* Datum;0
			* Zeit;1
			* Saison;2
			* Verband;3
			* Mannschaftsart_Key;4
			* Mannschaftsart;5
			* Spielklasse_Key;6
			* Spielklasse;7
			* Spielgebiet_Key;8
			* Spielgebiet;9
			* Rahmenspielplan;10
			* Staffel_Nr;11
			* Staffel;12
			* Staffelkennung;13
			* Staffelleiter;14
			* Spieldatum;15
			* Uhrzeit;16
			* Wochentag;17
			* Spieltag;18
			* Schlüsseltag;19
			* Spielkennung;22
			* Heimmannschaft;20
			* Gastmannschaft;21
			* freigegeben;23
			* Spielstätte;24
			* Spielleitung;25
			* Assistent 1;26
			* Assistent 2;27
			* verlegtWochentag;28
			* verlegtSpieldatum;29
			* verlegtUhrzeit;30
			*/
			$startline = 1;

		}
		elseif ($whichfile == 'icsfile')
		{
			/**
			 * kalender file vom bfv anfang
			 */
          /*
			$ical = new ical();
			$ical->parse($file);

			$icsfile = $ical->get_all_data();
*/
			//
			$lfdnumber           = 0;
			$lfdnumberteam       = 1;
			$lfdnumbermatch      = 1;
			$lfdnumberplayground = 1;
          
          //$ical = new icaljsm;
          
          $ical = new icaljsm($file, array(
        'defaultSpan'                 => 2,     // Default value
        'defaultTimeZone'             => 'UTC',
        'defaultWeekStart'            => 'MO',  // Default value
        'disableCharacterReplacement' => false, // Default value
        'filterDaysAfter'             => null,  // Default value
        'filterDaysBefore'            => null,  // Default value
        'skipRecurrence'              => false, // Default value
    ));
          
//$app->enqueueMessage(__LINE__.'<pre>'.print_r($ical,true).'</pre>', '');
          //$events = $ical->eventsFromInterval('1 week');
			$events = $ical->events();
//$app->enqueueMessage(__LINE__.'<pre>'.print_r($events,true).'</pre>', '');          
          $convert = array(
							'Abgesagt -'    => '',
            '('    => '#',
            ')'    => '#'
						);
          
          for ($a = 0; $a < sizeof($events); $a++)
			{
		  
//$timestamp = $events[$a]->dtstart_array[2] - 7200;  
$timestamp = $events[$a]->dtstart_array[2];
$matchdate = date('Y-m-d', $timestamp) . " ".date('H:i:s', $timestamp);                        
$exportmatchplan[$events[$a]->uid]['match_date'] = $matchdate;            

$spieltag = $events[$a]->description;            
preg_match_all("/\((.*?)\)/", $spieltag, $treffer, PREG_SET_ORDER);
//$app->enqueueMessage(__LINE__.'<pre>'.print_r($treffer,true).'</pre>', ''); 
$exportmatchplan[$events[$a]->uid]['spieltag'] = $treffer[0][1];            
$nr = preg_replace('![^0-9]!', '', $treffer[0][1]);    
            
            if ( $country == 'AUT' )
            {
$exportmatchplan[$events[$a]->uid]['round_id'] = $nr;            
$temp                   = new stdClass();
$temp->id               = $nr;
$temp->roundcode        = $nr;
$temp->name             = $treffer[0][1];
$temp->alias            = OutputFilter::stringURLSafe($treffer[0][1]);
$temp->round_date_first = '';
$temp->round_date_last  = '';
$exportround[$nr]        = $temp;
            }
            
            $teile  = explode(":", $events[$a]->summary);
            if ( preg_match("/Abgesagt -/i", $teile[0])	)
						{
							$teile[0] = str_replace(array_keys($convert), array_values($convert), $teile[0]);
							//$teile2   = explode(":", $teile[0]);
						}
            
            $exportmatchplan[$events[$a]->uid]['heim'] = trim($teile[0]);
            $exportmatchplan[$events[$a]->uid]['gast'] = trim($teile[1]);
            
            $exportmatchplan[$events[$a]->uid]['heimnummer'] = $events[$a]->x_homenr;
            $exportmatchplan[$events[$a]->uid]['gastnummer'] = $events[$a]->x_awaynr;
            
            //$exportmatchplan[$events[$a]->uid]['spieltag'] = $events[$a]->description;
            
            
            $events[$a]->location = str_replace(array_keys($convert), array_values($convert), $events[$a]->location);
            $teilelocation  = explode("#", $events[$a]->location);
            
            //$app->enqueueMessage(__LINE__.'<pre>'.print_r($teilelocation,true).'</pre>', '');
            $teileadresse  = explode(",", $teilelocation[1]  );
            //$app->enqueueMessage(__LINE__.'<pre>'.print_r($teileadresse,true).'</pre>', '');
            $address = $teileadresse[0];
            $zipcode = substr($teileadresse[1],0,5);
            $city = substr($teileadresse[1],6,50);
            
            $exportmatchplan[$events[$a]->uid]['playground'] = $teilelocation[0];
            
            
            
            // heimmannschaft
            $valueheim = trim($teile[0]);
            $valueplayground = $teilelocation[0];
					if (array_key_exists(trim($teile[0]), $exportteamstemp))
					{
					}
					else
					{
						$exportteamstemp[trim($teile[0])] = $lfdnumberteam;
						$lfdnumberteam++;
					}

					// gastmannschaft
					if (array_key_exists(trim($teile[1]), $exportteamstemp))
					{
					}
					else
					{
						$exportteamstemp[trim($teile[1])] = $lfdnumberteam;
						$lfdnumberteam++;
					}
            
            
            $exportteamplaygroundtemp[$valueheim] = $valueplayground;

						if (array_key_exists($valueplayground, $exportplaygroundtemp))
						{
						}
						else
						{
							$exportplaygroundtemp[$valueplayground] = $lfdnumberplayground;

							$temp               = new stdClass();
							$temp->id           = $lfdnumberplayground;
							$temp->name         = $valueplayground;
							$temp->short_name   = $valueplayground;
							$temp->alias        = $valueplayground;
							$temp->club_id      = $exportteamstemp[$valueheim];
							$temp->address      = $address;
							$temp->zipcode      = $zipcode;
							$temp->city         = $city;
							$temp->country      = $country;
							$temp->max_visitors = 0;
							$exportplayground[] = $temp;

							$lfdnumberplayground++;
						}
            
            
            
            
            
            if (empty($lfdnumber))
					{
$projectname = $events[$a]->description;
						$temp                          = new stdClass();
						$temp->name                    = $projectname;
						$temp->exportRoutine           = '2010-09-19 23:00:00';
						$this->_datas['exportversion'] = $temp;

						$temp                   = new stdClass();
		    $temp->id = $season_id;
		    $mdl          = BaseDatabaseModel::getInstance('seasons', 'sportsmanagementModel');
				$temp->name = $mdl->getSeasonName($season_id);
						//$temp->name             = '';
						$this->_datas['season'] = $temp;

						$temp                       = new stdClass();
						$temp->id                   = 1;
						$temp->name                 = 'COM_SPORTSMANAGEMENT_ST_SOCCER';
						$this->_datas['sportstype'] = $temp;
              
              $temp                   = new stdClass();
						$temp->name             = $projectname;
						$temp->alias            = $projectname;
						$temp->short_name       = $projectname;
						$temp->middle_name      = $projectname;
						$temp->country          = $country;
						$this->_datas['league'] = $temp;

						$temp               = new stdClass();
						$temp->name         = $projectname;
						$temp->serveroffset = 0;
						$temp->project_type = 'SIMPLE_LEAGUE';

						$temp->current_round_auto        = '2';
						$temp->auto_time                 = '2880';
						$temp->start_date                = '2013-08-08';
						$temp->start_time                = '15:30';
						$temp->game_regular_time         = '90';
						$temp->game_parts                = '2';
						$temp->halftime                  = '15';
						$temp->points_after_regular_time = '3,1,0';
						$temp->use_legs                  = '0';
						$temp->allow_add_time            = '0';
						$temp->add_time                  = '30';
						$temp->points_after_add_time     = '3,1,0';
						$temp->points_after_penalty      = '3,1,0';
              $temp->season_id = $season_id;

						$this->_datas['project'] = $temp;

              
            }
            $lfdnumber++;
					$lfdnumbermatch++;
          }
          
//$app->enqueueMessage(__LINE__.'<pre>'.print_r($exportmatchplan,true).'</pre>', '');                    
//$app->enqueueMessage(__LINE__.'<pre>'.print_r($exportround,true).'</pre>', '');          
          
			for ($a = 0; $a < sizeof($icsfile['VEVENT']); $a++)
			{
				// Mannschaften, die spielfrei haben werden in der ics Datei "mit fehlender Gastmannschaft", z.B.
				// SUMMARY:FC Kempten-\, BZL Schwaben Süd
				// bzw. "mit fehlender Heimmannschaft" ausgegeben, z.B.
				// SUMMARY:-SVO Germaringen\, BZL Schwaben Süd
				// Diese VEVENT Blöcke sollen ausgeschlossen werden
				if ((!strstr($icsfile['VEVENT'][$a]['SUMMARY'], "-\,")) && (!strpos($icsfile['VEVENT'][$a]['SUMMARY'], "-") == "0"))
				{
					$icsfile['VEVENT'][$a]['UID']                                 = $lfdnumbermatch;
					$exportmatchplan[$icsfile['VEVENT'][$a]['UID']]['match_date'] = date('Y-m-d', $icsfile['VEVENT'][$a]['DTSTART']) . " " . date('H:i', $icsfile['VEVENT'][$a]['DTSTART']);

					// Paarung
					$teile  = explode("\,", $icsfile['VEVENT'][$a]['SUMMARY']);
					$teile2 = explode("-", $teile[0]);

					if (empty($lfdnumber))
					{
						$projectname = trim($teile[1]);
					}


					$text = $teile[0];

					$anzahltrenner = substr_count($text, '-');
					//$app->enqueueMessage('Ich habe -> '.$anzahltrenner.' Trennzeichen <br>');

					if ($anzahltrenner > 1)
					{
						$convert = array(
							'-SV'    => ':SV',
							'-SVO'   => ':SVO',
							'-FC'    => ':FC',
							'-TSV'   => ':TSV',
							'-JFG'   => ':JFG',
							'-TV'    => ':TV',
							'-ASV'   => ':ASV',
							'-SSV'   => ':SSV',
							'-(SG)'  => ':(SG)',
							'-SpVgg' => ':SpVgg',
							'-VfB'   => ':VfB',
							'-FSV'   => ':FSV',
							'-BSK'   => ':BSK'
						);

						if (preg_match("/-SV/i", $teile[0])
							|| preg_match("/-SVO/i", $teile[0])
							|| preg_match("/-TSV/i", $teile[0])
							|| preg_match("/-JFG/i", $teile[0])
							|| preg_match("/-TV/i", $teile[0])
							|| preg_match("/-ASV/i", $teile[0])
							|| preg_match("/-SSV/i", $teile[0])
							|| preg_match("/-(SG)/i", $teile[0])
							|| preg_match("/-SpVgg/i", $teile[0])
							|| preg_match("/-VfB/i", $teile[0])
							|| preg_match("/-FSV/i", $teile[0])
							|| preg_match("/-BSK/i", $teile[0])
							|| preg_match("/-FC/i", $teile[0])
						)
						{
							$teile[0] = str_replace(array_keys($convert), array_values($convert), $teile[0]);
							$teile2   = explode(":", $teile[0]);
						}
						else
						{
							$pos = strrpos($teile[0], "-");
							//echo 'letzte position -> '.$pos.' trennzeichen <br>';

							$teile2[0] = substr($teile[0], 0, $pos);
							$teile2[1] = substr($teile[0], $pos + 1, 100);
						}

					}

					$exportmatchplan[$icsfile['VEVENT'][$a]['UID']]['heim'] = trim($teile2[0]);

					$valueheim = trim($teile2[0]);
					//$exportteamplaygroundtemp[$valueheim] = $valueplayground;

					$exportmatchplan[$icsfile['VEVENT'][$a]['UID']]['gast'] = trim($teile2[1]);
					$valuegast                                              = trim($teile2[1]);

					// heimmannschaft
					if (array_key_exists($valueheim, $exportteamstemp))
					{
					}
					else
					{
						$exportteamstemp[$valueheim] = $lfdnumberteam;
						$lfdnumberteam++;
					}

					// gastmannschaft
					if (array_key_exists($valuegast, $exportteamstemp))
					{
					}
					else
					{
						$exportteamstemp[$valuegast] = $lfdnumberteam;
						$lfdnumberteam++;
					}

					if (isset($icsfile['VEVENT'][$a]['LOCATION']))
					{
						// sportanlage neu
						$teile = explode("\,", $icsfile['VEVENT'][$a]['LOCATION']);

						if (sizeof($teile) === 4)
						{
							$exportmatchplan[$icsfile['VEVENT'][$a]['UID']]['playground']         = trim($teile[0]);
							$exportmatchplan[$icsfile['VEVENT'][$a]['UID']]['playground_strasse'] = trim($teile[1]);
							$exportmatchplan[$icsfile['VEVENT'][$a]['UID']]['playground_plz']     = trim($teile[2]);
							$exportmatchplan[$icsfile['VEVENT'][$a]['UID']]['playground_ort']     = trim($teile[3]);
							$valueplayground                                                      = trim($teile[0]);
							$address                                                              = trim($teile[1]);
							$zipcode                                                              = trim($teile[2]);
							$city                                                                 = trim($teile[3]);
						}
						else
						{
							$exportmatchplan[$icsfile['VEVENT'][$a]['UID']]['playground']         = '';
							$exportmatchplan[$icsfile['VEVENT'][$a]['UID']]['playground_strasse'] = trim($teile[0]);
							$exportmatchplan[$icsfile['VEVENT'][$a]['UID']]['playground_plz']     = trim($teile[1]);
							$exportmatchplan[$icsfile['VEVENT'][$a]['UID']]['playground_ort']     = trim($teile[2]);
							$valueplayground                                                      = $valueheim;
							$address                                                              = trim($teile[0]);
							$zipcode                                                              = trim($teile[1]);
							$city                                                                 = trim($teile[2]);
						}

					}

					if ($valueplayground)
					{

						$exportteamplaygroundtemp[$valueheim] = $valueplayground;

						if (array_key_exists($valueplayground, $exportplaygroundtemp))
						{
						}
						else
						{
							$exportplaygroundtemp[$valueplayground] = $lfdnumberplayground;

							$temp               = new stdClass();
							$temp->id           = $lfdnumberplayground;
							$temp->name         = $valueplayground;
							$temp->short_name   = $valueplayground;
							$temp->alias        = $valueplayground;
							$temp->club_id      = $exportteamstemp[$valueheim];
							$temp->address      = $address;
							$temp->zipcode      = $zipcode;
							$temp->city         = $city;
							$temp->country      = $country;
							$temp->max_visitors = 0;
							$exportplayground[] = $temp;

							$lfdnumberplayground++;
						}

					}

					if (empty($lfdnumber))
					{

						$temp                          = new stdClass();
						$temp->name                    = $projectname;
						$temp->exportRoutine           = '2010-09-19 23:00:00';
						$this->_datas['exportversion'] = $temp;

						$temp                   = new stdClass();
						$temp->name             = '';
						$this->_datas['season'] = $temp;

						$temp                       = new stdClass();
						$temp->id                   = 1;
						$temp->name                 = 'COM_SPORTSMANAGEMENT_ST_SOCCER';
						$this->_datas['sportstype'] = $temp;

						$temp                   = new stdClass();
						$temp->name             = $projectname;
						$temp->alias            = $projectname;
						$temp->short_name       = $projectname;
						$temp->middle_name      = $projectname;
						$temp->country          = $country;
						$this->_datas['league'] = $temp;

						$temp               = new stdClass();
						$temp->name         = $projectname;
						$temp->serveroffset = 0;
						$temp->project_type = 'SIMPLE_LEAGUE';

						$temp->current_round_auto        = '2';
						$temp->auto_time                 = '2880';
						$temp->start_date                = '2013-08-08';
						$temp->start_time                = '15:30';
						$temp->game_regular_time         = '90';
						$temp->game_parts                = '2';
						$temp->halftime                  = '15';
						$temp->points_after_regular_time = '3,1,0';
						$temp->use_legs                  = '0';
						$temp->allow_add_time            = '0';
						$temp->add_time                  = '30';
						$temp->points_after_add_time     = '3,1,0';
						$temp->points_after_penalty      = '3,1,0';

						$this->_datas['project'] = $temp;
					}


					$lfdnumber++;
					$lfdnumbermatch++;
				} //spielfreie Mannschaften ausschließen
			}

			ksort($exportmatchplan);

			// teams verarbeiten
			foreach ($exportteamstemp as $key => $value)
			{
				// team
				$temp              = new stdClass();
				$temp->id          = $value;
				$temp->club_id     = $value;
				$temp->name        = $key;
				$temp->middle_name = $key;
				$temp->short_name  = $key;
				//$temp->info = '';
				$temp->info     = 'Herren ';
				$temp->extended = '';
				$exportteams[]  = $temp;

				$standard_playground        = $exportteamplaygroundtemp[$key];
				$standard_playground_nummer = $exportplaygroundtemp[$standard_playground];

				// club
				$temp                      = new stdClass();
				$temp->id                  = $value;
				$temp->name                = $key;
				$temp->country             = $country;
				$temp->extended            = '';
				$temp->standard_playground = $standard_playground_nummer;
				$exportclubs[]             = $temp;

				// projektteam
				$temp                      = new stdClass();
				$temp->id                  = $value;
				$temp->team_id             = $value;
				$temp->project_team_id     = $value;
				$temp->is_in_score         = 1;
				$temp->standard_playground = $standard_playground_nummer;
				$exportprojectteams[]      = $temp;

			}

           if ( $country == 'AUT' )
            {
           }
          else
          {
			$anzahlteams = sizeof($exportteamstemp);
			$app->enqueueMessage(Text::_('Wir haben ' . $anzahlteams . ' Teams f&uuml;r die Berechnung der Spieltage und Paarungen pro Spieltag'), '');

			if ($anzahlteams % 2 == 0)
			{
				$anzahltage      = ($anzahlteams - 1) * 2;
				$anzahlpaarungen = $anzahlteams / 2;
			}
			else
			{
				//$anzahltage = ( $anzahlteams - 1 ) * 2;
				$anzahltage      = $anzahlteams * 2;
				$anzahlpaarungen = ($anzahlteams - 1) / 2;
			}
			$app->enqueueMessage(Text::_('Wir haben ' . $anzahltage . ' Spieltage'), '');
			$app->enqueueMessage(Text::_('Wir haben ' . $anzahlpaarungen . ' Paarungen pro Spieltag'), '');

			// so jetzt die runden erstellen
			for ($a = 1; $a <= $anzahltage; $a++)
			{
				$temp                   = new stdClass();
				$temp->id               = $a;
				$temp->roundcode        = $a;
				$temp->name             = $a . '. Spieltag';
				$temp->alias            = $a . '-spieltag';
				$temp->round_date_first = '';
				$temp->round_date_last  = '';
				$exportround[$a]        = $temp;
			}
          }
          
			// so jetzt die spiele erstellen
			$lfdnumbermatch    = 1;
			$lfdnumberpaarung  = 1;
			$lfdnumberspieltag = 1;
			foreach ($exportmatchplan as $key => $value)
			{

				$tempmatch               = new stdClass();
				$tempmatch->id           = $lfdnumbermatch;
				$tempmatch->match_number = $lfdnumbermatch;
				$tempmatch->published    = 1;
				$tempmatch->count_result = 1;
				$tempmatch->show_report  = 1;
				$tempmatch->team1_result = '';
				$tempmatch->team2_result = '';
				$tempmatch->summary      = '';
				$tempmatch->match_date   = $value['match_date'];

				if (isset($value['playground']))
				{
					if (array_key_exists($value['playground'], $exportplaygroundtemp))
					{
						$tempmatch->playground_id = $exportplaygroundtemp[$value['playground']];
					}
				}

				$tempmatch->projectteam1_id = $exportteamstemp[$value['heim']];
				$tempmatch->projectteam2_id = $exportteamstemp[$value['gast']];
              if ( $country == 'AUT' )
            {
                $tempmatch->round_id        = $value['round_id'];
              }
              else
              {
				$tempmatch->round_id        = $lfdnumberspieltag;
              }
				$exportmatch[]              = $tempmatch;

				if ($lfdnumberpaarung == $anzahlpaarungen)
				{
					$lfdnumberpaarung = 0;
					$lfdnumberspieltag++;
				}

				$lfdnumbermatch++;
				$lfdnumberpaarung++;
			}

			// daten übergeben
			$this->_datas['round']       = array_merge($exportround);
			$this->_datas['match']       = array_merge($exportmatch);
			$this->_datas['team']        = array_merge($exportteams);
			$this->_datas['projectteam'] = array_merge($exportprojectteams);
			$this->_datas['club']        = array_merge($exportclubs);
			$this->_datas['playground']  = array_merge($exportplayground);


			/**
			 * das ganze für den standardimport aufbereiten
			 */
			$output = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
			// open the project
			$output .= "<project>\n";
			// set the version of SportsManagement
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSportsManagementVersion());
			// set the project datas
			if (isset($this->_datas['project']))
			{
				$app->enqueueMessage(Text::_('project Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setProjectData($this->_datas['project']));
			}
			// set league data of project
			if (isset($this->_datas['league']))
			{
				$app->enqueueMessage(Text::_('league Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setLeagueData($this->_datas['league']));
			}
			// set season data of project
			if (isset($this->_datas['season']))
			{
				$app->enqueueMessage(Text::_('season Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSeasonData($this->_datas['season']));
			}
			// set the rounds data
			if (isset($this->_datas['round']))
			{
				$app->enqueueMessage(Text::_('round Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['round'], 'Round'));
			}
			// set the teams data
			if (isset($this->_datas['team']))
			{
				$app->enqueueMessage(Text::_('team Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['team'], 'JL_Team'));
			}
			// set the clubs data
			if (isset($this->_datas['club']))
			{
				$app->enqueueMessage(Text::_('club Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['club'], 'Club'));
			}
			// set the matches data
			if (isset($this->_datas['match']))
			{
				$app->enqueueMessage(Text::_('match Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['match'], 'Match'));
			}
			// set the positions data
			if (isset($this->_datas['position']))
			{
				$app->enqueueMessage(Text::_('position Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['position'], 'Position'));
			}
			// set the positions parent data
			if (isset($this->_datas['parentposition']))
			{
				$app->enqueueMessage(Text::_('parentposition Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['parentposition'], 'ParentPosition'));
			}
			// set position data of project
			if (isset($this->_datas['projectposition']))
			{
				$app->enqueueMessage(Text::_('projectposition Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectposition'], 'ProjectPosition'));
			}
			// set the matchreferee data
			if (isset($this->_datas['matchreferee']))
			{
				$app->enqueueMessage(Text::_('matchreferee Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['matchreferee'], 'MatchReferee'));
			}
			// set the person data
			if (isset($this->_datas['person']))
			{
				$app->enqueueMessage(Text::_('person Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['person'], 'Person'));
			}
			// set the projectreferee data
			if (isset($this->_datas['projectreferee']))
			{
				$app->enqueueMessage(Text::_('projectreferee Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectreferee'], 'ProjectReferee'));
			}
			// set the projectteam data
			if (isset($this->_datas['projectteam']))
			{
				$app->enqueueMessage(Text::_('projectteam Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectteam'], 'ProjectTeam'));
			}
			// set playground data of project
			if (isset($this->_datas['playground']))
			{
				$app->enqueueMessage(Text::_('playground Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['playground'], 'Playground'));
			}

			// close the project
			$output .= '</project>';
			// mal als test
			$xmlfile = $output;
			$file    = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'sportsmanagement_import.jlg';
			File::write($file, $xmlfile);

		}
		/**
		 * kalender file vom bfv ende
		 */


		/*
		csv->data

		Array
		(
		[0] => Array
		(
			[Datum] => 27.07.2010
			[Zeit] => 00:43:12
			[Saison] => 10/11
			[Verband] => Schleswig-Holsteinischer Fußballverband
			[Mannschaftsart_Key] => 013
			[Mannschaftsart] => Herren
			[Spielklasse_Key] => 058
			[Spielklasse] => Kreisklasse C
			[Spielgebiet_Key] => 032
			[Spielgebiet] => Kreis Nordfriesland
			[Rahmenspielplan] => 3
			[Staffel_Nr] => 2
			[Staffel] => Kreisklasse C-Süd
			[Staffelkennung] => 040423
			[Staffelleiter] => Bülter, Dirk
			[Spieldatum] => 28.08.2010
			[Uhrzeit] => 16:00
			[Wochentag] => Samstag
			[Spieltag] => 1
			[Schlüsseltag] => 2
			[Spielkennung] => 040423 001
			[Heimmannschaft] => TSV Drelsdorf III
			[Gastmannschaft] => 1.FC Wittbek
			[freigegeben] => Nein
			[Spielstätte] => A-Platz Drelsdorf
			[Spielleitung] => ,
			[Assistent 1] => ,
			[Assistent 2] => ,
			[verlegtWochentag] =>
			[verlegtSpieldatum] =>
			[verlegtUhrzeit] =>
		)

		*/

		$teamid = 1;
		//$this->fileName = File::read($file);
		$this->lines = file($file);
		if ($this->lines)
		{
			$row = 0;

			/**
			 * Spielerdatei
			 */
			if ($whichfile == 'playerfile')
			{
				// tab delimited, and encoding conversion
				$csv = new JSMparseCSV();
				//$csv->encoding('UTF-16', 'UTF-8');
				$csv->encoding('ISO-8859-1', 'UTF-8');
				// Spielerdatei des DFBNet ist seit 2013 mit einem Tabulator als Delimiter, deswegen ist eine Auswahl nicht erforderlich
				$csv->delimiter = ";";
				$row            = 0;
				$start          = 0;
				$header         = array();
				if (($handle = fopen($file, "r")) !== false)
				{
					while (($data = fgetcsv($handle, 4000, $csv->delimiter)) !== false)
					{

						if ($row == 8)
						{
							$num = count($data);
							for ($c = 0; $c < $num; $c++)
							{
								$header[$c] = $data[$c];
							}
						}
						elseif ($row > 8)
						{
							$num = count($data);
							for ($c = 0; $c < $num; $c++)
							{
								$csv->data[$start][$header[$c]] = $data[$c];
							}
							$start++;
						}

						$row++;
					}

					fclose($handle);
				}


				// anfang schleife csv file
				for ($a = 0; $a < sizeof($csv->data); $a++)
				{
					$temp            = new stdClass();
					$temp->id        = 0;
					$temp->knvbnr    = $csv->data[$a]['Passnr.'];
					$temp->lastname  = $csv->data[$a]['Name'];
					$temp->firstname = $csv->data[$a]['Vorname'];

					$temp->info     = $csv->data[$a]['Altersklasse'];
					$datetime       = strtotime($csv->data[$a]['Geburtsdatum']);
					$temp->birthday = date('Y-m-d', $datetime);

					$temp->country     = $country;
					$temp->nickname    = '';
					$temp->position_id = '';
					//$temp->lastname = utf8_encode ($temp->lastname);
					//$temp->firstname = utf8_encode ($temp->firstname);
					$exportplayer[] = $temp;
				}

				// spielerdatei
				$temp                          = new stdClass();
				$temp->name                    = 'playerfile';
				$temp->exportRoutine           = '2010-09-19 23:00:00';
				$this->_datas['exportversion'] = $temp;

				$this->_datas['person'] = array_merge($exportplayer);
			}
			elseif ($whichfile == 'matchfile')
			{
				/**
				 * Spielplan
				 */
				$row          = 0;
				$header       = array();
				$dfbnetspiele = array();
				$csv          = new JSMparseCSV();

				$input    = file_get_contents($file);
				$encoding = mb_detect_encoding($input, mb_list_encodings(), true);

				if ($encoding !== "UTF-8")
				{
					$csvneu = explode("\n", $input);
					$numcsv = count($csvneu);
					for ($a = 0; $a <= $numcsv; $a++)
					{
						$csvneu[$a] = mb_convert_encoding($csvneu[$a], "UTF-8", $encoding);
						$data       = explode("\t", $csvneu[$a]);

						$num = count($data);
						for ($c = 1; $c < $num; $c++)
						{
							if (empty($row))
							{
								$header[$c] = trim(strip_tags($data[$c]));
							}
							else
							{

								if ($header[$c] == 'Spielkennung')
								{
									$dfbnetspiele[$data[$c]] = '';
								}

								$csv->data[$row][$header[$c]] = trim(strip_tags($data[$c]));
							}

						}
						$row++;
					}

				}
				else
				{

					if (($handle = fopen($file, "r")) !== false)
					{

						while (($data = fgetcsv($handle, 1000, "\t")) !== false)
						{

							$num = count($data);
							for ($c = 0; $c < $num; $c++)
							{
								if (empty($row))
								{
									$header[$c] = $data[$c];
								}
								else
								{

									if ($header[$c] == 'Spielkennung')
									{
										$dfbnetspiele[$data[$c]] = '';
									}

									$csv->data[$row][$header[$c]] = $data[$c];
								}
							}
							$row++;
						}
						fclose($handle);
					}
				}

				for ($a = 1; $a <= sizeof($csv->data); $a++)
				{
					$temp          = array();
					$spielkennung  = '';
					$anhaengengast = false;
					$anhaengenheim = false;
					foreach ($csv->data[$a] as $pos => $val)
					{
						$temp[$pos] = $val;

						switch (trim($pos))
						{
							case 'Heimmannschaft':
								if ($val)
								{
									$anhaengenheim = true;
								}
								break;
							case 'Gastmannschaft':
								if ($val)
								{
									$anhaengengast = true;
								}
								break;
							case 'Spielkennung':
								$spielkennung = $val;
								break;
							case 'verlegtSpieldatum':
								if ($val)
								{
									$temp['Spieldatum'] = $val;
								}
								break;
							case 'verlegtUhrzeit':
								if ($val)
								{
									$temp['Uhrzeit'] = $val;
								}
								break;
						}

					}

					if ($spielkennung && $anhaengenheim && $anhaengengast)
					{
						$dfbnetspiele[$spielkennung] = $temp;
					}
					else
					{
						unset($dfbnetspiele[$spielkennung]);
					}

				}

				unset($csv->data);
				foreach ($dfbnetspiele as $pos => $val)
				{
					$csv->data[] = $val;
				}
				if (sizeof($csv->data) == 0)
				{
					$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . '-' . 'Falsches Dateiformat'), 'Error');
					$importcsv = false;
				}
				else
				{
					$importcsv = true;
				}

				$lfdnumber             = 0;
				$lfdnumberteam         = 1;
				$lfdnumbermatch        = 1;
				$lfdnumberplayground   = 1;
				$lfdnumberperson       = 1;
				$lfdnumbermatchreferee = 1;

				/**
				 * anfang schleife csv file
				 */
				for ($a = 0; $a < sizeof($csv->data); $a++)
				{

					if (empty($lfdnumber))
					{

						/*
						[Saison] => 10/11
							  [Verband] => Schleswig-Holsteinischer Fußballverband
							  [Mannschaftsart_Key] => 013
							  [Mannschaftsart] => Herren
							  [Spielklasse_Key] => 058
							  [Spielklasse] => Kreisklasse C
							  [Spielgebiet_Key] => 032
							  [Spielgebiet] => Kreis Nordfriesland
							  [Rahmenspielplan] => 3
							  [Staffel_Nr] => 2
							  [Staffel] => Kreisklasse C-Süd
						*/

						$temp                          = new stdClass();
						$temp->name                    = $csv->data[$a]['Verband'];
						$temp->exportRoutine           = '2010-09-19 23:00:00';
						$this->_datas['exportversion'] = $temp;

						$temp                   = new stdClass();
						$temp->name             = $csv->data[$a]['Saison'];
						$this->_datas['season'] = $temp;

						$temp                   = new stdClass();
						$temp->name             = $csv->data[$a]['Staffel'] . ' ' . $csv->data[$a]['Staffel_Nr'];
						$temp->country          = $country;
						$this->_datas['league'] = $temp;

						$temp                       = new stdClass();
						$temp->id                   = 1;
						$temp->name                 = 'COM_SPORTSMANAGEMENT_ST_SOCCER';
						$this->_datas['sportstype'] = $temp;

						$temp                 = new stdClass();
						$temp->name           = $csv->data[$a]['Staffel'] . ' ' . $csv->data[$a]['Saison'];
						$temp->serveroffset   = 0;
						$temp->sports_type_id = 1;
						$temp->project_type   = 'SIMPLE_LEAGUE';

						$temp->current_round_auto        = '2';
						$temp->auto_time                 = '2880';
						$temp->start_date                = '2013-08-08';
						$temp->start_time                = '15:30';
						$temp->game_regular_time         = '90';
						$temp->game_parts                = '2';
						$temp->halftime                  = '15';
						$temp->points_after_regular_time = '3,1,0';
						$temp->use_legs                  = '0';
						$temp->allow_add_time            = '0';
						$temp->add_time                  = '30';
						$temp->points_after_add_time     = '3,1,0';
						$temp->points_after_penalty      = '3,1,0';

						$this->_datas['project'] = $temp;
					}

					$valuematchday = $csv->data[$a]['Spieltag'];

					if (isset($exportround[$valuematchday]))
					{
					}
					else
					{
						if ($valuematchday)
						{
							$temp                        = new stdClass();
							$temp->id                    = $valuematchday;
							$temp->roundcode             = $valuematchday;
							$temp->name                  = $valuematchday . '. Spieltag';
							$temp->alias                 = $valuematchday . '-spieltag';
							$temp->round_date_first      = '';
							$temp->round_date_last       = '';
							$exportround[$valuematchday] = $temp;
						}
					}

					// dfbnet heimmannschaft
					$valueheim = $csv->data[$a]['Heim Mannschaft'];
					if (empty($valueheim))
					{
						$valueheim = $csv->data[$a]['Heimmannschaft'];
					}
					if ($valueheim != 'Spielfrei' && $valueheim)
					{
						if (in_array($valueheim, $exportteamstemp))
						{
							// echo $valueheim." <- enthalten<br>";
							$exportclubsstandardplayground[$valueheim]             = $csv->data[$a]['Spielstätte'];
							$exportplaygroundclubib[$csv->data[$a]['Spielstätte']] = $valueheim;
						}
						else
						{
							// echo $valueheim." <- nicht enthalten<br>";
							$exportclubsstandardplayground[$valueheim]             = $csv->data[$a]['Spielstätte'];
							$exportplaygroundclubib[$csv->data[$a]['Spielstätte']] = $valueheim;
							$exportteamstemp[]                                     = $valueheim;
							$temp                                                  = new stdClass();
							$temp->id                                              = $lfdnumberteam;
							$temp->club_id                                         = $lfdnumberteam;
							$temp->name                                            = $valueheim;
							$temp->middle_name                                     = $valueheim;
							$temp->short_name                                      = $valueheim;
							$temp->info                                            = $csv->data[$a]['Mannschaftsart'];
							$temp->extended                                        = '';
							$exportteams[]                                         = $temp;

							$app->setUserState($option . "teamart", $temp->info);

							// der clubname muss um die mannschaftsnummer verkürzt werden
							if (substr($valueheim, -4, 4) == ' III')
							{
								$convert   = array(
									' III' => ''
								);
								$valueheim = str_replace(array_keys($convert), array_values($convert), $valueheim);
							}
							if (substr($valueheim, -3, 3) == ' II')
							{
								$convert   = array(
									' II' => ''
								);
								$valueheim = str_replace(array_keys($convert), array_values($convert), $valueheim);
							}
							if (substr($valueheim, -2, 2) == ' I')
							{
								$convert   = array(
									' I' => ''
								);
								$valueheim = str_replace(array_keys($convert), array_values($convert), $valueheim);
							}

							if (substr($valueheim, -2, 2) == ' 3')
							{
								$convert   = array(
									' 3' => ''
								);
								$valueheim = str_replace(array_keys($convert), array_values($convert), $valueheim);
							}
							if (substr($valueheim, -2, 2) == ' 2')
							{
								$convert   = array(
									' 2' => ''
								);
								$valueheim = str_replace(array_keys($convert), array_values($convert), $valueheim);
							}


							$temp                      = new stdClass();
							$temp->id                  = $lfdnumberteam;
							$club_id                   = $lfdnumberteam;
							$temp->name                = $valueheim;
							$temp->country             = $country;
							$temp->extended            = '';
							$temp->standard_playground = $lfdnumberplayground;
							$exportclubs[]             = $temp;

							$temp                  = new stdClass();
							$temp->id              = $lfdnumberteam;
							$temp->team_id         = $lfdnumberteam;
							$temp->project_team_id = $lfdnumberteam;
							$temp->is_in_score     = 1;

							$temp->division_id        = 0;
							$temp->start_points       = 0;
							$temp->points_finally     = 0;
							$temp->neg_points_finally = 0;
							$temp->matches_finally    = 0;
							$temp->won_finally        = 0;
							$temp->draws_finally      = 0;
							$temp->lost_finally       = 0;
							$temp->homegoals_finally  = 0;
							$temp->guestgoals_finally = 0;
							$temp->diffgoals_finally  = 0;

							$temp->standard_playground = $lfdnumberplayground;
							$exportprojectteams[]      = $temp;

							$lfdnumberteam++;
						}
					}

					// dfbnet gastmannschaft
					$valuegast = $csv->data[$a]['Gast Mannschaft'];
					if (empty($valuegast))
					{
						$valuegast = $csv->data[$a]['Gastmannschaft'];
					}
					if ($valuegast != 'Spielfrei' && $valuegast)
					{
						if (in_array($valuegast, $exportteamstemp))
						{
							// echo $valuegast." <- enthalten<br>";
						}
						else
						{
							// echo $valuegast." <- nicht enthalten<br>";
							$exportteamstemp[] = $valuegast;
							$temp              = new stdClass();
							$temp->id          = $lfdnumberteam;
							$temp->club_id     = $lfdnumberteam;
							$temp->name        = $valuegast;
							$temp->middle_name = $valuegast;
							$temp->short_name  = $valuegast;
							$temp->info        = $csv->data[$a]['Mannschaftsart'];
							$temp->extended    = '';
							$exportteams[]     = $temp;

							// der clubname muss um die mannschaftsnummer verkürzt werden
							if (substr($valuegast, -4, 4) == ' III')
							{
								$convert   = array(
									' III' => ''
								);
								$valuegast = str_replace(array_keys($convert), array_values($convert), $valuegast);
							}
							if (substr($valuegast, -3, 3) == ' II')
							{
								$convert   = array(
									' II' => ''
								);
								$valuegast = str_replace(array_keys($convert), array_values($convert), $valuegast);
							}
							if (substr($valuegast, -2, 2) == ' I')
							{
								$convert   = array(
									' I' => ''
								);
								$valuegast = str_replace(array_keys($convert), array_values($convert), $valuegast);
							}

							if (substr($valuegast, -2, 2) == ' 3')
							{
								$convert   = array(
									' 3' => ''
								);
								$valuegast = str_replace(array_keys($convert), array_values($convert), $valuegast);
							}
							if (substr($valuegast, -2, 2) == ' 2')
							{
								$convert   = array(
									' 2' => ''
								);
								$valuegast = str_replace(array_keys($convert), array_values($convert), $valuegast);
							}

							$temp                      = new stdClass();
							$temp->id                  = $lfdnumberteam;
							$temp->name                = $valuegast;
							$temp->standard_playground = 0;
							$temp->country             = $country;
							$temp->extended            = '';
							$exportclubs[]             = $temp;

							$temp                  = new stdClass();
							$temp->id              = $lfdnumberteam;
							$temp->team_id         = $lfdnumberteam;
							$temp->project_team_id = $lfdnumberteam;
							$temp->is_in_score     = 1;

							$temp->division_id        = 0;
							$temp->start_points       = 0;
							$temp->points_finally     = 0;
							$temp->neg_points_finally = 0;
							$temp->matches_finally    = 0;
							$temp->won_finally        = 0;
							$temp->draws_finally      = 0;
							$temp->lost_finally       = 0;
							$temp->homegoals_finally  = 0;
							$temp->guestgoals_finally = 0;
							$temp->diffgoals_finally  = 0;

							$temp->standard_playground = 0;
							$exportprojectteams[]      = $temp;

							$lfdnumberteam++;
						}
					}

					// dfbnet spielstaette
					$valueplayground = $csv->data[$a]['Spielstätte'];
					if (!$valueplayground)
					{
						$valueplayground = $csv->data[$a]['Spielstaette'];
					}

					if ($valueplayground)
					{
						if (array_key_exists($valueplayground, $exportplaygroundtemp))
						{
							// echo $valueplayground." <- enthalten<br>";
						}
						else
						{
							// echo $valueplayground." <- nicht enthalten<br>";

							$exportplaygroundtemp[$valueplayground] = $lfdnumberplayground;
							$temp                                   = new stdClass();
							$temp->id                               = $lfdnumberplayground;
							$matchnumberplayground                  = $lfdnumberplayground;
							$temp->name                             = $valueplayground;
							$temp->short_name                       = $valueplayground;
							$temp->country                          = $country;
							$temp->max_visitors                     = 0;
							$valueheimsuchen                        = $exportplaygroundclubib[$valueplayground];
							foreach ($exportteamstemp as $key => $value)
							{
								if ($value == $valueheimsuchen)
								{
									$club_id = $key + 1;
								}
							}

							$temp->club_id      = $club_id;
							$exportplayground[] = $temp;

							//$app->enqueueMessage(Text::_('playground -> '.$valueplayground ),'Notice');
							//$app->enqueueMessage(Text::_('heimmannschaft suchen -> '.$valueheimsuchen ),'Notice');
							//$app->enqueueMessage(Text::_('heimmannschaft club-id -> '.$club_id ),'Notice');

							$lfdnumberplayground++;
						}
					}

					$valueperson  = $csv->data[$a]['Spielleitung'];
					$valueperson1 = $csv->data[$a]['Assistent 1'];
					$valueperson2 = $csv->data[$a]['Assistent 2'];

					//if (in_array($valueperson, $exportpersonstemp))
					if (array_key_exists($valueperson, $exportpersonstemp))
					{

						if (isset($csv->data[$a]['Heimmannschaft']))
						{
							$bemerkung = $csv->data[$a]['Heimmannschaft'];
						}
						if (isset($csv->data[$a]['Gastmannschaft']))
						{
							$bemerkung = $csv->data[$a]['Gastmannschaft'];
						}
						if (isset($csv->data[$a]['Heim Mannschaft']))
						{
							$bemerkung = $csv->data[$a]['Heim Mannschaft'];
						}
						if (isset($csv->data[$a]['Gast Mannschaft']))
						{
							$bemerkung = $csv->data[$a]['Gast Mannschaft'];
						}

						if ($bemerkung == 'Spielfrei' || $bemerkung == 'Spielfrei' || $bemerkung == '')
						{
							/**
							 * nichts machen
							 */
						}
						else
						{
							$tempmatchreferee                      = new stdClass();
							$tempmatchreferee->id                  = $lfdnumbermatchreferee;
							$tempmatchreferee->match_id            = $lfdnumbermatch;
							$tempmatchreferee->project_referee_id  = $exportpersonstemp[$valueperson];
							$tempmatchreferee->project_position_id = 1000;
							$exportmatchreferee[]                  = $tempmatchreferee;
							$lfdnumbermatchreferee++;
						}

					}
					else
					{

						if (strlen($valueperson) > 6 && $valueperson)
						{
							// echo $valueperson." <- nicht enthalten<br>";
							$exportpersonstemp[$valueperson] = $lfdnumberperson;

							// nach- und vorname richtig setzen
							$teile = explode(",", $valueperson);

							$temp                      = new stdClass();
							$temp->id                  = $lfdnumberperson;
							$temp->person_id           = $lfdnumberperson;
							$temp->project_position_id = 1000;
							$exportreferee[]           = $temp;

							$temp              = new stdClass();
							$temp->id          = $lfdnumberperson;
							$temp->lastname    = trim($teile[0]);
							$temp->firstname   = trim($teile[1]);
							$temp->nickname    = '';
							$temp->knvbnr      = '';
							$temp->location    = '';
							$temp->birthday    = '0000-00-00';
							$temp->country     = $country;
							$temp->position_id = 1000;
							$temp->info        = 'Schiri';
							$exportpersons[]   = $temp;

							if (isset($csv->data[$a]['Heimmannschaft']))
							{
								$bemerkung = $csv->data[$a]['Heimmannschaft'];
							}
							if (isset($csv->data[$a]['Gastmannschaft']))
							{
								$bemerkung = $csv->data[$a]['Gastmannschaft'];
							}
							if (isset($csv->data[$a]['Heim Mannschaft']))
							{
								$bemerkung = $csv->data[$a]['Heim Mannschaft'];
							}
							if (isset($csv->data[$a]['Gast Mannschaft']))
							{
								$bemerkung = $csv->data[$a]['Gast Mannschaft'];
							}

							if ($bemerkung == 'Spielfrei' || $bemerkung == 'Spielfrei' || $bemerkung == '')
							{
								/**
								 * nichts machen
								 */
							}
							else
							{
								$tempmatchreferee                      = new stdClass();
								$tempmatchreferee->id                  = $lfdnumbermatchreferee;
								$tempmatchreferee->match_id            = $lfdnumbermatch;
								$tempmatchreferee->project_referee_id  = $lfdnumberperson;
								$tempmatchreferee->project_position_id = 1000;
								$exportmatchreferee[]                  = $tempmatchreferee;
								$lfdnumbermatchreferee++;
							}
							$lfdnumberperson++;
						}

					}

					// 1.assistent
					if (array_key_exists($valueperson1, $exportpersonstemp))
					{

						if (isset($csv->data[$a]['Heimmannschaft']))
						{
							$bemerkung = $csv->data[$a]['Heimmannschaft'];
						}
						if (isset($csv->data[$a]['Gastmannschaft']))
						{
							$bemerkung = $csv->data[$a]['Gastmannschaft'];
						}
						if (isset($csv->data[$a]['Heim Mannschaft']))
						{
							$bemerkung = $csv->data[$a]['Heim Mannschaft'];
						}
						if (isset($csv->data[$a]['Gast Mannschaft']))
						{
							$bemerkung = $csv->data[$a]['Gast Mannschaft'];
						}

						if ($bemerkung == 'Spielfrei' || $bemerkung == 'Spielfrei' || $bemerkung == '')
						{
							/**
							 * nichts machen
							 */
						}
						else
						{
							$tempmatchreferee                      = new stdClass();
							$tempmatchreferee->id                  = $lfdnumbermatchreferee;
							$tempmatchreferee->match_id            = $lfdnumbermatch;
							$tempmatchreferee->project_referee_id  = $exportpersonstemp[$valueperson1];
							$tempmatchreferee->project_position_id = 1001;
							$exportmatchreferee[]                  = $tempmatchreferee;
							$lfdnumbermatchreferee++;
						}

					}
					else
					{

						if (strlen($valueperson1) > 6 && $valueperson1)
						{
							// echo $valueperson." <- nicht enthalten<br>";
							$exportpersonstemp[$valueperson1] = $lfdnumberperson;

							// nach- und vorname richtig setzen
							$teile = explode(",", $valueperson1);

							$temp                      = new stdClass();
							$temp->id                  = $lfdnumberperson;
							$temp->person_id           = $lfdnumberperson;
							$temp->project_position_id = 1001;
							$exportreferee[]           = $temp;

							$temp              = new stdClass();
							$temp->id          = $lfdnumberperson;
							$temp->lastname    = trim($teile[0]);
							$temp->firstname   = trim($teile[1]);
							$temp->nickname    = '';
							$temp->knvbnr      = '';
							$temp->location    = '';
							$temp->birthday    = '0000-00-00';
							$temp->country     = $country;
							$temp->position_id = 1001;
							$temp->info        = 'Schiri';
							$exportpersons[]   = $temp;

							if (isset($csv->data[$a]['Heimmannschaft']))
							{
								$bemerkung = $csv->data[$a]['Heimmannschaft'];
							}
							if (isset($csv->data[$a]['Gastmannschaft']))
							{
								$bemerkung = $csv->data[$a]['Gastmannschaft'];
							}
							if (isset($csv->data[$a]['Heim Mannschaft']))
							{
								$bemerkung = $csv->data[$a]['Heim Mannschaft'];
							}
							if (isset($csv->data[$a]['Gast Mannschaft']))
							{
								$bemerkung = $csv->data[$a]['Gast Mannschaft'];
							}

							if ($bemerkung == 'Spielfrei' || $bemerkung == 'Spielfrei' || $bemerkung == '')
							{
								/**
								 * nichts machen
								 */
							}
							else
							{
								$tempmatchreferee                      = new stdClass();
								$tempmatchreferee->id                  = $lfdnumbermatchreferee;
								$tempmatchreferee->match_id            = $lfdnumbermatch;
								$tempmatchreferee->project_referee_id  = $lfdnumberperson;
								$tempmatchreferee->project_position_id = 1001;
								$exportmatchreferee[]                  = $tempmatchreferee;
								$lfdnumbermatchreferee++;
							}
							$lfdnumberperson++;
						}

					}

					// 2.assistent
					if (array_key_exists($valueperson2, $exportpersonstemp))
					{

						if (isset($csv->data[$a]['Heimmannschaft']))
						{
							$bemerkung = $csv->data[$a]['Heimmannschaft'];
						}
						if (isset($csv->data[$a]['Gastmannschaft']))
						{
							$bemerkung = $csv->data[$a]['Gastmannschaft'];
						}
						if (isset($csv->data[$a]['Heim Mannschaft']))
						{
							$bemerkung = $csv->data[$a]['Heim Mannschaft'];
						}
						if (isset($csv->data[$a]['Gast Mannschaft']))
						{
							$bemerkung = $csv->data[$a]['Gast Mannschaft'];
						}

						if ($bemerkung == 'Spielfrei' || $bemerkung == 'Spielfrei' || $bemerkung == '')
						{
							/**
							 * nichts machen
							 */
						}
						else
						{
							$tempmatchreferee                      = new stdClass();
							$tempmatchreferee->id                  = $lfdnumbermatchreferee;
							$tempmatchreferee->match_id            = $lfdnumbermatch;
							$tempmatchreferee->project_referee_id  = $exportpersonstemp[$valueperson2];
							$tempmatchreferee->project_position_id = 1002;
							$exportmatchreferee[]                  = $tempmatchreferee;
							$lfdnumbermatchreferee++;
						}

					}
					else
					{

						if (strlen($valueperson2) > 6 && $valueperson2)
						{
							// echo $valueperson." <- nicht enthalten<br>";
							$exportpersonstemp[$valueperson2] = $lfdnumberperson;

							// nach- und vorname richtig setzen
							$teile = explode(",", $valueperson2);

							$temp                      = new stdClass();
							$temp->id                  = $lfdnumberperson;
							$temp->person_id           = $lfdnumberperson;
							$temp->project_position_id = 1002;
							$exportreferee[]           = $temp;

							$temp              = new stdClass();
							$temp->id          = $lfdnumberperson;
							$temp->lastname    = trim($teile[0]);
							$temp->firstname   = trim($teile[1]);
							$temp->nickname    = '';
							$temp->knvbnr      = '';
							$temp->location    = '';
							$temp->birthday    = '0000-00-00';
							$temp->country     = $country;
							$temp->position_id = 1002;
							$temp->info        = 'Schiri';
							$exportpersons[]   = $temp;

							if (isset($csv->data[$a]['Heimmannschaft']))
							{
								$bemerkung = $csv->data[$a]['Heimmannschaft'];
							}
							if (isset($csv->data[$a]['Gastmannschaft']))
							{
								$bemerkung = $csv->data[$a]['Gastmannschaft'];
							}
							if (isset($csv->data[$a]['Heim Mannschaft']))
							{
								$bemerkung = $csv->data[$a]['Heim Mannschaft'];
							}
							if (isset($csv->data[$a]['Gast Mannschaft']))
							{
								$bemerkung = $csv->data[$a]['Gast Mannschaft'];
							}
							if ($bemerkung == 'Spielfrei' || $bemerkung == 'Spielfrei' || $bemerkung == '')
							{
								/**
								 * nichts machen
								 */
							}
							else
							{
								$tempmatchreferee                      = new stdClass();
								$tempmatchreferee->id                  = $lfdnumbermatchreferee;
								$tempmatchreferee->match_id            = $lfdnumbermatch;
								$tempmatchreferee->project_referee_id  = $lfdnumberperson;
								$tempmatchreferee->project_position_id = 1002;
								$exportmatchreferee[]                  = $tempmatchreferee;
								$lfdnumbermatchreferee++;
							}
							$lfdnumberperson++;
						}

					}


					if (isset($csv->data[$a]['Heimmannschaft']))
					{
						$bemerkung = $csv->data[$a]['Heimmannschaft'];
					}
					if (isset($csv->data[$a]['Gastmannschaft']))
					{
						$bemerkung = $csv->data[$a]['Gastmannschaft'];
					}
					if (isset($csv->data[$a]['Heim Mannschaft']))
					{
						$bemerkung = $csv->data[$a]['Heim Mannschaft'];
					}
					if (isset($csv->data[$a]['Gast Mannschaft']))
					{
						$bemerkung = $csv->data[$a]['Gast Mannschaft'];
					}

					if ($bemerkung == 'Spielfrei' || $bemerkung == 'Spielfrei' || $bemerkung == '')
					{
						/**
						 * nichts machen
						 */
					}
					else
					{
						$round_id              = $csv->data[$a]['Spieltag'];
						$tempmatch             = new stdClass();
						$tempmatch->id         = $lfdnumbermatch;
						$tempmatch->round_id   = $round_id;
						$datetime              = strtotime($csv->data[$a]['Spieldatum']);
						$tempmatch->match_date = date('Y-m-d', $datetime) . " " . $csv->data[$a]['Uhrzeit'];

						if ($csv->data[$a]['verlegtSpieldatum'])
						{
							$datetime                      = strtotime($csv->data[$a]['verlegtSpieldatum']);
							$tempmatch->match_date_verlegt = date('Y-m-d', $datetime) . " " . $csv->data[$a]['verlegtUhrzeit'];
						}
						else
						{
							$tempmatch->match_date_verlegt = '';
						}

						// datum im spieltag setzen
						if (!$exportround[$round_id]->round_date_first && !$exportround[$round_id]->round_date_last && $valuematchday)
						{
							$exportround[$round_id]->round_date_first = date('Y-m-d', $datetime);
							$exportround[$round_id]->round_date_last  = date('Y-m-d', $datetime);
						}
						if ($exportround[$round_id]->round_date_first && $exportround[$round_id]->round_date_last && $valuematchday)
						{
							$datetime_first = strtotime($exportround[$round_id]->round_date_first);
							$datetime_last  = strtotime($exportround[$round_id]->round_date_last);

							if ($datetime_first > $datetime)
							{
								$exportround[$round_id]->round_date_first = date('Y-m-d', $datetime);
							}
							if ($datetime_last < $datetime)
							{
								$exportround[$round_id]->round_date_last = date('Y-m-d', $datetime);
							}


						}

						$tempmatch->match_number = $csv->data[$a]['Spielkennung'];
						//$tempmatch->match_number = $lfdnumbermatch;
						$tempmatch->published       = 1;
						$tempmatch->count_result    = 1;
						$tempmatch->show_report     = 1;
						$tempmatch->projectteam1_id = 0;
						$tempmatch->projectteam2_id = 0;
						if (isset($csv->data[$a]['Heimmannschaft']))
						{
							$tempmatch->projectteam1_dfbnet = $csv->data[$a]['Heimmannschaft'];
						}
						if (isset($csv->data[$a]['Gastmannschaft']))
						{
							$tempmatch->projectteam2_dfbnet = $csv->data[$a]['Gastmannschaft'];
						}
						if (isset($csv->data[$a]['Heim Mannschaft']))
						{
							$tempmatch->projectteam1_dfbnet = $csv->data[$a]['Heim Mannschaft'];
						}
						if (isset($csv->data[$a]['Gast Mannschaft']))
						{
							$tempmatch->projectteam2_dfbnet = $csv->data[$a]['Gast Mannschaft'];
						}


						$tempmatch->team1_result = '';
						$tempmatch->team2_result = '';
						$tempmatch->summary      = '';

						if (array_key_exists($valueplayground, $exportplaygroundtemp))
						{
							$tempmatch->playground_id = $exportplaygroundtemp[$valueplayground];
						}


						if (empty($tempmatch->projectteam1_dfbnet) || empty($tempmatch->projectteam2_dfbnet))
						{
							/**
							 * keine spiele anfügen
							 */
						}
						else
						{
							if (array_key_exists($tempmatch->match_number, $temp_match_number))
							{
								$exportmatch[] = $tempmatch;
							}
							else
							{
								$temp_match_number[$tempmatch->match_number] = $tempmatch->match_number;
								$exportmatch[]                               = $tempmatch;
							}
						}
						$lfdnumbermatch++;
						$lfdnumber++;
					}

				}
				// ende schleife csv file

				foreach ($exportmatch as $rowmatch)
				{
					foreach ($exportteams as $rowteam)
					{

						if ($rowmatch->projectteam1_dfbnet == $rowteam->name)
						{
							$rowmatch->projectteam1_id = $rowteam->id;
						}
						if ($rowmatch->projectteam2_dfbnet == $rowteam->name)
						{
							$rowmatch->projectteam2_id = $rowteam->id;
						}

					}

				}

				if ($importcsv && sizeof($exportreferee) > 0)
				{
					$temp                   = new stdClass();
					$temp->id               = 1;
					$temp->name             = 'Schiedsrichter';
					$temp->alias            = 'Schiedsrichter';
					$temp->published        = 1;
					$exportparentposition[] = $temp;

					$temp             = new stdClass();
					$temp->id         = 1000;
					$temp->name       = 'Spielleitung';
					$temp->alias      = 'Spielleitung';
					$temp->parent_id  = 1;
					$temp->published  = 1;
					$temp->persontype = 3;
					$exportposition[] = $temp;

					$temp             = new stdClass();
					$temp->id         = 1001;
					$temp->name       = 'Assistent 1';
					$temp->alias      = 'Assistent 1';
					$temp->parent_id  = 1;
					$temp->published  = 1;
					$temp->persontype = 3;
					$exportposition[] = $temp;

					$temp             = new stdClass();
					$temp->id         = 1002;
					$temp->name       = 'Assistent 2';
					$temp->alias      = 'Assistent 2';
					$temp->parent_id  = 1;
					$temp->published  = 1;
					$temp->persontype = 3;
					$exportposition[] = $temp;

					$temp                    = new stdClass();
					$temp->id                = 1000;
					$temp->position_id       = 1000;
					$exportprojectposition[] = $temp;
					$temp                    = new stdClass();
					$temp->id                = 1001;
					$temp->position_id       = 1001;
					$exportprojectposition[] = $temp;
					$temp                    = new stdClass();
					$temp->id                = 1002;
					$temp->position_id       = 1002;
					$exportprojectposition[] = $temp;
				}


				foreach ($exportteams as $rowteam)
				{

					$play_ground = $exportclubsstandardplayground[$rowteam->name];
					$club_id     = $rowteam->club_id;

					foreach ($exportplayground as $rowground)
					{
						if ($play_ground == $rowground->name)
						{
							$play_ground_id = $rowground->id;
							foreach ($exportclubs as $rowclubs)
							{
								if ($club_id == $rowclubs->id)
								{
									$rowclubs->standard_playground = $play_ground_id;
								}
							}

						}
					}


				}

				// von mir
				$this->_datas['position'] = array_merge($exportposition);
				// von mir
				$this->_datas['projectposition'] = array_merge($exportprojectposition);
				// von mir
				$this->_datas['parentposition'] = array_merge($exportparentposition);
				// von mir
				$this->_datas['person'] = array_merge($exportpersons);
				// von mir
				$this->_datas['projectreferee'] = array_merge($exportreferee);
				$this->_datas['team']           = array_merge($exportteams);
				$this->_datas['projectteam']    = array_merge($exportprojectteams);
				$this->_datas['club']           = array_merge($exportclubs);
				$this->_datas['playground']     = array_merge($exportplayground);
				// damit die spieltage in der richtigen reihenfolge angelegt werden
				ksort($exportround);
				$this->_datas['round'] = array_merge($exportround);
				$this->_datas['match'] = array_merge($exportmatch);
				// von mir
				$this->_datas['matchreferee'] = array_merge($exportmatchreferee);

			}

		}


		if ($whichfile == 'playerfile')
		{
			/**
			 * das ganze für den standardimport aufbereiten
			 */
			$output = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
			// open the project
			$output .= "<project>\n";
			// set the version of SportsManagement
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSportsManagementVersion());
			// set the person data
			if (isset($this->_datas['person']))
			{
				$app->enqueueMessage(Text::_('Personen Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['person'], 'Person'));
			}
			// close the project
			$output .= '</project>';
			// mal als test
			$xmlfile = utf8_encode( $output );
			$file    = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'sportsmanagement_import.jlg';
			File::write($file, $xmlfile);
			$this->import_version = 'NEW';
		}
		else
		{
			/**
			 * das ganze für den standardimport aufbereiten
			 */
			$output = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
			// open the project
			$output .= "<project>\n";
			// set the version of SportsManagement
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSportsManagementVersion());
			// set the project datas
			if (isset($this->_datas['project']))
			{
				$app->enqueueMessage(Text::_('Projekt Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setProjectData($this->_datas['project']));
			}

			// set the rounds sportstype
			if (isset($this->_datas['sportstype']))
			{
				$app->enqueueMessage(Text::_('Sportstype Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSportsType($this->_datas['sportstype']));
			}

			// set league data of project
			if (isset($this->_datas['league']))
			{
				$app->enqueueMessage(Text::_('Liga Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setLeagueData($this->_datas['league']));
			}
			// set season data of project
			if (isset($this->_datas['season']))
			{
				$app->enqueueMessage(Text::_('Saison Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSeasonData($this->_datas['season']));
			}
			// set the rounds data
			if (isset($this->_datas['round']))
			{
				$app->enqueueMessage(Text::_('Round Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['round'], 'Round'));
			}
			// set the teams data
			if (isset($this->_datas['team']))
			{
				$app->enqueueMessage(Text::_('Team Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['team'], 'JL_Team'));
			}
			// set the clubs data
			if (isset($this->_datas['club']))
			{
				$app->enqueueMessage(Text::_('Club Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['club'], 'Club'));
			}
			// set the matches data
			if (isset($this->_datas['match']))
			{
				$app->enqueueMessage(Text::_('Match Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['match'], 'Match'));
			}
			// set the positions data
			if (isset($this->_datas['position']))
			{
				// von mir
				$app->enqueueMessage(Text::_('position Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['position'], 'Position'));
			}
			// set the positions parent data
			if (isset($this->_datas['parentposition']))
			{
				// von mir
				$app->enqueueMessage(Text::_('parentposition Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['parentposition'], 'ParentPosition'));
			}
			// set position data of project
			if (isset($this->_datas['projectposition']))
			{
				// von mir
				$app->enqueueMessage(Text::_('projectposition Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectposition'], 'ProjectPosition'));
			}
			// set the matchreferee data
			if (isset($this->_datas['matchreferee']))
			{
				// von mir
				$app->enqueueMessage(Text::_('matchreferee Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['matchreferee'], 'MatchReferee'));
			}
			// set the person data
			if (isset($this->_datas['person']))
			{
				// von mir
				$app->enqueueMessage(Text::_('person Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['person'], 'Person'));
			}
			// set the projectreferee data
			if (isset($this->_datas['projectreferee']))
			{
				// von mir
				$app->enqueueMessage(Text::_('projectreferee Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectreferee'], 'ProjectReferee'));
			}
			// set the projectteam data
			if (isset($this->_datas['projectteam']))
			{
				$app->enqueueMessage(Text::_('projectteam Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectteam'], 'ProjectTeam'));
			}
			// set playground data of project
			if (isset($this->_datas['playground']))
			{
				// von mir
				$app->enqueueMessage(Text::_('playground Daten ' . 'generiert'), '');
				$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['playground'], 'Playground'));
			}

			// close the project
			$output .= '</project>';
			// mal als test
//			$xmlfile = utf8_encode( $output );

			$xmlfile = $output ;
            $file    = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'sportsmanagement_import.jlg';
			File::write($file, $xmlfile);
		}

		$this->import_version = 'NEW';

		if ($this->debug_info)
		{
			echo $this->pane->endPane();
		}

		return $this->_datas;

	}

	/**
	 * sportsmanagementModeljlextdfbnetplayerimport::getProjectUpdateData()
	 *
	 * @param   mixed  $csvdata
	 * @param   mixed  $project
	 *
	 * @return
	 */
	function getProjectUpdateData($csvdata, $project)
	{
		//global $app, $option;
		$app         = Factory::getApplication();
		$document    = Factory::getDocument();
		$exportmatch = array();


		foreach ($csvdata as $row)
		{

			$tempmatch = new stdClass();

			// round_id suchen
			$query = "SELECT r.id
from #__sportsmanagement_round as r
where r.project_id = '$project'
and r.roundcode = '$row->round_id'
";
			$this->_db->setQuery($query);
			$tempmatch->round_id = $this->_db->loadResult();

			$tempmatch->roundcode          = $row->round_id;
			$tempmatch->match_date         = $row->match_date;
			$tempmatch->match_date_verlegt = $row->match_date_verlegt;
			$tempmatch->match_number       = $row->match_number;
			$tempmatch->published          = 1;
			$tempmatch->count_result       = 1;
			$tempmatch->show_report        = 1;

			$tempmatch->projectteam1_dfbnet = $row->projectteam1_dfbnet;
			$tempmatch->projectteam2_dfbnet = $row->projectteam2_dfbnet;

			// projectteam1_id suchen
			$query = "SELECT pt.id
from #__sportsmanagement_project_team as pt
inner join #__sportsmanagement_team as te
on te.id = pt.team_id
where pt.project_id = '$project'
and te.name like '$row->projectteam1_dfbnet'
";
			$this->_db->setQuery($query);
			$tempmatch->projectteam1_id = $this->_db->loadResult();

			// projectteam2_id suchen
			$query = "SELECT pt.id
from #__sportsmanagement_project_team as pt
inner join #__sportsmanagement_team as te
on te.id = pt.team_id
where pt.project_id = '$project'
and te.name like '$row->projectteam2_dfbnet'
";
			$this->_db->setQuery($query);
			$tempmatch->projectteam2_id = $this->_db->loadResult();

			$tempmatch->team1_result = $row->team1_result;
			$tempmatch->team2_result = $row->team2_result;
			$tempmatch->summary      = '';

			$query = "SELECT ma.id
from #__sportsmanagement_match as ma
where ma.round_id = '$tempmatch->round_id'
and ma.projectteam1_id = '$tempmatch->projectteam1_id'
and ma.projectteam2_id = '$tempmatch->projectteam2_id'
";
			$this->_db->setQuery($query);
			$tempmatch->id = $this->_db->loadResult();

			$exportmatch[] = $tempmatch;

		}
		$updatematches = array_merge($exportmatch);

		return $updatematches;
	}

}


?>

