<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      projectteams.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage projectteams
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * sportsmanagementModelProjectteams
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelProjectteams extends JSMModelList 
{
	var $_identifier = "pteams";
    static $_project_id = 0;
    static $_division_id = 0;
    var $_season_id = 0;
    static $_pro_teams_in_used = array();
    var $project_art_id  = 0;
    var $sports_type_id= 0;
    
    /**
     * sportsmanagementModelProjectteams::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {
	    $config['filter_fields'] = array(
                        't.name',
                        't.lastname',
                        'tl.admin',
                        'd.name',
                        'tl.picture',
                        'st.team_id',
                        'st.id',
                        'tl.id',
                        't.ordering'
                        );
                parent::__construct($config);
                parent::setDbo($this->jsmdb);
	    
            self::$_project_id	= $this->jsmjinput->getInt('pid',0);
            self::$_division_id	= $this->jsmjinput->getInt('division',0);
            //$post = JFactory::getApplication()->input->get( 'post' );

if ( isset($this->jsmpost['addteam']) )
{
if ( $this->jsmpost['team_id'] )
{	
$this->addNewProjectTeam($post['team_id'],self::$_project_id);    
}    
}
            if ( !self::$_project_id )
                {
                self::$_project_id	= $this->jsmapp->getUserState( "$this->jsmoption.pid", '0' );    
                }
                $this->jsmapp->setUserState( "$this->jsmoption.pid", self::$_project_id ); 
                
                
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
        
        if ( JComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend') )
        {
		$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context -> '.$this->context.''),'');
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' identifier -> '.$this->_identifier.''),'');
        }

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);
		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $published);
        $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
		$this->setState('list.limit', $value);	

		// List state information.
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);
        // Filter.order
		$orderCol = $this->getUserStateFromRequest($this->context. '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 't.name';
		}
		$this->setState('list.ordering', $orderCol);
		$listOrder = $this->getUserStateFromRequest($this->context. '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);
	}

	/**
	 * sportsmanagementModelProjectteams::getListQuery()
	 * 
	 * @return
	 */
	protected function getListQuery()
	{
        //$this->_season_id = $this->jsmapp->getUserState( "$this->jsmoption.season_id", '0' );
        self::$_project_id = $this->jsmjinput->getVar('pid');
        self::$_division_id = $this->jsmjinput->getInt('division',0);
		
	// Create a new query object.		
	$this->jsmquery->clear();	
	// Select some fields
	$this->jsmquery->select('p.season_id');
        // From table
	$this->jsmquery->from('#__sportsmanagement_project AS p');
	$this->jsmquery->where('p.id = ' . self::$_project_id);	
	$this->jsmdb->setQuery($this->jsmquery);	
	$this->_season_id = $this->jsmdb->loadResult();	
	$this->jsmapp->setUserState( "$this->jsmoption.season_id", $this->_season_id);
		
        if ( !self::$_project_id )
        {
        self::$_project_id = $this->jsmapp->getUserState( "$this->jsmoption.pid", '0' );
        }
        $this->project_art_id = $this->jsmapp->getUserState( "$this->jsmoption.project_art_id", '0' );
        $this->sports_type_id = $this->jsmapp->getUserState( "$this->jsmoption.sports_type_id", '0' );
        
        // Create a new query object.		
		$this->jsmquery->clear();
        $this->jsmsubquery1->clear();
        $this->jsmsubquery2->clear();
        $this->jsmsubquery3->clear();
        
        // Select some fields
		$this->jsmquery->select('tl.id AS projectteamid,tl.*,st.team_id as team_id,st.id as season_team_id');
        // From table
		$this->jsmquery->from('#__sportsmanagement_project_team AS tl');
        
        if ( $this->project_art_id == 3 )
        {
        $this->jsmquery->join('LEFT', '#__sportsmanagement_season_person_id AS st on tl.team_id = st.id'); 
        $this->jsmquery->select("concat(t.lastname,' - ',t.firstname,'' ) AS name");
		$this->jsmquery->join('LEFT', '#__sportsmanagement_person AS t on st.person_id = t.id');   
        }
        else
        {    
        $this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st on tl.team_id = st.id');    
        // count team player
        $this->jsmsubquery1->select('count(tp.id)');
        $this->jsmsubquery1->from('#__sportsmanagement_season_team_person_id AS tp');
        $this->jsmsubquery1->where('tp.published = 1');
        $this->jsmsubquery1->where('tp.team_id = st.team_id');
        $this->jsmsubquery1->where('tp.persontype = 1');
        $this->jsmsubquery1->where('tp.season_id = '.$this->_season_id);
        $this->jsmquery->select('(' . $this->jsmsubquery1 . ') AS playercount');
        // count team staff
        $this->jsmsubquery2->select('count(tp.id)');
        $this->jsmsubquery2->from('#__sportsmanagement_season_team_person_id AS tp');
        $this->jsmsubquery2->where('tp.published = 1');
        $this->jsmsubquery2->where('tp.team_id = st.team_id');
        $this->jsmsubquery2->where('tp.persontype = 2');
        $this->jsmsubquery2->where('tp.season_id = '.$this->_season_id);
        $this->jsmquery->select('(' . $this->jsmsubquery2 . ') AS staffcount');    

        // Join over the team
		$this->jsmquery->select('t.name,t.club_id');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_team AS t on st.team_id = t.id');
        // Join over the club
		$this->jsmquery->select('c.email AS club_email,c.logo_big as club_logo,c.country,c.latitude,c.longitude,c.location,c.founded_year,c.unique_id');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_club AS c on t.club_id = c.id');
        // Join over the playground
		$this->jsmquery->join('LEFT', '#__sportsmanagement_playground AS plg on plg.id = tl.standard_playground');
        // Join over the division
		$this->jsmquery->join('LEFT', '#__sportsmanagement_division AS d on d.id = tl.division_id');
        }
        
        if ($this->getState('filter.search'))
		{
        $this->jsmquery->where('LOWER(t.name) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%'));
        }
        
        // Join over the users for the checked out user.
		$this->jsmquery->select('u.name AS editor,u.email AS email');
		$this->jsmquery->join('LEFT', '#__users AS u on tl.admin = u.id');
        
        $this->jsmquery->where('tl.project_id = ' . self::$_project_id);
        
        if ( self::$_division_id )
        {
        $this->jsmquery->where('tl.division_id = ' . self::$_division_id);    
        }	
        
        if ($this->getState('filter.search_nation'))
		{
        $this->jsmquery->where('c.country LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search_nation').'%') );
        }
        
        if (is_numeric($this->getState('filter.state')) )
		{
		$this->jsmquery->where('tl.published = '.$this->getState('filter.state'));	
		}

        $this->jsmquery->order($this->jsmdb->escape($this->getState('list.ordering', 't.name')).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));
 
 //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
 
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = 'dump<pre>'.print_r($this->jsmquery->dump(),true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }

		return $this->jsmquery;
	}


	
    
    /**
     * sportsmanagementModelProjectteams::addNewProjectTeam()
     * 
     * @param mixed $team_id
     * @param mixed $_project_id
     * @return void
     */
    function addNewProjectTeam($team_id,$project_id)
    {
	$db = JFactory::getDBO();
    $query = $db->getQuery(true);	
    $app = JFactory::getApplication();    
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($team_id,true).'</pre>'),'Notice');    
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($project_id,true).'</pre>'),'Notice');    

