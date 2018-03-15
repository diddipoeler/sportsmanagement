<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      alllpersons.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage alllpersons
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');


/**
 * sportsmanagementModelallpersons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelallpersons extends JModelList
{

var $_identifier = "allpersons";
	var $limitstart = 0;
    var $limit = 0;
    var $columns= array();
    
	/**
	 * sportsmanagementModelallpersons::__construct()
	 * 
	 * @param mixed $config
	 * @return void
	 */
	public function __construct($config = array())
        {   
            // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $this->use_current_season = $jinput->getVar('use_current_season', '0','request','string');
                $this->limitstart = $jinput->getVar('limitstart', 0, '', 'int');
//                JFactory::getApplication()->input->setVar('limitstart', JFactory::getApplication()->input->getVar('limitstart', 0, '', 'int'));
                //$this->setState('limitstart', JFactory::getApplication()->input->getVar('limitstart', 0, '', 'int'));
              //  $this->limit = $this->getUserStateFromRequest($this->context.'.limit', 'limit', $app->getCfg('list_limit', 0));
                $config['filter_fields'] = array(
                        'v.lastname',
                        'v.firstname',
                        'v.picture',
                        'v.website',
                        'v.address',
                        'v.zipcode',
                        'v.city',
                        'v.country',
                        'v.birthday',
                        'v.deathday',
                        'v.position_id'
                        );
                parent::__construct($config);
        }

/**
 * Method to get the starting number of items for the data set.
 *
 * @return  integer  The starting number of items available in the data set.
 *
 * @since   11.1
 */
public function getStart()
{
    // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
    //$limitstart = $this->getUserStateFromRequest($this->context.'.limitstart', 'limitstart');
    $this->setState('list.start', $this->limitstart );
    
    $store = $this->getStoreId('getstart');

    // Try to load the data from internal storage.
    if (isset($this->cache[$store]))
    {
        return $this->cache[$store];
    }

    $start = $this->getState('list.start');
    $limit = $this->getState('list.limit');
    $total = $this->getTotal();
    if ($start > $total - $limit)
    {
        $start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
    }
    
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' limitstart<br><pre>'.print_r($limitstart,true).'</pre>'),'');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' this->limitstart<br><pre>'.print_r($this->limitstart,true).'</pre>'),'');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' store<br><pre>'.print_r($store,true).'</pre>'),'');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' list.start<br><pre>'.print_r($this->getState('list.start'),true).'</pre>'),'');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' list.limit<br><pre>'.print_r($this->getState('list.limit'),true).'</pre>'),'');

    // Add the total to the internal cache.
    $this->cache[$store] = $start;

    return $this->cache[$store];
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
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Initialise variables.
		$app = JFactory::getApplication('site');
        
        
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' request<br><pre>'.print_r($_REQUEST,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' limitstart<br><pre>'.print_r(JFactory::getApplication()->input->getVar('limitstart'),true).'</pre>'),'');
        
        // List state information
		//$value = JFactory::getApplication()->input->getUInt('limit', $app->getCfg('list_limit', 0));
        
        $value = $this->getUserStateFromRequest($this->context.'.limit', 'limit', $app->getCfg('list_limit', 0));
		$this->setState('list.limit', $value);
        
        //$this->setState('list.start', JFactory::getApplication()->input->getVar('limitstart', 0, '', 'int'));
        //$this->setState('list.start', $this->getUserStateFromRequest($this->context.'.limitstart', 'limitstart') );
        
        // In case limit has been changed, adjust limitstart accordingly
        //$this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));

//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' list.limit<br><pre>'.print_r($value,true).'</pre>'),'');

		//$limitstart = JFactory::getApplication()->input->getVar('limitstart', 0, '', 'int');
		//$limitstart = $this->getUserStateFromRequest($this->context.'.limitstart', 'limitstart',0);
        //$value = JFactory::getApplication()->input->getVar('limitstart');
