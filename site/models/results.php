<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      results.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage results
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

jimport('joomla.html.pane');
HTMLHelper::_('behavior.tooltip');

/**
 * sportsmanagementModelResults
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelResults extends JSMModelList
{
	static $projectid	= 0;
	static $divisionid	= 0;
	static $roundid = 0;
	var $rounds = array(0);
	static $mode = 0;
	static $order = 0;
	var $config = 0;
	var $project = null;
	var $matches = null;

    static $cfg_which_database = 0;
    static $layout = '';
static $limitstart = 0;
static $limit = 0;
	var $_identifier = "results";
	/**
	 * sportsmanagementModelResults::__construct()
	 * 
	 * @return
	 */
	function __construct()
	{
        parent::__construct();
        self::$limitstart = $this->jsmjinput->getVar('limitstart', 0, '', 'int');
		self::$divisionid = $this->jsmjinput->getVar('division','0');
		self::$mode = $this->jsmjinput->getVar('mode','0');
		self::$order = $this->jsmjinput->getVar('order','0');
        self::$projectid = $this->jsmjinput->getVar('p','0');
        self::$layout = $this->jsmjinput->getVar('layout','');
        
		$round = $this->jsmjinput->getVar('r','0');
		$roundid = $round;
        self::$cfg_which_database = $this->jsmjinput->getVar('cfg_which_database','0');
        
        sportsmanagementModelProject::$projectid = self::$projectid;
        sportsmanagementModelProject::$cfg_which_database = self::$cfg_which_database;
        sportsmanagementModelProject::$roundslug = $round;
        sportsmanagementModelProject::$seasonid = $this->jsmjinput->getVar('s','0');
        sportsmanagementModelProject::$layout = self::$layout;
        
		if( (int)$round > 0 ) 
        {
            self::$roundid = $round;
		} 
        else 
        {
            self::$roundid = sportsmanagementModelProject::getCurrentRound($this->jsmjinput->getVar('view'),self::$cfg_which_database);
		}
        
        sportsmanagementHelperHtml::$roundid = (int)self::$roundid;
		$this->config = sportsmanagementModelProject::getTemplateConfig('results',self::$cfg_which_database);
	}

	/**
 * Method to get the starting number of items for the data set.
 *
 * @return  integer  The starting number of items available in the data set.
 *
 * @since   11.1
 */
public function getStart()
{
    //$limitstart = $this->getUserStateFromRequest($this->context.'.limitstart', 'limitstart');
    $this->setState('list.start', self::$limitstart );
    
    $store = $this->getStoreId('getstart');
    // Try to load the data from internal storage.
    if (isset($this->cache[$store]))
    {
        return $this->cache[$store];
    }
    $start = $this->getState('list.start');
    $limit = $this->getState('list.limit');
    $total = $this->getTotal();
    if ($start > $total - $limit)
    {
        $start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
    }
    
    // Add the total to the internal cache.
    $this->cache[$store] = $start;
    return $this->cache[$store];
}

