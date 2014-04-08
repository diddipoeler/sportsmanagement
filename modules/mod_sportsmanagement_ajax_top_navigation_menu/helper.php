<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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

// 	protected $_project_id;
// 	protected $_team_id;
// 	protected $_division_id = 0;
// 	protected $_round_id = null;
    var $_project_slug = '';
    var $_league_slug = '';
    var $_round_slug = '';
    var $_season_slug = '';
	var $_team_slug = '';
    var $_club_slug = '';
	var $_division_slug = '';
	
	var $_project_id;
  var $_league_id;
	var $_team_id;
	var $_country_fed = array();
	var $_club_id;
	var $_division_id = 0;
	var $_round_id = null;
	
	protected $_teamoptions;
	
	protected $_project;
	
	/**
	 * modSportsmanagementAjaxTopNavigationMenuHelper::__construct()
	 * 
	 * @param mixed $params
	 * @return void
	 */
	public function __construct($params)
	{
		$this->_params = $params;
		$this->_db = Jfactory::getDBO();
        
    if ( $this->_project_id )
		{
    JRequest::setVar( 'jlamtopseason', $this->getSeasonId() );
    JRequest::setVar( 'jlamtopleague', $this->getLeagueId() );
    JRequest::setVar( 'jlamtopproject', $this->_project_id );
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
    $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);
        
        $query->select('name,id');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_federations');
        $db->setQuery($query);
        
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
$mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);

        $query->select('s.id AS value, s.name AS text');
$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_associations AS s');
$query->where('s.parent_id = '.$assoc_id);
$query->order('s.name');

                
		$db->setQuery($query);
		$res = $db->loadObjectList();
		if ($res) {
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
$mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);
        
        $query->select('s.id AS value, s.name AS text');
$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_associations AS s');
$query->where('s.parent_id = '.$assoc_id);
$query->order('s.name');

		$db->setQuery($query);
		$res = $db->loadObjectList();
		if ($res) {
		$options = array(JHTML::_('select.option', 0, JText::_('-- Landesverbände -- ')));
			$options = array_merge($options, $res);
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
$mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);
        
        $query->select('s.id AS value, s.name AS text');
$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_associations AS s');
$query->where('s.country = \''.$country.'\'');
$query->where('s.parent_id = 0');
$query->order('s.name');

		$db->setQuery($query);
		$res = $db->loadObjectList();
		if ($res) {
		  $options = array(JHTML::_('select.option', 0, JText::_('-- Regionalverbände -- ')));
			$options = array_merge($options, $res);
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
	   $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);
	$options = array();
    
    $fedtext = $federation;

