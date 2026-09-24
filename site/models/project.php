<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage project
 * @file       project.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;
use Joomla\CMS\User\UserHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Log\Log;
use Diddipoeler\Component\SportsManagement\Site\Model\ProjectModel as NativeProjectModel;
use Diddipoeler\Component\SportsManagement\Site\Model\MatchreportDataModel as NativeMatchreportDataModel;

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
	static $_teams = null;
	static $_rounds = null;
	static $_stats = null;
	static $_positions = null;
	static $_divisions = null;
	static $_current_round;
	static $seasonid = 0;
	static $cfg_which_database = 0;
	static $favteams = null;
	static $projectslug = '';
	static $divisionslug = '';
	static $roundslug = '';
	static $layout = '';
	var $country = null;
	var $_matches = null;
	static $tips = array();
	static $warnings = array();
	static $notes = array();

	/**
	 * Build the native Joomla 5/6 project model for legacy static callers.
	 */
	private static function nativeProjectModel($cfg_which_database = 0): NativeProjectModel
	{
		if (!class_exists(NativeProjectModel::class)) {
			foreach ([
				JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
				JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
				JPATH_SITE . '/components/com_sportsmanagement/src/Model/ProjectModel.php',
			] as $nativeFile) {
				if (is_file($nativeFile)) {
					require_once $nativeFile;
				}
			}
		}

		if (!class_exists(NativeProjectModel::class)) {
			throw new \RuntimeException('SportsManagement native Project model could not be loaded.', 500);
		}

		$model = new NativeProjectModel();
		$model->setDatabaseSelector((int) $cfg_which_database);

		if ((int) self::$projectid <= 0) {
			self::$projectid = Factory::getApplication()->input->getInt('p', 0);
		}

		$model->setProjectId((int) self::$projectid);

		return $model;
	}

	/**
	 * Build the native Joomla 5/6 match-report data model for legacy callers.
	 */
	private static function nativeMatchreportDataModel($cfg_which_database = 0): NativeMatchreportDataModel
	{
		if (!class_exists(NativeMatchreportDataModel::class)) {
			foreach ([
				JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
				JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
				JPATH_SITE . '/components/com_sportsmanagement/src/Model/MatchreportDataModel.php',
			] as $nativeFile) {
				if (is_file($nativeFile)) {
					require_once $nativeFile;
				}
			}
		}

		if (!class_exists(NativeMatchreportDataModel::class)) {
			throw new \RuntimeException('SportsManagement native MatchreportData model could not be loaded.', 500);
		}

		$model = new NativeMatchreportDataModel();
		$model->setDatabaseSelector((int) $cfg_which_database);

		if ((int) self::$projectid <= 0) {
			self::$projectid = Factory::getApplication()->input->getInt('p', 0);
		}

		$model->setProjectId((int) self::$projectid);

		return $model;
	}


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
	 * sportsmanagementModelProject::getProjectCountMatches()
	 * 
	 * @param integer $project_id
	 * @return void
	 */
	public static function getProjectCountMatches($project_id = 0,$alloverleagueid = false,$league_id = 0,$season_id = 0)
	{
		return self::nativeProjectModel(self::$cfg_which_database)->getProjectMatchCount(
			(int) $project_id,
			(bool) $alloverleagueid,
			(int) $league_id,
			(int) $season_id
		);
	}
    
	/**
	 * sportsmanagementModelProject::getnextproject()
	 * 
	 * @param string $name
	 * @param integer $league_id
	 * @return
	 */
	public static function getnextproject($name = '', $league_id = 0)
	{
		return self::nativeProjectModel(self::$cfg_which_database)
			->getAdjacentProject((string) $name, (int) $league_id, true);
	}
	
	/**
	 * sportsmanagementModelProject::getprevproject()
	 * 
	 * @param string $name
	 * @param integer $league_id
	 * @return
	 */
	public static function getprevproject($name = '', $league_id = 0)
	{
		return self::nativeProjectModel(self::$cfg_which_database)
			->getAdjacentProject((string) $name, (int) $league_id, false);
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
		$project = self::getProject($cfg_which_database, __METHOD__);

		return $project ? (int) $project->sports_type_id : 0;
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
		$model = self::nativeProjectModel($cfg_which_database);

		if ((int) self::$projectid > 0) {
			self::updateHits((int) self::$projectid, (int) $inserthits);
		}

		self::$_project = $model->getProject();

		if (self::$_project) {
			self::$projectid = (int) self::$_project->id;
			self::$projectslug = (string) (self::$_project->slug ?? '');
			self::$seasonid = (int) (self::$_project->season_id ?? 0);
		}

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
		self::nativeProjectModel(self::$cfg_which_database)
			->updateProjectHits((int) $projectid, (bool) $inserthits);
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
		$model = self::nativeProjectModel($cfg_which_database);
		$roundId = $model->getCurrentRound();
		self::$_current_round = $roundId;

		if ($view === 'result' && class_exists('sportsmanagementModelResults')) {
			sportsmanagementModelResults::$roundid = $roundId;
		}

		return $roundId;
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
		$query   = $db->createQuery();
		$result  = '';
		$project = self::getProject($cfg_which_database, __METHOD__);
        $project->auto_time = $project->auto_time ? $project->auto_time : 7200 ;

		if (!self::$_current_round && $project)
		{
			$current_date = date('Y-m-d', time());
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
	$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
   $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' data <pre>'.print_r($query->dump(),true).'</pre>'  ), '');
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' current_round_auto <pre>'.print_r($project->current_round_auto,true).'</pre>'  ), '');
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
	$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
   $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');

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
					$resultupdate = $db->updateObject('#__sportsmanagement_project', $object, 'id');
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
		return self::nativeProjectModel($cfg_which_database)->getCurrentRoundNumber();
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
		self::$_divisions = self::nativeProjectModel($cfg_which_database)
			->getProjectDivisions((int) $divLevel);

		return self::$_divisions;
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
		$result = self::nativeProjectModel($cfg_which_database)
			->getProjectDivisionIds((int) $divLevel);

		if (!$result) {
			self::$warnings[] = Text::_('COM_SPORTSMANAGEMENT_RANKING_NO_SUBLEVEL_DIVISION_FOUND') . $divLevel;
		}

		return $result;
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
		self::$_rounds = self::nativeProjectModel($cfg_which_database)
			->getRounds((string) $ordering, (bool) $slug);

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
		return self::nativeProjectModel($cfg_which_database)
			->getRoundOptions((string) $ordering, (bool) $slug);
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
		return self::nativeProjectModel($cfg_which_database)
			->getProjectTeamInfo((int) $projectteamid);
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
		$teams = [];
		foreach (self::getTeams($division, $teamname, $cfg_which_database) as $team) {
			$teamId = (int) ($team->id ?? 0);
			if ($teamId > 0) {
				$teams[$teamId] = $team;
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
		$model = self::nativeProjectModel($cfg_which_database);
		$teams = $model->getProjectTeams((int) $division, (int) $playground);

		if ($teamname !== 'name') {
			foreach ($teams as $team) {
				if (isset($team->{$teamname})) {
					$team->name = $team->{$teamname};
				}
			}
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
		$query     = $db->createQuery();
		$starttime = microtime();

		$query->select('tl.id AS projectteamid,tl.division_id,tl.standard_playground,tl.admin,tl.start_points,tl.points_finally,tl.neg_points_finally,tl.matches_finally,tl.won_finally,tl.draws_finally,tl.lost_finally');
		$query->select('tl.homegoals_finally,tl.guestgoals_finally,tl.diffgoals_finally,tl.info,tl.reason,tl.team_id as project_team_team_id,tl.checked_out,tl.checked_out_time,tl.is_in_score,tl.picture AS projectteam_picture');
		$query->select('IF((ISNULL(tl.picture) OR (tl.picture="")),(IF((ISNULL(t.picture) OR (t.picture="")), c.logo_small , t.picture)) , t.picture) as picture,tl.project_id');
		$query->select('t.picture as team_picture,t.id,t.name,t.short_name,t.middle_name,t.notes,t.club_id');
		$query->select('u.username,u.email');
		$query->select('st.team_id');
		$query->select('c.email as club_email,c.phone as club_phone,c.fax as club_fax,c.logo_small,c.logo_middle,c.logo_big,c.country,c.website,c.new_club_id,c.facebook,c.twitter,c.instagram');
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
        $query->where('tl.is_in_score = 1' );


try{
		$db->setQuery($query);

		self::$_teams = $db->loadObjectList();
        }
		catch (Exception $e)
		{
	$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
   $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');

		}
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
		return self::nativeProjectModel($cfg_which_database)
			->getProjectDivisionTreeIds((int) $divisionid);
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
		self::$favteams = self::nativeProjectModel($cfg_which_database)->getFavTeams();

		return self::$favteams;
	}

	
	/**
	 * sportsmanagementModelProject::getEventTypes()
	 * 
	 * @param string $evid
	 * @param integer $cfg_which_database
	 * @param integer $sports_type_id
	 * @return
	 */
	public static function getEventTypes($evid = 0, $cfg_which_database = 0, $sports_type_id = 0,
					    $p = 0,$tid = 0,$s = 0,$mid = 0)
	{
		return self::nativeProjectModel($cfg_which_database)->getEventTypes(
			$evid,
			(int) $sports_type_id,
			(int) $p,
			(int) $mid
		);
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
		return self::nativeProjectModel($cfg_which_database)
			->getProjectTeamId((int) $teamid);
	}

	/**
	 * sportsmanagementModelProject::getOverallConfig()
	 *
	 * @return
	 */
	public static function getOverallConfig($cfg_which_database = 0)
	{
		return self::nativeProjectModel($cfg_which_database)->getOverallConfig();
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
		return self::nativeProjectModel($cfg_which_database)
			->getTemplateConfig((string) $template);
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
		return self::nativeProjectModel($cfg_which_database)
			->getProjectEvents((int) $position_id);
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
		return self::nativeProjectModel($cfg_which_database)
			->getProjectStats($statid, (int) $positionid);
	}

	/**
	 * sportsmanagementModelProject::getProjectPositions()
	 *
	 * @return
	 */
	public static function getProjectPositions($cfg_which_database = 0)
	{
		self::$_positions = self::nativeProjectModel($cfg_which_database)
			->getProjectPositions();

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
		return self::nativeMatchreportDataModel($cfg_which_database)
			->getMatchSubstitutions((int) $match_id);
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
		$query = $db->createQuery();

		if (is_null(self::$_match))
		{
			$query->select('m.*,DATE_FORMAT(m.time_present,"%H:%i") time_present, r.project_id, p.timezone, p.game_parts ');
			$query->from('#__sportsmanagement_match AS m ');
			$query->join('INNER', '#__sportsmanagement_round AS r on r.id = m.round_id ');
			$query->join('INNER', '#__sportsmanagement_project AS p on r.project_id = p.id ');
			$query->where('m.id = ' . (int) self::$matchid);
try{
			$db->setQuery($query, 0, 1);
			self::$_match = $db->loadObject();
 }
		catch (Exception $e)
		{
	$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
   $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
		}
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
		return self::nativeMatchreportDataModel($cfg_which_database)->getMatchEvents(
			(int) $match_id,
			(int) $showcomments === 1,
			(int) $sortdesc === 1
		);
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
		return self::nativeProjectModel($cfg_which_database)
			->hasEditPermission($task === null ? null : (string) $task);
	}

	
	/**
	 * sportsmanagementModelProject::isUserProjectAdminOrEditor()
	 * 
	 * @param integer $userId
	 * @param mixed $project
	 * @param integer $cfg_which_database
	 * @return
	 */
	public static function isUserProjectAdminOrEditor($userId = 0, $project = array(), $cfg_which_database = 0)
	{
		$projectObject = is_object($project) ? $project : null;

		return self::nativeProjectModel($cfg_which_database)
			->isUserProjectAdminOrEditor((int) $userId, $projectObject);
	}

	/**
	 * return array of team ids
	 *
	 * @return array     *
	 */
	function getTeamIds($division = 0, $cfg_which_database = 0)
	{
		return self::nativeProjectModel($cfg_which_database)
			->getTeamIds((int) $division);
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
		return self::nativeProjectModel($cfg_which_database)->getPlaygrounds();
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
		return self::nativeProjectModel($cfg_which_database)
			->getProjectGameRegularTime((int) $project_id);
	}

	/**
	 * sportsmanagementModelProject::getReferees()
	 *
	 * @return
	 */
	function getReferees($cfg_which_database = 0)
	{
		return self::nativeProjectModel($cfg_which_database)->getRefereeOptions();
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
		return self::nativeProjectModel($cfg_which_database)->getTemplateConfig('map');
	}

	/**
	 * @return country from project-league
	 * @since  2011-11-12
	 * @author diddipoeler
	 */
	function getProjectCountry($cfg_which_database = 0)
	{
		$this->country = self::nativeProjectModel($cfg_which_database)->getProjectCountry();

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