/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{	
	
    
	$value = $this->jsmapp->getUserStateFromRequest('global.list.limit', 'limit', $this->jsmapp->getCfg('list_limit'), 'uint');
self::$limit = $value;
$this->setState('list.limit', self::$limit);
$value = $this->jsmapp->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0);
self::$limitstart = (self::$limit != 0 ? (floor($value / self::$limit) * self::$limit) : 0);
$this->setState('list.start', self::$limitstart);
	
	}
	
    /**
     * sportsmanagementModelResults::getLimit()
     * 
     * @return
     */
    function getLimit()
	{
		return $this->getState('list.limit');
	}
	
	
	/**
	 * sportsmanagementModelResults::getLimitStart()
	 * 
	 * @return
	 */
	function getLimitStart()
	{
		return $this->getState('list.start');
	}
    
	/**
	 * sportsmanagementModelResults::getDivisionID()
	 * 
	 * @return
	 */
	function getDivisionID($cfg_which_database = 0)
	{
		return self::$divisionid;
	}

	/**
	 * sportsmanagementModelResults::getDivision()
	 * 
	 * @return
	 */
	function getDivision($cfg_which_database = 0)
	{
		$division = null;
		if ( (int)self::$divisionid > 0)
		{
			$division = self::getTable('Division','sportsmanagementTable');
			$division->load((int)self::$divisionid);
		}

		return $division;
	}
    
	/**
	 * sportsmanagementModelResults::limitText()
	 * 
	 * @param mixed $text
	 * @param mixed $wordcount
	 * @return
	 */
	function limitText($text, $wordcount)
	{
		if(!$wordcount) {
			return $text;
		}

		$texts = explode( ' ', $text );
		$count = count( $texts );

		if ( $count > $wordcount )
		{
			$texts = array_slice($texts,0, $wordcount ) ;
			$text = implode(' ' , $texts);
			$text .= '...';
		}

		return $text;
	}
    
    /**
     * sportsmanagementModelResults::getRssFeeds()
     * 
     * @param mixed $rssfeedlink
     * @param mixed $rssitems
     * @return
     */
    function getRssFeeds($rssfeedlink,$rssitems)
    {
    $rssIds = array();    
    $rssIds = explode(',',$rssfeedlink);    
    //  get RSS parsed object
		$options = array();
        $options['cache_time'] = null;
        
        $lists = array();
		foreach ($rssIds as $rssId)
		{
		$options['rssUrl'] = $rssId; 
        
         if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
$rssDoc = Factory::getFeedParser($options);
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
$rssDoc = Factory::getXMLparser('RSS', $options);
} 
elseif(version_compare(JVERSION,'1.7.0','ge')) 
{
// Joomla! 1.7 code here
} 
elseif(version_compare(JVERSION,'1.6.0','ge')) 
{
// Joomla! 1.6 code here
} 
else 
{
// Joomla! 1.5 code here
}      
		$feed = new stdclass();
        if ($rssDoc != false)
			{
				// channel header and link
				$feed->title = $rssDoc->get_title();
				$feed->link = $rssDoc->get_link();
				$feed->description = $rssDoc->get_description();
		$feed->image = NULL;
	$feed->image->url = '';
		$feed->image->title = '';
				// channel image if exists
		if ( !is_null($rssDoc->get_image_url()) )
		{
				$feed->image->url = $rssDoc->get_image_url();
		}
		else
		{
			$feed->image->url = '';
		}
		if ( !is_null($rssDoc->get_image_title()) )
		{
				$feed->image->title = $rssDoc->get_image_title();
		}
		else
		{
			$feed->image->title = '';
		}
	
				// items
				$items = $rssDoc->get_items();
				// feed elements
				$feed->items = array_slice($items, 0, $rssitems);
				$lists[] = $feed;
			}
        
         
        }  
    return $lists;         
    }

	
/**
 * sportsmanagementModelResults::getTotal()
 * 
 * @return
 */
