<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       ical.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage ical
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;


/**
 * sportsmanagementModelical
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelical extends BaseDatabaseModel
{
	static $projectid = 0;

	static $divisionid = 0;

	static $cfg_which_database = 0;

	static $teamid = 0;

	static $projectteamid = 0;

	var $team = null;

	var $club = null;


	/**
	 * sportsmanagementModelical::__construct()
	 *
	 * @return void
	 */
	function __construct( )
	{
		  // Reference global application object
		$app = Factory::getApplication();
		$jinput = $app->input;
		parent::__construct();

			  self::$teamid = (int) $jinput->get('tid', 0, '');
		self::$projectteamid = (int) $jinput->get('ptid', 0, '');

			  self::$projectid = $jinput->request->get('p', 0, 'INT');
		self::$divisionid = $jinput->request->get('division', 0, 'INT');
		self::$cfg_which_database = $jinput->request->get('cfg_which_database', 0, 'INT');
		sportsmanagementModelProject::$projectid = self::$projectid;

		// SportsmanagementModelResults::$projectid = self::$projectid;
		sportsmanagementModelNextMatch::$projectid = self::$projectid;

	}

	function getResultsPlan($projectid = 0, $teamid = 0, $divisionid = 0, $playgroundid = 0, $ordering = 'ASC',$cfg_which_database = 0)
	{
		$app = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Get a db connection.
		$db = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);
		$result = array();

			  $query->select('m.id,m.projectteam1_id, m.projectteam2_id, m.match_date,DATE_FORMAT(m.time_present,"%H:%i") time_present');
		$query->select('playground.id AS playground_id,playground.name AS playground_name,playground.short_name AS playground_short_name');
		$query->select('playground.address AS playground_address,playground.zipcode AS playground_zipcode,playground.city AS playground_city');
		$query->select('pt1.project_id');
		$query->select('d1.name as divhome');
		$query->select('d2.name as divaway');
		$query->select('CASE WHEN CHAR_LENGTH(t1.alias) AND CHAR_LENGTH(t2.alias) THEN CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) ELSE m.id END AS slug ');
		$query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
		$query->select('CONCAT_WS( \':\', r.id, r.alias ) AS round_slug');
		$query->select('CONCAT_WS( \':\', playground.id, playground.alias ) AS playground_slug');

		$query->select('t1.id AS team1, t2.id AS team2');
		$query->select('p.name AS project_name');

			  // From
		$query->from('#__sportsmanagement_match AS m');

		// Join
		$query->join('INNER', '#__sportsmanagement_round AS r ON m.round_id = r.id ');
		$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id ');
		$query->join('LEFT', '#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id');
		$query->join('LEFT', '#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id');
		$query->join('LEFT', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
		$query->join('LEFT', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
		$query->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
		$query->join('LEFT', '#__sportsmanagement_club AS c1 ON c1.id = t1.club_id');
		$query->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
		$query->join('LEFT', '#__sportsmanagement_club AS c2 ON c2.id = t2.club_id');
		$query->join('LEFT', '#__sportsmanagement_division AS d1 ON m.division_id = d1.id');
		$query->join('LEFT', '#__sportsmanagement_division AS d2 ON m.division_id = d2.id');
		$query->join('LEFT', '#__sportsmanagement_playground AS playground ON playground.id = m.playground_id');

			  // Where
		$query->where('m.published = 1');
		$query->where('r.project_id = ' . $projectid);

		if ($teamid)
		{
			$query->where('(t1.id = ' . $teamid . ' OR t2.id = ' . $teamid . ')');
		}

		// Order
		$query->order('m.match_date ASC,m.match_number');

		try
		{
			$db->setQuery($query);
			$result = $db->loadObjectList('id');
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$result = false;
		}

			// }

			  $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			  return $result;

	}


}
