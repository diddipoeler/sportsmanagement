<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage project
 * @file       project.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Log\Log;

if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

/**
 * prüft vor Benutzung ob die gewünschte Klasse definiert ist
 */
if (!class_exists('sportsmanagementModeldatabasetool'))
{
	JLoader::import('components.com_sportsmanagement.models.databasetool', JPATH_ADMINISTRATOR);

	// Sprachdatei aus dem backend laden
	$langtag      = Factory::getLanguage();
	$document     = Factory::getDocument();
	$app          = Factory::getApplication();
	$config       = Factory::getConfig();
	$lang         = Factory::getLanguage();
	$extension    = 'com_sportsmanagement';
	$base_dir     = JPATH_ADMINISTRATOR;
	$language_tag = $langtag->getTag();
	$reload       = true;
	$lang->load($extension, $base_dir, $language_tag, $reload);

	// Welche tabelle soll genutzt werden
	$paramscomponent           = ComponentHelper::getParams('com_sportsmanagement');
	$database_table            = $paramscomponent->get('cfg_which_database_table');
	$show_debug_info           = $paramscomponent->get('show_debug_info');
	$show_query_debug_info     = $paramscomponent->get('show_query_debug_info');
	$cfg_which_database_server = $paramscomponent->get('cfg_which_database_server');

	if (!defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE'))
	{
		DEFINE('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', $paramscomponent->get('cfg_which_database'));
	}

	if (!defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO'))
	{
		DEFINE('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO', $show_debug_info);
	}

	if (!defined('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO'))
	{
		DEFINE('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO', $show_query_debug_info);
	}
}

/**
 * sportsmanagementModelProject
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelProject extends BaseDatabaseModel
{
	static $_project = null;
	static $projectid = 0;
	static $matchid = 0;
	static $_round_from;
	static $_round_to;
	static $_match = null;
	static $projectnotes = array();
	static $projectwarnings = array();
	static $projecttips = array();
	/**
	 * data array for teams
	 *
	 * @var array
	 */
	static $_teams = null;
	/**
	 * data array for rounds
	 *
	 * @var array
	 */
	static $_rounds = null;
	/**
	 * data project stats
	 *
	 * @var array
	 */
	static $_stats = null;
	/**
	 * data project positions
	 *
	 * @var array
	 */
	static $_positions = null;
	/**
	 * cache for project divisions
	 *
	 * @var array
	 */
	static $_divisions = null;
	/**
	 * caching for current round
	 *
	 * @var object
	 */
	static $_current_round;
	static $seasonid = 0;
	static $cfg_which_database = 0;
	static $favteams = null;
	static $projectslug = '';
	static $divisionslug = '';
	static $roundslug = '';
	static $layout = '';
	/**
	 * project league country
	 *
	 * @var string
	 */
	var $country = null;
	/**
	 * data array for matches
	 *
	 * @var array
	 */
	var $_matches = null;
    
    /** @var    array    An array of tips */
	static $tips = array();
	/** @var    array    An array of warnings */
	static $warnings = array();
    /** @var    array    An array of notes */
	static $notes = array();

	/**
	 * sportsmanagementModelProject::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput                   = $app->input;
		self::$projectid          = $jinput->getVar('p', '0');
		self::$cfg_which_database = $jinput->getVar('cfg_which_database', '0');
		self::$matchid            = $jinput->getVar('mid', '0');
		self::$layout             = $jinput->getVar('layout', '');

		parent::__construct();
	}

	/**
	 * sportsmanagementModelProject::setProjectID()
	 *
	 * @param   integer  $id
	 *
	 * @return void
	 */
	public static function setProjectID($id = 0, $cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		self::$projectid      = (int) $id;
		self::$_project       = null;
		self::$_current_round = 0;

	}

	/**
	 * sportsmanagementModelProject::getSportsType()
	 *
	 * @return
	 */
	public static function getSportsType($cfg_which_database = 0)
	{
		if (!$project = self::getProject($cfg_which_database, __METHOD__))
		{
			$this->setError(0, Text::_('COM_SPORTSMANAGEMENT_ERROR_PROJECTMODEL_PROJECT_IS_REQUIRED'));

			return false;
		}

		return $project->sports_type_id;
	}

	/**
	 * sportsmanagementModelProject::getProject()
	 *
	 * @param   integer  $cfg_which_database
	 * @param   string   $call_function
	 * @param   integer  $inserthits
	 *
	 * @return
	 */
	public static function getProject($cfg_which_database = 0, $call_function = '', $inserthits = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Get a db connection.
		$db        = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query     = $db->getQuery(true);
		$starttime = microtime();

		if (!self::$projectid)
		{
			self::$projectid = $app->input->getInt('p', 0);
		}

		self::updateHits(self::$projectid, $inserthits);

		if (self::$projectid > 0)
		{
			$query->select('p.*, l.country, st.id AS sport_type_id, st.name AS sport_type_name');
			$query->select('st.icon AS sport_type_picture, l.picture as leaguepicture, l.name as league_name, s.name as season_name,r.name as round_name');
			$query->select('LOWER(SUBSTR(st.name, CHAR_LENGTH( "COM_SPORTSMANAGEMENT_ST_")+1)) AS fs_sport_type_name');
			$query->select('CONCAT_WS( \':\', p.id, p.alias ) AS slug');
			$query->select('CONCAT_WS( \':\', l.id, l.alias ) AS league_slug');
			$query->select('CONCAT_WS( \':\', s.id, s.alias ) AS season_slug');
			$query->select('CONCAT_WS( \':\', r.id, r.alias ) AS round_slug');
			$query->select('l.cr_picture as cr_leaguepicture');
			$query->from('#__sportsmanagement_project AS p ');
			$query->join('INNER', '#__sportsmanagement_sports_type AS st ON p.sports_type_id = st.id ');
			$query->join('LEFT', '#__sportsmanagement_league AS l ON p.league_id = l.id ');
			$query->join('LEFT', '#__sportsmanagement_season AS s ON p.season_id = s.id ');
			$query->join('LEFT', '#__sportsmanagement_round AS r ON p.current_round = r.id ');
			$query->where('p.id = ' . (int) self::$projectid);

			$db->setQuery($query, 0, 1);

			self::$_project = $db->loadObject();
			$query->clear();
			$query->select('eventtime');
			$query->from('#__sportsmanagement_sports_type');
			$query->where('id = ' . self::$_project->sports_type_id);
			$db->setQuery($query);

			try
			{
				$useeventtime                 = $db->loadResult();
				self::$_project->useeventtime = $useeventtime;
			}
			catch (Exception $e)
			{
				$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
				$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');
				self::$_project->useeventtime = false;
			}

			if (self::$_project)
			{
				self::$projectslug = self::$_project->slug;
			}

			if (!self::$seasonid && self::$_project)
			{
				self::$seasonid = self::$_project->season_id;
			}
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return self::$_project;
	}

	/**
	 * sportsmanagementModelProject::updateHits()
	 *
	 * @param   integer  $projectid
	 *
	 * @return void
	 */
	public static function updateHits($projectid = 0, $inserthits = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');
		$db     = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query  = $db->getQuery(true);

		if ($inserthits)
		{
			$query->update($db->quoteName('#__sportsmanagement_project'))->set('hits = hits + 1')->where('id = ' . (int) $projectid);

			$db->setQuery($query);

			$result = $db->execute();
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		}

	}

	/**
	 * sportsmanagementModelProject::getCurrentRound()
	 *
	 * @param   mixed    $view
	 * @param   integer  $cfg_which_database
	 *
	 * @return
	 */
	public static function getCurrentRound($view = null, $cfg_which_database = 0)
	{
		$app   = Factory::getApplication();
		$round = self::increaseRound($cfg_which_database);

		self::$_current_round = $round;

		switch ($view)
		{
			case 'result':
				sportsmanagementModelResults::$roundid = self::$_current_round;
				break;
		}

		return ($round ? $round->id : 0);
	}

	/**
	 * method to update and return the project current round
	 *
	 * @return object
	 */
	public static function increaseRound($cfg_which_database = 0)
	{

		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Get a db connection.
		$db      = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query   = $db->getQuery(true);
		$result  = '';
		$project = self::getProject($cfg_which_database, __METHOD__);

		if (!self::$_current_round && $project)
		{
			$current_date = strftime("%Y-%m-%d %H:%M:%S");
			$query->clear();
			$query->select('r.id, r.roundcode, CONCAT_WS( \':\', r.id, r.alias ) AS round_slug');
			$query->from('#__sportsmanagement_round AS r ');

			// Determine current round according to project settings
			switch ($project->current_round_auto)
			{
				case 0 :     // Manual mode
					$query->where('r.id = ' . $project->current_round);
					$query->where('r.project_id = ' . $project->id);
					break;

				case 1 :     // Get current round from round_date_first
					$query->where('r.project_id = ' . $project->id);
					$query->where("(r.round_date_first - INTERVAL " . ($project->auto_time) . " MINUTE < '" . $current_date . "')");
					$query->order('r.round_date_first DESC LIMIT 1');
					break;

				case 2 : // Get current round from round_date_last
					$query->where('r.project_id = ' . $project->id);
					$query->where("(r.round_date_last - INTERVAL " . ($project->auto_time) . " MINUTE < '" . $current_date . "')");
					$query->order('r.round_date_first DESC LIMIT 1');
					break;

				case 3 : // Get current round from first game of the round
					$query->join('INNER', '#__sportsmanagement_match AS m ON m.round_id = r.id');
					$query->where('r.project_id = ' . $project->id);
					$query->where("(m.match_date - INTERVAL " . ($project->auto_time) . " MINUTE < '" . $current_date . "')");
					$query->order('m.match_date DESC LIMIT 1');
					break;

				case 4 : // Get current round from last game of the round
					$query->join('INNER', '#__sportsmanagement_match AS m ON m.round_id = r.id');
					$query->where('r.project_id = ' . $project->id);
					$query->where("(m.match_date + INTERVAL " . ($project->auto_time) . " MINUTE < '" . $current_date . "')");
					$query->order('m.match_date ASC LIMIT 1');
					break;
			}

			try
			{
				$db->setQuery($query);
				$result = $db->loadObject();
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
			}

			// If result is empty, it probably means either this is not started, either this is over, depending on the mode.
			// Either way, do not change current value
			if (!$result)
			{
				$query->clear();
				$query->select('r.id, r.roundcode,CONCAT_WS( \':\', r.id, r.alias ) AS round_slug');
				$query->from('#__sportsmanagement_round AS r ');
				$query->where('r.id = ' . $project->current_round);
				$query->where('r.project_id = ' . $project->id);

				try
				{
					$db->setQuery($query);
					$result = $db->loadObject();
				}
				catch (Exception $e)
				{
					echo $e->getMessage();
				}

				if (!$result)
				{
					$query->clear();
					$query->select('r.id, r.roundcode,CONCAT_WS( \':\', r.id, r.alias ) AS round_slug');
					$query->from('#__sportsmanagement_round AS r ');
					$query->where('r.project_id = ' . $project->id);

					// Determine current round according to project settings
					switch ($project->current_round_auto)
					{
						case 0 :     // Manual mode
						case 2 : // get current round from round_date_last
							// the current value is invalid... saison is over, just take the last round
							$query->order('r.roundcode DESC');
							break;
						default:
							// The current value is invalid... just take the first round
							$query->order('r.roundcode ASC');
							break;
					}

					try
					{
						$db->setQuery($query);
						$result = $db->loadObject();
					}
					catch (Exception $e)
					{
						echo $e->getMessage();
					}
				}
			}

			// Update the database if determined current round is different from that in the database

			if ($result && ($project->current_round <> $result->id))
			{
				// Must be a valid primary key value.
				$object                = new stdClass;
				$object->id            = $project->id;
				$object->current_round = $result->id;

				try
				{
					// Update their details in the users table using id as the primary key.
					$resultupdate = Factory::getDbo()->updateObject('#__sportsmanagement_project', $object, 'id');
				}
				catch (Exception $e)
				{
					$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
					$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');
				}
			}

			self::$_current_round = $result;
		}

		if (!isset(self::$_current_round->round_slug))
		{
			self::$roundslug = '';
		}
		else
		{
			self::$roundslug = self::$_current_round->round_slug;
		}

		return self::$_current_round;
	}

	/**
	 * sportsmanagementModelProject::getCurrentRoundNumber()
	 *
	 * @param   integer  $cfg_which_database
	 *
	 * @return
	 */
	public static function getCurrentRoundNumber($cfg_which_database = 0)
	{
		$app   = Factory::getApplication();
		$round = self::increaseRound($cfg_which_database);

		return ($round ? $round->roundcode : 0);
	}

	/**
	 * sportsmanagementModelProject::getColors()
	 *
	 * @param   string  $configcolors
	 *
	 * @return
	 */
	public static function getColors($configcolors = '', $cfg_which_database = 0)
	{
		// $s=substr($configcolors,0,-1);
		$s    = $configcolors;
		$arr1 = array();

		if (trim($s) != "")
		{
			$arr1 = explode(";", $s);
		}

		$colors = array();

		$colors[0]["from"]        = "";
		$colors[0]["to"]          = "";
		$colors[0]["color"]       = "";
		$colors[0]["description"] = "";

		for ($i = 0; $i < count($arr1); $i++)
		{
			$arr2 = explode(",", $arr1[$i]);

			if (count($arr2) != 4)
			{
				break;
			}

			$colors[$i]["from"]        = $arr2[0];
			$colors[$i]["to"]          = $arr2[1];
			$colors[$i]["color"]       = $arr2[2];
			$colors[$i]["description"] = $arr2[3];
		}

		return $colors;
	}

	/**
	 * sportsmanagementModelProject::getDivision()
	 *
	 * @param   mixed  $id
	 *
	 * @return
	 */
	public static function getDivision($id, $cfg_which_database = 0)
	{
		$divs = self::getDivisions(0, $cfg_which_database);

		if ($divs && isset($divs[$id]))
		{
			return $divs[$id];
		}

		$div       = new stdClass;
		$div->id   = 0;
		$div->name = '';

		return $div;
	}

	/**
	 * sportsmanagementModelProject::getDivisions()
	 *
	 * @param   integer  $divLevel
	 *
	 * @return
	 */
	public static function getDivisions($divLevel = 0, $cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

		$project = self::getProject($cfg_which_database, __METHOD__);

		if ($project)
		{
			if ($project->project_type == 'DIVISIONS_LEAGUE')
			{
				if (empty(self::$_divisions))
				{
					$query->select('*');

					// From
					$query->from('#__sportsmanagement_division');

					// Where
					$query->where('project_id = ' . (int) self::$projectid);

					$db->setQuery($query);
					self::$_divisions = $db->loadObjectList('id');
				}

				if ($divLevel)
				{
					$ids = self::getDivisionsId($divLevel, $cfg_which_database);
					$res = array();

					foreach ($this->_divisions as $d)
					{
						if (in_array($d->id, $ids))
						{
							$res[] = $d;
						}
					}

					return $res;
				}

				return self::$_divisions;
			}
		}

		return array();
	}

	/**
	 * sportsmanagementModelProject::getDivisionsId()
	 *
	 * @param   integer  $divLevel
	 * @param   integer  $cfg_which_database
	 *
	 * @return
	 */
	public static function getDivisionsId($divLevel = 0, $cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		$db    = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__sportsmanagement_division');
		$query->where('project_id = ' . (int) self::$projectid);

		if ($divLevel == 1)
		{
			$query->where('(parent_id=0 OR parent_id IS NULL)');
		}
		elseif ($divLevel == 2)
		{
			$query->where('parent_id > 0');
		}

		$query->order('ordering');
		$db->setQuery($query);

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$res = $db->loadColumn();
		}

		if (count($res) == 0)
		{
			//echo Text::_('COM_SPORTSMANAGEMENT_RANKING_NO_SUBLEVEL_DIVISION_FOUND') . $divLevel;
            self::$warnings[] = Text::_('COM_SPORTSMANAGEMENT_RANKING_NO_SUBLEVEL_DIVISION_FOUND') . $divLevel;
		}

		return $res;
	}

	/**
	 * return project rounds objects ordered by roundcode
	 *
	 * @param   string ordering 'ASC or 'DESC'
	 *
	 * @return array
	 */
	public static function getRounds($ordering = 'ASC', $cfg_which_database = 0, $slug = true)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Get a db connection.
		$db        = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query     = $db->getQuery(true);
		$starttime = microtime();

		if (!self::$projectid)
		{
			self::$projectid = Factory::getApplication()->input->getInt('p', 0);
		}

		if (empty(self::$_rounds))
		{
			if ($slug)
			{
				$query->select('CONCAT_WS( \':\', id, alias ) AS id');
			}
			else
			{
				$query->select('id');
			}

			$query->select('round_date_first,round_date_last,CASE LENGTH(name) when 0 then roundcode else name END as name,roundcode');

			// From
			$query->from('#__sportsmanagement_round');

			// Where
			$query->where('project_id = ' . (int) self::$projectid);

			// Order
			$query->order('roundcode ASC');

			try
			{
				$db->setQuery($query);
				self::$_rounds = $db->loadObjectList();
			}
			catch (Exception $e)
			{
				$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			}
		}

		if ($ordering == 'DESC')
		{
			return array_reverse(self::$_rounds);
		}

		return self::$_rounds;
	}

	/**
	 * sportsmanagementModelProject::getRoundOptions()
	 *
	 * @param   string   $ordering
	 * @param   integer  $cfg_which_database
	 * @param   bool     $slug
	 *
	 * @return
	 */
	public static function getRoundOptions($ordering = 'ASC', $cfg_which_database = 0, $slug = true)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Get a db connection.
		$db        = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query     = $db->getQuery(true);
		$starttime = microtime();

		if (!self::$projectid)
		{
			self::$projectid = Factory::getApplication()->input->getInt('p', 0);
		}

		if ($slug)
		{
			$query->select('CONCAT_WS( \':\', id, alias ) AS slug');
		}

		$query->select('id AS value');
		$query->select("CASE LENGTH(name) when 0 then CONCAT('" . Text::_('COM_SPORTSMANAGEMENT_MATCHDAY_NAME') . "',' ', id) else name END as text");
		$query->from('#__sportsmanagement_round ');
		$query->where('project_id = ' . (int) self::$projectid);
		$query->order('roundcode ' . $ordering);

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * sportsmanagementModelProject::getTeaminfo()
	 *
	 * @param   integer  $projectteamid
	 * @param   integer  $cfg_which_database
	 *
	 * @return
	 */
	public static function getTeaminfo($projectteamid = 0, $cfg_which_database = 0)
	{
		$app       = Factory::getApplication();
		$option    = $app->input->getCmd('option');
		$db        = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query     = $db->getQuery(true);
		$starttime = microtime();

		if ($projectteamid)
		{
			$query->select('t.*,t.id as team_id,t.picture,t.picture as team_picture,t.extended as teamextended ');
			$query->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
			$query->select('pt.division_id,pt.picture AS projectteam_picture');
			$query->select('c.logo_small,c.logo_middle,c.logo_big,c.country');
			$query->from('#__sportsmanagement_project_team AS pt ');
			$query->join('INNER', '#__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
			$query->join('INNER', '#__sportsmanagement_team AS t ON st.team_id = t.id ');
			$query->join('INNER', '#__sportsmanagement_club AS c ON t.club_id = c.id  ');
			$query->where('pt.id = ' . (int) $projectteamid);

			$db->setQuery($query);

			$result = $db->loadObject();
		}
		else
		{
			$result = false;
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;

	}

	/**
	 * sportsmanagementModelProject::getTeamsIndexedById()
	 *
	 * @param   integer  $division
	 * @param   string   $teamname
	 *
	 * @return
	 */
	public static function getTeamsIndexedById($division = 0, $teamname = 'name', $cfg_which_database = 0)
	{
		$result = self::getTeams($division, $teamname, $cfg_which_database);
		$teams  = array();

		if (count($result))
		{
			foreach ($result as $r)
			{
				$teams[$r->id] = $r;
			}
		}

		return $teams;
	}

	/**
	 * sportsmanagementModelProject::getTeams()
	 *
	 * @param   integer  $division
	 * @param   string   $teamname
	 * @param   integer  $cfg_which_database
	 * @param   string   $call_function
	 *
	 * @return
	 */
	public static function getTeams($division = 0, $teamname = 'name', $cfg_which_database = 0, $call_function = '', $playground = 0)
	{
		$app = Factory::getApplication();

		$teams = array();

		if ($division != 0)
		{
			$divids = self::getDivisionTreeIds($division, $cfg_which_database);

			foreach ((array) self::_getTeams($teamname, $cfg_which_database, __METHOD__, $playground) as $t)
			{
				if (in_array($t->division_id, $divids))
				{
					$teams[] = $t;
				}
			}
		}
		else
		{
			$teams = self::_getTeams($teamname, $cfg_which_database, __METHOD__, $playground);
		}

		return $teams;
	}

	/**
	 * sportsmanagementModelProject::_getTeams()
	 *
	 * @param   string   $teamname
	 * @param   integer  $cfg_which_database
	 * @param   string   $call_function
	 *
	 * @return
	 */
	public static function & _getTeams($teamname = 'name', $cfg_which_database = 0, $call_function = '', $playground = 0)
	{
		$app       = Factory::getApplication();
		$option    = $app->input->getCmd('option');
		$db        = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query     = $db->getQuery(true);
		$starttime = microtime();

		$query->select('tl.id AS projectteamid,tl.division_id,tl.standard_playground,tl.admin,tl.start_points,tl.points_finally,tl.neg_points_finally,tl.matches_finally,tl.won_finally,tl.draws_finally,tl.lost_finally');
		$query->select('tl.homegoals_finally,tl.guestgoals_finally,tl.diffgoals_finally,tl.info,tl.reason,tl.team_id as project_team_team_id,tl.checked_out,tl.checked_out_time,tl.is_in_score,tl.picture AS projectteam_picture');
		$query->select('IF((ISNULL(tl.picture) OR (tl.picture="")),(IF((ISNULL(t.picture) OR (t.picture="")), c.logo_small , t.picture)) , t.picture) as picture,tl.project_id');
		$query->select('t.picture as team_picture,t.id,t.name,t.short_name,t.middle_name,t.notes,t.club_id');
		$query->select('u.username,u.email');
		$query->select('st.team_id');
		$query->select('c.email as club_email,c.phone as club_phone,c.fax as club_fax,c.logo_small,c.logo_middle,c.logo_big,c.country,c.website,c.new_club_id,c.facebook,c.twitter');
		$query->select('d.name AS division_name,d.shortname AS division_shortname,d.parent_id AS parent_division_id');
		$query->select('plg.name AS playground_name,plg.short_name AS playground_short_name, c.trikot_home, c.trikot_away');
		$query->select('CONCAT_WS(\':\',p.id,p.alias) AS project_slug');
		$query->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
		$query->select('CONCAT_WS(\':\',tl.id,t.alias) AS projectteam_slug');
		$query->select('CONCAT_WS(\':\',d.id,d.alias) AS division_slug');
		$query->select('CONCAT_WS(\':\',c.id,c.alias) AS club_slug');

		if ($playground)
		{
			$query->select('plg.picture as playground_picture');
			$query->select('CONCAT_WS( \':\', plg.id, plg.alias ) AS playground_slug');
		}

		// Für die anzeige der teams im frontend
		$query->select('t.name as team_name,t.short_name,t.middle_name,t.club_id,t.website AS team_www,t.picture as team_picture,c.name as club_name,c.address as club_address');
		$query->select('c.zipcode as club_zipcode,c.state as club_state,c.location as club_location,c.unique_id,c.country as club_country,c.website AS club_www');

		$query->from('#__sportsmanagement_project_team AS tl ');
		$query->join('LEFT', ' #__sportsmanagement_season_team_id st ON st.id = tl.team_id ');
		$query->join('LEFT', ' #__sportsmanagement_team t ON st.team_id = t.id ');
		$query->join('LEFT', ' #__users u ON tl.admin=u.id ');
		$query->join('LEFT', ' #__sportsmanagement_club c ON t.club_id = c.id ');
		$query->join('LEFT', ' #__sportsmanagement_division d ON d.id = tl.division_id ');
		$query->join('LEFT', ' #__sportsmanagement_playground plg ON plg.id = tl.standard_playground ');
		$query->join('LEFT', ' #__sportsmanagement_project AS p ON p.id = tl.project_id ');
		$query->where('tl.project_id = ' . (int) self::$projectid);

		$db->setQuery($query);

		self::$_teams = $db->loadObjectList();
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return self::$_teams;
	}

	/**
	 * return an array of division id and it's subdivision ids
	 *
	 * @param   int division id
	 *
	 * @return integer
	 */
	public static function getDivisionTreeIds($divisionid, $cfg_which_database = 0)
	{
		if ($divisionid == 0)
		{
			return self::getDivisionsId(0, $cfg_which_database);
		}

		$divisions = self::getDivisions(0, $cfg_which_database);
		$res       = array($divisionid);

		foreach ($divisions as $d)
		{
			if ($d->parent_id == $divisionid)
			{
				$res[] = $d->id;
			}
		}

		return $res;
	}

	/**
	 * sportsmanagementModelProject::getTeamsIndexedByPtid()
	 *
	 * @param   integer  $division
	 * @param   string   $teamname
	 *
	 * @return
	 */
	public static function getTeamsIndexedByPtid($division = 0, $teamname = 'name', $cfg_which_database = 0, $call_function = '')
	{
		$app = Factory::getApplication();

		$result = self::getTeams($division, $teamname, $cfg_which_database, $call_function);
		$teams  = array();

		if (count($result))
		{
			foreach ($result as $r)
			{
				$teams[$r->projectteamid] = $r;
			}
		}

		return $teams;
	}

	/**
	 * sportsmanagementModelProject::getFavTeams()
	 *
	 * @return
	 */
	public static function getFavTeams($cfg_which_database = 0)
	{
		$project = self::getProject($cfg_which_database, __METHOD__);

		if (!is_null($project))
		{
			self::$favteams = explode(",", $project->fav_team);

			return self::$favteams;
		}
		else
		{
			return array();
		}
	}

	
	/**
	 * sportsmanagementModelProject::getEventTypes()
	 * 
	 * @param string $evid
	 * @param integer $cfg_which_database
	 * @param integer $sports_type_id
	 * @return
	 */
	public static function getEventTypes($evid = '', $cfg_which_database = 0, $sports_type_id = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		$db    = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

		$query->select('et.id AS etid,et.name,et.icon');
		$query->select('me.event_type_id AS id');
		$query->select('CONCAT_WS( \':\', et.id, et.alias ) AS event_slug');
		$query->from('#__sportsmanagement_eventtype AS et');
		$query->join('LEFT', '#__sportsmanagement_match_event AS me ON et.id = me.event_type_id');

		if ($evid)
		{
		$query->where("me.event_type_id IN (" . $evid . ")");
		}
        if ( $sports_type_id )
        {
        $query->where('et.sports_type_id = ' . $sports_type_id);
        }

		$db->setQuery($query);
		$result = $db->loadObjectList('etid');
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}

	/**
	 * sportsmanagementModelProject::getprojectteamID()
	 *
	 * @param   mixed  $teamid
	 *
	 * @return
	 */
	public static function getprojectteamID($teamid, $cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		if (!self::$projectid)
		{
			self::$projectid = Factory::getApplication()->input->getInt('p', 0);
		}

		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__sportsmanagement_project_team');
		$query->where('team_id = ' . (int) $teamid);
		$query->where('project_id = ' . (int) self::$projectid);
		$db->setQuery($query);
		$result = $db->loadResult();
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}

	/**
	 * sportsmanagementModelProject::getOverallConfig()
	 *
	 * @return
	 */
	public static function getOverallConfig($cfg_which_database = 0)
	{
		return self::getTemplateConfig('overall', $cfg_which_database, __METHOD__);
	}

	/**
	 * sportsmanagementModelProject::getTemplateConfig()
	 *
	 * @param   mixed  $template
	 *
	 * @return
	 */
	public static function getTemplateConfig($template, $cfg_which_database = 0, $call_function = '')
	{
		$app           = Factory::getApplication();
		$option        = $app->input->getCmd('option');
		$view          = $app->input->getVar("view");
		$db            = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query         = $db->getQuery(true);
		$checktemplate = false;

		switch ($view)
		{
			case 'editmatch':
			case 'editprojectteam':
			case 'editteam':
			case 'editperson':
			case 'editclub':
			case 'jltournamenttree':
				break;
			default:
				$checktemplate = true;
				break;
		}

		/**
		 *
		 * first load the default settings from the default <template>.xml file
		 */
		$paramsdata          = "";
		$arrStandardSettings = array();

		$xmlfile = JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . $template . '.xml';

		if (self::$projectid == 0)
		{
			return $arrStandardSettings;
		}

		$query->select('t.params');
		$query->from('#__sportsmanagement_template_config AS t');
		$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = t.project_id');
		$query->where('t.template LIKE ' . $db->Quote($template));
		$query->where('p.id = ' . (int) self::$projectid);

		$starttime = microtime();
		$db->setQuery($query);
		$result = $db->loadResult();

		if ($checktemplate)
		{
			if (!$result)
			{
				$project = self::getProject($cfg_which_database, __METHOD__);

				if (!empty($project) && $project->master_template > 0)
				{
					$query->clear();
					$query->select('t.params');
					$query->from('#__sportsmanagement_template_config AS t');
					$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = t.project_id');
					$query->where('t.template LIKE ' . $db->Quote($template));
					$query->where('p.id = ' . $project->master_template);

					$starttime = microtime();
					$db->setQuery($query);
					$result = $db->loadResult();

					if (!$result)
					{
self::$projectwarnings[] = Text::_('COM_SPORTSMANAGEMENT_MASTER_TEMPLATE_MISSING') . " " . $template;
self::$projectwarnings[] = Text::_('COM_SPORTSMANAGEMENT_MASTER_TEMPLATE_MISSING_PID') . $project->master_template;
self::$projectwarnings[] = Text::_('COM_SPORTSMANAGEMENT_TEMPLATE_MISSING_HINT');
					

						return $arrStandardSettings;
					}
				}
				else
				{
					/**
					 *
					 * there are no saved settings found, use the standard xml file default values
					 */
					return $arrStandardSettings;
				}
			}
		}

		$jRegistry = new Registry;

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$jRegistry->loadString($result);
		}
		else
		{
			$jRegistry->loadJSON($result);
		}

		$configvalues = $jRegistry->toArray();

		/**
		 *
		 * merge and overwrite standard settings with individual view settings
		 */
		$settings = array_merge($arrStandardSettings, $configvalues);
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $settings;

	}

	/**
	 * return events assigned to the project
	 *
	 * @param   int position_id if specified,returns only events assigned to this position
	 *
	 * @return array
	 */
	public static function getProjectEvents($position_id = 0, $cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

		$query->select('et.id,et.name,et.icon');
		$query->from('#__sportsmanagement_eventtype AS et');
		$query->join('INNER', '#__sportsmanagement_position_eventtype AS pet ON pet.eventtype_id = et.id ');
		$query->join('INNER', '#__sportsmanagement_project_position AS ppos ON ppos.position_id = pet.position_id ');
		$query->where('ppos.project_id = ' . (int) self::$projectid);

		if ($position_id)
		{
			$query->where('ppos.position_id = ' . (int) $position_id);
		}

		$query->group('et.id,et.name,et.icon');

		try
		{
			$db->setQuery($query);
			$events = $db->loadObjectList('id');
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');
			$events = false;
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $events;
	}

	/**
	 * returns stats assigned to positions assigned to project
	 *
	 * @param   int statid 0 for all stats
	 * @param   int positionid 0 for all positions
	 *
	 * @return array objects
	 */
	public static function getProjectStats($statid = 0, $positionid = 0, $cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

		if (empty(self::$_stats))
		{
			include_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'statistics' . DIRECTORY_SEPARATOR . 'base.php';
			$project    = self::getProject($cfg_which_database, __METHOD__);
			$project_id = $project->id;
			$query->select('ppos.id as pposid,ppos.position_id AS position_id');
			$query->select('stat.id,stat.name,stat.short,stat.class,stat.icon,stat.calculated,stat.params,stat.baseparams,stat.ordering');
			$query->from('#__sportsmanagement_statistic AS stat');
			$query->join('INNER', '#__sportsmanagement_position_statistic AS ps ON ps.statistic_id = stat.id ');
			$query->join(
				'INNER', '#__sportsmanagement_project_position AS ppos ON ppos.position_id = ps.position_id
						  AND ppos.project_id=' . $project_id
			);
			$query->join('INNER', '#__sportsmanagement_position AS pos ON pos.id = ps.position_id');

			if ($statid)
			{
				$query->where('stat.id = ' . (int) $statid);
			}

			$query->where('stat.published = 1');
			$query->where('pos.published = 1');
			$query->order('pos.ordering,ps.ordering');

			try
			{
				$db->setQuery($query);
				self::$_stats = $db->loadObjectList();
			}
			catch (Exception $e)
			{
				$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			}
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		// Sort into positions
		$positions = self::getProjectPositions($cfg_which_database);

		$stats = array();

		if (count(self::$_stats) > 0)
		{
			foreach (self::$_stats as $k => $row)
			{
				if (!$statid || $statid == $row->id || (is_array($statid) && in_array($row->id, $statid)))
				{
					$stat = SMStatistic::getInstance($row->class);
					$stat->bind($row);
					$stat->set('position_id', $row->position_id);
					$stats[$row->position_id][$row->id] = $stat;
				}
			}

			if ($positionid)
			{
				return (isset($stats[$positionid]) ? $stats[$positionid] : array());
			}
			else
			{
				return $stats;
			}
		}
		else
		{
			return $stats;
		}
	}

	/**
	 * sportsmanagementModelProject::getProjectPositions()
	 *
	 * @return
	 */
	public static function getProjectPositions($cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

		if (empty(self::$_positions))
		{
			$query->select('pos.id,pos.persontype,pos.name,pos.ordering,pos.published');
			$query->select('ppos.id AS pposid');
			$query->from('#__sportsmanagement_project_position AS ppos');
			$query->join('INNER', '#__sportsmanagement_position AS pos ON ppos.position_id = pos.id');
			$query->where('ppos.project_id = ' . (int) self::$projectid);
			$query->order('pos.persontype,pos.ordering');

			try
			{
				$db->setQuery($query);
				self::$_positions = $db->loadObjectList('id');
			}
			catch (Exception $e)
			{
				$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			}
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return self::$_positions;
	}

	/**
	 * sportsmanagementModelProject::getClubIconHtml()
	 *
	 * @param   mixed    $team
	 * @param   integer  $type
	 * @param   integer  $with_space
	 * @param   string   $club_icon
	 * @param   integer  $cfg_which_database
	 * @param   integer  $roundcode
	 *
	 * @return
	 */
	public static function getClubIconHtml(&$team, $type = 1, $with_space = 0, $club_icon = 'logo_big', $cfg_which_database = 0, $roundcode = 0, $modalwidth = '100', $modalheight = '200', $use_jquery_modal = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		if ($type == 1)
		{
			if (!sportsmanagementHelper::existPicture($team->$club_icon))
			{
				$team->$club_icon = sportsmanagementHelper::getDefaultPlaceholder($club_icon);
			}

			$image = sportsmanagementHelperHtml::getBootstrapModalImage(
				$roundcode . 'team' . $team->team_id,
				$team->$club_icon,
				$team->name,
				'20',
				'',
				$modalwidth,
				$modalheight,
				$use_jquery_modal
			);

			return $image;
		}
		elseif (($type == 2) && (isset($team->country)))
		{
			return JSMCountries::getCountryFlag($team->country);
		}
	}

	/**
	 * returns match substitutions
	 *
	 * @param   int match id
	 *
	 * @return array
	 */
	public static function getMatchSubstitutions($match_id, $cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

		$query->clear();
		$query->select('mp.in_out_time,mp.teamplayer_id,mp.in_for,mp.project_position_id');
		$query->from('#__sportsmanagement_match_player AS mp');
		$query->where('mp.match_id = ' . (int) $match_id);
		$query->where('mp.came_in > 0');
		$query->order('mp.in_out_time');
		$db->setQuery($query);
		$result = $db->loadObjectList();

		foreach ($result AS $inout)
		{
			$query->clear();
			$query->select('p.firstname,p.nickname,p.lastname,p.id AS playerid');
			$query->select('CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS person_slug');
			$query->from('#__sportsmanagement_person AS p');
			$query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp1 ON tp1.person_id = p.id');
			$query->where('tp1.id = ' . $inout->teamplayer_id);
			$db->setQuery($query);
			$result1 = $db->loadObject();

			$inout->firstname       = $result1->firstname;
			$inout->nickname        = $result1->nickname;
			$inout->lastname        = $result1->lastname;
			$inout->playerid        = $result1->playerid;
			$inout->person_id       = $result1->person_slug;
			$inout->sub_person_slug = $result1->person_slug;

			$query->clear();
			$query->select('pos.id,pos.name');
			$query->from('#__sportsmanagement_position AS pos');
			$query->where('pos.id = ' . $inout->project_position_id);
			$db->setQuery($query);
			$result1 = $db->loadObject();

			$inout->in_position = $result1->name;

			$query->clear();
			$query->select('ppos.id');
			$query->from('#__sportsmanagement_project_position AS ppos');
			$query->where('ppos.position_id = ' . $result1->id);
			$query->where('ppos.project_id = ' . (int) self::$projectid);

			try
			{
				$db->setQuery($query);
				$result2        = $db->loadObject();
				$inout->pposid1 = $result2->id;
			}
			catch (Exception $e)
			{
				// Catch any database errors.
				$inout->pposid1 = 0;
			}

			$query->clear();
			$query->select('p.firstname AS out_firstname,p.nickname AS out_nickname,p.lastname AS out_lastname,p.id AS out_ptid');
			$query->select('CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS person_slug');
			$query->from('#__sportsmanagement_person AS p');
			$query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp1 ON tp1.person_id = p.id');
			$query->where('tp1.id = ' . $inout->in_for);
			try
			{
			$db->setQuery($query);
			$result1 = $db->loadObject();
			$inout->out_firstname = $result1->out_firstname;
			$inout->out_nickname  = $result1->out_nickname;
			$inout->out_lastname  = $result1->out_lastname;
			$inout->out_ptid      = $result1->out_ptid;
			$inout->out_person_id = $result1->person_slug;
			$inout->person_slug   = $result1->person_slug;
			}
			catch (Exception $e)
			{
			// Catch any database errors.
			$inout->out_firstname = '';
			$inout->out_nickname  = '';
			$inout->out_lastname  = '';
			$inout->out_ptid      = 0;
			$inout->out_person_id = '';
			$inout->person_slug   = '';	
			}

			$query->clear();
			$query->select('pos.id,pos.name');
			$query->from('#__sportsmanagement_position AS pos');
			$query->join('INNER', '#__sportsmanagement_match_player AS mp ON mp.project_position_id = pos.id ');
			$query->where('mp.teamplayer_id = ' . $inout->in_for);
			$query->where('mp.match_id = ' . (int) $match_id);
			try
			{
			$db->setQuery($query);
			$result1 = $db->loadObject();
			$inout->out_position = $result1->name;
			}
			catch (Exception $e)
			{
			// Catch any database errors.
			$inout->out_position = '';
			}

			$query->clear();
			$query->select('ppos.id');
			$query->from('#__sportsmanagement_project_position AS ppos');
			$query->where('ppos.position_id = ' . $result1->id);
			$query->where('ppos.project_id = ' . (int) self::$projectid);

			try
			{
				$db->setQuery($query);
				$result2 = $db->loadObject();

				$inout->pposid2 = $result2->id;
			}
			catch (Exception $e)
			{
				// Catch any database errors.
				$inout->pposid2 = 0;
			}

			$query->clear();
			$query->select('pt.team_id,pt.id AS ptid');
			$query->select('CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\',t.id,t.alias) ELSE t.id END AS team_slug');
			$query->from('#__sportsmanagement_project_team AS pt');
			$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt.team_id ');
			$query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp1 ON tp1.team_id = st1.team_id');
			$query->join('INNER', '#__sportsmanagement_team AS t ON t.id = st1.team_id');
			$query->where('pt.project_id = ' . (int) self::$projectid);
			$query->where('tp1.id = ' . $inout->teamplayer_id);
			$db->setQuery($query);
			$result1          = $db->loadObject();
			$inout->ptid      = $result1->ptid;
			$inout->team_id   = $result1->team_slug;
			$inout->team_slug = $result1->team_slug;
		}

		if (!$result)
		{
		}

		return $result;
	}

	/**
	 * sportsmanagementModelProject::getMatch()
	 *
	 * @return
	 */
	public static function getMatch()
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;

		// Get a refrence of the page instance in joomla
		$document = Factory::getDocument();
		$option   = $jinput->getCmd('option');

		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

		if (is_null(self::$_match))
		{
			$query->select('m.*,DATE_FORMAT(m.time_present,"%H:%i") time_present, r.project_id, p.timezone ');
			$query->from('#__sportsmanagement_match AS m ');
			$query->join('INNER', '#__sportsmanagement_round AS r on r.id = m.round_id ');
			$query->join('INNER', '#__sportsmanagement_project AS p on r.project_id = p.id ');
			$query->where('m.id = ' . (int) self::$matchid);

			$db->setQuery($query, 0, 1);
			self::$_match = $db->loadObject();

			if (self::$_match)
			{
				sportsmanagementHelper::convertMatchDateToTimezone(self::$_match);
			}
		}

		return self::$_match;
	}

	/**
	 * returns match events
	 *
	 * @param   int match id
	 *
	 * @return array
	 */
	public static function getMatchEvents($match_id, $showcomments = 0, $sortdesc = 0, $cfg_which_database = 0)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;

		// Get a refrence of the page instance in joomla
		$document = Factory::getDocument();
		$option   = $jinput->getCmd('option');

		// Get a db connection.
		$db        = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query     = $db->getQuery(true);
		$starttime = microtime();

		if ($showcomments == 1)
		{
			$join = 'LEFT';

			// $addline = ' me.notes,';
			$query->select('me.notes');
		}
		else
		{
			$join = 'INNER';

			// $addline = '';
		}

		$esort           = '';
		$arrayobjectsort = '1';

		if ($sortdesc == 1)
		{
			$esort           = ' DESC';
			$arrayobjectsort = '-1';
		}

		$query->select('me.event_type_id,me.id as event_id,me.event_time,me.notice,me.projectteam_id AS ptid,me.event_sum');
		$query->select('CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\',t.id,t.alias) ELSE t.id END AS team_id');
		$query->select('et.name AS eventtype_name');
		$query->select('t.name AS team_name');
		$query->select('tp.picture AS tppicture1');
		$query->select('p.firstname AS firstname1,p.nickname AS nickname1,p.lastname AS lastname1,p.picture AS picture1');
		$query->select('CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS playerid');

		// From
		$query->from('#__sportsmanagement_match_event AS me');
		$query->join($join, '#__sportsmanagement_eventtype AS et ON me.event_type_id = et.id');
		$query->join($join, '#__sportsmanagement_project_team AS pt ON me.projectteam_id = pt.id');
		$query->join($join, '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id');
		$query->join($join, '#__sportsmanagement_team AS t ON st.team_id = t.id');
		$query->join($join, '#__sportsmanagement_season_team_person_id AS tp ON tp.team_id = st.team_id AND tp.id = me.teamplayer_id');
		$query->join($join, '#__sportsmanagement_person AS p ON tp.person_id = p.id');

		// Where
		$query->where('me.match_id = ' . (int) $match_id);
		$query->where('p.published = 1');
		$query->group('me.event_type_id,me.id,me.event_time,me.notice,me.event_sum,me.projectteam_id,t.alias, t.id, et.name, t.name, p.picture, tp.picture,p.firstname, p.nickname, p.lastname, p.alias, p.id');

		// Order
		$query->order('(me.event_time + 0)' . $esort . ', me.event_type_id, me.id');

		try
		{
			$db->setQuery($query);
			$events = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');
			$events = false;
		}

		if (!$events)
		{
		}

		$query = $db->getQuery(true);
		$query->clear();

		$query->select('*');

		// From
		$query->from('#__sportsmanagement_match_commentary');

		// Where
		$query->where('match_id = ' . (int) $match_id);

		$db->setQuery($query);
		$commentary = $db->loadObjectList();

		if ($commentary)
		{
			foreach ($commentary as $comment)
			{
				$temp                = new stdClass;
				$temp->event_type_id = 0;
				$temp->event_sum     = $comment->type;
				$temp->event_time    = $comment->event_time;
				$temp->notes         = $comment->notes;
				$events[]            = $temp;
			}
		}

		if ($events)
		{
			$events = ArrayHelper::sortObjects($events, 'event_time', $arrayobjectsort);
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $events;
	}

	/**
	 * sportsmanagementModelProject::hasEditPermission()
	 *
	 * @param   mixed  $task
	 *
	 * @return
	 */
	public static function hasEditPermission($task = null, $cfg_which_database = 0)
	{
		$app     = Factory::getApplication();
		$option  = $app->input->getCmd('option');
		$allowed = false;
		$user    = Factory::getUser();

		// Ist der user der einer gruppe zugeordnet ?
		$groups = JUserHelper::getUserGroups($user->get('id'));

		if ($user->id > 0)
		{
			if (!is_null($task))
			{
				if (!$user->authorise($task, $option))
				{
					$allowed = false;
					$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_CLUBINFO_PAGE_ERROR_ACL_PERMISSION', $task), 'Error');
				}
				else
				{
					$allowed = true;
				}
			}

			// If no ACL permission, check for overruling permission by project admin/editor (compatibility < 2.5)
			if (!$allowed)
			{
				// If not, then check if user is project admin or editor
				$project = self::getProject($cfg_which_database, __METHOD__);

				if (self::isUserProjectAdminOrEditor($user->id, $project))
				{
					$allowed = true;
				}
				else
				{
					$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_PAGE_ERROR_ADMIN_EDITOR'), 'Error');
				}
			}
		}

		return $allowed;
	}

	/**
	 * sportsmanagementModelProject::isUserProjectAdminOrEditor()
	 *
	 * @param   integer  $userId
	 * @param   mixed    $project
	 *
	 * @return
	 */
	public static function isUserProjectAdminOrEditor($userId = 0, $project, $cfg_which_database = 0)
	{
		$result = false;

		if ($userId > 0)
		{
			$result = ($userId == $project->admin || $userId == $project->editor);
		}

		return $result;
	}

	/**
	 * return array of team ids
	 *
	 * @return array     *
	 */
	function getTeamIds($division = 0, $cfg_which_database = 0)
	{
		$teams = array();

		foreach ((array) self::_getTeams($teamname, $cfg_which_database, __METHOD__) as $t)
		{
			if (!$division || $t->division_id == $division)
			{
				$teams[] = $t->id;
			}
		}

		return $teams;
	}

	/**
	 * Method to return a playgrounds array (id,name)
	 *
	 * @access public
	 * @return array
	 * @since  0.1
	 */
	function getPlaygrounds($cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);
		$query->select('id AS value,name AS text');
		$query->from('#__sportsmanagement_playground');
		$query->order('text ASC');

		$db->setQuery($query);
		$result = $db->loadObjectList();
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}

	/**
	 * sportsmanagementModelProject::getProjectGameRegularTime()
	 *
	 * @param   mixed  $project_id
	 *
	 * @return
	 */
	function getProjectGameRegularTime($project_id, $cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Get a db connection.
		$db              = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query           = $db->getQuery(true);
		$gameprojecttime = 0;
		$query->select('game_regular_time');
		$query->from('#__sportsmanagement_project');
		$query->where('id = ' . (int) $project_id);
		$db->setQuery($query);
		$result = $db->loadObject();
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		$gameprojecttime += $result->game_regular_time;

		if ($result->allow_add_time)
		{
			$gameprojecttime += $result->add_time;
		}

		return $gameprojecttime;
	}

	/**
	 * sportsmanagementModelProject::getReferees()
	 *
	 * @return
	 */
	function getReferees($cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

		$project = self::getProject($cfg_which_database, __METHOD__);

		if ($project->teams_as_referees)
		{
			$query->select('id AS value,name AS text');
			$query->from('#__sportsmanagement_team');
			$query->order('name');

			$db->setQuery($query);
			$refs = $db->loadObjectList();
		}
		else
		{
			$query->select('id AS value,firstname,lastname');
			$query->from('#__sportsmanagement_project_referee');
			$query->order('lastname');

			$db->setQuery($query);
			$refs = $db->loadObjectList();

			foreach ($refs as $ref)
			{
				$ref->text = $ref->lastname . "," . $ref->firstname;
			}
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $refs;
	}

	/**
	 * sportsmanagementModelProject::getMapConfig()
	 *
	 * @param   integer  $cfg_which_database
	 *
	 * @return
	 */
	function getMapConfig($cfg_which_database = 0)
	{
		return self::getTemplateConfig('map', $cfg_which_database, __METHOD__);
	}

	/**
	 * @return country from project-league
	 * @since  2011-11-12
	 * @author diddipoeler
	 */
	function getProjectCountry($cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

		$query->select('l.country');
		$query->from('#__sportsmanagement_league as l');
		$query->join('INNER', '#__sportsmanagement_project as pro ON pro.league_id = l.id ');
		$query->where('pro.id = ' . (int) self::$projectid);

		$db->setQuery($query);
		$this->country = $db->loadResult();
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $this->country;
	}

	/**
	 * Method to store the item
	 *
	 * @access public
	 * @return boolean    True on success
	 * @since  1.5
	 */
	function store($data, $table = '', $cfg_which_database = 0)
	{
		if ($table == '')
		{
			$row =& $this->getTable();
		}
		else
		{
			$row = Table::getInstance($table, 'Table');
		}

		// Bind the form fields to the items table
		if (!$row->bind($data))
		{
			$this->setError(Text::_('Binding failed'));

			return false;
		}

		// Create the timestamp for the date
		$row->checked_out_time = gmdate('Y-m-d H:i:s');

		// If new item,order last,but only if an ordering exist
		if ((isset($row->id)) && (isset($row->ordering)))
		{
			if (!$row->id && $row->ordering != null)
			{
				$row->ordering = $row->getNextOrder();
			}
		}

		// Make sure the item is valid
		if (!$row->check())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Store the item to the database
		if (!$row->store())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		return $row->id;
	}
}
