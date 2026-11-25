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
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\ProjectModel as NativeProjectModel;
use Diddipoeler\Component\SportsManagement\Site\Model\MatchreportDataModel as NativeMatchreportDataModel;

if (!defined('JSM_PATH')) {
    define('JSM_PATH', 'components/com_sportsmanagement');
}

// Keep the historical constants available for direct legacy includes while
// using Joomla 5/6 component parameters instead of importing administrator models.
$paramscomponent = ComponentHelper::getParams('com_sportsmanagement');

if (!defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE')) {
    define('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', (int) $paramscomponent->get('cfg_which_database', 0));
}

if (!defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO')) {
    define('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO', (int) $paramscomponent->get('show_debug_info', 0));
}

if (!defined('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO')) {
    define('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO', (int) $paramscomponent->get('show_query_debug_info', 0));
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
	/**
	 * Resolve the Joomla site application through the shared Joomla 5/6 resolver.
	 * Load the compatibility resolver for direct includes and reject non-site clients before request data is accessed.
	 * @return CMSApplicationInterface Active site application.
	 * @throws \RuntimeException If the resolver is unavailable or the client is not the site.
	 */
	private static function siteApplication(): CMSApplicationInterface
	{
		if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
			$resolverFile = JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php';

			if (is_file($resolverFile)) {
				require_once $resolverFile;
			}
		}

		if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
			throw new \RuntimeException('SportsManagement site application resolver could not be loaded.', 500);
		}

		$app = SportsManagementSiteApplicationResolver::resolve();

		if (!$app->isClient('site')) {
			throw new \RuntimeException('SportsManagement legacy Project model requires the Joomla site application.', 500);
		}

		return $app;
	}

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
	 * Create a native project model for the legacy static API.
	 * Apply the selected database and current project ID, falling back to the Joomla input when the legacy project context is unset.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return NativeProjectModel Configured project model.
	 * @throws \RuntimeException If the native model cannot be loaded.
	 */
	private static function nativeProjectModel($cfg_which_database = 0): NativeProjectModel
	{
		if (!class_exists(NativeProjectModel::class)) {
			foreach ([
				JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
				JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
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
			self::$projectid = self::siteApplication()->getInput()->getInt('p', 0);
		}

		$model->setProjectId((int) self::$projectid);

		return $model;
	}

	/**
	 * Create a native match-report model for the legacy static API.
	 * Apply the selected database and project context so event and substitution queries use the same data source as the caller.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return NativeMatchreportDataModel Configured match-report model.
	 * @throws \RuntimeException If the native model cannot be loaded.
	 */
	private static function nativeMatchreportDataModel($cfg_which_database = 0): NativeMatchreportDataModel
	{
		if (!class_exists(NativeMatchreportDataModel::class)) {
			foreach ([
				JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
				JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
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
			self::$projectid = self::siteApplication()->getInput()->getInt('p', 0);
		}

		$model->setProjectId((int) self::$projectid);

		return $model;
	}


	/**
	 * Initialise the legacy project facade using the Joomla 5/6 MVC factory and site input.
	 * Read the project, database, match and layout selectors into the historical static context used by legacy templates.
	 * @param array $config Joomla model configuration.
	 * @param MVCFactoryInterface|null $factory Optional component MVC factory.
	 */
	public function __construct($config = [], ?MVCFactoryInterface $factory = null)
	{
		parent::__construct($config, $factory);

		$input = self::siteApplication()->getInput();
		self::$projectid = $input->getInt('p', 0);
		self::$cfg_which_database = $input->getInt('cfg_which_database', 0);
		self::$matchid = $input->getInt('mid', 0);
		self::$layout = $input->getCmd('layout', '');
	}


	/**
	 * Count matches using the native project model.
	 * Pass the explicit project or league/season selection to the native count query using the current legacy database selector.
	 * @param int $project_id Project ID.
	 * @param bool $alloverleagueid Whether to count across the selected league.
	 * @param int $league_id League ID.
	 * @param int $season_id Season ID.
	 * @return int Number of matching records.
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
	 * Find the next project within the requested league.
	 * Delegate the name-based forward lookup to the native model using the legacy database context.
	 * @param string $name Current project name.
	 * @param int $league_id League ID.
	 * @return object|null Next project or null.
	 */
	public static function getnextproject($name = '', $league_id = 0)
	{
		return self::nativeProjectModel(self::$cfg_which_database)
			->getAdjacentProject((string) $name, (int) $league_id, true);
	}
	
	/**
	 * Find the previous project within the requested league.
	 * Delegate the name-based backward lookup to the native model using the legacy database context.
	 * @param string $name Current project name.
	 * @param int $league_id League ID.
	 * @return object|null Previous project or null.
	 */
	public static function getprevproject($name = '', $league_id = 0)
	{
		return self::nativeProjectModel(self::$cfg_which_database)
			->getAdjacentProject((string) $name, (int) $league_id, false);
	}
	
	/**
	 * Set the project and database context for subsequent legacy calls.
	 * Invalidate the stored project and current-round objects so later lookups resolve the new context.
	 * @param int $id Project ID.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return void
	 */
	public static function setProjectID($id = 0, $cfg_which_database = 0)
	{
		self::$projectid = (int) $id;
		self::$cfg_which_database = (int) $cfg_which_database;
		self::$_project = null;
		self::$_current_round = null;
	}

	/**
	 * Retrieve the sport type assigned to the current project.
	 * Resolve the project through the native model and return zero when no project is available.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return int Sport type ID or zero.
	 */
	public static function getSportsType($cfg_which_database = 0)
	{
		$project = self::getProject($cfg_which_database, __METHOD__);

		return $project ? (int) $project->sports_type_id : 0;
	}

	/**
	 * Load the current project and refresh the legacy static project context.
	 * Optionally update the hit counter, then retain the returned project ID, slug and season for compatibility callers.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @param string $call_function Historical caller label retained for compatibility.
	 * @param int $inserthits Whether to increment the project hit counter.
	 * @return object|null Project data or null.
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
	 * Delegate the optional project hit increment to the native model.
	 * Use the legacy database selector and let the native model decide whether a write is required.
	 * @param int $projectid Target project ID.
	 * @param int $inserthits Nonzero enables the increment.
	 * @return void
	 */
	public static function updateHits($projectid = 0, $inserthits = 0)
	{
		self::nativeProjectModel(self::$cfg_which_database)
			->updateProjectHits((int) $projectid, (bool) $inserthits);
	}

	/**
	 * Resolve the current round ID for the selected project.
	 * Synchronise the legacy Results model round ID when requested and available; return zero if no round exists.
	 * @param mixed $view Legacy view context.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return int Current round ID or zero.
	 */
	public static function getCurrentRound($view = null, $cfg_which_database = 0)
	{
		$round = self::increaseRound($cfg_which_database);

		if ($view === 'result' && class_exists('sportsmanagementModelResults')) {
			sportsmanagementModelResults::$roundid = $round ? (int) $round->id : 0;
		}

		return $round ? (int) $round->id : 0;
	}

	/**
	 * Retrieve current-round data from the native project model.
	 * Store the returned round in the historical static property for subsequent legacy calls.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return object|null Current round data or null.
	 */
	public static function increaseRound($cfg_which_database = 0)
	{
		$round = self::nativeProjectModel($cfg_which_database)->getCurrentRoundData();
		self::$_current_round = $round;

		return $round;
	}

	/**
	 * Return the display number of the current project round.
	 * Reuse the native current-round lookup and return zero if it has no result.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return int Round code or zero.
	 */
	public static function getCurrentRoundNumber($cfg_which_database = 0)
	{
		$round = self::increaseRound($cfg_which_database);

		return $round ? (int) $round->roundcode : 0;
	}

	/**
	 * Decode the legacy semicolon-separated ranking colour configuration.
	 * Each comma-separated rule must contain a start rank, end rank, colour and description; stop at the first malformed rule.
	 * @param string $configcolors Serialized colour rules.
	 * @param int $cfg_which_database Unused legacy database argument.
	 * @return array Parsed rules or the initial empty rule.
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
	 * Find a division in the current project's division list.
	 * Return an empty division object with ID zero when the requested key is missing, preserving legacy template expectations.
	 * @param int|string $id Division key.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return object Division data or an empty placeholder.
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
	 * Load the current project's divisions from the native model.
	 * Apply the requested level and retain the result in the legacy static division property.
	 * @param int $divLevel Division level filter.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return array Project divisions.
	 */
	public static function getDivisions($divLevel = 0, $cfg_which_database = 0)
	{
		self::$_divisions = self::nativeProjectModel($cfg_which_database)
			->getProjectDivisions((int) $divLevel);

		return self::$_divisions;
	}

	/**
	 * Load the division IDs at the requested project level.
	 * Record a translated legacy ranking warning when the native lookup finds no IDs.
	 * @param int $divLevel Division level filter.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return array Matching division IDs.
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
	 * Load project round objects ordered by their round codes.
	 * Forward ordering and slug options to the native model and update the legacy static round list.
	 * @param string $ordering Sort direction.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @param bool $slug Whether slug values are requested.
	 * @return array Project rounds.
	 */
	public static function getRounds($ordering = 'ASC', $cfg_which_database = 0, $slug = true)
	{
		self::$_rounds = self::nativeProjectModel($cfg_which_database)
			->getRounds((string) $ordering, (bool) $slug);

		return self::$_rounds;
	}

	/**
	 * Build the native round selection options for the current project.
	 * Forward the requested ordering and slug mode without maintaining a second query implementation.
	 * @param string $ordering Sort direction.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @param bool $slug Whether option values use slugs.
	 * @return array Round options.
	 */
	public static function getRoundOptions($ordering = 'ASC', $cfg_which_database = 0, $slug = true)
	{
		return self::nativeProjectModel($cfg_which_database)
			->getRoundOptions((string) $ordering, (bool) $slug);
	}

	/**
	 * Read details for a project-team relation.
	 * Resolve the explicit relation ID through the native project model on the selected database.
	 * @param int $projectteamid Project-team relation ID.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return object|null Project team or null.
	 */
	public static function getTeaminfo($projectteamid = 0, $cfg_which_database = 0)
	{
		return self::nativeProjectModel($cfg_which_database)
			->getProjectTeamInfo((int) $projectteamid);
	}

	/**
	 * Index project teams by the legacy row ID.
	 * Use the common team-list method and omit rows without positive IDs to preserve lookup semantics.
	 * @param int $division Division filter.
	 * @param string $teamname Field used as the display name.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return array Team objects indexed by ID.
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
	 * Load teams belonging to the current project.
	 * Apply division and playground filters and optionally copy the requested name field to the legacy name property.
	 * @param int $division Division filter.
	 * @param string $teamname Field used as the display name.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @param string $call_function Historical caller label.
	 * @param int $playground Playground filter.
	 * @return array Matching team objects.
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
	 * Return all project teams through the historical reference-returning API.
	 * Load the native list with an optional playground filter and map the requested alternate name field to the legacy name property.
	 * @param string $teamname Field used as the display name.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @param string $call_function Historical caller label.
	 * @param int $playground Playground filter.
	 * @return array Matching team objects.
	 */
	public static function & _getTeams($teamname = 'name', $cfg_which_database = 0, $call_function = '', $playground = 0)
	{
		$teams = self::nativeProjectModel($cfg_which_database)
			->getProjectTeams(0, (int) $playground);

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
	 * Resolve a division and its descendant IDs.
	 * Delegate traversal to the native project model for the selected database.
	 * @param int $divisionid Root division ID.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return array Division tree IDs.
	 */
	public static function getDivisionTreeIds($divisionid, $cfg_which_database = 0)
	{
		return self::nativeProjectModel($cfg_which_database)
			->getProjectDivisionTreeIds((int) $divisionid);
	}

	/**
	 * Index the filtered team list by project-team relation ID.
	 * Preserve the legacy name and caller options while ignoring rows with missing or nonpositive relation IDs.
	 * @param int $division Division filter.
	 * @param string $teamname Field used as the display name.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @param string $call_function Historical caller label.
	 * @return array Teams indexed by project-team ID.
	 */
	public static function getTeamsIndexedByPtid($division = 0, $teamname = 'name', $cfg_which_database = 0, $call_function = '')
	{
		$teams = [];

		foreach (self::getTeams($division, $teamname, $cfg_which_database, $call_function) as $team) {
			$projectTeamId = (int) ($team->projectteamid ?? 0);

			if ($projectTeamId > 0) {
				$teams[$projectTeamId] = $team;
			}
		}

		return $teams;
	}

	/**
	 * Retrieve the favourite teams configured for the current project.
	 * Store the native model result in the historical static favourite-team property.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return array Favourite-team data.
	 */
	public static function getFavTeams($cfg_which_database = 0)
	{
		self::$favteams = self::nativeProjectModel($cfg_which_database)->getFavTeams();

		return self::$favteams;
	}

	
	/**
	 * Load event types through the native project model.
	 * Forward the event, sport, project and match filters; keep the unused team and season arguments for legacy callers.
	 * @param mixed $evid Event selection.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @param int $sports_type_id Sport type ID.
	 * @param int $p Project ID.
	 * @param int $tid Legacy team argument.
	 * @param int $s Legacy season argument.
	 * @param int $mid Match ID.
	 * @return array Matching event types.
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
	 * Resolve a season-team ID to its project-team relation.
	 * Use the native model and current project context on the selected database.
	 * @param int $teamid Season-team ID.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return int Project-team ID or zero.
	 */
	public static function getprojectteamID($teamid, $cfg_which_database = 0)
	{
		return self::nativeProjectModel($cfg_which_database)
			->getProjectTeamId((int) $teamid);
	}

	/**
	 * Read the effective overall template configuration for the current project.
	 * Delegate configuration resolution to the native model rather than duplicating defaults or overrides.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return array Effective overall configuration.
	 */
	public static function getOverallConfig($cfg_which_database = 0)
	{
		return self::nativeProjectModel($cfg_which_database)->getOverallConfig();
	}

	/**
	 * Read the effective configuration for a named project template.
	 * Use the native model to resolve template settings and retain the historical caller argument for compatibility.
	 * @param string $template Template name.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @param string $call_function Historical caller label.
	 * @return array Effective template configuration.
	 */
	public static function getTemplateConfig($template, $cfg_which_database = 0, $call_function = '')
	{
		return self::nativeProjectModel($cfg_which_database)
			->getTemplateConfig((string) $template);
	}

	/**
	 * Load events assigned to the current project.
	 * Forward an optional position filter to the native model so only events for that position are returned when requested.
	 * @param int $position_id Position filter; zero means no explicit filter.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return array Project event definitions.
	 */
	public static function getProjectEvents($position_id = 0, $cfg_which_database = 0)
	{
		return self::nativeProjectModel($cfg_which_database)
			->getProjectEvents((int) $position_id);
	}

	/**
	 * Load statistics assigned to project positions.
	 * Pass statistic and position selections to the native model on the chosen database.
	 * @param mixed $statid Statistic selection; zero means all.
	 * @param int $positionid Position filter; zero means all.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return array Statistic definitions.
	 */
	public static function getProjectStats($statid = 0, $positionid = 0, $cfg_which_database = 0)
	{
		return self::nativeProjectModel($cfg_which_database)
			->getProjectStats($statid, (int) $positionid);
	}

	/**
	 * Load the positions available to the current project.
	 * Update the legacy static position list with the native model result.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return array Project positions.
	 */
	public static function getProjectPositions($cfg_which_database = 0)
	{
		self::$_positions = self::nativeProjectModel($cfg_which_database)
			->getProjectPositions();

		return self::$_positions;
	}

	/**
	 * Render the historical club icon or country flag for a team.
	 * Replace a missing club image with the configured placeholder, then delegate modal or flag markup to the existing presentation helpers.
	 * @param object $team Team data; the image property may be updated.
	 * @param int $type Icon mode (1 for logo, 2 for flag).
	 * @param int $with_space Unused historical spacing argument.
	 * @param string $club_icon Team image field.
	 * @param int $cfg_which_database Unused historical database argument.
	 * @param int $roundcode Round identifier for the modal.
	 * @param string $modalwidth Modal width.
	 * @param string $modalheight Modal height.
	 * @param int $use_jquery_modal Legacy modal mode.
	 * @return string Generated markup or an empty string.
	 */
	public static function getClubIconHtml(&$team, $type = 1, $with_space = 0, $club_icon = 'logo_big', $cfg_which_database = 0, $roundcode = 0, $modalwidth = '100', $modalheight = '200', $use_jquery_modal = 0)
	{
		if ((int) $type === 1) {
			if (!sportsmanagementHelper::existPicture($team->{$club_icon})) {
				$team->{$club_icon} = sportsmanagementHelper::getDefaultPlaceholder($club_icon);
			}

			return sportsmanagementHelperHtml::getBootstrapModalImage(
				$roundcode . 'team' . $team->team_id,
				$team->{$club_icon},
				$team->name,
				'20',
				'',
				$modalwidth,
				$modalheight,
				$use_jquery_modal
			);
		}

		if ((int) $type === 2 && isset($team->country)) {
			return JSMCountries::getCountryFlag($team->country);
		}

		return '';
	}

	/**
	 * Retrieve substitutions recorded for a match.
	 * Use the native match-report model with the selected project and database context.
	 * @param int $match_id Match ID.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return array Match substitution records.
	 */
	public static function getMatchSubstitutions($match_id, $cfg_which_database = 0)
	{
		return self::nativeMatchreportDataModel($cfg_which_database)
			->getMatchSubstitutions((int) $match_id);
	}

	/**
	 * Resolve the current legacy match on first access.
	 * Cache the native match result and convert its date to the expected timezone when the legacy helper is available.
	 * @return object|null Match data or null.
	 */
	public static function getMatch()
	{
		if (self::$_match === null) {
			self::$_match = self::nativeProjectModel(self::$cfg_which_database)
				->getLegacyMatch((int) self::$matchid);

			if (self::$_match && class_exists('sportsmanagementHelper')) {
				sportsmanagementHelper::convertMatchDateToTimezone(self::$_match);
			}
		}

		return self::$_match;
	}

	/**
	 * Retrieve match events from the native match-report model.
	 * Forward the comment and descending-sort flags using their historical integer-one semantics.
	 * @param int $match_id Match ID.
	 * @param int $showcomments Whether to include comments.
	 * @param int $sortdesc Whether to request descending order.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return array Match events.
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
	 * Check whether the current user may perform the requested project task.
	 * Delegate the decision to the native project model with the selected database context.
	 * @param string|null $task Optional task name.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return bool Whether editing is permitted.
	 */
	public static function hasEditPermission($task = null, $cfg_which_database = 0)
	{
		return self::nativeProjectModel($cfg_which_database)
			->hasEditPermission($task === null ? null : (string) $task);
	}

	
	/**
	 * Check a user's project administrator or editor relationship.
	 * Pass the optional project object to the native model; non-object legacy values become null.
	 * @param int $userId Joomla user ID.
	 * @param mixed $project Optional project object.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return bool Whether the user has the relationship.
	 */
	public static function isUserProjectAdminOrEditor($userId = 0, $project = array(), $cfg_which_database = 0)
	{
		$projectObject = is_object($project) ? $project : null;

		return self::nativeProjectModel($cfg_which_database)
			->isUserProjectAdminOrEditor((int) $userId, $projectObject);
	}

	/**
	 * Read team IDs for the current project.
	 * Forward the optional division filter to the native project model.
	 * @param int $division Division filter.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return array Team IDs.
	 */
	function getTeamIds($division = 0, $cfg_which_database = 0)
	{
		return self::nativeProjectModel($cfg_which_database)
			->getTeamIds((int) $division);
	}

	/**
	 * Read playground selection data for the current project.
	 * Delegate the ID and name lookup to the native model on the selected database.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return array Playground options.
	 */
	function getPlaygrounds($cfg_which_database = 0)
	{
		return self::nativeProjectModel($cfg_which_database)->getPlaygrounds();
	}

	/**
	 * Read the configured regular playing time for a project.
	 * Use the explicit project ID and the native database-aware model rather than reading legacy static data.
	 * @param int $project_id Project ID.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return int Regular playing time.
	 */
	function getProjectGameRegularTime($project_id, $cfg_which_database = 0)
	{
		return self::nativeProjectModel($cfg_which_database)
			->getProjectGameRegularTime((int) $project_id);
	}

	/**
	 * Retrieve referee selection options for the current project.
	 * Use the native model to prepare the options for legacy forms and views.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return array Referee options.
	 */
	function getReferees($cfg_which_database = 0)
	{
		return self::nativeProjectModel($cfg_which_database)->getRefereeOptions();
	}

	/**
	 * Read the current project's map template configuration.
	 * Reuse the native template configuration resolver with the fixed map template name.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return array Effective map configuration.
	 */
	function getMapConfig($cfg_which_database = 0)
	{
		return self::nativeProjectModel($cfg_which_database)->getTemplateConfig('map');
	}

	/**
	 * Resolve the country of the current project through its league context.
	 * Store the native country result on the facade instance for compatibility callers.
	 * @param int $cfg_which_database SportsManagement database selector.
	 * @return string Project country code.
	 */
	function getProjectCountry($cfg_which_database = 0)
	{
		$this->country = self::nativeProjectModel($cfg_which_database)->getProjectCountry();

		return $this->country;
	}

	/**
	 * Persist a legacy table record through Joomla's table API.
	 * Bind the submitted data, initialise checkout time and ordering when supported, validate and store the row; failures return false.
	 * @param array $data Values to bind to the table.
	 * @param string $table Optional administrator table name.
	 * @param int $cfg_which_database Historical database argument not used by this method.
	 * @return int|false Stored row ID or false on failure.
	 */
	function store($data, $table = '', $cfg_which_database = 0)
	{
		try {
			$row = $table === ''
				? $this->getTable()
				: $this->getTable((string) $table, 'Administrator');

			if (!$row->bind((array) $data)) {
				return false;
			}

			if (property_exists($row, 'checked_out_time')) {
				$row->checked_out_time = gmdate('Y-m-d H:i:s');
			}

			if (isset($row->id, $row->ordering) && !$row->id && $row->ordering !== null) {
				$row->ordering = $row->getNextOrder();
			}

			if (!$row->check()) {
				return false;
			}

			if (!$row->store()) {
				return false;
			}

			return (int) ($row->id ?? 0);
		} catch (\Throwable $e) {
			return false;
		}
	}
}
