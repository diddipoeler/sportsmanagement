<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       jlextcountries.php
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
 * sportsmanagementModeljlextcountries
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModeljlextcountries extends JSMModelList
{
	var $_identifier = "jlextcountries";

	/**
	 * sportsmanagementModeljlextcountries::__construct()
	 *
	 * @param   mixed $config
	 * @return void
	 */
	public function __construct($config = array())
	{
				$config['filter_fields'] = array(
						'objcountry.name',
						'objcountry.picture',
						'objcountry.id',
						'objcountry.alpha2',
						'objcountry.alpha3',
						'objcountry.itu',
						'objcountry.fips',
						'objcountry.ioc',
						'objcountry.fifa',
						'objcountry.ds',
						'objcountry.wmo',
						'objcountry.ordering',
						'objcountry.checked_out',
						'objcountry.published',
						'objcountry.checked_out_time'
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
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' context -> ' . $this->context . ''), '');
			  $this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' identifier -> ' . $this->_identifier . ''), '');
		}

			  // Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $published);
		$federation = $this->getUserStateFromRequest($this->context . '.filter.federation', 'filter_federation', '');
		$this->setState('filter.federation', $federation);
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
		$orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'objcountry.name';
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
	 * sportsmanagementModeljlextcountries::getListQuery()
	 *
	 * @return
	 */
	function getListQuery()
	{
		// Create a new query object.
		$this->jsmquery->clear();
		$this->jsmsubquery1->clear();
		$this->jsmsubquery2->clear();

			  // Select some fields
		$this->jsmquery->select(implode(",", $this->filter_fields));
		$this->jsmquery->select('f.name as federation_name');

		// From table
		$this->jsmquery->from('#__sportsmanagement_countries AS objcountry');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_federations AS f ON f.id = objcountry.federation');

		// Join over the users for the checked out user.
		$this->jsmquery->select('uc.name AS editor');
		$this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = objcountry.checked_out');

		if ($this->getState('filter.search'))
		{
			$this->jsmquery->where('LOWER(objcountry.name) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
		}

		if ($this->getState('filter.federation'))
		{
			$this->jsmquery->where('objcountry.federation = ' . $this->getState('filter.federation'));
		}

		if (is_numeric($this->getState('filter.state')))
		{
			$this->jsmquery->where('objcountry.published = ' . $this->getState('filter.state'));
		}

			  $this->jsmquery->order(
				  $this->jsmdb->escape($this->getState('list.ordering', 'objcountry.name')) . ' ' .
				  $this->jsmdb->escape($this->getState('list.direction', 'ASC'))
			  );

			 return $this->jsmquery;

	}



	/**
	 * sportsmanagementModeljlextcountries::getFederation()
	 *
	 * @return void
	 */
	function getFederation()
	{
		// Select some fields
		$this->jsmquery->clear();
		$this->jsmquery->select('id as value,name as text');

		// From the table
		$this->jsmquery->from('#__sportsmanagement_federations as objassoc');

		try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			$results = $this->jsmdb->loadObjectList();

			if (!$results)
			{
				$this->jsmmessage .= '<br>' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_FEDERATIONS_NULL');
			}

			return $results;
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');

			return false;
		}

	}

}
