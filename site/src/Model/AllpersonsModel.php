<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class AllpersonsModel extends SportsManagementListModel
{
    private const SELECTABLE_COLUMNS = [
        'firstname',
        'lastname',
        'nickname',
        'picture',
        'website',
        'address',
        'zipcode',
        'country',
        'birthday',
        'deathday',
        'position_id',
    ];

    protected $_identifier = 'allpersons';
    public int $limitstart = 0;
    public int $limit = 0;
    public bool $use_current_season = false;
    public array $columns = [];

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $input = $this->siteApplication()->getInput();
        $this->use_current_season = (bool) $input->getInt('use_current_season', 0);
        $this->limitstart = $input->getInt('limitstart', 0);

        $config['filter_fields'] = [
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
            'v.position_id',
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
        $selectColumns = $this->normaliseSelectColumns($this->getState('filter.select_columns', []));

        if ($selectColumns) {
            foreach ($selectColumns as $column) {
                $query->select($db->quoteName('v.' . $column));
            }
            $query->select($db->quoteName('v.id'));
        } else {
            $query->select('v.*');
        }

        $query->select("CONCAT_WS(':', v.id, v.alias) AS slug")
            ->select("CONCAT_WS(':', p.id, p.alias) AS projectslug")
            ->select("CONCAT_WS(':', t.id, t.alias) AS teamslug")
            ->select('po.name AS position_name')
            ->from('#__sportsmanagement_person AS v')
            ->join('INNER', '#__sportsmanagement_season_team_person_id AS stp ON stp.person_id = v.id')
            ->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = stp.team_id')
            ->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id')
            ->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt.project_id')
            ->join('INNER', '#__sportsmanagement_team AS t ON t.id = stp.team_id')
            ->join('LEFT', '#__sportsmanagement_position AS po ON po.id = v.position_id');

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $query->where('LOWER(v.lastname) LIKE ' . $db->quote('%' . strtolower($search) . '%'));
        }

        $nation = trim((string) $this->getState('filter.search_nation'));
        if ($nation !== '') {
            $query->where('v.country = ' . $db->quote($nation));
        }

        if ($this->use_current_season) {
            $currentSeason = ComponentHelper::getParams('com_sportsmanagement')->get('current_season', []);
            $seasonIds = is_array($currentSeason) ? $currentSeason : [$currentSeason];
            $seasonIds = array_values(array_filter(array_map('intval', $seasonIds), static fn($id) => $id > 0));

            if ($seasonIds) {
                $query->where('p.season_id IN (' . implode(',', $seasonIds) . ')');
            }
        }

        $query->group('v.id')
            ->order(
                $db->escape((string) $this->getState('filter_order', 'v.lastname')) . ' '
                . $db->escape((string) $this->getState('filter_order_Dir', 'ASC'))
            );

        return $query;
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $app = $this->siteApplication();
        $input = $app->getInput();
        $defaultLimit = (int) $app->getConfig()->get('list_limit', 20);

        $this->setState('list.limit', $this->getUserStateFromRequest($this->context . '.limit', 'limit', $defaultLimit, 'int'));
        $this->setState('list.start', $input->getUInt('limitstart', 0));

        $columns = $this->normaliseSelectColumns($input->get('show_columns', [], 'array'));
        $this->setState('filter.select_columns', $columns);
        $this->columns = $columns;

        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search'));
        $this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string'));
        $this->setState('filter.search_nation', $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', ''));

        $filterOrder = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');
        if (!in_array($filterOrder, $this->filter_fields, true)) {
            $filterOrder = 'v.lastname';
        }

        $filterOrderDir = strtoupper((string) $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd'));
        if (!in_array($filterOrderDir, ['ASC', 'DESC'], true)) {
            $filterOrderDir = 'ASC';
        }

        $this->setState('filter_order', $filterOrder);
        $this->setState('filter_order_Dir', $filterOrderDir);
    }

    private function normaliseSelectColumns($columns): array
    {
        $requested = array_map('strval', (array) $columns);
        return array_values(array_unique(array_intersect($requested, self::SELECTABLE_COLUMNS)));
    }
}
