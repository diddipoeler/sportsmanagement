<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       predictiongroups.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelpredictiongroups
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelpredictiongroups extends JSMModelList
{
	var $_identifier = "predictiongroups";

	/**
	 * sportsmanagementModelpredictiongroups::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		$config['filter_fields'] = array(
			's.name',
			's.ordering',
			's.id',
			's.modified',
			's.modified_by'
		);
		parent::__construct($config);
		parent::setDbo($this->jsmdb);
	}

	/**
	 * sportsmanagementModelpredictiongroups::getListQuery()
	 *
	 * @return
	 */
	function getListQuery()
	{
		// Create a new query object.
		$this->jsmquery->clear();
		$this->jsmquery->select(array('s.*', 'u.name AS editor', 'u1.username'))
			->from('#__sportsmanagement_prediction_groups AS s')
			->join('LEFT', '#__users AS u ON u.id = s.checked_out')
			->join('LEFT', '#__users AS u1 ON u1.id = s.modified_by');

		if ($this->getState('filter.search'))
		{
			$this->jsmquery->where('(LOWER(s.name) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
		}

		$this->jsmquery->order(
			$this->jsmdb->escape($this->getState('list.ordering', 's.name')) . ' ' .
			$this->jsmdb->escape($this->getState('list.direction', 'ASC'))
		);

		return $this->jsmquery;
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
        
		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));

		$this->setState('filter.prediction_id', $this->getUserStateFromRequest($this->context . '.filter.prediction_id', 'filter_prediction_id', '', 'string'));

		// List state information.
		$this->setState('list.limit', $this->getUserStateFromRequest($this->context . '.list.limit', 'list_limit', $this->jsmapp->get('list_limit'), 'int'));
		$this->setState('list.start', $this->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int'));

		// Filter.order
		$orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 's.name';
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
