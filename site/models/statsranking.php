<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage statsranking
 * @file       statsranking.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelStatsRanking
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelStatsRanking extends BaseDatabaseModel
{
	/**
	 * players total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	var $order = null;

	static $divisionid = 0;

	static $teamid = 0;

	static $cfg_which_database = 0;

	static $projectid = 0;

	/**
	 * sportsmanagementModelStatsRanking::__construct()
	 *
	 * @return void
	 */
	function __construct( )
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		parent::__construct();

		self::$projectid    = $jinput->getInt('p', 0);
		self::$divisionid    = $jinput->getInt('division', 0);
		self::$teamid        = $jinput->getInt('tid', 0);
		self::setStatid($jinput->getVar('sid', 0));
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName());

		// TODO: the default value for limit should be updated when we know if there is more than 1 statistics type to be shown
		if ($this->stat_id != 0)
		{
			$this->limit = $jinput->getInt('limit', $config["max_stats"]);
		}
		else
		{
			$this->limit = $jinput->getInt('limit', $config["count_stats"]);
		}

		$this->limitstart = $jinput->getInt('limitstart', 0);
		$this->setOrder($jinput->getVar('order'));

			  self::$cfg_which_database = $jinput->getInt('cfg_which_database', 0);
		sportsmanagementModelProject::$projectid = self::$projectid;

	}

	/**
	 * sportsmanagementModelStatsRanking::getDivision()
	 *
	 * @return
	 */
	function getDivision()
	{
		$division = null;

		if (self::$divisionid != 0)
		{
			$division = sportsmanagementModelProject::getDivision(self::$divisionid, self::$cfg_which_database);
		}

		return $division;
	}

	/**
	 * sportsmanagementModelStatsRanking::getTeamId()
	 *
	 * @return
	 */
	function getTeamId()
	{
		return self::$teamid;
	}

	/**
	 * set order (asc or desc)
	 *
	 * @param   string $order
	 * @return string order
	 */
	function setOrder($order)
	{
		if (strcasecmp($order, 'asc') === 0 || strcasecmp($order, 'desc') === 0)
		{
			$this->order = strtolower($order);
		}

		return $this->order;
	}

	/**
	 * sportsmanagementModelStatsRanking::getLimit()
	 *
	 * @return
	 */
	function getLimit( )
	{
		return $this->limit;
	}

	/**
	 * sportsmanagementModelStatsRanking::getLimitStart()
	 *
	 * @return
	 */
	function getLimitStart( )
	{
		return $this->limitstart;
	}

	/**
	 * sportsmanagementModelStatsRanking::setStatid()
	 *
	 * @param   mixed $statid
	 * @return void
	 */
	function setStatid($statid)
	{
		// Allow for multiple statistics IDs, arranged in a single parameters (sid) as string
		// with "|" as separator
		$sidarr = explode("|", $statid);
		$this->stat_id = array();

		foreach ($sidarr as $sid)
		{
			$this->stat_id[] = (int) $sid;    // The cast gets rid of the slug
		}

		// In case 0 was (part of) the sid string, make sure all stat types are loaded)
		if (in_array(0, $this->stat_id))
		{
			$this->stat_id = 0;
		}
	}


	/**
	 * sportsmanagementModelStatsRanking::getProjectUniqueStats()
	 *
	 * @return
	 */
	function getProjectUniqueStats()
	{
		$pos_stats = sportsmanagementModelProject::getProjectStats($this->stat_id, 0, self::$cfg_which_database);

			  $allstats = array();

		foreach ($pos_stats as $pos => $stats)
		{
			foreach ($stats as $stat)
			{
				$allstats[$stat->id] = $stat;
			}
		}

		return $allstats;
	}

	/**
	 * sportsmanagementModelStatsRanking::getPlayersStats()
	 *
	 * @param   mixed $order
	 * @return
	 */
	function getPlayersStats($order=null)
	{
		$stats = self::getProjectUniqueStats();
		$order = ($order ? $order : $this->order);
		$results = array();
		$results2 = array();

		foreach ($stats as $stat)
		{
			$results[$stat->id] = $stat->getPlayersRanking(self::$projectid, self::$divisionid, self::$teamid, self::getLimit(), self::getLimitStart(), $order);
			$results2[$stat->id] = $stat->getTeamsRanking(self::$projectid, self::getLimit(), self::getLimitStart(), $order);
		}

		return $results;
	}

}
