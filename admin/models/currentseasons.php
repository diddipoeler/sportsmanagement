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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

//jimport('joomla.application.component.model');
//require_once (JPATH_COMPONENT.DS.'models'.DS.'list.php');
//jimport('joomla.application.component.modellist');

/**
 * sportsmanagementModelcurrentseasons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelcurrentseasons extends JSMModelList
{
	var $_identifier = "currentseasons";
    
    
    
    /**
     * sportsmanagementModelLeagues::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'p.name'
                        );
                //$config['dbo'] = sportsmanagementHelper::getDBConnection();  
                parent::__construct($config);
//                $getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($this->jsmdb);
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
	   
       
    parent::populateState('p.name', 'asc');   
    }
    
    
    /**
     * sportsmanagementModelcurrentseasons::getListQuery()
     * 
     * @return void
     */
    protected function getListQuery()
	{
	$filter_season = JComponentHelper::getParams($this->jsmoption)->get('current_season',0);
    // Select some fields
    $this->jsmquery->clear();
	$this->jsmquery->select('p.id,p.project_art_id,p.name,st.name AS sportstype,s.name AS season,l.name AS league,l.country AS country,u.name AS editor');   
    // From table
	$this->jsmquery->from('#__sportsmanagement_project AS p');
    $this->jsmquery->join('LEFT', '#__sportsmanagement_season AS s ON s.id = p.season_id');   
    $this->jsmquery->join('LEFT', '#__sportsmanagement_league AS l ON l.id = p.league_id');
    $this->jsmquery->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = p.sports_type_id');
    $this->jsmquery->join('LEFT', '#__users AS u ON u.id = p.checked_out ');
    
    if ( $filter_season )
    {
    $filter_season = implode(",",$filter_season);   
    $this->jsmquery->where('p.season_id IN (' . $filter_season .')' );     
    }      
    $this->jsmquery->order($this->jsmdb->escape($this->getState('list.ordering', 'p.name')).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));  
    return $this->jsmquery;
    }
    

}

?>    