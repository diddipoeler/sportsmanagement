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
 * sportsmanagementModelProjectpositions
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementModelProjectpositions extends JSMModelList
{
	var $_identifier = "pposition";
    var $_project_id = 0;
	
    
    /**
     * sportsmanagementModelProjectpositions::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'po.name',
                        'po.parent_id',
                        'po.id',
                        'pt.id'
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
                $pid = $this->jsmjinput->get('pid');
        //$app->enqueueMessage(JText::_('sportsmanagementModelsmquotes populateState context<br><pre>'.print_r($this->context,true).'</pre>'   ),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        
        $this->setState('filter.pid', $pid );

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('po.name', 'asc');
	}    
    
    
	/**
	 * sportsmanagementModelProjectpositions::getListQuery()
	 * 
	 * @return
	 */
	protected function getListQuery()
	{
		$option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
//        $this->_project_id	= $app->getUserState( "$option.pid", '0' );
        
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        $subQuery1= $db->getQuery(true);
        $subQuery2= $db->getQuery(true);
        
        // Select some fields
		$query->select('pt.*,pt.id AS positiontoolid');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS pt');
        // Select some fields
		$query->select('po.*,po.name AS name');
		// From the table
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position po ON pt.position_id = po.id');
        // Select some fields
		$query->select('pid.name AS parent_name');
		// From the table
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position pid ON po.parent_id = pid.id');
        // count 
        $subQuery1->select('count(*)');
        $subQuery1->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pe ');
        $subQuery1->where('pe.position_id=po.id');
        $query->select('('.$subQuery1.') AS countEvents');
        // count 
        $subQuery2->select('count(*)');
        $subQuery2->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_position_statistic AS ps ');
        $subQuery2->where('ps.position_id=po.id');
        $query->select('('.$subQuery2.') AS countStats');
        
        $query->where('pt.project_id = '.$this->getState('filter.pid') );

//        $query->where(self::_buildContentWhere());
//		$query->order(self::_buildContentOrderBy());
        
        $query->order($db->escape($this->getState('list.ordering', 'po.name')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
                
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        { 
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
}

        return $query;
	}
    
    
    
   
    /**
     * sportsmanagementModelProjectpositions::insertStandardProjectPositions()
     * 
     * @param integer $project_id
     * @param integer $sports_type_id
     * @return void
     */
    function insertStandardProjectPositions($project_id = 0,$sports_type_id = 0)
    {
    $app = JFactory::getApplication();    
    $db = sportsmanagementHelper::getDBConnection();
    $query = $db->getQuery(true);
    $query->select('id');
    $query->from('#__sportsmanagement_position');
    $query->where('parent_id != 0');
    $query->where('sports_type_id = '.$sports_type_id);
    $query->where('persontype IN (1,2)');
    
    $db->setQuery($query);
	$result = $db->loadObjectList();
    
    if ( $result )
    {
        foreach ($result as $row)
			{
			$query->clear();
            $query->select('id');
            $query->from('#__sportsmanagement_project_position');
            $query->where('project_id = '.$project_id);
            $query->where('position_id = '.$row->id);
            $db->setQuery($query);
	        $position = $db->loadObjectList();
            
            if ( !$position )
            {
            // Create and populate an object.
            $temp = new stdClass();
            $temp->project_id = $project_id;
            $temp->position_id = $row->id;
            // Insert the object
            $resultquery = $db->insertObject('#__sportsmanagement_project_position', $temp);    
            }	
            
			}
    }
    
    
        
    }

//	function _buildContentOrderBy()
//	{
//		$option = JRequest::getCmd('option');
//		$app = JFactory::getApplication();
//		$filter_order		= $app->getUserStateFromRequest($option.'.'.$this->_identifier.'.po_filter_order','filter_order','po.name','cmd');
//		$filter_order_Dir	= $app->getUserStateFromRequest($option.'.'.$this->_identifier.'.po_filter_order_Dir','filter_order_Dir','','word');
//
//		if ($filter_order=='po.name')
//		{
//			$orderby=' po.parent_id,po.name '.$filter_order_Dir;
//		}
//		else
//		{
//			$orderby=' '.$filter_order.' '.$filter_order_Dir.',po.name ';
//		}
//		return $orderby;
//	}

//	function _buildContentWhere()
//	{
//		$option = JRequest::getCmd('option');
//		$app = JFactory::getApplication();
//		$where =' pt.project_id='.$this->_project_id;
//		return $where;
//	}

	

	/**
	 * Method to return the positions which are subpositions and are equal to a sportstype array (id,name)
	 *
	 * @access  public
	 * @return  array
	 * @since 0.1
	 */
	function getSubPositions($sports_type_id=1)
	{
		$query='	SELECT	id AS value,
							name AS text,
							sports_type_id AS type,
							parent_id AS parentID
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position
					WHERE published=1 AND sports_type_id='.$sports_type_id.'
					ORDER BY parent_id ASC,name ASC ';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		//echo '<br /><pre>2~'.print_r($result,true).'~</pre><br />';
		return $result;
	}

	/**
	 * Method to return the project positions array (id,name)
	 *
	 * @access  public
	 * @return  array
	 * @since 0.1
	 */
	function getProjectPositions()
	{
		$app = JFactory::getApplication();
		$project_id=$app->getUserState('com_joomleagueproject');
		$query='	SELECT	p.id AS value,
							p.name AS text,
							p.sports_type_id AS type,
							p.parent_id AS parentID
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS p
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS pp ON pp.position_id=p.id
					WHERE pp.project_id='.$project_id.'
					ORDER BY p.parent_id ASC,p.name ASC ';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return $result;
	}

	/**
	* Method to assign positions of an existing project to a copied project
	*
	* @access  public
	* @return  array
	* @since 0.1
	*/
	function cpCopyPositions($post)
	{
		$old_id=(int)$post['old_id'];
		$project_id=(int)$post['id'];
		//copy positions
		$query="SELECT * FROM #__".COM_SPORTSMANAGEMENT_TABLE."_project_position WHERE project_id=".$old_id;
		$this->_db->setQuery($query);
		if ($results=$this->_db->loadAssocList())
		{
			foreach($results as $result)
			{
				$p_position =& $this->getTable();
				$p_position->bind($result);
				$p_position->set('id',NULL);
				$p_position->set('project_id',$project_id);
				if (!$p_position->store())
				{
					echo $this->_db->getErrorMsg();
					return false;
				}
				$newid = $this->getDbo()->insertid();
				$query = "UPDATE #__".COM_SPORTSMANAGEMENT_TABLE."_team_player " . 
							"SET project_position_id = " . $newid .
							" WHERE project_position_id = " . $result['id'];
				$this->_db->setQuery($query);
				if(!$this->_db->query())
				{
					$this->setError($this->_db->getErrorMsg());
					$result=false;
				}	
			}
		}
		return true;
	}
	
	/**
	 * return count of projectpositions
	 *
	 * @param int project_id
	 * @return int
	 */
	function getProjectPositionsCount($project_id)
	{
		$query='SELECT count(*) AS count
		FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS pp
		JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p on p.id = pp.project_id
		WHERE p.id='.$project_id;
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}
	
}
?>
