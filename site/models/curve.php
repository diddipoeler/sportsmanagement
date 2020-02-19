<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      curve.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage curve
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelCurve
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelCurve extends BaseDatabaseModel
{
	var $project = null;
	static $projectid = 0;
	static $teamid1 = 0;
	var $team1 = array();
	static $teamid2 = 0;
	var $team2 = array();
	var $allteams = null;
	var $divisions = null;
	var $favteams = null;
	var $divlevel = null;
	var $height = 180;
	var $selectoptions = array();
	var $teamlist2options = array();

	// Chart Data
	static $division = 0;
	var $round = 0;
	var $roundsName = array();
	var $ranking1 = array();
	var $ranking2 = array();
	var $ranking = array(); // cache for ranking function return data
	var $teamcount = array();
    
    static $cfg_which_database = 0;
	static $season_id = 0;
	
	/**
	 * sportsmanagementModelCurve::__construct()
	 * 
	 * @return
	 */
	function __construct( )
	{
	   // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
		parent::__construct( );
		self::$projectid = $jinput->get('p', 0, 'INT');
		self::$division = $jinput->get('division', 0, 'INT');
		self::$teamid1 = $jinput->get('tid1', 0, 'INT');
		self::$teamid2 = $jinput->get('tid2', 0, 'INT');
		$this->both = $jinput->getInt('both', 0);
        sportsmanagementModelProject::$projectid = self::$projectid;
        self::$cfg_which_database = $jinput->get('cfg_which_database', 0, 'INT');
	self::$season_id = $jinput->get('s', 0, 'INT');	
$post = $jinput->post->getArray(array());
if ( $post )
{
self::$teamid1 = $post['tid1_'.$post['division']];
self::$teamid2 = $post['tid2_'.$post['division']];    
}
        
		$this->determineTeam1And2();
	}

	/**
	 * sportsmanagementModelCurve::determineTeam1And2()
	 * 
	 * @return
	 */
	function determineTeam1And2()
	{
	   $option = Factory::getApplication()->input->getCmd('option');
	$app = Factory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
		// Use favorite team(s) in case both teamids are 0
		if ((self::$teamid1 == 0) && (self::$teamid2 == 0))
		{
			$favteams = sportsmanagementModelProject::getFavTeams();
			$selteam1 = ( isset( $favteams[0] ) ) ? $favteams[0] : 0;
			$selteam2 = ( isset( $favteams[1] ) ) ? $favteams[1] : 0;
			self::$teamid1 = (self::$teamid1 == 0 ) ? $selteam1 : self::$teamid1;
			self::$teamid2 = (self::$teamid2 == 0 ) ? $selteam2 : self::$teamid2;
		}

		// When (one of) the teams are not specified, search for the next unplayed or the latest played match
		if ((self::$teamid1 == 0) || (self::$teamid2 == 0))
		{
			
            $query->select('t1.id AS teamid1, t2.id AS teamid2');
            $query->from('#__sportsmanagement_match AS m ');
            $query->join('INNER','#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id ');
            $query->where('pt1.project_id = '.self::$projectid);

			if (self::$division)
			{
                $query->where('pt1.division_id = '.self::$division);
			}
            
            
            $query->join('INNER','#__sportsmanagement_season_team_id as st1 ON st1.id = pt1.team_id ');
            $query->join('INNER','#__sportsmanagement_team AS t1 ON st1.team_id = t1.id ');
            $query->join('INNER','#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id ');
            $query->where('pt2.project_id = '.self::$projectid);
                
			if (self::$division)
			{
                $query->where('pt2.division_id = '.self::$division);
			}
            
            
            $query->join('INNER','#__sportsmanagement_season_team_id as st2 ON st2.id = pt2.team_id ');
            $query->join('INNER','#__sportsmanagement_team AS t2 ON st2.team_id = t2.id ');
            $query->join('INNER','#__sportsmanagement_project AS p ON pt1.project_id = p.id AND pt2.project_id = p.id ');
            
            $query->where('m.published = 1 AND m.cancel = 0');
            
			if (self::$teamid1)
			{
				$quoted_team_id = self::$teamid1;
                $team = 'st1';
			}
			else
			{
				$quoted_team_id = self::$teamid2;
                $team = 'st2';
			}
            
			if ($this->both)
			{
                $query->where('(st1.team_id='.$quoted_team_id.' OR st2.team_id='.$quoted_team_id.')');
			}
			else
			{
                $query->where($team.'.team_id='.$quoted_team_id);
			}
            
			$config = sportsmanagementModelProject::getTemplateConfig($this->getName(),self::$cfg_which_database);
			$expiry_time = $config ? $config['expiry_time'] : 0;
            
            $query->where('(m.team1_result IS NULL OR m.team2_result IS NULL)');
            $query->where('DATE_ADD(m.match_date, INTERVAL '.$this->_db->Quote($expiry_time).' MINUTE) >= NOW()');

            $query->order('m.match_date');
            
			$db->setQuery($query);
        
			$match = $db->loadObject();

			// If there is no unplayed match left, take the latest match played
			if (!isset($match))
			{
                $query->clear('order');
                $query->order('m.match_date DESC');
                
                $starttime = microtime(); 
                
				$db->setQuery($query);
        
				$match = $db->loadObject();
			}
			if (isset($match))
			{
				self::$teamid1 = $match->teamid1;
				self::$teamid2 = $match->teamid2;
			}
		}
	}

	/**
	 * sportsmanagementModelCurve::getDivLevel()
	 * 
	 * @return
	 */
	function getDivLevel()
	{
		if ( is_null( $this->divlevel ) )
		{
			$config = sportsmanagementModelProject::getTemplateConfig("ranking",self::$cfg_which_database);
			$this->divlevel = $config['default_division_view'];
		}
		return $this->divlevel;
	}

	/**
	 * sportsmanagementModelCurve::getTeam1()
	 * 
	 * @param integer $division
	 * @return
	 */
	function getTeam1($division=0)
	{
		if (!self::$teamid1) {
			return false;
		}
		$data = self::getDataByDivision($division);
		foreach ($data as $team)
		{
			if ($team->id == self::$teamid1) {
				return $team;
			}
		}
		return false;
	}

	/**
	 * sportsmanagementModelCurve::getTeam2()
	 * 
	 * @param integer $division
	 * @return
	 */
	function getTeam2($division=0)
	{
		if (!self::$teamid2) {
			return false;
		}
		$data = self::getDataByDivision($division);
		foreach ($data as $team)
		{
			if ($team->id == self::$teamid2) {
				return $team;
			}
		}
		return false;
	}

	/**
	 * sportsmanagementModelCurve::getDivision()
	 * 
	 * @return
	 */
	function getDivision( )
	{
		return sportsmanagementModelProject::getDivision(self::$division,self::$cfg_which_database);
	}

	/**
	 * sportsmanagementModelCurve::getDivisionId()
	 * 
	 * @return
	 */
	function getDivisionId( )
	{
		return self::$division;
	}
	
	/**
	 * sportsmanagementModelCurve::getDataByDivision()
	 * 
	 * @param integer $division
	 * @return
	 */
	function getDataByDivision($division=0)
	{
	   $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        
		$project = sportsmanagementModelProject::getProject(self::$cfg_which_database);
		$rounds  = sportsmanagementModelProject::getRounds('ASC',self::$cfg_which_database,FALSE);
		$teams   = sportsmanagementModelProject::getTeamsIndexedByPtid($division,'name',self::$cfg_which_database);
        	
		$rankinghelper = JSMRanking::getInstance($project,self::$cfg_which_database);
		$rankinghelper->setProjectId( $project->id,self::$cfg_which_database );

        sportsmanagementModelRounds::$_project_id = $project->id;
		$firstRound = sportsmanagementModelRounds::getFirstRound($project->id,self::$cfg_which_database);
		$firstRoundId = $firstRound['id'];
		
		$rankings = array();
		foreach ($rounds as $r)
		{
			$rankings[$r->id] = $rankinghelper->getRanking($firstRoundId,$r->id,$division,self::$cfg_which_database);
		}
        
		foreach ($teams as $ptid => $team)
		{
			if($team->is_in_score==0) continue;
			$team_rankings = array();
			foreach ($rankings as $roundcode => $t)
			{
				if(empty($t[$ptid])) continue;
				$team_rankings[] = $t[$ptid]->rank;
			}
			$teams[$ptid]->rankings = $team_rankings;
		}
		return $teams;
	}
}
?>