$query->select('s.alpha3 AS value, s.name AS text');
$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_countries AS s');
$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l ON l.country = s.alpha3 ');
$query->where('s.federation = '.$federationid);
$query->order('s.name DESC');
$db->setQuery($query);
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
      $this->_country_fed[$row->value] = $fedtext;
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
  return $this->_country_fed[$country_id];
  }
	
  /**
   * modSportsmanagementAjaxTopNavigationMenuHelper::getQueryValues()
   * 
   * @return
   */
  public function getQueryValues()
	{
	// diddipoeler
	/*
	muss ich erstmal so machen, da die request variablen falsch 
	uebernommen werden.
	kommt vor, wenn das modul nach einen anderen modul dargestellt wird
	*/
	
	$url = $_SERVER["REQUEST_URI"]; 
    $parsearray = parse_url($url);
    $startseo = 0;
    $jltemplate = '';
    $varAdd_array = array();
    
    if ( $parsearray['query'] )
    {
    $varAdd = explode('&', $parsearray['query']);
        foreach($varAdd as $varOne)
        {
            $name_value = explode('=', $varOne);
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
            default:
            $varAdd_array[$name_value[0]] = $name_value[1];
            break;
            }
            
       }
       //echo 'jltemplaterequest queries -> <pre>'.print_r($varAdd_array,true).'</pre><br>';
    }
    else
    {
    $varAdd = explode('/', $parsearray['path']);    
    
    foreach( $varAdd as $key => $value )
    {
    
    if ( $value == 'joomleague' )
    {
    $startseo = $key + 1;
    $jltemplate = $varAdd[$startseo];
    
    //echo 'jltemplaterequest queries -> <pre>'.print_r($jltemplate,true).'</pre><br>';
    
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
	$this->_project_id 		= $project_id;
	//$this->_division_id = $division_id;
	$this->_team_id 		= $team_id;
	$this->_project 		= $this->getProject();
	$this->_round_id   		= $this->getCurrentRoundId();
  JRequest::setVar( 'jlamtopseason', $this->getSeasonId() );
  JRequest::setVar( 'jlamtopleague', $this->getLeagueId() );
  JRequest::setVar( 'jlamtopproject', $this->_project_id );
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
	   $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);
        
        $query->select('parent_id');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_associations ');
            $query->where('id = '. $assoc_id );

		$db->setQuery($query);
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
	 * modSportsmanagementAjaxTopNavigationMenuHelper::getLeagueAssocId()
	 * 
	 * @return
	 */
	public function getLeagueAssocId()
	{
	   $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);
        
        $query->select('associations');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_league ');
            $query->where('id = '. $this->_league_id );
                
		$db->setQuery($query);
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
	   $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);
        
        $query->select('t.club_id');
        $query->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
        $query->select('CONCAT_WS(\':\',c.id,c.alias) AS club_slug');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team as t');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_club c ON t.club_id = c.id ');
            $query->where('t.id = '. $this->_team_id );
            
                
		$db->setQuery($query);
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
  $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);
        
        $query->select('fav_team');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project');
            $query->where('id = '. $project_id );

		$db->setQuery($query);
		$teams = $db->loadResult();
		
    //echo 'teams -><pre>'.print_r($teams,true).'</pre><br>';
		
		if ( $teams )
		{
		  $query->clear();
          $query->select('t.id as team_id, t.name, t.club_id');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team as t');
            $query->where('t.id in ('. $teams . ')' );

    	
				$db->setQuery($query);
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
	   $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);
        
        $query->select('pt.team_id');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st ON st.id = pt.team_id ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = st.team_id');
            
            $query->where('pt.project_id = '.intval($project_id));
            $query->where('t.club_id = '.$club_id);

                
		$db->setQuery($query);
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
	   $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);
        
		$options = array(JHTML::_('select.option', 0, JText::_($this->getParam('seasons_text'))));
        
        $query->select('s.id AS value, s.name AS text');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s');
            $query->order('s.name DESC');

                
		$db->setQuery($query);
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
	   $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);

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
$mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);
        
        $query->select('l.id AS value, l.name AS text');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p on l.id = p.league_id');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s on s.id = p.season_id ');
            $query->where('l.associations = ' . $associd );
            $query->where('l.country = \'' . $country_id. '\'' );
            $query->group('l.name');
            $query->order('l.name');

		$db->setQuery($query);
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
        $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);
        $query->select('l.country');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p on l.id = p.league_id');
            $query->where('p.id = ' . $project_id );

		$db->setQuery($query);
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
	   $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);
        
        $query->select('l.id AS value, l.name AS text');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p on l.id = p.league_id');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s on s.id = p.season_id ');
            $query->where('s.id = ' . $season );
            $query->group('l.name');
            $query->order('l.name');
              
               
		$db->setQuery($query);
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
$mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);
        
        $query->select('p.id AS value, p.name AS text');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s on s.id = p.season_id ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l on l.id = p.league_id');
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
//		if (!$this->_project_id) {
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
	   $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);
        
		if (empty($this->_teamoptions))
		{

			$query->select('t.id AS value, t.name AS text');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st ON st.id = pt.team_id ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = st.team_id');
            
            $query->where('pt.project_id = '.intval($project_id));
            $query->order('t.name ASC');
              
			$db->setQuery($query);
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
	public function getProject()
	{
	   $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $query = $db->getQuery(true);
        
		if (!$this->_project)
		{
			if (!$this->_project_id) 
            {
				return false;
			}
			
            $query->select('p.id, p.name');
            $query->select('CONCAT_WS(\':\',p.id,p.alias) AS project_slug');
            $query->select('CONCAT_WS(\':\',s.id,s.alias) AS saeson_slug');
            $query->select('CONCAT_WS(\':\',l.id,l.alias) AS league_slug');
            $query->select('CONCAT_WS(\':\',r.id,r.alias) AS round_slug');
            $query->select('p.season_id, p.league_id, p.current_round');
            
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p');
          $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s on s.id = p.season_id');
            
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l on l.id = p.league_id');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r on p.id = r.project_id ');
            
          $query->where('p.id = ' . $this->_project_id);
          

                   
			$db->setQuery($query);
			$this->_project = $db->loadObject();
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
				
				$link = sportsmanagementHelperRoute::getPlayersRoute( $this->_project_id, $team_id );
				break;
				
				
			case "teaminfo":
				
				$link = sportsmanagementHelperRoute::getTeamInfoRoute( $this->_project_id, $team_id );
				break;				
				
			case "teamplan":
				
				$link = sportsmanagementHelperRoute::getTeamPlanRoute( $this->_project_id, $team_id, $this->_division_id );
				break;		
				
			case "clubinfo":
				
				$this->getClubId();
				$link = sportsmanagementHelperRoute::getClubInfoRoute( $this->_project_id, $club_id );
				break;
      case "clubplan":
				
				$this->getClubId();
				$link = sportsmanagementHelperRoute::getClubPlanRoute( $this->_project_id, $club_id );
				break;	
      
        	
			case "teamstats":
				
				$link = sportsmanagementHelperRoute::getTeamStatsRoute( $this->_project_id, $team_id );
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
		if (!$this->_project_id) {
			return false;
		}
		
// echo 'getLink <br>';
// echo 'round_id ->'.$this->_round_id.'<br>';
// echo 'project_id ->'.$this->_project_id.'<br>';
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
				$link = sportsmanagementHelperRoute::getMatrixRoute( $this->_project_slug, $this->_division_id );
				break;
				
			case "referees":
				$link = sportsmanagementHelperRoute::getRefereesRoute( $this->_project_slug);
				break;
				
			case "results":
				$link = sportsmanagementHelperRoute::getResultsRoute( $this->_project_slug, $this->_round_slug, $this->_division_id );
				break;
				
			case "resultsmatrix":
				$link = sportsmanagementHelperRoute::getResultsMatrixRoute( $this->_project_slug, $this->_round_slug, $this->_division_id  );
				break;

			case "resultsranking":
				$link = sportsmanagementHelperRoute::getResultsRankingRoute( $this->_project_slug, $this->_round_slug, $this->_division_id  );
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
				$link = sportsmanagementHelperRoute::getTeamsRoute( $this->_project_slug,$this->_division_id );
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
				$link = sportsmanagementHelperRoute::getRankingRoute( $this->_project_slug, $this->_round_slug,null,null,0,$this->_division_id );
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