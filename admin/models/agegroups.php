<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       agegroups.php
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
 * sportsmanagementModelagegroups
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class sportsmanagementModelagegroups extends JSMModelList
{
    var $_identifier = "agegroups";
    
    /**
     * sportsmanagementModelagegroups::__construct()
     * 
     * @param  mixed $config
     * @return void
     */
    public function __construct($config = array())
    {   
                $config['filter_fields'] = array(
                        'obj.name',
                        'obj.alias',
                        'obj.age_from',
                        'obj.age_to',
                        'obj.deadline_day',
                        'obj.country',
                        'obj.sportstype_id',
                        'obj.id',
                        'obj.picture',
                        'obj.ordering',
                        'obj.published',
                        'obj.modified',
                        'obj.modified_by',
                        'obj.checked_out',
                        'obj.checked_out_time'
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
     * @since 1.6
     */
    protected function populateState($ordering = 'obj.name', $direction = 'asc')
    {
        if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend') ) {
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' context -> '.TVarDumper::dump($this->context, 10, true).''), '');
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' identifier -> '.TVarDumper::dump($this->context, 10, true).''), '');
        }
        // Load the filter state.
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $published);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.sports_type', 'filter_sports_type', '');
        $this->setState('filter.sports_type', $temp_user_request);
           $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
        $this->setState('filter.search_nation', $temp_user_request);
           $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
        $this->setState('list.limit', $value);    

        // List state information.
        parent::populateState($ordering, $direction);
           $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
        $this->setState('list.start', $value);

    }
    
    /**
     * sportsmanagementModelagegroups::getListQuery()
     * 
     * @return
     */
    function getListQuery()
    {
        // Create a new query object.		
        $this->jsmquery->clear();
        // Select some fields
        $this->jsmquery->select(implode(",", $this->filter_fields));
        $this->jsmquery->select('uc.name AS editor');
        // From table
        $this->jsmquery->from('#__sportsmanagement_agegroup as obj');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = obj.sportstype_id');
        $this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');
  
        if ($this->getState('filter.search') ) {
            $this->jsmquery->where('LOWER(obj.name) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%'));
        }
        
        if ($this->getState('filter.search_nation')) {
            $this->jsmquery->where('obj.country LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search_nation').'%'));
        }
        
        if ($this->getState('filter.sports_type')) {
            $this->jsmquery->where('obj.sportstype_id = '.$this->getState('filter.sports_type'));
        }
        
        if (is_numeric($this->getState('filter.state')) ) {
            $this->jsmquery->where('obj.published = '.$this->getState('filter.state'));    
        }

        $this->jsmquery->order(
            $this->jsmdb->escape($this->getState('list.ordering', 'obj.name')).' '.
            $this->jsmdb->escape($this->getState('list.direction', 'ASC'))
        );
        
        return $this->jsmquery;
        
    }
    
    /**
     * sportsmanagementModelagegroups::getAgeGroups()
     * 
     * @return
     */
    function getAgeGroups($country = '', $infotext = 0)
    {
        $this->jsmquery->clear();
        $this->jsmquery->select('a.id AS value');
    
        if ($infotext ) {
            $this->jsmquery->select('concat(a.name, \' von: \',a.age_from,\' bis: \',a.age_to,\' Stichtag: \',a.deadline_day) AS text');
        }
        else
        {
            $this->jsmquery->select('a.name AS text');    
        }
        if ($country ) {
            $this->jsmquery->where('a.country LIKE '.$this->jsmdb->Quote('%'.$country.'%'));
        }
        
        $this->jsmquery->from('#__sportsmanagement_agegroup as a');
                       
        $this->jsmquery->order('a.name ASC');

        $this->jsmdb->setQuery($this->jsmquery);
        if (!$result = $this->jsmdb->loadObjectList() ) {
            return array();
        }

        return $result;
    }

}
?>
