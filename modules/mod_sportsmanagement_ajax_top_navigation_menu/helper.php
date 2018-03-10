<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// no direct access

defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.utilities.arrayhelper' );

/**
 * modSportsmanagementAjaxTopNavigationMenuHelper
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class modSportsmanagementAjaxTopNavigationMenuHelper 
{
	
	protected $_params;
	protected $_db;
    protected $_query;
    protected $_app;

    var $_project_slug = '';
    var $_league_slug = '';
    var $_round_slug = '';
    var $_season_slug = '';
	var $_team_slug = '';
    var $_club_slug = '';
	var $_division_slug = '';
	
	static $_project_id;
  var $_league_id;
	var $_team_id;
	static $_country_fed = array();
	var $_club_id;
	var $_division_id = 0;
	var $_round_id = null;
	
	protected $_teamoptions;
	
	protected $_project;
    
    static $query_getFederations = '';
    static $query_getFederationSelect = '';
    
    static $query_getAssocLeagueSelect = '';

    static $query_getCountryAssocSelect = '';
    static $query_getCountryFederation = '';
    static $query_getCountrySubAssocSelect = '';
    static $query_getCountrySubSubAssocSelect = '';
    
    static $query_getLeagueAssocId = '';
    static $query_getLeagueSelect = '';
    
	
	/**
	 * modSportsmanagementAjaxTopNavigationMenuHelper::__construct()
	 * 
	 * @param mixed $params
	 * @return void
	 */
	public function __construct($params)
	{
		$this->_app = JFactory::getApplication();
        $this->_params = $params;
		$this->_db = sportsmanagementHelper::getDBConnection();
        $this->_query = $this->_db->getQuery(true);
//        $projectid = JRequest::getInt('p',0);
//        if ( $projectid )
//        {
//        self::$_project_id = $projectid ;
//        }
        
    if ( self::$_project_id )
		{

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _project_id<br><pre>'.print_r(self::$_project_id,true).'</pre>'),'Notice');

//JRequest::setVar( 'jlamtopcountry', $this->getProjectCountry(self::$_project_id) );

    JRequest::setVar( 'jlamtopseason', $this->getSeasonId() );
    JRequest::setVar( 'jlamtopleague', $this->getLeagueId() );
    JRequest::setVar( 'jlamtopproject', self::$_project_id );
    JRequest::setVar( 'jlamtopteam', $this->_team_id );
    JRequest::setVar( 'jlamtopdivisionid', $this->_division_id );
    }
    
		
		
		
	}

  
  /**
   * modSportsmanagementAjaxTopNavigationMenuHelper::getFederations()
   * 
   * @return
   */
  public function getFederations()
{
    $app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
        
        $query->select('name,id');
        $query->from('#__sportsmanagement_federations');
        $query->where('published = 1');
        $db->setQuery($query);
        
        self::$query_getFederations = $query->dump();
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
        $result = $db->loadObjectList();
    
    return $result;
    
    }
  
  
  /**
   * modSportsmanagementAjaxTopNavigationMenuHelper::getCountrySubSubAssocSelect()
   * 
   * @param mixed $assoc_id
   * @return
   */
  public function getCountrySubSubAssocSelect($assoc_id)
{
$app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);

        $query->select('s.id AS value, s.name AS text');
$query->from('#__sportsmanagement_associations AS s');
$query->where('s.parent_id = '.$assoc_id);
$query->where('s.published = 1');
$query->order('s.name');

                
		$db->setQuery($query);
        
        $this->getCountrySubSubAssocSelect = $query->dump();
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		$res = $db->loadObjectList();
		if ($res) 
        {
		$options = array(JHTML::_('select.option', 0, JText::_('-- Kreisverbände -- ')));
			$options = array_merge($options, $res);
		}

		return $options;

}

/**
 * modSportsmanagementAjaxTopNavigationMenuHelper::getCountrySubAssocSelect()
 * 
 * @param mixed $assoc_id
 * @return
 */
