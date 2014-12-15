<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model');

//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

class sportsmanagementModelStatsRanking extends JModelLegacy
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
    
	function __construct( )
	{
		parent::__construct( );

		self::$projectid	= JRequest::getInt( 'p', 0 );
		self::$divisionid	= JRequest::getInt( 'division', 0 );
		self::$teamid		= JRequest::getInt( 'tid', 0 );
		self::setStatid(JRequest::getVar( 'sid', 0 ));
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName());
		// TODO: the default value for limit should be updated when we know if there is more than 1 statistics type to be shown
		if ( $this->stat_id != 0 )
		{
			$this->limit = JRequest::getInt( 'limit', $config["max_stats"] );
		}
		else
		{
			$this->limit = JRequest::getInt( 'limit', $config["count_stats"] );
		}
		$this->limitstart = JRequest::getInt( 'limitstart', 0 );
		$this->setOrder(JRequest::getVar('order'));
        
        self::$cfg_which_database = JRequest::getInt( 'cfg_which_database', 0 );
        sportsmanagementModelProject::$projectid = self::$projectid;
        
	}
	
	function getDivision()
	{
		$division = null;
		if (self::$divisionid != 0)
		{
			$division = sportsmanagementModelProject::getDivision(self::$divisionid,self::$cfg_which_database);
		}
		return $division;
	}

	function getTeamId()
	{
		return self::$teamid;
	}

	/**
	 * set order (asc or desc)
	 * @param string $order
	 * @return string order
	 */
	function setOrder($order)
	{
		if (strcasecmp($order, 'asc') === 0 || strcasecmp($order, 'desc') === 0) {
			$this->order = strtolower($order);
		}
		return $this->order;
	}

	function getLimit( )
	{
		return $this->limit;
	}

	function getLimitStart( )
	{
		return $this->limitstart;
	}

	function setStatid($statid)
	{
		// Allow for multiple statistics IDs, arranged in a single parameters (sid) as string
		// with "|" as separator
		$sidarr = explode("|", $statid);
		$this->stat_id = array();
		foreach ($sidarr as $sid)
		{
			$this->stat_id[] = (int)$sid;	// The cast gets rid of the slug
		}
		// In case 0 was (part of) the sid string, make sure all stat types are loaded)
		if (in_array(0, $this->stat_id))
		{
			$this->stat_id = 0;
		}
	}

	
	function getProjectUniqueStats()
	{
		$pos_stats = sportsmanagementModelProject::getProjectStats($this->stat_id,0,self::$cfg_which_database);
		
		$allstats = array();
		foreach ($pos_stats as $pos => $stats) 
		{
			foreach ($stats as $stat) {
				$allstats[$stat->id] = $stat;
			} 
		}
		return $allstats;
	}
	
	function getPlayersStats($order=null)
	{
		$stats = self::getProjectUniqueStats();
		$order = ($order ? $order : $this->order);
		
		$results = array();
		foreach ($stats as $stat) 
		{
			$results[$stat->id] = $stat->getPlayersRanking(self::$projectid, self::$divisionid, self::$teamid, self::getLimit(), self::getLimitStart(), $order);
		}
		
		return $results;
	}

}
?>