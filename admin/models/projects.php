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
 * sportsmanagementModelProjects
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelProjects extends JModelList
{
	var $_identifier = "projects";
	
    /**
     * sportsmanagementModelProjects::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'p.name',
                        'l.name',
                        's.name',
                        'st.name',
                        'p.project_type',
                        'p.published',
                        'p.id',
                        'p.ordering'
                        );
                parent::__construct($config);
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
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Initialise variables.
		$app = JFactory::getApplication('administrator');
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelsmquotes populateState context<br><pre>'.print_r($this->context,true).'</pre>'   ),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.league', 'filter_league', '');
		$this->setState('filter.league', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.sports_type', 'filter_sports_type', '');
		$this->setState('filter.sports_type', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.season', 'filter_season', '');
		$this->setState('filter.season', $temp_user_request);

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('p.name', 'asc');
	}
    
	/**
	 * sportsmanagementModelProjects::getListQuery()
	 * 
	 * @return
	 */
	protected function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
        $search	= $this->getState('filter.search');
        $search_league	= $this->getState('filter.league');
        $search_sports_type	= $this->getState('filter.sports_type');
        $search_season	= $this->getState('filter.season');
        $filter_state = $this->getState('filter.state');
        
        
        // Create a new query object.
        $db = JFactory::getDBO();
		$query = $db->getQuery(true);
        $query->select('p.id,p.ordering,p.published,p.project_type,p.name,p.checked_out,p.sports_type_id ,st.name AS sportstype,s.name AS season,l.name AS league,u.name AS editor');
    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p');
    $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s ON s.id = p.season_id');
    $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l ON l.id = p.league_id');
    $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON st.id = p.sports_type_id');
    $query->join('LEFT', '#__users AS u ON u.id = p.checked_out');
  
  

if ($search)
		{
        $query->where('LOWER(p.name) LIKE ' . $db->Quote( '%' . $search . '%' ));
        }
        if ($search_league)
		{
        $query->where('p.league_id = ' . $search_league);
        }
        if ($search_sports_type)
		{
        $query->where('p.sports_type_id = ' . $db->Quote($search_sports_type));
        }
        if ($search_season)
		{
        $query->where('p.season_id = ' . $search_season);
        }
        if (is_numeric($filter_state) )
		{
		$query->where('p.published = '.$filter_state);	
		}
     
     $query->order($db->escape($this->getState('list.ordering', 'p.name')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
                
                if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }
                
		return $query;
        
        
	}
	
  
  



	
	
	
	

	
}
?>