public function getCountrySubAssocSelect($assoc_id)
{
$app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
        $options = '';
        
        $query->select('s.id AS value, s.name AS text');
$query->from('#__sportsmanagement_associations AS s');
$query->where('s.parent_id = '.$assoc_id);
$query->order('s.name');

		$db->setQuery($query);
        
        $this->getCountrySubAssocSelect = $query->dump();
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		$res = $db->loadObjectList();
		if ($res) {
		$options = array(JHTML::_('select.option', 0, JText::_('-- Landesverbände -- ')));
			$options = array_merge($options, $res);
		}
        else
        {
            $options = array(JHTML::_('select.option', 0, JText::_('-- Landesverbände -- ')));
        }

		return $options;

}
	
	/**
	 * modSportsmanagementAjaxTopNavigationMenuHelper::getCountryAssocSelect()
	 * 
	 * @param mixed $country
	 * @return
	 */
	public function getCountryAssocSelect($country)
	{
$app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
        $options = '';
        
        $query->select('s.id AS value, s.name AS text');
$query->from('#__sportsmanagement_associations AS s');
$query->where('s.country = \''.$country.'\'');
$query->where('s.parent_id = 0');
$query->order('s.name');

		$db->setQuery($query);
        
        $this->getCountryAssocSelect = $query->dump();
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		$res = $db->loadObjectList();
		if ($res) {
		  $options = array(JHTML::_('select.option', 0, JText::_('-- Regionalverbände -- ')));
			$options = array_merge($options, $res);
		}
        else
        {
            $options = array(JHTML::_('select.option', 0, JText::_('-- Regionalverbände -- ')));
        }

		return $options;
	}
	
	/**
	 * modSportsmanagementAjaxTopNavigationMenuHelper::getFederationSelect()
	 * 
	 * @param string $federation
	 * @param integer $federationid
	 * @return
	 */
	public function getFederationSelect($federation='',$federationid=0)
	{
	   $app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
	$options = array();
    
    $fedtext = $federation;

$query->select('s.alpha3 AS value, s.name AS text');
$query->from('#__sportsmanagement_countries AS s');
$query->join('INNER','#__sportsmanagement_league AS l ON l.country = s.alpha3 ');
$query->where('s.federation = '.$federationid);
$query->group('s.name');
$query->order('s.name DESC');
$db->setQuery($query);

self::$query_getFederationSelect = $query->dump();
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');

$res = $db->loadObjectList();

if ($res) 
    {

    foreach ( $res as $row )
			{
      $row->text = JText::_($row->text);
      }
      
      $res = JArrayHelper::sortObjects($res,'text',1);
      
		  $options = array(JHTML::_('select.option', 0, JText::_($fedtext)));
			$options = array_merge($options, $res);
			
			foreach ( $res as $row )
			{
      self::$_country_fed[$row->value] = $fedtext;
      }
			
		}
		

		return $options;
	}
	
    
    
    
	/**
	 * modSportsmanagementAjaxTopNavigationMenuHelper::getCountryFederation()
	 * 
	 * @param mixed $country_id
	 * @return
	 */
	public function getCountryFederation($country_id)
	{
	   $app = JFactory::getApplication();
       $result = FALSE;
       //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' country_id<br><pre>'.print_r($country_id,true).'</pre>'),'Notice');
       if ( $country_id )
       {
       $result = self::$_country_fed[$country_id]; 
       }
  return $result;
  }
	
  /**
   * modSportsmanagementAjaxTopNavigationMenuHelper::getQueryValues()
   * 
   * @return
   */
  public function getQueryValues()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
       
       //$test = $jinput->get('p', 0, 'INT');
      // $test = $jinput->request->get('p', 0, 'INT');
       
       //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' test<pre>'.print_r($test,true).'</pre><br>'),'');
       //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' input<pre>'.print_r($jinput,true).'</pre><br>'),'');
       
       $uri = JFactory::getURI();
       
       $varAdd_array = array();
       
       $varAdd_array['option'] = $jinput->request->get('option', 0, 'STR');
       
       $varAdd_array['view'] = $jinput->request->get('view', 0, 'STR');
       $varAdd_array['cfg_which_database'] = $jinput->request->get('cfg_which_database', 0, 'INT');
       $varAdd_array['s'] = $jinput->request->get('s', 0, 'INT');
       $varAdd_array['p'] = $jinput->request->get('p', 0, 'INT');
       $varAdd_array['division'] = $jinput->request->get('division', 0, 'INT');
       $varAdd_array['type'] = $jinput->request->get('type', 0, 'INT');
       $varAdd_array['r'] = $jinput->request->get('r', 0, 'INT');
       $varAdd_array['from'] = $jinput->request->get('from', 0, 'INT');
       $varAdd_array['to'] = $jinput->request->get('to', 0, 'INT');
       
       $varAdd_array['mid'] = $jinput->request->get('mid', 0, 'INT');
       $varAdd_array['tid'] = $jinput->request->get('tid', 0, 'INT');
       $varAdd_array['cid'] = $jinput->request->get('cid', 0, 'INT');
       
           
       $varAdd_array['Itemid'] = $jinput->request->get('Itemid', 0, 'INT');
       
//       $router = JSite::getRouter();
//   $var = $router->getVars();
//   $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' var<pre>'.print_r($var,true).'</pre><br>'),'');
//   
//   $query_string_array = JRequest::get( 'get' ); 
//   $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_string_array<pre>'.print_r($query_string_array,true).'</pre><br>'),'');
   
//   $url = $_SERVER["REQUEST_URI"]; 
//   $u = JURI::getInstance( $url );
//   $var = $router->_parseRawRoute($u);
//   $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' var<pre>'.print_r($var,true).'</pre><br>'),'');
   
	// diddipoeler
	/*
	muss ich erstmal so machen, da die request variablen falsch 
	uebernommen werden.
	kommt vor, wenn das modul nach einen anderen modul dargestellt wird
	*/

