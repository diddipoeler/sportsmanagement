<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       eventtypes.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Sportsmanagement Component Events Model
 *
 * @package Sportsmanagement
 * @since   1.5.0a
 */
class sportsmanagementModelEventtypes extends JSMModelList
{
	var $_identifier = "eventtypes";

	/**
	 * sportsmanagementModelEventtypes::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		$config['filter_fields'] = array(
			'obj.name',
			'obj.icon',
			'obj.sports_type_id',
			'obj.published',
			'obj.modified',
			'obj.modified_by',
			'obj.id',
			'obj.ordering',
            'state',
            'sports_type'
		);
		parent::__construct($config);
		$getDBConnection = sportsmanagementHelper::getDBConnection();
		parent::setDbo($getDBConnection);
	}

	/**
	 * sportsmanagementModelEventtypes::getEvents()
	 *
	 * @param   integer  $sports_type_id
	 *
	 * @return
	 */
	public static function getEvents($sports_type_id = 0)
	{
		$jsmdb    = sportsmanagementHelper::getDBConnection();
		$jsmquery = $jsmdb->getQuery(true);
		$result = array();

		$jsmquery->clear();
		$jsmquery->select('evt.id AS value, concat(evt.name, " (" , st.name, ")") AS text,evt.name as posname,st.name AS stname');
		$jsmquery->from('#__sportsmanagement_eventtype as evt');
		$jsmquery->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = evt.sports_type_id');
		$jsmquery->where('evt.published = 1');

		if ($sports_type_id)
		{
			$jsmquery->where('evt.sports_type_id = ' . $sports_type_id);
		}

		$jsmquery->order('evt.name ASC');

		$jsmdb->setQuery($jsmquery);

		if (!$result = $jsmdb->loadObjectList())
		{
			return $result;
		}

		foreach ($result as $position)
		{
			$position->text = Text::_($position->posname) . ' (' . Text::_($position->stname) . ')';
		}

		return $result;
	}

	/**
	 * sportsmanagementModelEventtypes::getListQuery()
	 *
	 * @return
	 */
	function getListQuery()
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('obj.*');
		$this->jsmquery->from('#__sportsmanagement_eventtype as obj');
		$this->jsmquery->select('st.name AS sportstype');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = obj.sports_type_id');
		$this->jsmquery->select('uc.name AS editor');
		$this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');

		if ($this->getState('filter.search'))
		{
			$this->jsmquery->where('LOWER(obj.name) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
		}

		if (is_numeric($this->getState('filter.state')))
		{
			$this->jsmquery->where('obj.published = ' . $this->getState('filter.state'));
		}

		if ($this->getState('filter.sports_type'))
		{
			$this->jsmquery->where('obj.sports_type_id = ' . $this->getState('filter.sports_type'));
		}

		$this->jsmquery->order(
			$this->jsmdb->escape($this->getState('list.ordering', 'obj.name')) . ' ' .
			$this->jsmdb->escape($this->getState('list.direction', 'ASC'))
		);

		return $this->jsmquery;

	}

	/**
	 * Method to return the position events array (id,name)
	 *
	 * @access public
	 * @return array
	 * @since  0.1
	 */
	function getEventsPosition($id = 0)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('p.id AS value,p.name as posname,st.name AS stname,concat(p.name, \' (\' , st.name, \')\') AS text');
		$this->jsmquery->from('#__sportsmanagement_eventtype AS p');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_position_eventtype AS pe ON pe.eventtype_id=p.id');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = p.sports_type_id');

		if ($id)
		{
			$this->jsmquery->where('pe.position_id = ' . $id);
		}

		$this->jsmquery->order('pe.ordering ASC');

		try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->loadObjectList();

			foreach ($result as $event)
			{
				$event->text = Text::_($event->posname) . ' (' . Text::_($event->stname) . ')';
			}

			return $result;
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');

			return false;
		}
	}

	/**
	 * sportsmanagementModelEventtypes::getEventList()
	 *
	 * @return
	 */
	public function getEventList()
	{
		$query = 'SELECT *,id AS value,name AS text FROM #__sportsmanagement_eventtype ORDER BY name';
		$this->_db->setQuery($query);

		return $this->_db->loadObjectList();
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
		if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info'))
		{
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' context -> ' . $this->context . ''), '');
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' identifier -> ' . $this->_identifier . ''), '');
		}

        $list = $this->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array');
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));
		$this->setState('filter.sports_type', $this->getUserStateFromRequest($this->context . '.filter.sports_type', 'filter_sports_type', ''));
		$this->setState('list.limit', $this->getUserStateFromRequest($this->context . '.list.limit', 'list_limit', $this->jsmapp->get('list_limit'), 'int'));
		$this->setState('list.start', $this->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int'));

		$orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'obj.name';
		}

		$this->setState('list.ordering', $orderCol);
		$listOrder = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);
	}


}
