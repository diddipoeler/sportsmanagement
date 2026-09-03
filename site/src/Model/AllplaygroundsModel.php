<?php
/**
 * Joomla 5/6 list model for the public all-playgrounds view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class AllplaygroundsModel extends SportsManagementListModel
{
    protected $_identifier = 'allplaygrounds';
    protected $limitstart = 0;
    protected $limit = 0;
    protected $use_current_season = false;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $input = $this->siteApplication()->getInput();
        $this->use_current_season = (bool) $input->getInt('use_current_season', 0);
        $this->limitstart = $input->getInt('limitstart', 0);
        $config['filter_fields'] = [
            'v.name', 'v.picture', 'v.website', 'v.address', 'v.zipcode', 'v.city', 'v.country',
        ];

        parent::__construct($config, $factory);
    }

    public function getStart()
    {
        $this->setState('list.start', $this->limitstart);
        $store = $this->getStoreId('getstart');

        if (isset($this->cache[$store])) {
            return $this->cache[$store];
        }

        $start = (int) $this->getState('list.start');
        $limit = (int) $this->getState('list.limit');
        $total = (int) $this->getTotal();

        if ($limit <= 0) {
            return $this->cache[$store] = max(0, $start);
        }

        if ($start > $total - $limit) {
            $start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
        }

        return $this->cache[$store] = $start;
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->createQuery();

        $query->select('v.id,v.name,v.picture,v.website,v.address,v.zipcode,v.city,v.country')
            ->select("CONCAT_WS(':', v.id, v.alias) AS slug")
            ->select("CONCAT_WS(':', p.id, p.alias) AS projectslug")
            ->select('c.name AS club')
            ->from('#__sportsmanagement_playground AS v')
            ->join('LEFT', '#__sportsmanagement_club AS c ON c.id = v.club_id')
            ->join('LEFT', '#__sportsmanagement_team AS t ON t.club_id = c.id')
            ->join('LEFT', '#__sportsmanagement_season_team_id AS st ON st.team_id = t.id')
            ->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id')
            ->join('LEFT', '#__sportsmanagement_project AS p ON p.id = pt.project_id');

        if ($this->use_current_season) {
            $currentSeason = ComponentHelper::getParams('com_sportsmanagement')->get('current_season', []);
            $seasonIds = is_array($currentSeason) ? $currentSeason : [$currentSeason];
            $seasonIds = array_values(array_filter(array_map('intval', $seasonIds), static fn($id) => $id > 0));
            if ($seasonIds) {
                $query->where('p.season_id IN (' . implode(',', $seasonIds) . ')');
            }
        }

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $query->where('LOWER(v.name) LIKE ' . $db->quote('%' . strtolower($search) . '%'));
        }

        $nation = trim((string) $this->getState('filter.search_nation'));
        if ($nation !== '') {
            $query->where('v.country = ' . $db->quote($nation));
        }

        $query->group('v.id')
            ->order($db->escape((string) $this->getState('filter_order', 'v.name')) . ' '
                . $db->escape((string) $this->getState('filter_order_Dir', 'ASC')));

        return $query;
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $app = $this->siteApplication();
        $input = $app->getInput();
        $defaultLimit = (int) $app->getConfig()->get('list_limit', 20);
        $this->setState('list.limit', $this->getUserStateFromRequest($this->context . '.limit', 'limit', $defaultLimit, 'int'));
        $this->setState('list.start', $input->getUInt('limitstart', 0));
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search'));
        $this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string'));
        $this->setState('filter.search_nation', $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', ''));

        $filterOrder = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');
        if (!in_array($filterOrder, $this->filter_fields, true)) {
            $filterOrder = 'v.name';
        }

        $filterOrderDir = strtoupper((string) $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd'));
        if (!in_array($filterOrderDir, ['ASC', 'DESC'], true)) {
            $filterOrderDir = 'ASC';
        }

        $this->setState('filter_order', $filterOrder);
        $this->setState('filter_order_Dir', $filterOrderDir);
    }
}
