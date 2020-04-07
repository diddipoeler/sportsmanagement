<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       jlextsisimport.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Filesystem\File;

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

JLoader::import('components.com_sportsmanagement.helpers.csvhelper', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.helpers.ical', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.helpers.countries', JPATH_SITE);

jimport('joomla.utilities.utility');



/**
 * sportsmanagementModeljlextsisimport
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModeljlextsisimport extends BaseDatabaseModel
{
	var $_datas = array();

	var $_league_id = 0;

	var $_season_id = 0;

	var $_sportstype_id = 0;

	var $import_version = '';

	var $debug_info = false;

	var $_project_id = 0;

	var $_sis_art = 1;

	var $_sis_datei = '';

	/**
	 * sportsmanagementModeljlextsisimport::getData()
	 *
	 * @return void
	 */
	function getData()
	{
		  // Global $app, $option;
		  $option = Factory::getApplication()->input->getCmd('option');
		  $app = Factory::getApplication();
		  $document    = Factory::getDocument();
		  $post = Factory::getApplication()->input->get('post');

		$country = '';
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

		$lfdnumber = 0;
		$lfdnumberteam = 1;
		$lfdnumbermatch = 1;
		$lfdnumberplayground = 1;
		$lfdnumberperson = 1;
		$lfdnumbermatchreferee = 1;

		$params = ComponentHelper::getParams($option);
		$sis_xmllink    = $params->get('sis_xmllink');
		$sis_nummer    = $params->get('sis_meinevereinsnummer');
		$sis_passwort    = $params->get('sis_meinvereinspasswort');
		/**
		 * test herren : 001514505501506501000000000000000003000
		 * test damen :  001514505501506502000000000000000004000
		 */

		switch ($sis_xmllink)
		{
			case 'http://www.sis-handball.de':
				$country = 'DEU';
			break;
			case 'http://www.sis-handball.at':
				$country = 'AUT';
			break;
		}

			  $liganummer = $post ['liganummer'];
			$teamart = substr($liganummer, 17, 4);

			$db = sportsmanagementHelper::getDBConnection();

			// Create a new query object.
			$query = $db->getQuery(true);
			$query->select(array('id'))
				->from('#__sportsmanagement_sports_type')
				->where('name LIKE ' . "'COM_SPORTSMANAGEMENT_ST_HANDBALL'");
			$db->setQuery($query);
			$sp_id = $db->loadResult();

			$query = $db->getQuery(true);
			$query->select(array('id,name'))
				->from('#__sportsmanagement_agegroup')
				->where('info LIKE ' . "'" . $teamart . "'")
				->where('country LIKE ' . "'" . $country . "'");
			$db->setQuery($query);
			$agegroup = $db->loadObject();
			$linkresults = self::getLink($sis_nummer, $sis_passwort, $liganummer, $this->_sis_art, $sis_xmllink);
			$linkspielplan = self::getSpielplan($linkresults, $liganummer, $this->_sis_art);

			$temp = new stdClass;
			$temp->name = '';
			$this->_datas['season'] = $temp;

			$temp = new stdClass;
			$temp->id = 1;
			$temp->name = 'COM_SPORTSMANAGEMENT_ST_HANDBALL';
			$this->_datas['sportstype'] = $temp;

			$projectname = (string) $linkspielplan->Spielklasse->Name;

			  $temp = new stdClass;
			$temp->name = $projectname;
			$temp->exportRoutine = '2010-09-19 23:00:00';
			$this->_datas['exportversion'] = $temp;

			$temp = new stdClass;
			$temp->name = $projectname;
			$temp->alias = $projectname;
			$temp->short_name = $projectname;
			$temp->middle_name = $projectname;
			$temp->country = $country;
			$this->_datas['league'] = $temp;

			$temp = new stdClass;
			$temp->name = $projectname;
			$temp->staffel_id = $liganummer;
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

			// Spielplan auswerten
		foreach ($linkspielplan->Spiel as $tempspiel)
		{
			// Das spiele
			$tempmatch = new stdClass;
			$tempmatch->id = (string) $tempspiel->Nummer;
			$tempmatch->round_id = (string) $tempspiel->Spieltag;
			$tempmatch->match_date = (string) $tempspiel->Date . " " . (string) $tempspiel->vonUhrzeit;
			$tempmatch->match_number = (string) $tempspiel->Nummer;
			$tempmatch->published = 1;
			$tempmatch->count_result = 1;
			$tempmatch->show_report = 1;
			$tempmatch->projectteam1_id = (string) $tempspiel->HeimNr;
			$tempmatch->projectteam2_id = (string) $tempspiel->GastNr;

			if ((string) $tempspiel->Tore1 && (string) $tempspiel->Tore2)
			{
				$tore_team_1 = array();
				$tore_team_2 = array();
				$tore_team_1[] = (string) $tempspiel->Tore01;
				$tore_team_1[] = (string) $tempspiel->Tore1;
				$tore_team_2[] = (string) $tempspiel->Tore02;
				$tore_team_2[] = (string) $tempspiel->Tore2;
				$tempmatch->team1_result = (string) $tempspiel->Tore1;
				$tempmatch->team1_result_split = implode(";", $tore_team_1);
				$tempmatch->team2_result = (string) $tempspiel->Tore2;
				$tempmatch->team2_result_split = implode(";", $tore_team_2);
			}

			$tempmatch->summary = '';
			$tempmatch->preview = (string) $tempspiel->Anmerkung;
			$tempmatch->playground_id = (string) $tempspiel->Halle;

			$exportmatch[] = $tempmatch;

			// Runden
			if (array_key_exists((string) $tempspiel->Spieltag, $exportround))
			{
			}
			else
			{
				$temp = new stdClass;
				$temp->id = (string) $tempspiel->Spieltag;
				$temp->roundcode = (string) $tempspiel->Spieltag;
				$temp->name = (string) $tempspiel->Spieltag . '. Spieltag';
				$temp->alias = (string) $tempspiel->Spieltag . '-spieltag';
				$temp->round_date_first = '';
				$temp->round_date_last = '';
				$exportround[(string) $tempspiel->Spieltag] = $temp;
			}

			// Personen
			if (array_key_exists((string) $tempspiel->Schiri, $exportpersonstemp))
			{
			}
			else
			{
				$exportpersonstemp[(string) $tempspiel->Schiri] = (string) $tempspiel->GespannName;
				$temp = new stdClass;
				$temp->id = (string) $tempspiel->Schiri;
				$temp->person_id = (string) $tempspiel->Schiri;
				$temp->project_position_id = 1000;
				$exportreferee[] = $temp;

				$temp = new stdClass;
				$temp->id = (string) $tempspiel->Schiri;
				$temp->lastname = (string) $tempspiel->GespannName;
				$temp->firstname = '';
				$temp->nickname = '';
				$temp->knvbnr = (string) $tempspiel->Schiri;
				$temp->unique_id = (string) $tempspiel->Schiri;
				$temp->location = '';
				$temp->birthday = '0000-00-00';
				$temp->country = $country;
				$temp->position_id = 1000;
				$temp->info = 'Schiri';
				$exportpersons[] = $temp;
			}

			// Schiedsrichter
			$tempmatchreferee = new stdClass;
			$tempmatchreferee->id = (string) $tempspiel->Schiri;
			$tempmatchreferee->match_id = (string) $tempspiel->Nummer;
			$tempmatchreferee->project_referee_id = (string) $tempspiel->Schiri;
			$tempmatchreferee->project_position_id = 1000;
			$exportmatchreferee[] = $tempmatchreferee;

			// Sporthallen
			if (array_key_exists((string) $tempspiel->HallenName, $exportplaygroundtemp))
			{
			}
			else
			{
				$exportplaygroundtemp[(string) $tempspiel->HallenName] = (string) $tempspiel->Halle;

				$temp = new stdClass;
				$temp->id = $tempspiel->Halle;
				$temp->name = (string) $tempspiel->HallenName;
				$temp->short_name = (string) $tempspiel->HallenName;
				$temp->alias = (string) $tempspiel->HallenName;
				$temp->club_id = (string) $tempspiel->HeimNr;
				$temp->address = (string) $tempspiel->HallenStrasse;
				$teile = explode(" ", (string) $tempspiel->HallenOrt);
				$temp->zipcode = $teile[0];
				$temp->city = $teile[1];
				$temp->country = $country;
				$temp->max_visitors = 0;
				$exportplayground[] = $temp;
				$exportteamplaygroundtemp[(string) $tempspiel->HeimNr] = (string) $tempspiel->Halle;
			}

			// Heimmannschaft
			if (array_key_exists((string) $tempspiel->Heim, $exportteamstemp))
			{
			}
			else
			{
				$exportteamstemp[(string) $tempspiel->Heim] = (string) $tempspiel->HeimNr;
			}

			// Gastmannschaft
			if (array_key_exists((string) $tempspiel->Gast, $exportteamstemp))
			{
			}
			else
			{
				$exportteamstemp[(string) $tempspiel->Gast] = (string) $tempspiel->GastNr;
			}
		}

			// Teams verarbeiten
		foreach ($exportteamstemp as $key => $value)
		{
			// Team
			$temp = new stdClass;
			$temp->id = $value;
			$temp->club_id = $value;
			$temp->name = $key;
			$temp->middle_name = $key;
			$temp->short_name = $key;
			$temp->info = $agegroup->name;

			$temp->agegroup_id = $agegroup->id;
			$temp->sports_type_id = $sp_id;

			$temp->extended = '';
			$exportteams[] = $temp;

			// $standard_playground = $exportteamplaygroundtemp[$key];
			$standard_playground_nummer = $exportteamplaygroundtemp[$value];

			// Club
			$temp = new stdClass;
			$temp->id = $value;
			$temp->unique_id = $value;
			$temp->name = $key;
			$temp->country = $country;
			$temp->extended = '';
			$temp->standard_playground = $standard_playground_nummer;
			$exportclubs[] = $temp;

			// Projektteam
			$temp = new stdClass;
			$temp->id = $value;
			$temp->team_id = $value;
			$temp->project_team_id = $value;
			$temp->is_in_score = 1;
			$temp->standard_playground = $standard_playground_nummer;
			$exportprojectteams[] = $temp;
		}

			// Spielerpositionen
			$temp = new stdClass;
			$temp->id = 2;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_F_PLAYERS';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_F_PLAYERS';
			$temp->published = 1;
			$exportparentposition[] = $temp;

			$temp = new stdClass;
			$temp->id = 1;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_P_RA';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_P_RA';
			$temp->parent_id = 2;
			$temp->published = 1;
			$temp->persontype = 1;
			$exportposition[] = $temp;

			$temp = new stdClass;
			$temp->id = 2;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_P_HR';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_P_HR';
			$temp->parent_id = 2;
			$temp->published = 1;
			$temp->persontype = 1;
			$exportposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 3;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_P_HM';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_P_HM';
			$temp->parent_id = 2;
			$temp->published = 1;
			$temp->persontype = 1;
			$exportposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 4;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_P_VM';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_P_VM';
			$temp->parent_id = 2;
			$temp->published = 1;
			$temp->persontype = 1;
			$exportposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 5;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_P_IL';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_P_IL';
			$temp->parent_id = 2;
			$temp->published = 1;
			$temp->persontype = 1;
			$exportposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 6;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_P_IR';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_P_IR';
			$temp->parent_id = 2;
			$temp->published = 1;
			$temp->persontype = 1;
			$exportposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 7;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_P_HL';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_P_HL';
			$temp->parent_id = 2;
			$temp->published = 1;
			$temp->persontype = 1;
			$exportposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 8;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_P_AR';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_P_AR';
			$temp->parent_id = 2;
			$temp->published = 1;
			$temp->persontype = 1;
			$exportposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 9;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_P_LA';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_P_LA';
			$temp->parent_id = 2;
			$temp->published = 1;
			$temp->persontype = 1;
			$exportposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 10;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_P_RL';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_P_RL';
			$temp->parent_id = 2;
			$temp->published = 1;
			$temp->persontype = 1;
			$exportposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 11;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_P_RM';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_P_RM';
			$temp->parent_id = 2;
			$temp->published = 1;
			$temp->persontype = 1;
			$exportposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 12;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_P_RR';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_P_RR';
			$temp->parent_id = 2;
			$temp->published = 1;
			$temp->persontype = 1;
			$exportposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 13;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_P_KM';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_P_KM';
			$temp->parent_id = 2;
			$temp->published = 1;
			$temp->persontype = 1;
			$exportposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 14;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_P_KL';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_P_KL';
			$temp->parent_id = 2;
			$temp->published = 1;
			$temp->persontype = 1;
			$exportposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 15;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_P_KR';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_P_KR';
			$temp->parent_id = 2;
			$temp->published = 1;
			$temp->persontype = 1;
			$exportposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 16;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_P_TW';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_P_TW';
			$temp->parent_id = 2;
			$temp->published = 1;
			$temp->persontype = 1;
			$exportposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 17;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_P_AL';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_P_AL';
			$temp->parent_id = 2;
			$temp->published = 1;
			$temp->persontype = 1;
			$exportposition[] = $temp;

			// Schiedsrichterpositionen
			$temp = new stdClass;
			$temp->id = 1;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_F_REFEREES';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_F_REFEREES';
			$temp->published = 1;
			$exportparentposition[] = $temp;

			$temp = new stdClass;
			$temp->id = 1000;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_F_FIELD_REFEREE';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_F_FIELD_REFEREE';
			$temp->parent_id = 1;
			$temp->published = 1;
			$temp->persontype = 3;
			$exportposition[] = $temp;

			$temp = new stdClass;
			$temp->id = 1001;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_F_GOAL_REFEREE';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_F_GOAL_REFEREE';
			$temp->parent_id = 1;
			$temp->published = 1;
			$temp->persontype = 3;
			$exportposition[] = $temp;

			$temp = new stdClass;
			$temp->id = 1002;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_F_TIME_REFEREE';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_F_TIME_REFEREE';
			$temp->parent_id = 1;
			$temp->published = 1;
			$temp->persontype = 3;
			$exportposition[] = $temp;

			$temp = new stdClass;
			$temp->id = 1003;
			$temp->name = 'COM_SPORTSMANAGEMENT_HANDBALL_F_SEKR_REFEREE';
			$temp->alias = 'COM_SPORTSMANAGEMENT_HANDBALL_F_SEKR_REFEREE';
			$temp->parent_id = 1;
			$temp->published = 1;
			$temp->persontype = 3;
			$exportposition[] = $temp;

			$temp = new stdClass;
			$temp->id = 1000;
			$temp->position_id = 1000;
			$exportprojectposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 1001;
			$temp->position_id = 1001;
			$exportprojectposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 1002;
			$temp->position_id = 1002;
			$exportprojectposition[] = $temp;
			$temp = new stdClass;
			$temp->id = 1003;
			$temp->position_id = 1003;
			$exportprojectposition[] = $temp;

			$this->_datas['matchreferee'] = array_merge($exportmatchreferee);
			$this->_datas['position'] = array_merge($exportposition);
			$this->_datas['projectposition'] = array_merge($exportprojectposition);
			$this->_datas['parentposition'] = array_merge($exportparentposition);
			$this->_datas['playground'] = array_merge($exportplayground);
			$this->_datas['team'] = array_merge($exportteams);
			$this->_datas['projectteam'] = array_merge($exportprojectteams);
			$this->_datas['club'] = array_merge($exportclubs);
			$this->_datas['person'] = array_merge($exportpersons);
			$this->_datas['projectreferee'] = array_merge($exportreferee);
			$this->_datas['round'] = array_merge($exportround);
			$this->_datas['match'] = array_merge($exportmatch);

			/**
*
 * das ganze für den standardimport aufbereiten
*/
			$output = '<?xml version="1.0" encoding="utf-8"?>' . "\n";

			// Open the project
			$output .= "<project>\n";

			// Set the version of SportsManagement
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSportsManagementVersion());

			// Set the project datas
		if (isset($this->_datas['project']))
		{
			$app->enqueueMessage(Text::_('project Daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setProjectData($this->_datas['project']));
		}

			// Set league data of project
		if (isset($this->_datas['league']))
		{
			$app->enqueueMessage(Text::_('league Daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setLeagueData($this->_datas['league']));
		}

			// Set season data of project
		if (isset($this->_datas['season']))
		{
			$app->enqueueMessage(Text::_('season Daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSeasonData($this->_datas['season']));
		}

			// Set sportstype data of project
		if (isset($this->_datas['sportstype']))
		{
			$app->enqueueMessage(Text::_('sportstype Daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSportsType($this->_datas['sportstype']));
		}

			// Set the rounds data
		if (isset($this->_datas['round']))
		{
			$app->enqueueMessage(Text::_('round Daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['round'], 'Round'));
		}

			// Set the teams data
		if (isset($this->_datas['team']))
		{
			$app->enqueueMessage(Text::_('team Daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['team'], 'JL_Team'));
		}

			// Set the clubs data
		if (isset($this->_datas['club']))
		{
			$app->enqueueMessage(Text::_('club Daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['club'], 'Club'));
		}

			// Set the matches data
		if (isset($this->_datas['match']))
		{
			$app->enqueueMessage(Text::_('match Daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['match'], 'Match'));
		}

			// Set the positions data
		if (isset($this->_datas['position']))
		{
			$app->enqueueMessage(Text::_('position Daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['position'], 'Position'));
		}

			// Set the positions parent data
		if (isset($this->_datas['parentposition']))
		{
			$app->enqueueMessage(Text::_('parentposition Daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['parentposition'], 'ParentPosition'));
		}

			// Set position data of project
		if (isset($this->_datas['projectposition']))
		{
			$app->enqueueMessage(Text::_('projectposition Daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectposition'], 'ProjectPosition'));
		}

			// Set the matchreferee data
		if (isset($this->_datas['matchreferee']))
		{
			$app->enqueueMessage(Text::_('matchreferee Daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['matchreferee'], 'MatchReferee'));
		}

			// Set the person data
		if (isset($this->_datas['person']))
		{
			$app->enqueueMessage(Text::_('person Daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['person'], 'Person'));
		}

			// Set the projectreferee data
		if (isset($this->_datas['projectreferee']))
		{
			$app->enqueueMessage(Text::_('projectreferee Daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectreferee'], 'ProjectReferee'));
		}

			// Set the projectteam data
		if (isset($this->_datas['projectteam']))
		{
			$app->enqueueMessage(Text::_('projectteam Daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectteam'], 'ProjectTeam'));
		}

			// Set playground data of project
		if (isset($this->_datas['playground']))
		{
			$app->enqueueMessage(Text::_('playground Daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['playground'], 'Playground'));
		}

				  // Close the project
			$output .= '</project>';

			$xmlfile = $output;
			$file = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'sportsmanagement_import.jlg';
			File::write($file, $xmlfile);

	}


	/**
	 * sportsmanagementModeljlextsisimport::getLink()
	 *
	 * @param   mixed $vereinsnummer
	 * @param   mixed $vereinspasswort
	 * @param   mixed $liganummer
	 * @param   mixed $sis_art
	 * @param   mixed $sis_xmllink
	 * @return
	 */
	function getLink($vereinsnummer,$vereinspasswort,$liganummer,$sis_art,$sis_xmllink)
	{
		$sislink = $sis_xmllink . '/xmlexport/xml_dyn.aspx?user=%s&pass=%s&art=%s&auf=%s';
		$link = sprintf($sislink, $vereinsnummer, $vereinspasswort, $sis_art, $liganummer);

		return $link;
	}



	/**
	 * sportsmanagementModeljlextsisimport::getSpielplan()
	 *
	 * @param   mixed $linkresults
	 * @param   mixed $liganummer
	 * @param   mixed $sis_art
	 * @return
	 */
	function getSpielplan($linkresults,$liganummer,$sis_art)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
		/**
*
 * XML File
*/
		$filepath = 'components/' . $option . '/sisdata/';

			  /**
*
 * File laden
*/
		$datei = ($filepath . 'sp_sis_art_' . $sis_art . '_ln_' . $liganummer . '.xml');

		if (file_exists($datei))
		{
			$LetzteAenderung = filemtime($datei);

			if ((time() - $LetzteAenderung) > 1800)
			{
				if (function_exists('curl_version'))
				{
					$curl = curl_init();
					/**
*
 * Define header array for cURL requestes
*/
					$header = array('Contect-Type:application/xml');
					curl_setopt($curl, CURLOPT_URL, $linkresults);
					curl_setopt($curl, CURLOPT_VERBOSE, 1);
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
					$content = curl_exec($curl);
					curl_close($curl);
				}
				elseif (file_get_contents(__FILE__) && ini_get('allow_url_fopen'))
				{
					$content = file_get_contents($linkresults);
				}
				else
				{
					$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'), 'Error');
				}

				/**
*
 * Parsen
*/
				$doc = DOMDocument::loadXML($content);
				/**
*
 * Altes File löschen
*/
				unlink($datei);
				/**
*
 * Speichern
*/
				$doc->save($filepath . 'sp_sis_art_' . $sis_art . '_ln_' . $liganummer . '.xml');
			}
		}
		else
		{
			/**
*
 * Laden
*/
			if (function_exists('curl_version'))
			{
				$curl = curl_init();
				/**
*
 * Define header array for cURL requestes
*/
				$header = array('Contect-Type:application/xml');
				curl_setopt($curl, CURLOPT_URL, $linkresults);
				curl_setopt($curl, CURLOPT_VERBOSE, 1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
				$content = curl_exec($curl);
				curl_close($curl);
			}
			elseif (file_get_contents(__FILE__) && ini_get('allow_url_fopen'))
			{
				$content = file_get_contents($linkresults);
			}
			else
			{
				$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'), 'Error');
			}

			/**
*
 * Parsen
*/
			$doc = DOMDocument::loadXML($content);
			/**
*
 * Speichern
*/
			$doc->save($filepath . 'sp_sis_art_' . $sis_art . '_ln_' . $liganummer . '.xml');
		}

		$result = simplexml_load_file($datei);
		/**
*
 * XML File end
*/
		foreach ($result->Spiel as $temp)
		{
			$nummer = substr($temp->Liga, -3);
			$datum = substr($temp->SpielVon, 0, 10);
			$datum_en = date("d.m.Y", strToTime($datum));
			$temp->Date = $datum;
			$temp->Nummer = $nummer;
			$temp->Datum = $datum_en;
			$temp->vonUhrzeit = substr($temp->SpielVon, 11, 8);
			$temp->bisUhrzeit = substr($temp->SpielBis, 11, 8);
		}

		return $result;
	}

}

