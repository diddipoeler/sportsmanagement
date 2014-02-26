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


jimport('joomla.application.component.modellist');

/**
 * Sportsmanagement Component Events Model
 *
 * @package	Sportsmanagement
 * @since	1.5.0a
 */
class sportsmanagementModelEventtypes extends JModelList
{
	var $_identifier = "eventtypes";
	
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'obj.name',
                        'obj.id',
                        'obj.ordering'
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
        
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.sports_type', 'filter_sports_type', '');
		$this->setState('filter.sports_type', $temp_user_request);

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('obj.name', 'asc');
	}
    
	function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $search	= $this->getState('filter.search');
        $search_state	= $this->getState('filter.state');
        $search_sports_type	= $this->getState('filter.sports_type');
        
        //$search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
		
        // Select some fields
		$query->select('obj.*');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype as obj');
        // Join over the sportstype
		$query->select('st.name AS sportstype');
		$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON st.id = obj.sports_type_id');
        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');
        
        
        if ($search)
		{
        $query->where('LOWER(obj.name) LIKE '.$this->_db->Quote('%'.$search.'%'));
        }
		if (is_numeric($search_state))
		{
        $query->where('obj.published = '.$search_state);
        }
        if ($search_sports_type)
		{
        $query->where('obj.sports_type_id = '.$this->_db->Quote($filter_sports_type));
        }
        
        
        $query->order($db->escape($this->getState('list.ordering', 'obj.name')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
        
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		return $query;
        
        
        
        
        
	}




    
    /**
	* Method to return a events array (id,name)
	*
	* @access  public
	* @return  array
	* @since 0.1
	*/
	function getEvents($sports_type_id  = 0)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $query = $this->_db->getQuery(true);
        // Select some fields
		$query->select('evt.id AS value, concat(evt.name, " (" , st.name, ")") AS text,evt.name as posname,st.name AS stname');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype as evt');
        // Join over the sportstype
		$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON st.id = evt.sports_type_id');
        $query->where('evt.published = 1');
        if ( $sports_type_id )
        {
            $query->where('evt.sports_type_id = '.$sports_type_id);
        }
        $query->order('evt.name ASC');
                
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		foreach ($result as $position)
        {
            //$position->text = JText::_($position->text);
            $position->text = JText::_($position->posname).' ('.JText::_($position->stname).')';
        }
		return $result;
	}
    
    /**
	* Method to return the position events array (id,name)
	*
	* @access  public
	* @return  array
	* @since 0.1
	*/
	function getEventsPosition($id)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $query='	SELECT	p.id AS value,
        p.name as posname,
						st.name AS stname,
                        concat(p.name, " (" , st.name, ")") AS text  
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS p
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pe
						ON pe.eventtype_id=p.id
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON st.id = p.sports_type_id 
					WHERE pe.position_id='. $id.'
					ORDER BY pe.ordering ASC ';

		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		foreach ($result as $event)
        {
            //$event->text = JText::_($event->text);
            $event->text = JText::_($event->posname).' ('.JText::_($event->stname).')';
        }
		return $result;
	}
    
    public function getEventList()
	{
		$query='SELECT *,id AS value,name AS text FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype ORDER BY name';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
    
    
    

}
?>