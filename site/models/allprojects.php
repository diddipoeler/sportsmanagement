<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      allprojects.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage allprojects
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');


/**
 * sportsmanagementModelallprojects
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelallprojects extends JModelList
{

var $_identifier = "allprojects";
	var $limitstart = 0;
    var $limit = 0;
    
	/**
	 * sportsmanagementModelallprojects::__construct()
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
                $config['filter_fields'] = array(
                        'v.name',
                        'v.picture',
                        'v.website',
                        'l.name',
                        's.name',
                        'v.location',
                        'l.country'
                        );
                parent::__construct($config);
                $getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($getDBConnection);
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
        
        // List state information
		//$value = JFactory::getApplication()->input->getUInt('limit', $app->getCfg('list_limit', 0));
		$value = $this->getUserStateFromRequest($this->context.'.limit', 'limit', $app->getCfg('list_limit', 0));
        $this->setState('list.limit', $value);
        
        //$temp_user_request = $this->getUserStateFromRequest($this->context.'.limit', 'limit', $app->getCfg('list_limit', 0));
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($temp_user_request,true).'</pre>'   ),'');

		//$value = JFactory::getApplication()->input->getUInt('limitstart', 0);
//		$this->setState('list.start', $value);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($this->getUserStateFromRequest(),true).'</pre>'   ),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);
        
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_leagues', 'filter_search_leagues', '');
        if ( !$temp_user_request )
        {
//            $temp_user_request = JFactory::getApplication()->input->getInt( "l", 0 );
//            JFactory::getApplication()->input->setVar( "l", 0 );
        }
        
		$this->setState('filter.search_leagues', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_seasons', 'filter_search_seasons', '');
		$this->setState('filter.search_seasons', $temp_user_request);

        //$filter_order = JFactory::getApplication()->input->getCmd('filter_order');
        $filter_order = $this->getUserStateFromRequest($this->context.'.filter_order', 'filter_order', '', 'string');
        if (!in_array($filter_order, $this->filter_fields)) 
        {
			$filter_order = 'v.name';
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
		//parent::populateState('v.name', 'ASC');
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
        
        //$search_leagues	= $this->getState('filter.search_leagues');
        //$search_seasons	= $this->getState('filter.search_seasons');
        
        
        // Create a new query object.
		$db		= sportsmanagementHelper::getDBConnection();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
		
        // Select some fields
		$query->select('v.id,v.name,v.picture');
        $query->select('l.country,l.name as leaguename');
        $query->select('s.name as seasonname');
        $query->select('CONCAT_WS( \':\', v.id, v.alias ) AS slug');
        $query->select('CONCAT_WS( \':\', l.id, l.alias ) AS leagueslug');
        // From table
		$query->from('#__sportsmanagement_project AS v');
        $query->join('INNER','#__sportsmanagement_league AS l ON l.id = v.league_id');
        $query->join('INNER','#__sportsmanagement_season AS s ON s.id = v.season_id');
//        $query->join('LEFT','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
//        $query->join('LEFT','#__sportsmanagement_project AS p ON p.id = pt.project_id');
        
//        // Join over the users for the checked out user.
//		$query->select('uc.name AS editor');
//		$query->join('LEFT', '#__users AS uc ON uc.id = v.checked_out');
        
        
        if ($this->getState('filter.search'))
		{
        $query->where('LOWER(v.name) LIKE '.$db->Quote('%'.$this->getState('filter.search').'%'));
        }
        if ($this->getState('filter.search_nation'))
		{
        //$query->where("l.country = '".$search_nation."'");
        $query->where('l.country LIKE '.$db->Quote(''.$this->getState('filter.search_nation').''));
        }
        
        if ($this->getState('filter.search_leagues'))
		{
        $query->where('v.league_id = ' . $this->getState('filter.search_leagues'));
        }
        if ($this->getState('filter.search_seasons'))
		{
        $query->where('v.season_id = ' . $this->getState('filter.search_seasons'));
        }
        
        if ( $this->use_current_season )
        {
        $filter_season = JComponentHelper::getParams($option)->get('current_season',0);    
        $query->where('v.season_id IN ('.implode(',',$filter_season).')');
        }
        
        $query->where('v.published = 1');
        
        $query->group('v.id');
        

        $query->order($db->escape($this->getState('filter_order', 'v.name')).' '.$db->escape($this->getState('filter_order_Dir', 'ASC') ) );
        
if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');

        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' ordering<br><pre>'.print_r($this->getState('filter_order'),true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' direction<br><pre>'.print_r($this->getState('filter_order_Dir'),true).'</pre>'),'');
        
		return $query;

	}
    
    
}

?>    