<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      positions.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage positions
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelPositions
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPositions extends JSMModelList
{
	var $_identifier = "positions";
	
	/**
	 * sportsmanagementModelPositions::__construct()
	 * 
	 * @param mixed $config
	 * @return void
	 */
	public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'po.name',
                        'po.picture',
                        'po.parent_id',
                        'po.sports_type_id',
                        'po.persontype',
                        'po.id',
                        'po.published',
                        'po.modified',
                        'po.modified_by',
                        'po.ordering'
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
	   if ( ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend') )
{
		$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' context -> '.$this->context.''),'');
        $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' identifier -> '.$this->_identifier.''),'');
        }
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.sports_type', 'filter_sports_type', '');
		$this->setState('filter.sports_type', $temp_user_request);
        $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
		$this->setState('list.limit', $value);

		// List state information.
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);
		// Filter.order
		$orderCol = $this->getUserStateFromRequest($this->context. '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'po.name';
		}
		$this->setState('list.ordering', $orderCol);
		$listOrder = $this->getUserStateFromRequest($this->context. '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);
	}
	
	/**
	 * sportsmanagementModelPositions::getListQuery()
	 * 
	 * @return
	 */
	function getListQuery()
	{
        // Create a new query object.		
		$this->jsmquery->clear();
        // Select some fields
		$this->jsmquery->select('po.*,pop.name AS parent_name,st.name AS sportstype,u.name AS editor');
        $this->jsmquery->select('(select count(*) FROM #__sportsmanagement_position_eventtype WHERE position_id = po.id) countEvents');
        $this->jsmquery->select('(select count(*) FROM #__sportsmanagement_position_statistic WHERE position_id = po.id) countStats');
        $this->jsmquery->from('#__sportsmanagement_position AS po');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = po.sports_type_id');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_position AS pop ON pop.id = po.parent_id');
        $this->jsmquery->join('LEFT', '#__users AS u ON u.id=po.checked_out');
        
        if ($this->getState('filter.search'))
		{
        $this->jsmquery->where('LOWER(po.name) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%'));
        }
        
        if (is_numeric($this->getState('filter.state')))
		{
        $this->jsmquery->where('po.published = '.$this->getState('filter.state'));
        }
        
        if ($this->getState('filter.sports_type'))
		{
        $this->jsmquery->where('po.sports_type_id = '.$this->getState('filter.sports_type') );
        }
	
		$this->jsmquery->order($this->jsmdb->escape($this->getState('list.ordering', 'po.name')).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));
    
		return $this->jsmquery;
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
        // Select some fields
	$this->jsmquery->clear();
        $this->jsmquery->select('pos.id,pos.name,pos.id AS value,pos.name AS text,pos.alias,pos.parent_id,pos.persontype,pos.sports_type_id');
        // From the table
		$this->jsmquery->from('#__sportsmanagement_position AS pos');
        $this->jsmquery->where('pos.parent_id = 0');  
        $this->jsmquery->order('pos.ordering ASC ');  

		$this->jsmdb->setQuery($this->jsmquery);
		if (!$result = $this->jsmdb->loadObjectList())
		{
            return false;
		}
       
		return $result;
	}
    
    
    
    
    /**
     * sportsmanagementModelPositions::getProjectPositions()
     * 
     * @param mixed $project_id
     * @param integer $persontype
     * @return
     */
    function getProjectPositions($project_id,$persontype=1)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
        $query = Factory::getDbo()->getQuery(true);
        
        // Select some fields
        $query->select('ppos.id AS value, pos.name AS text, ppos.position_id as position_id');
        // From the table
	$query->from('#__sportsmanagement_position AS pos');
        $query->join('INNER', '#__sportsmanagement_project_position AS ppos ON ppos.position_id = pos.id');
        $query->where('ppos.project_id = '.(int)$project_id);  
        $query->where('pos.persontype = '.(int)$persontype);  
        $query->order('pos.ordering');  
        try{		
		Factory::getDbo()->setQuery($query);
       $result = Factory::getDbo()->loadObjectList();
		foreach ($result as $position)
        {
			$position->text = Text::_($position->text);
		}
        }
catch (Exception $e){
$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
	$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getCode()), 'error');
    $result = false;
}
        
        
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
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
        $query = Factory::getDbo()->getQuery(true);
       
        // Select some fields
        $query->select('pp.id AS value,name AS text');
        // From the table
		$query->from('#__sportsmanagement_position AS p');
        $query->join('LEFT', '#__sportsmanagement_project_position AS pp ON pp.position_id = p.id');
        $query->where('pp.project_id = '.$project_id);  
        $query->order('p.ordering');  
        
    	Factory::getDbo()->setQuery($query);
		if (!$result=Factory::getDbo()->loadObjectList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, Factory::getDbo()->getErrorMsg(), __LINE__);
			return false;
		}
		else
		{
			foreach ($result as $position) {
				$position->text = Text::_($position->text);
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
	   
        $this->jsmquery->clear();
        
        // Select some fields
        $this->jsmquery->select('pos.id AS value, pos.name AS posName,s.name AS sName');
        // From the table
		$this->jsmquery->from('#__sportsmanagement_position AS pos');
        $this->jsmquery->join('INNER', '#__sportsmanagement_sports_type AS s ON s.id = pos.sports_type_id');
        $this->jsmquery->where('pos.published = 1');  
        $this->jsmquery->order('pos.ordering,pos.name');  
	
		$this->jsmdb->setQuery($this->jsmquery);
		if ( !$result = $this->jsmdb->loadObjectList() )
		{
			return array();
		}
		else
		{
			foreach ($result as $position)
            {
                $position->text=Text::_($position->posName).' ('.Text::_($position->sName).')';
            }
			return $result;
		}
	}

    
    /**
     * sportsmanagementModelPositions::getPositionListSelect()
     * 
     * @return
     */
    public function getPositionListSelect()
	{
	   $option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
        $query = Factory::getDbo()->getQuery(true);
        // Select some fields
        $query->select('id,name,id AS value,name AS text,alias,parent_id,persontype,sports_type_id');
        // From the table
		$query->from('#__sportsmanagement_position');
        $query->order('name');
       
		Factory::getDbo()->setQuery($query);
		$result = Factory::getDbo()->loadObjectList();
		foreach ($result as $position)
        {
            $position->text = Text::_($position->text);
        }
		return $result;
	}


}
?>
