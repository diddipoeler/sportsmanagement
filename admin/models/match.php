<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage match
 * @file       match.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * https://hotexamples.com/de/examples/-/Google_Service_Calendar_EventDateTime/-/php-google_service_calendar_eventdatetime-class-examples.html
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Log\Log;

JLoader::import('components.com_sportsmanagement.libraries.google-php.vendor.autoload', JPATH_ADMINISTRATOR);
JLoader::import('joomla.utilities.simplecrypt');

/**
 * sportsmanagementModelMatch
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 *
 * https://docs.joomla.org/Sending_email_from_extensions
 */
class sportsmanagementModelMatch extends JSMModelAdmin
{

	const MATCH_ROSTER_STARTER = 0;
	const MATCH_ROSTER_SUBSTITUTE_IN = 1;
	const MATCH_ROSTER_SUBSTITUTE_OUT = 2;
	const MATCH_ROSTER_RESERVE = 3;
	static $_season_id = 0;
	static $_project_id = 0;
	var $teams = null;
	var $storeFailedColor = 'red';

	var $storeSuccessColor = 'green';

	var $existingInDbColor = 'orange';

	var $csv_player = array();

	var $csv_in_out = array();

	var $csv_cards = array();

	var $csv_staff = array();

	var $projectteamid = 0;

	/**
	 * Override parent constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see   BaseDatabaseModel
	 * @since 3.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * sportsmanagementModelMatch::getMatchEvents()
	 *
	 * @param   integer  $match_id
	 *
	 * @return
	 */
	public static function getMatchEvents($match_id = 0)
	{
		$app        = Factory::getApplication();
		$jinput     = $app->input;
		$option     = $jinput->getCmd('option');
		$project_id = $app->getUserState("$option.pid", '0');
		$db         = sportsmanagementHelper::getDBConnection();
		$query      = $db->getQuery(true);
		$query->select('me.*,t.name AS team,et.name AS event,CONCAT(t1.firstname," \'",t1.nickname,"\' ",t1.lastname) AS player1');
		$query->from('#__sportsmanagement_match_event AS me');
		$query->join('LEFT', '#__sportsmanagement_season_team_person_id AS tp1 ON tp1.id = me.teamplayer_id');
		$query->join('LEFT', '#__sportsmanagement_season_team_id AS st1 ON st1.team_id = tp1.team_id and st1.season_id = tp1.season_id');
		$query->join('LEFT', '#__sportsmanagement_project_team AS pt1 ON st1.id = pt1.team_id');
		$query->join('LEFT', '#__sportsmanagement_person AS t1 ON t1.id = tp1.person_id AND t1.published = 1');
		$query->join('LEFT', '#__sportsmanagement_team AS t ON t.id = st1.team_id');
		$query->join('LEFT', '#__sportsmanagement_eventtype AS et ON et.id = me.event_type_id ');
		$query->join('LEFT', '#__sportsmanagement_person_project_position AS ppp on ppp.person_id = tp1.id and ppp.project_id = pt1.project_id');
		$query->where('pt1.project_id = ' . $project_id);
		$query->where('me.match_id = ' . $match_id);
		$query->order('me.event_time ASC');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * sportsmanagementModelMatch::getMatchSingleData()
	 *
	 * @param   mixed  $match_id
	 *
	 * @return
	 */
	public static function getMatchSingleData($match_id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('m.*');
		$query->from('#__sportsmanagement_match_single AS m');
		$query->where('m.match_id = ' . (int) $match_id);

		try
		{
			$db->setQuery($query);
			$result = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . Text::_($e->getMessage()), 'Error');
			$result = false;
		}

		return $result;
	}

	/**
	 * sportsmanagementModelMatch::getMatchRelationsOptions()
	 *
	 * @param   mixed    $project_id
	 * @param   integer  $excludeMatchId
	 *
	 * @return
	 */
	public static function getMatchRelationsOptions($project_id, $excludeMatchId = 0)
	{
		$app   = Factory::getApplication();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('m.id AS value,m.match_date, p.timezone, t1.name AS t1_name, t2.name AS t2_name');
		$query->from('#__sportsmanagement_match AS m');
		$query->join('LEFT', '#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id');
		$query->join('LEFT', '#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id');
		$query->join('LEFT', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
		$query->join('LEFT', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
		$query->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
		$query->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
		$query->join('LEFT', '#__sportsmanagement_project AS p ON p.id = pt1.project_id');

		$query->where('pt1.project_id = ' . (int) $project_id);
		$query->where('m.id NOT in (' . $excludeMatchId . ')');
		$query->where('m.published = 1');

		$query->order('m.match_date DESC,t1.short_name');

		try
		{
			$db->setQuery($query);
			$matches = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$matches = false;
		}

		if ($matches)
		{
			foreach ($matches as $match)
			{
				if (empty($match->timezone))
				{
					$match->timezone = 'Europe/Berlin';
				}

				sportsmanagementHelper::convertMatchDateToTimezone($match);
			}
		}

		return $matches;
	}

	/**
	 * sportsmanagementModelMatch::getteampersons()
	 *
	 * @param   mixed  $projectteam_id
	 * @param   bool   $filter
	 * @param   mixed  $persontype
	 *
	 * @return
	 */
	public static function getteampersons($projectteam_id, $filter = false, $persontype)
	{
		$option            = Factory::getApplication()->input->getCmd('option');
		$app               = Factory::getApplication();
		self::$_season_id  = $app->getUserState("$option.season_id", '0');
		self::$_project_id = $app->getUserState("$option.pid", '0');

		$query = Factory::getDbo()->getQuery(true);
		$query->select('sp.id AS value');
		$query->select('pl.firstname,pl.nickname,pl.lastname,pl.info,sp.jerseynumber,pl.ordering');
		$query->select('pos.name AS positionname');
		$query->from('#__sportsmanagement_person AS pl');
		$query->join('INNER', ' #__sportsmanagement_season_team_person_id AS sp ON sp.person_id = pl.id ');
		$query->join('INNER', ' #__sportsmanagement_season_team_id AS st ON st.team_id = sp.team_id and st.season_id = sp.season_id ');
		$query->join('LEFT', '#__sportsmanagement_person_project_position AS ppp on ppp.person_id = sp.person_id');
		$query->join('LEFT', ' #__sportsmanagement_project_position AS ppos ON ppos.id = ppp.project_position_id');
		$query->join('LEFT', ' #__sportsmanagement_position AS pos ON pos.id = ppos.position_id ');
		$query->join('INNER', ' #__sportsmanagement_project_team AS pt ON pt.team_id = st.id ');
		$query->where('pt.id = ' . $projectteam_id);
		$query->where('pl.published = 1');
		$query->where('sp.persontype = ' . $persontype);
		$query->where('ppp.persontype = ' . $persontype);
		$query->where('sp.season_id = ' . self::$_season_id);
		$query->where('ppp.project_id = ' . self::$_project_id);

		if (is_array($filter) && count($filter) > 0)
		{
			$query->where("sp.id NOT IN (" . implode(',', $filter) . ")");
		}

		$query->order("pl.lastname ASC");
		Factory::getDbo()->setQuery($query);
		$result = Factory::getDbo()->loadObjectList();

		if (!$result)
		{
			switch ($persontype)
			{
				case 1:
					$position_value = 'COM_SPORTSMANAGEMENT_SOCCER_F_PLAYERS';
					break;
				case 2:
					$position_value = 'COM_SPORTSMANAGEMENT_SOCCER_F_COACH';
					break;
			}
		}

		return $result;
	}

	/**
	 * Method to return substitutions made by a team during a match
	 * if no team id is passed,all substitutions should be returned (to be done!!)
	 *
	 * @access public
	 * @return array of substitutions
	 */
	public static function getSubstitutions($tid = 0, $match_id)
	{
		$option           = Factory::getApplication()->input->getCmd('option');
		$app              = Factory::getApplication();
		self::$_season_id = $app->getUserState("$option.season_id", '0');
		$project_id       = $app->getUserState("$option.pid", '0');
		$starttime        = microtime();
		$db               = Factory::getDbo();
		$query            = $db->getQuery(true);

		$in_out = array();
		$query->select('mp.id,mp.came_in,mp.teamplayer_id,mp.match_id,mp.in_out_time');
		$query->select('p1.firstname AS firstname, p1.nickname AS nickname, p1.lastname AS lastname');
		$query->select('p2.firstname AS out_firstname,p2.nickname AS out_nickname, p2.lastname AS out_lastname');
		$query->select('pos.name AS in_position');
		$query->from('#__sportsmanagement_match_player AS mp');
		$query->join('LEFT', ' #__sportsmanagement_season_team_person_id AS sp1 ON sp1.id = mp.teamplayer_id ');
		$query->join('LEFT', ' #__sportsmanagement_person AS p1 ON sp1.person_id = p1.id ');
		$query->join('LEFT', ' #__sportsmanagement_position AS pos ON pos.id = p1.position_id ');
		$query->join('LEFT', ' #__sportsmanagement_project_position AS ppos ON ppos.position_id = mp.project_position_id ');
		$query->join('LEFT', ' #__sportsmanagement_season_team_person_id AS sp2 ON sp2.id = mp.in_for ');
		$query->join('LEFT', ' #__sportsmanagement_person AS p2 ON sp2.person_id = p2.id ');
		$query->join('LEFT', '#__sportsmanagement_person_project_position AS ppp on ppp.person_id = sp1.id and ppp.project_id = ppos.project_id');
		$query->join('LEFT', '#__sportsmanagement_season_team_id AS st1 ON st1.team_id = sp1.team_id and st1.season_id = sp1.season_id');
		$query->join('LEFT', '#__sportsmanagement_project_team AS pt1 ON st1.id = pt1.team_id');
		$query->where('pt1.project_id = ' . $project_id);
		$query->where('mp.match_id = ' . $match_id);
		$query->where('came_in > 0');
		$query->where('pt1.id = ' . $tid);
		$query->order("(mp.in_out_time+0)");

		$query->group("mp.id,mp.came_in,mp.teamplayer_id,mp.match_id,mp.in_out_time");

		try
		{
			$db->setQuery($query);
			$in_out[$tid] = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$in_out[$tid] = false;
		}

		return $in_out;
	}

	/**
	 * returns starters player id for the specified team
	 *
	 * @param   int  $team_id
	 * @param   int  $project_position_id
	 *
	 * @return array of player ids
	 */
	public static function getRoster($team_id, $project_position_id = 0, $match_id, $position_value)
	{
		$option            = Factory::getApplication()->input->getCmd('option');
		$app               = Factory::getApplication();
		self::$_season_id  = $app->getUserState("$option.season_id", '0');
		self::$_project_id = $app->getUserState("$option.pid", '0');

		$query = Factory::getDbo()->getQuery(true);
		$query->select('mp.id AS table_id,mp.match_id,mp.teamplayer_id AS value,mp.trikot_number AS trikot_number,mp.captain AS captain');
		$query->select('pl.firstname,pl.nickname,pl.lastname,pl.info,pl.ordering,pl.position_id as person_position_id');
		$query->select('pos.name AS positionname,pos.id as position_position_id');
		$query->select('sp.jerseynumber,sp.project_position_id as stp_project_position_id');
		$query->from('#__sportsmanagement_match_player AS mp');
		$query->join('INNER', ' #__sportsmanagement_season_team_person_id AS sp ON sp.id = mp.teamplayer_id ');
		$query->join('INNER', ' #__sportsmanagement_person AS pl ON pl.id = sp.person_id ');
		$query->join('LEFT', ' #__sportsmanagement_project_position AS ppos ON ppos.position_id = mp.project_position_id ');
		$query->join('LEFT', ' #__sportsmanagement_position AS pos ON pos.id = ppos.position_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.team_id = sp.team_id ');
		$query->join('LEFT', ' #__sportsmanagement_project_team AS pt ON pt.team_id = st1.id ');
		$query->where('mp.match_id = ' . $match_id);
		$query->where('ppos.project_id = ' . self::$_project_id);
		$query->where('mp.came_in = ' . self::MATCH_ROSTER_STARTER);
		$query->where('pl.published = 1');

		if ($project_position_id > 0)
		{
			$query->where('ppos.id = ' . $project_position_id);
		}

		if ($team_id > 0)
		{
			$query->where('pt.id = ' . $team_id);
		}

		$query->order('mp.ordering ASC');
		Factory::getDbo()->setQuery($query);
		$result = Factory::getDbo()->loadObjectList('value');

		if (!$result)
		{
		}

		return $result;
	}

	/**
	 * sportsmanagementModelMatch::getMatchText()
	 *
	 * @param   mixed  $match_id
	 *
	 * @return
	 */
	public static function getMatchText($match_id = 0, $cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$db     = sportsmanagementHelper::getDBConnection(true, $cfg_which_database, false);
		$query  = $db->getQuery(true);
		$query->select('m.*');
		$query->select('t1.name as t1name,t2.name as t2name');
		$query->from('#__sportsmanagement_match AS m ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id ');
		$query->where('m.id = ' . $match_id);
		$query->where('m.published = 1');
		$query->order('m.match_date, t1.short_name');

		try
		{
			$db->setQuery($query);
			$result = $db->loadObject();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$result = false;
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}

	/**
	 * returns assigned teams referees id for the specified match
	 *
	 * @param   int  $match_id
	 *
	 * @return array of referee ids
	 */
	public static function getTeamsRefereeRoster($match_id = 0)
	{
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$result = '';

		$query->select('spi.id AS value,pr.name,pr.middle_name,pr.short_name,pr.alias');
		$query->from('#__sportsmanagement_match_referee AS mr');
		$query->join('LEFT', '#__sportsmanagement_project_team AS spi ON mr.project_referee_id=spi.id');
		$query->join('LEFT', '#__sportsmanagement_season_team_id AS st1 ON st1.id = spi.team_id');
		$query->join('LEFT', '#__sportsmanagement_team AS pr ON st1.team_id=pr.id AND pr.published = 1');
		$query->where('mr.match_id = ' . $match_id);

		try
		{
			$db->setQuery($query);
			$result = $db->loadObjectList('value');
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}

		return $result;
	}

	/**
	 * Method to return the projects referees array
	 *
	 * @access public
	 * @return array
	 * @since  0.1
	 */
	public static function getProjectReferees($already_sel = false, $project_id)
	{
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$result = '';
		$query->select('pref.id AS value,pl.firstname,pl.nickname,pl.lastname,pl.info,pos.name AS positionname');
		$query->from('#__sportsmanagement_person AS pl');
		$query->join('LEFT', '#__sportsmanagement_season_person_id AS spi ON spi.person_id=pl.id');
		$query->join('LEFT', '#__sportsmanagement_project_referee AS pref ON pref.person_id=spi.id AND pref.published=1');
		$query->join('LEFT', '#__sportsmanagement_project_position AS ppos ON ppos.id=pref.project_position_id');
		$query->join('LEFT', '#__sportsmanagement_position AS pos ON pos.id=ppos.position_id');
		$query->where('pref.project_id=' . $project_id);
		$query->where('pl.published = 1');

		if (is_array($already_sel) && count($already_sel) > 0)
		{
			$query->where('pref.id NOT IN (' . implode(',', $already_sel) . ')');
		}

		$query->order('pl.lastname ASC');

		try
		{
			$db->setQuery($query);
			$result = $db->loadObjectList('value');
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}

		return $result;
	}

	/**
	 * sportsmanagementModelMatch::getMatchPersons()
	 *
	 * @param   mixed    $projectteam_id
	 * @param   integer  $project_position_id
	 * @param   mixed    $match_id
	 * @param   mixed    $table
	 *
	 * @return
	 */
	public static function getMatchPersons($projectteam_id, $project_position_id = 0, $match_id, $table)
	{
		$app               = Factory::getApplication();
		$option            = Factory::getApplication()->input->getCmd('option');
		$query             = Factory::getDbo()->getQuery(true);
		self::$_season_id  = $app->getUserState("$option.season_id", '0');
		self::$_project_id = $app->getUserState("$option.pid", '0');

		switch ($table)
		{
			case 'player':
				$id = 'teamplayer_id';
				$query->select('mp.' . $id . ' AS tpid,mp.' . $id . ',mp.project_position_id,mp.match_id,mp.id as update_id');
				break;
			case 'staff':
				$id = 'team_staff_id';
				$query->select('mp.' . $id . ' ,mp.project_position_id,mp.match_id,mp.id as update_id');
				break;
		}

		$query->select('pt.id as projectteam_id');
		$query->select('sp.id AS value');
		$query->select('pl.firstname,pl.nickname,pl.lastname');
		$query->select('pos.name AS positionname');
		$query->select('ppos.position_id,ppos.id AS pposid');
		$query->from('#__sportsmanagement_match_' . $table . ' AS mp');
		$query->join('INNER', '#__sportsmanagement_season_team_person_id AS sp ON mp.' . $id . ' = sp.id');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = sp.team_id ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query->join('LEFT', '#__sportsmanagement_person_project_position AS ppp on ppp.person_id = sp.person_id and ppp.persontype = sp.persontype');
		$query->join('LEFT', ' #__sportsmanagement_project_position as ppos ON ppos.id = ppp.project_position_id');
		$query->join('INNER', ' #__sportsmanagement_person AS pl ON pl.id = sp.person_id');
		$query->join('INNER', ' #__sportsmanagement_position AS pos ON pos.id = ppos.position_id');
		$query->where('mp.match_id = ' . $match_id);
		$query->where('pl.published = 1');

		$query->where('ppp.project_id = ' . self::$_project_id);

		if ($projectteam_id > 0)
		{
			$query->where('pt.id = ' . $projectteam_id);
		}

		if ($project_position_id > 0)
		{
			$query->where('mp.project_position_id = ' . $project_position_id);
		}

		$query->order('mp.project_position_id, mp.ordering,	pl.lastname, pl.firstname ASC');
		Factory::getDbo()->setQuery($query);

		$result = Factory::getDbo()->loadObjectList($id);

		if (!$result)
		{
		}

		return $result;
	}

	/**
	 * sportsmanagementModelMatch::getInputStats()
	 *
	 * @param   mixed  $project_id
	 *
	 * @return
	 */
	public static function getInputStats($project_id)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		$query = Factory::getDbo()->getQuery(true);

		include_once JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'statistics' . DIRECTORY_SEPARATOR . 'base.php';

		$query->select('stat.id,stat.name,stat.short,stat.class,stat.icon,stat.calculated,ppos.position_id AS posid');
		$query->from('#__sportsmanagement_statistic AS stat ');
		$query->join('INNER', '#__sportsmanagement_position_statistic AS ps ON ps.statistic_id = stat.id ');
		$query->join('INNER', '#__sportsmanagement_project_position AS ppos ON ppos.position_id = ps.position_id ');
		$query->where('ppos.project_id = ' . $project_id);
		$query->order('stat.ordering, ps.ordering');

		Factory::getDbo()->setQuery($query);

		$res   = Factory::getDbo()->loadObjectList();
		$stats = array();

		foreach ($res as $k => $row)
		{
			$stat = SMStatistic::getInstance($row->class);
			$stat->bind($row);
			$stat->set('position_id', $row->posid);
			$stats[] = $stat;
		}

		return $stats;
	}

	/**
	 * sportsmanagementModelMatch::getMatchStatsInput()
	 *
	 * @param   mixed  $match_id
	 * @param   mixed  $projectteam1_id
	 * @param   mixed  $projectteam2_id
	 *
	 * @return
	 */
	public static function getMatchStatsInput($match_id, $projectteam1_id, $projectteam2_id)
	{
		$option    = Factory::getApplication()->input->getCmd('option');
		$app       = Factory::getApplication();
		$starttime = microtime();
		$query     = Factory::getDbo()->getQuery(true);
		$query->select('*');
		$query->from('#__sportsmanagement_match_statistic ');
		$query->where('match_id = ' . $match_id);

		Factory::getDbo()->setQuery($query);

		$res   = Factory::getDbo()->loadObjectList();
		$stats = array($projectteam1_id => array(),
		               $projectteam2_id => array());

		foreach ($res as $stat)
		{
			@$stats[$stat->projectteam_id][$stat->teamplayer_id][$stat->statistic_id] = $stat->value;
		}

		return $stats;
	}

	/**
	 * sportsmanagementModelMatch::getMatchStaffStatsInput()
	 *
	 * @param   mixed  $match_id
	 * @param   mixed  $projectteam1_id
	 * @param   mixed  $projectteam2_id
	 *
	 * @return
	 */
	public static function getMatchStaffStatsInput($match_id, $projectteam1_id, $projectteam2_id)
	{
		$option    = Factory::getApplication()->input->getCmd('option');
		$app       = Factory::getApplication();
		$starttime = microtime();

		$query = Factory::getDbo()->getQuery(true);

		$query->select('*');
		$query->from('#__sportsmanagement_match_staff_statistic ');
		$query->where('match_id = ' . $match_id);

		Factory::getDbo()->setQuery($query);

		$res   = Factory::getDbo()->loadObjectList();
		$stats = array($projectteam1_id => array(), $projectteam2_id => array());

		foreach ((array) $res as $stat)
		{
			@$stats[$stat->projectteam_id][$stat->team_staff_id][$stat->statistic_id] = $stat->value;
		}

		return $stats;
	}

	/**
	 * Method to return the project positions array (id,name)
	 *
	 * @access public
	 * @return array
	 * @since  1.5
	 */
	public static function getProjectPositionsOptions($id = 0, $person_type = 1, $project_id)
	{
		$starttime = microtime();
		$db        = Factory::getDbo();
		$result    = '';
		$query     = $db->getQuery(true);

		$query->select('ppos.id AS value,pos.name AS text,pos.id AS posid,pos.id AS pposid');
		$query->from('#__sportsmanagement_position AS pos ');
		$query->join('INNER', '#__sportsmanagement_project_position AS ppos ON ppos.position_id = pos.id ');

		$query->where('ppos.project_id = ' . $project_id);
		$query->where('pos.persontype = ' . $person_type);

		if ($id > 0)
		{
			$query->where('ppos.position_id = ' . $id);
		}

		$query->order('pos.ordering');

		$db->setQuery($query);

		try
		{
			$db->setQuery($query);
			$result = $db->loadObjectList('value');
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}

		return $result;

	}

	/**
	 * sportsmanagementModelMatch::getEventsOptions()
	 *
	 * @param   mixed  $project_id
	 * @param   mixed  $match_id
	 *
	 * @return
	 */
	public static function getEventsOptions($project_id = 0, $match_id = 0)
	{
		$app       = Factory::getApplication();
		$starttime = microtime();

		$query = Factory::getDbo()->getQuery(true);

		$query->select('et.id AS value,et.name AS text,et.icon AS icon');

		if ($match_id)
		{
			$query->from('#__sportsmanagement_match AS m ');
			$query->join('INNER', '#__sportsmanagement_project_position AS ppos ON ppos.project_id = ' . $project_id);
			$query->where('m.id = ' . $match_id);
		}
		else
		{
			$query->from('#__sportsmanagement_project_position AS ppos ');
			$query->where('ppos.project_id = ' . $project_id);
		}

		$query->join('INNER', '#__sportsmanagement_position_eventtype AS pet ON pet.position_id = ppos.position_id ');
		$query->join('INNER', '#__sportsmanagement_eventtype AS et ON et.id = pet.eventtype_id ');
		$query->where('et.published = 1');
		$query->order('et.id,pet.ordering, et.ordering');
		$query->group('et.id');

		Factory::getDbo()->setQuery($query);

		$result = Factory::getDbo()->loadObjectList();

		if (!$result)
		{
			$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_EVENTS_POS'), 'Error');
		}

		foreach ($result as $event)
		{
			$event->text = Text::_($event->text);
		}

		return $result;
	}

	/**
	 * get match commentary
	 *
	 * @return array
	 */
	public static function getMatchCommentary($match_id)
	{
		$option    = Factory::getApplication()->input->getCmd('option');
		$app       = Factory::getApplication();
		$starttime = microtime();

		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, $app->getUserState("com_sportsmanagement.cfg_which_database", false));
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('#__sportsmanagement_match_commentary');
		$query->where('match_id = ' . $match_id);
		$query->order('event_time DESC');

		try
		{
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (Exception $e)
		{
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . ' match id' . $match_id), Log::ERROR, 'jsmerror');
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');

			return false;
		}

	}

