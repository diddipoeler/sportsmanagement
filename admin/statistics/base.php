<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage statistics
 * @file       base.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\Registry\Registry;
use Joomla\CMS\Log\Log;

/**
 * SMStatistic
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class SMStatistic extends CMSObject
{
	var $_name = 'default';
	var $_calculated = 0;
	var $_showinsinglematchreports = 1;

	/**
	 * JRegistry object for parameters
	 *
	 * @var object JRegistry
	 */
	var $_params = null;

	/**
	 * statistic
	 *
	 * @var integer
	 */
	var $id = null;

	/**
	 * stat name
	 *
	 * @var string
	 */
	var $name;

	/**
	 * short form (abbreviation) of the name
	 *
	 * @var string
	 */
	var $short;

	/**
	 * icon path
	 *
	 * @var string
	 */
	var $icon;

	/**
	 * parameters for the stat (from xml)
	 *
	 * @var string
	 */
	var $baseparams;

	/**
	 * parameters for the stat (from xml)
	 *
	 * @var string
	 */
	var $params;

	/**
	 * SMStatistic::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{

	}

	/**
	 * get an instance of class corresponding to type
	 *
	 * @param   string class
	 *
	 * @return object
	 */
	public static function getInstance($class)
	{
		$classname = 'SMStatistic' . ucfirst($class);

		if (!class_exists($classname))
		{
			$file = JPATH_SITE . '/administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_sportsmanagement' . DIRECTORY_SEPARATOR . 'statistics' . DIRECTORY_SEPARATOR . $class . '.php';

			if (!File::exists($file))
			{
				Log::add($classname . ': ' . Text::_('STATISTIC CLASS NOT DEFINED'), Log::ERROR, 'jsmerror');
			}

			try
			{
				JLoader::import('components.com_sportsmanagement.statistics.' . $class, JPATH_ADMINISTRATOR);
			}
			catch (Exception $e)
			{
				Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . Text::_($e->getMessage()), 'Error');
				$result = false;
			}
		}

		$stat = new $classname;

		return $stat;
	}

	/**
	 * SMStatistic::getTeamsRankingStatisticNumQuery()
	 * damit die einzelnen statistik dateien nicht zu gross werden,
	 * habe ich die queries hierher verlegt.
	 *
	 * @param   mixed  $project_id
	 * @param   mixed  $sids
	 *
	 * @return
	 */
	function getTeamsRankingStatisticNumQuery($project_id, $sids)
	{
		$option    = Factory::getApplication()->input->getCmd('option');
		$app       = Factory::getApplication();
		$db        = sportsmanagementHelper::getDBConnection();
		$query_num = Factory::getDbo()->getQuery(true);

		$query_num->select('SUM(ms.value) AS num, pt.id');
		$query_num->from('#__sportsmanagement_season_team_person_id AS tp');
		$query_num->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query_num->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query_num->join('INNER', '#__sportsmanagement_match_statistic AS ms ON ms.teamplayer_id = tp.id AND ms.statistic_id IN (' . implode(',', $sids) . ')');
		$query_num->join('INNER', '#__sportsmanagement_match AS m ON m.id = ms.match_id ');
		$query_num->where('pt.project_id = ' . $project_id);
		$query_num->where('m.published = 1');
		$query_num->where('m.team1_result IS NOT NULL');
		$query_num->group('pt.id');

		return $query_num;
	}

	/**
	 * SMStatistic::getTeamsRankingStatisticDenQuery()
	 *
	 * @param   mixed  $project_id
	 * @param   mixed  $sids
	 *
	 * @return
	 */
	function getTeamsRankingStatisticDenQuery($project_id, $sids)
	{
		$option    = Factory::getApplication()->input->getCmd('option');
		$app       = Factory::getApplication();
		$db        = sportsmanagementHelper::getDBConnection();
		$query_den = Factory::getDbo()->getQuery(true);

		$query_den->select('SUM(ms.value) AS den, pt.id');
		$query_den->from('#__sportsmanagement_season_team_person_id AS tp');
		$query_den->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query_den->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query_den->join('INNER', '#__sportsmanagement_match_statistic AS ms ON ms.teamplayer_id = tp.id AND ms.statistic_id IN (' . implode(',', $sids) . ')');
		$query_den->join('INNER', '#__sportsmanagement_match AS m ON m.id = ms.match_id ');
		$query_den->where('pt.project_id = ' . $project_id);
		$query_num->where('m.published = 1');
		$query_num->where('m.team1_result IS NOT NULL');
		$query_den->where('value > 0');
		$query_den->group('pt.id');

		return $query_den;

	}

	/**
	 * SMStatistic::getTeamsRankingStatisticCoreQuery()
	 *
	 * @param   mixed  $project_id
	 * @param   mixed  $query_num
	 * @param   mixed  $query_den
	 *
	 * @return
	 */
	function getTeamsRankingStatisticCoreQuery($project_id, $query_num, $query_den)
	{
		$option     = Factory::getApplication()->input->getCmd('option');
		$app        = Factory::getApplication();
		$db         = sportsmanagementHelper::getDBConnection();
		$query_core = Factory::getDbo()->getQuery(true);

		$query_core->select('(n.num / d.den) AS total, pt.team_id');
		$query_core->from('#__sportsmanagement_project_team AS pt');
		$query_core->join('INNER', '(' . $query_num . ') AS n ON n.id = pt.id');
		$query_core->join('INNER', '(' . $query_den . ') AS d ON d.id = pt.id');
		$query_core->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id ');
		$query_core->join('INNER', '#__sportsmanagement_team AS t ON st.team_id = t.id ');
		$query_core->where('pt.project_id = ' . $project_id);
		$query_core->group('total');

		return $query_core;
	}

	/**
	 * SMStatistic::getStaffStatsQuery()
	 *
	 * @param   mixed   $person_id
	 * @param   mixed   $team_id
	 * @param   mixed   $project_id
	 * @param   mixed   $sids
	 * @param   mixed   $select
	 * @param   bool    $history
	 * @param   string  $table
	 *
	 * @return
	 */
	function getStaffStatsQuery($person_id, $team_id, $project_id, $sids, $select, $history = false, $table = 'match_staff_statistic')
	{
		$option     = Factory::getApplication()->input->getCmd('option');
		$app        = Factory::getApplication();
		$db         = sportsmanagementHelper::getDBConnection();
		$query_core = $db->getQuery(true);

		$query_core->select($select);
		$query_core->from('#__sportsmanagement_season_team_person_id AS tp');
		$query_core->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query_core->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query_core->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt.project_id');

		switch ($table)
		{
			case 'match_staff_statistic':
				if ($sids)
				{
					$query_core->join('INNER', '#__sportsmanagement_match_staff_statistic AS ms ON ms.team_staff_id = tp.id AND ms.statistic_id IN (' . $sids . ')');
				}
				else
				{
					$query_core->join('INNER', '#__sportsmanagement_match_staff_statistic AS ms ON ms.team_staff_id = tp.id ');
				}
				break;
			case 'match_staff':
				$query_core->join('INNER', '#__sportsmanagement_match_staff AS ms ON ms.team_staff_id = tp.id ');
				break;
		}

		$query_core->join('INNER', '#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1 ');
		$query_core->where('p.published = 1');
		$query_core->where('tp.person_id = ' . $person_id);

		if (!$history)
		{
			$query_core->where('pt.project_id = ' . $project_id);
			$query_core->where('st.team_id = ' . $team_id);
		}

		$query_core->group('tp.id');

		return $query_core;
	}

	/**
	 * SMStatistic::getPlayersRankingStatisticQuery()
	 *
	 * @param   mixed   $project_id
	 * @param   mixed   $division_id
	 * @param   mixed   $team_id
	 * @param   mixed   $sids
	 * @param   mixed   $select
	 * @param   string  $which
	 *
	 * @return
	 */
	function getPlayersRankingStatisticQuery($project_id, $division_id, $team_id, $sids, $select, $which = 'statistic')
	{
		$option    = Factory::getApplication()->input->getCmd('option');
		$app       = Factory::getApplication();
		$db        = sportsmanagementHelper::getDBConnection();
		$query_num = Factory::getDbo()->getQuery(true);

		$query_num->select($select);
		$query_num->from('#__sportsmanagement_season_team_person_id AS tp');
		$query_num->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query_num->join('INNER', '#__sportsmanagement_person AS p ON p.id = tp.person_id ');
		$query_num->join('INNER', '#__sportsmanagement_team AS t ON st.team_id = t.id');
		$query_num->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');

		switch ($which)
		{
			case 'statistic':
				$query_num->join('INNER', '#__sportsmanagement_match_statistic AS ms ON ms.teamplayer_id = tp.id AND ms.statistic_id IN (' . implode(',', $sids) . ')');
				break;
			case 'event':
				$query_num->join('INNER', '#__sportsmanagement_match_event AS ms ON ms.teamplayer_id = tp.id AND ms.event_type_id IN (' . implode(',', $sids) . ')');
				break;
		}

		$query_num->join('INNER', '#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1 ');
		$query_num->where('pt.project_id = ' . $project_id);

		if ($division_id != 0)
		{
			$query_num->where('pt.division_id = ' . $division_id);
		}

		if ($team_id != 0)
		{
			$query_num->where('st.team_id = ' . $team_id);
		}

		$query_num->group('tp.id');

		return $query_num;

	}

	/**
	 * SMStatistic::getPlayersRankingStatisticNumQuery()
	 *
	 * @param   mixed  $project_id
	 * @param   mixed  $division_id
	 * @param   mixed  $team_id
	 * @param   mixed  $sids
	 *
	 * @return
	 */
	function getPlayersRankingStatisticNumQuery($project_id, $division_id, $team_id, $sids)
	{
		$option    = Factory::getApplication()->input->getCmd('option');
		$app       = Factory::getApplication();
		$db        = sportsmanagementHelper::getDBConnection();
		$query_num = Factory::getDbo()->getQuery(true);

		$query_num->select('SUM(ms.value) AS num, tp.id AS tpid, tp.person_id');
		$query_num->from('#__sportsmanagement_season_team_person_id AS tp');
		$query_num->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query_num->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query_num->join('INNER', '#__sportsmanagement_match_statistic AS ms ON ms.teamplayer_id = tp.id AND ms.statistic_id IN (' . implode(',', $sids) . ')');
		$query_num->join('INNER', '#__sportsmanagement_match AS m ON m.id = ms.match_id ');
		$query_num->where('m.published = 1');
		$query_num->where('m.team1_result IS NOT NULL');
		$query_num->where('pt.project_id = ' . $project_id);

		if ($division_id != 0)
		{
			$query_num->where('pt.division_id = ' . $division_id);
		}

		if ($team_id != 0)
		{
			$query_num->where('st.team_id = ' . $team_id);
		}

		$query_num->group('tp.id');

		return $query_num;

	}

	/**
	 * SMStatistic::getPlayersRankingStatisticCoreQuery()
	 *
	 * @param   mixed  $project_id
	 * @param   mixed  $division_id
	 * @param   mixed  $team_id
	 * @param   mixed  $query_num
	 * @param   mixed  $query_den
	 * @param   mixed  $select
	 *
	 * @return
	 */
	function getPlayersRankingStatisticCoreQuery($project_id, $division_id, $team_id, $query_num, $query_den, $select)
	{
		$option     = Factory::getApplication()->input->getCmd('option');
		$app        = Factory::getApplication();
		$db         = sportsmanagementHelper::getDBConnection();
		$query_core = Factory::getDbo()->getQuery(true);

		$query_core->select($select);
		$query_core->from('#__sportsmanagement_season_team_person_id AS tp');
		$query_core->join('INNER', '(' . $query_num . ') AS n ON n.tpid = tp.id');
		$query_core->join('INNER', '(' . $query_den . ') AS d ON d.tpid = tp.id');
		$query_core->join('INNER', '#__sportsmanagement_person AS p ON p.id = tp.person_id ');
		$query_core->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query_core->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query_core->join('INNER', '#__sportsmanagement_team AS t ON st.team_id = t.id');
		$query_core->where('pt.project_id = ' . $project_id);
		$query_core->where('p.published = 1');

		if ($division_id != 0)
		{
			$query_core->where('pt.division_id = ' . $division_id);
		}

		if ($team_id != 0)
		{
			$query_core->where('st.team_id = ' . $team_id);
		}

		return $query_core;

	}

	/**
	 * SMStatistic::getSids()
	 *
	 * @param   string  $ids
	 *
	 * @return
	 */
	function getSids($id_field = 'stat_ids')
	{
		$app    = Factory::getApplication();
		$params = self::getParams();

		$stat_ids = $params->get($id_field);
is_array($stat_ids) ? '' : return array();
		/**
		if (!count($stat_ids))
		{
//			Log::add(Text::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id), Log::WARNING, 'jsmerror');
			return (array(0));
		}
*/
		$db   = sportsmanagementHelper::getDBConnection();
		$sids = array();

		foreach ($stat_ids as $s)
		{
			$sids[] = (int) $s;
		}

		return $sids;
	}

	/**
	 * return Statistic params as objet
	 *
	 * @return object
	 * https://docs.joomla.org/J2.5:Developing_a_MVC_Component/Adding_configuration
	 * merge params
	 */
	function getParams()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		if (empty($this->_params))
		{
			$params = new Registry;
			$params = self::getBaseParams();

			// Merge global params with item params
			$classparams = self::getClassParams();
			$params->merge($classparams);

			// $this->_params = self::getBaseParams();
			// $this->_params->merge(self::getClassParams());
			// $this->_params = self::getClassParams();
			$this->_params = $params;
		}

		return $this->_params;
	}

	/**
	 * return Statistic params as objet
	 *
	 * @return object
	 */
	function getBaseParams()
	{
		$app = Factory::getApplication();

		$paramsdata = $this->baseparams;
		$paramsdefs = JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'statistics' . DIRECTORY_SEPARATOR . 'base.xml';
		$params     = new Registry($paramsdata, $paramsdefs);

		return $params;
	}

	/**
	 * return Statistic params as objet
	 *
	 * @return object
	 */
	function getClassParams()
	{
		// Cannot use __FILE__ because extensions can have their own stats
		$rc         = new ReflectionClass(get_class($this));
		$currentdir = dirname($rc->getFileName());
		$paramsdata = $this->params;
		$paramsdefs = $currentdir . DIRECTORY_SEPARATOR . $this->_name . '.xml';
		$params     = new Registry($paramsdata, $paramsdefs);

		return $params;
	}

	/**
	 * SMStatistic::getQuotedSids()
	 *
	 * @param   string  $ids
	 *
	 * @return
	 */
	function getQuotedSids($id_field = 'stat_ids')
	{
		$app    = Factory::getApplication();
		$params = self::getParams();

		$event_ids = $params->get($id_field);

		if (!count($event_ids))
		{
//			Log::add(Text::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id), Log::WARNING, 'jsmerror');
			return (array(0));
		}

		$db  = sportsmanagementHelper::getDBConnection();
		$ids = array();

		foreach ($event_ids as $s)
		{
			$ids[] = $db->Quote((int) $s);
		}

		return $ids;
	}

	/**
	 * Binds a named array/hash to this object
	 *
	 * Can be overloaded/supplemented by the child class
	 *
	 * @access public
	 *
	 * @param  $from      mixed    An associative array or object
	 * @param  $ignore    mixed    An array or space separated list of fields not to bind
	 *
	 * @return boolean
	 */
	function bind($from, $ignore = array())
	{
		$fromArray  = is_array($from);
		$fromObject = is_object($from);

		if (!$fromArray && !$fromObject)
		{
			$this->setError(get_class($this) . '::bind failed. Invalid from argument');

			return false;
		}

		if (!is_array($ignore))
		{
			$ignore = explode(' ', $ignore);
		}

		foreach ($this->getProperties() as $k => $v)
		{
			// Internal attributes of an object are ignored
			if (!in_array($k, $ignore))
			{
				if ($fromArray && isset($from[$k]))
				{
					$this->$k = $from[$k];
				}
				elseif ($fromObject && isset($from->$k))
				{
					$this->$k = $from->$k;
				}
			}
		}

		return true;
	}

	/**
	 * return path to xml config file associated to this statistic
	 *
	 * @return object
	 */
	public function getXmlPath()
	{
		// Cannot use __FILE__ because extensions can have their own stats
		$rc         = new ReflectionClass(get_class($this));
		$currentdir = dirname($rc->getFileName());

		return $currentdir . DIRECTORY_SEPARATOR . $this->_name . '.xml';
	}

	/**
	 * return a parameter value
	 *
	 * @param   string name
	 * @param   mixed default value
	 *
	 * @return mixed
	 */
	function getParam($name, $default = '')
	{
		$params = self::getParams();

		return $params->get($name, $default);
	}

	/**
	 * is the stat calculated
	 * in this case, no user input
	 *
	 * @return boolean
	 */
	function getCalculated()
	{
		return $this->_calculated;
	}

	/**
	 * show stat in single match views/reports?
	 * e.g.: stats 'per game' should not be displayed in match report...
	 *
	 * @return boolean
	 */
	function showInSingleMatchReports()
	{
		return $this->_showinsinglematchreports;
	}

	/**
	 * show stat in match report?
	 * setting in every statistic (backend) on which frontend views the stat should be shown
	 *
	 * @return boolean
	 */
	function showInMatchReport()
	{
		$params = self::getParams();

		// $statistic_views = explode(',', $params->get('statistic_views'));
		$statistic_views = $params->get('statistic_views');

		if (!count($statistic_views))
		{
//			Log::add(get_class($this) . ' ' . __FUNCTION__ . ' ' . __LINE__ . ' ' . Text::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id), Log::WARNING, 'jsmerror');
			return (array(0));
		}

		if (in_array("matchreport", $statistic_views) || empty($statistic_views[0]))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * show stat in team roster?
	 * setting in every statistic (backend) on which frontend views the stat should be shown
	 *
	 * @return boolean
	 */
	function showInRoster()
	{
		$params = self::getParams();

		// $statistic_views = explode(',', $params->get('statistic_views'));
		$statistic_views = $params->get('statistic_views');

		if (!count($statistic_views))
		{
//			Log::add(get_class($this) . ' ' . __FUNCTION__ . ' ' . __LINE__ . ' ' . Text::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id), Log::WARNING, 'jsmerror');
			return (array(0));
		}

		if (in_array("roster", $statistic_views) || empty($statistic_views[0]))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * show stat in player page?
	 * setting in every statistic (backend) on which frontend views the stat should be shown
	 *
	 * @return boolean
	 */
	function showInPlayer()
	{
		$params = self::getParams();

		// $statistic_views = explode(',', $params->get('statistic_views'));
		$statistic_views = $params->get('statistic_views');

		if (!count($statistic_views))
		{
//			Log::add(get_class($this) . ' ' . __FUNCTION__ . ' ' . __LINE__ . ' ' . Text::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id), Log::WARNING, 'jsmerror');
			return (array(0));
		}

		if (in_array("player", $statistic_views) || empty($statistic_views[0]))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	
	/**
	 * SMStatistic::getImage()
	 * 
	 * @param integer $events_picture_height
	 * @return
	 */
	function getImage($events_picture_height = 30)
	{
		if (!empty($this->icon))
		{
			$iconPath = $this->icon;

			if (!strpos(" " . $iconPath, "/"))
			{
				$iconPath = "images/com_sportsmanagement/database/statistics/" . $iconPath;
			}

			return HTMLHelper::_('image',$iconPath, Text::_($this->name), array("title" => Text::_($this->name), "style" => 'width: auto;height: ' . $events_picture_height . 'px'));
		}

		return '<span class="stat-alternate hasTip" title="' . Text::_($this->name) . '">' . Text::_($this->short) . '</span>';
	}

	/**
	 * return the stat value for specified player in a specific game
	 *
	 * @param   object  $gamemodel  must provide methods getPlayersStats and getPlayersEvents
	 * @param   int     $teamplayer_id
	 *
	 * @return mixed stat value
	 */
	function getMatchPlayerStat(&$gamemodel, $teamplayer_id)
	{
//		Log::add($this->_name . ': ' . Text::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'), Log::WARNING, 'jsmerror');
		return 0;
	}

	/**
	 * return all players stat for the game indexed by teamplyer_id
	 *
	 * @param   int match_id
	 *
	 * @return value
	 */
	function getMatchPlayersStats($match_id)
	{
		Log::add($this->_name . ': ' . Text::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'), Log::WARNING, 'jsmerror');

		return 0;
	}

	/**
	 * return stat for all player games in the project
	 *
	 * @param   int match_id
	 *
	 * @return value
	 */
	function getPlayerStatsByGame($teamplayer_id, $project_id)
	{
//		Log::add($this->_name . ': ' . Text::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'), Log::WARNING, 'jsmerror');
		return 0;
	}

	/**
	 * return player stats in project if project_id != 0, otherwise player stats in whole player's career
	 *
	 * @param   int person_id
	 *
	 * @return float
	 */
	function getPlayerStatsByProject($person_id, $projectteam_id = 0, $project_id = 0, $sports_type_id = 0)
	{
//		Log::add($this->_name . ': ' . Text::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'), Log::WARNING, 'jsmerror');
		return 0;
	}

	/**
	 * Get players stats
	 *
	 * @param  $team_id
	 * @param  $project_id
	 * @param  $position_id
	 *
	 * @return array
	 */
	function getRosterStats($team_id, $project_id, $position_id)
	{
//		Log::add($this->_name . ': ' . Text::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'), Log::WARNING, 'jsmerror');
		return 0;
	}

	/**
	 * returns players ranking
	 *
	 * @param   int project_id
	 * @param   int division_id
	 * @param   int limit
	 * @param   in limitstart
	 *
	 * @return array
	 */
	function getPlayersRanking($project_id, $division_id, $team_id, $limit = 20, $limitstart = 0, $order = null)
	{
//		Log::add($this->_name . ': ' . Text::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'), Log::WARNING, 'jsmerror');
		return array();
	}

	/**
	 * returns teams ranking
	 *
	 * @param   int project_id
	 * @param   int limit
	 * @param   in limitstart
	 *
	 * @return array
	 */
	function getTeamsRanking($project_id, $limit = 20, $limitstart = 0, $order = null, $select = '', $statistic_id = 0)
	{
		$app        = Factory::getApplication();
		$db         = sportsmanagementHelper::getDBConnection();
		$query_core = $db->getQuery(true);

		switch ($this->_name)
		{
			case 'basic':
			case 'complexsum':
			case 'sumstats':
				$query_core->select($select);
				$query_core->from('#__sportsmanagement_season_team_person_id AS tp');
				$query_core->join('INNER', '#__sportsmanagement_person AS p ON p.id = tp.person_id ');
				$query_core->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
				$query_core->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
				$query_core->join('INNER', '#__sportsmanagement_team AS t ON st.team_id = t.id');
				$query_core->join('INNER', '#__sportsmanagement_match_statistic AS ms ON ms.teamplayer_id = tp.id AND ms.statistic_id IN ( ' . $statistic_id . ' )');
				$query_core->join('INNER', '#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1');
				$query_core->where('pt.project_id = ' . $project_id);

				return $query_core;
				break;
			default:
				Log::add($this->_name . ': ' . Text::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'), Log::WARNING, 'jsmerror');

				return array();
				break;
		}

	}

	/**
	 * return the stat value for specified player in a specific game
	 *
	 * @param   object  $gamemodel  must provide methods getPlayersStats and getPlayersEvents
	 * @param   int     $teamstaff_id
	 *
	 * @return mixed stat value
	 */
	function getMatchStaffStat(&$gamemodel, $team_staff_id)
	{
//		Log::add($this->_name . ': ' . Text::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'), Log::WARNING, 'jsmerror');
		return 0;
	}

	/**
	 * return staff stat in project
	 *
	 * @param   int person_id
	 * @param   int project_id
	 *
	 * @return float
	 */
	function getStaffStats($person_id, $team_id, $project_id)
	{
//		Log::add($this->_name . ': ' . Text::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'), Log::WARNING, 'jsmerror');
		return 0;
	}

	/**
	 * return player stats in project
	 *
	 * @param   int person_id
	 * @param   int project_id
	 *
	 * @return float
	 */
	function getHistoryStaffStats($person_id)
	{
//		Log::add($this->_name . ': ' . Text::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'), Log::WARNING, 'jsmerror');
		return 0;
	}

	/**
	 * SMStatistic::formatZeroValue()
	 *
	 * @return
	 */
	function formatZeroValue()
	{
		return number_format(0, self::getPrecision());
	}

	/**
	 * SMStatistic::getPrecision()
	 *
	 * @return
	 */
	function getPrecision()
	{
		$params = self::getParams();

		return $params->get('precision', 2);
	}

	/**
	 * return the player stats per match for given statistics IDs, for given project,
	 * using weighting factors if given
	 *
	 * @param   int teamplayer IDs
	 * @param   array statistic IDs
	 * @param   int project_id
	 * @param   array factors to be used for each statistics type
	 *
	 * @return array of stdclass objects with statistics info
	 */
	protected function getPlayerStatsByGameForIds($teamplayer_ids, $project_id, $sids, $factors = null)
	{
		$app   = Factory::getApplication();
		$db    = sportsmanagementHelper::getDBConnection();
		$query = Factory::getDbo()->getQuery(true);

		$quoted_sids = array();

		foreach ($sids as $sid)
		{
			$quoted_sids[] = $db->Quote($sid);
		}

		$quoted_tpids = array();

		foreach ($teamplayer_ids as $tpid)
		{
			$quoted_tpids[] = $db->Quote($tpid);
		}

		if (isset($factors))
		{
			$query->select('ms.value AS value, ms.match_id, ms.statistic_id');
		}
		else
		{
			$query->select('SUM(ms.value) AS value, ms.match_id');
		}

		$query->from('#__sportsmanagement_match_statistic AS ms');
		$query->join('INNER', '#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1');
		$query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.id = ms.teamplayer_id ');
		$query->join('INNER', '#__sportsmanagement_project_position AS ppos ON ppos.id = tp.project_position_id ');
		$query->join('INNER', '#__sportsmanagement_position AS pos ON ppos.position_id = pos.id ');
		$query->join('INNER', '#__sportsmanagement_position_statistic AS ps ON ps.position_id = pos.id AND ps.statistic_id = ms.statistic_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query->join('INNER', '#__sportsmanagement_project AS p ON pt.project_id = p.id AND p.id=' . $db->Quote($project_id));
		$query->where('ms.teamplayer_id IN (' . implode(',', $quoted_tpids) . ')');
		$query->where('p.published = 1');
		$query->where('ms.statistic_id IN (' . implode(',', $quoted_sids) . ')');

		if (isset($factors))
		{
			$db->setQuery($query);

			try
			{
				$stats = $db->loadObjectList();
			}
			catch (Exception $e)
			{
				$msg  = $e->getMessage(); // Returns "Normally you would have other code...
				$code = $e->getCode(); // Returns '500';
				Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
			}

			// Apply weighting using factors
			$res = array();

			foreach ($stats as $stat)
			{
				$key = array_search($stat->statistic_id, $sids);

				if ($key !== false)
				{
					if (!isset($res[$stat->match_id]))
					{
						$res[$stat->match_id]           = new stdclass;
						$res[$stat->match_id]->match_id = $stat->match_id;
						$res[$stat->match_id]->value    = 0;
					}

					$res[$stat->match_id]->value += $factors[$key] * $stat->value;
				}
			}
		}
		else
		{
			$query->group('ms.match_id');
			$db->setQuery($query);

			try
			{
				$res = $db->loadObjectList('match_id');
			}
			catch (Exception $e)
			{
				$msg  = $e->getMessage(); // Returns "Normally you would have other code...
				$code = $e->getCode(); // Returns '500';
				Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
			}
		}

		if (is_array($res) && count($res) > 0)
		{
			// Determine total for the whole project
			$total        = new stdclass;
			$total->value = 0;

			foreach ($res as $match)
			{
				$total->value += $match->value;
			}

			$res['totals'] = $total;
		}

		return $res;
	}

	/**
	 * return the player stats per project for given statistics IDs, for given project,
	 * using weighting factors if given
	 *
	 * @param   int person_id
	 * @param   int projectteam_id
	 * @param   int project_id
	 * @param   int sports_type_id
	 * @param   array statistic IDs
	 * @param   array factors to be used for each statistics type
	 *
	 * @return float
	 */
	protected function getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, $sids, $factors = null)
	{
		$app   = Factory::getApplication();
		$db    = sportsmanagementHelper::getDBConnection();
		$query = Factory::getDbo()->getQuery(true);

		$quoted_sids = array();

		foreach ($sids as $sid)
		{
			$quoted_sids[] = $db->Quote($sid);
		}

		if (isset($factors))
		{
			$query->select('ms.value AS value, ms.statistic_id');
		}
		else
		{
			$query->select('SUM(ms.value) AS value');
		}

		$query->from('#__sportsmanagement_match_statistic AS ms');
		$query->join('INNER', '#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1');
		$query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.id = ms.teamplayer_id ');
		$query->join('INNER', '#__sportsmanagement_project_position AS ppos ON ppos.id = tp.project_position_id ');
		$query->join('INNER', '#__sportsmanagement_position AS pos ON ppos.position_id = pos.id ');
		$query->join('INNER', '#__sportsmanagement_position_statistic AS ps ON ps.position_id = pos.id AND ps.statistic_id = ms.statistic_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query->join('INNER', '#__sportsmanagement_project AS p ON pt.project_id = p.id');
		$query->where('tp.person_id = ' . $person_id);
		$query->where('ms.statistic_id IN (' . implode(',', $quoted_sids) . ')');

		if ($projectteam_id)
		{
			$query->where('pt.id = ' . $projectteam_id);
		}

		if ($project_id)
		{
			$query->where('p.id = ' . $project_id);
			$query->where('pt.project_id = ' . $project_id);
			$query->where('ppos.project_id = ' . $project_id);
		}

		if ($sports_type_id)
		{
			$query->where('p.sports_type_id = ' . $sports_type_id);
		}

		if (isset($factors))
		{
			$db->setQuery($query);
			$stats = $db->loadObjectList();

			// Apply weighting using factors
			$res = 0;

			foreach ($stats as $stat)
			{
				$key = array_search($stat->statistic_id, $sids);

				if ($key !== false)
				{
					$res += $factors[$key] * $stat->value;
				}
			}
		}
		else
		{
			$db->setQuery($query, 0, 1);
			$res = $db->loadResult();

			if (!isset($res))
			{
				$res = 0;
			}
		}

		return $res;
	}

	/**
	 * return the number of games the player played for given project
	 *
	 * @param   int person_id
	 * @param   int project_id, if 0 then the number of played games for the complete career are returned
	 *
	 * @return integer
	 */
	protected function getGamesPlayedByPlayer($person_id, $projectteam_id, $project_id, $sports_type_id)
	{
		$db       = sportsmanagementHelper::getDBConnection();
		$app      = Factory::getApplication();
		$query    = Factory::getDbo()->getQuery(true);
		$query_mp = Factory::getDbo()->getQuery(true);
		$query_ms = Factory::getDbo()->getQuery(true);
		$query_me = Factory::getDbo()->getQuery(true);

		/**
		 *          To be robust against partly filled in information for a match (match player, statistic, event)
		 *          we determine if a player was contributing to a match, by checking for the following conditions:
		 *          1. the player is registered as a player for the match
		 *          2. the player has a statistic registered for the match
		 *          3. the player has an event registered for the match
		 *          If any of these conditions are met, we assume the player was part of the match
		 */

		if ($projectteam_id)
		{
			$query_mp->where('pt.id = ' . $projectteam_id);
		}

		if ($project_id)
		{
			$query_mp->where('p.id = ' . $project_id);
		}

		if ($sports_type_id)
		{
			$query_mp->where('p.sports_type_id = ' . $sports_type_id);
		}

		/**
		 *          Use md (stands for match detail, where the detail can be a match_player, match_statistic or match_event)
		 *          All of them have a match_id and teamplayer_id.
		 */

		$query_mp->select('m.id AS mid, tp.person_id');
		$query_mp->from('#__sportsmanagement_match_player AS md');
		$query_mp->join('INNER', '#__sportsmanagement_match AS m ON m.id = md.match_id');
		$query_mp->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.id = md.teamplayer_id ');
		$query_mp->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query_mp->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query_mp->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt.project_id');
		$query_mp->where('tp.person_id = ' . $person_id);
		$query_mp->where('(md.came_in = 0 OR md.came_in = 1)');
		$query_mp->where('m.published = 1');
		$query_mp->where('m.team1_result IS NOT NULL');
		$query_mp->group('m.id');

		$query->select('COUNT(m.id)');
		$query->from('#__sportsmanagement_match AS m');
		$query->join('LEFT', '(' . $query_mp . ') AS mp ON mp.mid = m.id');

		$query->where('mp.person_id = ' . $person_id);

		$db->setQuery($query);

		$res = $db->loadResult();

		return $res;
	}

	/**
	 * return the player stats for events per project for given statistics IDs, for given project,
	 * using weighting factors if given
	 *
	 * @param   int person_id
	 * @param   int projectteam_id
	 * @param   int project_id
	 * @param   int sports_type_id
	 * @param   array statistic IDs
	 *
	 * @return float
	 */
	protected function getPlayerStatsByProjectForEvents($person_id, $projectteam_id, $project_id, $sports_type_id, $sids)
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$db     = sportsmanagementHelper::getDBConnection();
		$query  = $db->getQuery(true);

		if ($sids)
		{
			$quoted_sids = array();

			foreach ($sids as $sid)
			{
				$quoted_sids[] = $db->Quote($sid);
			}

			$query->select('SUM(me.event_sum) AS value, tp.person_id');
			$query->from('#__sportsmanagement_season_team_person_id AS tp');
			$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
			$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');

			if ($sports_type_id)
			{
				$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt.project_id AND p.sports_type_id = ' . $sports_type_id);
			}

			$query->join('INNER', '#__sportsmanagement_match_event AS me ON me.teamplayer_id = tp.id AND me.event_type_id IN (' . implode(',', $quoted_sids) . ')');
			$query->join('INNER', '#__sportsmanagement_match AS m ON m.id = me.match_id AND m.published = 1');
			$query->where('tp.person_id = ' . $person_id);

			if ($projectteam_id)
			{
				$query->where('pt.id = ' . $projectteam_id);
			}

			if ($project_id)
			{
				$query->where('pt.project_id = ' . $project_id);
			}

			$query->group('tp.person_id');

			$db->setQuery($query);

			try
			{
				$res = $db->loadResult();
			}
			catch (Exception $e)
			{
				$msg  = $e->getMessage(); // Returns "Normally you would have other code...
				$code = $e->getCode(); // Returns '500';
				Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
			}
		}

		if (!isset($res))
		{
			$res = 0;
		}

		return $res;
	}

	/**
	 * return the statistics for given team and project
	 *
	 * @param   int team_id
	 * @param   int statistics ids (array)
	 * @param   int project_id
	 * @param   int array with multiplication factors for each sids entry
	 *
	 * @return array of integers
	 */
	protected function getRosterStatsForIds($team_id, $project_id, $position_id, $sids, $factors = null)
	{
		$db    = sportsmanagementHelper::getDBConnection();
		$app   = Factory::getApplication();
		$query = Factory::getDbo()->getQuery(true);

		$quoted_sids = array();

		foreach ($sids as $sid)
		{
			$quoted_sids[] = $db->Quote($sid);
		}

		if (isset($factors))
		{
			$query->select('ms.value AS value, tp.person_id, ms.statistic_id');
		}
		else
		{
			// $query = '';
			$query->clear('select');
		}

		$query->from('#__sportsmanagement_season_team_person_id AS tp');
		$query->join('INNER', '#__sportsmanagement_person AS prs ON prs.id = tp.person_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query->join('INNER', '#__sportsmanagement_match_statistic AS ms ON ms.teamplayer_id = tp.id AND ms.statistic_id IN (' . implode(',', $quoted_sids) . ')');
		$query->join('INNER', '#__sportsmanagement_project_position AS ppos ON ppos.id = tp.project_position_id ');
		$query->join('INNER', '#__sportsmanagement_position AS pos ON pos.id = ppos.position_id ');
		$query->join('INNER', '#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1 ');
		$query->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id AND r.project_id = pt.project_id ');
		$query->where('st.team_id = ' . $team_id);
		$query->where('pt.project_id = ' . $project_id);
		$query->where('ppos.position_id = ' . $position_id);
		$query->where('r.project_id = ' . $project_id);

		if (isset($factors))
		{
			$db->setQuery($query);

			try
			{
				$stats = $db->loadObjectList();
			}
			catch (Exception $e)
			{
				$msg  = $e->getMessage(); // Returns "Normally you would have other code...
				$code = $e->getCode(); // Returns '500';
				Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
			}

			// Apply weighting using factors
			$res                  = array();
			$res['totals']        = new stdclass;
			$res['totals']->value = 0;

			foreach ($stats as $stat)
			{
				$key = array_search($stat->statistic_id, $sids);

				if ($key !== false)
				{
					if (!isset($res[$stat->person_id]))
					{
						$res[$stat->person_id]            = new stdclass;
						$res[$stat->person_id]->person_id = $stat->person_id;
						$res[$stat->person_id]->value     = 0;
					}

					$contribution                 = $factors[$key] * $stat->value;
					$res[$stat->person_id]->value += $contribution;
					$res['totals']->value         += $contribution;
				}
			}
		}
		else
		{
			// Determine the statistics per project team player
			$query->clear('select');
			$query->clear('group');
			$query->select('SUM(ms.value) AS value, tp.person_id');
			$query->group('tp.person_id');
			$db->setQuery($query);

			try
			{
				$res = $db->loadObjectList('person_id');
			}
			catch (Exception $e)
			{
				$msg  = $e->getMessage(); // Returns "Normally you would have other code...
				$code = $e->getCode(); // Returns '500';
				Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
			}

			// Determine the total statistics for the position_id of the project team
			$query->clear('select');
			$query->clear('group');
			$query->select('SUM(ms.value) AS value');
			$db->setQuery($query);

			try
			{
				$res['totals']        = new stdclass;
				$res['totals']->value = $db->loadResult();
			}
			catch (Exception $e)
			{
				$msg  = $e->getMessage(); // Returns "Normally you would have other code...
				$code = $e->getCode(); // Returns '500';
				Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
			}

			if (!isset($res['totals']->value))
			{
				$res['totals']->value = 0;
			}
		}

		return $res;
	}

	/**
	 * return the number of games the player played for given project
	 *
	 * @param   int team_id
	 * @param   int project_id
	 *
	 * @return array of integers
	 */
	protected function getGamesPlayedByProjectTeam($team_id, $project_id, $position_id)
	{
		$db = sportsmanagementHelper::getDBConnection();

		$app      = Factory::getApplication();
		$query    = Factory::getDbo()->getQuery(true);
		$subquery = Factory::getDbo()->getQuery(true);
		$query_mp = Factory::getDbo()->getQuery(true);
		$query_ms = Factory::getDbo()->getQuery(true);
		$query_me = Factory::getDbo()->getQuery(true);

		$mp_array     = array();
		$mptpid_array = array();
		$ms_array     = array();
		$mstpid_array = array();
		$me_array     = array();
		$metpid_array = array();

		// Get the number of matches played per teamplayer of the projectteam.
		// This is derived from three tables: match_player, match_statistic and match_event
		// Use md (stands for match detail, where the detail can be a match_player, match_statistic or match_event)
		// All of them have a match_id and teamplayer_id.
		$query_mp->clear();
		$query_mp->select('DISTINCT m.id AS mid, tp.id AS tpid');
		$query_mp->from('#__sportsmanagement_match_player AS md');
		$query_mp->join('INNER', '#__sportsmanagement_match AS m ON m.id = md.match_id');
		$query_mp->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id');
		$query_mp->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id');
		$query_mp->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.project_id = p.id');
		$query_mp->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id');
		$query_mp->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.id = md.teamplayer_id and st.team_id = tp.team_id');

		$query_mp->where('pt.project_id = ' . $project_id);
		$query_mp->where('p.id = ' . $project_id);
		$query_mp->where('st.team_id = ' . $team_id);
		$query_mp->where('(md.came_in = 0 OR md.came_in = 1)');

		$db->setQuery($query_mp);
		$res_mp = $db->loadObjectList();

		foreach ($res_mp as $mp)
		{
			$mp_array[]     = $mp->mid;
			$mptpid_array[] = $mp->tpid;
		}

		$comma_separated_mp     = implode(",", $mp_array);
		$comma_separated_mptpid = implode(",", $mptpid_array);

		$query_ms->clear();
		$query_ms->select('DISTINCT m.id AS mid, tp.id AS tpid');
		$query_ms->from('#__sportsmanagement_match_statistic AS md');
		$query_ms->join('INNER', '#__sportsmanagement_match AS m ON m.id = md.match_id');

		$query_ms->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id');
		$query_ms->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id');
		$query_ms->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.project_id = p.id');
		$query_ms->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id');
		$query_ms->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.id = md.teamplayer_id and st.team_id = tp.team_id');

		$query_ms->where('pt.project_id = ' . $project_id);
		$query_ms->where('st.team_id = ' . $team_id);

		$db->setQuery($query_ms);
		$res_ms = $db->loadObjectList();

		foreach ($res_ms as $ms)
		{
			$ms_array[]     = $ms->mid;
			$mstpid_array[] = $ms->tpid;
		}

		$comma_separated_ms     = implode(",", $ms_array);
		$comma_separated_mstpid = implode(",", $mstpid_array);

		$query_me->clear();
		$query_me->select('DISTINCT m.id AS mid, tp.id AS tpid');
		$query_me->from('#__sportsmanagement_match_event AS md');
		$query_me->join('INNER', '#__sportsmanagement_match AS m ON m.id = md.match_id');

		$query_me->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id');
		$query_me->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id');
		$query_me->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.project_id = p.id');
		$query_me->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id');
		$query_me->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.id = md.teamplayer_id and st.team_id = tp.team_id');

		$query_me->where('pt.project_id = ' . $project_id);
		$query_me->where('st.team_id = ' . $team_id);

		$db->setQuery($query_me);
		$res_me = $db->loadObjectList();

		foreach ($res_me as $me)
		{
			$me_array[]     = $me->mid;
			$metpid_array[] = $me->tpid;
		}

		$comma_separated_me     = implode(",", $me_array);
		$comma_separated_metpid = implode(",", $metpid_array);

		$subquery->select('DISTINCT m.id as mid, tp.id as tpid, tp.person_id');
		$subquery->from('#__sportsmanagement_match AS m');
		$subquery->join('INNER', '#__sportsmanagement_round as r ON m.round_id=r.id ');
		$subquery->join('INNER', '#__sportsmanagement_project AS p ON p.id=r.project_id ');
		$subquery->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ');
		$subquery->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$subquery->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');

		if (!$comma_separated_mptpid)
		{
			$comma_separated_mptpid = 0;
		}

		if (!$comma_separated_mstpid)
		{
			$comma_separated_mstpid = 0;
		}

		if (!$comma_separated_metpid)
		{
			$comma_separated_metpid = 0;
		}

		$subquery->where('( tp.id IN (' . $comma_separated_mptpid . ') OR tp.id IN (' . $comma_separated_mstpid . ') OR tp.id IN (' . $comma_separated_metpid . ') )');

		if ($comma_separated_mp)
		{
			$subquery->where('m.id IN (' . $comma_separated_mp . ')');
		}

		if ($comma_separated_ms)
		{
			$subquery->where('m.id IN (' . $comma_separated_ms . ')');
		}

		if ($comma_separated_me)
		{
			$subquery->where('m.id IN (' . $comma_separated_me . ')');
		}

		$subquery->where('st.team_id = ' . $team_id);
		$subquery->where('p.id = ' . $project_id);
		$subquery->where('p.published = 1');
		$subquery->where('m.published = 1');

		$query->select('pse.person_id, COUNT(pse.mid) AS value');
		$query->from('( ' . $subquery . ' ) AS pse');
		$query->group('pse.tpid');

		$db->setQuery($query);
		$res = $db->loadObjectList('person_id');

		$res['totals']        = new stdclass;
		$res['totals']->value = 0;

		if (is_array($res) && !empty($res))
		{
			foreach ($res as $person)
			{
				$res['totals']->value += $person->value;
			}
		}

		return $res;
	}

	/**
	 * return the player stats for events per project for given statistics IDs, for given project,
	 * using weighting factors if given
	 *
	 * @param   int person_id
	 * @param   array statistic IDs
	 * @param   int project_id, if 0 then the complete career statistics of the player are returned
	 *
	 * @return float
	 */
	protected function getRosterStatsForEvents($team_id, $project_id, $position_id, $sids)
	{
		$db    = sportsmanagementHelper::getDBConnection();
		$query = Factory::getDbo()->getQuery(true);
		$app   = Factory::getApplication();

		$quoted_sids = array();

		foreach ($sids as $sid)
		{
			$quoted_sids[] = $db->Quote($sid);
		}

		// Determine the events for each project team player
		$query->select('SUM(es.event_sum) AS value, tp.person_id');
		$query->from('#__sportsmanagement_season_team_person_id AS tp');
		$query->join('INNER', '#__sportsmanagement_person AS prs ON prs.id = tp.person_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');

		$query->join('LEFT', '#__sportsmanagement_project_position AS ppos ON ppos.id = tp.project_position_id');
		$query->join('LEFT', '#__sportsmanagement_position AS pos ON pos.id=ppos.position_id');
		$query->join('LEFT', '#__sportsmanagement_match_event AS es ON es.teamplayer_id = tp.id AND es.event_type_id IN (' . implode(',', $quoted_sids) . ')');
		$query->join('LEFT', '#__sportsmanagement_match AS m ON m.id = es.event_type_id AND m.published = 1');
		$query->where('st.team_id = ' . $team_id);
		$query->where('pt.project_id = ' . $project_id);
		$query->where('ppos.position_id = ' . $position_id);
		$query->group('tp.id');

		$db->setQuery($query);

		$res = $db->loadObjectList('person_id');

		// Determine the event totals for the position_id of the project team
		$query->clear('select');
		$query->clear('group');
		$query->select('SUM(es.event_sum) AS value');

		$db->setQuery($query);

		$res['totals']        = new stdclass;
		$res['totals']->value = $db->loadResult();

		return $res;
	}

	/**
	 * SMStatistic::getGamesPlayedQuery()
	 *
	 * @param   mixed  $project_id
	 * @param   mixed  $division_id
	 * @param   mixed  $team_id
	 *
	 * @return
	 */
	protected function getGamesPlayedQuery($project_id, $division_id, $team_id)
	{
		$db       = sportsmanagementHelper::getDBConnection();
		$app      = Factory::getApplication();
		$query    = Factory::getDbo()->getQuery(true);
		$subquery = Factory::getDbo()->getQuery(true);
		$query_mp = Factory::getDbo()->getQuery(true);
		$query_ms = Factory::getDbo()->getQuery(true);
		$query_me = Factory::getDbo()->getQuery(true);

		/**
		 *         To be robust against partly filled in information for a match (match player, statistic, event)
		 *         we determine if a player was contributing to a match, by checking for the following conditions:
		 *         1. the player is registered as a player for the match
		 *         2. the player has a statistic registered for the match
		 *         3. the player has an event registered for the match
		 *         If any of these conditions are met, we assume the player was part of the match
		 */

		/**
		 *         Use md (stands for match detail, where the detail can be a match_player, match_statistic or match_event)
		 *         All of them have a match_id and teamplayer_id.
		 */
		$query_mp->select('m.id AS mid, tp.id AS tpid');
		$query_mp->from('#__sportsmanagement_match_player AS md');
		$query_mp->join('INNER', '#__sportsmanagement_match AS m ON m.id = md.match_id');
		$query_mp->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.id = md.teamplayer_id ');
		$query_mp->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query_mp->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query_mp->where('pt.project_id = ' . $project_id);
		$query_mp->where('m.published = 1');
		$query_mp->where('m.team1_result IS NOT NULL');

		if ($division_id)
		{
			$query_mp->where('pt.division_id = ' . $division_id);
		}

		if ($team_id)
		{
			$query_mp->where('st.team_id = ' . $team_id);
		}

		$query_mp->where('(md.came_in = 0 OR md.came_in = 1)');
		$query_mp->group('m.id, tp.id');

		$query_ms->select('m.id AS mid, tp.id AS tpid');
		$query_ms->from('#__sportsmanagement_match_statistic AS md');
		$query_ms->join('INNER', '#__sportsmanagement_match AS m ON m.id = md.match_id');
		$query_ms->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.id = md.teamplayer_id ');
		$query_ms->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query_ms->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query_ms->where('pt.project_id = ' . $project_id);
		$query_ms->where('m.published = 1');
		$query_ms->where('m.team1_result IS NOT NULL');

		if ($division_id)
		{
			$query_ms->where('pt.division_id = ' . $division_id);
		}

		if ($team_id)
		{
			$query_ms->where('st.team_id = ' . $team_id);
		}

		$query_ms->group('m.id, tp.id');
		$query_me->select('m.id AS mid, tp.id AS tpid');
		$query_me->from('#__sportsmanagement_match_event AS md');
		$query_me->join('INNER', '#__sportsmanagement_match AS m ON m.id = md.match_id');
		$query_me->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.id = md.teamplayer_id ');
		$query_me->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query_me->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query_me->where('pt.project_id = ' . $project_id);
		$query_me->where('m.published = 1');
		$query_me->where('m.team1_result IS NOT NULL');

		if ($division_id)
		{
			$query_me->where('pt.division_id = ' . $division_id);
		}

		if ($team_id)
		{
			$query_me->where('st.team_id = ' . $team_id);
		}

		$query_me->group('m.id, tp.id');

		$subquery->select('m.id AS mid, tp.person_id, tp.id AS tpid');
		$subquery->from('#__sportsmanagement_person AS p');
		$subquery->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = p.id ');
		$subquery->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$subquery->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$subquery->join('INNER', '#__sportsmanagement_match AS m ON m.projectteam1_id = pt.id OR m.projectteam2_id = pt.id');

		$subquery->join('LEFT', '( ' . $query_mp . ' ) AS mp ON mp.mid = m.id AND mp.tpid = tp.id ');
		$subquery->join('LEFT', '( ' . $query_ms . ' ) AS ms ON ms.mid = m.id AND ms.tpid = tp.id ');
		$subquery->join('LEFT', '( ' . $query_me . ' ) AS me ON me.mid = m.id AND me.tpid = tp.id ');

		$subquery->where('pt.project_id = ' . $project_id);
		$subquery->where('p.published = 1');
		$subquery->where('m.published = 1');
		$subquery->where('(mp.tpid = tp.id OR ms.tpid = tp.id OR me.tpid = tp.id)');

		if ($division_id)
		{
			$subquery->where('pt.division_id = ' . $division_id);
		}

		if ($team_id)
		{
			$subquery->where('st.team_id = ' . $team_id);
		}

		$subquery->group('m.id, tp.id');

		$query->select('gp.person_id, gp.tpid, COUNT(gp.mid) AS played');
		$query->from('( ' . $subquery . ' ) AS gp');
		$query->group('gp.tpid');

		return $query;
	}
}