//        $this->setState('limitstart', $this->limitstart);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' list.start<br><pre>'.print_r($this->getUserStateFromRequest($this->context.'.limitstart', 'limitstart'),true).'</pre>'),'');
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' limitstart<br><pre>'.print_r($this->getState('limitstart'),true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context<br><pre>'.print_r($this->context,true).'</pre>'   ),'');
        
        $columns = $jinput->getVar('show_columns');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' columns<br><pre>'.print_r($columns,true).'</pre>'),'');
        $this->setState('filter.select_columns', $columns);
        $this->columns = $columns;

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);

        //$filter_order = JFactory::getApplication()->input->getCmd('filter_order');
        $filter_order = $this->getUserStateFromRequest($this->context.'.filter_order', 'filter_order', '', 'string');
        if (!in_array($filter_order, $this->filter_fields)) 
        {
			$filter_order = 'v.lastname';
		}
        
        //$filter_order_Dir = JFactory::getApplication()->input->getCmd('filter_order_Dir');
        $filter_order_Dir = $this->getUserStateFromRequest($this->context.'.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
        if (!in_array(strtoupper($filter_order_Dir), array('ASC', 'DESC', ''))) 
        {
			$filter_order_Dir = 'ASC';
		}

        $this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);
  
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ordering<br><pre>'.print_r($filter_order,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' direction<br><pre>'.print_r($filter_order_Dir,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context<br><pre>'.print_r($this->context,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' listOrder<br><pre>'.print_r($listOrder,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' orderCol<br><pre>'.print_r($orderCol,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('v.lastname', 'ASC');
	}
    
    
    /**
     * sportsmanagementModelallplaygrounds::getListQuery()
     * 
     * @return
     */
    function getListQuery()
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        //$search	= $this->getState('filter.search');
        //$search_nation	= $this->getState('filter.search_nation');
        $select_columns	= $this->getState('filter.select_columns');
        
        if ( $select_columns )
        {
        foreach( $select_columns as $key => $value )
        {
            $select_columns[$key] = 'v.'.$value;
        } 
        }
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' select_columns' .  ' <br><pre>'.print_r($select_columns,true).'</pre>'),'Notice');
        
        //$select_columns_temp	= implode(",",$select_columns);
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' select_columns_temp' .  ' <br><pre>'.print_r($select_columns_temp,true).'</pre>'),'Notice');
        
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
		
        // Select some fields
        if ( $select_columns )
        {
        $query->select(implode(",",$select_columns)); 
        $query->select('v.id'); 
        }
        else
        {
        $query->select('v.*');    
        }
		
        $query->select('CONCAT_WS( \':\', v.id, v.alias ) AS slug');
        $query->select('CONCAT_WS( \':\', p.id, p.alias ) AS projectslug');
        $query->select('CONCAT_WS( \':\', t.id, t.alias ) AS teamslug');
        
        $query->select('po.name as position_name'); 
        // From table
		$query->from('#__sportsmanagement_person as v');
        // Join over the clubs
//		$query->select('c.name As club');
//		$query->join('LEFT','#__sportsmanagement_club AS c ON c.id = v.club_id');
        $query->join('INNER','#__sportsmanagement_season_team_person_id AS stp ON stp.person_id = v.id');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = stp.team_id');
        $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id');
        $query->join('INNER','#__sportsmanagement_team AS t ON t.id = stp.team_id');
        $query->join('LEFT','#__sportsmanagement_position AS po ON po.id = v.position_id');
        
//        // Join over the users for the checked out user.
//		$query->select('uc.name AS editor');
//		$query->join('LEFT', '#__users AS uc ON uc.id = v.checked_out');
        
        
        if ($this->getState('filter.search'))
		{
        $query->where('LOWER(v.lastname) LIKE '.$db->Quote('%'.$this->getState('filter.search').'%'));
        }
        if ($this->getState('filter.search_nation'))
		{
        //$query->where("v.country = '".$search_nation."'");
        $query->where('v.country LIKE '.$db->Quote(''.$this->getState('filter.search_nation').''));
        }
        if ( $this->use_current_season )
        {
        $filter_season = JComponentHelper::getParams($option)->get('current_season',0);    
        $query->where('p.season_id IN ('.implode(',',$filter_season).')');
        }
        
        //$query->limit($this->getState('list.start'));
        
        $query->group('v.id');

        $query->order($db->escape($this->getState('filter_order', 'v.lastname')).' '.$db->escape($this->getState('filter_order_Dir', 'ASC') ) );
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' ordering<br><pre>'.print_r($this->getState('filter_order'),true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' direction<br><pre>'.print_r($this->getState('filter_order_Dir'),true).'</pre>'),'');
        
		return $query;

	}
    
    
}

?>    