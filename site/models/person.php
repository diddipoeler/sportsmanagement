<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage player
 * @file       person.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
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
	static $person = null;
	/**
	 * store person info specific to the project
	 *
	 * @var object
	 */
	static $_inproject = null;
	static $cfg_which_database = 0;
	var $teamplayerid = 0;
	var $teamplayer = null;
	/**
	 * data array for player history
	 *
	 * @var array
	 */
	var $_playerhistory = null;

	/**
	 * sportsmanagementModelPerson::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		$this->jsmapp    = Factory::getApplication('site');
		$this->jsmjinput = $this->jsmapp->input;
		$this->jsmoption = $this->jsmjinput->getCmd('option');
		$this->jsmview   = $this->jsmjinput->getCmd('view');

		parent::__construct();
		self::$projectid          = (int) $this->jsmjinput->get('p', 0);
		self::$personid           = (int) $this->jsmjinput->get('pid', 0);
		$this->teamplayerid       = (int) $this->jsmjinput->get('pt', 0);
		self::$cfg_which_database = (int) $this->jsmjinput->get('cfg_which_database', 0);
        $this->jsmdb     = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$this->jsmquery  = $this->jsmdb->getQuery(true);
	}

	/**
	 * sportsmanagementModelPerson::getReferee()
	 *
	 * @return
	 */
	public static function getReferee()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

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
	 * sportsmanagementModelPerson::getAllowed()
	 *
	 * @param   mixed  $config_editOwnPlayer
	 *
	 * @return
	 */
	public static function getAllowed($config_editOwnPlayer)
	{
		$app = Factory::getApplication();

		$user    = Factory::getUser();
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
	 * @param   mixed  $user
	 *
	 * @return
	 */
	public static function _isAdmin($user)
	{
		$document = Factory::getDocument();
		$app      = Factory::getApplication();
		$option   = Factory::getApplication()->input->getCmd('option');
		$allowed  = false;

		if ($user->id > 0)
		{
			$project = sportsmanagementModelProject::getProject();

			/** Check if user is project admin or editor */
			if (sportsmanagementModelProject::isUserProjectAdminOrEditor($user->id, $project))
			{
				$allowed = true;
			}

			/** If not, then check if user has ACL rights */
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
	 * @param   mixed  $user
	 * @param   mixed  $config_editOwnPlayer
	 *
	 * @return
	 */
	public static function _isOwnPlayer($user, $config_editOwnPlayer)
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
	 * sportsmanagementModelPerson::isContactDataVisible()
	 *
	 * @param   mixed  $config_showContactDataOnlyTeamMembers
	 *
	 * @return
	 */
	public static function isContactDataVisible($config_showContactDataOnlyTeamMembers)
	{
		$user   = Factory::getUser();
		$result = true;

		/** Project admin and editor,see contact always */
		if ($config_showContactDataOnlyTeamMembers && !sportsmanagementModelProject::isUserProjectAdminOrEditor($user->id, sportsmanagementModelProject::getProject()))
		{
			$result = false;

			if ($user->id > 0)
			{
				/** Get project_team id to user-id from team-player or team-staff */
				$projectTeamIds = self::_getProjectTeamIds4UserId($user->id);
				$teamplayer     = sportsmanagementModelPlayer::getTeamPlayer();

				if (isset($teamplayer->projectteam_id))
				{
					$result = in_array($teamplayer->projectteam_id, $projectTeamIds);
				}
			}
		}

		return $result;
	}

	/**
	 * sportsmanagementModelPerson::_getProjectTeamIds4UserId()
	 *
	 * @param   mixed  $userId
	 *
	 * @return
	 */
	function _getProjectTeamIds4UserId($userId)
	{
		/** Team player */
        $this->jsmquery->clear(); 
		$this->jsmquery->select('tp.projectteam_id');
		$this->jsmquery->from('#__sportsmanagement_person AS pr');
		$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = pr.id');
		$this->jsmquery->where('pr.user_id = ' . $userId);
		$this->jsmquery->where('pr.published = 1');
		$this->jsmquery->where('tp.persontype = 1');

		$this->jsmdb->setQuery($this->jsmquery);

		$projectTeamIds = array();

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			/** Joomla! 3.0 code here */
			$projectTeamIds = $this->jsmdb->loadColumn();
		}

		/** Team_staff */
        $this->jsmquery->clear(); 
		$this->jsmquery->select('tp.projectteam_id');
		$this->jsmquery->from('#__sportsmanagement_person AS pr');
		$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = pr.id');
		$this->jsmquery->where('pr.user_id = ' . $userId);
		$this->jsmquery->where('pr.published = 1');
		$this->jsmquery->where('tp.persontype = 2');

		$this->jsmdb->setQuery($this->jsmquery);

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			/** Joomla! 3.0 code here */
			$res = $this->jsmdb->loadColumn();
		}
		
		$projectTeamIds = array_merge($projectTeamIds, $res);
		$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $projectTeamIds;
	}

	/**
	 * get person history across all projects, with team, season, position,... info
	 *
	 * @param   int     $person_id  , linked to player_id from Person object
	 * @param   int     $order      ordering for season and league, default is ASC ordering
	 * @param   string  $filter     e.g. "s.name = 2007/2008", default empty string
	 *
	 * @return array of objects
	 */
	function getRefereeHistory($order = 'ASC')
	{
		$personid = $this->personid;
        $this->jsmquery->clear();
        $this->jsmquery->select('p.id AS person_id,tt.project_id,p.firstname AS fname,p.lastname AS lname,pj.name AS pname,s.name AS sname,pos.name AS position,COUNT(mr.id) AS matchesCount');
        $this->jsmquery->from('#__sportsmanagement_match_referee AS mr');
        $this->jsmquery->join('INNER', '#__sportsmanagement_match AS m ON m.id = mr.match_id ');
		$this->jsmquery->join('INNER', '#__sportsmanagement_person AS p ON p.id = mr.project_referee_id ');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project_team AS tt ON tt.id = m.projectteam1_id ');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project AS pj ON pj.id = tt.project_id ');
		$this->jsmquery->join('INNER', '#__sportsmanagement_season AS s ON s.id = pj.season_id ');
		$this->jsmquery->join('INNER', '#__sportsmanagement_league AS l ON l.id = pj.league_id ');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_position AS pos ON pos.id = mr.project_position_id ');
        $this->jsmquery->where('p.id = ' . (int) $personid);
        $this->jsmquery->group('tt.project_id');
        $this->jsmquery->order('s.ordering ASC, l.ordering ASC, pj.name ASC');

		$this->_db->setQuery($query);
		$results = $this->_db->loadObjectList();

		return $results;
	}

	/**
	 * sportsmanagementModelPerson::getContactID()
	 *
	 * @param   mixed  $catid
	 *
	 * @return
	 */
	function getContactID($catid)
	{
		$person = self::getPerson();
        
        $this->jsmquery->clear();
		$this->jsmquery->select('id');
		$this->jsmquery->from('#__contact_details');
		$this->jsmquery->where('user_id = ' . $person->jl_user_id);
		$this->jsmquery->where('catid = ' . $catid);

		$this->jsmdb->setQuery($this->jsmquery);
		$contact_id = $this->jsmdb->loadResult();
		$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $contact_id;
	}

	/**
	 * sportsmanagementModelPerson::getPerson()
	 *
	 * @param   integer  $personid
	 * @param   integer  $cfg_which_database
	 * @param   integer  $inserthits
	 *
	 * @return
	 */
	public static function getPerson($personid = 0, $cfg_which_database = 0, $inserthits = 0)
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		if ($personid)
		{
			self::$personid = $personid;
		}
		else
		{
			if (!self::$personid)
			{
				self::$personid = Factory::getApplication()->input->getInt('pid', 0);
			}
		}

		$starttime = microtime();

		self::updateHits(self::$personid, $inserthits);

		$db    = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
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
	 * sportsmanagementModelPerson::updateHits()
	 *
	 * @param   integer  $personid
	 * @param   integer  $inserthits
	 *
	 * @return void
	 */
	public static function updateHits($personid = 0, $inserthits = 0)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);

		if ($inserthits)
		{
			$query->update($db->quoteName('#__sportsmanagement_person'))->set('hits = hits + 1')->where('id = ' . $personid);

			$db->setQuery($query);

			$result = $db->execute();
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		}

	}

	/**
	 * get all positions the player was assigned too in different projects
	 *
	 * @return unknown_type
	 */
	function getAllEvents()
	{
		$history         = sportsmanagementModelPlayer::getPlayerHistory();
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
        
        $this->jsmquery->clear(); 
		$this->jsmquery->select('et.*');
		$this->jsmquery->from('#__sportsmanagement_eventtype AS et');
		$this->jsmquery->join('INNER', '#__sportsmanagement_position_eventtype AS pet ON pet.eventtype_id = et.id');
		$this->jsmquery->where('published = 1');
		$this->jsmquery->where('pet.position_id IN (' . implode(',', $positionhistory) . ')');
		$this->jsmquery->order('et.ordering');

		$this->jsmdb->setQuery($this->jsmquery);

		$info = $this->jsmdb->loadObjectList();
		$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $info;
	}

	
	/**
	 * sportsmanagementModelPerson::getPlayerEvents()
	 * 
	 * @param mixed $eventid
	 * @param mixed $projectid
	 * @param mixed $projectteamid
	 * @param integer $show_events_as_sum
	 * @return
	 */
	function getPlayerEvents($eventid, $projectid = null, $projectteamid = null,$show_events_as_sum = 1)
	{
		$this->jsmquery->clear(); 
        if ( $show_events_as_sum )
        {
        $this->jsmquery->select('SUM(me.event_sum) as total');    
        }
        else
        {
        $this->jsmquery->select('COUNT(me.event_sum) as total');    
        }        
        
		$this->jsmquery->from('#__sportsmanagement_match_event AS me');
		$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_person_id AS tp1 ON tp1.id = me.teamplayer_id');
		$this->jsmquery->where('me.event_type_id = ' . (int) $eventid);
		$this->jsmquery->where('tp1.person_id = ' . (int) self::$personid);

		if ($projectteamid)
		{
			$this->jsmquery->where('me.projectteam_id = ' . (int) $projectteamid);
		}

		$this->jsmquery->group('tp1.person_id');
		$this->jsmdb->setQuery($this->jsmquery);
		$result = $this->jsmdb->loadResult();

		if (empty($result))
		{
			$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
			return 0;
		}

		$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $result;
	}

	/**
	 * sportsmanagementModelPerson::getPlayerChangedRecipients()
	 *
	 * @return
	 */
	function getPlayerChangedRecipients()
	{
        $this->jsmquery->clear(); 
		$this->jsmquery->select('email');
		$this->jsmquery->from('#__users');
		$this->jsmquery->where('usertype = \'Super Administrator\' OR usertype = \'Administrator\' ');
		$this->jsmdb->setQuery($this->jsmquery);

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			/** Joomla! 3.0 code here */
			$res = $this->jsmdb->loadColumn();
		}

		$this->jsmdb->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $res;
	}

	/**
	 * sportsmanagementModelPerson::sendMailTo()
	 *
	 * @param   mixed  $listOfRecipients
	 * @param   mixed  $subject
	 * @param   mixed  $message
	 *
	 * @return void
	 */
	function sendMailTo($listOfRecipients, $subject, $message)
	{
		$app      = Factory::getApplication();
		$mailFrom = $app->getCfg('mailfrom');
		$fromName = $app->getCfg('fromname');
		Utility::sendMail($mailFrom, $fromName, $listOfRecipients, $subject, $message);
	}

	/**
	 * sportsmanagementModelPerson::isEditAllowed()
	 *
	 * @param   mixed  $config_editOwnPlayer
	 * @param   mixed  $config_editAllowed
	 *
	 * @return
	 */
	function isEditAllowed($config_editOwnPlayer, $config_editAllowed)
	{
		$app = Factory::getApplication();

		$allowed = false;
		$user    = Factory::getUser();

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

}
