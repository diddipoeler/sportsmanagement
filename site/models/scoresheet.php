<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage scoresheet
 * @file       ical.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;


/**
 * sportsmanagementModelScoresheet
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelScoresheet extends BaseDatabaseModel
{
	static $cfg_which_database = 0;

	static $matchid = 0;

	static $projectid = 0;

	/**
	 * sportsmanagementModelical::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		// Reference global application object
		$app    = Factory::getApplication();
		$jinput = $app->input;
		parent::__construct();

		self::$matchid                           = (int) $jinput->get('mid', 0, '');
		self::$cfg_which_database                = $jinput->request->get('cfg_which_database', 0, 'INT');
		self::$projectid                         = $jinput->request->get('p', 0, 'INT');
		sportsmanagementModelProject::$projectid = self::$projectid;
	}

	function getMatch($matchid = 0, $cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Get a db connection.
		$db     = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query  = $db->getQuery(true);
		$result = array();

		$query->select('m.match_number as match_number, m.match_date as match_date, m.projectteam1_id as projectteam1_id, m.projectteam2_id as projectteam2_id');
		$query->select('x.game_parts as game_parts, x.season_id as season_id, s1.team_id as team1_id, t1.name as team1_name, s2.team_id as team2_id, t2.name as team2_name');
		$query->select('j.name as projectname, j.timezone as timezone, g.name as playgroundname');

		// From
		$query->from('#__sportsmanagement_match AS m');

		// Join
		$query->join('INNER', '#__sportsmanagement_project_team AS p1 ON m.projectteam1_id=p1.id');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS s1 ON p1.team_id=s1.id');
		$query->join('INNER', '#__sportsmanagement_team AS t1 ON s1.team_id=t1.id');
		$query->join('INNER', '#__sportsmanagement_project AS x ON p1.project_id=x.id');
		$query->join('INNER', '#__sportsmanagement_project_team AS p2 ON m.projectteam2_id=p2.id');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS s2 ON p2.team_id=s2.id');
		$query->join('INNER', '#__sportsmanagement_team AS t2 ON s2.team_id=t2.id');
		$query->join('INNER', '#__sportsmanagement_project AS j ON p1.project_id=j.id');
		$query->join('LEFT', '#__sportsmanagement_playground AS g ON m.playground_id=g.id');
		$project = sportsmanagementModelProject::getProject(self::$projectid);

		if ($project->teams_as_referees)
		{
			$query->select('u.name as referee');
			$query->join('LEFT', '#__sportsmanagement_match_referee AS r ON m.id=r.match_id');
			$query->join('LEFT', '#__sportsmanagement_project_team AS spi ON r.project_referee_id=spi.id');
			$query->join('LEFT', '#__sportsmanagement_season_team_id AS st1 ON st1.id = spi.team_id');
			$query->join('LEFT', '#__sportsmanagement_team AS u ON st1.team_id=u.id AND u.published = 1');
		}
		else
		{
			$query->select('u.lastname as referee');
			$query->join('LEFT', '#__sportsmanagement_match_referee AS r ON m.id=r.match_id');
			$query->join('LEFT', '#__sportsmanagement_project_referee AS s ON r.project_referee_id=s.id');
			$query->join('LEFT', '#__sportsmanagement_person AS u ON s.person_id=u.id');
		}

		// Where
		$query->where('m.id = ' . $matchid);

		try
		{
			$db->setQuery($query);
			$result = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$result = false;
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}

	function getTeamPlayer($teamid = 0, $seasonid = 0, $cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Get a db connection.
		$db     = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query  = $db->getQuery(true);
		$result = array();

		$query->select('b.firstname, b.lastname, b.knvbnr');

		// From
		$query->from('#__sportsmanagement_season_team_person_id AS a');

		// Join
		$query->join('INNER', '#__sportsmanagement_person AS b ON a.person_id=b.id');

		$query->order('b.lastname');

		// Where
		$query->where('team_id = ' . $teamid . ' AND season_id = ' . $seasonid);

		try
		{
			$db->setQuery($query);
			$result = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$result = false;
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}

}
