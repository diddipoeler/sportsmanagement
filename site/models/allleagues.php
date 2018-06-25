<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      allleagues.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage allleagues
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * sportsmanagementModelallleagues
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelallleagues extends JSMModelList
{

var $_identifier = "allleagues";
	var $limitstart = 0;
    var $limit = 0;
    
	/**
	 * sportsmanagementModelallleagues::__construct()
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
                        'v.country'
                        );
                parent::__construct($config);
                //$getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($this->jsmdb);
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

		$value = $jinput->getUInt('limitstart', 0);
		$this->setState('list.start', $value);
        
        //$app->enqueueMessage(JText::_('sportsmanagementModelsmquotes populateState context<br><pre>'.print_r($this->context,true).'</pre>'   ),'');

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
     * sportsmanagementModelallleagues::getListQuery()
     * 
     * @return
     */
    function getListQuery()
	{
		//// Reference global application object
//        $app = JFactory::getApplication();
//        // JInput object
//        $jinput = $app->input;
//        
        
        // Create a new query object.
//		$db		= sportsmanagementHelper::getDBConnection();
//		$query	= $db->getQuery(true);
		//$user	= JFactory::getUser(); 
		
        // Select some fields
        $this->jsmquery->clear();
		$this->jsmquery->select('v.id,v.name,v.picture,v.country');
        // From table
		$this->jsmquery->from('#__sportsmanagement_league AS v');
      
        
        if ($this->getState('filter.search'))
		{
        $this->jsmquery->where('LOWER(v.name) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%'));
        }
        if ($this->getState('filter.search_nation'))
		{
        $this->jsmquery->where('v.country LIKE '.$this->jsmdb->Quote(''.$this->getState('filter.search_nation').''));
        }
        
        if ( $this->use_current_season )
        {
        $filter_season = JComponentHelper::getParams($this->jsmoption)->get('current_season',0);    
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($filter_season,true).'</pre>'),'Notice');
        $this->jsmquery->join('INNER','#__sportsmanagement_project AS p ON v.id = p.league_id');
/**
 * sicherheitshalber noch eine abfrage, wenn der user die nutzung der saison eingeschaltet,
 * aber keine saisons hintlergt hat
*/        
        if ( $filter_season )
        {
        $this->jsmquery->where('p.season_id IN ('.implode(',',$filter_season).')');
        }
        }
        
        $this->jsmquery->group('v.id');

        $this->jsmquery->order($this->jsmdb->escape($this->getState('filter_order', 'v.name')).' '.$this->jsmdb->escape($this->getState('filter_order_Dir', 'ASC') ) );
if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {        
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'Notice');
        }
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' ordering<br><pre>'.print_r($this->getState('filter_order'),true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' direction<br><pre>'.print_r($this->getState('filter_order_Dir'),true).'</pre>'),'');
        
		return $this->jsmquery;

	}
    
    
}

?>    