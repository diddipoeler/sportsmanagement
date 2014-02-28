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
 * sportsmanagementModelPositions
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPositions extends JModelList
{
	var $_identifier = "positions";
	
	public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'po.name',
                        'po.parent_id',
                        'po.sports_type_id',
                        'po.persontype',
                        'po.id',
                        'po.ordering'
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
		parent::populateState('po.name', 'asc');
	}
	
	function getListQuery()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        
        $search	= $this->getState('filter.search');
        $search_state	= $this->getState('filter.state');
        $search_sports_type	= $this->getState('filter.sports_type');
        
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        

        // Select some fields
		$query->select('po.*,pop.name AS parent_name,st.name AS sportstype,u.name AS editor');
        $query->select('(select count(*) FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype WHERE position_id = po.id) countEvents');
        $query->select('(select count(*) FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_statistic WHERE position_id = po.id) countStats');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS po');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON st.id = po.sports_type_id');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pop ON pop.id = po.parent_id');
        $query->join('LEFT', '#__users AS u ON u.id=po.checked_out');
        
        if ($search)
		{
        $query->where('LOWER(po.name) LIKE '.$db->Quote('%'.$search.'%'));
        }
        if (is_numeric($search_state))
		{
        $query->where('po.published = '.$search_state);
        }
        if ($search_sports_type)
		{
        $query->where('po.sports_type_id = '.$db->Quote($search_sports_type));
        }

	
		$query->order($db->escape($this->getState('list.ordering', 'po.name')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
                
  $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
  
		return $query;
	}





	/**
	 * Method to return the positions array (id,name) 
	 *
	 * @access	public
	 * @return	array
	 * @since 0.1
	 */
	function getParentsPositions()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		//$project_id=$mainframe->getUserState($option.'project');
		//get positions already in project for parents list
		//support only 2 sublevel, so parent must not have parents themselves
		$query='	SELECT pos.id, pos.name,	
        pos.id AS value,
							pos.name AS text,
                            pos.alias,pos.parent_id,pos.persontype,pos.sports_type_id 
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos
					WHERE pos.parent_id=0
					ORDER BY pos.ordering ASC 
					';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		return $result;
	}
    
    
    /**
	 * Method to return a positions array (id,position)
		*
		* @access  public
		* @return  array
		* @since 0.1
		*/
	function getStaffPositions($project_id)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		//$project_id=$mainframe->getUserState($option.'project');
		$query="	SELECT ppos.id AS value, pos.name AS text
					FROM #__".COM_SPORTSMANAGEMENT_TABLE."_position AS pos
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project_position AS ppos ON ppos.position_id=pos.id
					WHERE ppos.project_id=$project_id AND pos.persontype=2
					ORDER BY ordering ";
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		foreach ($result as $position){
			$position->text=JText::_($position->text);
		}
		return $result;
	}

    
    /**
	 * Method to return a positions array (id,position)
		*
		* @access  public
		* @return  array
		* @since 0.1
		*/
	function getPlayerPositions($project_id)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		//$project_id=$mainframe->getUserState($option.'project');

		$query="	SELECT pp.id AS value,name AS text
					FROM #__".COM_SPORTSMANAGEMENT_TABLE."_position AS p
					LEFT JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project_position AS pp ON pp.position_id=p.id
					WHERE pp.project_id=$project_id AND p.persontype=1
					ORDER BY ordering ";
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		foreach ($result as $position){$position->text=JText::_($position->text);}
		return $result;
	}


    /**
	 * Method to return a project positions array (id,position)
		*
		* @access  public
		* @return  array
		* @since 0.1
		*/
	function getPositions($project_id)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		//$project_id=$mainframe->getUserState($option.'project');
		$query='SELECT	pp.id AS value,
				name AS text
				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS p
				LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS pp ON pp.position_id=p.id
				WHERE pp.project_id='.$project_id.'
						ORDER BY ordering ';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		else
		{
			foreach ($result as $position) {
				$position->text=JText::_($position->text);
			}
			return $result;
		}
	}
    
    /**
	 * Method to return a positions array (id,position + (sports_type_name))
	 *
	 * @access	public
	 * @return	array
	 * @since 0.1
	 */
	function getAllPositions()
	{
		$query='	SELECT	pos.id AS value,
							pos.name AS posName,
							s.name AS sName
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position pos
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS s ON s.id=pos.sports_type_id
					WHERE pos.published=1
					ORDER BY pos.ordering,pos.name';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return array();
		}
		else
		{
			foreach ($result as $position){$position->text=JText::_($position->posName).' ('.JText::_($position->sName).')';}
			return $result;
		}
	}
    

/**
	 * Method to return a positions array of referees (id,position)
	 *
	 * @access	public
	 * @return	array
	 *
	 */

	function getRefereePositions($project_id)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		//$project_id=$mainframe->getUserState($option.'project');
		$query='SELECT	ppos.id AS value,
				pos.name AS text
				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos
				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON pos.id=ppos.position_id
				WHERE ppos.project_id='. $this->_db->Quote($project_id).' AND pos.persontype=3';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		else
		{
			foreach ($result as $position) {
				$position->text=JText::_($position->text);
			}
			return $result;
		}
	}
    
    public function getPositionListSelect()
	{
		$query='SELECT id,name,id AS value,name AS text,alias,parent_id,persontype,sports_type_id FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position ORDER BY name';
		$this->_db->setQuery($query);
		$result=$this->_db->loadObjectList();
		foreach ($result as $position)
        {
            $position->text=JText::_($position->text);
        }
		return $result;
	}


}
?>