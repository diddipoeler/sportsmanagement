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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');


/**
 * sportsmanagementModelSeasons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelSeasons extends JModelList
{
	var $_identifier = "seasons";
	var $_order = "s.name";
    
    /**
     * sportsmanagementModelSeasons::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
        // Reference global application object
        $this->app = JFactory::getApplication();
        // JInput object
        $this->jinput = $this->app->input;
                
                $layout = $this->jinput->getVar('layout');
                
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($layout,true).'</pre>'),'Notice');
                
                switch ($layout)
        {
            case 'assignteams':
            $this->_order = 't.name';
            break;
            
            case 'assignpersons':
            $this->_order = 'p.lastname';
            break;
            
            default:
		    $this->_order = 's.name';
            break;
        }
                $config['filter_fields'] = array(
                        's.name',
                        's.alias',
                        's.id',
                        's.ordering',
                        's.checked_out',
                        's.checked_out_time'
                        );
                parent::__construct($config);
                $getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($getDBConnection);
                
        $this->user	= JFactory::getUser();     
        $this->option = $this->jinput->getCmd('option');
        $this->jsmdb = $this->getDbo();
        $this->query = $this->jsmdb->getQuery(true);
                
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
		
        $layout = $this->jinput->getVar('layout');
        // Initialise variables.
		//$app = JFactory::getApplication('administrator');
        $order = '';
        
        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context ->'.$this->context.''),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);
        
        $value = $this->jinput->getUInt('limitstart', 0);
		$this->setState('list.start', $value);
        
        // List state information.
		parent::populateState($this->_order, 'asc');
        
	}
    
	/**
	 * sportsmanagementModelSeasons::getListQuery()
	 * 
	 * @return
	 */
	protected function getListQuery()
	{

        $layout = $this->jinput->getVar('layout');
        $season_id = $this->jinput->getVar('id');
        
        $this->setState('list.ordering', $this->_order);
        
        $Subquery = $this->jsmdb->getQuery(true);
        
        switch ($layout)
        {
            case 'assignteams':
            	$this->query->clear();
            // Select some fields
		    $this->query->select('t.*');
		    // From the seasons table
		    $this->query->from('#__sportsmanagement_team as t');
            $this->query->join('LEFT', '#__sportsmanagement_club AS c ON c.id = t.club_id');
            $Subquery->select('stp.team_id');
            $Subquery->from('#__sportsmanagement_season_team_id AS stp  ');
            $Subquery->where('stp.season_id = '.$season_id);
            $this->query->where('t.id NOT IN ('.$Subquery.')');
            if ($this->getState('filter.search_nation'))
		    {
            $this->query->where('c.country LIKE '.$this->jsmdb->Quote(''.$this->getState('filter.search_nation').''));
            }
            if ($this->getState('filter.search'))
		    {
            $this->query->where(' LOWER(t.name) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%'));
            }
            //$order = 't.name';
            break;
            
            case 'assignpersons':
            	$this->query->clear();
            // Select some fields
		    $this->query->select('p.*');
		    // From the seasons table
		    $this->query->from('#__sportsmanagement_person as p');
            $Subquery->select('stp.person_id');
            $Subquery->from('#__sportsmanagement_season_person_id AS stp  ');
            $Subquery->where('stp.season_id = '.$season_id);
            $this->query->where('p.id NOT IN ('.$Subquery.')');
            if ($this->getState('filter.search_nation'))
		    {
            $this->query->where('p.country LIKE '.$this->jsmdb->Quote(''.$this->getState('filter.search_nation').'') );
            }
            if ($this->getState('filter.search'))
		{
        $this->query->where('(LOWER(p.lastname) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ).
						   'OR LOWER(p.firstname) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ) .
						   'OR LOWER(p.nickname) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ) .
                           'OR LOWER(p.info) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ) .
                            ')');
        }
            //$order = 'p.lastname';
            break;
            
            default:
            	$this->query->clear();
            // Select some fields
		    $this->query->select(implode(",",$this->filter_fields));
		    // From the seasons table
		    $this->query->from('#__sportsmanagement_season as s');
            if ($this->getState('filter.search'))
		    {
            $this->query->where(' LOWER(s.name) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%'));
            }
		    //$order = 's.name';
            break;
        }
		
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _order<br><pre>'.print_r($this->_order,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' list.ordering<br><pre>'.print_r($this->getState('list.ordering', $this->_order),true).'</pre>'),'Notice');
        
        $this->query->order($this->jsmdb->escape($this->getState('list.ordering', $this->_order)).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));
 
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->query->dump(),true).'</pre>'),'Notice');
 
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = ' <br><pre>'.print_r($this->query->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }

        return $this->query;
	}
	
  
  



	/**
	 * sportsmanagementModelSeasons::getSeasonTeams()
	 * 
	 * @param integer $season_id
	 * @return void
	 */
	public function getSeasonTeams($season_id=0)
    {
    $this->query->clear();
        // Select some fields
		    $this->query->select('t.id as value, t.name as text');
        // From the seasons table
		    $this->query->from('#__sportsmanagement_team as t');
        $this->query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
        $this->query->where('st.season_id = '.$season_id);
        $this->jsmdb->setQuery($this->query);
        $result = $this->jsmdb->loadObjectList();
        return $result;    
    }
        
	/**
     * Method to return a seasons array (id,name)
     *
     * @access	public
     * @return	array seasons
     * @since	1.5.0a
     */
    //public static function getSeasons()
    function getSeasons()
    {
        $this->query->clear();
        $this->query->select(array('id', 'name'))
        ->from('#__sportsmanagement_season')
        ->order('name DESC');

        $this->jsmdb->setQuery($this->query);
        if (!$result = $this->jsmdb->loadObjectList())
        {
            $this->setError($this->jsmdb->getErrorMsg());
            return array();
        }
        foreach ($result as $season)
        {
            $season->name = JText::_($season->name);
        }
        return $result;
    }
	
	

	
}
?>
