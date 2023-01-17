<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       smquotes.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelsmquotes
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelsmquotes extends JSMModelList
{
	var $_identifier = "smquotes";

	/**
	 * sportsmanagementModelsmquotes::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		$config['filter_fields'] = array(
			'obj.quote',
			'obj.id',
			'obj.ordering',
			'obj.author',
			'obj.catid',
			'obj.published',
			'obj.quote'
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
		if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend'))
		{
			$this->jsmmessage .= '<br>' . Text::_(__METHOD__ . ' ' . __LINE__ . ' context -> ' . $this->context);
			$this->jsmmessage .= '<br>' . Text::_(__METHOD__ . ' ' . __LINE__ . ' identifier -> ' . $this->_identifier . '');
		}
$list = $this->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array');
		
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));
		$this->setState('filter.catid', $this->getUserStateFromRequest($this->context . '.filter.catid', 'filter_catid', ''));
		$this->setState('list.limit', $this->getUserStateFromRequest($this->context . '.list.limit', 'list_limit', $this->jsmapp->get('list_limit'), 'int'));
		$this->setState('list.start', $this->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int'));
/*		
		$value = $this->getUserStateFromRequest($this->context . '.list.ordering', 'ordering', $ordering, 'string');
		$this->setState('list.ordering', $value);
		$value = $this->getUserStateFromRequest($this->context . '.list.direction', 'direction', $direction, 'string');
		$this->setState('list.direction', $value);
*/
		$orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'obj.quote';
		}

		$this->setState('list.ordering', $orderCol);
		$listOrder = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');

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
		$this->jsmquery->clear();
		$this->jsmsubquery1->clear();
		$this->jsmsubquery2->clear();
		$this->jsmquery->select('obj.*,obj.author as name');
		$this->jsmquery->from('#__sportsmanagement_rquote as obj');
		$this->jsmquery->select('uc.name AS editor');
		$this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');
		$this->jsmquery->select('c.title AS category_title');
		$this->jsmquery->join('LEFT', '#__categories AS c ON c.id = obj.catid');

		if ($this->getState('filter.search'))
		{
			$this->jsmquery->where('LOWER(obj.author) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
		}

		if ($this->getState('filter.state'))
		{
			$this->jsmquery->where('obj.published = ' . $this->getState('filter.state'));
		}

		if ($this->getState('filter.catid'))
		{
			$this->jsmquery->where('obj.catid = ' . $this->getState('filter.catid'));
		}

		$this->jsmquery->order(
			$this->jsmdb->escape($this->getState('list.ordering', 'obj.quote')) . ' ' .
			$this->jsmdb->escape($this->getState('list.direction', 'ASC'))
		);

		return $this->jsmquery;
	}


}
