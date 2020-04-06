<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       currentseasons.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage currentseasons
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelcurrentseasons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelcurrentseasons extends JSMModelList
{
    var $_identifier = "currentseasons";
    
    /**
     * sportsmanagementModelLeagues::__construct()
     * 
     * @param  mixed $config
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
     * @since 1.6
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
        $filter_season = ComponentHelper::getParams($this->jsmoption)->get('current_season', 0);
        // Select some fields
        $this->jsmquery->clear();
        $this->jsmquery->select('p.id,p.project_art_id,p.name,st.name AS sportstype,s.name AS season,l.name AS league,l.country AS country,u.name AS editor');   
        // From table
        $this->jsmquery->from('#__sportsmanagement_project AS p');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_season AS s ON s.id = p.season_id');   
        $this->jsmquery->join('LEFT', '#__sportsmanagement_league AS l ON l.id = p.league_id');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = p.sports_type_id');
        $this->jsmquery->join('LEFT', '#__users AS u ON u.id = p.checked_out ');
    
        if ($filter_season ) {
            $filter_season = implode(",", $filter_season);   
            $this->jsmquery->where('p.season_id IN (' . $filter_season .')');     
        }      
        $this->jsmquery->order(
            $this->jsmdb->escape($this->getState('list.ordering', 'p.name')).' '.
            $this->jsmdb->escape($this->getState('list.direction', 'ASC'))
        );  
        return $this->jsmquery;
    }
    

}

?>    
