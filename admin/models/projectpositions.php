<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      projectpositions.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

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
		$app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        // Initialise variables.
		$app = Factory::getApplication('administrator');
                $pid = $this->jsmjinput->get('pid');
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        $this->setState('filter.pid', $pid );
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
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        $subQuery1= $db->getQuery(true);
        $subQuery2= $db->getQuery(true);
        
        // Select some fields
		$query->select('pt.*,pt.id AS positiontoolid');
		// From the table
		$query->from('#__sportsmanagement_project_position AS pt');
        // Select some fields
		$query->select('po.*,po.name AS name');
		// From the table
		$query->join('LEFT','#__sportsmanagement_position po ON pt.position_id = po.id');
        // Select some fields
		$query->select('pid.name AS parent_name');
		// From the table
		$query->join('LEFT','#__sportsmanagement_position pid ON po.parent_id = pid.id');
        // count 
        $subQuery1->select('count(*)');
        $subQuery1->from('#__sportsmanagement_position_eventtype AS pe ');
        $subQuery1->where('pe.position_id=po.id');
        $query->select('('.$subQuery1.') AS countEvents');
        // count 
        $subQuery2->select('count(*)');
        $subQuery2->from('#__sportsmanagement_position_statistic AS ps ');
        $subQuery2->where('ps.position_id=po.id');
        $query->select('('.$subQuery2.') AS countStats');
        
        $query->where('pt.project_id = '.$this->getState('filter.pid') );
        
        $query->order($db->escape($this->getState('list.ordering', 'po.name')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));

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
        $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
        return false;
        }

if ( $position  )
{
$result = array_unique($position );
$match_ids = implode(",",$result);    
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
$this->jsmdb->setQuery($this->jsmquery);
$resultupdate = $this->jsmdb->execute();
}
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
        return false;
        }
	
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
    $app = Factory::getApplication();    
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
        $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.' '.$e->getMessage()), 'error');
        return false;
        }
        
		return $result;
	}

	


	
	/**
	 * return count of projectpositions
	 *
	 * @param int project_id
	 * @return int
	 */
	function getProjectPositionsCount($project_id)
	{
$this->jsmquery->clear();
$this->jsmquery->select('count(*) AS count');   
$this->jsmquery->from('#__sportsmanagement_project_position AS pp');   
$this->jsmquery->join('INNER','#__sportsmanagement_project AS p on p.id = pp.project_id');
$this->jsmquery->where('p.id = '.$project_id); 
$this->jsmdb->setQuery($this->jsmquery);
return $this->jsmdb->loadResult();
	}
	
}
?>
