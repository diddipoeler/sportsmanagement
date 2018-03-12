<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      statistics.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.modellist' );

/**
 * sportsmanagementModelStatistics
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelStatistics extends JModelList
{
	var $_identifier = "statistics";
	
    /**
     * sportsmanagementModelStatistics::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'obj.name',
                        'obj.short',
                        'obj.icon',
                        'obj.sports_type_id',
                        'obj.published',
                        'obj.id',
                        'obj.ordering'
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
		//$app = JFactory::getApplication();
        //$option = JFactory::getApplication()->input->getCmd('option');
        // Initialise variables.
	//	$app = JFactory::getApplication('administrator');
        
        //$app->enqueueMessage(JText::_('sportsmanagementModelsmquotes populateState context<br><pre>'.print_r($this->context,true).'</pre>'   ),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.sports_type', 'filter_sports_type', '');
		$this->setState('filter.sports_type', $temp_user_request);
        
        $value = JFactory::getApplication()->input->getUInt('limitstart', 0);
		$this->setState('list.start', $value);

		// List state information.
		parent::populateState('obj.name', 'asc');
	}
    
	/**
	 * sportsmanagementModelStatistics::getListQuery()
	 * 
	 * @return
	 */
	function getListQuery()
	{
		$app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        //$search	= $this->getState('filter.search');
        //$search_sports_type	= $this->getState('filter.sports_type');
        //$search_state	= $this->getState('filter.state');

        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
		
        // Select some fields
		$query->select('obj.*');
        // From table
		$query->from('#__sportsmanagement_statistic AS obj');
        // Join over the sportstype
		$query->select('st.name AS sportstype');
		$query->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = obj.sports_type_id	');
        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');
        
        
        if ($this->getState('filter.search'))
		{
        $query->where('LOWER(obj.name) LIKE ' . $this->_db->Quote('%' . $this->getState('filter.search') . '%'));
        }
        if ($this->getState('filter.sports_type'))
		{
        $query->where('obj.sports_type_id = '.$this->getState('filter.sports_type') );
        }
        if (is_numeric($this->getState('filter.state')))
		{
        $query->where('obj.published = '.$this->getState('filter.state'));
        }

        $query->order($db->escape($this->getState('list.ordering', 'obj.name')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }
        
		return $query;
        
        
        
        
	}

	


    
    /**
	* Method to return the position stats array (value,text)
	*
	* @access  public
	* @return  array
	* @since 0.1
	*/
	function getPositionStatsOptions($id)
	{
	   $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        //$search	= $app->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query	= $db->getQuery(true);
        
        $query->select('s.id AS value, concat(s.name, " (" , st.name, ")") AS text');
        $query->from('#__sportsmanagement_statistic AS s');
        $query->join('INNER','#__sportsmanagement_position_statistic AS ps ON ps.statistic_id = s.id ');
        $query->join('LEFT','#__sportsmanagement_sports_type AS st ON st.id = s.sports_type_id  ');
        $query->where('ps.position_id = '.(int) $id);
        $query->order('ps.ordering ASC');

		$db->setQuery($query);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		return $db->loadObjectList();
	}
    
    /**
	* Method to return the stats not yet assigned to position (value,text)
	*
	* @access  public
	* @return  array
	* @since 0.1
	*/
	function getAvailablePositionStatsOptions($id)
	{
	   $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        //$search	= $app->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query	= $db->getQuery(true);
        
        $query->select('s.id AS value, concat(s.name, " (" , st.name, ")") AS text');
        $query->from('#__sportsmanagement_statistic AS s');
        $query->join('LEFT','#__sportsmanagement_position_statistic AS ps ON ps.statistic_id = s.id AND ps.position_id = '.(int) $id );
        $query->join('LEFT','#__sportsmanagement_sports_type AS st ON st.id = s.sports_type_id  ');
        $query->where('ps.id IS NULL');
        $query->order('s.ordering ASC');

		$db->setQuery($query);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		return $db->loadObjectList();
	}
    
    
    /**
     * sportsmanagementModelStatistics::getStatisticListSelect()
     * 
     * @return
     */
    public function getStatisticListSelect()
	{
	   $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        //$search	= $app->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query	= $db->getQuery(true);
        
        $query->select('id,name,id AS value,name AS text,short,class,note');
        $query->from('#__sportsmanagement_statistic');
        $query->order('name');
                
		$db->setQuery($query);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		return $db->loadObjectList();
	}
    
    
    

}
?>