	/**
	 * sportsmanagementModelMatch::getRoundMatches()
	 *
	 * @param   mixed  $round_id
	 *
	 * @return
	 */
	function getRoundMatches($round_id)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('m.*');
		$this->jsmquery->from('#__sportsmanagement_match AS m');
		$this->jsmquery->where('m.round_id = ' . $round_id);
		$this->jsmquery->order('m.match_number');
		$this->jsmdb->setQuery($this->jsmquery);

		return ($this->jsmdb->loadObjectList());
	}

	/**
	 * sportsmanagementModelMatch::count_result()
	 *
	 * @param   integer  $count_result
	 *
	 * @return void
	 */
	function count_result($count_result = 0)
	{
		$pks    = $this->jsmjinput->getVar('cid', null, 'post', 'array');
		$post   = $this->jsmjinput->post->getArray(array());
		$result = true;

		for ($x = 0; $x < count($pks); $x++)
		{
			$object               = new stdClass;
			$object->id           = $pks[$x];
			$object->count_result = $count_result;

			try
			{
				$result_update = $this->jsmdb->updateObject('#__sportsmanagement_match', $object, 'id', true);
				sprintf(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_SAVED'), $pks[$x]);
				$this->jsmapp->enqueueMessage(sprintf(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_SAVED'), $pks[$x]), 'Notice');
			}
			catch (Exception $e)
			{
				$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . Text::_($e->getMessage()), 'Error');
				$result = false;
			}
		}
	}

	/**
	 * sportsmanagementModelMatch::getProjectRoundCodes()
	 *
	 * @param   mixed  $project_id
	 *
	 * @return
	 */
	function getProjectRoundCodes($project_id)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('id,roundcode,round_date_first');
		$this->jsmquery->from('#__sportsmanagement_round');
		$this->jsmquery->where('project_id = ' . $project_id);
		$this->jsmquery->order('roundcode,round_date_first ASC');
		$this->jsmdb->setQuery($this->jsmquery);

		return $this->jsmdb->loadObjectList();
	}

	/**
	 * sportsmanagementModelMatch::insertgooglecalendar()
	 *
	 * @return void
	 * https://packagist.org/packages/joomla/google
	 */
	function insertgooglecalendar()
	{
		$pks        = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
		$post       = Factory::getApplication()->input->post->getArray(array());
		$project_id = $post['project_id'];
		$match_ids  = implode(",", $pks);

		if (!$post['calendar_id'])
		{
			return false;
		}

		$this->jsmquery->clear();
		$this->jsmquery->select('*');
		$this->jsmquery->from('#__sportsmanagement_gcalendar');
		$this->jsmquery->where('id = ' . $post['calendar_id']);
		$this->jsmdb->setQuery($this->jsmquery);
		$calendar_result = $this->jsmdb->loadObjectList();

		$calendar             = new stdClass;
		$calendar->calendarId = new Registry(json_decode($calendar_result[0]->calendar_id));
		$calendar->params     = new Registry(json_decode($calendar_result[0]->params));

		$params = array();
		$client = new Google_Client(
			array(
				'ioFileCache_directory' => Factory::getConfig()->get('tmp_path')
			)
		);
		$client->setApplicationName("JSMCalendar");

		// $client->setApprovalPrompt('force');
		$client->setClientId($calendar->params->get('client-id'));
		$client->setClientSecret($calendar->params->get('client-secret'));
		$client->setScopes(
			array(
				'https://www.googleapis.com/auth/calendar'
			)
		);
		$client->setAccessType("offline");

		if (version_compare(JSM_JVERSION, '4', 'eq'))
		{
			$uri = Uri::getInstance();
		}
		else
		{
			$uri = Factory::getURI();
		}

		if (filter_var($uri->getHost(), FILTER_VALIDATE_IP))
		{
			$uri->setHost('localhost');
		}

		$client->setRedirectUri(
			$uri->toString(
				array(
					'scheme',
					'host',
					'port',
					'path'
				)
			) . '?option=' . $option . '&task=jsmgcalendarimport.import'
		);
		$client->setApprovalPrompt('force');
		$client->refreshToken($calendar->params->get('refreshToken'));

		$cal = new Google_Service_Calendar($client);

		$obj          = $cal->events->listEvents($calendar->params->get('calendarId'), $params);
		$googleEvents = $obj->items;

		$this->jsmquery->clear();
		$this->jsmquery->select('p.timezone,p.name,p.gcalendar_id,p.game_regular_time,p.halftime,p.gcalendar_use_fav_teams,p.fav_team,gc.username,gc.password,gc.calendar_id');
		$this->jsmquery->from('#__sportsmanagement_project as p');
		$this->jsmquery->join('INNER', '#__sportsmanagement_gcalendar AS gc ON gc.id = p.gcalendar_id');
		$this->jsmquery->where('p.id = ' . $project_id);
		$this->jsmdb->setQuery($this->jsmquery);
		$gcalendar_id = $this->jsmdb->loadObject();
		$this->jsmquery->clear();
		$this->jsmquery->select('m.id,m.match_date,m.team1_result,m.team2_result,m.gcal_event_id,DATE_FORMAT(m.time_present,"%H:%i") time_present,m.match_timestamp');
		$this->jsmquery->select('m.cancel, m.cancel_reason');
		$this->jsmquery->select('playground.name AS playground_name,playground.zipcode AS playground_zipcode,playground.city AS playground_city,playground.address AS playground_address');
		$this->jsmquery->select('pt1.project_id');
		$this->jsmquery->select('t1.name as hometeam,t2.name as awayteam');
		$this->jsmquery->select('r.name as roundname');
		$this->jsmquery->select('d1.name as divhome');
		$this->jsmquery->select('d2.name as divaway');
		$this->jsmquery->select('CASE WHEN CHAR_LENGTH(t1.alias) AND CHAR_LENGTH(t2.alias) THEN CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) ELSE m.id END AS slug ');
		$this->jsmquery->from('#__sportsmanagement_match AS m');
		$this->jsmquery->join('INNER', '#__sportsmanagement_round AS r ON m.round_id = r.id ');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_division AS d1 ON pt1.division_id = d1.id');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_division AS d2 ON pt2.division_id = d2.id');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_playground AS playground ON playground.id = m.playground_id');
		$this->jsmquery->where('m.published = 1');
		$this->jsmquery->where('m.id IN (' . $match_ids . ' )');
		$this->jsmquery->where('r.project_id = ' . (int) $project_id);

		if ($gcalendar_id->gcalendar_use_fav_teams)
		{
			$this->jsmquery->where('( t1.id IN (' . $gcalendar_id->fav_team . ' ) OR t2.id IN (' . $gcalendar_id->fav_team . ' )  )');
		}

		$this->jsmquery->group('m.id ');

		$this->jsmquery->order('m.match_date ASC,m.match_number');
		$this->jsmdb->setQuery($this->jsmquery);
		$result = $this->jsmdb->loadObjectList();

		foreach ($result as $row)
		{
			$event  = new Google_Service_Calendar_Event;
			$detail = "";
			if ($row->cancel == 1)
			{
				$detail = ' (' . $row->cancel_reason . ')';
			}
			else if (isset($row->team1_result))
			{
				$detail = ' (' . $row->team1_result . ':' . $row->team2_result . ')';
			}
			$event->setSummary($row->hometeam . ' - ' . $row->awayteam . $detail);
			$event->setDescription($row->roundname);
			if (!empty($row->playground_name))
			{
				$event->setLocation($row->playground_name . ',' . $row->playground_city . ',' . $row->playground_address);
			}
			$start = new Google_Service_Calendar_EventDateTime;

			$dtTmp = new DateTime($row->match_date, new DateTimeZone($gcalendar_id->timezone));
			$start->setDateTime($dtTmp->format("c"));
			$start->setTimeZone($gcalendar_id->timezone);
			$event->setStart($start);

			$dtTmp->add(new DateInterval('PT' . ($gcalendar_id->game_regular_time + $gcalendar_id->halftime) . 'M'));
			$end = new Google_Service_Calendar_EventDateTime;
			$end->setDateTime($dtTmp->format("c"));
			$end->setTimeZone($gcalendar_id->timezone);
			$event->setEnd($end);

			if ($row->gcal_event_id)
			{
				$event = $cal->events->update($calendar->params->get('calendarId'), $row->gcal_event_id, $event);
			}
			else
			{
				$event                 = $cal->events->insert($calendar->params->get('calendarId'), $event);
				$id                    = $event->getId();
				$object                = new stdClass;
				$object->id            = $row->id;
				$object->gcal_event_id = $id;
				$result_update         = Factory::getDbo()->updateObject('#__sportsmanagement_match', $object, 'id', true);
			}
		}

		return true;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return mixed    A JForm object on success, false on failure
	 * @since  1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_sportsmanagement.match', 'match', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string    Script files
	 */
	public function getScript()
	{
		return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
	}

	/**
	 * Method to save item order
	 *
	 * @access public
	 * @return boolean    True on success
	 * @since  1.5
	 */
	function saveorder($pks = null, $order = null)
	{
		$row =& $this->getTable();

		for ($i = 0; $i < count($pks); $i++)
		{
			$row->load((int) $pks[$i]);

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];

				if (!$row->store())
				{
					sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, Factory::getDbo()->getErrorMsg(), __LINE__);

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    The table type to instantiate
	 * @param   string    A prefix for the table class name. Optional.
	 * @param   array    Configuration array for model. Optional.
	 *
	 * @return JTable    A database object
	 * @since  1.6
	 */
	public function getTable($type = 'match', $prefix = 'sportsmanagementTable', $config = array())
	{
		$config['dbo'] = sportsmanagementHelper::getDBConnection();

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * sportsmanagementModelMatch::savestats()
	 *
	 * @param   mixed  $data
	 *
	 * @return
	 */
	function savestats($data)
	{
		$app      = Factory::getApplication();
		$db       = sportsmanagementHelper::getDBConnection();
		$query    = $db->getQuery(true);
		$match_id = $data['match_id'];

		if (isset($data['cid']))
		{
			foreach ($data['teamplayer_id'] as $idx => $tpid)
			{
				$teamplayer_id  = $data['teamplayer_id'][$idx];
				$projectteam_id = $data['projectteam_id'][$idx];
				$query->clear();
				$conditions = array(
					$db->quoteName('match_id') . ' = ' . $match_id,
					$db->quoteName('teamplayer_id') . ' = ' . $teamplayer_id
				);
				$query->delete($db->quoteName('#__sportsmanagement_match_statistic'));
				$query->where($conditions);

				try
				{
					$db->setQuery($query);
					$res = $db->execute();

					foreach ($data as $key => $value)
					{
						if (preg_match('/^stat' . $teamplayer_id . '_([0-9]+)/', $key, $reg) && $value != "")
						{
							$statistic_id         = $reg[1];
							$stat                 = Table::getInstance('Matchstatistic', 'sportsmanagementTable');
							$stat->match_id       = $match_id;
							$stat->projectteam_id = $projectteam_id;
							$stat->teamplayer_id  = $teamplayer_id;
							$stat->statistic_id   = $statistic_id;
							$stat->value          = ($value == "") ? null : $value;

							if (!$stat->check())
							{
								echo "stat check failed!";
								die();
							}

							if (!$stat->store())
							{
								echo "stat store failed!";
								die();
							}
						}
					}
				}
				catch (Exception $e)
				{
					$result = false;
				}
			}
		}

		// Staff stats
		if (isset($data['staffcid']))
		{
			foreach ($data['team_staff_id'] as $idx => $stid)
			{
				$team_staff_id  = $data['team_staff_id'][$idx];
				$projectteam_id = $data['sprojectteam_id'][$idx];
				$query->clear();
				$conditions = array(
					$db->quoteName('match_id') . ' = ' . $match_id,
					$db->quoteName('team_staff_id') . ' = ' . $team_staff_id
				);
				$query->delete($db->quoteName('#__sportsmanagement_match_staff_statistic'));
				$query->where($conditions);

				try
				{
					$db->setQuery($query);
					$res = $db->execute();

					foreach ($data as $key => $value)
					{
						if (ereg('^staffstat' . $team_staff_id . '_([0-9]+)', $key, $reg) && $value != "")
						{
							$statistic_id         = $reg[1];
							$stat                 = Table::getInstance('Matchstaffstatistic', 'sportsmanagementTable');
							$stat->match_id       = $match_id;
							$stat->projectteam_id = $projectteam_id;
							$stat->team_staff_id  = $team_staff_id;
							$stat->statistic_id   = $statistic_id;
							$stat->value          = ($value == "") ? null : $value;

							if (!$stat->check())
							{
								echo "stat check failed!";
								die();
							}

							if (!$stat->store())
							{
								echo "stat store failed!";
								die();
							}
						}
					}
				}
				catch (Exception $e)
				{
					$result = false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to update checked project match
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function saveshort()
	{
		$pks  = $this->jsmapp->input->getVar('cid', null, 'post', 'array');
		$post = $this->jsmapp->input->post->getArray(array());

		$result = true;
        $projectteam1_id = 0;
        $projectteam2_id = 0;
        $match_id = 0;

		for ($x = 0; $x < count($pks); $x++)
		{
			/** änderungen im datum oder der uhrzeit */
			$tblMatch = $this->getTable();;
			$tblMatch->load((int) $pks[$x]);

			$object               = new stdClass;
			$object->id           = $pks[$x];
			$object->team1_result = null;
			$object->team2_result = null;

			list($date, $time) = explode(" ", $tblMatch->match_date);
			$this->_match_time_new = $post['match_time' . $pks[$x]] . ':00';
			$this->_match_date_new = $post['match_date' . $pks[$x]];
			$this->_match_time_old = $time;
			$this->_match_date_old = sportsmanagementHelper::convertDate($date);

			$post['match_date' . $pks[$x]] = sportsmanagementHelper::convertDate($post['match_date' . $pks[$x]], 0);
			$post['match_date' . $pks[$x]] = $post['match_date' . $pks[$x]] . ' ' . $post['match_time' . $pks[$x]] . ':00';

			if ($post['match_date' . $pks[$x]] != $tblMatch->match_date)
			{
				$this->jsmquery->clear();
				$this->jsmquery->select('t1.id as hometeam,t2.id as awayteam');
				$this->jsmquery->select('p.fav_team, p.fav_team_send_mail');
				$this->jsmquery->select('CASE WHEN CHAR_LENGTH(t1.alias) AND CHAR_LENGTH(t2.alias) THEN CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) ELSE m.id END AS slug ');
				$this->jsmquery->from('#__sportsmanagement_match AS m');
				$this->jsmquery->join('INNER', '#__sportsmanagement_round AS r ON m.round_id = r.id ');
				$this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id');
				$this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id');
				$this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
				$this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
				$this->jsmquery->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
				$this->jsmquery->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
				$this->jsmquery->join('LEFT', '#__sportsmanagement_project AS p ON p.id = pt1.project_id');
				$this->jsmquery->where('m.id = ' . $object->id);
				$this->jsmdb->setQuery($this->jsmquery);
				$result = $this->jsmdb->loadObject();

				if ($result->fav_team_send_mail == 1 || ($result->fav_team_send_mail == 2 && ($result->fav_team == $result->hometeam || $result->fav_team == $result->awayteam)))
				{
					$this->jsmapp->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_ADMIN_CHANGE'), 'Notice');
					self::sendEmailtoPlayers();
				}
			}

			$object->match_number = $post['match_number' . $pks[$x]];
			$object->match_date   = $post['match_date' . $pks[$x]];

			if (isset($post['result_type' . $pks[$x]]))
			{
				$object->result_type = $post['result_type' . $pks[$x]];
			}
			else
			{
				$object->result_type = 0;
			}

			if (isset($post['match_result_type' . $pks[$x]]))
			{
				$object->match_result_type = $post['match_result_type' . $pks[$x]];
			}
			else
			{
				$object->match_result_type = 0;
			}

			$object->crowd    = $post['crowd' . $pks[$x]];
			$object->round_id = $post['round_id' . $pks[$x]];

			if (isset($post['division_id' . $pks[$x]]))
			{
				$object->division_id = $post['division_id' . $pks[$x]];
			}
			else
			{
				$object->division_id = 0;
			}

			$object->projectteam1_id         = $post['projectteam1_id' . $pks[$x]];
			$object->projectteam2_id         = $post['projectteam2_id' . $pks[$x]];

			$object->team1_single_matchpoint = $post['team1_single_matchpoint' . $pks[$x]];
			$object->team2_single_matchpoint = $post['team2_single_matchpoint' . $pks[$x]];

			$object->team1_single_sets       = $post['team1_single_sets' . $pks[$x]];
			$object->team2_single_sets       = $post['team2_single_sets' . $pks[$x]];
			$object->team1_single_games      = $post['team1_single_games' . $pks[$x]];
			$object->team2_single_games      = $post['team2_single_games' . $pks[$x]];
			$object->content_id              = $post['content_id' . $pks[$x]];
			$object->match_timestamp         = sportsmanagementHelper::getTimestamp($object->match_date);

			$object->playground_id           = $post['playground_id' . $pks[$x]];

            if ( !$object->team1_single_matchpoint )
            {
                $object->team1_single_matchpoint = NULL;
            }
            if ( !$object->team2_single_matchpoint )
            {
                $object->team2_single_matchpoint = NULL;
            }

            if ( !$object->team1_single_sets )
            {
                $object->team1_single_sets = NULL;
            }
            if ( !$object->team2_single_sets )
            {
                $object->team2_single_sets = NULL;
            }

            if ( !$object->team1_single_games )
            {
                $object->team1_single_games = NULL;
            }
            if ( !$object->team2_single_games )
            {
                $object->team2_single_games = NULL;
            }

			/** handelt es sich um eine turnierrunde ? */
			$this->jsmquery->clear();
			$this->jsmquery->select('tournement');
			$this->jsmquery->from('#__sportsmanagement_round');
			$this->jsmquery->where('id = ' . $object->round_id);
			$this->jsmdb->setQuery($this->jsmquery);
			$tournement_round = $this->jsmdb->loadResult();

			if ($tournement_round)
			{
				/** roundcode für die nächste runde */
				$this->jsmquery->clear();
				$this->jsmquery->select('roundcode,project_id');
				$this->jsmquery->from('#__sportsmanagement_round');
				$this->jsmquery->where('id = ' . $object->round_id);
				$this->jsmdb->setQuery($this->jsmquery);

				try
				{
					$round_object = $this->jsmdb->loadObject();
					$round_code   = $round_object->roundcode;
				}
				catch (RuntimeException $e)
				{
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>' . print_r($this->jsmquery->dump(), true) . '</pre>', 'Error');
				}

				$round_code--;
				$this->jsmquery->clear();
				$this->jsmquery->select('id');
				$this->jsmquery->from('#__sportsmanagement_round');
				$this->jsmquery->where('roundcode <= ' . $round_code);
				$this->jsmquery->where('tournement = 1');
				$this->jsmquery->where('project_id = ' . $round_object->project_id);
				$this->jsmdb->setQuery($this->jsmquery);

				try
				{
					$prev_round_id = $this->jsmdb->loadResult();
				}
				catch (RuntimeException $e)
				{
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>' . print_r($this->jsmquery->dump(), true) . '</pre>', 'Error');
				}

				/** update */
				$this->jsmquery->clear();
				$this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_match'))
					->set('next_match_id = ' . $object->id)
					->where('round_id = ' . $prev_round_id . ' AND ( projectteam1_id = ' . $object->projectteam1_id . ' OR projectteam2_id = ' . $object->projectteam1_id . ')');
				$this->jsmdb->setQuery($this->jsmquery);

				try
				{
					$this->jsmdb->execute();
				}
				catch (RuntimeException $e)
				{
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
				}

				$this->jsmquery->clear();
				$this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_match'))
					->set('next_match_id = ' . $object->id)
					->where('round_id = ' . $prev_round_id . ' AND ( projectteam1_id = ' . $object->projectteam2_id . ' OR projectteam2_id = ' . $object->projectteam2_id . ')');
				$this->jsmdb->setQuery($this->jsmquery);

				try
				{
					$this->jsmdb->execute();
				}
				catch (RuntimeException $e)
				{
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
				}
			}

			if ($post['use_legs'])
			{
				foreach ($post['team1_result_split' . $pks[$x]] as $key => $value)
				{
					if (is_numeric($post['team1_result_split' . $pks[$x]][$key]))
					{
						if ($post['team1_result_split' . $pks[$x]][$key] > $post['team2_result_split' . $pks[$x]][$key])
						{
							$object->team1_result += 1;
							$object->team2_result += 0;
						}

						if ($post['team1_result_split' . $pks[$x]][$key] < $post['team2_result_split' . $pks[$x]][$key])
						{
							$object->team1_result += 0;
							$object->team2_result += 1;
						}

						if ($post['team1_result_split' . $pks[$x]][$key] == $post['team2_result_split' . $pks[$x]][$key])
						{
							$object->team1_result += 1;
							$object->team2_result += 1;
						}
					}
					else
					{
						if (is_numeric($post['team1_result' . $pks[$x]]) && is_numeric($post['team2_result' . $pks[$x]]))
						{
							$object->team1_result = $post['team1_result' . $pks[$x]];
							$object->team2_result = $post['team2_result' . $pks[$x]];
						}
					}
				}
			}
			else
			{
				if (is_numeric($post['team1_result' . $pks[$x]]) && is_numeric($post['team2_result' . $pks[$x]]))
				{
					$object->team1_result = $post['team1_result' . $pks[$x]];
					$object->team2_result = $post['team2_result' . $pks[$x]];
					/**
					 * wer ist der sieger des spiels in der turnierrunde
					 * nach regulärer spielzeit
					 */
					if ($tournement_round)
					{
						if ($object->team1_result > $object->team2_result)
						{
							$object->team_won  = $object->projectteam1_id;
							$object->team_lost = $object->projectteam2_id;
						}

						if ($object->team1_result < $object->team2_result)
						{
							$object->team_won  = $object->projectteam2_id;
							$object->team_lost = $object->projectteam1_id;
						}
					}
				}

				if (($post['team1_result_ot' . $pks[$x]]) && ($post['team2_result_ot' . $pks[$x]]))
				{
					$object->team1_result_ot = $post['team1_result_ot' . $pks[$x]];
					$object->team2_result_ot = $post['team2_result_ot' . $pks[$x]];
					/**
					 * wer ist der sieger des spiels in der turnierrunde
					 * nach verlängerung
					 */
					if ($tournement_round)
					{
						if ($object->team1_result_ot > $object->team2_result_ot)
						{
							$object->team_won  = $object->projectteam1_id;
							$object->team_lost = $object->projectteam2_id;
						}

						if ($object->team1_result_ot < $object->team2_result_ot)
						{
							$object->team_won  = $object->projectteam2_id;
							$object->team_lost = $object->projectteam1_id;
						}
					}
				}

				if (($post['team1_result_so' . $pks[$x]]) && ($post['team2_result_so' . $pks[$x]]))
				{
					$object->team1_result_so = $post['team1_result_so' . $pks[$x]];
					$object->team2_result_so = $post['team2_result_so' . $pks[$x]];
					/**
					 * wer ist der sieger des spiels in der turnierrunde
					 * nach elfmeterschiessen
					 */
					if ($tournement_round)
					{
						if ($object->team1_result_so > $object->team2_result_so)
						{
							$object->team_won  = $object->projectteam1_id;
							$object->team_lost = $object->projectteam2_id;
						}

						if ($object->team1_result_so < $object->team2_result_so)
						{
							$object->team_won  = $object->projectteam2_id;
							$object->team_lost = $object->projectteam1_id;
						}
					}
				}
			}

			$object->team1_result_split = implode(";", $post['team1_result_split' . $pks[$x]]);
			$object->team2_result_split = implode(";", $post['team2_result_split' . $pks[$x]]);

			try
			{
				$result_update = Factory::getDbo()->updateObject('#__sportsmanagement_match', $object, 'id', true);
			}
			catch (Exception $e)
			{
        $this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
        $this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
        //$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>' . print_r($object, true) . '</pre>', 'Error');

				$result = false;
			}

			$mdlProject = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
			$project    = $mdlProject->getProject($post['project_id']);
			if($project->teams_as_referees == 1)
			{
				$postReferee = array();
				$modelMatches = BaseDatabaseModel::getInstance("Match", "sportsmanagementModel");
				$postReferee['id'] = $object->id;
				
				$positions              = $modelMatches->getProjectPositionsOptions(0, 3, $post['project_id']);
				$position = array();
				
				if ($post['referee_id' . $pks[$x]] != null && $post['referee_id' . $pks[$x]] > 0)
				{
					$position['0'] = $post['referee_id' . $pks[$x]];
					foreach ($positions AS $key => $pos)
					{
						$postReferee['position' . $key] = $position;
					}
				}
				
				$postReferee['positions'] = $positions;
				$postReferee['project_id'] = $post['project_id'];
				$this->jsmquery->clear();
				$this->updateReferees($postReferee);
			}
		}

switch ($post['sports_type_name'] )
{
case 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION':
$match_single_free = array();
$match_single_player = array();

$mdlProject = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
$mdlteamplayers = BaseDatabaseModel::getInstance("teamplayers", "sportsmanagementModel");
$mdljlextindividualsportes = BaseDatabaseModel::getInstance("jlextindividualsportes", "sportsmanagementModel");

$project = $mdlProject->getProject($post['project_id']);
$rid = $post['rid'];
for ($x = 0; $x < count($pks); $x++)
{
$projectteam1_id = $post['projectteam1_id' . $pks[$x]];
$projectteam2_id = $post['projectteam2_id' . $pks[$x]];
$match_id = $pks[$x];

$mdljlextindividualsportes->checkGames($project, $match_id, $rid, $projectteam1_id, $projectteam2_id);




if ( $projectteam1_id )
{
$teamplayer1 = $mdlteamplayers->getProjectTeamplayers(0, $project->season_id,$projectteam1_id);
//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>' . print_r($teamplayer1, true) . '</pre>', 'Error');
}
/** schützen dem spiel zuordnen */
foreach ($teamplayer1 as $player)
{
$this->jsmquery->clear();
$this->jsmquery->select('id');
$this->jsmquery->from('#__sportsmanagement_match_player');
$this->jsmquery->where('match_id = ' . $match_id);
$this->jsmquery->where('teamplayer_id = '.$player->season_team_person_id);
$this->jsmdb->setQuery($this->jsmquery);
try
{
$match_player_id = $this->jsmdb->loadResult();
}
catch (RuntimeException $e)
{
//$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
//$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>' . print_r($this->jsmquery->dump(), true) . '</pre>', 'Error');

}

if ( !$match_player_id )
{
$profile = new stdClass;
$profile->match_id = $match_id;
$profile->teamplayer_id = $player->season_team_person_id;
$profile->project_position_id = $player->position_id;
try
{
$result = $this->jsmdb->insertObject('#__sportsmanagement_match_player', $profile);
}
catch (RuntimeException $e)
{
//$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
//$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>' . print_r($this->jsmquery->dump(), true) . '</pre>', 'Error');
}
}



}

/** einzelschützen anlegen */
unset($match_single_free);
unset($match_single_player);

$this->jsmquery->clear();
$this->jsmquery->select('*');
$this->jsmquery->from('#__sportsmanagement_match_single');
$this->jsmquery->where('match_id = ' . $match_id);
$this->jsmquery->where('projectteam1_id = '.$projectteam1_id);
$this->jsmquery->where('round_id = '.$rid);
$this->jsmdb->setQuery($this->jsmquery);
$result_single1 = $this->jsmdb->loadObjectList();
//$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' result_single <pre>' . print_r($result_single1, true) . '</pre>', 'notice');

foreach ($result_single1 as $single1)
{
$match_single_player[$single1->teamplayer1_id] = $single1->teamplayer1_id;
if ( !$single1->teamplayer1_id )
{
$match_single_free[$single1->id] = $single1->id;
}

}

foreach ($teamplayer1 as $player)
{

if ( !array_key_exists( $player->season_team_person_id, $match_single_player ) );
{
$match_single_id = array_pop($match_single_free);
$object = new stdClass;
$object->id = $match_single_id;
$object->teamplayer1_id = $player->season_team_person_id;
$result_update = $this->jsmdb->updateObject('#__sportsmanagement_match_single', $object, 'id', true);


}

}




}
break;
}





		return $result;

	}

	/**
	 * sportsmanagementModelMatch::sendEmailtoPlayers()
	 *
	 * @return void
	 */
	function sendEmailtoPlayers()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$mailer = Factory::getMailer();
		$user   = Factory::getUser();

		// Get settings from com_issuetracker parameters
		$params           = ComponentHelper::getParams($option);
		$this->project_id = $app->getUserState("$option.pid", '0');
		$mdl              = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
		$project          = $mdl->getProject($this->project_id);

		if ($project->fav_team)
		{
			$mdl        = BaseDatabaseModel::getInstance("teamplayers", "sportsmanagementModel");
			$teamplayer = $mdl->getProjectTeamplayers($project->fav_team, $project->season_id);
		}

		foreach ($teamplayer as $player)
		{
			if (trim($player->email))
			{
				// Add the sender Information.
				$sender = array(
					$user->email,
					$user->name
				);
				$mailer->setSender($sender);

				// Add the recipient. $recipient = $user_email;
				$mailer->addRecipient($player->email);

				// Add the subject
				$mailer->setSubject($params->get('match_mail_header', ''));

				// Add body
				// $fcontent = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_ADMIN_MAIL',$this->_match_date_old,$this->_match_time_old,$this->_match_date_new,$this->_match_time_new,$user->name);
				$fcontent = $params->get('match_mail_text', '');

				$fcontent = str_replace('[FROMDATE]', $this->_match_date_old, $fcontent);
				$fcontent = str_replace('[FROMTIME]', $this->_match_time_old, $fcontent);
				$fcontent = str_replace('[TODATE]', $this->_match_date_new, $fcontent);
				$fcontent = str_replace('[TOTIME]', $this->_match_time_new, $fcontent);
				$fcontent = str_replace('[MAILFROM]', $user->name, $fcontent);

				$mailer->setBody($fcontent);
				$mailer->isHTML(true);
				$send =& $mailer->Send();

				if ($send !== true)
				{
					// Echo 'Error sending email: ' . $send->message;
					$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_ADMIN_MAIL_SEND_ERROR', $send->message), 'Error');
				}
				else
				{
					$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_ADMIN_MAIL_SEND_SUCCESS', $player->firstname, $player->lastname), 'Notice');
				}
			}
		}

	}

	/**
	 * Method to remove a matchday
	 *
	 * @access public
	 * @return boolean    True on success
	 * @since  0.1
	 */
	function delete(&$pks)
	{
		$app   = Factory::getApplication();
		$db    = Factory::getDbo();
		$query = Factory::getDbo()->getQuery(true);

		$result = false;

		if (count($pks))
		{
			$cids = implode(',', $pks);

			// Wir löschen mit join
			$query = 'DELETE ms,mss,mst,mev,mre,mpl
            FROM #__sportsmanagement_match as m  
            LEFT JOIN #__sportsmanagement_match_statistic as ms
            ON ms.match_id = m.id
            LEFT JOIN #__sportsmanagement_match_staff_statistic as mss
            ON mss.match_id = m.id
            LEFT JOIN #__sportsmanagement_match_staff as mst
            ON mst.match_id = m.id
            LEFT JOIN #__sportsmanagement_match_event as mev
            ON mev.match_id = m.id
            LEFT JOIN #__sportsmanagement_match_referee as mre
            ON mre.match_id = m.id
            LEFT JOIN #__sportsmanagement_match_player as mpl
            ON mpl.match_id = m.id
            WHERE m.id IN (' . $cids . ')';
			Factory::getDbo()->setQuery($query);
			Factory::getDbo()->execute();

			if (!Factory::getDbo()->execute())
			{
			}

			return parent::delete($pks);
		}

		return true;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array    The form data.
	 *
	 * @return boolean    True on success.
	 * @since  1.6
	 */
	public function save($data)
	{
		$date       = Factory::getDate();
		$user       = Factory::getUser();
		$post       = Factory::getApplication()->input->post->getArray(array());
		$parentsave = true;

		$data['modified']    = $date->toSql();
		$data['modified_by'] = $user->get('id');
		$data['id']          = $post['id'];

		if (isset($post['extended']) && is_array($post['extended']))
		{
			$parameter = new Registry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string) $parameter;
		}

		$data['team1_bonus']         = $post['team1_bonus'] == "" ? null : $post['team1_bonus'];
		$data['team2_bonus']         = $post['team2_bonus'] == "" ? null : $post['team2_bonus'];
		$data['team1_legs']          = $post['team1_legs'];
		$data['team2_legs']          = $post['team2_legs'];
		$data['match_result_detail'] = $post['match_result_detail'];

		$data['playground_id'] = $post['playground_id'];
		// $data['alt_decision'] = $post['alt_decision'];
		$data['team1_result_decision'] = $post['team1_result_decision'];
		$data['team2_result_decision'] = $post['team2_result_decision'];
		$data['decision_info']         = $post['decision_info'];
		$data['team_won']              = $post['team_won'];

		if ($data['id'] && !empty($data['match_date']))
		{
			$data['match_timestamp'] = sportsmanagementHelper::getTimestamp($data['match_date']);
		}

		/**
		 *
		 * zuerst sichern, damit wir bei einer neuanlage die id haben
		 */
		try
		{
			$parentsave = parent::save($data);
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');
		}

		if ($parentsave)
		{
			$id         = (int) $this->getState($this->getName() . '.id');
			$isNew      = $this->getState($this->getName() . '.new');
			$data['id'] = $id;

			if ($isNew)
			{
				// Here you can do other tasks with your newly saved record...
				$this->jsmapp->enqueueMessage(Text::plural(strtoupper($this->jsmoption) . '_N_ITEMS_CREATED', $id), '');
			}

			return true;
		}
		else
		{
			return false;
		}

	}

	/**
	 * Method to update starting lineup list
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function updateReferees($post)
	{
		$app    = Factory::getApplication();
		$config = Factory::getConfig();

		$sender = array(
			$config->get('mailfrom'),
			$config->get('fromname')
		);

		$mid       = $post['id'];
		$peid      = array();
		$result    = true;
		$positions = $post['positions'];

		$paramsmail = ComponentHelper::getParams($this->jsmoption)->get('ishd_referee_insert_match_mail');

		foreach ($positions AS $key => $pos)
		{
			if (isset($post['position' . $key]))
			{
				$peid = array_merge((array) $post['position' . $key], $peid);
			}
		}

		if (!$peid)
		{
			/**
			 *
			 * Delete all referees assigned to this match
			 */
			$this->jsmquery->delete($this->jsmdb->quoteName('#__sportsmanagement_match_referee'));
			$this->jsmquery->where('match_id = ' . $mid);
		}
		else
		{
			/**
			 * erst die schiedsrichter selektieren die gelöscht werden
			 * damit wir eine email senden können, dass sie vom spiel
			 * ausgetragen werden.
			 */
			$peids = implode(',', $peid);
			$this->jsmquery->clear();
			$this->jsmquery->select('id');
			$this->jsmquery->from('#__sportsmanagement_match_referee');
			$this->jsmquery->where('match_id = ' . $mid);
			$this->jsmquery->where('project_referee_id NOT IN (' . $peids . ')');
			$this->jsmdb->setQuery($this->jsmquery);
			$result_referee_delete = $this->jsmdb->loadObjectList();

			/**
			 *
			 * Delete all referees which are not selected anymore from this match
			 */
			ArrayHelper::toInteger($peid);
			$peids = implode(',', $peid);
			$this->jsmquery->clear();
			$this->jsmquery->delete($this->jsmdb->quoteName('#__sportsmanagement_match_referee'));
			$this->jsmquery->where('match_id = ' . $mid);
			$this->jsmquery->where('project_referee_id NOT IN (' . $peids . ')');
		}

		$this->jsmdb->setQuery($this->jsmquery);

		if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
		{
			$this->setError($this->jsmdb->getErrorMsg());
			$result = false;
		}

		foreach ($positions AS $key => $pos)
		{
			if (isset($post['position' . $key]))
			{
				for ($x = 0; $x < count($post['position' . $key]); $x++)
				{
					$project_referee_id = $post['position' . $key][$x];
					$this->jsmquery->clear();
					$this->jsmquery->select('id');
					$this->jsmquery->from('#__sportsmanagement_match_referee');
					$this->jsmquery->where('match_id = ' . $mid);
					$this->jsmquery->where('project_referee_id = ' . $project_referee_id);

					$this->jsmdb->setQuery($this->jsmquery);
					$result_referee = $this->jsmdb->loadResult();

					if ($result_referee)
					{
						$object                      = new stdClass;
						$object->id                  = $result;
						$object->project_position_id = $key;
						$object->ordering            = $x;

						try
						{
							$result = $this->jsmdb->updateObject('#__sportsmanagement_match_referee', $object, 'id');
						}
						catch (Exception $e)
						{
							sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->jsmdb->getErrorMsg(), __LINE__);
						}
					}
					else
					{
						$profile                      = new stdClass;
						$profile->match_id            = $mid;
						$profile->project_referee_id  = $project_referee_id;
						$profile->project_position_id = $key;
						$profile->ordering            = $x;

						try
						{
							$result = $this->jsmdb->insertObject('#__sportsmanagement_match_referee', $profile);

							if ($result)
							{
								$this->jsmquery->clear();
								$match_teams    = self::getMatchTeams($mid);
								$match_detail   = self::getMatchData($mid);
								$refreee_detail = self::getRefereeRoster($key, $mid, $project_referee_id);
								$mailer         = Factory::getMailer();
								$mailer->setSender($sender);
								$recipient = $refreee_detail[$project_referee_id]->email;
								$mailer->addRecipient($recipient);

								$body = sprintf(
									$paramsmail,
									$refreee_detail[$project_referee_id]->firstname,
									$refreee_detail[$project_referee_id]->lastname,
									'Schiedsrichterverein',
									'Schiedsrichterstufe',
									date("d.m.Y - H:i", $match_detail->match_timestamp),
									$match_detail->playground_name,
									'Ligakurzname',
									$match_teams->team1,
									$match_teams->team2
								);

								$mailer->setSubject('Neueinteilung Schiedsrichtereinsatz am : ' . date("d.m.Y - H:i", $match_detail->match_timestamp));
								$mailer->isHTML(true);
								$mailer->Encoding = 'base64';
								$mailer->setBody($body);
								$send = $mailer->Send();

								if ($send !== true)
								{
								}
								else
								{
								}
							}
						}
						catch (Exception $e)
						{
						}
					}
				}
			}
		}

		return true;
	}


	/**
	 * sportsmanagementModelMatch::getMatchTeams()
	 *
	 * @param integer $match_id
	 * @param integer $projectteam1_id
	 * @param integer $projectteam2_id
	 * @param string $sports_type_name
	 * @return
	 */
	public static function getMatchTeams($match_id=0,$projectteam1_id=0,$projectteam2_id=0,$sports_type_name='')
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$db     = sportsmanagementHelper::getDBConnection();
		$query  = $db->getQuery(true);

        $query->select('mc.*');
        $query->select('u.name AS editor');
        $query->from('#__sportsmanagement_match AS mc ');
        $query->join('LEFT', '#__users u ON u.id = mc.checked_out');
		$query->where('mc.id = ' . $match_id);
        switch ($sports_type_name)
		{
		case 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION':
        if ( $projectteam1_id && $projectteam2_id )
        {
        $query->select('t1.name as team1,t2.name as team2');
        $query->join('INNER', '#__sportsmanagement_project_team AS pt1 ON mc.projectteam1_id = pt1.id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt2 ON mc.projectteam2_id = pt2.id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id ');
        }
        elseif ( $projectteam1_id && !$projectteam2_id )
        {
        $query->select('t1.name as team1');
        $query->join('INNER', '#__sportsmanagement_project_team AS pt1 ON mc.projectteam1_id = pt1.id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id ');
        }
        elseif ( !$projectteam1_id && $projectteam2_id )
        {
        $query->select('t2.name as team2');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt2 ON mc.projectteam2_id = pt2.id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id ');
        }







        break;
        default:
		//$query->select('mc.*');
		$query->select('t1.name as team1,t2.name as team2');
		//$query->select('u.name AS editor');
//		$query->from('#__sportsmanagement_match AS mc ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt1 ON mc.projectteam1_id = pt1.id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt2 ON mc.projectteam2_id = pt2.id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id ');
//		$query->join('LEFT', '#__users u ON u.id = mc.checked_out');
//		$query->where('mc.id = ' . $match_id);
        break;
        }

		try
		{
			$db->setQuery($query);
			$result = $db->loadObject();

            if ( $result )
            {
            if ( !property_exists($result, "projectteam1_id") )
            {
                $result->projectteam1_id = 0;
            }
            if ( !property_exists($result, "projectteam2_id") )
            {
                $result->projectteam2_id = 0;
            }
            if ( !property_exists($result, "team1") )
            {
                $result->team1 = '';
            }
            if ( !property_exists($result, "team2") )
            {
                $result->team2 = '';
            }
            }

		}
		catch (Exception $e)
		{
$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
			$result = false;
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}

	/**
	 * Method to load content matchday data
	 *
	 * @access private
	 * @return boolean    True on success
	 * @since  1.5
	 */
	public static function getMatchData($match_id, $cfg_which_database = 0)
	{
		$app   = Factory::getApplication();
		$db    = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);
		$query->select('m.*,CASE m.time_present	when NULL then NULL	else DATE_FORMAT(m.time_present, "%H:%i") END AS time_present,m.extended as matchextended');
		$query->select('t1.name AS hometeam, t1.id AS t1id');
		$query->select('t2.name as awayteam, t2.id AS t2id');
		$query->select('pt1.project_id');
		$query->select('CONCAT_WS(\':\',pg.id,pg.alias) AS playground_slug ');
		$query->select('pg.picture AS playground_picture,pg.name AS playground_name ');
		$query->select('r.roundcode');
		$query->from('#__sportsmanagement_match AS m');
		$query->join('INNER', '#__sportsmanagement_round AS r ON m.round_id = r.id');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt1 ON pt1.id = m.projectteam1_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id as st1 ON st1.id = pt1.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt2 ON pt2.id = m.projectteam2_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id as st2 ON st2.id = pt2.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id ');
		$query->join('LEFT', '#__sportsmanagement_playground AS pg ON pg.id = m.playground_id ');
		$query->where('m.id = ' . (int) $match_id);

		try
		{
			$db->setQuery($query);
			$result = $db->loadObject();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . Text::_($e->getMessage()), 'Error');
			$result = false;
		}

		return $result;

	}

	/**
	 * returns starters referees id for the specified team
	 *
	 * @param   int  $project_position_id
	 *
	 * @return array of referee ids
	 */
	public static function getRefereeRoster($project_position_id = 0, $match_id = 0, $project_referee_id = 0)
	{
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$result = '';
		$query->select('pref.id AS value,pr.firstname,pr.nickname,pr.lastname,pr.email');
		$query->from('#__sportsmanagement_match_referee AS mr');
		$query->join('LEFT', '#__sportsmanagement_project_referee AS pref ON mr.project_referee_id=pref.id AND pref.published = 1');
		$query->join('LEFT', '#__sportsmanagement_season_person_id AS spi ON pref.person_id=spi.id');
		$query->join('LEFT', '#__sportsmanagement_person AS pr ON spi.person_id=pr.id AND pr.published = 1');
		$query->where('mr.match_id = ' . $match_id);

		if ($project_position_id)
		{
			$query->where('mr.project_position_id = ' . $project_position_id);
		}

		if ($project_referee_id)
		{
			$query->where('mr.project_referee_id = ' . $project_referee_id);
		}

		$query->order('mr.project_position_id, mr.ordering ASC');

		try
		{
			$db->setQuery($query);
			$result = $db->loadObjectList('value');
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}

		return $result;
	}

	/**
	 * sportsmanagementModelMatch::getPlayerEventsbb()
	 *
	 * @param   integer  $teamplayer_id
	 * @param   integer  $event_type_id
	 * @param   integer  $match_id
	 *
	 * @return
	 */
	function getPlayerEventsbb($teamplayer_id = 0, $event_type_id = 0, $match_id = 0)
	{
		$query = Factory::getDBO()->getQuery(true);

		$ret                   = array();
		$record                = new stdClass;
		$record->id            = '';
		$record->event_sum     = 0;
		$record->event_time    = "";
		$record->notice        = "";
		$record->teamplayer_id = $teamplayer_id;
		$record->event_type_id = $event_type_id;
		$ret[0]                = $record;

		$query->select('me.projectteam_id,me.id,me.match_id,me.teamplayer_id,me.event_type_id,me.event_sum,me.event_time,me.notice');
		$query->from('#__sportsmanagement_match_event AS me');
		$query->where('me.match_id = ' . $match_id);
		$query->where('me.teamplayer_id = ' . $teamplayer_id);
		$query->where('me.event_type_id = ' . $event_type_id);
		$query->order('me.teamplayer_id ASC');

		Factory::getDBO()->setQuery($query);
		$result = Factory::getDBO()->loadObjectList();

		if (count($result) > 0)
		{
			return $result;
		}

		return $ret;
	}


	/**
	 * Method to update starting lineup list
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function updateRoster($post)
	{
		$app           = Factory::getApplication();
		$option        = Factory::getApplication()->input->getCmd('option');
		$date          = Factory::getDate();
		$user          = Factory::getUser();
		$db            = sportsmanagementHelper::getDBConnection();
		$query         = $db->getQuery(true);
		$result        = true;
		$positions     = $post['positions'];
		$mid           = $post['id'];
		$team          = $post['team'];
		$trikotnumbers = $post['trikot_number'];
		$captain       = $post['captain'];

		if ($mid && $team)
		{
			$query->clear();
			$query->select('mp.id');
			$query->from('#__sportsmanagement_match_player AS mp');
			$query->join('INNER', ' #__sportsmanagement_season_team_person_id AS sp ON sp.id = mp.teamplayer_id ');
			$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.team_id = sp.team_id ');
			$query->join('LEFT', ' #__sportsmanagement_project_team AS pt ON pt.team_id = st1.id ');
			$query->where('mp.came_in = ' . self::MATCH_ROSTER_STARTER);
			$query->where('mp.match_id = ' . $mid);
			$query->where('pt.id = ' . $team);
			$db->setQuery($query);

			try
			{
				$result = $db->loadColumn();
			}
			catch (Exception $e)
			{
				$msg  = $e->getMessage(); // Returns "Normally you would have other code...
				$code = $e->getCode(); // Returns '500';
			}

			if ($result)
			{
				$query->clear();
				$query->delete($db->quoteName('#__sportsmanagement_match_player'));
				$query->where('id IN (' . implode(",", $result) . ')');
				$db->setQuery($query);

				try
				{
					$result = $db->execute();
				}
				catch (Exception $e)
				{
					$msg  = $e->getMessage(); // Returns "Normally you would have other code...
					$code = $e->getCode(); // Returns '500';
				}
			}

			foreach ($positions AS $project_position_id => $pos)
			{
				if (isset($post['position' . $project_position_id]))
				{
					foreach ($post['position' . $project_position_id] AS $ordering => $player_id)
					{
						$temp                      = new stdClass;
						$temp->match_id            = $mid;
						$temp->teamplayer_id       = $player_id;
						$temp->project_position_id = $pos->pposid;
						$temp->came_in             = self::MATCH_ROSTER_STARTER;
						$temp->trikot_number       = $trikotnumbers[$player_id];
						$temp->captain             = $captain[$player_id];
						$temp->ordering            = $ordering;
						$temp->modified            = $date->toSql();
						$temp->modified_by         = $user->get('id');

						try
						{
							$resultquery = $db->insertObject('#__sportsmanagement_match_player', $temp);
						}
						catch (Exception $e)
						{
							$msg  = $e->getMessage(); // Returns "Normally you would have other code...
							$code = $e->getCode(); // Returns '500';
							$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
						}
					}
				}
			}
		}
		else
		{
			$result = true;
		}

		return $result;
	}

	/**
	 * Method to update starting lineup list
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function updateStaff($post)
	{
		$app       = Factory::getApplication();
		$option    = Factory::getApplication()->input->getCmd('option');
		$date      = Factory::getDate();
		$user      = Factory::getUser();
		$db        = sportsmanagementHelper::getDBConnection();
		$query     = $db->getQuery(true);
		$result    = true;
		$positions = $post['staffpositions'];
		$mid       = $post['id'];
		$team      = $post['team'];

		if ($mid && $team)
		{
			$query->clear();
			$query->select('mp.id');
			$query->from('#__sportsmanagement_match_staff AS mp');
			$query->join('INNER', ' #__sportsmanagement_season_team_person_id AS sp ON sp.id = mp.team_staff_id ');
			$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.team_id = sp.team_id ');
			$query->join('LEFT', ' #__sportsmanagement_project_team AS pt ON pt.team_id = st1.id ');
			$query->where('mp.match_id = ' . $mid);
			$query->where('pt.id = ' . $team);
			$db->setQuery($query);

			try
			{
				$result = $db->loadColumn();
			}
			catch (Exception $e)
			{
				$msg  = $e->getMessage(); // Returns "Normally you would have other code...
				$code = $e->getCode(); // Returns '500';
			}

			if ($result)
			{
				$query->clear();
				$query->delete(Factory::getDBO()->quoteName('#__sportsmanagement_match_staff'));
				$query->where('id IN (' . implode(",", $result) . ')');

				try
				{
					$result = $db->execute();
				}
				catch (Exception $e)
				{
					$msg  = $e->getMessage(); // Returns "Normally you would have other code...
					$code = $e->getCode(); // Returns '500';
					$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
				}
			}

			foreach ($positions AS $project_position_id => $pos)
			{
				if (isset($post['staffposition' . $project_position_id]))
				{
					foreach ($post['staffposition' . $project_position_id] AS $ordering => $player_id)
					{
						$temp                      = new stdClass;
						$temp->match_id            = $mid;
						$temp->team_staff_id       = $player_id;
						$temp->project_position_id = $pos->pposid;
						$temp->ordering            = $ordering;
						$temp->modified            = $date->toSql();
						$temp->modified_by         = $user->get('id');

						try
						{
							$resultquery = $db->insertObject('#__sportsmanagement_match_staff', $temp);
						}
						catch (Exception $e)
						{
							$msg  = $e->getMessage(); // Returns "Normally you would have other code...
							$code = $e->getCode(); // Returns '500';
							$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
						}
					}
				}
			}
		}
		else
		{
			return true;
		}

		return true;
	}


	/**
	 * save the submitted substitution
	 *
	 * @param   array  $data
	 *
	 * @return boolean
	 */
	function savesubstitution($data)
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$date   = Factory::getDate();
		$user   = Factory::getUser();
		$db     = sportsmanagementHelper::getDBConnection();
		$query  = $db->getQuery(true);

		if (empty($data['project_position_id']))
		{
			$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_NO_SUBST_POSITION_ID'));

			return false;
		}

		if (empty($data['in_out_time']))
		{
			$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_NO_SUBST_TIME'));

			return false;
		}

		if (empty($data['in']))
		{
			$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_NO_SUBST_IN'));

			return false;
		}

		if (empty($data['out']))
		{
			$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_NO_SUBST_OUT'));

			return false;
		}

		if ((int) $data['in_out_time'] > (int) $data['projecttime'])
		{
			$this->setError(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_SUBST_TIME_OVER_PROJECTTIME', $data['in_out_time'], $data['projecttime']));

			return false;
		}

		if (!($data['matchid']))
		{
			$this->setError(
				"in: " . $data['in'] .
				", out: " . $data['out'] .
				", matchid: " . $data['matchid'] .
				", project_position_id: " . $data['project_position_id']
			);

			return false;
		}

		$player_in           = (int) $data['in'];
		$player_out          = (int) $data['out'];
		$match_id            = (int) $data['matchid'];
		$in_out_time         = $data['in_out_time'];
		$project_position_id = $data['project_position_id'];

		/**
		 * nicht anlegen, wenn der wechsel schon existiert
		 */

		$query->clear();
		$query->select('mp.id');
		$query->from('#__sportsmanagement_match_player as mp');
		$query->where('mp.match_id = ' . $match_id);
		$query->where('mp.teamplayer_id = ' . $player_in);
		$query->where('mp.in_for = ' . $player_out);
		$query->where('mp.in_out_time = ' . $in_out_time);
		$query->where('mp.came_in = 1');
		$db->setQuery($query);
		$substitution_id = $db->loadResult();

		if ($substitution_id)
		{
			return false;
		}

		if ($project_position_id == 0 && $player_in > 0)
		{
			/**
			 * retrieve normal position of player getting in
			 */
			$query->clear();
			$query->select('pt.project_position_id');
			$query->from('#__sportsmanagement_team_player AS pt');
			$query->where('pt.player_id = ' . $player_in);
			$db->setQuery($query);
			$project_position_id = $db->loadResult();
		}

		if ($player_in > 0)
		{
			$in_player_record                      = new stdClass;
			$in_player_record->match_id            = $match_id;
			$in_player_record->came_in             = self::MATCH_ROSTER_SUBSTITUTE_IN; // 1 //1=came in, 2=went out
			$in_player_record->teamplayer_id       = $player_in;
			$in_player_record->in_for              = ($player_out > 0) ? $player_out : 0;
			$in_player_record->in_out_time         = $in_out_time;
			$in_player_record->project_position_id = $project_position_id;
			$in_player_record->modified            = $date->toSql();
			$in_player_record->modified_by         = $user->get('id');
			/**
			 * Insert the object into the table.
			 */
			try
			{
				$resultinsert = $db->insertObject('#__sportsmanagement_match_player', $in_player_record);
			}
			catch (Exception $e)
			{
				$app->enqueueMessage(Text::_(__METHOD__ . ' ' . ' ' . $e->getMessage()), 'error');

				return false;
			}
		}

		if ($player_out > 0 && $player_in == 0)
		{
			$out_player_record                      = new stdClass;
			$out_player_record->match_id            = $match_id;
			$out_player_record->came_in             = self::MATCH_ROSTER_SUBSTITUTE_OUT; // 2; //0=starting lineup
			$out_player_record->teamplayer_id       = $player_out;
			$out_player_record->in_out_time         = $in_out_time;
			$out_player_record->project_position_id = $project_position_id;
			$out_player_record->out                 = 1;
			$out_player_record->modified            = $date->toSql();
			$out_player_record->modified_by         = $user->get('id');
			/**
			 * Insert the object into the table.
			 */
			try
			{
				$resultinsert = $db->insertObject('#__sportsmanagement_match_player', $out_player_record);
			}
			catch (Exception $e)
			{
				$app->enqueueMessage(Text::_(__METHOD__ . ' ' . ' ' . $e->getMessage()), 'error');

				return false;
			}
		}

		return true;
	}

	/**
	 * remove specified subsitution
	 *
	 * @param   int  $substitution_id
	 *
	 * @return boolean
	 */
	function removeSubstitution($substitution_id)
	{
		$app   = Factory::getApplication();
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		/**
		 * the subsitute isn't getting in so we delete the substitution
		 */
		$query->clear();
		$query->delete('#__sportsmanagement_match_player');
		$query->where("id = " . $db->Quote($substitution_id) . " OR id = " . $db->Quote($substitution_id + 1));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_ERROR_DELETING_SUBST') . ' ' . $e->getMessage()), 'error');

			return false;
		}

		return true;
	}


	/**
	 * sportsmanagementModelMatch::deleteevent()
	 *
	 * @param   mixed  $event_id
	 *
	 * @return
	 */
	function deleteevent($event_id)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);

		// Delete all custom keys
		$conditions = array(
			$db->quoteName('id') . '=' . $event_id
		);
		$query->delete($db->quoteName('#__sportsmanagement_match_event'));
		$query->where($conditions);
		$db->setQuery($query);

		/**
		 * Delete the object from the table.
		 */
		try
		{
			$db->execute();
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return true;
		}
		catch (Exception $e)
		{
			$this->setError('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_DELETE_FAILED_EVENT');
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return false;
		}

		return true;

	}


	/**
	 * sportsmanagementModelMatch::deletecommentary()
	 *
	 * @param   mixed  $event_id
	 *
	 * @return
	 */
	function deletecommentary($event_id)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);

		// Delete all custom keys
		$conditions = array(
			$db->quoteName('id') . '=' . $event_id
		);

		$query->delete($db->quoteName('#__sportsmanagement_match_commentary'));
		$query->where($conditions);
		$db->setQuery($query);

		/**
		 * Delete the object from the table.
		 */
		try
		{
			$db->execute();
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return true;
		}
		catch (Exception $e)
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_DELETE_FAILED_COMMENTARY'), Log::ERROR, 'jsmerror');
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return false;
		}

		return true;

	}


	/**
	 * sportsmanagementModelMatch::savecomment()
	 *
	 * @param   mixed  $data
	 *
	 * @return
	 */
	function savecomment($data)
	{
		$date = Factory::getDate();
		$user = Factory::getUser();

		// Live kommentar speichern
		if (empty($data['event_time']))
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_COMMENT_NO_TIME'), Log::ERROR, 'jsmerror');

			return false;
		}

		if (empty($data['notes']))
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_COMMENT_NO_COMMENT'), Log::ERROR, 'jsmerror');

			return false;
		}

		if ((int) $data['event_time'] > (int) $data['projecttime'])
		{
			Log::add(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_COMMENT_TIME_OVER_PROJECTTIME', $data['event_time'], $data['projecttime']), Log::ERROR, 'jsmerror');

			return false;
		}

		$db                = Factory::getDbo();
		$query             = $db->getQuery(true);
		$temp              = new stdClass;
		$temp->event_time  = $data['event_time'];
		$temp->match_id    = $data['match_id'];
		$temp->type        = $data['type'];
		$temp->notes       = $data['notes'];
		$temp->modified    = $date->toSql();
		$temp->modified_by = $user->get('id');

		/**
		 * Insert the object into the table.
		 */
		try
		{
			$resultinsert = $db->insertObject('#__sportsmanagement_match_commentary', $temp);
			$result       = $db->insertid();
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return $result;
		}
		catch (Exception $e)
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_DELETE_FAILED_EVENT'), Log::ERROR, 'jsmerror');
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return false;
		}

		return true;

	}

	/**
	 * sportsmanagementModelMatch::saveevent()
	 *
	 * @param   mixed  $data
	 *
	 * @return
	 */
	function saveevent($data)
	{
		$date = Factory::getDate();
		$user = Factory::getUser();
		$app  = Factory::getApplication();

		if ($data['useeventtime'])
		{
			if (empty($data['event_time']))
			{
				Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_EVENT_NO_TIME')), Log::ERROR, 'jsmerror');
				$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_EVENT_NO_TIME'));
				return false;
			}
		}

		if (empty($data['event_sum']))
		{
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_EVENT_NO_EVENT_SUM')), Log::ERROR, 'jsmerror');
			$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_EVENT_NO_EVENT_SUM'));
			return false;
		}

		if ($data['useeventtime'])
		{
			if ((int) $data['event_time'] > (int) $data['projecttime'])
			{
				Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_EVENT_TIME_OVER_PROJECTTIME', $data['event_time'], $data['projecttime'])), Log::ERROR, 'jsmerror');
				$this->setError(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_EVENT_TIME_OVER_PROJECTTIME', $data['event_time'], $data['projecttime']));
				return false;
			}
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->clear();
		$query->select('mp.id');
		$query->from('#__sportsmanagement_match_event as mp');
		$query->where('mp.match_id = ' . $data['match_id']);
		$query->where('mp.projectteam_id = ' . $data['projectteam_id']);
		$query->where('mp.teamplayer_id = ' . $data['teamplayer_id']);
		$query->where('mp.event_time = ' . $data['event_time']);
		$query->where('mp.event_sum = ' . $data['event_sum']);
		$db->setQuery($query);
		$match_event_id = $db->loadResult();

		if ($match_event_id)
		{
			return false;
		}

		$temp                 = new stdClass;
		$temp->match_id       = $data['match_id'];
		$temp->projectteam_id = $data['projectteam_id'];
		$temp->teamplayer_id  = $data['teamplayer_id'];
		$temp->event_time     = $data['event_time'];
		$temp->event_type_id  = $data['event_type_id'];
		$temp->event_sum      = $data['event_sum'];
		$temp->notice         = $data['notice'];
		$temp->notes          = $data['notes'];
		$temp->modified       = $date->toSql();
		$temp->modified_by    = $user->get('id');
		/**
		 * Insert the object into the table.
		 */
		try
		{
			$resultinsert = $db->insertObject('#__sportsmanagement_match_event', $temp);
			$result       = $db->insertid();
			/**
			 * jetzt schauen wir nach, ob es statistiken zu dem event in der position gibt
			 */
			$query->clear();
			$query->select('st.id,st.params,st.class');
			$query->from('#__sportsmanagement_statistic as st');
			$query->join('INNER', '#__sportsmanagement_position_statistic AS possta ON st.id = possta.statistic_id');
			$query->join('INNER', '#__sportsmanagement_match_player AS matplay ON matplay.project_position_id = possta.position_id');
			$query->where('matplay.match_id = ' . $data['match_id']);
			$query->where('matplay.teamplayer_id = ' . $data['teamplayer_id']);
			$db->setQuery($query);
			$stats_params = $db->loadObjectList();

			foreach ($stats_params as $stats_param)
			{
				if ($stats_param->class == 'basic')
				{
					$event_param = json_decode($stats_param->params, true);

					if ($event_param['event_id'] == $data['event_type_id'])
					{
						$statsvalue = $event_param['event_value'];
						$statsid    = $stats_param->id;
					}
				}
			}

			/**
			 * Überprüfen und anlegen
			 */
			if ($statsvalue && $statsid)
			{
				$query->clear();
				$query->select('id');
				$query->from('#__sportsmanagement_match_statistic');
				$query->where('match_id = ' . $data['match_id']);
				$query->where('teamplayer_id = ' . $data['teamplayer_id']);
				$query->where('statistic_id = ' . $statsid);
				$db->setQuery($query);
				$match_stats = $db->loadResult();

				if (!$match_stats)
				{
					$temp                 = new stdClass;// $object = new stdClass();
					$temp->match_id       = $data['match_id'];
					$temp->projectteam_id = $data['projectteam_id'];
					$temp->teamplayer_id  = $data['teamplayer_id'];
					$temp->statistic_id   = $statsid;
					$temp->value          = $statsvalue;
					$temp->modified       = $date->toSql();
					$temp->modified_by    = $user->get('id');
					$resultinsert         = $db->insertObject('#__sportsmanagement_match_statistic', $temp);
				}
			}

			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return $result;
		}
		catch (Exception $e)
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_DELETE_FAILED_EVENT'), Log::ERROR, 'jsmerror');
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return false;
		}

		return true;

	}

	/**
	 * sportsmanagementModelMatch::getPressebericht()
	 *
	 * @return
	 */
	function getPressebericht()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		// $match_id = $cid[0];
		$match_id  = Factory::getApplication()->input->getVar('match_id');
		$this->_id = $match_id;
		$file      = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_sportsmanagement' . DIRECTORY_SEPARATOR . 'pressebericht' . DIRECTORY_SEPARATOR . $match_id . '.jlg';
		$app->enqueueMessage(Text::_('datei = ' . $file), '');

		// Where the cache will be stored
		$dcsv['file']      = $file;
		$dcsv['cachefile'] = JPATH_SITE . DIRECTORY_SEPARATOR . '/tmp/' . md5($dcsv['file']);

		// If there is no chache saved or is older than the cache time create a new cache
		// open the cache file for writing
		$fp = fopen($dcsv['cachefile'], 'w');

		// Save the contents of output buffer to the file
		fwrite($fp, file_get_contents($dcsv['file']));

		// Close the file
		fclose($fp);

		// New ParseCSV object.
		$csv = new JSMparseCSV;

		// Parse CSV with auto delimiter detection
		$csv->auto($dcsv['cachefile']);

		return $csv;
	}

	/**
	 * sportsmanagementModelMatch::getPresseberichtMatchnumber()
	 *
	 * @param   mixed  $csv_file
	 *
	 * @return
	 */
	function getPresseberichtMatchnumber($csv_file)
	{
		$option   = Factory::getApplication()->input->getCmd('option');
		$app      = Factory::getApplication();
		$match_id = Factory::getApplication()->input->getVar('match_id');
		$tblmatch = Table::getInstance("match", "sportsmanagementTable");
		$tblmatch->load($match_id);
		$match_number     = $tblmatch->match_number;
		$csv_match_number = $csv_file->data[0]['Spielberichtsnummer'];
		$teile            = explode(".", $csv_match_number);

		if ($match_number != $teile[0])
		{
			$app->enqueueMessage(Text::_('Spielnummer der Datei passt nicht zur Spielnummer im Projekt.'), 'Error');
			$app->enqueueMessage(Text::_($teile[0]), 'Error');
			return false;
		}
		else
		{
			$app->enqueueMessage(Text::_('Spielnummern sind identisch. Datei wird verarbeitet'), 'Notice');
			return true;
		}

	}

	/**
	 * sportsmanagementModelMatch::getPresseberichtReadPlayers()
	 *
	 * @param   mixed  $csv_file
	 *
	 * @return void
	 */
	function getPresseberichtReadPlayers($csv_file)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$db     = sportsmanagementHelper::getDBConnection();
		$query  = $db->getQuery(true);

		$csv_player_count = 40;
		$find_csv = '';
		$project_id       = $app->getUserState("$option.pid", '0');
		$match_id         = Factory::getApplication()->input->getVar('match_id');
		$tblmatch         = Table::getInstance("match", "sportsmanagementTable");
		$tblmatch->load($match_id);
		$tblproject = Table::getInstance("project", "sportsmanagementTable");
		$tblproject->load($project_id);
		$favteam   = $tblproject->fav_team;
		$season_id = $tblproject->season_id;

		for ($a = 0; $a < sizeof($csv_file->titles); $a++)
		{
			$csv_file->titles[$a] = utf8_encode($csv_file->titles[$a]);
		}

		foreach ($csv_file->data as $key => $key2)
		{
			foreach ($key2 as $key3 => $value)
			{
				$key4[$key3] = $value;
			}

			$csv_file->data[$key] = $key4;
		}

		if (!$favteam)
		{
			$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_PRESSEBERICHT_NO_FAV_TEAM'), 'error');

			return false;
		}

		$tblteam = Table::getInstance("team", "sportsmanagementTable");
		$tblteam->load($favteam);

		// Select some fields
		$query->clear();
		$query->select('pt1.id');

		// From the table
		$query->from('#__sportsmanagement_project_team as pt1');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
		$query->where('pt1.project_id = ' . $project_id);
		$query->where('st1.team_id = ' . $favteam);

		$db->setQuery($query);

		try
		{
			$projectteamid = $db->loadResult();
		}
		catch (Exception $e)
		{
			$msg  = $e->getMessage(); // Returns "Normally you would have other code...
			$code = $e->getCode(); // Returns '500';
		}

		if ($projectteamid)
		{
			$this->projectteamid = $projectteamid;
			$app->enqueueMessage(Text::_('Spieldetails von ' . $tblteam->name . ' werden verarbeitet.'), 'Notice');

			if ($projectteamid == $tblmatch->projectteam1_id)
			{
				$app->enqueueMessage(Text::_('Heimteam ' . $tblteam->name . ' wird verarbeitet.'), 'Notice');
				$find_csv = 'H';
			}
			elseif ($projectteamid == $tblmatch->projectteam2_id)
			{
				$app->enqueueMessage(Text::_('Auswärtsteam ' . $tblteam->name . ' wird verarbeitet.'), 'Notice');
				$find_csv = 'G';
			}

			// Schiedsrichtergespann
			$schiedsrichter_gespann = array('Schiedsrichter', 'Assistent-1', 'Assistent-2', 'Vierter-Offizieller');

			foreach ($schiedsrichter_gespann as $schiedsrichter)
			{
				// Ist ein Schiedsrichter gepflegt?
				if (isset($csv_file->data[0][$schiedsrichter]) && !empty($csv_file->data[0][$schiedsrichter]))
				{
					// Falls wir den Schiedsrichter noch nicht im Objekt halten müssen wir eine neue stdClass anlegen
					if (!isset($this->csv_referee[$csv_file->data[0][$schiedsrichter]]))
					{
						$this->csv_referee[$csv_file->data[0][$schiedsrichter]] = new stdClass;
					}

					// Aufbau eines Schiedsrichters: Vorname Nachname (Ort)
					$teile     = explode("(", ($csv_file->data[0][$schiedsrichter]));
					$name      = explode(" ", trim($teile[0]), 2);
					$firstname = htmlspecialchars(trim($name[0]), ENT_QUOTES);
					$lastname  = htmlspecialchars(trim($name[1]), ENT_QUOTES);

					$this->csv_referee[$csv_file->data[0][$schiedsrichter]]->csv_position        = $schiedsrichter;
					$this->csv_referee[$csv_file->data[0][$schiedsrichter]]->firstname           = $firstname;
					$this->csv_referee[$csv_file->data[0][$schiedsrichter]]->lastname            = $lastname;
					$this->csv_referee[$csv_file->data[0][$schiedsrichter]]->person_id           = 0;
					$this->csv_referee[$csv_file->data[0][$schiedsrichter]]->project_referee_id  = 0;
					$this->csv_referee[$csv_file->data[0][$schiedsrichter]]->project_position_id = 0;

					// Falls es die Person schon gibt, laden wir ein paar grundlegende Daten
					if ($firstname && $lastname)
					{
						$person_id = $this->getPersonId($firstname, $lastname);

						if ($person_id)
						{
							$this->csv_referee[$csv_file->data[0][$schiedsrichter]]->person_id = $person_id;

							$season_person_id = $this->getSeasonPersonAssignment($person_id, $season_id, 3)->id;

							if ($season_person_id)
							{
								$this->csv_referee[$csv_file->data[0][$schiedsrichter]]->season_person_id = $season_person_id;

								$project_referee = $this->getProjectReferee($season_person_id, $project_id);

								if ($project_referee)
								{
									$this->csv_referee[$csv_file->data[0][$schiedsrichter]]->project_referee_id  = $project_referee->id;
									$this->csv_referee[$csv_file->data[0][$schiedsrichter]]->project_position_id = $project_referee->project_position_id;
								}
							}
						}
					}
				}
			}

			// Stammspieler
			for ($i = 1; $i <= $csv_player_count; $i++)
			{
				if (isset($csv_file->data[0][$find_csv . '-S' . $i . '-Nr']) && !empty($csv_file->data[0][$find_csv . '-S' . $i . '-Nr']))
				{
					if (!isset($this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']]))
					{
						$this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']] = new stdClass;
					}

					$this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']]->nummer  = $csv_file->data[0][$find_csv . '-S' . $i . '-Nr'];
					$this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']]->name    = $csv_file->data[0][$find_csv . '-S' . $i . '-Spieler'];
					$this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']]->hinweis = $csv_file->data[0][$find_csv . '-S' . $i . '-Hinweis'];
					$this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']]->status  = $csv_file->data[0][$find_csv . '-S' . $i . '-Status'];

					$teile                                                                                    = explode(",", $csv_file->data[0][$find_csv . '-S' . $i . '-Spieler']);
					$this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']]->lastname            = htmlspecialchars(trim($teile[0]), ENT_QUOTES);
					$this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']]->firstname           = htmlspecialchars(trim($teile[1]), ENT_QUOTES);
					$this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']]->person_id           = 0;
					$this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']]->project_person_id   = 0;
					$this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']]->project_position_id = 0;
					$this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']]->position_id         = 0;
					$this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']]->startaufstellung    = 1;

					// Gibt es den spieler
					// Select some fields
					if ($teile[0])
					{
						$person_id = $this->getPersonId($teile[1], $teile[0]);
					}

					if ($person_id)
					{
						if (!isset($this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']]))
						{
							$this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']] = new stdClass;
						}

						$this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']]->person_id           = $person_id;
						$projectpersonid                                                                          = $this->getSeasonTeamPersonId($person_id, $favteam, $season_id);
						$this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']]->project_person_id   = $projectpersonid->id;
						$this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']]->project_position_id = $projectpersonid->project_position_id;
						$this->csv_player[$csv_file->data[0][$find_csv . '-S' . $i . '-Nr']]->position_id         = $projectpersonid->position_id;
					}
				}
			}

			// Auswechselspieler
			for ($i = 1; $i <= $csv_player_count; $i++)
			{
				if (isset($csv_file->data[0][$find_csv . '-A' . $i . '-Nr']) && !empty($csv_file->data[0][$find_csv . '-A' . $i . '-Nr']))
				{
					if (!isset($this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']]))
					{
						$this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']] = new stdClass;
					}

					$this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']]->nummer  = $csv_file->data[0][$find_csv . '-A' . $i . '-Nr'];
					$this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']]->name    = $csv_file->data[0][$find_csv . '-A' . $i . '-Spieler'];
					$this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']]->hinweis = $csv_file->data[0][$find_csv . '-A' . $i . '-Hinweis'];
					$this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']]->status  = $csv_file->data[0][$find_csv . '-A' . $i . '-Status'];

					$teile                                                                                    = explode(",", $csv_file->data[0][$find_csv . '-A' . $i . '-Spieler']);
					$this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']]->lastname            = htmlspecialchars(trim($teile[0]), ENT_QUOTES);
					$this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']]->firstname           = htmlspecialchars(trim($teile[1]), ENT_QUOTES);
					$this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']]->person_id           = 0;
					$this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']]->project_person_id   = 0;
					$this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']]->project_position_id = 0;
					$this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']]->position_id         = 0;
					$this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']]->startaufstellung    = 0;

					// Gibt es den spieler ?
					// Select some fields
					if ($teile[0])
					{
						$person_id = $this->getPersonId($teile[1], $teile[0]);
					}

					if ($person_id)
					{
						if (!isset($this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']]))
						{
							$this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']] = new stdClass;
						}

						$this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']]->person_id           = $person_id;
						$projectpersonid                                                                          = $this->getSeasonTeamPersonId($person_id, $favteam, $season_id);
						$this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']]->project_person_id   = $projectpersonid->id;
						$this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']]->project_position_id = $projectpersonid->project_position_id;
						$this->csv_player[$csv_file->data[0][$find_csv . '-A' . $i . '-Nr']]->position_id         = $projectpersonid->position_id;
					}
				}
			}

			// Auswechslungen
			for ($i = 1; $i <= $csv_player_count; $i++)
			{
				if (isset($csv_file->data[0][$find_csv . '-S' . $i . '-Ausw-Zeit']) && !empty($csv_file->data[0][$find_csv . '-S' . $i . '-Ausw-Zeit']))
				{
					if (!isset($this->csv_in_out[$i]))
					{
						$this->csv_in_out[$i] = new stdClass;
					}

					$this->csv_in_out[$i]->in_out_time = $csv_file->data[0][$find_csv . '-S' . $i . '-Ausw-Zeit'];
					$this->csv_in_out[$i]->came_in     = 1;
					$this->csv_in_out[$i]->in          = $csv_file->data[0][$find_csv . '-S' . $i . '-Ausw-Nr'];
					$this->csv_in_out[$i]->out         = $csv_file->data[0][$find_csv . '-S' . $i . '-Ausw-Für Nr'];
					$this->csv_in_out[$i]->spieler     = $csv_file->data[0][$find_csv . '-S' . $i . '-Ausw-Spieler'];
					$this->csv_in_out[$i]->spielerout  = $csv_file->data[0][$find_csv . '-S' . $i . '-Ausw-für-Spieler'];
				}
			}

			// Karten
			$karten_index = 0;
			$karten_typen = array(
				'Gelb'    => 'COM_SPORTSMANAGEMENT_SOCCER_E_YELLOW_CARD',
				'Gelbrot' => 'COM_SPORTSMANAGEMENT_SOCCER_E_YELLOW-RED_CARD',
				'Rot'     => 'COM_SPORTSMANAGEMENT_SOCCER_E_RED_CARD'
			);

			foreach ($karten_typen as $csv_karte => $event_type_name)
			{
				// Event Type ID aus der DB holen, wenn es ein Event gibt, das den gleichen Namen hat
				$karten_event_type_id = 0;
				$karten_event_type    = $this->getEventType($event_type_name);

				if ($karten_event_type)
				{
					$karten_event_type_id = $karten_event_type->id;
				}

				for ($i = 1; $i <= $csv_player_count; $i++)
				{
					if (isset($csv_file->data[0][$find_csv . '-S' . $i . '-' . $csv_karte . '-Zeit']) && !empty($csv_file->data[0][$find_csv . '-S' . $i . '-' . $csv_karte . '-Zeit']))
					{
						if (!isset($this->csv_cards[$karten_index]))
						{
							$this->csv_cards[$karten_index] = new stdClass;
						}

						$this->csv_cards[$karten_index]->event_time    = $csv_file->data[0][$find_csv . '-S' . $i . '-' . $csv_karte . '-Zeit'];
						$this->csv_cards[$karten_index]->event_name    = $event_type_name;
						$this->csv_cards[$karten_index]->event_sum     = 1;
						$this->csv_cards[$karten_index]->spielernummer = $csv_file->data[0][$find_csv . '-S' . $i . '-' . $csv_karte . '-Nr'];
						$this->csv_cards[$karten_index]->spieler       = $csv_file->data[0][$find_csv . '-S' . $i . '-' . $csv_karte . '-Spieler'];
						$this->csv_cards[$karten_index]->notice        = $csv_file->data[0][$find_csv . '-S' . $i . '-' . $csv_karte . '-Grund'];
						$this->csv_cards[$karten_index]->event_type_id = $karten_event_type_id;
						$karten_index++;
					}
				}
			}

			// Gibt es die karten schon ?
			// gibt es das event schon ?
			foreach ($this->csv_cards as $key => $value)
			{
				$spielernummer = $value->spielernummer;

				if (!isset($this->csv_player[$spielernummer]))
				{
					$this->csv_player[$spielernummer] = new stdClass;
				}

				$project_person_id = $this->csv_player[$spielernummer]->project_person_id;

				if ($project_person_id)
				{
					$query->clear();
					$query->select('event_type_id');

					// From the table
					$query->from('#__sportsmanagement_match_event');
					$query->where('match_id = ' . $match_id);
					$query->where('projectteam_id = ' . $projectteamid);
					$query->where('teamplayer_id = ' . $project_person_id);

					$db->setQuery($query);

					try
					{
						$match_event_id = $db->loadResult();

						if ($match_event_id)
						{
							$this->csv_cards[$key]->event_type_id = $match_event_id;
						}
					}
					catch (Exception $e)
					{
						$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage(), 'error'); // Commonly to still display that error
						$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode(), 'error'); // commonly to still display that error
					}
				}
			}

			// Sämtliche Mannschaftsverantwortlichen verarbeiten
			$i                                    = 1;
			$mannschaftsverantwortlichePositionen = array('Trainer', 'Trainerassistent', 'Arzt', 'Masseur', 'Zeugwart', 'Mannschaftsverantwortlicher');

			foreach ($mannschaftsverantwortlichePositionen as $mannschaftsverantwortlichePosition)
			{
				if ( array_key_exists( $find_csv.'-'.$mannschaftsverantwortlichePosition, $csv_file->data[0] ) );
				{
				if (!isset($this->csv_staff[$i]))
				{
					$this->csv_staff[$i] = new stdClass;
				}

				$this->csv_staff[$i]->position            = $mannschaftsverantwortlichePosition;
				$this->csv_staff[$i]->name                = $csv_file->data[0][$find_csv . '-' . $mannschaftsverantwortlichePosition];
				$teile                                    = explode(",", $this->csv_staff[$i]->name);
				$firstname                                = htmlspecialchars(trim($teile[1]), ENT_QUOTES);
				$lastname                                 = htmlspecialchars(trim($teile[0]), ENT_QUOTES);
				$this->csv_staff[$i]->lastname            = $lastname;
				$this->csv_staff[$i]->firstname           = $firstname;
				$this->csv_staff[$i]->person_id           = 0;
				$this->csv_staff[$i]->project_person_id   = 0;
				$this->csv_staff[$i]->project_position_id = 0;
				$this->csv_staff[$i]->position_id         = 0;

				/** Falls es den Staff gibt, ein paar Felder selektieren */
				if ($lastname)
				{
					$person_id = $this->getPersonId($firstname, $lastname);
					if ($person_id)
					{
						$this->csv_staff[$i]->person_id           = $person_id;
						$projectpersonid                          = $this->getSeasonTeamPersonId($person_id, $favteam, $season_id);
						$this->csv_staff[$i]->project_person_id   = $projectpersonid->id;
						$this->csv_staff[$i]->project_position_id = $projectpersonid->project_position_id;
						$this->csv_staff[$i]->position_id         = $projectpersonid->position_id;
					}
				}
				$i++;
			}
			}
		}

		$app->setUserState($option . 'csv_staff', $this->csv_staff);
		$app->setUserState($option . 'csv_cards', $this->csv_cards);
		$app->setUserState($option . 'csv_in_out', $this->csv_in_out);
		$app->setUserState($option . 'csv_player', $this->csv_player);
		$app->setUserState($option . 'projectteamid', $projectteamid);

	}

	/**
	 * sportsmanagementModelMatch::getPersonId()
	 *
	 * @param   string  $firstname
	 * @param   string  $lastname
	 *
	 * @return void
	 */
	function getPersonId($firstname = '', $lastname = '')
	{
		// Reference global application object
		$app = Factory::getApplication();

		// Get a db connection.
		$db        = sportsmanagementHelper::getDBConnection();
		$person_id = 0;

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select('id');

		// From the table
		$query->from('#__sportsmanagement_person');
		$query->where('firstname LIKE ' . $db->Quote('' . trim($firstname) . ''));
		$query->where('lastname LIKE ' . $db->Quote('' . trim($lastname) . ''));
		$db->setQuery($query);

		try
		{
			$person_id = $db->loadResult();
		}
		catch (Exception $e)
		{
			$msg  = $e->getMessage(); // Returns "Normally you would have other code...
			$code = $e->getCode(); // Returns '500';
		}

		return $person_id;

	}

	function getSeasonPersonAssignment($person_id = 0, $season_id = 0, $person_type = 0, $position_id = 0)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__sportsmanagement_season_person_id');
		$query->where('person_id = ' . $person_id);
		$query->where('season_id = ' . $season_id);
		$query->where('persontype = ' . $person_type);

		if ($position_id > 0)
		{
			$query->where('position_id = ' . $position_id);
		}

		$db->setQuery($query);

		return $db->loadObject();
	}

	function getProjectReferee($person_id = 0, $project_id = 0)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__sportsmanagement_project_referee');
		$query->where('project_id = ' . $project_id);
		$query->where('person_id = ' . $person_id);

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * sportsmanagementModelMatch::getSeasonTeamPersonId()
	 *
	 * @param   integer  $person_id
	 * @param   integer  $favteam
	 * @param   mixed    $season_id
	 *
	 * @return void
	 */
	function getSeasonTeamPersonId($person_id = 0, $favteam = 0, $season_id)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// Get a db connection.
		$db = sportsmanagementHelper::getDBConnection();

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select('id,project_position_id');

		// From the table
		$query->from('#__sportsmanagement_season_team_person_id');
		$query->where('person_id = ' . $person_id);
		$query->where('team_id = ' . $favteam);
		$query->where('season_id = ' . $season_id);

		$db->setQuery($query);

		try
		{
			$projectpersonid = $db->loadObject();
		}
		catch (Exception $e)
		{
			$msg                                  = $e->getMessage(); // Returns "Normally you would have other code...
			$code                                 = $e->getCode(); // Returns '500';
			$projectpersonid                      = new stdClass;
			$projectpersonid->id                  = 0;
			$projectpersonid->project_position_id = 0;
		}

		if (!$projectpersonid)
		{
			$projectpersonid                      = new stdClass;
			$projectpersonid->id                  = 0;
			$projectpersonid->project_position_id = 0;
		}

		$query->clear('');
		$query->select('position_id');
		$query->from('#__sportsmanagement_project_position');
		$query->where('id = ' . $projectpersonid->project_position_id);
		$db->setQuery($query);
		$projectpersonid->position_id = $db->loadResult();

		return $projectpersonid;

	}

	/**
	 * sportsmanagementModelMatch::getEventType()
	 *
	 * @param string $event_type_name
	 * @return
	 */
	function getEventType($event_type_name = '')
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__sportsmanagement_eventtype');
		$query->where('name = ' . $db->Quote($event_type_name));

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * sportsmanagementModelMatch::savePressebericht()
	 *
	 * @return void
	 */
	function savePressebericht($post = null)
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		$match_id        = $app->input->getVar('match_id');
		$project_id      = $app->getUserState("$option.pid", '0');
		$season_id       = $post['season_id'];
		$fav_team        = $post['fav_team'];
		$project_team_id = $post['projectteamid'];

		$csv_referee_person_id           = $post['refereepersonid'];
		$csv_referee_firstname           = $post['refereefirstname'];
		$csv_referee_lastname            = $post['refereelastname'];
		$csv_referee_project_position_id = $post['referee_project_position_id'];
		$csv_referee_season_person_id    = $post['season_person_id'];
		$csv_referee_project_referee_id  = $post['refereeprojectrefereeid'];

		$csv_player_number                    = $post['player'];
        $csv_player_csvname                   = $post['csvname'];
		$csv_player_firstname                 = $post['playerfirstname'];
		$csv_player_lastname                  = $post['playerlastname'];
		$csv_player_person_id                 = $post['playerpersonid'];
		$csv_player_project_person_id         = $post['playerprojectpersonid'];
		$csv_player_project_position_id       = $post['player_project_position_id'];
		$csv_player_startaufstellung          = $post['startaufstellung'];
		$csv_player_inout_project_position_id = $post['player_inout_project_position_id'];
		$csv_player_project_events_id         = $post['project_events_id'];
		$csv_player_hinweis                   = $post['playerhinweis'];

		$csv_staff_lastnames           = $post['stafflastname'];
		$csv_staff_firstnames          = $post['stafffirstname'];
		$csv_staff_project_position_id = $post['staff_project_position_id'];

		$this->csv_referee = $app->getUserState($option . 'csv_referee');
		$this->csv_staff   = $app->getUserState($option . 'csv_staff');
		$this->csv_cards   = $app->getUserState($option . 'csv_cards');
		$this->csv_in_out  = $app->getUserState($option . 'csv_in_out');
		$this->csv_player  = $app->getUserState($option . 'csv_player');

		// Schiedsrichter verarbeiten
		foreach ($csv_referee_lastname as $referee_key => $referee_lastname)
		{
			$ref_firstname           = $csv_referee_firstname[$referee_key];
			$ref_lastname            = $referee_lastname;
			$ref_person_id           = $csv_referee_person_id[$referee_key];
			$ref_project_position_id = $csv_referee_project_position_id[$referee_key];

			// Wir verarbeiten den Schiedsrichter nur, wenn der Benutzer eine Position ausgewählt hat
			if ($ref_project_position_id)
			{
				// Hat der Schiedsrichter noch keine Person-ID, muss diese Person angelegt werden
				if (!$ref_person_id)
				{
					$staff_position_id = $this->getProjectPosition($ref_project_position_id)->position_id;
					$ref_person_id     = $this->createPerson($ref_firstname, $ref_lastname, $staff_position_id);
				}

				// Die nachfolgenden Schritte nur ausführen, wenn eine Person existiert
				if ($ref_person_id)
				{
					// Zuordnung zur Saison
					$ref_season_person_id = $this->createSeasonPersonAssignment($ref_person_id, $season_id, 3, $ref_project_position_id);

					// Zuordnung zum Projekt
					$ref_project_id = $csv_referee_project_referee_id[$referee_key];

					if (!$ref_project_id)
					{
						$ref_project_id = $this->createProjectReferee($project_id, $ref_season_person_id, $ref_project_position_id);
					}

					// Zuordnung zum Spiel
					$ref_match_id = $this->createMatchReferee($match_id, $ref_project_id, $ref_project_position_id);
				}
			}
		}

		// Spieler verarbeiten
		foreach ($csv_player_csvname as $player_key => $player_csvname)
		{
			$player_firstname           = $csv_player_firstname[$player_key];
            $player_lastname            = $csv_player_lastname[$player_key];
			$player_person_id           = $csv_player_person_id[$player_key];
			$player_project_position_id = $csv_player_project_position_id[$player_key];
			$player_startaufstellung    = $csv_player_startaufstellung[$player_key];
			$player_jerseynumber        = $csv_player_number[$player_key];
			$player_hinweis             = $csv_player_hinweis[$player_key];
			$player_captain             = ($player_hinweis == 'C') ? 1 : 0;

			// Wir verarbeiten den Spieler nur, wenn der Benutzer eine Position ausgewählt hat
			if ($player_project_position_id)
			{
				// Hat der Spieler noch keine Person-ID, muss diese Person angelegt werden
				$player_position_id = $this->getProjectPosition($player_project_position_id)->position_id;

				if (!$player_person_id)
				{
					$player_person_id = $this->createPerson($player_firstname, $player_lastname, $player_position_id);
				}

				// Die nachfolgenden Schritte nur ausführen, wenn eine Person existiert
				if ($player_person_id)
				{
					// Zuordnung zur Saison
					$player_season_person_id = $this->createSeasonPersonAssignment($player_person_id, $season_id, 1, $player_project_position_id);

					// Zuordnung Person -> Projekt Position
					$player_person_project_position_id = $this->createPersonProjektPositionAssignment($player_person_id, $project_id, 1, $player_project_position_id);

					// Zuordnung zur Season Team Person
					$player_season_team_person_id = $this->createSeasonTeamPersonAssignment($player_person_id, $season_id, $fav_team, 1, $player_project_position_id, $player_jerseynumber);

					// Zuordnung zum Spiel
					if ($player_startaufstellung)
					{
						// Der Spieler war in der Startelf
						$player_match_id = $this->createMatchPlayer($match_id, $player_season_team_person_id, $player_position_id, $player_jerseynumber, $player_captain);
					}
					else
					{
						// Der Spieler wurde eingewechselt, hier brauchen wir mehr Informationen
						$player_came_in     = 0;
						$player_in_for      = null;
						$player_in_out_time = null;

						foreach ($this->csv_in_out as $player_inout_key => $player_inout_object)
						{
							// Überprüfen ob der eingewechselte Spieler zum aktuellen Spieler passt (Nachname + Nummer muss stimmen)
							if ($player_inout_object->spieler == $player_csvname && $player_inout_object->in == $player_jerseynumber)
							{
								// Hat der Benutzer eine Position beim Import ausgewählt? Wenn nicht wird die selbe Position verwendet, wie der ausgewechselte Spieler
								if ($csv_player_inout_project_position_id[$player_inout_object->in])
								{
									$player_position_id = $csv_player_inout_project_position_id[$player_inout_object->in];
								}

								// Die ID des ausgewechselten Spielers suchen (Nachname + Nummer muss stimmen)
								foreach ($csv_player_csvname as $player_out_key => $player_out_object)
								{
									if ($csv_player_number[$player_out_key] == $player_inout_object->out && $player_out_object == $player_inout_object->spielerout)
									{
										$player_in_for = $csv_player_project_person_id[$player_out_key];

										// Finden wir keinen Spieler kann es sein, dass dieser erst mit diesem Pressebericht angelegt wurde.
										// Deswegen müssen wir ihn hier nun von der DB laden, falls es ihn gibt
										if (!$player_in_for)
										{
											$player_out_lastname           = $player_out_object;
											$player_out_firstname          = $csv_player_firstname[$player_out_key];
											$player_out_person_id          = $this->getPersonId($player_out_firstname, $player_out_lastname);
											$player_out_season_team_person = $this->getSeasonTeamPersonAssignment($player_out_person_id, $season_id, $fav_team, 1);
											$player_in_for                 = $player_out_season_team_person->id;
										}
									}
								}

								// Wir notieren uns die Auswechselzeit und dass der Spieler eingewechselt wurde
								$player_came_in     = $player_inout_object->came_in;
								$player_in_out_time = $player_inout_object->in_out_time;
							}
						}

						if ($player_came_in && $player_in_for)
						{
							$player_match_id = $this->createMatchPlayer($match_id, $player_season_team_person_id, $player_position_id, $player_jerseynumber, $player_captain, $player_came_in, $player_in_for, $player_in_out_time);
						}
					}

					// Events für den Spieler verarbeiten
					foreach ($this->csv_cards as $event_key => $event_object)
					{
						// Überprüfen ob das Event zum aktuellen Spieler passt (Nachname + Nummer muss stimmen)
						if ($event_object->spieler == $csv_player_csvname && $event_object->spielernummer == $player_jerseynumber)
						{
							$player_event_time   = $event_object->event_time;
							$player_event_type   = $csv_player_project_events_id[$event_key];
							$player_event_notice = $event_object->notice;

							// Hat der User ein ProjectEvent ausgewählt? Wenn nicht können wir den Datensatz nicht verarbeiten
							if ($player_event_type)
							{
								// Zuordnung des Events
								$player_event_id = $this->createMatchEvent($match_id, $project_team_id, $player_season_team_person_id, $player_event_time, $player_event_type, $player_event_notice);
							}
						}
					}
				}
			}
		}

		// Jetzt werden die Staffs eingearbeitet
		foreach ($csv_staff_lastnames as $staff_key => $staff_lastname)
		{
			// Zu Beginn holen wir uns das Staff-Objekt (Vor- und Nachname muss gleich sein)
			$staff_firstname           = $csv_staff_firstnames[$staff_key];
			$staff_person_id           = 0;
			$staff_position_id         = 0;
			$staff_project_position_id = $csv_staff_project_position_id[$staff_key];

			foreach ($this->csv_staff as $csv_staff_key => $csv_staff_value)
			{
				if ($csv_staff_value->lastname == $staff_lastname && $csv_staff_value->firstname == $staff_firstname)
				{
					$staff_person_id = $csv_staff_value->person_id;
				}
			}

			// Wir verarbeiten den Staff nur, wenn der Benutzer eine Position ausgewählt hat
			if ($staff_project_position_id)
			{
				// Dann schauen wir, ob der Staff als Person schon angelegt ist und legen diese Person ggf. an
				$staff_position_id = $this->getProjectPosition($staff_project_position_id)->position_id;

				if ($staff_person_id == 0)
				{
					$staff_person_id = $this->createPerson($staff_firstname, $staff_lastname, $staff_position_id);
				}

				// Die nachfolgenden Schritte nur ausführen, wenn eine Person existiert
				if ($staff_person_id)
				{
					// Zuordnung zur Saison
					$staff_season_person_id = $this->createSeasonPersonAssignment($staff_person_id, $season_id, 1, $staff_project_position_id);

					// Zuordnung Person -> Projekt Position
					$staff_person_project_position_id = $this->createPersonProjektPositionAssignment($staff_person_id, $project_id, 2, $staff_project_position_id);

					// Zuordnung zur Season Team Person
					$staff_season_team_person_id = $this->createSeasonTeamPersonAssignment($staff_person_id, $season_id, $fav_team, 2, $staff_project_position_id);

					// Zuordnung zum Spiel
					$staff_match_id = $this->createMatchStaff($match_id, $staff_season_team_person_id, $staff_position_id);
				}
			}
		}

		$my_text                                               = 'Pressebericht imported successfully ';
		$this->_success_text['Importing general Person data:'] = $my_text;
	}

	/**
	 * sportsmanagementModelMatch::getProjectPosition()
	 *
	 * @param integer $project_position_id
	 * @return
	 */
	function getProjectPosition($project_position_id = 0)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__sportsmanagement_project_position');
		$query->where('id = ' . $project_position_id);

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * sportsmanagementModelMatch::createPerson()
	 *
	 * @param string $firstname
	 * @param string $lastname
	 * @param integer $position_id
	 * @return
	 */
	function createPerson($firstname = '', $lastname = '', $position_id = 0)
	{
		$app = Factory::getApplication();
		$db  = Factory::getDbo();

		$temp              = new stdClass;
		$temp->firstname   = $firstname;
		$temp->lastname    = $lastname;
		$temp->alias       = OutputFilter::stringURLSafe($temp->firstname . ' ' . $temp->lastname);
		$temp->position_id = $position_id;
		$temp->notes       = ' ';
		$temp->email       = ' ';
		$temp->website     = ' ';
		$temp->published   = 1;

		$temp->injury_date   = -1;
		$temp->injury_end    = -1;
		$temp->injury_detail = ' ';

		$temp->suspension_date   = -1;
		$temp->suspension_end    = -1;
		$temp->suspension_detail = ' ';

		$temp->away_date   = -1;
		$temp->away_end    = -1;
		$temp->away_detail = ' ';

		try
		{
			$result = $db->insertObject('#__sportsmanagement_person', $temp);

			return $db->insertid();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getMessage()), 'Error');
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getCode()), 'Error');
		}

		return null;
	}

	/**
	 * sportsmanagementModelMatch::createSeasonPersonAssignment()
	 *
	 * @param integer $person_id
	 * @param integer $season_id
	 * @param integer $person_type
	 * @param integer $position_id
	 * @return
	 */
	function createSeasonPersonAssignment($person_id = 0, $season_id = 0, $person_type = 0, $position_id = 0)
	{
		// Zu Beginn überprüfen, ob es diesen Datensatz bereits gibt, falls ja geben wir die ID davon zurück
		$existing = $this->getSeasonPersonAssignment($person_id, $season_id, $person_type, $position_id);

		if ($existing)
		{
			return $existing->id;
		}

		$app  = Factory::getApplication();
		$db   = Factory::getDbo();
		$date = Factory::getDate();
		$user = Factory::getUser();

		$insertquery = $db->getQuery(true);
		$columns     = array('person_id', 'season_id', 'persontype', 'position_id', 'modified', 'modified_by');
		$values      = array($person_id, $season_id, $person_type, $position_id, $db->Quote($date->toSql()), $user->id);
		$insertquery
			->insert($db->quoteName('#__sportsmanagement_season_person_id'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		try
		{
			$db->setQuery($insertquery);
			$db->execute();

			return $db->insertid();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getMessage()), 'Error');
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getCode()), 'Error');
		}

		return null;
	}

	/**
	 * sportsmanagementModelMatch::createProjectReferee()
	 *
	 * @param integer $project_id
	 * @param integer $season_person_id
	 * @param integer $position_id
	 * @return
	 */
	function createProjectReferee($project_id = 0, $season_person_id = 0, $position_id = 0)
	{
		$app  = Factory::getApplication();
		$db   = Factory::getDbo();
		$date = Factory::getDate();
		$user = Factory::getUser();

		$insertquery = $db->getQuery(true);
		$columns     = array('project_id', 'person_id', 'project_position_id', 'modified', 'modified_by');
		$values      = array($project_id, $season_person_id, $position_id, $db->Quote($date->toSql()), $user->id);
		$insertquery
			->insert($db->quoteName('#__sportsmanagement_project_referee'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		try
		{
			$db->setQuery($insertquery);
			$db->execute();

			return $db->insertid();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getMessage()), 'Error');
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getCode()), 'Error');
		}

		return null;
	}

	/**
	 * sportsmanagementModelMatch::createMatchReferee()
	 *
	 * @param integer $match_id
	 * @param integer $project_referee_id
	 * @param integer $position_id
	 * @return
	 */
	function createMatchReferee($match_id = 0, $project_referee_id = 0, $position_id = 0)
	{
		$existing = $this->getMatchReferee($match_id, $project_referee_id, $position_id);

		if ($existing)
		{
			return $existing->id;
		}

		$app  = Factory::getApplication();
		$db   = Factory::getDbo();
		$date = Factory::getDate();
		$user = Factory::getUser();

		$insertquery = $db->getQuery(true);
		$columns     = array('match_id', 'project_referee_id', 'project_position_id', 'modified', 'modified_by');
		$values      = array($match_id, $project_referee_id, $position_id, $db->Quote($date->toSql()), $user->id);
		$insertquery
			->insert($db->quoteName('#__sportsmanagement_match_referee'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		try
		{
			$db->setQuery($insertquery);
			$db->execute();

			return $db->insertid();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getMessage()), 'Error');
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getCode()), 'Error');
		}

		return null;
	}

	/**
	 * sportsmanagementModelMatch::getMatchReferee()
	 *
	 * @param integer $match_id
	 * @param integer $project_referee_id
	 * @param integer $position_id
	 * @return
	 */
	function getMatchReferee($match_id = 0, $project_referee_id = 0, $position_id = 0)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__sportsmanagement_match_referee');
		$query->where('match_id = ' . $match_id);
		$query->where('project_referee_id = ' . $project_referee_id);
		$query->where('project_position_id = ' . $position_id);

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * sportsmanagementModelMatch::createPersonProjektPositionAssignment()
	 *
	 * @param integer $person_id
	 * @param integer $project_id
	 * @param integer $person_type
	 * @param integer $project_position_id
	 * @return
	 */
	function createPersonProjektPositionAssignment($person_id = 0, $project_id = 0, $person_type = 0, $project_position_id = 0)
	{
		$existing = $this->getPersonProjektPositionAssignment($person_id, $project_id, $person_type, $project_position_id);

		if ($existing)
		{
			return $existing->id;
		}

		$app  = Factory::getApplication();
		$db   = Factory::getDbo();
		$date = Factory::getDate();
		$user = Factory::getUser();

		$insertquery = $db->getQuery(true);
		$columns     = array('person_id', 'project_id', 'persontype', 'project_position_id', 'modified', 'modified_by');
		$values      = array($person_id, $project_id, $person_type, $project_position_id, $db->Quote($date->toSql()), $user->id);
		$insertquery
			->insert($db->quoteName('#__sportsmanagement_person_project_position'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		try
		{
			$db->setQuery($insertquery);
			$db->execute();

			return $db->insertid();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getMessage()), 'Error');
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getCode()), 'Error');
		}
	}

	/**
	 * sportsmanagementModelMatch::getPersonProjektPositionAssignment()
	 *
	 * @param integer $person_id
	 * @param integer $project_id
	 * @param integer $person_type
	 * @param integer $project_position_id
	 * @return
	 */
	function getPersonProjektPositionAssignment($person_id = 0, $project_id = 0, $person_type = 0, $project_position_id = 0)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__sportsmanagement_person_project_position');
		$query->where('person_id = ' . $person_id);
		$query->where('project_id = ' . $project_id);
		$query->where('persontype = ' . $person_type);
		$query->where('project_position_id = ' . $project_position_id);

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * sportsmanagementModelMatch::createSeasonTeamPersonAssignment()
	 *
	 * @param integer $person_id
	 * @param integer $season_id
	 * @param integer $team_id
	 * @param integer $person_type
	 * @param integer $project_position_id
	 * @param integer $jerseynumber
	 * @return
	 */
	function createSeasonTeamPersonAssignment($person_id = 0, $season_id = 0, $team_id = 0, $person_type = 0, $project_position_id = 0, $jerseynumber = 0)
	{
		$existing = $this->getSeasonTeamPersonAssignment($person_id, $season_id, $team_id, $person_type, $project_position_id);

		if ($existing)
		{
			return $existing->id;
		}

		$app  = Factory::getApplication();
		$db   = Factory::getDbo();
		$date = Factory::getDate();
		$user = Factory::getUser();

		$insertquery = $db->getQuery(true);
		$columns     = array('person_id', 'season_id', 'team_id', 'persontype', 'project_position_id', 'jerseynumber', 'published', 'modified', 'modified_by');
		$values      = array($person_id, $season_id, $team_id, $person_type, $project_position_id, $jerseynumber, 1, $db->Quote($date->toSql()), $user->id);
		$insertquery
			->insert($db->quoteName('#__sportsmanagement_season_team_person_id'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		try
		{
			$db->setQuery($insertquery);
			$db->execute();

			return $db->insertid();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getMessage()), 'Error');
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getCode()), 'Error');
		}
	}

	/**
	 * sportsmanagementModelMatch::getSeasonTeamPersonAssignment()
	 *
	 * @param integer $person_id
	 * @param integer $season_id
	 * @param integer $team_id
	 * @param integer $person_type
	 * @param integer $project_position_id
	 * @return
	 */
	function getSeasonTeamPersonAssignment($person_id = 0, $season_id = 0, $team_id = 0, $person_type = 0, $project_position_id = 0)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__sportsmanagement_season_team_person_id');
		$query->where('person_id = ' . $person_id);
		$query->where('season_id = ' . $season_id);
		$query->where('team_id = ' . $team_id);
		$query->where('persontype = ' . $person_type);

		if ($project_position_id > 0)
		{
			$query->where('project_position_id = ' . $project_position_id);
		}

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * sportsmanagementModelMatch::createMatchPlayer()
	 *
	 * @param integer $match_id
	 * @param integer $season_team_person_id
	 * @param integer $project_position_id
	 * @param integer $jerseynumber
	 * @param integer $captain
	 * @param integer $came_in
	 * @param integer $in_for
	 * @param integer $in_out_time
	 * @return
	 */
	function createMatchPlayer($match_id = 0, $season_team_person_id = 0, $project_position_id = 0, $jerseynumber = 0, $captain = 0, $came_in = 0, $in_for = 0, $in_out_time = 0)
	{
		$existing = $this->getMatchPlayer($match_id, $season_team_person_id, $project_position_id);

		if ($existing)
		{
			return $existing->id;
		}

		$app  = Factory::getApplication();
		$db   = Factory::getDbo();
		$date = Factory::getDate();
		$user = Factory::getUser();

		$insertquery = $db->getQuery(true);
		$columns     = array('match_id', 'teamplayer_id', 'project_position_id', 'trikot_number', 'came_in', 'in_for', 'in_out_time', 'captain', 'modified', 'modified_by');
		$values      = array($match_id, $season_team_person_id, $project_position_id, $jerseynumber, $came_in, $in_for, $in_out_time, $captain, $db->Quote($date->toSql()), $user->id);
		$insertquery
			->insert($db->quoteName('#__sportsmanagement_match_player'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		try
		{
			$db->setQuery($insertquery);
			$db->execute();

			return $db->insertid();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getMessage()), 'Error');
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getCode()), 'Error');
		}
	}

	/**
	 * sportsmanagementModelMatch::getMatchPlayer()
	 *
	 * @param integer $match_id
	 * @param integer $season_team_person_id
	 * @param integer $project_position_id
	 * @return
	 */
	function getMatchPlayer($match_id = 0, $season_team_person_id = 0, $project_position_id = 0)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__sportsmanagement_match_player');
		$query->where('match_id = ' . $match_id);
		$query->where('teamplayer_id = ' . $season_team_person_id);
		$query->where('project_position_id = ' . $project_position_id);

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * sportsmanagementModelMatch::createMatchEvent()
	 *
	 * @param integer $match_id
	 * @param integer $project_team_id
	 * @param integer $season_team_person_id
	 * @param integer $event_time
	 * @param integer $event_type
	 * @param string $notice
	 * @return
	 */
	function createMatchEvent($match_id = 0, $project_team_id = 0, $season_team_person_id = 0, $event_time = 0, $event_type = 0, $notice = '')
	{
		$existing = $this->getMatchEvent($match_id, $project_team_id, $season_team_person_id, $event_time, $event_type);

		if ($existing)
		{
			return $existing->id;
		}

		$app  = Factory::getApplication();
		$db   = Factory::getDbo();
		$date = Factory::getDate();
		$user = Factory::getUser();

		$insertquery = $db->getQuery(true);
		$columns     = array('match_id', 'projectteam_id', 'teamplayer_id', 'event_time', 'event_type_id', 'event_sum', 'notice', 'modified', 'modified_by');
		$values      = array($match_id, $project_team_id, $season_team_person_id, $event_time, $event_type, 1, $db->Quote($notice), $db->Quote($date->toSql()), $user->id);
		$insertquery
			->insert($db->quoteName('#__sportsmanagement_match_event'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		try
		{
			$db->setQuery($insertquery);
			$db->execute();

			return $db->insertid();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getMessage()), 'Error');
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getCode()), 'Error');
		}
	}

	/**
	 * sportsmanagementModelMatch::getMatchEvent()
	 *
	 * @param integer $match_id
	 * @param integer $project_team_id
	 * @param integer $season_team_person_id
	 * @param integer $event_time
	 * @param integer $event_type
	 * @return
	 */
	function getMatchEvent($match_id = 0, $project_team_id = 0, $season_team_person_id = 0, $event_time = 0, $event_type = 0)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__sportsmanagement_match_event');
		$query->where('match_id = ' . $match_id);
		$query->where('projectteam_id = ' . $project_team_id);
		$query->where('teamplayer_id = ' . $season_team_person_id);
		$query->where('event_time = ' . $event_time);
		$query->where('event_type_id = ' . $event_type);

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * sportsmanagementModelMatch::createMatchStaff()
	 *
	 * @param integer $match_id
	 * @param integer $team_staff_id
	 * @param integer $project_position_id
	 * @return
	 */
	function createMatchStaff($match_id = 0, $team_staff_id = 0, $project_position_id = 0)
	{
		$existing = $this->getMatchStaff($match_id, $team_staff_id, $project_position_id);

		if ($existing)
		{
			return $existing->id;
		}

		$app  = Factory::getApplication();
		$db   = Factory::getDbo();
		$date = Factory::getDate();
		$user = Factory::getUser();

		$insertquery = $db->getQuery(true);
		$columns     = array('match_id', 'team_staff_id', 'project_position_id', 'modified', 'modified_by');
		$values      = array($match_id, $team_staff_id, $project_position_id, $db->Quote($date->toSql()), $user->id);
		$insertquery
			->insert($db->quoteName('#__sportsmanagement_match_staff'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		try
		{
			$db->setQuery($insertquery);
			$db->execute();

			return $db->insertid();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getMessage()), 'Error');
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getCode()), 'Error');
		}
	}

	/**
	 * sportsmanagementModelMatch::getMatchStaff()
	 *
	 * @param integer $match_id
	 * @param integer $team_staff_id
	 * @param integer $project_position_id
	 * @return
	 */
	function getMatchStaff($match_id = 0, $team_staff_id = 0, $project_position_id = 0)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__sportsmanagement_match_staff');
		$query->where('match_id = ' . $match_id);
		$query->where('team_staff_id = ' . $team_staff_id);
		$query->where('project_position_id = ' . $project_position_id);

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return boolean
	 * @since  1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		return Factory::getUser()->authorise('core.edit', 'com_sportsmanagement.message.' . ((int) isset($data[$key]) ? $data[$key] : 0)) || parent::allowEdit($data, $key);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return mixed    The data for the form.
	 * @since  1.6
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.match.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}
}

