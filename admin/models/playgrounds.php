<?php
/**
 * @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


jimport('joomla.application.component.modellist');

/**
 * Sportsmanagement Component Venues Model
 *
 * @package	Sportsmanagement
 * @since	0.1
 */
class sportsmanagementModelPlaygrounds extends JModelList
{
	var $_identifier = "playgrounds";
	
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'v.name',
                        'v.id',
                        'v.ordering'
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
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('v.name', 'asc');
	}
    
	function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $search	= $this->getState('filter.search');
        $search_nation	= $this->getState('filter.search_nation');
        
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
		
        // Select some fields
		$query->select('v.*');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_playground as v');
        // Join over the clubs
		$query->select('c.name As club');
		$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_club AS c ON c.id = v.club_id');
        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = v.checked_out');
        
        
        if ($search)
		{
        $query->where('LOWER(v.name) LIKE '.$db->Quote('%'.$search.'%'));
        }
        if ($search_nation)
		{
        $query->where("v.country = '".$search_nation."'");
        }

        
        $query->order($db->escape($this->getState('list.ordering', 's.name')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        
		return $query;
        
        
        
	}




    
    
    /**
	 * Method to return a playground/venue array (id,text)
		*
		* @access	public
		* @return	array
		* @since 0.1
		*/
	function getPlaygrounds()
	{
		$query='SELECT id AS value, name AS text FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_playground ORDER BY text ASC ';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		return $result;
	}
    
    public function getPlaygroundListSelect()
	{
		$query='SELECT id,name,id AS value,name AS text,short_name,club_id FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_playground ORDER BY name';
		$this->_db->setQuery($query);
		if ($results=$this->_db->loadObjectList())
		{
			return $results;
		}
		return false;
	}
    
    

	
}
?>
