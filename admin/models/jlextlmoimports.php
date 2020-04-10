<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       jlextlmoimports.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Utility\Utility;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;
use Joomla\Utilities\ArrayHelper;

$option        = Factory::getApplication()->input->getCmd('option');
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
	$maxImportMemory = '350M';
}


if ((int) ini_get('memory_limit') < (int) $maxImportMemory)
{
	@ini_set('memory_limit', $maxImportMemory);
}


jimport('joomla.html.pane');


/**
 * sportsmanagementModeljlextlmoimports
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class sportsmanagementModeljlextlmoimports extends BaseDatabaseModel
{
	var $_datas = array();

	var $_league_id = 0;

	var $_season_id = 0;

	var $_sportstype_id = 0;

	var $import_version = '';

	var $debug_info = false;

	/**
	 * sportsmanagementModeljlextlmoimports::__construct()
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
	 * sportsmanagementModeljlextlmoimports::checkStartExtension()
	 *
	 * @return void
	 */
	function checkStartExtension()
	{
		$option        = Factory::getApplication()->input->getCmd('option');
		$app           = Factory::getApplication();
		$user          = Factory::getUser();
		$fileextension = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'lmoimport-2-0.txt';
		$xmlfile       = '';

		if (!File::exists($fileextension))
		{
			$to      = 'diddipoeler@gmx.de';
			$subject = 'LMO-Import Extension';
			$message = 'LMO-Import Extension wurde auf der Seite : ' . Uri::base() . ' gestartet.';
			Utility::sendMail('', Uri::base(), $to, $subject, $message);

			$xmlfile = $xmlfile . $message;
			File::write($fileextension, $xmlfile);
		}

	}

	/**
	 * sportsmanagementModeljlextlmoimports::_getXml()
	 *
	 * @return
	 */
	function _getXml()
	{
		if (File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'sportsmanagement_import.l98'))
		{
			if (function_exists('simplexml_load_file'))
			{
				return @simplexml_load_file(JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'sportsmanagement_import.l98', 'SimpleXMLElement', LIBXML_NOCDATA);
			}
			else
			{
				Log::add(Text::_('<a href="http://php.net/manual/en/book.simplexml.php" target="_blank">SimpleXML</a> does not exist on your system!'), Log::WARNING, 'jsmerror');
			}
		}
		else
		{
			Log::add(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_LMO_ERROR', 'Missing import file'), Log::WARNING, 'jsmerror');
			echo "<script> alert('" . Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_LMO_ERROR', 'Missing import file') . "'); window.history.go(-1); </script>\n";
		}
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return mixed    A JForm object on success, false on failure
	 * @since  1.7
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_sportsmanagement.' . $this->name, $this->name,
			array('load_data' => $loadData)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return mixed    The data for the form.
	 * @since  1.7
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.' . $this->name . '.data', array());

		if (empty($data))
		{
			$data = $this->getData();
		}

		return $data;
	}

	/**
	 * sportsmanagementModeljlextlmoimports::getData()
	 *
	 * @return
	 */
	function getData()
	{

		global $app, $option;
		$app      =& Factory::getApplication();
		$document =& Factory::getDocument();

		$lang  = Factory::getLanguage();
		$teile = explode("-", $lang->getTag());

		$post     = Factory::getApplication()->input->post->getArray(array());
		$country  = $post['country'];
		$agegroup = $post['agegroup'];
		$template = $post['copyTemplate'];

		$app->enqueueMessage(Text::_('land ' . $country . ''), '');

		$option  = Factory::getApplication()->input->getCmd('option');
		$project = $app->getUserState($option . 'project', 0);

		$tempprovorschlag = '';
		$team2_summary    = '';

		if ($project)
		{
			// Projekt wurde mitgegeben, also die liga und alles andere vorselektieren

			$temp                          = new stdClass;
			$temp->exportRoutine           = '2010-09-19 23:00:00';
			$temp->exportSystem            = 'LMO File';
			$this->_datas['exportversion'] = $temp;

			// Projektname
			$query = 'SELECT pro.name,pro.id
FROM #__sportsmanagement_project as pro
WHERE pro.id = ' . (int) $project;
			$this->_db->setQuery($query);
			$row              = $this->_db->loadAssoc();
			$tempprovorschlag = $row['name'];
			$app->enqueueMessage(Text::_('project ' . $tempprovorschlag . ''), '');

			// Saisonname
			$query = 'SELECT se.name,se.id
FROM #__sportsmanagement_season as se
inner join #__sportsmanagement_project as pro
on se.id = pro.season_id
WHERE pro.id = ' . (int) $project;
			$this->_db->setQuery($query);
			$row = $this->_db->loadAssoc();

			$temp                   = new stdClass;
			$temp->id               = $row['id'];
			$temp->name             = $row['name'];
			$this->_datas['season'] = $temp;
			$app->enqueueMessage(Text::_('season ' . $temp->name . ''), '');
			$convert          = array(
				$temp->name => ''
			);
			$tempprovorschlag = str_replace(array_keys($convert), array_values($convert), $tempprovorschlag);

			// Liganame
			$query = 'SELECT le.name,le.country,le.id
FROM #__sportsmanagement_league as le
inner join #__sportsmanagement_project as pro
on le.id = pro.league_id
WHERE pro.id = ' . (int) $project;
			$this->_db->setQuery($query);
			$row = $this->_db->loadAssoc();

			$temp       = new stdClass;
			$temp->id   = $row['id'];
			$temp->name = $row['name'];

			if (!$row['country'])
			{
				$temp->country = 'DEU';
			}
			else
			{
				$temp->country = $row['country'];
			}

			$this->_datas['league'] = $temp;
			$app->enqueueMessage(Text::_('league ' . $temp->name . ''), '');

			//   $temp = new stdClass();
			//   $temp->project_type = 'SIMPLE_LEAGUE';
			//   $temp->namevorschlag = $tempprovorschlag;
			//   $this->_datas['project'] = $temp;

			// Sporttyp
			$query = 'SELECT st.name,st.id
FROM #__sportsmanagement_sports_type as st
inner join #__sportsmanagement_project as pro
on st.id = pro.sports_type_id
WHERE pro.id = ' . (int) $project;
			$this->_db->setQuery($query);
			$row                        = $this->_db->loadAssoc();
			$temp                       = new stdClass;
			$temp->id                   = $row['id'];
			$temp->name                 = $row['name'];
			$this->_datas['sportstype'] = $temp;
			$app->enqueueMessage(Text::_('sportstype ' . $temp->name . ''), '');

			$temp                     = new stdClass;
			$this->_datas['template'] = $temp;
		}
		else
		{
			//   $temp = new stdClass();
			//   $temp->id = 1;
			//   $temp->name = 'Soccer';
			//   $this->_datas['sportstype'] = $temp;
		}

		$temp                       = new stdClass;
		$temp->id                   = 1;
		$temp->name                 = 'COM_SPORTSMANAGEMENT_ST_SOCCER';
		$this->_datas['sportstype'] = $temp;

		$lmoimportuseteams = $app->getUserState($option . 'lmoimportuseteams');

		$teamid = 1;
		$file   = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'sportsmanagement_import.l98';

		$exportplayer          = array();
		$exportclubs           = array();
		$exportteams           = array();
		$exportteamplayer      = array();
		$exportprojectteam     = array();
		$exportreferee         = array();
		$exportprojectposition = array();
		$exportposition        = array();
		$exportparentposition  = array();
		$exportplayground      = array();
		$exportround           = array();
		$exportmatch           = array();
		$exportmatchplayer     = array();
		$exportmatchevent      = array();
		$exportevent           = array();

		$parse = $this->parse_ini_file_ersatz($file);

		// Select options for the project
		foreach ($parse['Options'] AS $key => $value)
		{
			if ($key == 'Title')
			{
				$exportname = $value;
			}

			if ($key == 'Name')
			{
				$projectname = utf8_encode($value);

				if (array_key_exists('season', $this->_datas))
				{
					// Nichts machen
				}
				else
				{
					// Which season ?
					$teile                  = explode(" ", $value);
					$temp                   = new stdClass;
					$temp->name             = array_pop($teile);
					$this->_datas['season'] = $temp;
				}

				if (array_key_exists('league', $this->_datas))
				{
					// Nichts machen
				}
				else
				{
					// Which liga ?
					$temp       = new stdClass;
					$temp->name = $value;

					if (!$country)
					{
						$temp->country = 'DEU';
					}
					else
					{
						$temp->country = $country;
					}

					$this->_datas['league'] = $temp;
				}
			}

			if ($key == 'Rounds')
			{
				$countrounds = $value;
			}

			if ($key == 'Teams')
			{
				$countlmoteams = $value;
			}

			if ($key == 'Matches')
			{
				$matchesperrounds = $value;
			}

			if ($key == 'PointsForWin')
			{
				$Points[] = $value;
			}

			if ($key == 'PointsForDraw')
			{
				$Points[] = $value;
			}

			if ($key == 'PointsForLost')
			{
				$Points[] = $value;
			}
		}

		$temp->points_after_regular_time = implode(",", $Points);
		$temp->name                      = $projectname;
		$temp->namevorschlag             = $tempprovorschlag;
		$temp->serveroffset              = 0;
		$temp->project_type              = 'SIMPLE_LEAGUE';
		$temp->start_time                = '15:30';
		$temp->start_date                = '0000-00-00';
		$temp->sports_type_id            = 1;
		$temp->admin                     = 62;
		$temp->editor                    = 62;
		$temp->timezone                  = 321;

		$temp->master_template = $template;
		$temp->agegroup_id     = $agegroup;

		$temp->current_round_auto = 1;
		$temp->auto_time          = 1440;

		$temp->points_after_add_time = implode(",", $Points);
		$temp->points_after_penalty  = implode(",", $Points);
		$temp->game_regular_time     = 90;
		$temp->game_parts            = 2;

		$this->_datas['project'] = $temp;

		$temp                          = new stdClass;
		$temp->name                    = $exportname;
		$this->_datas['exportversion'] = $temp;

		// Select rounds
		unset($export);
		$matchnumber = 1;

		for ($a = 1; $a <= $countrounds; $a++)
		{
			$spielnummerrunde = 1;
			$lfdmatch         = 1;

			foreach ($parse['Round' . $a] AS $key => $value)
			{
				$temp                     = new stdClass;
				$tempmatch                = new stdClass;
				$tempmatch->playground_id = 0;

				$temp->id        = $a;
				$temp->roundcode = $a;
				$temp->name      = $a . '. Spieltag';
				$temp->alias     = $a . '. Spieltag';

				if ($key == 'D1')
				{
					$round_id         = $a;
					$datetime         = strtotime($value);
					$round_date_first = date('Y-m-d', $datetime);

					if ($a == 1)
					{
						$this->_datas['project']->start_date = $round_date_first;
					}
				}

				if ($key == 'D2')
				{
					$temp->round_date_first = $round_date_first;
					$datetime               = strtotime($value);
					$temp->round_date_last  = date('Y-m-d', $datetime);
					$export[]               = $temp;
					$this->_datas['round']  = array_merge($export);
				}

				if (substr($key, 0, 2) == 'TA')
				{
					$projectteam1_id_lmo = $value;
				}

				if (substr($key, 0, 2) == 'TB')
				{
					$projectteam2_id_lmo = $value;
				}

				if (substr($key, 0, 2) == 'GA')
				{
					if ($value != -1)
					{
						$team1_result = $value;
					}
					else
					{
						$team1_result = '';
					}
				}

				if (substr($key, 0, 2) == 'GB')
				{
					if ($value != -1)
					{
						$team2_result = $value;
					}
					else
					{
						$team2_result = '';
					}
				}

				if (substr($key, 0, 2) == 'NT')
				{
					$team2_summary = utf8_encode($value);

					if (array_key_exists('AT' . $lfdmatch, $parse['Round' . $a]))
					{
					}
					else
					{
						$lfdmatch++;

						$tempmatch->id                  = $matchnumber;
						$tempmatch->round_id            = $round_id;
						$tempmatch->round_id_lmo        = $round_id;
						$tempmatch->match_number        = $matchnumber;
						$tempmatch->published           = 1;
						$tempmatch->count_result        = 1;
						$tempmatch->show_report         = 1;
						$tempmatch->projectteam1_id     = $projectteam1_id_lmo;
						$tempmatch->projectteam2_id     = $projectteam2_id_lmo;
						$tempmatch->projectteam1_id_lmo = $projectteam1_id_lmo;
						$tempmatch->projectteam2_id_lmo = $projectteam2_id_lmo;
						$tempmatch->team1_result        = $team1_result;
						$tempmatch->team2_result        = $team2_result;
						$tempmatch->summary             = $team2_summary;

						if ($projectteam1_id_lmo)
						{
							$exportmatch[] = $tempmatch;
						}

						$matchnumber++;
					}
				}

				if (substr($key, 0, 2) == 'AT')
				{
					$timestamp = $value;

					if ($timestamp)
					{
						$tempmatch->match_date = date('Y-m-d', $timestamp) . " " . date('H:i', $timestamp);
					}

					// 		$tempmatch->match_date = $mazch_date." ".$mazch_time;

					$tempmatch->id                  = $matchnumber;
					$tempmatch->round_id            = $round_id;
					$tempmatch->round_id_lmo        = $round_id;
					$tempmatch->match_number        = $matchnumber;
					$tempmatch->published           = 1;
					$tempmatch->count_result        = 1;
					$tempmatch->show_report         = 1;
					$tempmatch->projectteam1_id     = $projectteam1_id_lmo;
					$tempmatch->projectteam2_id     = $projectteam2_id_lmo;
					$tempmatch->projectteam1_id_lmo = $projectteam1_id_lmo;
					$tempmatch->projectteam2_id_lmo = $projectteam2_id_lmo;
					$tempmatch->team1_result        = $team1_result;
					$tempmatch->team2_result        = $team2_result;
					$tempmatch->summary             = $team2_summary;

					if ($projectteam1_id_lmo)
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

		// Select clubs
		unset($export);
		$teamid = 1;

		foreach ($parse['Teams'] AS $key => $value)
		{
			// Der clubname muss um die mannschaftsnummer verkürzt werden
			if (substr($value, -4, 4) == ' III')
			{
				$convert = array(
					' III' => ''
				);
				$value   = str_replace(array_keys($convert), array_values($convert), $value);
			}

			if (substr($value, -3, 3) == ' II')
			{
				$convert = array(
					' II' => ''
				);
				$value   = str_replace(array_keys($convert), array_values($convert), $value);
			}

			if (substr($value, -2, 2) == ' I')
			{
				$convert = array(
					' I' => ''
				);
				$value   = str_replace(array_keys($convert), array_values($convert), $value);
			}

			if (substr($value, -2, 2) == ' 3')
			{
				$convert = array(
					' 3' => ''
				);
				$value   = str_replace(array_keys($convert), array_values($convert), $value);
			}

			if (substr($value, -2, 2) == ' 2')
			{
				$convert = array(
					' 2' => ''
				);
				$value   = str_replace(array_keys($convert), array_values($convert), $value);
			}

			if (substr($value, -3, 3) == ' 2.')
			{
				$convert = array(
					' 2.' => ''
				);
				$value   = str_replace(array_keys($convert), array_values($convert), $value);
			}

			$convert = array(
				'.' => ' '
			);
			$value   = str_replace(array_keys($convert), array_values($convert), $value);
			$value   = trim($value);

			$temp                      = new stdClass;
			$temp->name                = utf8_encode($value);
			$temp->alias               = $temp->name;
			$temp->id                  = $teamid;
			$temp->info                = '';
			$temp->extended            = '';
			$temp->standard_playground = '';

			if (!$country)
			{
				$temp->country = 'DEU';
			}
			else
			{
				$temp->country = $country;
			}

			foreach ($parse['Team' . $teamid] AS $key => $value)
			{
				if ($key == 'URL')
				{
					$temp->website = $value;
				}
			}

			$export[]             = $temp;
			$this->_datas['club'] = array_merge($export);

			$teamid++;
		}

		// Select teams
		unset($export);
		$teamid = 1;

		foreach ($parse['Teams'] AS $key => $value)
		{
			$convert = array(
				'.' => ' '
			);
			$value   = str_replace(array_keys($convert), array_values($convert), $value);
			$value   = trim($value);

			$temp          = new stdClass;
			$temp->name    = utf8_encode($value);
			$temp->alias   = $temp->name;
			$temp->id      = $teamid;
			$temp->team_id = $teamid;
			$temp->club_id = $teamid;

			// $temp->country = $country;
			$temp->info            = '';
			$temp->extended        = '';
			$temp->is_in_score     = 1;
			$temp->project_team_id = $teamid;

			// Select middle name
			if (array_key_exists('Teamm', $parse))
			{
				foreach ($parse['Teamm'] AS $keymiddle => $valuemiddle)
				{
					if ($key == $keymiddle)
					{
						$temp->middle_name = utf8_encode($valuemiddle);
					}

					if (empty($temp->middle_name))
					{
						$temp->middle_name = $temp->name;
					}
				}
			}

			// Select short name
			if (array_key_exists('Teamk', $parse))
			{
				foreach ($parse['Teamk'] AS $keyshort => $valueshort)
				{
					if ($key == $keyshort)
					{
						$temp->short_name = utf8_encode($valueshort);
					}
				}
			}

			// Add default middle size name
			if (empty($temp->middle_name))
			{
				$parts             = explode(" ", $temp->name);
				$temp->middle_name = substr($parts[0], 0, 20);
			}

			// Add default short size name
			if (empty($temp->short_name))
			{
				$parts            = explode(" ", $temp->name);
				$temp->short_name = substr($parts[0], 0, 2);
			}

			$export[]                    = $temp;
			$this->_datas['team']        = array_merge($export);
			$this->_datas['projectteam'] = array_merge($export);
			$teamid++;
		}

		// Check count teams lmo <-> project
		if ($lmoimportuseteams)
		{
			$query = '	SELECT count(*) as total
FROM #__sportsmanagement_project_team
WHERE project = ' . $project;

			$this->_db->setQuery($query);
			$countteams = $this->_db->loadResult();

			if ($countlmoteams != $countteams)
			{
				$app->enqueueMessage(Text::_('Die Anzahl der Teams im Projekt ' . $project . ' stimmt nicht überein!'), 'Error');
			}
			else
			{
				$app->enqueueMessage(Text::_('Die Anzahl der Teams im Projekt ' . $project . ' stimmt überein!'), 'Notice');
			}
		}

		/**
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
			$app->enqueueMessage(Text::_('project daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setProjectData($this->_datas['project']));
		}

		// Set league data of project
		if (isset($this->_datas['league']))
		{
			$app->enqueueMessage(Text::_('league daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setLeagueData($this->_datas['league']));
		}

		// Set season data of project
		if (isset($this->_datas['season']))
		{
			$app->enqueueMessage(Text::_('season daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSeasonData($this->_datas['season']));
		}

		// Set the rounds sportstype
		if (isset($this->_datas['sportstype']))
		{
			$app->enqueueMessage(Text::_('sportstype daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSportsType($this->_datas['sportstype']));
		}

		// Set the rounds data
		if (isset($this->_datas['round']))
		{
			$app->enqueueMessage(Text::_('round daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['round'], 'Round'));
		}

		// Set the teams data
		if (isset($this->_datas['team']))
		{
			$app->enqueueMessage(Text::_('team daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['team'], 'JL_Team'));
		}

		// Set the clubs data
		if (isset($this->_datas['club']))
		{
			$app->enqueueMessage(Text::_('club daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['club'], 'Club'));
		}

		// Set the matches data
		if (isset($this->_datas['match']))
		{
			$app->enqueueMessage(Text::_('match daten ' . 'generiert'), '');
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['match'], 'Match'));
		}

		// Set the positions data
		if (isset($this->_datas['position']))
		{
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['position'], 'Position'));
		}

		// Set the positions parent data
		if (isset($this->_datas['parentposition']))
		{
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['parentposition'], 'ParentPosition'));
		}

		// Set position data of project
		if (isset($this->_datas['projectposition']))
		{
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectposition'], 'ProjectPosition'));
		}

		// Set the matchreferee data
		if (isset($this->_datas['matchreferee']))
		{
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['matchreferee'], 'MatchReferee'));
		}

		// Set the person data
		if (isset($this->_datas['person']))
		{
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['person'], 'Person'));
		}

		// Set the projectreferee data
		if (isset($this->_datas['projectreferee']))
		{
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectreferee'], 'ProjectReferee'));
		}

		// Set the projectteam data
		if (isset($this->_datas['projectteam']))
		{
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectteam'], 'ProjectTeam'));
		}

		// Set playground data of project
		if (isset($this->_datas['playground']))
		{
			$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['playground'], 'Playground'));
		}

		// Close the project
		$output .= '</project>';

		// Mal als test
		$xmlfile = $output;
		$file    = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'sportsmanagement_import.jlg';
		File::write($file, $xmlfile);

		$this->import_version = 'NEW';

		// $this->import_version='';
		return $this->_datas;

	}

	/**
	 * sportsmanagementModeljlextlmoimports::parse_ini_file_ersatz()
	 *
	 * @param   mixed  $f
	 *
	 * @return
	 */
	function parse_ini_file_ersatz($f)
	{
		$r   = null;
		$sec = null;
		$f   = @file($f);

		for ($i = 0; $i < @count($f); $i++)
		{
			$newsec = 0;
			$w      = @trim($f[$i]);

			if ($w)
			{
				if ((!$r) || ($sec))
				{
					if ((@substr($w, 0, 1) == "[") && (@substr($w, -1, 1)) == "]")
					{
						$sec    = @substr($w, 1, @strlen($w) - 2);
						$newsec = 1;
					}
				}

				if (!$newsec)
				{
					$w = @explode("=", $w);
					$k = @trim($w[0]);
					unset($w[0]);
					$v = @trim(@implode("=", $w));

					if ((@substr($v, 0, 1) == "\"") && (@substr($v, -1, 1) == "\""))
					{
						$v = @substr($v, 1, @strlen($v) - 2);
					}

					if ($sec)
					{
						$r[$sec][$k] = $v;
					}
					else
					{
						$r[$k] = $v;
					}
				}
			}
		}

		return $r;
	}


}


