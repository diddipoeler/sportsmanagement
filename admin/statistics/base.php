<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      base.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage statistics
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * SMStatistic
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class SMStatistic extends JObject 
{
	
	var $_name = 'default';
	
	var $_calculated = 0;
	
	var $_showinsinglematchreports = 1;
	
	/**
	 * JRegistry object for parameters
	 * @var object JRegistry
	 */
	var $_params = null;
	
	/**
	 * statistic
	 *
	 * @var int
	 */
	var $id = null;

	/**
	 * stat name
	 * @var string
	 */
	var $name;
	
	/**
	 * short form (abbreviation) of the name
	 * @var string
	 */
	var $short;
	/**
	 * icon path
	 * @var string
	 */
	var $icon;

	/**
	 * parameters for the stat (from xml)
	 * @var string
	 */
	var $baseparams;

	/**
	 * parameters for the stat (from xml)
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
     * SMStatistic::getTeamsRankingStatisticNumQuery()
     * damit die einzelnen statistik dateien nicht zu gross werden,
     * habe ich die queries hierher verlegt.
     * 
     * @param mixed $project_id
     * @param mixed $sids
     * @return
     */
    function getTeamsRankingStatisticNumQuery($project_id, $sids)
    {
    $option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
	$db = sportsmanagementHelper::getDBConnection();
    $query_num = JFactory::getDbo()->getQuery(true);
    
    $query_num->select('SUM(ms.value) AS num, pt.id');
    $query_num->from('#__sportsmanagement_season_team_person_id AS tp');
    $query_num->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
    $query_num->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
    $query_num->join('INNER','#__sportsmanagement_match_statistic AS ms ON ms.teamplayer_id = tp.id AND ms.statistic_id IN ('. implode(',', $sids) .')');
    $query_num->join('INNER','#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1 ');
    $query_num->where('pt.project_id = ' . $project_id);
    $query_num->group('pt.id');
    
    return $query_num;
    }
    
    /**
     * SMStatistic::getTeamsRankingStatisticDenQuery()
     * 
     * @param mixed $project_id
     * @param mixed $sids
     * @return
     */
    function getTeamsRankingStatisticDenQuery($project_id, $sids)
    {
    $option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
	$db = sportsmanagementHelper::getDBConnection();
    $query_den = JFactory::getDbo()->getQuery(true);
    
    $query_den->select('SUM(ms.value) AS den, pt.id');
    $query_den->from('#__sportsmanagement_season_team_person_id AS tp');
    $query_den->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
    $query_den->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
    $query_den->join('INNER','#__sportsmanagement_match_statistic AS ms ON ms.teamplayer_id = tp.id AND ms.statistic_id IN ('. implode(',', $sids) .')');
    $query_den->join('INNER','#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1 ');
    $query_den->where('pt.project_id = ' . $project_id);
    $query_den->where('value > 0');
    $query_den->group('pt.id');
    
    return $query_den;
        
    }
    
    
    /**
     * SMStatistic::getTeamsRankingStatisticCoreQuery()
     * 
     * @param mixed $project_id
     * @param mixed $query_num
     * @param mixed $query_den
     * @return
     */
    function getTeamsRankingStatisticCoreQuery($project_id, $query_num,$query_den)
    {
    $option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
	$db = sportsmanagementHelper::getDBConnection();
    $query_core = JFactory::getDbo()->getQuery(true);
    
    $query_core->select('(n.num / d.den) AS total, pt.team_id');
    $query_core->from('#__sportsmanagement_project_team AS pt');
    $query_core->join('INNER','('.$query_num.') AS n ON n.id = pt.id');
    $query_core->join('INNER','('.$query_den.') AS d ON d.id = pt.id');
    $query_core->join('INNER','#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id ');
    $query_core->join('INNER','#__sportsmanagement_team AS t ON st.team_id = t.id ');
    $query_core->where('pt.project_id = ' . $project_id);
    $query_core->group('total');
    return $query_core;
    }
   
    /**
     * SMStatistic::getStaffStatsQuery()
     * 
     * @param mixed $person_id
     * @param mixed $team_id
     * @param mixed $project_id
     * @param mixed $sids
     * @param mixed $select
     * @param bool $history
     * @param string $table
     * @return
     */
    function getStaffStatsQuery($person_id, $team_id, $project_id, $sids, $select,$history = false,$table = 'match_staff_statistic')
	{
		$option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
	$db = sportsmanagementHelper::getDBConnection();
    $query_core = $db->getQuery(true);
    
	$query_core->select($select);
    $query_core->from('#__sportsmanagement_season_team_person_id AS tp');
    $query_core->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
    $query_core->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
    $query_core->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id');
    
    switch ( $table )
    {
        case 'match_staff_statistic':
        if ( $sids )
        {
        $query_core->join('INNER','#__sportsmanagement_match_staff_statistic AS ms ON ms.team_staff_id = tp.id AND ms.statistic_id IN ('. $sids .')');    
        }
        else
        {
        $query_core->join('INNER','#__sportsmanagement_match_staff_statistic AS ms ON ms.team_staff_id = tp.id ');    
        }
        break;
        case 'match_staff':
        $query_core->join('INNER','#__sportsmanagement_match_staff AS ms ON ms.team_staff_id = tp.id '); 
        break;
        
    }
    
    
    
    $query_core->join('INNER','#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1 ');
    $query_core->where('p.published = 1');
    $query_core->where('tp.person_id = '. $person_id);
    
    if ( !$history )
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
     * @param mixed $project_id
     * @param mixed $division_id
     * @param mixed $team_id
     * @param mixed $sids
     * @param mixed $select
     * @param string $which
     * @return
     */
    function getPlayersRankingStatisticQuery($project_id, $division_id, $team_id, $sids, $select,$which='statistic')
    {
    $option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
	$db = sportsmanagementHelper::getDBConnection();
    $query_num = JFactory::getDbo()->getQuery(true);
    
    $query_num->select($select);
    $query_num->from('#__sportsmanagement_season_team_person_id AS tp');
    $query_num->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
    $query_num->join('INNER','#__sportsmanagement_person AS p ON p.id = tp.person_id ');
    $query_num->join('INNER','#__sportsmanagement_team AS t ON st.team_id = t.id');
    $query_num->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
    switch ($which)
    {
        case 'statistic':
        $query_num->join('INNER','#__sportsmanagement_match_statistic AS ms ON ms.teamplayer_id = tp.id AND ms.statistic_id IN ('. implode(',', $sids) .')');
        break;
        case 'event':
        $query_num->join('INNER','#__sportsmanagement_match_event AS ms ON ms.teamplayer_id = tp.id AND ms.event_type_id IN ('. implode(',', $sids) .')');
        break;
    }
    
    $query_num->join('INNER','#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1 ');
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
     * @param mixed $project_id
     * @param mixed $division_id
     * @param mixed $team_id
     * @param mixed $sids
     * @return
     */
    function getPlayersRankingStatisticNumQuery($project_id, $division_id, $team_id, $sids)
    {
    $option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
	$db = sportsmanagementHelper::getDBConnection();
    $query_num = JFactory::getDbo()->getQuery(true);
    
    $query_num->select('SUM(ms.value) AS num, tp.id AS tpid, tp.person_id');
    $query_num->from('#__sportsmanagement_season_team_person_id AS tp');
    $query_num->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
    $query_num->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
    $query_num->join('INNER','#__sportsmanagement_match_statistic AS ms ON ms.teamplayer_id = tp.id AND ms.statistic_id IN ('. implode(',', $sids) .')');
    $query_num->join('INNER','#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1 ');
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
     * @param mixed $project_id
     * @param mixed $division_id
     * @param mixed $team_id
     * @param mixed $query_num
     * @param mixed $query_den
     * @param mixed $select
     * @return
     */
    function getPlayersRankingStatisticCoreQuery($project_id, $division_id, $team_id,$query_num,$query_den,$select)
    {
    $option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
	$db = sportsmanagementHelper::getDBConnection();
    $query_core = JFactory::getDbo()->getQuery(true);
    
    $query_core->select($select);
    $query_core->from('#__sportsmanagement_season_team_person_id AS tp');
    $query_core->join('INNER','('.$query_num.') AS n ON n.tpid = tp.id');
    $query_core->join('INNER','('.$query_den.') AS d ON d.tpid = tp.id');
    $query_core->join('INNER','#__sportsmanagement_person AS p ON p.id = tp.person_id ');
    $query_core->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
    $query_core->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
    $query_core->join('INNER','#__sportsmanagement_team AS t ON st.team_id = t.id');
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
     * @param string $ids
     * @return
     */
    function getSids($id_field='stat_ids')
	{
	   $app = JFactory::getApplication();
		$params = self::getParams();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params<br><pre>'.print_r($params,true).'</pre>'),'');
        
        $stat_ids = $params->get($id_field);
       
		if (!count($stat_ids)) 
        {
			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
			return(array(0));
		}
				
		$db = sportsmanagementHelper::getDBConnection();
		$sids = array();
		foreach ($stat_ids as $s) 
        {
			$sids[] = (int)$s;
		}		
		return $sids;
	}
    
    /**
     * SMStatistic::getQuotedSids()
     * 
     * @param string $ids
     * @return
     */
    function getQuotedSids($id_field='stat_ids')
	{
	   $app = JFactory::getApplication();
		$params = self::getParams();
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' id_field<br><pre>'.print_r($id_field,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params<br><pre>'.print_r($params,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' get<br><pre>'.print_r($params->get($id_field),true).'</pre>'),'');
        
		//$event_ids = explode(',', $params->get($id_field));
        $event_ids = $params->get($id_field);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' event_ids<br><pre>'.print_r($event_ids,true).'</pre>'),'');
 }
        
		if (!count($event_ids)) 
        {
			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
			return(array(0));
		}
				
		$db = sportsmanagementHelper::getDBConnection();
		$ids = array();
		foreach ($event_ids as $s) 
        {
			$ids[] = $db->Quote((int)$s);
		}		
		return $ids;
	}
    
    
    
    
    

	/**
	 * get an instance of class corresponding to type
	 * @param string class
	 * @return object
	 */
	public static function getInstance($class)
	{
		$classname = 'SMStatistic'. ucfirst($class);

		if (!class_exists($classname))
		{
			$file = JPATH_COMPONENT_ADMINISTRATOR .DS . 'statistics' . DS .$class.'.php';
			if (!file_exists($file)) {
				JError::raiseError(0, $classname .': '. JText::_('STATISTIC CLASS NOT DEFINED'));
			}
			require_once($file);
		}
		$stat = new $classname();
		return $stat;
	}
	
	/**
	 * Binds a named array/hash to this object
	 *
	 * Can be overloaded/supplemented by the child class
	 *
	 * @access	public
	 * @param	$from	mixed	An associative array or object
	 * @param	$ignore	mixed	An array or space separated list of fields not to bind
	 * @return	boolean
	 */
	function bind( $from, $ignore=array() )
	{
		$fromArray	= is_array( $from );
		$fromObject	= is_object( $from );

		if (!$fromArray && !$fromObject)
		{
			$this->setError( get_class( $this ).'::bind failed. Invalid from argument' );
			return false;
		}
		if (!is_array( $ignore )) {
			$ignore = explode( ' ', $ignore );
		}
		foreach ($this->getProperties() as $k => $v)
		{
			// internal attributes of an object are ignored
			if (!in_array( $k, $ignore ))
			{
				if ($fromArray && isset( $from[$k] )) {
					$this->$k = $from[$k];
				} else if ($fromObject && isset( $from->$k )) {
					$this->$k = $from->$k;
				}
			}
		}
		return true;
	}
	
	/**
	 * return Statistic params as JParameter objet
	 * @return object
	 */
	function getBaseParams()
	{
	   $app = JFactory::getApplication();
       
       if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' baseparams<br><pre>'.print_r($this->baseparams,true).'</pre>'),'');
 }
       
		$paramsdata = $this->baseparams;
		$paramsdefs = JPATH_COMPONENT_ADMINISTRATOR.DS.'statistics' . DS . 'base.xml';
		$params = new JRegistry( $paramsdata, $paramsdefs );
  
        return $params;
	}
	
	/**
	 * return Statistic params as JParameter objet
	 * @return object
	 */
	function getClassParams()
	{
		// cannot use __FILE__ because extensions can have their own stats
		$rc = new ReflectionClass(get_class($this));
		$currentdir = dirname($rc->getFileName());
		$paramsdata = $this->params;
		$paramsdefs = $currentdir. DS . $this->_name .'.xml';
		$params = new JRegistry( $paramsdata, $paramsdefs );
        return $params;
	}
	
	/**
	 * return path to xml config file associated to this statistic
	 * @return object
	 */
	public function getXmlPath()
	{
		// cannot use __FILE__ because extensions can have their own stats
		$rc = new ReflectionClass(get_class($this));
		$currentdir = dirname($rc->getFileName());
		return $currentdir. DS . $this->_name .'.xml';
	}
	
	/**
	 * return Statistic params as JParameter objet
	 * @return object
     * https://docs.joomla.org/J2.5:Developing_a_MVC_Component/Adding_configuration
     * merge params
	 */
	function getParams()
	{
	   $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        
		if (empty($this->_params))
		{
		  $params = new JRegistry;
          $params = self::getBaseParams();
          // Merge global params with item params
          $classparams = self::getClassParams();
          $params->merge($classparams);
			//$this->_params = self::getBaseParams();
			//$this->_params->merge(self::getClassParams());
            //$this->_params = self::getClassParams();
          $this->_params = $params;  
		}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
    $my_text = ' <br><pre>'.print_r($this->_params,true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _params<br><pre>'.print_r($this->_params,true).'</pre>'),'');
 }
        
		return $this->_params;
	}
	
	/**
	 * return a parameter value
	 * @param string name
	 * @param mixed default value
	 * @return mixed
	 */
	function getParam($name, $default = '')
	{
		$params = self::getParams();
		return $params->get($name, $default);
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
	 * is the stat calculated
	 * in this case, no user input
	 * @return boolean
	 */
	function getCalculated()
	{
		return $this->_calculated;
	}
	
	/**
	 * show stat in single match views/reports?
	 * e.g.: stats 'per game' should not be displayed in match report...
	 * @return boolean
	 */
	function showInSingleMatchReports()
	{
		return $this->_showinsinglematchreports;
	}

	/**
	 * show stat in match report?
	 * setting in every statistic (backend) on which frontend views the stat should be shown
	 * @return boolean
	 */
	function showInMatchReport()
	{
		$params = self::getParams();
		//$statistic_views = explode(',', $params->get('statistic_views'));
        $statistic_views = $params->get('statistic_views');
		if (!count($statistic_views)) {
			JError::raiseWarning(0, get_class($this).' '.__FUNCTION__.' '.__LINE__.' '.JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
			return(array(0));
		}
				
		if ( in_array("matchreport", $statistic_views) || empty($statistic_views[0]) ) {
		    return 1;
		} else { 
		    return 0;
		} 
	}

	/**
	 * show stat in team roster?
	 * setting in every statistic (backend) on which frontend views the stat should be shown
	 * @return boolean
	 */
	function showInRoster()
	{
		$params = self::getParams();
		//$statistic_views = explode(',', $params->get('statistic_views'));
        $statistic_views = $params->get('statistic_views');
		if (!count($statistic_views)) 
        {
			JError::raiseWarning(0, get_class($this).' '.__FUNCTION__.' '.__LINE__.' '.JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
			return(array(0));
		}
				
		if ( in_array("roster", $statistic_views) || empty($statistic_views[0]) ) 
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
	 * @return boolean
	 */
	function showInPlayer()
	{
		$params = self::getParams();
		//$statistic_views = explode(',', $params->get('statistic_views'));
        $statistic_views = $params->get('statistic_views');
		if (!count($statistic_views)) {
			JError::raiseWarning(0, get_class($this).' '.__FUNCTION__.' '.__LINE__.' '.JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
			return(array(0));
		}
				
		if ( in_array("player", $statistic_views) || empty($statistic_views[0]) ) 
        {
		    return 1;
		} 
        else 
        { 
		    return 0;
		} 
	}
	
	/**
	 * return stat <img> html tag
	 * @return string
	 */
	function getImage()
	{
		if (!empty($this->icon))
		{
			$iconPath = $this->icon;
			if ( !strpos( " " . $iconPath, "/"	) )
			{
				$iconPath = "images/com_sportsmanagement/database/statistics/" . $iconPath;
			}
			return JHtml::image($iconPath, JText::_($this->name),	array( "title" => JText::_($this->name) ));
		}
		return '<span class="stat-alternate hasTip" title="'.JText::_($this->name).'">'.JText::_($this->short).'</span>';
	}

	/**
	 * return the stat value for specified player in a specific game
	 * @param object $gamemodel must provide methods getPlayersStats and getPlayersEvents
	 * @param int $teamplayer_id
	 * @return mixed stat value
	 */
	function getMatchPlayerStat(&$gamemodel, $teamplayer_id)
	{
		JError::raiseWarning(0, $this->_name .': '. JText::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'));
		return 0;
	}
	
	/**
	 * return all players stat for the game indexed by teamplyer_id
	 * @param int match_id
	 * @return value
	 */
	function getMatchPlayersStats($match_id)
	{
		JError::raiseWarning(0, $this->_name .': '. JText::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'));
		return 0;
	}

	/**
	 * return stat for all player games in the project
	 * @param int match_id
	 * @return value
	 */
	function getPlayerStatsByGame($teamplayer_id, $project_id)
	{
		JError::raiseWarning(0, $this->_name .': '. JText::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'));
		return 0;
	}
	
	/**
	 * return player stats in project if project_id != 0, otherwise player stats in whole player's career
	 * @param int person_id
	 * @return float
	 */
	function getPlayerStatsByProject($person_id, $projectteam_id = 0, $project_id = 0, $sports_type_id = 0)
	{
		JError::raiseWarning(0, $this->_name .': '. JText::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'));
		return 0;
	}
	
	/**
	 * Get players stats
	 * @param $team_id
	 * @param $project_id
	 * @param $position_id
	 * @return array
	 */
	function getRosterStats($team_id, $project_id, $position_id)
	{
		JError::raiseWarning(0, $this->_name .': '. JText::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'));
		return 0;
	}
	
	/**
	 * returns players ranking
	 * @param int project_id
	 * @param int division_id
	 * @param int limit
	 * @param in limitstart
	 * @return array
	 */
	function getPlayersRanking($project_id, $division_id, $team_id, $limit = 20, $limitstart = 0, $order = null)
	{
		JError::raiseWarning(0, $this->_name .': '. JText::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'));
		return array();
	}

	/**
	 * returns teams ranking
	 * @param int project_id
	 * @param int limit
	 * @param in limitstart
	 * @return array
	 */
	function getTeamsRanking($project_id, $limit = 20, $limitstart = 0, $order = null, $select = '', $statistic_id = 0)
	{
        $app = JFactory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();
		$query_core = $db->getQuery(true);
		
        switch ($this->_name)
        {
            case 'basic':
            case 'complexsum':
            case 'sumstats':
            $query_core->select($select);
            $query_core->from('#__sportsmanagement_season_team_person_id AS tp');
            $query_core->join('INNER','#__sportsmanagement_person AS p ON p.id = tp.person_id ');
            $query_core->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
            $query_core->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
            $query_core->join('INNER','#__sportsmanagement_team AS t ON st.team_id = t.id');
            $query_core->join('INNER','#__sportsmanagement_match_statistic AS ms ON ms.teamplayer_id = tp.id AND ms.statistic_id IN ( '. $statistic_id .' )' );
            $query_core->join('INNER','#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1');
            $query_core->where('pt.project_id = ' . $project_id);
            return $query_core;
            break;
            default:
            JError::raiseWarning(0, $this->_name .': '. JText::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'));
            return array();
            break;
        }
        
	}
	
	/**
	 * return the stat value for specified player in a specific game
	 * @param object $gamemodel must provide methods getPlayersStats and getPlayersEvents
	 * @param int $teamstaff_id
	 * @return mixed stat value
	 */
	function getMatchStaffStat(&$gamemodel, $team_staff_id)
	{		
		JError::raiseWarning(0,$this->_name .': '.  JText::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'));
		return 0;
	}
	
	/**
	 * return staff stat in project
	 * @param int person_id
	 * @param int project_id
	 * @return float
	 */
	function getStaffStats($person_id, $team_id, $project_id)
	{
		JError::raiseWarning(0,$this->_name .': '.  JText::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'));
		return 0;
	}

	/**
	 * return player stats in project
	 * @param int person_id
	 * @param int project_id
	 * @return float
	 */
	function getHistoryStaffStats($person_id)
	{
		JError::raiseWarning(0, $this->_name .': '. JText::_('METHOD NOT IMPLEMENTED IN THIS STATISTIC INSTANCE'));
		return 0;
	}

	/**
	 * return the player stats per match for given statistics IDs, for given project,
	 * using weighting factors if given
	 * @param int teamplayer IDs
	 * @param array statistic IDs
	 * @param int project_id
	 * @param array factors to be used for each statistics type
	 * @return array of stdclass objects with statistics info
	 */
	protected function getPlayerStatsByGameForIds($teamplayer_ids, $project_id, $sids, $factors = NULL)
	{
		$app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection();
        $query = JFactory::getDbo()->getQuery(true);

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
        $query->join('INNER','#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1');
        $query->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.id = ms.teamplayer_id ');
        $query->join('INNER','#__sportsmanagement_project_position AS ppos ON ppos.id = tp.project_position_id ');
        $query->join('INNER','#__sportsmanagement_position AS pos ON ppos.position_id = pos.id ');
        $query->join('INNER','#__sportsmanagement_position_statistic AS ps ON ps.position_id = pos.id AND ps.statistic_id = ms.statistic_id ');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query->join('INNER','#__sportsmanagement_project AS p ON pt.project_id = p.id AND p.id=' . $db->Quote($project_id));
        $query->where('ms.teamplayer_id IN (' . implode(',', $quoted_tpids) .')');
        $query->where('p.published = 1');
        $query->where('ms.statistic_id IN ('. implode(',', $quoted_sids) .')');

		if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        
        if (isset($factors))
		{
			$db->setQuery($query);
try{
			$stats = $db->loadObjectList();
            } catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}
			// Apply weighting using factors
			$res = array();
			foreach ($stats as $stat)
			{
				$key = array_search($stat->statistic_id, $sids);
				if ($key !== FALSE)
				{
					if (!isset($res[$stat->match_id])) {
						$res[$stat->match_id] = new stdclass;
						$res[$stat->match_id]->match_id = $stat->match_id;
						$res[$stat->match_id]->value = 0;
					}
					$res[$stat->match_id]->value += $factors[$key] * $stat->value;
				}
			}
		}
		else
		{
			$query->group('ms.match_id');
            $db->setQuery($query);
try{
			$res = $db->loadObjectList('match_id');
            } catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}
		}

		if (is_array($res) && count($res) > 0)
		{
			// Determine total for the whole project
			$total = new stdclass;
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
	 * @param int person_id
	 * @param int projectteam_id
	 * @param int project_id
	 * @param int sports_type_id
	 * @param array statistic IDs
	 * @param array factors to be used for each statistics type
	 * @return float
	 */
	protected function getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, $sids, $factors = NULL)
	{
		$app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection();
        $query = JFactory::getDbo()->getQuery(true);

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
        $query->join('INNER','#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1');
        $query->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.id = ms.teamplayer_id ');
        $query->join('INNER','#__sportsmanagement_project_position AS ppos ON ppos.id = tp.project_position_id ');
        $query->join('INNER','#__sportsmanagement_position AS pos ON ppos.position_id = pos.id ');
        $query->join('INNER','#__sportsmanagement_position_statistic AS ps ON ps.position_id = pos.id AND ps.statistic_id = ms.statistic_id ');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query->join('INNER','#__sportsmanagement_project AS p ON pt.project_id = p.id');
        $query->where('tp.person_id = ' . $person_id);
        $query->where('ms.statistic_id IN ('. implode(',', $quoted_sids) .')');

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
            $query->where('p.sports_type_id = '.$sports_type_id);
		}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
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
				if ($key !== FALSE)
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
	 * @param int person_id
	 * @param int project_id, if 0 then the number of played games for the complete career are returned
	 * @return integer
	 */
	protected function getGamesPlayedByPlayer($person_id, $projectteam_id, $project_id, $sports_type_id)
	{
		$db = sportsmanagementHelper::getDBConnection();
        $app = JFactory::getApplication();
        $query = JFactory::getDbo()->getQuery(true);
        $query_mp = JFactory::getDbo()->getQuery(true);
        $query_ms = JFactory::getDbo()->getQuery(true);
        $query_me = JFactory::getDbo()->getQuery(true);

		// To be robust against partly filled in information for a match (match player, statistic, event)
		// we determine if a player was contributing to a match, by checking for the following conditions:
		// 1. the player is registered as a player for the match
		// 2. the player has a statistic registered for the match
		// 3. the player has an event registered for the match
		// If any of these conditions are met, we assume the player was part of the match
		

        
        
        if ($projectteam_id)
		{
            $query_mp->where('pt.id = ' . $projectteam_id);
            $query_ms->where('pt.id = ' . $projectteam_id);
            $query_me->where('pt.id = ' . $projectteam_id);
		}
		if ($project_id)
		{
            $query_mp->where('p.id = ' . $project_id);
            $query_ms->where('p.id = ' . $project_id);
            $query_me->where('p.id = ' . $project_id);
		}
		if ($sports_type_id)
		{
            $query_mp->where('p.sports_type_id = ' . $sports_type_id);
            $query_ms->where('p.sports_type_id = ' . $sports_type_id);
            $query_me->where('p.sports_type_id = ' . $sports_type_id);
		}

		// Use md (stands for match detail, where the detail can be a match_player, match_statistic or match_event)
		// All of them have a match_id and teamplayer_id.
       
        $query_mp->select('m.id AS mid, tp.person_id');
        $query_mp->from('#__sportsmanagement_match_player AS md');
        $query_mp->join('INNER','#__sportsmanagement_match AS m ON m.id = md.match_id');
        $query_mp->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.id = md.teamplayer_id ');
        $query_mp->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $query_mp->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query_mp->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id');
        $query_mp->where('tp.person_id = '.$person_id);
        $query_mp->where('(md.came_in = 0 OR md.came_in = 1)');
        $query_mp->group('m.id');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_mp<br><pre>'.print_r($query_mp->dump(),true).'</pre>'),'');
 }       
 
        $query_ms->select('m.id AS mid, tp.person_id');
        $query_ms->from('#__sportsmanagement_match_statistic AS md');
        $query_ms->join('INNER','#__sportsmanagement_match AS m ON m.id = md.match_id');
        $query_ms->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.id = md.teamplayer_id ');
        $query_ms->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $query_ms->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query_ms->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id');
        $query_ms->where('tp.person_id = '.$person_id);
        $query_ms->group('m.id');

		$query_me->select('m.id AS mid, tp.person_id');
        $query_me->from('#__sportsmanagement_match_event AS md');
        $query_me->join('INNER','#__sportsmanagement_match AS m ON m.id = md.match_id');
        $query_me->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.id = md.teamplayer_id ');
        $query_me->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $query_me->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query_me->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id');
        $query_me->where('tp.person_id = '.$person_id);
        $query_me->group('m.id');
		
        $query->select('COUNT(m.id)');
        $query->from('#__sportsmanagement_match AS m');
        $query->join('LEFT','('.$query_mp.') AS mp ON mp.mid = m.id');
        $query->join('LEFT','('.$query_ms.') AS ms ON mp.mid = m.id');
        $query->join('LEFT','('.$query_me.') AS me ON mp.mid = m.id');
        $query->where('mp.person_id = '.$person_id.' OR ms.person_id = '.$person_id . ' OR me.person_id = '.$person_id);
        
		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
 }
        
		$res = $db->loadResult();
		return $res;
	}

	/**
	 * return the player stats for events per project for given statistics IDs, for given project,
	 * using weighting factors if given
	 * @param int person_id
	 * @param int projectteam_id
	 * @param int project_id
	 * @param int sports_type_id
	 * @param array statistic IDs
	 * @return float
	 */
	protected function getPlayerStatsByProjectForEvents($person_id, $projectteam_id, $project_id, $sports_type_id, $sids)
	{
		$app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' person_id<br><pre>'.print_r($person_id,true).'</pre>'),'');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectteam_id<br><pre>'.print_r($projectteam_id,true).'</pre>'),'');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_id<br><pre>'.print_r($project_id,true).'</pre>'),'');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' sports_type_id<br><pre>'.print_r($sports_type_id,true).'</pre>'),'');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' sids<br><pre>'.print_r($sids,true).'</pre>'),'');
        }

		if ( $sids )
        {
        $quoted_sids = array();
		foreach ($sids as $sid) 
        {
			$quoted_sids[] = $db->Quote($sid);
		}
        
        $query->select('SUM(me.event_sum) AS value, tp.person_id');
        $query->from('#__sportsmanagement_season_team_person_id AS tp');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
              
		if ($sports_type_id)
		{
            $query->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id AND p.sports_type_id = '. $sports_type_id);        
		}
        
        $query->join('INNER','#__sportsmanagement_match_event AS me ON me.teamplayer_id = tp.id AND me.event_type_id IN ('. implode(',', $quoted_sids) .')');
        $query->join('INNER','#__sportsmanagement_match AS m ON m.id = me.match_id AND m.published = 1');
		$query->where('tp.person_id = '. $person_id);
        
        if ($projectteam_id)
		{
               $query->where('pt.id = '. $projectteam_id);
		}

		if ($project_id)
		{
               $query->where('pt.project_id = '. $project_id);
		}

        $query->group('tp.person_id');

		$db->setQuery($query);

try{		
$res = $db->loadResult();
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}        
        }
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
    $my_text = ' <br><pre>'.print_r($query->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
 }       
        
        
		if (!isset($res))
		{
			$res = 0;
		}
		return $res;
	}

	/**
	 * return the statistics for given team and project
	 * @param int team_id
	 * @param int statistics ids (array)
	 * @param int project_id
	 * @param int array with multiplication factors for each sids entry
	 * @return array of integers
	 */
	protected function getRosterStatsForIds($team_id, $project_id, $position_id, $sids, $factors = NULL)
	{
		$db = sportsmanagementHelper::getDBConnection();
		$app = JFactory::getApplication();
        $query = JFactory::getDbo()->getQuery(true);
        
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
			//$query = '';
            $query->clear('select');
		}
        
        $query->from('#__sportsmanagement_season_team_person_id AS tp');
        $query->join('INNER','#__sportsmanagement_person AS prs ON prs.id = tp.person_id ');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query->join('INNER','#__sportsmanagement_match_statistic AS ms ON ms.teamplayer_id = tp.id AND ms.statistic_id IN ('. implode(',', $quoted_sids) .')');
        $query->join('INNER','#__sportsmanagement_project_position AS ppos ON ppos.id = tp.project_position_id ');
        $query->join('INNER','#__sportsmanagement_position AS pos ON pos.id = ppos.position_id ');
        $query->join('INNER','#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1 ');
        $query->where('st.team_id = '. $team_id);
        $query->where('pt.project_id = ' . $project_id);
        $query->where('ppos.position_id = '. $position_id);


		if (isset($factors))
		{
			$db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            }
            
try {
$stats = $db->loadObjectList();
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}
            
			// Apply weighting using factors
			$res = array();
			$res['totals'] = new stdclass;
			$res['totals']->value = 0;
			foreach ($stats as $stat)
			{
				$key = array_search($stat->statistic_id, $sids);
				if ($key !== FALSE)
				{
					if (!isset($res[$stat->person_id])) 
					{
						$res[$stat->person_id] = new stdclass;
						$res[$stat->person_id]->person_id = $stat->person_id;
						$res[$stat->person_id]->value = 0;
					}
					$contribution = $factors[$key] * $stat->value;
					$res[$stat->person_id]->value += $contribution;
					$res['totals']->value += $contribution;
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
            
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
    $my_text = ' <br><pre>'.print_r($query->dump(),true).'</pre>';    
        //sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            }
            
try{
			$res = $db->loadObjectList('person_id');
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}
			// Determine the total statistics for the position_id of the project team
            $query->clear('select');
            $query->clear('group');
            $query->select('SUM(ms.value) AS value');
            $db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
    $my_text .= ' <br><pre>'.print_r($query->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            }

try{            
			$res['totals'] = new stdclass;
			$res['totals']->value = $db->loadResult();
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
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
	 * @param int team_id
	 * @param int project_id
	 * @return array of integers
	 */
	protected function getGamesPlayedByProjectTeam($team_id, $project_id, $position_id)
	{
		$db = sportsmanagementHelper::getDBConnection();
        
        $app = JFactory::getApplication();
        $query = JFactory::getDbo()->getQuery(true);
        $subquery = JFactory::getDbo()->getQuery(true);
        $query_mp = JFactory::getDbo()->getQuery(true);
        $query_ms = JFactory::getDbo()->getQuery(true);
        $query_me = JFactory::getDbo()->getQuery(true);


		// Get the number of matches played per teamplayer of the projectteam.
		// This is derived from three tables: match_player, match_statistic and match_event
		// Use md (stands for match detail, where the detail can be a match_player, match_statistic or match_event)
		// All of them have a match_id and teamplayer_id.
        $query_mp->select('DISTINCT m.id AS mid, tp.id AS tpid');
        $query_mp->from('#__sportsmanagement_match_player AS md');
        $query_mp->join('INNER','#__sportsmanagement_match AS m ON m.id = md.match_id');
        $query_mp->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.id = md.teamplayer_id ');
        $query_mp->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $query_mp->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query_mp->where('pt.project_id = ' . $project_id);
        $query_mp->where('st.team_id = ' . $team_id);
        $query_mp->where('(md.came_in = 0 OR md.came_in = 1)');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_mp<br><pre>'.print_r($query_mp->dump(),true).'</pre>'),'');
 }

		$query_ms->select('DISTINCT m.id AS mid, tp.id AS tpid');
        $query_ms->from('#__sportsmanagement_match_statistic AS md');
        $query_ms->join('INNER','#__sportsmanagement_match AS m ON m.id = md.match_id');
        $query_ms->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.id = md.teamplayer_id ');
        $query_ms->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $query_ms->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query_ms->where('pt.project_id = ' . $project_id);
        $query_ms->where('st.team_id = ' . $team_id);
 
 if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{       
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_ms<br><pre>'.print_r($query_ms->dump(),true).'</pre>'),'');
 }
        
		$query_me->select('DISTINCT m.id AS mid, tp.id AS tpid');
        $query_me->from('#__sportsmanagement_match_event AS md');
        $query_me->join('INNER','#__sportsmanagement_match AS m ON m.id = md.match_id');
        $query_me->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.id = md.teamplayer_id ');
        $query_me->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $query_me->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query_me->where('pt.project_id = ' . $project_id);
        $query_me->where('st.team_id = ' . $team_id);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_me<br><pre>'.print_r($query_me->dump(),true).'</pre>'),'');
 }
        

        $subquery->select('DISTINCT m.id as mid, tp.id as tpid, tp.person_id');
        $subquery->from('#__sportsmanagement_match AS m');
        $subquery->join('INNER','#__sportsmanagement_round as r ON m.round_id=r.id ');
        $subquery->join('INNER','#__sportsmanagement_project AS p ON p.id=r.project_id ');
        $subquery->join('LEFT','( '.$query_mp.' ) AS mp ON mp.mid = m.id ');
        $subquery->join('LEFT','( '.$query_ms.' ) AS ms ON ms.mid = m.id ');
        $subquery->join('LEFT','( '.$query_me.' ) AS me ON me.mid = m.id ');
        $subquery->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON (tp.id = mp.tpid OR tp.id = ms.tpid OR tp.id = me.tpid) ');
        $subquery->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $subquery->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $subquery->where('st.team_id = '.$team_id);
        $subquery->where('p.id = ' . $project_id);
        $subquery->where('p.published = 1');
        $subquery->where('m.published = 1');
        $subquery->where('(mp.tpid = tp.id OR ms.tpid = tp.id OR me.tpid = tp.id)');
        
        
        $query->select('pse.person_id, COUNT(pse.mid) AS value');
        $query->from('( '.$subquery.' ) AS pse');
        $query->group('pse.tpid');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' subquery<br><pre>'.print_r($subquery->dump(),true).'</pre>'),'');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
 }       
                
		$db->setQuery($query);
		$res = $db->loadObjectList('person_id');

		$res['totals'] = new stdclass;
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
	 * @param int person_id
	 * @param array statistic IDs
	 * @param int project_id, if 0 then the complete career statistics of the player are returned
	 * @return float
	 */
	protected function getRosterStatsForEvents($team_id, $project_id, $position_id, $sids)
	{
		$db = sportsmanagementHelper::getDBConnection();
        $query = JFactory::getDbo()->getQuery(true);
        $app = JFactory::getApplication();

		$quoted_sids = array();
		foreach ($sids as $sid) 
        {
			$quoted_sids[] = $db->Quote($sid);
		}		


		// Determine the events for each project team player
        $query->select('SUM(es.event_sum) AS value, tp.person_id');
        $query->from('#__sportsmanagement_season_team_person_id AS tp');
        $query->join('INNER','#__sportsmanagement_person AS prs ON prs.id = tp.person_id ');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        
        $query->join('LEFT','#__sportsmanagement_project_position AS ppos ON ppos.id = tp.project_position_id');
        $query->join('LEFT','#__sportsmanagement_position AS pos ON pos.id=ppos.position_id');
        $query->join('LEFT','#__sportsmanagement_match_event AS es ON es.teamplayer_id = tp.id AND es.event_type_id IN ('. implode(',', $quoted_sids) .')');
        $query->join('LEFT','#__sportsmanagement_match AS m ON m.id = es.event_type_id AND m.published = 1');
        $query->where('st.team_id = '. $team_id);
        $query->where('pt.project_id = '. $project_id);
        $query->where('ppos.position_id = '. $position_id);
        $query->group('tp.id');
               
		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
 }
        
		$res = $db->loadObjectList('person_id');

		// Determine the event totals for the position_id of the project team
        $query->clear('select');
        $query->clear('group');
        $query->select('SUM(es.event_sum) AS value');
        
		$db->setQuery($query);
 
 if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{       
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
 }
        
		$res['totals'] = new stdclass;
		$res['totals']->value = $db->loadResult();
		return $res;
	}

	
	/**
	 * SMStatistic::getGamesPlayedQuery()
	 * 
	 * @param mixed $project_id
	 * @param mixed $division_id
	 * @param mixed $team_id
	 * @return
	 */
	protected function getGamesPlayedQuery($project_id, $division_id, $team_id)
	{
		$db = sportsmanagementHelper::getDBConnection();
        $app = JFactory::getApplication();
        $query = JFactory::getDbo()->getQuery(true);
        $subquery = JFactory::getDbo()->getQuery(true);
        $query_mp = JFactory::getDbo()->getQuery(true);
        $query_ms = JFactory::getDbo()->getQuery(true);
        $query_me = JFactory::getDbo()->getQuery(true);


		// To be robust against partly filled in information for a match (match player, statistic, event)
		// we determine if a player was contributing to a match, by checking for the following conditions:
		// 1. the player is registered as a player for the match
		// 2. the player has a statistic registered for the match
		// 3. the player has an event registered for the match
		// If any of these conditions are met, we assume the player was part of the match
//		$common_query_part 	= ' INNER JOIN #__joomleague_match AS m ON m.id = md.match_id'
//							. ' INNER JOIN #__joomleague_team_player AS tp ON tp.id = md.teamplayer_id'
//							. ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id'
//							. ' WHERE pt.project_id=' . $db->Quote($project_id);
//		if ($division_id)
//		{
//			$common_query_part .= ' AND pt.division_id=' . $db->Quote($division_id);
//		}
//		if ($team_id)
//		{
//			$common_query_part .= ' AND pt.team_id=' . $db->Quote($team_id);
//		}

		// Use md (stands for match detail, where the detail can be a match_player, match_statistic or match_event)
		// All of them have a match_id and teamplayer_id.
		$query_mp->select('m.id AS mid, tp.id AS tpid');
        $query_mp->from('#__sportsmanagement_match_player AS md');
        $query_mp->join('INNER','#__sportsmanagement_match AS m ON m.id = md.match_id');
        $query_mp->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.id = md.teamplayer_id ');
        $query_mp->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $query_mp->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query_mp->where('pt.project_id = ' . $project_id);
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
        $query_ms->join('INNER','#__sportsmanagement_match AS m ON m.id = md.match_id');
        $query_ms->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.id = md.teamplayer_id ');
        $query_ms->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $query_ms->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query_ms->where('pt.project_id = ' . $project_id);
        
        if ($division_id)
		{
            $query_ms->where('pt.division_id = ' . $division_id);
		}
		if ($team_id)
		{
            $query_ms->where('st.team_id = ' . $team_id);
		}
        //$query_ms->where('(md.came_in = 0 OR md.came_in = 1)');
        $query_ms->group('m.id, tp.id');
		$query_me->select('m.id AS mid, tp.id AS tpid');
        $query_me->from('#__sportsmanagement_match_event AS md');
        $query_me->join('INNER','#__sportsmanagement_match AS m ON m.id = md.match_id');
        $query_me->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.id = md.teamplayer_id ');
        $query_me->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $query_me->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query_me->where('pt.project_id = ' . $project_id);

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
        $subquery->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = p.id ');
        $subquery->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $subquery->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $subquery->join('INNER','#__sportsmanagement_match AS m ON m.projectteam1_id = pt.id OR m.projectteam2_id = pt.id');
        
        $subquery->join('LEFT','( '.$query_mp.' ) AS mp ON mp.mid = m.id AND mp.tpid = tp.id ');
        $subquery->join('LEFT','( '.$query_ms.' ) AS ms ON ms.mid = m.id AND ms.tpid = tp.id ');
        $subquery->join('LEFT','( '.$query_me.' ) AS me ON me.mid = m.id AND me.tpid = tp.id ');
        
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
        $query->from('( '.$subquery.' ) AS gp');
        $query->group('gp.tpid');

		return $query;
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
}
