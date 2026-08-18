<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage allplaygrounds
 * @file       allplaygrounds.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * sportsmanagementModelallplaygrounds
 */
class sportsmanagementModelallplaygrounds extends ListModel
{
	protected $_identifier = 'allplaygrounds';
	protected $limitstart = 0;
	protected $limit = 0;
	protected $use_current_season = false;

	/**
	 * Constructor.
	 *
	 * @param array $config Model configuration.
	 */
	public function __construct($config = array())
	{
		$input = Factory::getApplication()->getInput();
		$this->use_current_season = (bool) $input->getInt('use_current_season', 0);
		$this->limitstart = $input->getInt('limitstart', 0);

		$config['filter_fields'] = array(
			'v.name',
			'v.picture',
			'v.website',
			'v.address',
			'v.zipcode',
			'v.city',
			'v.country'
		);

		parent::__construct($config);
	}

	/**
	 * Method to get the starting number of items for the data set.
	 *
	 * @return integer
	 */
	public function getStart()
	{
		$this->setState('list.start', $this->limitstart);
		$store = $this->getStoreId('getstart');

		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$start = (int) $this->getState('list.start');
		$limit = (int) $this->getState('list.limit');
		$total = (int) $this->getTotal();

		if ($limit <= 0)
		{
			$this->cache[$store] = max(0, $start);
			return $this->cache[$store];
		}

		if ($start > $total - $limit)
		{
			$start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
		}

		$this->cache[$store] = $start;

		return $this->cache[$store];
	}

	/**
	 * Build the playground list query.
	 *
	 * @return mixed Database query object.
	 */
	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->createQuery();

		$query->select('v.id,v.name,v.picture,v.website,v.address,v.zipcode,v.city,v.country');
		$query->select("CONCAT_WS(':', v.id, v.alias) AS slug");
		$query->select("CONCAT_WS(':', p.id, p.alias) AS projectslug");
		$query->select('c.name AS club');
		$query->from('#__sportsmanagement_playground AS v');
		$query->join('LEFT', '#__sportsmanagement_club AS c ON c.id = v.club_id');
		$query->join('LEFT', '#__sportsmanagement_team AS t ON t.club_id = c.id');
		$query->join('LEFT', '#__sportsmanagement_season_team_id AS st ON st.team_id = t.id');
		$query->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query->join('LEFT', '#__sportsmanagement_project AS p ON p.id = pt.project_id');

		if ($this->use_current_season)
		{
			$currentSeason = ComponentHelper::getParams('com_sportsmanagement')->get('current_season', array());
			$seasonIds = is_array($currentSeason) ? $currentSeason : array($currentSeason);
			$seasonIds = array_values(array_filter(array_map('intval', $seasonIds), static fn($id) => $id > 0));

			if ($seasonIds)
			{
				$query->where('p.season_id IN (' . implode(',', $seasonIds) . ')');
			}
		}

		$search = trim((string) $this->getState('filter.search'));

		if ($search !== '')
		{
			$query->where('LOWER(v.name) LIKE ' . $db->quote('%' . strtolower($search) . '%'));
		}

		$nation = trim((string) $this->getState('filter.search_nation'));

		if ($nation !== '')
		{
			$query->where('v.country = ' . $db->quote($nation));
		}

		$query->group('v.id');
		$query->order(
			$db->escape($this->getState('filter_order', 'v.name')) . ' '
			. $db->escape($this->getState('filter_order_Dir', 'ASC'))
		);

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param string|null $ordering  Ordering field.
	 * @param string|null $direction Ordering direction.
	 *
	 * @return void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$input = Factory::getApplication()->getInput();
		$defaultLimit = (int) Factory::getConfig()->get('list_limit', 20);
		$value = $this->getUserStateFromRequest($this->context . '.limit', 'limit', $defaultLimit, 'int');
		$this->setState('list.limit', $value);
		$this->setState('list.start', $input->getUInt('limitstart', 0));

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest(
			$this->context . '.filter.state',
			'filter_published',
			'',
			'string'
		);
		$this->setState('filter.state', $published);

		$nation = $this->getUserStateFromRequest(
			$this->context . '.filter.search_nation',
			'filter_search_nation',
			''
		);
		$this->setState('filter.search_nation', $nation);

		$filterOrder = $this->getUserStateFromRequest(
			$this->context . '.filter_order',
			'filter_order',
			'',
			'string'
		);

		if (!in_array($filterOrder, $this->filter_fields, true))
		{
			$filterOrder = 'v.name';
		}

		$filterOrderDir = $this->getUserStateFromRequest(
			$this->context . '.filter_order_Dir',
			'filter_order_Dir',
			'',
			'cmd'
		);

		if (!in_array(strtoupper($filterOrderDir), array('ASC', 'DESC', ''), true))
		{
			$filterOrderDir = 'ASC';
		}

		$this->setState('filter_order', $filterOrder);
		$this->setState('filter_order_Dir', $filterOrderDir);
	}
}
