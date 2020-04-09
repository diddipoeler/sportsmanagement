<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage allleagues
 * @file       allleagues.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelallleagues
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelallleagues extends JSMModelList
{
	var $_identifier = "allleagues";

	var $limitstart = 0;

	var $limit = 0;

	/**
	 * sportsmanagementModelallleagues::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput                   = $app->input;
		$this->use_current_season = $jinput->getVar('use_current_season', '0', 'request', 'string');
		$this->limitstart         = $jinput->getVar('limitstart', 0, '', 'int');
		$config['filter_fields']  = array(
			'v.name',
			'v.picture',
			'v.country'
		);
		parent::__construct($config);

		// $getDBConnection = sportsmanagementHelper::getDBConnection();
		parent::setDbo($this->jsmdb);
	}

	/**
	 * Method to get the starting number of items for the data set.
	 *
	 * @return integer  The starting number of items available in the data set.
	 *
	 * @since 11.1
	 */
	public function getStart()
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;

		// $limitstart = $this->getUserStateFromRequest($this->context.'.limitstart', 'limitstart');
		$this->setState('list.start', $this->limitstart);

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

		// Add the total to the internal cache.
		$this->cache[$store] = $start;

		return $this->cache[$store];
	}

	/**
	 * sportsmanagementModelallleagues::getListQuery()
	 *
	 * @return
	 */
	function getListQuery()
	{

		$this->jsmquery->clear();
		$this->jsmquery->select('v.id,v.name,v.picture,v.country');

		// From table
		$this->jsmquery->from('#__sportsmanagement_league AS v');

		if ($this->getState('filter.search'))
		{
			$this->jsmquery->where('LOWER(v.name) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%'));
		}

		if ($this->getState('filter.search_nation'))
		{
			$this->jsmquery->where('v.country LIKE ' . $this->jsmdb->Quote('' . $this->getState('filter.search_nation') . ''));
		}

		if ($this->use_current_season)
		{
			$filter_season = ComponentHelper::getParams($this->jsmoption)->get('current_season', 0);
			$this->jsmquery->join('INNER', '#__sportsmanagement_project AS p ON v.id = p.league_id');
			/**
			 * sicherheitshalber noch eine abfrage, wenn der user die nutzung der saison eingeschaltet,
			 * aber keine saisons hintlergt hat
			 */
			if ($filter_season)
			{
				$this->jsmquery->where('p.season_id IN (' . implode(',', $filter_season) . ')');
			}
		}

		$this->jsmquery->group('v.id');

		$this->jsmquery->order($this->jsmdb->escape($this->getState('filter_order', 'v.name')) . ' ' . $this->jsmdb->escape($this->getState('filter_order_Dir', 'ASC')));

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
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Initialise variables.
		$app = Factory::getApplication('site');

		// List state information

		$value = $this->getUserStateFromRequest($this->context . '.limit', 'limit', $app->getCfg('list_limit', 0));
		$this->setState('list.limit', $value);

		$value = $jinput->getUInt('limitstart', 0);
		$this->setState('list.start', $value);

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
		$temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);

		$filter_order = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($filter_order, $this->filter_fields))
		{
			$filter_order = 'v.name';
		}

		$filter_order_Dir = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');

		if (!in_array(strtoupper($filter_order_Dir), array('ASC', 'DESC', '')))
		{
			$filter_order_Dir = 'ASC';
		}

		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);

	}


}

