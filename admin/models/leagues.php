<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       leagues.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelLeagues
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelLeagues extends JSMModelList
{
    var $_identifier = "leagues";
    
    /**
     * sportsmanagementModelLeagues::__construct()
     * 
     * @param  mixed $config
     * @return void
     */
    public function __construct($config = array())
    {   
                $config['filter_fields'] = array(
                        'obj.name',
                        'obj.alias',
                        'obj.short_name',
                        'obj.country',
                        'obj.published_act_season',
                        'st.name',
                        'obj.id',
                        'obj.ordering',
                        'obj.published',
                        'obj.modified',
                        'obj.modified_by',
                        'ag.name',
                        'fed.name'
                        );
                parent::__construct($config);
                parent::setDbo($this->jsmdb);
    }
        
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since 1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info') ) {
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' context -> '.$this->context.''), '');
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' identifier -> '.$this->_identifier.''), '');
        }
        
        // Load the filter state.
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $published);
           $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
        $this->setState('filter.search_nation', $temp_user_request);
           $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_agegroup', 'filter_search_agegroup', '');
        $this->setState('filter.search_agegroup', $temp_user_request);
           $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_association', 'filter_search_association', '');
        $this->setState('filter.search_association', $temp_user_request);
           $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.federation', 'filter_federation', '');
           $this->setState('filter.federation', $temp_user_request);  
           $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
        $this->setState('list.limit', $value);

        // List state information.
           $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
        $this->setState('list.start', $value);
        // Filter.order
        $orderCol = $this->getUserStateFromRequest($this->context. '.filter_order', 'filter_order', '', 'string');
        if (!in_array($orderCol, $this->filter_fields)) {
            $orderCol = 'obj.name';
        }
        $this->setState('list.ordering', $orderCol);
        $listOrder = $this->getUserStateFromRequest($this->context. '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
        if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
            $listOrder = 'ASC';
        }
        $this->setState('list.direction', $listOrder);
    }
    
    /**
     * sportsmanagementModelLeagues::getListQuery()
     * 
     * @return
     */
    protected function getListQuery()
    {
        // Create a new query object.		
        $this->jsmquery->clear();
        // Select some fields
        $this->jsmquery->select('obj.name,obj.short_name,obj.alias,obj.associations,obj.country,obj.ordering,obj.id,obj.picture,obj.checked_out,obj.checked_out_time,obj.agegroup_id');
        $this->jsmquery->select('obj.published,obj.modified,obj.modified_by,obj.published_act_season');
        $this->jsmquery->select('st.name AS sportstype');
        // From table
        $this->jsmquery->from('#__sportsmanagement_league as obj');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = obj.sports_type_id');
        // Join over the users for the checked out user.
        $this->jsmquery->select('uc.name AS editor');
        $this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');
        
        $this->jsmquery->select('ag.name AS agegroup');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_agegroup AS ag ON ag.id = obj.agegroup_id');
        
        $this->jsmquery->select('fed.name AS fedname');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_associations AS fed ON fed.id = obj.associations');
        
        if ($this->getState('filter.search')) {
            $this->jsmquery->where('LOWER(obj.name) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%'));
        }
        
        if ($this->getState('filter.search_nation')) {
            $this->jsmquery->where('obj.country LIKE '.$this->jsmdb->Quote(''.$this->getState('filter.search_nation').''));
        }
        
        if ($this->getState('filter.search_association')) {
            $this->jsmquery->where('obj.associations = '.$this->getState('filter.search_association'));
        }
        
        if ($this->getState('filter.federation')) {
            $this->jsmquery->where('obj.associations = '.$this->getState('filter.federation'));
        }
        
        if ($this->getState('filter.search_agegroup')) {
            $this->jsmquery->where('obj.agegroup_id = ' . $this->getState('filter.search_agegroup'));
        }
        
        if (is_numeric($this->getState('filter.state'))) {
            $this->jsmquery->where('obj.published = '.$this->getState('filter.state'));
        }

        $this->jsmquery->order(
            $this->jsmdb->escape($this->getState('list.ordering', 'obj.name')).' '.
            $this->jsmdb->escape($this->getState('list.direction', 'ASC'))
        );
 
        return $this->jsmquery;
    }

  



    
    /**
     * Method to return a leagues array (id,name)
     *
     * @access public
     * @return array seasons
     * @since  1.5.0a
     */
    //public static function getLeagues()
    function getLeagues()
    {

        $search_nation = '';
        

        
        if ($this->jsmapp->isClient('administrator') ) {
            $search_nation    = $this->getState('filter.search_nation');
            //$search_nation	= self::getState('filter.search_nation');
        }
        //        // Get a db connection.
        //        $db = sportsmanagementHelper::getDBConnection();
        //        // Create a new query object.
        //        $query = $db->getQuery(true);
        $this->jsmquery->select('id,name');
        $this->jsmquery->from('#__sportsmanagement_league');
        
        if ($search_nation) {
            $this->jsmquery->where('country LIKE '.$this->jsmdb->Quote(''.$search_nation.''));
        }
        
        $this->jsmquery->order('name ASC');

        $this->jsmdb->setQuery($this->jsmquery);
        if (!$result = $this->jsmdb->loadObjectList()) {
            //sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->jsmdb->getErrorMsg(), __LINE__);

            return array();
        }
        foreach ($result as $league)
        {
            $league->name = Text::_($league->name);
        }
        return $result;
    }

    
}
?>
