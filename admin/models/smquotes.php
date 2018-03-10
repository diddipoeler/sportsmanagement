<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementModelsmquotes
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelsmquotes extends JSMModelList
{
	var $_identifier = "smquotes";
	
	/**
	 * sportsmanagementModelsmquotes::__construct()
	 * 
	 * @param mixed $config
	 * @return void
	 */
	public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'obj.quote',
                        'obj.id',
                        'obj.ordering'
                        );
                parent::__construct($config);
                parent::setDbo($this->jsmdb);
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
		if ( JComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend') )
        {
		//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context -> '.$this->context.''),'');
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' identifier -> '.$this->_identifier.''),'');
        $this->jsmmessage .= '<br>'.JText::_(__METHOD__.' '.__LINE__.' context -> '.$this->context);
        $this->jsmmessage .= '<br>'.JText::_(__METHOD__.' '.__LINE__.' identifier -> '.$this->_identifier.'');
        }

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $published);
		$categoryId = $this->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id', '');
		$this->setState('filter.category_id', $categoryId);
        $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
		$this->setState('list.limit', $value);
        $value = $this->getUserStateFromRequest($this->context . '.list.ordering', 'ordering', $ordering, 'string');
		$this->setState('list.ordering', $value);
		$value = $this->getUserStateFromRequest($this->context . '.list.direction', 'direction', $direction, 'string');
		$this->setState('list.direction', $value);
		// List state information.
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);
        // Filter.order
		$orderCol = $this->getUserStateFromRequest($this->context. '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'obj.quote';
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
     * sportsmanagementModelsmquotes::getListQuery()
     * 
     * @return
     */
    protected function getListQuery()
	{
		// Create a new query object.		
		$this->jsmquery->clear();
        $this->jsmsubquery1->clear();
        $this->jsmsubquery2->clear();
		// Select some fields
		$this->jsmquery->select('obj.*,obj.author as name');
		// From the hello table
		$this->jsmquery->from('#__sportsmanagement_rquote as obj');
        // Join over the users for the checked out user.
		$this->jsmquery->select('uc.name AS editor');
		$this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');
        // Join over the categories.
		$this->jsmquery->select('c.title AS category_title');
		$this->jsmquery->join('LEFT', '#__categories AS c ON c.id = obj.catid');

        if ( $this->getState('filter.search')  )
		{
        $this->jsmquery->where('LOWER(obj.author) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%'));
        }
        if ( $this->getState('filter.state') )
		{
        $this->jsmquery->where('obj.published = '.$this->getState('filter.state'));
        }
        if ( $this->getState('filter.category_id') )
		{
        $this->jsmquery->where('obj.catid = '.$this->getState('filter.category_id'));
        }

        $this->jsmquery->order($this->jsmdb->escape($this->getState('list.ordering', 'obj.quote')).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));
 
		if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text .= ' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
        
        return $this->jsmquery;
	}

	
}
?>