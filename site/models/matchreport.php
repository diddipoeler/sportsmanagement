<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      matchreport.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\Path;

/**
 * sportsmanagementModelMatchReport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelMatchReport extends JSMModelLegacy
{

	var $matchid = 0;
	var $match = null;
    var $projectid = 0;
    
    //static $cfg_which_database = 0;

	/**
	 * caching for players events. Used in stats calculations
	 * @var unknown_type
	 */
	var $_playersevents = null;

	/**
	 * caching for players basic stats. Used in stats calculations
	 * @var unknown_type
	 */
	var $_playersbasicstats = null;

	/**
	 * caching for staff basic stats. Used in stats calculations
	 * @var unknown_type
	 */
	var $_staffsbasicstats = null;


	/**
	 * sportsmanagementModelMatchReport::__construct()
	 * 
	 * @return
	 */
	function __construct()
	{
	parent::__construct();
		// Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        
	$this->matchid = $jinput->getInt('mid',0);
        $this->projectid = $jinput->getInt( 'p', 0 );
        sportsmanagementModelProject::$cfg_which_database = $jinput->getInt('cfg_which_database',0);
	sportsmanagementModelProject::$projectid = $this->projectid;
        sportsmanagementModelProject::$matchid = $this->matchid;
	
	}

	
    /**
     * sportsmanagementModelMatchReport::checkMatchPlayerProjectPositionID()
     * 
     * @return void
     */
    function checkMatchPlayerProjectPositionID()
    {
    $option = Factory::getApplication()->input->getCmd('option');
	$app = Factory::getApplication();
    $starttime = microtime(); 
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
        $query = $db->getQuery(true);
        // Select some fields
		$query->select('id,match_id,teamplayer_id,project_position_id');
        // From table
		$query->from('#__sportsmanagement_match_player');
        $query->where('match_id = '.(int)$this->matchid);
        $query->where('project_position_id = 0');
        
        
        try{
		$db->setQuery($query);
        $result = $db->loadObjectList();
        }
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
}
	    
        if ( $result )
        {
        
        foreach ($result as $row)
			{
			$query->clear();
            // Select some fields
		    $query->select('ppp.project_position_id');
            // From table
		    $query->from('#__sportsmanagement_person_project_position as ppp');
            $query->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = ppp.person_id');
            $query->join('INNER','#__sportsmanagement_person AS pe ON pe.id = tp.person_id');
            $query->where('ppp.project_id = '.(int)$this->projectid);
            $query->where('tp.id = '.(int)$row->teamplayer_id);
            
        
 try{
	 $db->setQuery($query);
		$position = $db->loadResult();
	}
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
}	
            
            if ( $position )
            {
            $tblmatchplayer = Table::getInstance("matchplayer", "sportsmanagementTable");
            $tblmatchplayer->load( (int) $row->id);
            $tblmatchplayer->project_position_id = $position;
            // Store the item to the database
		      if (!$tblmatchplayer->store())
		      {
			 //$this->setError($this->_db->getErrorMsg());
			 //return false;
		      }    
            
            }	
            
			}    
            
        }    
        
    }
    
    /**
	 * sportsmanagementModelMatchReport::getClubinfo()
	 * 
	 * @param mixed $clubid
	 * @return
	 */
	function getClubinfo($clubid)
	{
        $this->club = Table::getInstance("club", "sportsmanagementTable");
		$this->club->load($clubid);
		return $this->club;
	}

	/**
	 * sportsmanagementModelMatchReport::getRound()
	 * 
	 * @return
	 */
	function getRound()
	{
        $match = sportsmanagementModelMatch::getMatchData($this->matchid,sportsmanagementModelProject::$cfg_which_database);
        $round = sportsmanagementModelround::getRound($match->round_id,sportsmanagementModelProject::$cfg_which_database);

		if(is_null($round->name) || empty($round->name))
		{
			$round->name = Text::sprintf('COM_SPORTSMANAGEMENT_RESULTS_GAMEDAY_NB',$round->id);
		}

		return $round;
	}

  /**
   * sportsmanagementModelMatchReport::getMatchPictures()
   * 
   * @param mixed $folder
   * @return
   */
  function getMatchPictures($folder)
  {
  $basePath = JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'com_sportsmanagement'.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.$folder;
  $sitePath = 'images'.DIRECTORY_SEPARATOR.'com_sportsmanagement'.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.$folder;
  $images 	= array ();
  
  if( Folder::exists($basePath) )
{
  /** Get the list of files and folders from the given folder */
	$fileList 	= Folder::files($basePath);
  /** Iterate over the files if they exist */
		if ($fileList !== false) {
			foreach ($fileList as $file)
			{
				if (is_file($basePath.DIRECTORY_SEPARATOR.$file) && substr($file, 0, 1) != '.'
					&& strtolower($file) !== 'index.html'
					&& strtolower($file) !== 'thumbs.db'
					&& strtolower($file) !== 'readme.txt'
					)

					{

					if ( $search == '') {
						$tmp = new JObject();
						$tmp->name = $file;
						$tmp->sitepath = $sitePath;
						$tmp->path = Path::clean($basePath.DIRECTORY_SEPARATOR.$file);

						$images[] = $tmp;

					} elseif(stristr( $file, $search)) {
						$tmp = new JObject();
						$tmp->name = $file;
						$tmp->sitepath = $sitePath;
						$tmp->path = Path::clean($basePath.DIRECTORY_SEPARATOR.$file);

						$images[] = $tmp;

					}
				}
			}
		}
		
	$list = $images;
  return $list;
  }
  else
  {
  return false;
  }
  	
  }
  
	
    
    /**
     * sportsmanagementModelMatchReport::getMatchPositions()
     * 
     * @param string $which
     * @return
     */
    function getMatchPositions($which = 'player')
	{
	$option = Factory::getApplication()->input->getCmd('option');
	$app = Factory::getApplication();
    $starttime = microtime(); 
    $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
    $query = $db->getQuery(true);
	$query->select('pos.id, pos.name');
	$query->select('ppos.position_id AS position_id, ppos.id as pposid');
	$query->from('#__sportsmanagement_position AS pos');
    $query->join('INNER','#__sportsmanagement_project_position AS ppos ON pos.id = ppos.position_id');
        
        switch($which)
        {
            case 'player':
            $query->join('INNER','#__sportsmanagement_match_player AS mp ON ppos.position_id = mp.project_position_id');
            break;
            case 'staff':
            $query->join('INNER','#__sportsmanagement_match_staff AS mp ON ppos.position_id = mp.project_position_id');
            break;
            case 'referee':
            $query->join('INNER','#__sportsmanagement_match_referee AS mp ON ppos.position_id = mp.project_position_id');
            break;
        }
        
        $query->where('mp.match_id = '.(int)$this->matchid);
	$query->where('ppos.project_id = '.(int)$this->projectid);    
        $query->group('pos.id');
        $query->order('pos.ordering ASC');
        try{
        $db->setQuery($query);
        $result = $db->loadObjectList();
        }
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
    $result = false;
}
       
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $result;
        
    }    
    
    /**
     * sportsmanagementModelMatchReport::getMatchPersons()
     * 
     * @param string $which
     * @return
     */
    function getMatchPersons($which = 'player')
	{
   $app = Factory::getApplication();
   $option = Factory::getApplication()->input->getCmd('option');
   $starttime = microtime(); 
   $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
   $query = $db->getQuery(true);
   $query->select('pt.id,pt.id as ptid');
   $query->select('p.firstname,p.nickname,p.lastname,p.picture AS ppic');
   $query->select('ppos.id AS pposid');
   $query->select('st.team_id,st.id as stid');
   $query->select('tp.person_id,tp.picture');
   $query->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
   $query->select('CONCAT_WS(\':\',p.id,p.alias) AS person_slug');
       
       switch($which)
        {
            case 'player':
            $query->select('mp.trikot_number,mp.teamplayer_id,mp.captain,mp.project_position_id as position_id');
            $query->select('tp.jerseynumber');
            $query->from('#__sportsmanagement_match_player AS mp');
            $query->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.id = mp.teamplayer_id ');
            $query->where('mp.came_in = 0');
            $query->order('mp.ordering, tp.jerseynumber, p.lastname');
            break;
            case 'staff':
            $query->select('mp.team_staff_id,mp.project_position_id as position_id');
            $query->from('#__sportsmanagement_match_staff AS mp');
            $query->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.id = mp.team_staff_id ');
            break;
        }    
       
       $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
	   $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id ');
	   $query->join('INNER','#__sportsmanagement_team AS t ON t.id = st.team_id ');
	   $query->join('INNER','#__sportsmanagement_person AS p ON tp.person_id = p.id ');
	   $query->join('LEFT','#__sportsmanagement_project_position AS ppos ON ppos.position_id = mp.project_position_id ');
      
       $query->where('pt.project_id = '.$this->projectid);
       $query->where('ppos.project_id = '.$this->projectid);
       $query->where('mp.match_id = '.(int)$this->matchid);
       $query->where('p.published = 1');

	    try{
		    $db->setQuery($query);
        $result = $db->loadObjectList();
		    }
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
    $result = false;
}
$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $result;
   }    
    
    

    /**
     * sportsmanagementModelMatchReport::getMatchReferees()
     * 
     * @return
     */
    function getMatchReferees()
	{
	$option = Factory::getApplication()->input->getCmd('option');
	$app = Factory::getApplication();
    $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
    $query = $db->getQuery(true);
        
    $query->select('p.id,p.firstname,p.nickname,p.lastname,CONCAT_WS(\':\',p.id,p.alias) AS person_slug,p.picture');
    $query->select('ppos.position_id,ppos.id AS pposid');
    $query->select('pos.name AS position_name');
    $query->from('#__sportsmanagement_match_referee AS mr');
    $query->join('INNER','#__sportsmanagement_project_referee AS pref ON mr.project_referee_id=pref.id ');
    $query->join('INNER','#__sportsmanagement_season_person_id AS tp on tp.id = pref.person_id');
        
    $query->join('INNER','#__sportsmanagement_person AS p ON tp.person_id=p.id');
    $query->join('LEFT','#__sportsmanagement_project_position AS ppos ON ppos.id=mr.project_position_id');
    $query->join('LEFT','#__sportsmanagement_position AS pos ON ppos.position_id=pos.id');
    $query->where('mr.match_id='.(int)$this->matchid);
    $query->where('p.published = 1');
    $query->where('tp.persontype = 3');
		
	    try{
	    $db->setQuery($query);
        $result = $db->loadObjectList();
	    }
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
    $result = false;
}
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
	return $result;	
	}



	/**
	 * sportsmanagementModelMatchReport::getEventTypes()
	 * 
	 * @return
	 */
	function getEventTypes()
	{
	$option = Factory::getApplication()->input->getCmd('option');
	$app = Factory::getApplication();
    $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
    $query = $db->getQuery(true);
        
    $query->select('et.id,et.name,et.icon');
    $query->from('#__sportsmanagement_eventtype AS et');
    $query->join('INNER','#__sportsmanagement_position_eventtype AS pet ON pet.eventtype_id = et.id ');
    $query->join('LEFT','#__sportsmanagement_match_event AS me ON et.id = me.event_type_id ');
    $query->where('me.match_id = '.(int)$this->matchid);
    $query->group('pet.ordering,et.id');
    $query->order('pet.ordering');
    try{            
	$db->setQuery($query);
    $result = $db->loadObjectList();
    $result = array_unique($result, SORT_REGULAR );
	}
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
    $result = false;
}
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
	return $result;	
	}

    
    
    
    /**
     * sportsmanagementModelMatchReport::getMatchArticle()
     * 
     * @param integer $article_id
     * @param integer $match_id
     * @param integer $cat_id
     * @return
     */
    function getMatchArticle($article_id = 0,$match_id = 0,$cat_id = 0)
	{
	$option = Factory::getApplication()->input->getCmd('option');
	$app = Factory::getApplication();
    $starttime = microtime(); 
    $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
    $query = $db->getQuery(true);
    $query->select('c.id,c.title');
    $query->select('c.introtext');
       
    switch ( ComponentHelper::getParams($option)->get('which_article_component') )
    {
        case 'com_content':
        $query->from('#__content as c');
        if ( $article_id && !$match_id )
        {
        $query->where('id = '. $article_id );
        }
        elseif ( !$article_id && $match_id )
        {
        $query->where('xreference = '. $match_id );
        }
        elseif ( $article_id && $match_id )
        {
        $query->where('(xreference = '. $match_id .' OR id = '. $article_id .' )' );
        }
        
        if ( $cat_id )
        {
        $query->where('catid = '. $cat_id );
        }
        break;
        case 'com_k2':
        $query->from('#__k2_items as c');
        if ( $article_id )
        {
        $query->where('id = '. $article_id );
        }
        break;
    }
    
    $db->setQuery($query); 
    $result = $db->loadObject();
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
    return $result;
    }
        
	/**
	 * sportsmanagementModelMatchReport::getMatchStats()
	 * 
	 * @return
	 */
	function getMatchStats()
	{
	$option = Factory::getApplication()->input->getCmd('option');
	$app = Factory::getApplication();
    $starttime = microtime(); 
    $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
    $query = $db->getQuery(true);
	$match = sportsmanagementModelMatch::getMatchData($this->matchid,sportsmanagementModelProject::$cfg_which_database);
    $query->select('*');
    $query->from('#__sportsmanagement_match_statistic');
    $query->where('match_id = '.$match->id );
        
 try{       
	 $db->setQuery($query);
		$res = $db->loadObjectList();
}
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
}
		$stats = array(	$match->projectteam1_id => array(),
		$match->projectteam2_id => array());
		if(count($stats)>0 && count($res)>0) {
			foreach ($res as $stat)
			{
				@$stats[$stat->projectteam_id][$stat->teamplayer_id][$stat->statistic_id]=$stat->value;
			}
		}
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $stats;
	}

	
	/**
	 * sportsmanagementModelMatchReport::getPlayersStats()
	 * 
	 * @return
	 */
	function getPlayersStats()
	{
	$option = Factory::getApplication()->input->getCmd('option');
	$app = Factory::getApplication();
    $starttime = microtime(); 
    $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
    $query = $db->getQuery(true);
        
	if (!($this->_playersbasicstats))
	{
	$match = sportsmanagementModelProject::getMatch();
    $query->select('*');
    $query->from('#__sportsmanagement_match_statistic');
    $query->where('match_id = '.$match->id );
 try{       
	 $db->setQuery($query);
			$res = $db->loadObjectList();
}
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
}
			$stats=array();
			if (count($res))
			{
				foreach ($res as $stat)
				{
					@$stats[$stat->teamplayer_id][$stat->statistic_id]=$stat->value;
				}
			}
			$this->_playersbasicstats=$stats;
		}
