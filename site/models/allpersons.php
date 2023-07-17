<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage alllpersons
 * @file       alllpersons.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelallpersons
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelallpersons extends ListModel
{
	var $_identifier = "allpersons";

	var $limitstart = 0;

	var $limit = 0;

	var $columns = array();

	/**
	 * sportsmanagementModelallpersons::__construct()
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

		$config['filter_fields'] = array(
			'v.lastname',
			'v.firstname',
			'v.picture',
			'v.website',
			'v.address',
			'v.zipcode',
			'v.city',
			'v.country',
			'v.birthday',
			'v.deathday',
			'v.position_id'
		);
		parent::__construct($config);
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
		$app = Factory::getApplication();
		$jinput = $app->input;
		$this->setState('list.start', $this->limitstart);
		$store = $this->getStoreId('getstart');

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

		$this->cache[$store] = $start;

		return $this->cache[$store];
	}

	/**
	 * sportsmanagementModelallplaygrounds::getListQuery()
	 *
	 * @return
	 */
	function getListQuery()
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$select_columns = $this->getState('filter.select_columns');

		if ($select_columns)
		{
			foreach ($select_columns as $key => $value)
			{
				$select_columns[$key] = 'v.' . $value;
			}
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$user  = Factory::getUser();

		if ($select_columns)
		{
			$query->select(implode(",", $select_columns));
			$query->select('v.id');
		}
		else
		{
			$query->select('v.*');
		}

		$query->select('CONCAT_WS( \':\', v.id, v.alias ) AS slug');
		$query->select('CONCAT_WS( \':\', p.id, p.alias ) AS projectslug');
		$query->select('CONCAT_WS( \':\', t.id, t.alias ) AS teamslug');
		$query->select('po.name as position_name');
		$query->from('#__sportsmanagement_person as v');
		$query->join('INNER', '#__sportsmanagement_season_team_person_id AS stp ON stp.person_id = v.id');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = stp.team_id');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt.project_id');
		$query->join('INNER', '#__sportsmanagement_team AS t ON t.id = stp.team_id');
		$query->join('LEFT', '#__sportsmanagement_position AS po ON po.id = v.position_id');

		if ($this->getState('filter.search'))
		{
			$query->where('LOWER(v.lastname) LIKE ' . $db->Quote('%' . $this->getState('filter.search') . '%'));
		}

		if ($this->getState('filter.search_nation'))
		{
			$query->where('v.country LIKE ' . $db->Quote('' . $this->getState('filter.search_nation') . ''));
		}

		if ($this->use_current_season)
		{
			$filter_season = ComponentHelper::getParams($option)->get('current_season', 0);
            if ( is_array($filter_season) )
            {
			$query->where('p.season_id IN (' . implode(',', $filter_season) . ')');
            }
		}

		$query->group('v.id');
		$query->order($db->escape($this->getState('filter_order', 'v.lastname')) . ' ' . $db->escape($this->getState('filter_order_Dir', 'ASC')));
		return $query;

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
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$app = Factory::getApplication('site');

    	$value = $this->getUserStateFromRequest($this->context . '.limit', 'limit', $app->getCfg('list_limit', 0));
		$this->setState('list.limit', $value);

		$columns = $jinput->getVar('show_columns');
		$this->setState('filter.select_columns', $columns);
		$this->columns = $columns;

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
		$temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);

		$filter_order = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($filter_order, $this->filter_fields))
		{
			$filter_order = 'v.lastname';
		}

		$filter_order_Dir = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');

		if (!in_array(strtoupper($filter_order_Dir), array('ASC', 'DESC', '')))
		{
			$filter_order_Dir = 'ASC';
		}

		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);

		parent::populateState('v.lastname', 'ASC');
	}


}