// holen wir uns das land der liga
$query->clear();
$query->select('l.country,p.season_id,p.project_type,p.master_template,p.extendeduser,p.points_after_regular_time');
$query->from('#__sportsmanagement_league as l');
$query->join('INNER','#__sportsmanagement_project as p on p.league_id = l.id');
$query->where('p.id = '.$project_id);
$db->setQuery( $query );
$pro_result = $db->loadObject();

$query->clear();
$query->select('id');
$query->from('#__sportsmanagement_season_team_id');
$query->where('team_id = '.$team_id);
$query->where('season_id = '.$pro_result->season_id );
$db->setQuery( $query );
$season_team_id = $db->loadResult();

// team ist der saison nicht zugeordnet
if ( !$season_team_id )
{
try {	
// Create and populate an object.
$temp_season_team_id = new stdClass();
$temp_season_team_id->team_id = $team_id;
$temp_season_team_id->season_id = $pro_result->season_id;
// Insert the object into the table.
$result_season_team_id = JFactory::getDbo()->insertObject('#__sportsmanagement_season_team_id', $temp_season_team_id);
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}
if ( $result_season_team_id )
{
$season_team_id = $db->insertid();
}
}

try {
// und dem projekt hinzufügen
$temp_project_team = new stdClass();
$temp_project_team->team_id = $season_team_id;
$temp_project_team->project_id = $project_id;
// Insert the object into the table.
$result_project_team = JFactory::getDbo()->insertObject('#__sportsmanagement_project_team', $temp_project_team);
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}
	
    }
        
	/**
     * Method to update project teams list
	 * sportsmanagementModelProjectteams::store()
	 * 
	 * @param mixed $data
	 * @return
	 */
	function store( $data )
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        
		$result = true;
		$peid = $data['project_teamslist'];
		if ( $peid == null )
		{
			$query = "	DELETE
						FROM #__sportsmanagement_project_team
						WHERE project_id = '" . $data['id'] . "'";
			
     try{
        $db->setQuery( $query );
        $db->execute();
             }
catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns
	JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error');	
	return false;
}    

		}
		else
		{
			JArrayHelper::toInteger( $peid );
			$peids = implode( ',', $peid );
			$query = "	DELETE
						FROM #__sportsmanagement_project_team
						WHERE project_id = '" . $data['id'] . "' AND team_id NOT IN  (" . $peids . ")";
			try{
        $db->setQuery( $query );
        $db->execute();
             }
catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns
	JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error');	
	return false;
}   

			$query = "	UPDATE  #__sportsmanagement_match
						SET projectteam1_id = NULL 
						WHERE projectteam1_id in (select id from #__sportsmanagement_project_team 
												where project_id = '" . $data['id'] . "' 
												AND team_id NOT IN  (" . $peids . "))";
			try{
        $db->setQuery( $query );
        $db->execute();
             }
catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns
	JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error');	
	return false;
}   
			$query = "	UPDATE  #__sportsmanagement_match
						SET projectteam2_id = NULL 
						WHERE projectteam2_id in (select id from #__sportsmanagement_project_team 
												where project_id = '" . $data['id'] . "' 
												AND team_id NOT IN  (" . $peids . "))";
			try{
        $db->setQuery( $query );
        $db->execute();
             }
catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns
	JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error');	
	return false;
}   
				
		}

		for ( $x = 0; $x < count( $data['project_teamslist'] ); $x++ )
		{
			$query = "	INSERT IGNORE
						INTO #__sportsmanagement_project_team
						(project_id, team_id)
						VALUES ( '" . $data['id'] . "', '".$data['project_teamslist'][$x] . "')";

			try{
        $db->setQuery( $query );
        $db->execute();
             }
catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns
	JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error');	
	return false;
}   
		}
		return $result;
	}

/**
 * sportsmanagementModelProjectteams::getCountryTeamsPicture()
 * 
 * @return
 */
function getCountryTeamsPicture()
	{
        self::$_project_id = $this->jsmapp->getUserState( "$this->jsmoption.pid", '0' );
        $this->_season_id = $this->jsmapp->getUserState( "$this->jsmoption.season_id", '0' );
        $this->project_art_id = $this->jsmapp->getUserState( "$this->jsmoption.project_art_id", '0' );
        $this->sports_type_id = $this->jsmapp->getUserState( "$this->jsmoption.sports_type_id", '0' );
        
        // noch das land der liga
        $this->jsmquery->clear();
        $this->jsmquery->select('l.country,p.season_id,p.project_type');
        $this->jsmquery->from('#__sportsmanagement_league as l');
        $this->jsmquery->join('INNER','#__sportsmanagement_project as p on p.league_id = l.id');
        $this->jsmquery->where('p.id = '.self::$_project_id);

        $this->jsmdb->setQuery( $this->jsmquery );
        $result = $this->jsmdb->loadObject();
		
		$this->jsmquery->clear();
        // Select some fields
		$this->jsmquery->select('t.id,c.logo_big as picture');
        // From table
		$this->jsmquery->from('#__sportsmanagement_team AS t');
        $this->jsmquery->join('INNER', '#__sportsmanagement_club AS c ON c.id = t.club_id');
        
        if ( $result->country )
        {
        $this->jsmquery->where('c.country LIKE '.$this->jsmdb->Quote(''.$result->country.''));
        }
        
        $this->jsmquery->order('t.name ASC');
        
try{
		$this->jsmdb->setQuery( $this->jsmquery );
		$result = $this->jsmdb->loadAssocList('id', 'picture');
        return $result;
        }
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(JText::_($e->getMessage()), 'error');
        return false;
        }
	}
	
