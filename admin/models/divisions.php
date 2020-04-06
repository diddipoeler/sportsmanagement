<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       divisions.php
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
 * sportsmanagementModelDivisions
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelDivisions extends JSMModelList
{
    var $_identifier = "divisions";
    static  $_project_id = 0;
    
    /**
     * sportsmanagementModelDivisions::__construct()
     * 
     * @param  mixed $config
     * @return void
     */
    public function __construct($config = array())
    {   
                $config['filter_fields'] = array(
                        'dv.name',
                        'dv.alias',
                        'dv.id',
                        'dv.ordering',
                        'dv.picture'
                        );
                parent::__construct($config);
                $getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($getDBConnection);
                self::$_project_id    = $this->jsmjinput->getInt('pid', 0);
        if (!self::$_project_id ) {
            self::$_project_id    = $this->jsmapp->getUserState("$this->jsmoption.pid", '0');    
        }
                $this->jsmapp->setUserState("$this->jsmoption.pid", self::$_project_id); 
                
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
        if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend') ) {
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' context -> '.$this->context.''), '');
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' identifier -> '.$this->_identifier.''), '');
        }
        // Load the filter state.
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $published);
           $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
        $this->setState('list.limit', $value);    
        
           // List state information.
           $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
        $this->setState('list.start', $value);
        // Filter.order
        $orderCol = $this->getUserStateFromRequest($this->context. '.filter_order', 'filter_order', '', 'string');
        if (!in_array($orderCol, $this->filter_fields)) {
            $orderCol = 'dv.name';
        }
        $this->setState('list.ordering', $orderCol);
        $listOrder = $this->getUserStateFromRequest($this->context. '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
        if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
            $listOrder = 'ASC';
        }
        $this->setState('list.direction', $listOrder);
        
    }
    
    /**
     * sportsmanagementModelDivisions::getListQuery()
     * 
     * @return
     */
    protected function getListQuery()
    {
        $this->jsmquery->clear();
        $this->jsmquery->select('dv.*,dvp.name AS parent_name,u.name AS editor');
        $this->jsmquery->from('#__sportsmanagement_division AS dv');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_division AS dvp ON dvp.id = dv.parent_id');
        $this->jsmquery->join('LEFT', '#__users AS u ON u.id = dv.checked_out');
        $this->jsmquery->where(' dv.project_id = ' . self::$_project_id);
        
        if ($this->getState('filter.search') ) {
            $this->jsmquery->where('LOWER(dv.name) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
        }
        
        if (is_numeric($this->getState('filter.state')) ) {
            $this->jsmquery->where('dv.published = '.$this->getState('filter.state'));    
        }
        
        $this->jsmquery->order(
            $this->jsmdb->escape($this->getState('list.ordering', 'dv.name')).' '.
            $this->jsmdb->escape($this->getState('list.direction', 'ASC'))
        );

        return $this->jsmquery;
    }

    /**
    * Method to return a divisions array (id, name)
    *
    * @param  int $project_id
    * @access public
    * @return array
    * @since  0.1
    */
    function getDivisions($project_id)
    {
        $starttime = microtime();
        $this->jsmquery->clear(); 
        $this->jsmquery->select('id AS value,name AS text');
        $this->jsmquery->from('#__sportsmanagement_division');
        $this->jsmquery->where('project_id = ' . $project_id);
        $this->jsmquery->order('name ASC');
        $this->jsmdb->setQuery($this->jsmquery);
       
        if (!$result = $this->jsmdb->loadObjectList("value") ) {
            return array();
        }
        else
        {
            return $result;
        }
        
    }
    
    /**
     * return count of project divisions
     *
     * @param  int project_id
     * @return int
     */
    function getProjectDivisionsCount($project_id)
    {
        $starttime = microtime(); 
        $this->jsmquery->clear();
        $this->jsmquery->select('count(*) AS count');
        $this->jsmquery->from('#__sportsmanagement_division AS d');
        $this->jsmquery->join('INNER', '#__sportsmanagement_project AS p on p.id = d.project_id');
        $this->jsmquery->where('p.id = ' . $project_id);
        $this->jsmdb->setQuery($this->jsmquery);
        return $this->jsmdb->loadResult();
    }
    
}
?>