/*	
	$url = $_SERVER["REQUEST_URI"]; 
    $parsearray = parse_url($url);
    $startseo = 0;
    $jltemplate = '';
    $varAdd_array = array();
    
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' url<pre>'.print_r($url,true).'</pre><br>'),'');
    
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' uri<pre>'.print_r($uri->getQuery(),true).'</pre><br>'),'');
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' parsearray<pre>'.print_r($parsearray,true).'</pre><br>'),'');
    
    if ( isset($parsearray['query']) )
    {
    $varAdd = explode('&', $parsearray['query']);
        foreach($varAdd as $varOne)
        {
            $name_value = explode('=', $varOne);
            
            //$app->enqueueMessage(JText::_('name_value -> <pre>'.print_r($name_value[0],true).'</pre><br>'),'');
            //$app->enqueueMessage(JText::_('name_value -> <pre>'.print_r($name_value[1],true).'</pre><br>'),'');
            
            switch ($name_value[0])
            {
            case 'p':
            case 'r':
            case 'division':
            case 'mode':
            case 'mid':
            case 'tid':
            case 'pid':
            case 'cid':
            $varAdd_array[$name_value[0]] = (int) $name_value[1];
            break;
            case 'l':
            $project = $this->getProject($name_value[1]);
            //$project = $this->getProjectSelect($name_value[1]);
            $varAdd_array['p'] = self::$_project_id;
            break;
            default:
            $varAdd_array[$name_value[0]] = $name_value[1];
            break;
            }
            
       }

       //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' varAdd_array<pre>'.print_r($varAdd_array,true).'</pre><br>'),'');
       //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project<pre>'.print_r($project,true).'</pre><br>'),'');

    }
    else
    {
    $varAdd = explode('/', $parsearray['path']);    
    
    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' varAdd<pre>'.print_r($varAdd,true).'</pre><br>'),'');
    
    foreach( $varAdd as $key => $value )
    {
    
    if ( $value == 'sportsmanagement' )
    {
    $startseo = $key + 1;
    $jltemplate = $varAdd[$startseo];
    
    //echo 'jltemplaterequest queries -> <pre>'.print_r($jltemplate,true).'</pre><br>';
    $app->enqueueMessage(JText::_('jltemplaterequest queries -> <pre>'.print_r($jltemplate,true).'</pre><br>'),'');
    
    switch ($jltemplate)
    {
    case 'clubinfo':
    case 'clubplan':
    $varAdd_array['p'] = $varAdd[$startseo + 1];
    $varAdd_array['cid'] = $varAdd[$startseo + 2];
    break;
    
    case 'roster':
    case 'teamplan':
    case 'teaminfo':
    case 'teamstats':
    case 'curve':
    case 'rosteralltime':
    $varAdd_array['p'] = $varAdd[$startseo + 1];
    $varAdd_array['tid'] = $varAdd[$startseo + 2];
    break;
    
    case 'ranking':
    $varAdd_array['p'] = $varAdd[$startseo + 1];
    $varAdd_array['r'] = $varAdd[$startseo + 3];
    break;
    
    case 'rankingalltime':
    //getProject($varAdd[$startseo + 1]);
    break;
    
    case 'results':
    case 'resultsmatrix':
    case 'resultsranking':
    case 'jlallprojectrounds':
    case 'jltournamenttree':
    $varAdd_array['p'] = $varAdd[$startseo + 1];
    $varAdd_array['r'] = $varAdd[$startseo + 2];
    break;
    
    case 'matchreport':
    case 'nextmatch':
    $varAdd_array['p'] = $varAdd[$startseo + 1];
    $varAdd_array['mid'] = $varAdd[$startseo + 2];
    break;
    
    case 'eventsranking':
    case 'matrix':
    case 'referees':
    case 'stats':
    case 'teams':
    case 'statsranking':
    $varAdd_array['p'] = $varAdd[$startseo + 1];
    break;
        
    }   
     
    }   
     
    }   
     
    }  
*/       
//     echo 'request queries -> <pre>'.print_r($varAdd_array,true).'</pre><br>';
	  
      
      
      return $varAdd_array;
	}
	
	/**
	 * modSportsmanagementAjaxTopNavigationMenuHelper::setProject()
	 * 
	 * @param mixed $project_id
	 * @param mixed $team_id
	 * @param mixed $division_id
	 * @return void
	 */
	public function setProject($project_id,$team_id,$division_id)
	{
	self::$_project_id 		= $project_id;
	//$this->_division_id = $division_id;
	$this->_team_id 		= $team_id;
	$this->_project 		= $this->getProject();
	$this->_round_id   		= $this->getCurrentRoundId();
  JRequest::setVar( 'jlamtopseason', $this->getSeasonId() );
  JRequest::setVar( 'jlamtopleague', $this->getLeagueId() );
  JRequest::setVar( 'jlamtopproject', self::$_project_id );
  JRequest::setVar( 'jlamtopteam', $this->_team_id );
  //JRequest::setVar( 'jlamdivisionid', $this->_division_id );
	}
	
	/**
	 * modSportsmanagementAjaxTopNavigationMenuHelper::setDivisionID()
	 * 
	 * @param mixed $division_id
	 * @return void
	 */
	public function setDivisionID($division_id)
	{
	
	$this->_division_id = $division_id;

// echo 'setDivisionID <br>';
// echo '_division_id ->'.$this->_division_id.'<br>';
	
	}
	
	/**
	 * modSportsmanagementAjaxTopNavigationMenuHelper::setTeamID()
	 * 
	 * @param mixed $team_id
	 * @return void
	 */
	public function setTeamID($team_id)
	{
	
	$this->_team_id = $team_id;

// echo 'setTeamID <br>';
// echo '_team_id ->'.$this->_team_id.'<br>';
	
	}
	
	
  /**
   * modSportsmanagementAjaxTopNavigationMenuHelper::getCurrentRoundId()
   * 
   * @return
   */
  public function getCurrentRoundId()
	{
		if ($this->getProject()) {
			return $this->getProject()->current_round;
		}
		else {
			return 0;
		}
	}
  
  /**
   * modSportsmanagementAjaxTopNavigationMenuHelper::getUserName()
   * 
   * @return
   */
  public function getUserName()
{
$user = JFactory::getUser();
$this->_user_name = $user->username;
return $user->username ;
}

  /**
   * modSportsmanagementAjaxTopNavigationMenuHelper::getSeasonId()
   * 
   * @return
   */
  public function getSeasonId()
	{
		if ($this->getProject()) {
			return $this->getProject()->season_id;
		}
		else {
			return 0;
		}
	}
	
	/**
	 * modSportsmanagementAjaxTopNavigationMenuHelper::getAssocParentId()
	 * 
	 * @param mixed $assoc_id
	 * @return
	 */
	public function getAssocParentId($assoc_id)
	{
	   $app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
       if ( $assoc_id )
       { 
        $query->select('parent_id');
            $query->from('#__sportsmanagement_associations ');
            $query->where('id = '. $assoc_id );

		$db->setQuery($query);
        
        $this->getAssocParentId = $query->dump();
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		$res = $db->loadResult();
		if ( $res )
    {
    return $res;
    }
    else
    {
    return false;
    }
    }
    else
    {
        return false;
    }
  }
  
	/**
	 * modSportsmanagementAjaxTopNavigationMenuHelper::getLeagueAssocId()
	 * 
	 * @return
	 */
	public function getLeagueAssocId()
	{
	   $app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
    
    if ( $this->_league_id )
    {    
        $query->select('associations');
            $query->from('#__sportsmanagement_league ');
            $query->where('id = '. $this->_league_id );
                
		$db->setQuery($query);
        
        $this->getLeagueAssocId = $query->dump();
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		$res = $db->loadResult();
		if ( $res )
    {
    return $res;
    }
    else
    {
    return false;
    }
    }
    else
    {
    return false;    
    }
    
	}
	
	/**
	 * modSportsmanagementAjaxTopNavigationMenuHelper::getLeagueId()
	 * 
	 * @return
	 */
	public function getLeagueId()
	{
		if ($this->getProject()) {
		  $this->_league_id = $this->getProject()->league_id;
			return $this->getProject()->league_id;
		}
		else {
			return 0;
		}
	}

	/**
	 * modSportsmanagementAjaxTopNavigationMenuHelper::getDivisionId()
	 * 
	 * @return
	 */
	public function getDivisionId()
	{
		return $this->_division_id;
	}

  /**
   * modSportsmanagementAjaxTopNavigationMenuHelper::getClubId()
   * 
   * @return
   */
  public function getClubId()
	{
	   $app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
        
        $query->select('t.club_id');
        $query->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
        $query->select('CONCAT_WS(\':\',c.id,c.alias) AS club_slug');
            $query->from('#__sportsmanagement_team as t');
            $query->join('INNER','#__sportsmanagement_club c ON t.club_id = c.id ');
            $query->where('t.id = '. $this->_team_id );
            
                
		$db->setQuery($query);
        
        $this->getClubId = $query->dump();
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		$res = $db->loadObject();
    
    if ( $res )
    {
    $this->_club_id = $res->club_id;
    $this->_club_slug = $res->club_slug;
    $this->_team_slug = $res->team_slug;
    return $this->_club_id;
    }
    else
    {
    return false;
    }
    
    
	}
	
	/**
	 * modSportsmanagementAjaxTopNavigationMenuHelper::getFavTeams()
	 * 
	 * @param mixed $project_id
	 * @return
	 */
	public function getFavTeams($project_id)
	{
  $app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
        
        $query->select('fav_team');
            $query->from('#__sportsmanagement_project');
            $query->where('id = '. $project_id );

		$db->setQuery($query);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		$teams = $db->loadResult();
		
    //echo 'teams -><pre>'.print_r($teams,true).'</pre><br>';
		
		if ( $teams )
		{
		  $query->clear();
          $query->select('t.id as team_id, t.name, t.club_id');
            $query->from('#__sportsmanagement_team as t');
            $query->where('t.id in ('. $teams . ')' );
   	
				$db->setQuery($query);
                
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
                
				$res = $db->loadObjectList();
				return $res;
    }
    else
    {
    return false;
    }
    
    
  }
  
  
	/**
	 * modSportsmanagementAjaxTopNavigationMenuHelper::getTeamId()
	 * 
	 * @param mixed $project_id
	 * @param mixed $club_id
	 * @return
	 */
	public function getTeamId($project_id,$club_id)
	{
	   $app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
        
        $query->select('pt.team_id');
            $query->from('#__sportsmanagement_project_team AS pt');
            $query->join('INNER','#__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
            $query->join('INNER','#__sportsmanagement_team AS t ON t.id = st.team_id');
            
            $query->where('pt.project_id = '.intval($project_id));
            $query->where('t.club_id = '.$club_id);

                
		$db->setQuery($query);
        
        $this->getTeamId = $query->dump();
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		$res = $db->loadResult();
    
    if ( $res )
    {
    $this->_team_id = $res;
    return $this->_team_id;
    }
    else
    {
    return false;
    }
    
    
//     return $this->_team_id;
	}
	
	/**
	 * returns the selector for season
	 *
	 * @return string html select
	 */
	public function getSeasonSelect()
	{
	   $app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
        
		$options = array(JHTML::_('select.option', 0, JText::_($this->getParam('seasons_text'))));
        
        $query->select('s.id AS value, s.name AS text');
            $query->from('#__sportsmanagement_season AS s');
            $query->where('s.published = 1');
            $query->order('s.name DESC');

                
		$db->setQuery($query);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		$res = $db->loadObjectList();
		if ($res) {
			$options = array_merge($options, $res);
		}
		//return JHTML::_('select.genericlist', $options, 's', 'class="jlajaxmenu-select" onchange="jlamnewproject('.$module->id.');"', 'value', 'text', $this->getSeasonId());
		return $options;
	}	
	
	/**
	 * returns the selector for division
	 * 
	 * @return string html select
	 */
	public function getDivisionSelect($project_id)
	{		
	   $app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
$options = array();

		$query = ' SELECT d.id AS value, d.name AS text ' 
		       . ' FROM #__sportsmanagement_division AS d ' 
		       . ' WHERE d.project_id = ' .  $project_id 
		       . ($this->getParam("show_only_subdivisions", 0) ? ' AND parent_id > 0' : '') 
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		if ($res) 
    {
    $options = array(JHTML::_('select.option', 0, JText::_($this->getParam('divisions_text'))));
		$options = array_merge($options, $res);
		}
//		return JHTML::_('select.genericlist', $options, 'd', 'class="jlnav-division"', 'value', 'text', $this->getDivisionId());
    return $options;
	}
	
    /**
	 * returns the selector for league
	 * 
	 * @return string html select
	 */
	public function getAssocLeagueSelect($country_id,$associd)
	{		
$app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
        
        $query->select('l.id AS value, l.name AS text');
            $query->from('#__sportsmanagement_league AS l');
            $query->join('INNER','#__sportsmanagement_project AS p on l.id = p.league_id');
            $query->join('INNER','#__sportsmanagement_season AS s on s.id = p.season_id ');
            $query->where('l.associations = ' . $associd );
            $query->where('l.country = \'' . $country_id. '\'' );
            $query->group('l.name');
            $query->order('l.name');
/*
select l.id AS value, l.name AS text
from j25_sportsmanagement_league AS l
inner join j25_sportsmanagement_project AS p on l.id = p.league_id
inner join j25_sportsmanagement_season AS s on s.id = p.season_id
where l.associations = 0
and l.country = '889'
group by l.name
order by l.name

*/

		$db->setQuery($query);
        
        $this->getAssocLeagueSelect = $query->dump();
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		$res = $db->loadObjectList();
		if ($res) 
        {
        $options = array(JHTML::_('select.option', 0, JText::_($this->getParam('leagues_text'))));
			$options = array_merge($options, $res);
		}

		return $options;
	}
    
    /**
     * modSportsmanagementAjaxTopNavigationMenuHelper::getProjectCountry()
     * 
     * @param mixed $project_id
     * @return
     */
    public function getProjectCountry($project_id)
    {
        $app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
        $query->select('l.country');
            $query->from('#__sportsmanagement_league AS l');
            $query->join('INNER','#__sportsmanagement_project AS p on l.id = p.league_id');
            $query->where('p.id = ' . $project_id );

		$db->setQuery($query);
        
        $this->getProjectCountry = $query->dump();
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		$res = $db->loadResult();
    
    if ( $res )
    {
    return $res;
    }
    else
    {
    return false;
    }    
        
    }
    
	/**
	 * returns the selector for league
	 * 
	 * @return string html select
	 */
	public function getLeagueSelect($season)
	{		
	   $app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
        
        $query->select('l.id AS value, l.name AS text');
            $query->from('#__sportsmanagement_league AS l');
            $query->join('INNER','#__sportsmanagement_project AS p on l.id = p.league_id');
            $query->join('INNER','#__sportsmanagement_season AS s on s.id = p.season_id ');
            $query->where('s.id = ' . $season );
            $query->where('s.published = 1');
            $query->where('l.published = 1');
            $query->group('l.name');
            $query->order('l.name');
              
               
		$db->setQuery($query);
        
        $this->getLeagueSelect = $query->dump();
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		$res = $db->loadObjectList();
		if ($res) 
        {
        $options = array(JHTML::_('select.option', 0, JText::_($this->getParam('leagues_text'))));
			$options = array_merge($options, $res);
		}
//		return JHTML::_('select.genericlist', $options, 'l', 'class="jlnav-select"', 'value', 'text', $this->getLeagueId());
		return $options;
	}

	/**
	 * returns the selector for project
	 * 
	 * @return string html select
	 */
	public function getProjectSelect($league_id)
	{
$app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
        
        $query->select('p.id AS value, p.name AS text');
            $query->from('#__sportsmanagement_project AS p');
            $query->join('INNER','#__sportsmanagement_season AS s on s.id = p.season_id ');
            $query->join('INNER','#__sportsmanagement_league AS l on l.id = p.league_id');
            $query->where('p.published = 1');
            $query->where('p.league_id = '. $league_id);
            $query->order('s.name DESC');
            
            
//		$query_base = ' SELECT p.id AS value, p.name AS text ' 
//		       . ' FROM #__joomleague_project AS p ' 
//		       . ' INNER JOIN #__joomleague_season AS s on s.id = p.season_id '
//		       . ' INNER JOIN #__joomleague_league AS l on l.id = p.league_id '
//		       . ' WHERE p.published = 1 ';
//		       . ' ORDER BY s.name DESC ';
		       
//      $query = $query_base;
//		if ($this->getParam('show_project_dropdown') == 'season' && $this->getProject()) 
//		{
			//$query .= ' AND p.season_id = '. $season_id;
//			$query .= ' AND p.league_id = '. $league_id;
//			$query .= ' ORDER BY s.name DESC ';
//		}
		
//		switch ($this->getParam('project_ordering', 0)) 
//		{
//			case 0:
//				$query .= ' ORDER BY p.ordering ASC';				
//			break;
//			
//			case 1:
//				$query .= ' ORDER BY p.ordering DESC';				
//			break;
//			
//			case 2:
//				$query .= ' ORDER BY s.ordering ASC, l.ordering ASC, p.ordering ASC';				
//			break;
//			
//			case 3:
//				$query .= ' ORDER BY s.ordering DESC, l.ordering DESC, p.ordering DESC';				
//			break;
//			
//			case 4:
//				$query .= ' ORDER BY s.name DESC';				
//			break;
//			
//			case 5:
//				$query .= ' ORDER BY p.name DESC';				
//			break;
//		}
		
		$db->setQuery($query);
        
        $this->getProjectSelect = $query->dump();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		$res = $db->loadObjectList();
		
		if ($res) 
		{

$options = array(JHTML::_('select.option', 0, JText::_($this->getParam('text_project_dropdown'))));
					$options = array_merge($options, $res);

		}

        return $options;		
	}

	/**
	 * returns the selector for teams
	 * 
	 * @return string html select
	 */
	public function getTeamSelect($project_id)
	{
//		if (!self::$_project_id) {
//			return false;
//		}
//		$options = array(JHTML::_('select.option', 0, JText::_($this->getParam('text_teams_dropdown'))));
		$res = $this->getTeamsOptions($project_id);
		if ($res) 
		{
		$options = array(JHTML::_('select.option', 0, JText::_($this->getParam('text_teams_dropdown'))));
			$options = array_merge($options, $res);
		}
//		return JHTML::_('select.genericlist', $options, 'tid', 'class="jlnav-team"', 'value', 'text', $this->getTeamId());
        return $options;		
	}
	
	/**
	 * returns select for project teams
	 * 
	 * @return string html select
	 */
	protected function getTeamsOptions($project_id)
	{
	   $app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
        
		if (empty($this->_teamoptions))
		{

			$query->select('t.id AS value, t.name AS text');
            $query->from('#__sportsmanagement_project_team AS pt');
            $query->join('INNER','#__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
            $query->join('INNER','#__sportsmanagement_team AS t ON t.id = st.team_id');
            
            $query->where('pt.project_id = '.intval($project_id));
            $query->order('t.name ASC');
              
			$db->setQuery($query);
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
            
			$res = $db->loadObjectList();
			
			if (!$res) 
            {
				Jerror::raiseWarning(0, $db->getErrorMsg());
			}
			$this->_teamoptions = $res;			
		}
		return $this->_teamoptions;
	}
	
	/**
	 * return info for current project
	 * 
	 * @return object
	 */
	public function getProject($league_id = 0)
	{
	   $app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _project<br><pre>'.print_r($this->_project,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _project_id<br><pre>'.print_r(self::$_project_id,true).'</pre>'),'Notice');
        
		if (!$this->_project)
		{
			if (!self::$_project_id && !$league_id ) 
            {
				return false;
			}
			
            $query->select('p.id, p.name,p.league_id');
            $query->select('CONCAT_WS(\':\',p.id,p.alias) AS project_slug');
            $query->select('CONCAT_WS(\':\',s.id,s.alias) AS saeson_slug');
            $query->select('CONCAT_WS(\':\',l.id,l.alias) AS league_slug');
            $query->select('CONCAT_WS(\':\',r.id,r.alias) AS round_slug');
            $query->select('p.season_id, p.league_id, p.current_round');
            
            $query->from('#__sportsmanagement_project AS p');
          $query->join('INNER','#__sportsmanagement_season AS s on s.id = p.season_id');
            
            $query->join('INNER','#__sportsmanagement_league AS l on l.id = p.league_id');
            //$query->join('INNER','#__sportsmanagement_round AS r on p.id = r.project_id ');
            $query->join('LEFT','#__sportsmanagement_round AS r on p.current_round = r.id ');
            
//          $query->where('p.id = ' . self::$_project_id);
         
         if ( $league_id )
         {
            $query->where('p.league_id = ' . $league_id);
         }
         else
         {
            $query->where('p.id = ' . self::$_project_id);
         }
			$db->setQuery($query);
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
            
			$this->_project = $db->loadObject();
            self::$_project_id = $this->_project->id;
			$this->_project_slug = $this->_project->project_slug;
        $this->_saeson_slug = $this->_project->saeson_slug;
        $this->_league_slug = $this->_project->league_slug;
        $this->_round_slug = $this->_project->round_slug;
		}
        
		return $this->_project;
	}
	
	
  /**
   * modSportsmanagementAjaxTopNavigationMenuHelper::getLinkFavTeam()
   * 
   * @param mixed $view
   * @param mixed $team_id
   * @param mixed $club_id
   * @return
   */
  public function getLinkFavTeam($view,$team_id,$club_id)
	{
	switch ($view)
		{								
				
				
			case "roster":
				
				$link = sportsmanagementHelperRoute::getPlayersRoute( self::$_project_id, $team_id );
				break;
				
				
			case "teaminfo":
				
				$link = sportsmanagementHelperRoute::getTeamInfoRoute( self::$_project_id, $team_id );
				break;				
				
			case "teamplan":
				
				$link = sportsmanagementHelperRoute::getTeamPlanRoute( self::$_project_id, $team_id, $this->_division_id );
				break;		
				
			case "clubinfo":
				
				$this->getClubId();
				$link = sportsmanagementHelperRoute::getClubInfoRoute( self::$_project_id, $club_id );
				break;
      case "clubplan":
				
				$this->getClubId();
				$link = sportsmanagementHelperRoute::getClubPlanRoute( self::$_project_id, $club_id );
				break;	
      
        	
			case "teamstats":
				
				$link = sportsmanagementHelperRoute::getTeamStatsRoute( self::$_project_id, $team_id );
				break;
				
			
								
			
		}
		return $link;
	
	}
  
  /**
	 * return link for specified view - allow seo consistency
	 * 
	 * @param string $view
	 * @return string url
	 */
	public function getLink($view)
	{
		if (!self::$_project_id) {
			return false;
		}
		
// echo 'getLink <br>';
// echo 'round_id ->'.$this->_round_id.'<br>';
// echo 'project_id ->'.self::$_project_id.'<br>';
// echo 'division_id ->'.$this->_division_id.'<br>';
// echo 'team_id ->'.$this->_team_id.'<br>';		
		
		switch ($view)
		{								
			case "calendar":
				$link = sportsmanagementHelperRoute::getTeamPlanRoute( $this->_project_slug, $this->_team_slug, $this->_division_id );
				break;	
				
			case "curve":
				$link = sportsmanagementHelperRoute::getCurveRoute( $this->_project_slug, $this->_team_slug, 0, $this->_division_id );
				break;
				
			case "eventsranking":				
				$link = sportsmanagementHelperRoute::getEventsRankingRoute( $this->_project_slug, $this->_division_id, $this->_team_slug );
				break;

			case "matrix":
            $routeparameter = array();
$routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database',0);
$routeparameter['s'] = JRequest::getInt('s',0);
$routeparameter['p'] = $this->_project_slug;
$routeparameter['division'] = $this->_division_id;
$routeparameter['r'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matrix',$routeparameter);
				//$link = sportsmanagementHelperRoute::getMatrixRoute( $this->_project_slug, $this->_division_id );
				break;
				
			case "referees":
				$link = sportsmanagementHelperRoute::getRefereesRoute( $this->_project_slug);
				break;
				
			case "results":
            $routeparameter = array();
$routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database',0);
$routeparameter['s'] = JRequest::getInt('s',0);
$routeparameter['p'] = $this->_project_slug;
$routeparameter['r'] = $this->_round_slug;
$routeparameter['division'] = $this->_division_id;
$routeparameter['mode'] = 0;
$routeparameter['order'] = '';
$routeparameter['layout'] = '';
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('results',$routeparameter);
				//$link = sportsmanagementHelperRoute::getResultsRoute( $this->_project_slug, $this->_round_slug, $this->_division_id );
				break;
				
			case "resultsmatrix":
				$link = sportsmanagementHelperRoute::getResultsMatrixRoute( $this->_project_slug, $this->_round_slug, $this->_division_id  );
				break;

			case "resultsranking":
            $routeparameter = array();
$routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database',0);
$routeparameter['s'] = JRequest::getInt('s',0);
$routeparameter['p'] = $this->_project_slug;
$routeparameter['r'] = $this->_round_slug;
$routeparameter['division'] = $this->_division_id;
$routeparameter['mode'] = 0;
$routeparameter['order'] = '';
$routeparameter['layout'] = '';
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsranking',$routeparameter);
				//$link = sportsmanagementHelperRoute::getResultsRankingRoute( $this->_project_slug, $this->_round_slug, $this->_division_id  );
				break;
			
            case "rankingalltime":
            $link = sportsmanagementHelperRoute::getRankingAllTimeRoute( $this->_league_id, $this->getParam('show_alltimetable_points'), $this->_project_slug);
 		         break;
                 
			case "resultsrankingmatrix":
				$link = sportsmanagementHelperRoute::getResultsRankingMatrixRoute( $this->_project_slug, $this->_round_slug, $this->_division_id  );
				break;
				
			case "roster":
				if (!$this->_team_id) {
					return false;
				}
				$link = sportsmanagementHelperRoute::getPlayersRoute( $this->_project_slug, $this->_team_slug );
				break;
				
			case "rosteralltime":
				if (!$this->_team_id) {
					return false;
				}
				$link = sportsmanagementHelperRoute::getPlayersRouteAllTime( $this->_project_slug, $this->_team_slug );
				break;	
				
			case "stats":
				$link = sportsmanagementHelperRoute::getStatsRoute( $this->_project_slug, $this->_division_id );
				break;
				
			case "statsranking":
				$link = sportsmanagementHelperRoute::getStatsRankingRoute( $this->_project_slug, $this->_division_id );
				break;
				
			case "teaminfo":
				if (!$this->_team_id) {
					return false;
				}
				$link = sportsmanagementHelperRoute::getTeamInfoRoute( $this->_project_slug, $this->_team_slug );
				break;				
				
			case "teamplan":
				if (!$this->_team_id) {
					return false;
				}
				$link = sportsmanagementHelperRoute::getTeamPlanRoute( $this->_project_slug, $this->_team_slug, $this->_division_id );
				break;		
				
			case "clubinfo":
				if (!$this->_team_id) 
        {
					return false;
				}
				$this->getClubId();
				$link = sportsmanagementHelperRoute::getClubInfoRoute( $this->_project_slug, $this->_club_slug );
				break;
                
      case "clubplan":
				if (!$this->_team_id) {
					return false;
				}
				$this->getClubId();
				$link = sportsmanagementHelperRoute::getClubPlanRoute( $this->_project_slug, $this->_club_slug);
				break;	
        
        	
			case "teamstats":
				if (!$this->_team_id) {
					return false;
				}
				$link = sportsmanagementHelperRoute::getTeamStatsRoute( $this->_project_slug, $this->_team_slug );
				break;
                
            case "teams":
$routeparameter = array();
$routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database',0);
$routeparameter['s'] = JRequest::getInt('s',0);
$routeparameter['p'] = $this->_project_slug;
$routeparameter['division'] = $this->_division_id;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('teams',$routeparameter);            
				//$link = sportsmanagementHelperRoute::getTeamsRoute( $this->_project_slug,$this->_division_id );
				break;    
				
			case "treetonode":
				$link = sportsmanagementHelperRoute::getBracketsRoute( $this->_project_slug);
				break;
                
            case "jltournamenttree":
				$link = sportsmanagementHelperRoute::getTournamentRoute( $this->_project_slug, $this->_round_slug );
				break;    
                
                case "jlallprojectrounds":
				$link = sportsmanagementHelperRoute::getAllProjectrounds( $this->_project_slug, $this->_round_slug );
				break;
				
			case "separator":
				return false;
								
			default:
			case "ranking":
            $routeparameter = array();
$routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database',0);
$routeparameter['s'] = JRequest::getInt('s',0);
$routeparameter['p'] = $this->_project_slug;
$routeparameter['type'] = 0;
$routeparameter['r'] = $this->_round_slug;
$routeparameter['from'] = 0;
$routeparameter['to'] = 0;
$routeparameter['division'] = $this->_division_id;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking',$routeparameter);
				//$link = sportsmanagementHelperRoute::getRankingRoute( $this->_project_slug, $this->_round_slug,null,null,0,$this->_division_id );
		}
		return $link;
	}
	
	/**
	 * return param value or default if not found
	 * 
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	protected function getParam($name, $default = null)
	{
		return $this->_params->get($name, $default);
	}
	
	
}
?>