/**
 * sportsmanagementModelProjectteams::getCountryTeams()
 * 
 * @return
 */
function getCountryTeams()
	{
        self::$_project_id = $this->jsmapp->getUserState( "$this->jsmoption.pid", '0' );
        $this->_season_id = $this->jsmapp->getUserState( "$this->jsmoption.season_id", '0' );
        $this->project_art_id = $this->jsmapp->getUserState( "$this->jsmoption.project_art_id", '0' );
        $this->sports_type_id = $this->jsmapp->getUserState( "$this->jsmoption.sports_type_id", '0' );
        
        // noch das land der liga
        $this->jsmquery->clear();
        $this->jsmquery->select('l.country,p.season_id,p.project_type');
        $this->jsmquery->from('#__sportsmanagement_league as l');
        $this->jsmquery->join('INNER','#__sportsmanagement_project as p on p.league_id = l.id');
        $this->jsmquery->where('p.id = '.self::$_project_id);

        $this->jsmdb->setQuery( $this->jsmquery );
        $result = $this->jsmdb->loadObject();
		
		$this->jsmquery->clear();
        // Select some fields
		$this->jsmquery->select('t.id AS value,t.name AS text,t.info,c.logo_big as picture');
        // From table
		$this->jsmquery->from('#__sportsmanagement_team AS t');
        $this->jsmquery->join('INNER', '#__sportsmanagement_club AS c ON c.id = t.club_id');
        
        if ( $result->country )
        {
        $this->jsmquery->where('c.country LIKE '.$this->jsmdb->Quote(''.$result->country.''));
        }
        
        $this->jsmquery->order('t.name ASC');
        
try{
		$this->jsmdb->setQuery( $this->jsmquery );
		$result = $this->jsmdb->loadObjectList();
		return $result;
        }
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(JText::_($e->getMessage()), 'error');
        return false;
        }
        
	}



	/**
	 * Method to return the teams array (id, name)
	 *
	 * @access  public
	 * @return  array
	 * @since 0.1
	 */
	function getTeams()
	{
        self::$_project_id = $this->jsmapp->getUserState( "$this->jsmoption.pid", '0' );
        $this->_season_id = $this->jsmapp->getUserState( "$this->jsmoption.season_id", '0' );
        $this->project_art_id = $this->jsmapp->getUserState( "$this->jsmoption.project_art_id", '0' );
        $this->sports_type_id = $this->jsmapp->getUserState( "$this->jsmoption.sports_type_id", '0' );
        
        // noch das land der liga
        $this->jsmquery->clear();
        $this->jsmquery->select('l.country,p.season_id,p.project_type');
        $this->jsmquery->from('#__sportsmanagement_league as l');
        $this->jsmquery->join('INNER','#__sportsmanagement_project as p on p.league_id = l.id');
        $this->jsmquery->where('p.id = '.self::$_project_id);

        $this->jsmdb->setQuery( $this->jsmquery );
        $result = $this->jsmdb->loadObject();
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = '_season_id<pre>'.print_r($this->_season_id,true).'</pre>';
        $my_text .= 'project_art_id<pre>'.print_r($this->project_art_id,true).'</pre>';
        $my_text .= 'sports_type_id<pre>'.print_r($this->sports_type_id,true).'</pre>';
        $my_text .= 'country<pre>'.print_r($result->country,true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
        }
       
        if ( $this->project_art_id == 3 )
        {
        $this->jsmquery->clear();
        // Select some fields
		$this->jsmquery->select("st.id AS value,concat(t.lastname,' - ',t.firstname,'' ) AS text,t.info");
        // From table
		$this->jsmquery->from('#__sportsmanagement_person AS t');
        $this->jsmquery->join('INNER', '#__sportsmanagement_season_person_id AS st on st.person_id = t.id');
        $this->jsmquery->where('st.season_id = ' . $this->_season_id);
        $this->jsmquery->where('t.sports_type_id = ' . $this->sports_type_id);
        $this->jsmquery->order('t.lastname ASC');    
        }
        else
        {
        $this->jsmquery->clear();
        // Select some fields
		$this->jsmquery->select('st.id AS value,t.name AS text,t.info');
        // From table
		$this->jsmquery->from('#__sportsmanagement_team AS t');
        $this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
        $this->jsmquery->join('INNER', '#__sportsmanagement_club AS c ON c.id = t.club_id');
        $this->jsmquery->where('st.season_id = ' . $this->_season_id);
        $this->jsmquery->where('t.sports_type_id = ' . $this->sports_type_id);
        
        if ( $result->country )
        {
        $this->jsmquery->where('c.country LIKE '.$this->jsmdb->Quote(''.$result->country.''));
        }
        
        $this->jsmquery->order('t.name ASC');
        }

try{
		$this->jsmdb->setQuery( $this->jsmquery );
        $result = $this->jsmdb->loadObjectList();
		return $result;
        }
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(JText::_($e->getMessage()), 'error');
        return false;
        }
        
        
	}

	/**
	 * sportsmanagementModelProjectteams::setNewTeamID()
	 * 
	 * @return void
	 */
	function setNewTeamID()
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        //$db = sportsmanagementHelper::getDBConnection();
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);

		$post = JFactory::getApplication()->input->post->getArray(array());
		$oldteamid = JFactory::getApplication()->input->getVar('oldteamid',array(),'post','array');
		$newteamid = JFactory::getApplication()->input->getVar('newteamid',array(),'post','array');

		for ($a=0; $a < sizeof($oldteamid); $a++ )
		{
			$project_team_id = $oldteamid[$a];
			$project_team_id_new = $newteamid[$project_team_id];
            
            // Select some fields
		$query->select('t.name');
        // From table
		$query->from('#__sportsmanagement_team AS t');
        $query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
        $query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query->where('pt.id = ' . $project_team_id);
			$db->setQuery($query);
			$old_team_name = $db->loadResult();
            
            $query->clear();
            $query->select('t.name');
        // From table
		$query->from('#__sportsmanagement_team AS t');
        $query->where('t.id = ' . $project_team_id_new);

//			$query = 'SELECT t.name
//					FROM #__sportsmanagement_team as t
//					WHERE t.id='.$project_team_id_new;
			$db->setQuery($query);
			$new_team_name = $db->loadResult();

			$app->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAM_MODEL_ASSIGNED_OLD_TEAMNAME', $old_team_name, $new_team_name),'Notice');

			$tabelle = '#__sportsmanagement_project_team';
			// Objekt erstellen
			$wertneu = new StdClass();
			// Werte zuweisen
			$wertneu->id = $project_team_id;
			$wertneu->team_id = $project_team_id_new;

			// Neue Werte in den vorher erstellten Datenbankeintrag einf?gen
			$db->updateObject($tabelle, $wertneu, 'id');

		}

	}





	/**
	 * Method to return a Teams array (id,name)
	 *
	 * @access	public
	 * @return	array seasons
	 * @since	1.5.0a
	 */
	function getAllTeams($pid)
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        
	   $this->_season_id = $app->getUserState( "$option.season_id", '0' );
       
       //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projekt_id ->'.$pid.''),'');
