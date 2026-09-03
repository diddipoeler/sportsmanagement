<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class AllprojectsModel extends SportsManagementListModel
{
    public string $_identifier = 'allprojects';
    public int $limitstart = 0;
    public int $limit = 0;
    public bool $use_current_season = false;
    public int $season = 0;

    private const ORDER_FIELDS = [
        'v.name' => 'v.name',
        'v.picture' => 'v.picture',
        'v.website' => 'v.website',
        'v.location' => 'v.location',
        'l.name' => 'l.name',
        's.name' => 's.name',
        'l.country' => 'l.country',
        // Historical template value; project has no country column here.
        'v.country' => 'l.country',
    ];

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $input = $this->siteApplication()->getInput();
        $this->use_current_season = (bool) $input->getInt('use_current_season', 0);
        $this->season = max(0, $input->getInt('s', 0));
        $this->limitstart = max(0, $input->getInt('limitstart', 0));
        $config['filter_fields'] = array_keys(self::ORDER_FIELDS);

        parent::__construct($config, $factory);
    }

    public function getStart()
    {
        $this->setState('list.start', $this->limitstart);
        $store = $this->getStoreId('getstart');

        if (isset($this->cache[$store])) {
            return $this->cache[$store];
        }

        $start = max(0, (int) $this->getState('list.start'));
        $limit = max(0, (int) $this->getState('list.limit'));
        $total = max(0, (int) $this->getTotal());

        if ($limit <= 0) {
            return $this->cache[$store] = $start;
        }

        if ($start > $total - $limit) {
            $start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
        }

        return $this->cache[$store] = $start;
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('v.id'),
                $db->quoteName('v.name'),
                $db->quoteName('v.picture'),
                $db->quoteName('l.country'),
                $db->quoteName('l.name', 'leaguename'),
                $db->quoteName('s.name', 'seasonname'),
                "CONCAT_WS(':', v.id, v.alias) AS slug",
                "CONCAT_WS(':', l.id, l.alias) AS leagueslug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'v'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('v.league_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('v.season_id'))
            ->where($db->quoteName('v.published') . ' = 1');

        $search = trim((string) $this->getState('filter.search', ''));
        if ($search !== '') {
            $query->where('LOWER(' . $db->quoteName('v.name') . ') LIKE ' . $db->quote('%' . strtolower($search) . '%'));
        }

        $nation = trim((string) $this->getState('filter.search_nation', ''));
        if ($nation !== '' && $nation !== '0') {
            $query->where($db->quoteName('l.country') . ' = ' . $db->quote($nation));
        }

        $leagueId = max(0, (int) $this->getState('filter.search_leagues', 0));
        if ($leagueId > 0) {
            $query->where($db->quoteName('v.league_id') . ' = ' . $leagueId);
        }

        $seasonId = max(0, (int) $this->getState('filter.search_seasons', 0));
        if ($seasonId > 0) {
            $query->where($db->quoteName('v.season_id') . ' = ' . $seasonId);
        }

        if ($this->use_current_season) {
            $currentSeason = ComponentHelper::getParams('com_sportsmanagement')->get('current_season', []);
            $seasonIds = self::normaliseIds($currentSeason);
            if ($seasonIds) {
                $query->where($db->quoteName('v.season_id') . ' IN (' . implode(',', $seasonIds) . ')');
            }
        }

        if ($this->season > 0) {
            $query->where($db->quoteName('v.season_id') . ' = ' . $this->season);
        }

        $requestedOrder = (string) $this->getState('filter_order', 'v.name');
        $orderColumn = self::ORDER_FIELDS[$requestedOrder] ?? 'v.name';
        $direction = strtoupper((string) $this->getState('filter_order_Dir', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order($db->quoteName($orderColumn) . ' ' . $direction);

        return $query;
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $app = $this->siteApplication();
        $defaultLimit = max(1, (int) $app->getConfig()->get('list_limit', 20));

        $this->setState(
            'list.limit',
            max(0, (int) $this->getUserStateFromRequest($this->context . '.limit', 'limit', $defaultLimit, 'int'))
        );
        $this->setState('list.start', $this->limitstart);
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
        $this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string'));
        $this->setState('filter.search_nation', $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', '', 'string'));
        $this->setState('filter.search_leagues', max(0, (int) $this->getUserStateFromRequest($this->context . '.filter.search_leagues', 'filter_search_leagues', 0, 'int')));
        $this->setState('filter.search_seasons', max(0, (int) $this->getUserStateFromRequest($this->context . '.filter.search_seasons', 'filter_search_seasons', 0, 'int')));

        $filterOrder = (string) $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'v.name', 'string');
        if (!isset(self::ORDER_FIELDS[$filterOrder])) {
            $filterOrder = 'v.name';
        }

        $filterOrderDir = strtoupper((string) $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'ASC', 'cmd'));
        if (!in_array($filterOrderDir, ['ASC', 'DESC'], true)) {
            $filterOrderDir = 'ASC';
        }

        $this->setState('filter_order', $filterOrder);
        $this->setState('filter_order_Dir', $filterOrderDir);
    }

    public function getLeagueOptions(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('name')])
            ->from($db->quoteName('#__sportsmanagement_league'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getSeasonOptions(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('name')])
            ->from($db->quoteName('#__sportsmanagement_season'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('name') . ' DESC');
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    private static function normaliseIds($value): array
    {
        $parts = is_array($value)
            ? $value
            : preg_split('/[|,]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
        $ids = [];
        foreach ($parts ?: [] as $part) {
            $id = (int) $part;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        return array_values($ids);
    }
}
