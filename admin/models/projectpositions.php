<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      projectpositions.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
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
        $option = JFactory::getApplication()->input->getCmd('option');
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
		$option = JFactory::getApplication()->input->getCmd('option');
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
        
        $query->order($db->escape($this->getState('list.ordering', 'po.name')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
                
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        { 
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
}

        return $query;
	}
    
    
    
   
   /**
    * sportsmanagementModelProjectpositions::updateprojectpositions()
    * 
    * @param mixed $items
    * @param integer $project_id
    * @return
    */
   function updateprojectpositions($items=NULL,$project_id=0)
   {
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_id<br><pre>'.print_r($project_id,true).'</pre>'),'');	   	   
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' items<br><pre>'.print_r($items,true).'</pre>'),'');	   


$this->jsmquery->clear();
$this->jsmquery->select('mp.match_id');
$this->jsmquery->from('#__sportsmanagement_match_player as mp');
$this->jsmquery->join('INNER','#__sportsmanagement_match as m ON m.id = mp.match_id');
$this->jsmquery->join('INNER','#__sportsmanagement_round as r ON r.id = m.round_id');
$this->jsmquery->where('r.project_id = '.$project_id);
$this->jsmquery->where('mp.project_position_id != 0');
try{
$this->jsmdb->setQuery($this->jsmquery);
$position = $this->jsmdb->loadColumn();
 }
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.' '.$e->getMessage()), 'error');
        return false;
        }


//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' position <br><pre>'.print_r($position ,true).'</pre>'),'');	   
$result = array_unique($position );
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result <br><pre>'.print_r($result ,true).'</pre>'),'');	   

$match_ids = implode(",",$result);
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' match_ids<br><pre>'.print_r($match_ids,true).'</pre>'),'');	   



foreach( $items as $item )
{
$this->jsmquery->clear();
// Fields to update.
$fields = array(
    $this->jsmdb->quoteName('project_position_id') . ' = ' . $item->position_id
);

// Conditions for which records should be updated.
$conditions = array(
    $this->jsmdb->quoteName('project_position_id') . ' = '. $item->positiontoolid, 
    $this->jsmdb->quoteName('match_id') . ' IN (' . $match_ids . ')'
);
try{
$this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_match_player'))->set($fields)->where($conditions);
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsmquery<br><pre>'.print_r($this->jsmquery,true).'</pre>'),'');	   

$this->jsmdb->setQuery($this->jsmquery);
$resultupdate = $this->jsmdb->execute();
}
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.' '.$e->getMessage()), 'error');
        return false;
        }
	
}	   
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

	
	/**
	 * sportsmanagementModelProjectpositions::getSubPositions()
	 * 
	 * @param integer $sports_type_id
	 * @return
	 */
	function getSubPositions($sports_type_id=1)
	{
$this->jsmquery->clear();
$this->jsmquery->select('id AS value,name AS text,sports_type_id AS type,parent_id AS parentID');
$this->jsmquery->from('#__sportsmanagement_position');
$this->jsmquery->where('sports_type_id = '.$sports_type_id);
$this->jsmquery->where('published = 1');
$this->jsmquery->order('parent_id ASC,name ASC');
try{
$this->jsmdb->setQuery($this->jsmquery);
$result = $this->jsmdb->loadObjectList();
 }
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.' '.$e->getMessage()), 'error');
        return false;
        }
        
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
				if(!$this->_db->execute())
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