//       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _season_id ->'.$this->_season_id.''),'');
       
        $db = sportsmanagementHelper::getDBConnection();

        $query = $db->getQuery(true);
        
		if ( $pid )
		{
			// jetzt brauchen wir noch das land der liga !
            $query->clear();
            $query->select('l.country');
        // From table
		$query->from('#__sportsmanagement_league as l');
        $query->join('INNER', '#__sportsmanagement_project as p on p.league_id = l.id');
        $query->where('p.id = ' . $pid);

			$db->setQuery( $query );
			$country = $db->loadResult();

// teams aus dem projekt selektieren , damit wir sie nicht in der anzeige haben
$query->clear();
// Select some fields
$query->select('team_id');
// From table
$query->from('#__sportsmanagement_project_team');
$query->where('project_id = ' . $pid);
$db->setQuery($query);
$teamresult = $db->loadColumn();
//$app->enqueueMessage(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($teamresult, true).'</pre><br>','Notice');

$query->clear();
$query->select('st.id as value, concat(t.name,\' [\',t.info,\']\' ) as text');
// From table
$query->from('#__sportsmanagement_team as t');
$query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
$query->join('INNER', '#__sportsmanagement_club AS c ON c.id = t.club_id');
$query->where('st.season_id = ' . $this->_season_id);
if ( $teamresult )
{
$query->where('st.id NOT IN (' . implode(",",$teamresult) .')' );

}



        
      if ( $country )
      {
      $query->where('c.country LIKE '.$db->Quote(''.$country.''));
      }
      else
      {
      $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_NO_LEAGUE_COUNTRY'),'Error');
      $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_SELECT_ALL_TEAMS'),'Notice');
      }

		}
		else
		{
		  $query->clear();
            $query->select('t.id as value, concat(t.name,\' [\',t.info,\']\' ) as text');
        // From table
		$query->from('#__sportsmanagement_team as t');
		}
        
        $query->order('t.name ASC');

		$db->setQuery($query);
		if (!$result = $db->loadObjectList())
		{
//			$app->enqueueMessage(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(), true).'</pre><br>','Error');
$app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_NO_CHANGE_TEAMS'),'Notice');			
			return false;
		}
		foreach ($result as $teams)
		{
			$teams->name = JText::_($teams->text);
		}