function getTotal() {
        // Load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = self::getResultsRows((int)self::$roundid,(int)self::$divisionid,$this->config,NULL,self::$cfg_which_database,0,true);
            try{
            $this->_total = $this->_getListCount($query);
            }
            catch (Exception $e)
{
    $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
    return false;
}
        }
        return $this->_total;
    }
    
    /**
     * sportsmanagementModelResults::getData()
     * 
     * @return
     */
    function getData() {
        $cat_id = 0;
        // if data hasn't already been obtained, load it
        if (empty($this->_data)) {
            $query = self::getResultsRows((int)self::$roundid,(int)self::$divisionid,$this->config,NULL,self::$cfg_which_database,0,true);
            try{
		$this->_data = $this->_getList($query);
		}
            catch (Exception $e)
{
    $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
    return false;
}
        }
        $project = sportsmanagementModelProject::getProject(self::$cfg_which_database,__METHOD__);
        $allowed = self::isAllowed(self::$cfg_which_database,$project->editorgroup);
		$user = Factory::getUser();

		if ( count($this->_data) > 0 )
		{
			
			foreach ( $this->_data as $k => $match )
			{
				$this->_data[$k]->allowed = false;
				if ( ( $match->checked_out == 0 ) || ( $match->checked_out == $user->id ) )
				{
					if ( $allowed || self::isMatchAdmin($match->id,$user->id) )
					{
						$this->_data[$k]->allowed = true;
					}
				}
                $this->jsmquery->clear();
                /** welche joomla version ? */
if(version_compare( substr(JVERSION, 0, 3),'4.0','ge'))
{
$this->jsmquery->select('c.id');
$this->jsmquery->from('#__content as c');
$this->jsmquery->join('INNER','#__fields_values AS fv ON fv.item_id = c.id ');
$this->jsmquery->join('INNER','#__fields AS f ON f.id = fv.field_id ');
$this->jsmquery->where("f.title LIKE 'xreference' ");
$this->jsmquery->where('fv.value = '. $match->id );
$this->jsmquery->where('c.catid = '. $cat_id );
}
elseif(version_compare(substr(JVERSION, 0, 3),'3.0','ge')) 
{
$this->jsmquery->select('c.id');
$this->jsmquery->from('#__content as c');
$this->jsmquery->where('c.xreference = '. $match->id );
$this->jsmquery->where('c.catid = '. $cat_id );
}
				try{
                $this->jsmdb->setQuery($this->jsmquery); 
                $this->_data[$k]->content_id = $this->jsmdb->loadResult();
		 }
            catch (Exception $e)
{
    $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
	$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$this->jsmquery->dump()), 'error');	    
    $this->_data[$k]->content_id = 0;
}		
                
			}
		}
        
        
        return $this->_data;
    }

	/**
	 * sportsmanagementModelResults::getMatches()
	 * 
	 * @param integer $cfg_which_database
	 * @param integer $editorgroup
	 * @param integer $cat_id
	 * @return
	 */
	function getMatches($cfg_which_database = 0,$editorgroup=0,$cat_id = 0)
	{
	$app = Factory::getApplication();
    // Get a db connection.
    $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
    $query = $db->getQuery(true);
    $option = $app->input->getCmd('option');
		if (is_null($this->matches))
		{
			$this->matches = self::getResultsRows((int)self::$roundid,(int)self::$divisionid,$this->config,NULL,$cfg_which_database,0,false);
		}
		
		$allowed = self::isAllowed($cfg_which_database,$editorgroup);
		$user = Factory::getUser();

		if ( count($this->matches) > 0 )
		{
			foreach ( $this->matches as $k => $match )
			{
				if ( ( $match->checked_out == 0 ) || ( $match->checked_out == $user->id ) )
				{
					if ( $allowed || self::isMatchAdmin($match->id,$user->id) )
					{
						$this->matches[$k]->allowed = true;
					}
				}
                $query->clear();
                $query->select('c.id');
                $query->from('#__content as c');
                $query->where('xreference = '. $match->id );
                $query->where('catid = '. $cat_id );
                $db->setQuery($query); 
                $this->matches[$k]->content_id = $db->loadResult();
                
			}
		}
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
//		if (empty($this->_data)) {
//            $this->_data = $this->_getList($query);
//        }
		return $this->matches;
	}

	
	/**
	 * sportsmanagementModelResults::getResultsRows()
	 * 
	 * @param mixed $round
	 * @param mixed $division
	 * @param mixed $config
	 * @return
	 */
	public static function getResultsRows($round,$division,&$config,$params = NULL,$cfg_which_database = 0,$team = 0,$pagination = false)
	{
	$app = Factory::getApplication();
	$option = $app->input->getCmd('option');
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
        $query = $db->getQuery(true);
        
        $project = sportsmanagementModelProject::getProject($cfg_which_database,__METHOD__);

		if ( !$round && $project ) 
        {
            $round = $project->current_round;
		}

	$result = array();
if ( $project ) 
        {		
        // select some fields
        $query->select('m.*,DATE_FORMAT(m.time_present,"%H:%i") time_present');
        $query->select('playground.name AS playground_name,playground.short_name AS playground_short_name');
        $query->select('pt1.project_id');
        $query->select('d1.name as divhome');
        $query->select('d2.name as divaway');
        $query->select('CASE WHEN CHAR_LENGTH(t1.alias) AND CHAR_LENGTH(t2.alias) THEN CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) ELSE m.id END AS slug ');
        $query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
        $query->select('CONCAT_WS( \':\', r.id, r.alias ) AS round_slug');
        $query->select('CONCAT_WS( \':\', playground.id, playground.alias ) AS playground_slug');
            
        if ( $params )
        {
            $query->select('c1.'.$params->get('picture_type').' as logohome');
            $query->select('c2.'.$params->get('picture_type').' as logoaway');
            $query->select('t1.'.$params->get('team_names').' as teamhome');
            $query->select('t2.'.$params->get('team_names').' as teamaway');
           
            // favorisierte teams nutzen
            if ( $params->get('use_fav') )
            {
                $query->where('(t1.id IN ('.$project->fav_team.') OR t2.id IN ('.$project->fav_team.'))');
            }
            // ganze saison ?
            if ( !$params->get('project_season') )
            {
                $query->where('r.id = '.(int)$round); 
            }
            
        }
        else
        {
        $query->where('r.id = '.(int)$round);    
        }
        
	$query->from('#__sportsmanagement_match AS m');
        $query->join('INNER','#__sportsmanagement_round AS r ON m.round_id = r.id ');
        $query->join('INNER','#__sportsmanagement_project AS p ON p.id = r.project_id ');
        $query->join('LEFT','#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id');
        $query->join('LEFT','#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id');
        $query->join('LEFT','#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
        $query->join('LEFT','#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
        $query->join('LEFT','#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
        $query->join('LEFT','#__sportsmanagement_club AS c1 ON c1.id = t1.club_id');
        $query->join('LEFT','#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
        $query->join('LEFT','#__sportsmanagement_club AS c2 ON c2.id = t2.club_id');
        $query->join('LEFT','#__sportsmanagement_division AS d1 ON m.division_id = d1.id');
        $query->join('LEFT','#__sportsmanagement_division AS d2 ON m.division_id = d2.id');
        $query->join('LEFT','#__sportsmanagement_playground AS playground ON playground.id = m.playground_id');
		
	if ( $team )
	{
	$query->where('(st1.team_id = '.$team . ' OR st2.team_id = '.$team.')' );	
	}
        $query->where('m.published = 1');
        $query->where('r.project_id = '.(int)$project->id);
        if(version_compare(JSM_JVERSION,'3','eq')) 
        {
        $query->group('m.id ');
        }
        $query->order('m.match_date ASC,m.match_number');    

		if ( $division>0 )
		{
		  $query->where('(d1.id = '.(int)$division.' OR d1.parent_id = '.(int)$division.' OR d2.id = '.(int)$division.' OR d2.parent_id = '.(int)$division.')');
		}

try{
			
            if ( $pagination )
            {
	$db->setQuery($query,self::$limitstart,self::$limit);
            $result = $query;
            }
            else
            {
$db->setQuery($query);
            $result = $db->loadObjectList('id');
            }
            }
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
    $result = false;
}
	}
        
$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        
		return $result;
	}

	
	/**
	 * sportsmanagementModelResults::getMatchReferees()
	 * 
	 * @param mixed $match_id
	 * @return
	 */
	public static function getMatchReferees($match_id = 0,$cfg_which_database = 0)
	{
	$app = Factory::getApplication();
	$option = $app->input->getCmd('option');
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        $query->select('pref.id AS person_id,p.firstname,p.lastname,pos.name AS position_name');
        $query->from('#__sportsmanagement_match_referee AS mr');
        $query->join('LEFT','#__sportsmanagement_project_referee AS pref ON mr.project_referee_id=pref.id');
        $query->join('INNER','#__sportsmanagement_season_person_id AS spi ON pref.person_id=spi.id');
        $query->join('INNER','#__sportsmanagement_person AS p ON spi.person_id=p.id');
        $query->join('LEFT','#__sportsmanagement_project_position AS ppos ON mr.project_position_id=ppos.id');
        $query->join('LEFT','#__sportsmanagement_position AS pos ON ppos.position_id=pos.id');
        $query->where('mr.match_id = '.(int)$match_id);
        $query->where('p.published = 1');
        $query->order('pos.name,mr.ordering');  

		$db->setQuery($query);
                
        $result = $db->loadObjectList();
        
$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect        
		return $result;
		
	}

	
	/**
	 * sportsmanagementModelResults::getMatchRefereeTeams()
	 * 
	 * @param mixed $match_id
	 * @return
	 */
	function getMatchRefereeTeams($match_id = 0,$cfg_which_database = 0)
	{
	$app = Factory::getApplication();
		$option = $app->input->getCmd('option');
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
        $query->select('mr.project_referee_id AS value');
        $query->select('t.name AS teamname');
        $query->select('pos.name AS position_name');
          $query->from('#__sportsmanagement_match_referee AS mr ');
          $query->join('LEFT',' #__sportsmanagement_project_team AS pt ON pt.id = mr.project_referee_id');
          $query->join('LEFT',' #__sportsmanagement_season_team_id st ON st.id = pt.team_id ');
          $query->join('LEFT',' #__sportsmanagement_team AS t ON t.id = st.team_id');
          $query->join('LEFT',' #__sportsmanagement_position AS pos ON mr.project_position_id = pos.id');
          
          $query->where('mr.match_id = '.(int) $match_id);
          $query->order('pos.name,mr.ordering ASC');

		$db->setQuery($query);
        $result = $db->loadObjectList('value');
        
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $result;

	}

	/**
	 * sportsmanagementModelResults::isTeamEditor()
	 * 
	 * @param mixed $userid
	 * @return
	 */
	function isTeamEditor($userid = 0,$cfg_which_database = 0)
	{
	$app = Factory::getApplication();
		$option = $app->input->getCmd('option');
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
		if ($userid > 0)
		{
		  $query->select('tt.admin');
          $query->from('#__sportsmanagement_project_team AS tt ');
          $query->join('INNER',' #__sportsmanagement_match AS m ON (m.projectteam1_id = tt.id OR m.projectteam2_id = tt.id)');
          $query->where('tt.project_id = '. (int)self::$projectid);
          $query->where('tt.admin = '. (int)$userid );
           
            
			$db->setQuery($query);
        
			if ($db->loadResult()) return true;
		}
		return false;
	}

	/**
	 * sportsmanagementModelResults::isMatchAdmin()
	 * 
	 * @param mixed $matchid
	 * @param mixed $userid
	 * @return
	 */
	function isMatchAdmin($matchid = 0,$userid = 0,$cfg_which_database = 0)
	{
	$app = Factory::getApplication();
		$option = $app->input->getCmd('option');
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
        $query->select('COUNT(*)');
          $query->from('#__sportsmanagement_match AS m ');
          $query->join('INNER',' #__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id ');
          $query->join('INNER',' #__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id ');
          $query->where('m.id = '.$matchid);
          $query->where('(pt1.admin = '.(int)$userid.' OR pt2.admin = '.(int)$userid.')');

        $db->setQuery($query);
		try{
        $result = $db->loadResult();
	}
            catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
	$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$query->dump()), 'error');	    
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	    
    return false;
}	
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return $result;
	}

	
	
	/**
	 * sportsmanagementModelResults::isAllowed()
	 * 
	 * @param integer $cfg_which_database
	 * @param integer $editorgroup
	 * @return
	 */
	function isAllowed($cfg_which_database = 0,$editorgroup = 0)
	{
		$allowed = false;
		$user = Factory::getUser();
		if ( $user->id != 0 )
		{
			$project = sportsmanagementModelProject::getProject($cfg_which_database,__METHOD__);
			if ( $project )
			{
			$hasACLPermssion = $user->authorise('results.saveshort', 'com_sportsmanagement');
			$isProjectAdmin = $user->id == $project->admin;
			$isProjectEditor = $user->id == $project->editor;
           
			if( $hasACLPermssion && ($isProjectAdmin || $isProjectEditor) )
			{
				$allowed = true;
			}
		}
            
            if ( !$allowed )
            {
            // ist der user der einer gruppe zugeordnet ?
        $groups = UserHelper::getUserGroups($user->get('id')); 
        
        if(in_array($editorgroup,$groups))
        {
            $allowed = true;
        }
        else
        {
            $allowed = false;
        }    
            }
            
		}
		return $allowed;
	}

	
	/**
	 * sportsmanagementModelResults::getShowEditIcon()
	 * 
	 * @param integer $editorgroup
	 * @return
	 */
	function getShowEditIcon($editorgroup=0)
	{
        $app = Factory::getApplication();
		$option = $app->input->getCmd('option');
		$user = Factory::getUser();
        
		$allowed = self::isAllowed();
    	$showediticon = false;
        
        if ( $user->id != 0 )
		{ 
		
        if ( !$allowed )
			{  
        /**
         * ist der user der einer gruppe zugeordnet ?
         */
        $groups = UserHelper::getUserGroups($user->get('id')); 
        
        if(in_array($editorgroup,$groups))
        {
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_CHANGE_ALLOWED'),'Notice');
            $showediticon = true;
        }
        else
        {
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_CHANGE_NOTALLOWED'),'Notice');
            $showediticon = false;
        }
}
else
	{	
			if ( $allowed || self::isTeamEditor($user->id) )
			{
				$showediticon = true;
			}
            }
            
		}
        
		return $showediticon;
	}


    
    /**
     * sportsmanagementModelResults::saveshort()
     * 
     * @return
     */
    function saveshort($cfg_which_database = 0)
	{
		$app = Factory::getApplication();
/**
 *         JInput object
 */
        $option = $app->input->getCmd('option');
        
/**
 *         Get a db connection.
 */
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
        
        $pks = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
        $post = Factory::getApplication()->input->post->getArray(array());
        
        $result = true;
		for ($x=0; $x < count($pks); $x++)
		{
/**
 * 			Änderungen im datum oder der uhrzeit
 */
            $tbl = $this->getTable();;
            $tbl->load((int) $pks[$x]);
            
/**
 *             Create an object for the record we are going to update.
 */
            $object = new stdClass();
/**
 *             Must be a valid primary key value.
 */
            $object->id = $pks[$x];
            $object->team1_result = NULL;
            $object->team2_result = NULL;
            $object->team1_legs	= NULL;
            $object->team2_legs	= NULL;
        if ( $post['match_date'.$pks[$x]] )
			{
            list($date,$time) = explode(" ",$tbl->match_date);
            $this->_match_time_new = $post['match_time'.$pks[$x]].':00';
            $this->_match_date_new = $post['match_date'.$pks[$x]];
            $this->_match_time_old = $time;
            $this->_match_date_old = sportsmanagementHelper::convertDate($date);
            
            $post['match_date'.$pks[$x]] = sportsmanagementHelper::convertDate($post['match_date'.$pks[$x]],0);
            $post['match_date'.$pks[$x]] = $post['match_date'.$pks[$x]].' '.$post['match_time'.$pks[$x]].':00';
            
            if ( $post['match_date'.$pks[$x]] != $tbl->match_date )
            {
                $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_ADMIN_CHANGE'),'Notice');
            }
		}
			if ( $post['match_number'.$pks[$x]] )
			{
	$object->match_number	= $post['match_number'.$pks[$x]];
			}
			if ( $post['match_date'.$pks[$x]] )
			{
            $object->match_date = $post['match_date'.$pks[$x]];
			}
            if ( isset($post['result_type'.$pks[$x]]) )
            {
            $object->result_type = $post['result_type'.$pks[$x]];
            }
            else
            {
            $object->result_type = 0;    
            }
            
            if ( isset($post['match_result_type'.$pks[$x]]) )
            {
	$object->match_result_type = $post['match_result_type'.$pks[$x]];
	    }
	else
	{
	$object->match_result_type = 0;	
	}
            
            if ( isset($post['crowd'.$pks[$x]]) )
            {
            $object->crowd = $post['crowd'.$pks[$x]];
            }
           	else
	       {
	       $object->crowd = 0;	
	       }
           
            if ( $post['round_id'.$pks[$x]] )
            {
            $object->round_id	= $post['round_id'.$pks[$x]];
            }
            
            if ( isset($post['division_id'.$pks[$x]]) )
            {
            $object->division_id = $post['division_id'.$pks[$x]];
            }
           	else
	       {
	       $object->division_id = 0;	
	       }
    
            $object->projectteam1_id = $post['projectteam1_id'.$pks[$x]];
            $object->projectteam2_id = $post['projectteam2_id'.$pks[$x]];
            
            $object->team1_single_matchpoint = $post['team1_single_matchpoint'.$pks[$x]];
            $object->team2_single_matchpoint = $post['team2_single_matchpoint'.$pks[$x]];
            $object->team1_single_sets = $post['team1_single_sets'.$pks[$x]];
            $object->team2_single_sets = $post['team2_single_sets'.$pks[$x]];
            $object->team1_single_games = $post['team1_single_games'.$pks[$x]];
            $object->team2_single_games = $post['team2_single_games'.$pks[$x]];
            if ( isset($post['content_id'.$pks[$x]]) )
            {
            $object->content_id = $post['content_id'.$pks[$x]];
            }
           	else
	       {
	       $object->content_id = 0;	
	       }
           
            if ( $post['use_legs'] )
            {
                foreach ( $post['team1_result_split'.$pks[$x]] as $key => $value )
                {
                    if ( is_numeric($post['team1_result_split'.$pks[$x]][$key]) )
                    {
                        if ( $post['team1_result_split'.$pks[$x]][$key] > $post['team2_result_split'.$pks[$x]][$key] )
                        {
                            $object->team1_result	+= 1;
                            $object->team2_result	+= 0; 
                        }
                        if ( $post['team1_result_split'.$pks[$x]][$key] < $post['team2_result_split'.$pks[$x]][$key] )
                        {
                            $object->team1_result	+= 0;
                            $object->team2_result	+= 1; 
                        }
                        if ( $post['team1_result_split'.$pks[$x]][$key] == $post['team2_result_split'.$pks[$x]][$key] )
                        {
                            $object->team1_result	+= 1;
                            $object->team2_result	+= 1; 
                        }
                    }
                }
            }
            else
            {

            if ( is_numeric($post['team1_result'.$pks[$x]]) && is_numeric($post['team2_result'.$pks[$x]]) )    
            {    
            $object->team1_result	= $post['team1_result'.$pks[$x]];
            $object->team2_result	= $post['team2_result'.$pks[$x]];
            }
             
            if ( is_numeric($post['team1_result_ot'.$pks[$x]]) && is_numeric($post['team2_result_ot'.$pks[$x]]) )    
            {    
            $object->team1_result_ot	= $post['team1_result_ot'.$pks[$x]];
            $object->team2_result_ot	= $post['team2_result_ot'.$pks[$x]];
            }
            
            if ( is_numeric($post['team1_result_so'.$pks[$x]]) && is_numeric($post['team2_result_so'.$pks[$x]]) )    
            {    
            $object->team1_result_so	= $post['team1_result_so'.$pks[$x]];
            $object->team2_result_so	= $post['team2_result_so'.$pks[$x]];
            }
            
            if ( is_numeric($post['team1_legs'.$pks[$x]]) && is_numeric($post['team2_legs'.$pks[$x]]) )    
            {    
            $object->team1_legs	= $post['team1_legs'.$pks[$x]];
            $object->team2_legs	= $post['team2_legs'.$pks[$x]];
            }
		    
            }
            
            
            $object->team1_result_split	= implode(";",$post['team1_result_split'.$pks[$x]]);
            $object->team2_result_split	= implode(";",$post['team2_result_split'.$pks[$x]]);
try{
/**
 *             Update their details in the table using id as the primary key.
 */
            $result_update = $db->updateObject('#__sportsmanagement_match', $object, 'id', true);
            $result = true;
            $app->enqueueMessage(sprintf(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_SAVED'),$pks[$x]),'Notice');
}
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getCode()), 'error');
    $result = false;
}            
            
		}
		return $result;
	}
   
    
    /**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	Table	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'match', $prefix = 'sportsmanagementTable', $config = array()) 
	{
		return Table::getInstance($type, $prefix, $config);
	}
    
}
?>
