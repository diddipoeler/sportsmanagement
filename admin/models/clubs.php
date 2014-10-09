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
 * sportsmanagementModelClubs
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelClubs extends JModelList
{
	var $_identifier = "clubs";
	
	/**
	 * sportsmanagementModelClubs::__construct()
	 * 
	 * @param mixed $config
	 * @return void
	 */
	public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'a.name',
                        'a.website',
                        'a.logo_big',
                        'a.logo_middle',
                        'a.logo_small',
                        'a.country',
                        'a.alias',
                        'a.zipcode',
                        'a.location',
                        'a.address',
                        'a.latitude',
                        'a.longitude',
                        'a.id',
                        'a.unique_id',
                        'a.ordering',
                        'a.checked_out',
                        'a.checked_out_time'
                        );
                parent::__construct($config);
                $getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($getDBConnection);
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
		$app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Initialise variables.
		$app = JFactory::getApplication('administrator');
        
        //$app->enqueueMessage(JText::_('sportsmanagementModelsmquotes populateState context<br><pre>'.print_r($this->context,true).'</pre>'   ),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.season', 'filter_season', '');
		$this->setState('filter.season', $temp_user_request);
        
        $value = JRequest::getUInt('limitstart', 0);
		$this->setState('list.start', $value);

//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.name', 'asc');
	}
    
    /**
     * sportsmanagementModelClubs::getListQuery()
     * 
     * @return
     */
    protected function getListQuery()
	{
		$app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $search	= $this->getState('filter.search');
        $search_nation	= $this->getState('filter.search_nation');
        $search_season = $this->getState('filter.season');

        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		// Select some fields
		$query->select(implode(",",$this->filter_fields));
		// From the club table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_club as a');
        
        if ($search)
		{
        //$query->where('LOWER(a.name) LIKE '.$db->Quote('%'.$search.'%'));
        $query->where(' ( LOWER(a.name) LIKE '.$db->Quote('%'.$search.'%') .' OR LOWER(a.unique_id) LIKE '.$db->Quote('%'.$search.'%') .')' );
        }
        if ($search_nation)
		{
        $query->where("a.country = '".$search_nation."'");
        }
        
        if ($search_season)
		{
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON a.id = t.club_id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st ON t.id = st.team_id ');
        $query->where('st.season_id = '.$search_season);
        }
        
        
        $query->order($db->escape($this->getState('list.ordering', 'a.name')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }
        

		return $query;
	}

   
    /**
     * sportsmanagementModelClubs::getClubListSelect()
     * 
     * @return
     */
    public function getClubListSelect()
	{
	   $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
        $starttime = microtime(); 
        $results = array();
        // Select some fields
		$query->select('id,name,id AS value,name AS text,country,standard_playground');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_club');
        $query->order('name');
        
		//$query='SELECT id,name,id AS value,name AS text,country,standard_playground FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_club ORDER BY name';
		$db->setQuery($query);
		if ( $results = $db->loadObjectList() )
		{
			return $results;
		}
		//return false;
        return $results;
	}

	
}
?>