$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $this->_playersbasicstats;
	}

	
	/**
	 * sportsmanagementModelMatchReport::getPlayersEvents()
	 * 
	 * @return
	 */
	function getPlayersEvents()
	{
	$option = Factory::getApplication()->input->getCmd('option');
	$app = Factory::getApplication();
    $starttime = microtime(); 
    $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
    $query = $db->getQuery(true);
        
	if (!($this->_playersevents))
	{
	$match = sportsmanagementModelMatch::getMatchData($this->matchid,sportsmanagementModelProject::$cfg_which_database);
    $query->select('*');
	$query->from('#__sportsmanagement_match_event');
    $query->where('match_id = '. $match->id );
        try{
		$db->setQuery($query);
			$res = $db->loadObjectList();
}
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
}
			
			$events = array();
			if (count($res))
			{
				foreach ($res as $event)
				{
					@$events[$event->teamplayer_id][$event->event_type_id] += $event->event_sum;
				}
			}
			$this->_playersevents = $events;
		}
$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $this->_playersevents;
	}

	
	/**
	 * sportsmanagementModelMatchReport::getMatchStaffStats()
	 * 
	 * @return
	 */
	function getMatchStaffStats()
	{
	$option = Factory::getApplication()->input->getCmd('option');
	$app = Factory::getApplication();
    $starttime = microtime(); 
    $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
    $query = $db->getQuery(true);
        
	if (!($this->_staffsbasicstats))
	{
	$match = sportsmanagementModelMatch::getMatchData($this->matchid,sportsmanagementModelProject::$cfg_which_database);
    $query->select('*');
	$query->from('#__sportsmanagement_match_staff_statistic');
    $query->where('match_id = '. $match->id );
try{       
	$db->setQuery($query);
			$res = $db->loadObjectList();
}
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
}
			$stats = array();
			if (count($res))
			{
				foreach ($res as $stat)
				{
					@$stats[$stat->team_staff_id][$stat->statistic_id]=$stat->value;
				}
			}
			$this->_staffsbasicstats=$stats;
		}
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $this->_staffsbasicstats;
	}
   
    
    	/**
    	 * sportsmanagementModelMatchReport::getPlaygroundSchema()
    	 * 
    	 * @param mixed $schema
    	 * @param mixed $which
    	 * @return
    	 */
    	function getPlaygroundSchema($schema,$which)
  {
  $option = Factory::getApplication()->input->getCmd('option');
	$app = Factory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
        $query = $db->getQuery(true);
        
  $bildpositionen = array();
  $position = 1;
  
  if ( $schema )
  {
  $query->select('extended');
  $query->from('#__sportsmanagement_rosterposition');
  $query->where('name LIKE '.  $db->Quote( '' . $schema . '' ) );
  
  switch ($which)
  {
    case 'heim':
    $query->where('short_name LIKE '.  $db->Quote( '' . 'HOME_POS' . '' ) );
    break;
    case 'gast':
    $query->where('short_name LIKE '.  $db->Quote( '' . 'AWAY_POS' . '' ) );
    break;
    
  }
  	try{	
        $db->setQuery($query);
		$res = $db->loadResult();
		}
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
}
	if ( $res )
    {
    	$xmlfile = JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'extended'.DIRECTORY_SEPARATOR.'rosterposition.xml';
		$jRegistry = new Registry;
		if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        $jRegistry->loadString($res); 
        }
        else
        {
		$jRegistry->loadJSON($res);
		}
    
    for($a=0; $a < 11; $a++)
    {
    $bildpositionen[$schema][$a][$which]['oben'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP');
    $bildpositionen[$schema][$a][$which]['links'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT');
    $position++;
    }
    }
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $bildpositionen;
  }
  else
  {
	  $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
    return false;
  }
  
  }

}
?>
