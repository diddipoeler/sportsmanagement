<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       statistics.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * sportsmanagementModelStatistics
 */
class sportsmanagementModelStatistics extends ListModel
{
	protected $_identifier = 'statistics';

	/**
	 * Constructor.
	 *
	 * @param array $config Model configuration.
	 */
	public function __construct($config = array())
	{
		$config['filter_fields'] = array(
			'obj.name',
			'obj.short',
			'obj.icon',
			'obj.sports_type_id',
			'obj.published',
			'obj.id',
			'obj.ordering'
		);

		parent::__construct($config);
		$this->setDatabase(sportsmanagementHelper::getDBConnection());
	}

	/**
	 * Build the statistics list query.
	 *
	 * @return mixed Database query object.
	 */
	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->createQuery();

		$query->select('obj.*');
		$query->from('#__sportsmanagement_statistic AS obj');
		$query->select('st.name AS sportstype');
		$query->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = obj.sports_type_id');
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');

		$search = trim((string) $this->getState('filter.search'));

		if ($search !== '')
		{
			$query->where('LOWER(obj.name) LIKE ' . $db->quote('%' . strtolower($search) . '%'));
		}

		$sportsType = (int) $this->getState('filter.sports_type');

		if ($sportsType > 0)
		{
			$query->where('obj.sports_type_id = ' . $sportsType);
		}

		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('obj.published = ' . (int) $state);
		}

		$query->order(
			$db->escape($this->getState('list.ordering', 'obj.name')) . ' '
			. $db->escape($this->getState('list.direction', 'ASC'))
		);

		return $query;
	}

	/**
	 * Return statistics assigned to a position.
	 *
	 * @param int $id Position ID.
	 *
	 * @return array
	 */
	public function getPositionStatsOptions($id)
	{
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->createQuery();
		$query->select('s.id AS value, concat(s.name, " (" , st.name, ")") AS text');
		$query->from('#__sportsmanagement_statistic AS s');
		$query->join('INNER', '#__sportsmanagement_position_statistic AS ps ON ps.statistic_id = s.id');
		$query->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = s.sports_type_id');
		$query->where('ps.position_id = ' . (int) $id);
		$query->order('ps.ordering ASC');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Return statistics not assigned to a position.
	 *
	 * @param int $id Position ID.
	 *
	 * @return array
	 */
	public function getAvailablePositionStatsOptions($id)
	{
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->createQuery();
		$query->select('s.id AS value, concat(s.name, " (" , st.name, ")") AS text');
		$query->from('#__sportsmanagement_statistic AS s');
		$query->join(
			'LEFT',
			'#__sportsmanagement_position_statistic AS ps ON ps.statistic_id = s.id AND ps.position_id = ' . (int) $id
		);
		$query->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = s.sports_type_id');
		$query->where('ps.id IS NULL');
		$query->order('s.ordering ASC');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Return all statistics for selection lists.
	 *
	 * @return array
	 */
	public function getStatisticListSelect()
	{
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->createQuery();
		$query->select('id,name,id AS value,name AS text,short,class,note');
		$query->from('#__sportsmanagement_statistic');
		$query->order('name');
		$db->setQuery($query);

		return $db->loadObjectList();
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
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest(
			$this->context . '.filter.state',
			'filter_published',
			'',
			'string'
		);
		$this->setState('filter.state', $published);

		$sportsType = $this->getUserStateFromRequest(
			$this->context . '.filter.sports_type',
			'filter_sports_type',
			''
		);
		$this->setState('filter.sports_type', $sportsType);

		$limitStart = Factory::getApplication()->getInput()->getUInt('limitstart', 0);
		$this->setState('list.start', $limitStart);

		parent::populateState('obj.name', 'asc');
	}
}
