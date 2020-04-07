<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       person.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage player
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Utility\Utility;

/**
 * sportsmanagementModelPerson
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelPerson extends BaseDatabaseModel
{
	static $projectid = 0;

	static $personid = 0;

	var $teamplayerid = 0;

	static $person = null;

	var $teamplayer    = null;

	/**
	 * store person info specific to the project
	 *
	 * @var object
	 */
	static $_inproject   = null;

	/**
	 * data array for player history
	 *
	 * @var array
	 */
	var $_playerhistory = null;

	static $cfg_which_database = 0;



	/**
	 * sportsmanagementModelPerson::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		 $option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();

		// JInput object
		 $jinput = $app->input;
		parent::__construct();
		self::$projectid = (int) $jinput->get('p', 0);
		self::$personid    = (int) $jinput->get('pid', 0);
		$this->teamplayerid    = (int) $jinput->get('pt', 0);
		self::$cfg_which_database = (int) $jinput->get('cfg_which_database', 0);

	}



	/**
	 * sportsmanagementModelPerson::updateHits()
	 *
	 * @param   integer $personid
	 * @param   integer $inserthits
	 * @return void
	 */
	public static function updateHits($personid=0,$inserthits=0)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		if ($inserthits)
		{
			 $query->update($db->quoteName('#__sportsmanagement_person'))->set('hits = hits + 1')->where('id = ' . $personid);

			$db->setQuery($query);

			$result = $db->execute();
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		}

	}

	/**
	 * sportsmanagementModelPerson::getPerson()
	 *
	 * @param   integer $personid
	 * @param   integer $cfg_which_database
	 * @param   integer $inserthits
	 * @return
	 */
	public static function getPerson($personid = 0, $cfg_which_database = 0,$inserthits=0)
	{
		$app = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		if ($personid)
		{
			self::$personid    = $personid;
		}
		else
		{
			if (!self::$personid)
			{
				self::$personid    = Factory::getApplication()->input->getInt('pid', 0);
			}
		}

		$starttime = microtime();

			  self::updateHits(self::$personid, $inserthits);

			  $db = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);
		$query->select('p.*');
		  $query->select('CONCAT_WS( \':\', p.id, p.alias ) AS slug ');
		  $query->from('#__sportsmanagement_person AS p ');
		  $query->where('p.id = ' . $db->Quote(self::$personid));
		$db->setQuery($query);

					  self::$person = $db->loadObject();
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return self::$person;
	}


	/**
	 * sportsmanagementModelPerson::getReferee()
	 *
	 * @return
	 */
	public static function getReferee()
	{
		  $option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

			  // If ( is_null( $this->_inproject ) )
		// {
		$query->select('p.*,CONCAT_WS(\':\',p.id,p.alias) AS slug');
		$query->select('pr.id,pr.notes AS prnotes,pr.picture');
		$query->select('pos.name AS position_name');

			  $query->from('#__sportsmanagement_project_referee AS pr ');

			  $query->join('INNER', '#__sportsmanagement_season_person_id AS o ON o.id = pr.person_id');
		$query->join('INNER', '#__sportsmanagement_person AS p ON p.id = o.person_id');

			  $query->join('LEFT', '#__sportsmanagement_project_position AS ppos ON ppos.id = pr.project_position_id');
		$query->join('LEFT', '#__sportsmanagement_position AS pos ON pos.id = ppos.position_id');

			  $query->where('pr.project_id = ' . self::$projectid);
		$query->where('p.published = 1 ');
		$query->where('o.person_id = ' . self::$personid);
		$db->setQuery($query);
		self::$_inproject = $db->loadObject();

			  $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return self::$_inproject;
	}


	/**
	 * get person history across all projects, with team, season, position,... info
	 *
	 * @param   int    $person_id , linked to player_id from Person object
	 * @param   int    $order     ordering for season and league, default is ASC ordering
	 * @param   string $filter    e.g. "s.name = 2007/2008", default empty string
	 * @return array of objects
	 */
	function getRefereeHistory($order = 'ASC')
	{
		$personid = $this->personid;

		$query = ' SELECT	p.id AS person_id, '
		  . ' tt.project_id, '
		  . ' p.firstname AS fname, '
		  . ' p.lastname AS lname, '
		  . ' pj.name AS pname, '
		  . ' s.name AS sname, '
		  . ' pos.name AS position, '
		  . ' COUNT(mr.id) AS matchesCount '
		  . ' FROM #__sportsmanagement_match_referee AS mr '
		  . ' INNER JOIN #__sportsmanagement_match AS m ON m.id = mr.match_id '
		  . ' INNER JOIN #__sportsmanagement_person AS p ON p.id = mr.project_referee_id '
		  . ' INNER JOIN #__sportsmanagement_project_team AS tt ON tt.id = m.projectteam1_id '
		  . ' INNER JOIN #__sportsmanagement_project AS pj ON pj.id = tt.project_id '
		  . ' INNER JOIN #__sportsmanagement_season AS s ON s.id = pj.season_id '
		  . ' INNER JOIN #__sportsmanagement_league AS l ON l.id = pj.league_id '
		  . ' LEFT JOIN #__sportsmanagement_position AS pos ON pos.id = mr.project_position_id '
		  . ' WHERE p.id = ' . (int) $personid
		  . ' GROUP BY (tt.project_id) '
		  . ' ORDER BY s.ordering ASC, l.ordering ASC, pj.name ASC ';

		$this->_db->setQuery($query);
		$results = $this->_db->loadObjectList();

		return $results;
	}

	/**
	 * sportsmanagementModelPerson::getContactID()
	 *
	 * @param   mixed $catid
	 * @return
	 */
	function getContactID( $catid )
	{
		$app = Factory::getApplication();
		  $option = Factory::getApplication()->input->getCmd('option');

		// Create a new query object.
		  $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		  $query = $db->getQuery(true);

			  $person = self::getPerson();

		$query->select('id');
		$query->from('#__contact_details');
		$query->where('user_id = ' . $person->jl_user_id);
		$query->where('catid = ' . $catid);

			  $db->setQuery($query);
		$contact_id = $db->loadResult();
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $contact_id;
	}


	/**
	 * get all positions the player was assigned too in different projects
	 *
	 * @return unknown_type
	 */
	function getAllEvents()
	{
		  $app = Factory::getApplication();
		  $option = Factory::getApplication()->input->getCmd('option');

		// Create a new query object.
		  $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		  $query = $db->getQuery(true);

			 $history = sportsmanagementModelPlayer::getPlayerHistory();
		$positionhistory = array();

		foreach ($history as $h)
		{
			if (!in_array($h->position_id, $positionhistory))
			{
				$positionhistory[] = $h->position_id;
			}
		}

		if (!count($positionhistory))
		{
			return array();
		}

			  $query->select('et.*');
		  $query->from('#__sportsmanagement_eventtype AS et');
		  $query->join('INNER', '#__sportsmanagement_position_eventtype AS pet ON pet.eventtype_id = et.id');
		  $query->where('published = 1');
		  $query->where('pet.position_id IN (' . implode(',', $positionhistory) . ')');
		  $query->order('et.ordering');

						$db->setQuery($query);

						$info = $db->loadObjectList();
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $info;
	}

	/**
	 * get player events total, global or per project
	 *
	 * @param   int $eventid
	 * @param   int $projectid, all projects if null (default)
	 * @return array
	 */
	function getPlayerEvents($eventid, $projectid = null, $projectteamid = null)
	{
		  $app = Factory::getApplication();
		  $option = Factory::getApplication()->input->getCmd('option');

		// Create a new query object.
		  $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		  $query = $db->getQuery(true);

			   $query->select('SUM(me.event_sum) as total');
		  $query->from('#__sportsmanagement_match_event AS me');
		  $query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp1 ON tp1.id = me.teamplayer_id');

		  // $query->join('INNER','#__sportsmanagement_season_team_id AS st1 ON st1.team_id = tp1.team_id');
		  // $query->join('INNER','#__sportsmanagement_project_team AS pt ON st1.id = pt.team_id');

		$query->where('me.event_type_id = ' . (int) $eventid);
		$query->where('tp1.person_id = ' . (int) self::$personid);

		if ($projectteamid)
		{
			$query->where('me.projectteam_id = ' . (int) $projectteamid);
		}

							//				if ($projectid)
							//				{
							//                    $query->where('pt.project_id =' . (int) $projectid);
							//				}
							$query->group('tp1.person_id');

							$db->setQuery($query);
							$result = $db->loadResult();

		if (empty($result))
		{
					 $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

					 return 0;
		}

			  $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

				return $result;
	}



	/**
	 * sportsmanagementModelPerson::getPlayerChangedRecipients()
	 *
	 * @return
	 */
	function getPlayerChangedRecipients()
	{
		$app = Factory::getApplication();
		  $option = Factory::getApplication()->input->getCmd('option');

		// Create a new query object.
		 $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		  $query = $db->getQuery(true);

			   $query->select('email');
		  $query->from('#__users');
		  $query->where('usertype = \'Super Administrator\' OR usertype = \'Administrator\' ');

		$db->setQuery($query);

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			// Joomla! 3.0 code here
			$res = $db->loadColumn();
		}
		elseif (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			// Joomla! 2.5 code here
			$res = $db->loadResultArray();
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $res;
	}

	/**
	 * sportsmanagementModelPerson::sendMailTo()
	 *
	 * @param   mixed $listOfRecipients
	 * @param   mixed $subject
	 * @param   mixed $message
	 * @return void
	 */
	function sendMailTo($listOfRecipients, $subject, $message)
	{
		$app    = Factory::getApplication();
		$mailFrom = $app->getCfg('mailfrom');
		$fromName = $app->getCfg('fromname');
		Utility::sendMail($mailFrom, $fromName, $listOfRecipients, $subject, $message);
	}

	/**
	 * sportsmanagementModelPerson::getAllowed()
	 *
	 * @param   mixed $config_editOwnPlayer
	 * @return
	 */
	public static function getAllowed($config_editOwnPlayer)
	{
		  $app = Factory::getApplication();

			 $user = Factory::getUser();
		$allowed = false;

		if (self::_isAdmin($user) || self::_isOwnPlayer($user, $config_editOwnPlayer))
		{
			$allowed = true;
		}

			 return $allowed;
	}

	/**
	 * sportsmanagementModelPerson::_isAdmin()
	 *
	 * @param   mixed $user
	 * @return
	 */
	public static function _isAdmin($user)
	{

			   // Get a refrence of the page instance in joomla
		$document = Factory::getDocument();
		$app = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$allowed = false;

		if ($user->id > 0)
		{
			$project = sportsmanagementModelProject::getProject();

			// Check if user is project admin or editor
			if (sportsmanagementModelProject::isUserProjectAdminOrEditor($user->id, $project))
			{
				$allowed = true;
			}

						  // If not, then check if user has ACL rights
			if (!$allowed)
			{
				if (!$user->authorise('person.edit', $option))
				{
					$allowed = false;
				}
				else
				{
					$allowed = true;
				}
			}
		}

			 return $allowed;
	}

	/**
	 * sportsmanagementModelPerson::_isOwnPlayer()
	 *
	 * @param   mixed $user
	 * @param   mixed $config_editOwnPlayer
	 * @return
	 */
	public static function _isOwnPlayer($user,$config_editOwnPlayer)
	{
		  $app = Factory::getApplication();

		if ($user->id > 0)
		{
			$person = self::getPerson();

			return $config_editOwnPlayer && $user->id == $person->user_id;
		}

			return false;
	}

	/**
	 * sportsmanagementModelPerson::isEditAllowed()
	 *
	 * @param   mixed $config_editOwnPlayer
	 * @param   mixed $config_editAllowed
	 * @return
	 */
	function isEditAllowed($config_editOwnPlayer,$config_editAllowed)
	{
		  $app = Factory::getApplication();

			 $allowed = false;
		$user = Factory::getUser();

		if ($user->id > 0)
		{
			if (self::_isAdmin($user) || ($config_editAllowed && self::_isOwnPlayer($user, $config_editOwnPlayer)))
			{
				$allowed = true;
			}

			return $allowed;
		}

			  return false;
	}

	/**
	 * sportsmanagementModelPerson::_getProjectTeamIds4UserId()
	 *
	 * @param   mixed $userId
	 * @return
	 */
	function _getProjectTeamIds4UserId($userId)
	{
		  $app = Factory::getApplication();
		  $option = Factory::getApplication()->input->getCmd('option');

		// Create a new query object.
		  $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		  $query = $db->getQuery(true);

			   // Team_player
		  $query->select('tp.projectteam_id');
		  $query->from('#__sportsmanagement_person AS pr');
		  $query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = pr.id');
		  $query->where('pr.user_id = ' . $userId);
		  $query->where('pr.published = 1');
		  $query->where('tp.persontype = 1');

							  $db->setQuery($query);

		$projectTeamIds = array();

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			// Joomla! 3.0 code here
			$projectTeamIds = $db->loadColumn();
		}
		elseif (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			// Joomla! 2.5 code here
			$projectTeamIds = $db->loadResultArray();
		}

			  // Team_staff
			$query->select('tp.projectteam_id');
			 $query->from('#__sportsmanagement_person AS pr');
			 $query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = pr.id');
			 $query->where('pr.user_id = ' . $userId);
			 $query->where('pr.published = 1');
			 $query->where('tp.persontype = 2');

							  $db->setQuery($query);

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			 // Joomla! 3.0 code here
			 $res = $db->loadColumn();
		}
		elseif (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			// Joomla! 2.5 code here
			$res = $db->loadResultArray();
		}

				  $projectTeamIds = array_merge($projectTeamIds, $res);
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return $projectTeamIds;
	}

	/**
	 * sportsmanagementModelPerson::isContactDataVisible()
	 *
	 * @param   mixed $config_showContactDataOnlyTeamMembers
	 * @return
	 */
	public static function isContactDataVisible($config_showContactDataOnlyTeamMembers)
	{
		$user = Factory::getUser();
		$result = true;

		// Project admin and editor,see contact always
		if ($config_showContactDataOnlyTeamMembers && !sportsmanagementModelProject::isUserProjectAdminOrEditor($user->id, sportsmanagementModelProject::getProject()))
		{
			$result = false;

			if ($user->id > 0)
			{
				// Get project_team id to user-id from team-player or team-staff
				$projectTeamIds = self::_getProjectTeamIds4UserId($user->id);
				$teamplayer = sportsmanagementModelPlayer::getTeamPlayer();

				if (isset($teamplayer->projectteam_id))
				{
					$result = in_array($teamplayer->projectteam_id, $projectTeamIds);
				}
			}
		}

		return $result;
	}

}