//$app->enqueueMessage(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($result, true).'</pre><br>','Notice');
		
		return $result;
	
       
	}



	
	/**
	 * sportsmanagementModelProjectteams::getProjectTeams()
	 * 
	 * @param integer $project_id
	 * @param bool $in_used
	 * @return
	 */
	function getProjectTeams($project_id=0,$in_used=FALSE)
	{
        $this->_season_id	= $this->jsmapp->getUserState( "$this->jsmoption.season_id", '0' );
        $this->project_art_id = $this->jsmapp->getUserState( "$this->jsmoption.project_art_id", '0' );
        $this->sports_type_id = $this->jsmapp->getUserState( "$this->jsmoption.sports_type_id", '0' );
        
        if ( isset(self::$_pro_teams_in_used) )
        {
        self::$_pro_teams_in_used = array();
        }
        
        $this->jsmquery->clear();
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $my_text = 'project_id<pre>'.print_r($project_id,true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
        //$app->enqueueMessage(get_class($this).' '.__FUNCTION__.' project_id<br><pre>'.print_r($project_id, true).'</pre><br>','Notice');
        }
        
        if ( $this->project_art_id == 3 )
        {
        // Select some fields
		$this->jsmquery->select("pt.id AS value,concat(t.lastname,' - ',t.firstname,'' ) AS text,t.notes, pt.info");
        // From table
		$this->jsmquery->from('#__sportsmanagement_person AS t');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_season_person_id AS st on st.person_id = t.id');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $this->jsmquery->where('pt.project_id = ' . $project_id);
        $this->jsmquery->order('t.lastname ASC');    
        }
        else
        {    
        // Select some fields
		$this->jsmquery->select('pt.id AS value,t.name AS text,t.notes, pt.info,st.id as season_team_id');
        // From table
		$this->jsmquery->from('#__sportsmanagement_team AS t');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $this->jsmquery->where('pt.project_id = ' . $project_id);
        if ( $in_used && isset(self::$_pro_teams_in_used) )
        {
        $this->jsmquery->where('pt.team_id NOT IN (' . implode(",",self::$_pro_teams_in_used) .')');    
        }
        $this->jsmquery->order('t.name ASC');
        }

		$this->jsmdb->setQuery( $this->jsmquery );
       
        if ( !$result = $this->jsmdb->loadObjectList() )
		{
			return false;
		}
		else
		{
		  foreach( $result as $row )
          {
          self::$_pro_teams_in_used[] = $row->season_team_id;  
          }
          
			return $result;
		}
	}
    
    
    
    /**
     * sportsmanagementModelProjectteams::getAllProjectTeams()
     * 
     * @param integer $projectid
     * @param integer $divisionid
     * @return
     */
    function getAllProjectTeams($projectid=0,$divisionid=0,$team_ids=NULL,$cfg_which_database = 0)
	{
        $starttime = microtime(); 
        
		$teams = array();
        $this->jsmquery->clear();
        $this->jsmquery->select('tl.id AS projectteamid,tl.team_id,tl.picture projectteam_picture,tl.project_id');
        $this->jsmquery->select('t.id,t.name as team_name,t.short_name,t.middle_name,t.club_id,t.website AS team_www,t.picture team_picture');
        $this->jsmquery->select('c.name as club_name,c.address as club_address,c.zipcode as club_zipcode,c.state as club_state,c.location as club_location,c.email as club_email,c.logo_big,c.unique_id,c.logo_small,c.logo_middle,c.country as club_country,c.website AS club_www,c.latitude AS latitude,c.longitude AS longitude');
        $this->jsmquery->from('#__sportsmanagement_project_team as tl ');
        $this->jsmquery->join('INNER','#__sportsmanagement_season_team_id as st ON st.id = tl.team_id ');
        $this->jsmquery->join('LEFT','#__sportsmanagement_team as t ON st.team_id = t.id ');
        $this->jsmquery->join('LEFT','#__sportsmanagement_club as c ON t.club_id = c.id ');
        $this->jsmquery->join('LEFT','#__sportsmanagement_division as d ON d.id = tl.division_id ');
        $this->jsmquery->join('LEFT','#__sportsmanagement_playground as plg ON plg.id = tl.standard_playground');
        
        $this->jsmquery->where('tl.project_id = ' . $projectid);
        
        if ( $team_ids )
		{
            $this->jsmquery->where('st.team_id IN (' . implode(',',$team_ids) .')');
		}

		if ( $divisionid > 0 )
		{
            $this->jsmquery->where('tl.division_id = ' . $divisionid);
		}
        $this->jsmquery->order('t.name');

		$this->jsmdb->setQuery($this->jsmquery);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		if ( !$teams = $this->jsmdb->loadObjectList() )
		{
		  $this->jsmapp->enqueueMessage(JText::_(COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_ERROR_NO_TEAM),'error');
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
		}

		return $teams;
	}

	/**
	 * copy teams to other projects
	 * 
	 * @param int $dest destination project id
	 * @param array $ptids teams to transfer
	 */
	function copy($dest, $ptids)
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        
		if (!$dest)
		{
			$this->setError(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_Destination_project_required'));
			return false;
		}
		
		if (!is_array($ptids) || !count($ptids))
		{
			$this->setError(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_no_teams_to_copy'));
			return false;
		}
		
		// first copy the teams
		$query = ' INSERT INTO #__sportsmanagement_project_team (team_id, project_id, info, picture, standard_playground, extended)' 
		       . ' SELECT team_id, '.$dest.', info, picture, standard_playground, extended '
		       . ' FROM #__sportsmanagement_project_team '
		       . ' WHERE id IN (' . implode(',', $ptids).')';
		$db->setQuery($query);
		$res = $db->execute();
		
		if (!$res) 
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		
		// now copy the players
		$query = ' INSERT INTO #__sportsmanagement_team_player (projectteam_id, person_id, jerseynumber, picture, extended, published) ' 
		       . ' SELECT dest.id AS projectteam_id, tp.person_id, tp.jerseynumber, tp.picture, tp.extended,tp.published '
		       . ' FROM #__sportsmanagement_team_player AS tp '
		       . ' INNER JOIN #__sportsmanagement_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__sportsmanagement_project_team AS dest ON pt.team_id = dest.team_id AND dest.project_id = '.$dest 
		       . ' WHERE pt.id IN (' . implode(',', $ptids).')';
		$db->setQuery($query);
		$res = $db->execute();
				
		// and finally the staff
		$query = ' INSERT INTO #__sportsmanagement_team_staff (projectteam_id, person_id, picture, extended, published) '
				       . ' SELECT dest.id AS projectteam_id, tp.person_id, tp.picture, tp.extended,tp.published '
				       . ' FROM #__sportsmanagement_team_staff AS tp '
				       . ' INNER JOIN #__sportsmanagement_project_team AS pt ON pt.id = tp.projectteam_id '
				       . ' INNER JOIN #__sportsmanagement_project_team AS dest ON pt.team_id = dest.team_id AND dest.project_id = '.$dest 
		. ' WHERE pt.id IN (' . implode(',', $ptids).')';
		$db->setQuery($query);
		$res = $db->execute();
		
		if (!$res) 
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		
		
		return true;
	}

	/**
	 * return count of projectteams
	 *
	 * @param int project_id
	 * @return int
	 */
	function getProjectTeamsCount($project_id)
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        //$db	= JFactory::getDbo();
        $db = sportsmanagementHelper::getDBConnection(); 
		$query = $db->getQuery(true);
        $starttime = microtime(); 
        
        $query->select('count(*) AS count');
        $query->from('#__sportsmanagement_project_team AS pt ');
        $query->join('INNER','#__sportsmanagement_project AS p on p.id = pt.project_id ');
        $query->where('p.id ='. $project_id);
		
        $db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		return $db->loadResult();
	}
	
	
}
?>
