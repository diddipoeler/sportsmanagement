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
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

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

    /**
     * Public database access for presentation helpers without exposing Joomla's
     * protected BaseDatabaseModel::getDatabase() API to the view layer.
     */
    public function getSportsManagementDatabase(): DatabaseInterface
    {
        return $this->getDatabase();
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
            $query->select($db->quoteName('v') . '.*');
        }

        $query->select([
                "CONCAT_WS(':', " . $db->quoteName('v.id') . ', ' . $db->quoteName('v.alias') . ') AS ' . $db->quoteName('slug'),
                "CONCAT_WS(':', " . $db->quoteName('p.id') . ', ' . $db->quoteName('p.alias') . ') AS ' . $db->quoteName('projectslug'),
                "CONCAT_WS(':', " . $db->quoteName('t.id') . ', ' . $db->quoteName('t.alias') . ') AS ' . $db->quoteName('teamslug'),
                $db->quoteName('po.name', 'position_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_person', 'v'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_person_id', 'stp')
                . ' ON ' . $db->quoteName('stp.person_id') . ' = ' . $db->quoteName('v.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('stp.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('stp.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_position', 'po')
                . ' ON ' . $db->quoteName('po.id') . ' = ' . $db->quoteName('v.position_id')
            );

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $searchToken = '%' . strtolower($search) . '%';
            $query->where('LOWER(' . $db->quoteName('v.lastname') . ') LIKE :personSearch')
                ->bind(':personSearch', $searchToken, ParameterType::STRING);
        }

        $nation = trim((string) $this->getState('filter.search_nation'));
        if ($nation !== '') {
            $query->where($db->quoteName('v.country') . ' = :personCountry')
                ->bind(':personCountry', $nation, ParameterType::STRING);
        }

        if ($this->use_current_season) {
            $currentSeason = ComponentHelper::getParams('com_sportsmanagement')->get('current_season', []);
            $seasonIds = is_array($currentSeason) ? $currentSeason : [$currentSeason];
            $seasonIds = array_values(array_filter(array_map('intval', $seasonIds), static fn ($id) => $id > 0));

            if ($seasonIds) {
                $query->whereIn($db->quoteName('p.season_id'), $seasonIds, ParameterType::INTEGER);
            }
        }

        $orderMap = [
            'v.lastname' => $db->quoteName('v.lastname'),
            'v.firstname' => $db->quoteName('v.firstname'),
            'v.picture' => $db->quoteName('v.picture'),
            'v.website' => $db->quoteName('v.website'),
            'v.address' => $db->quoteName('v.address'),
            'v.zipcode' => $db->quoteName('v.zipcode'),
            'v.city' => $db->quoteName('v.city'),
            'v.country' => $db->quoteName('v.country'),
            'v.birthday' => $db->quoteName('v.birthday'),
            'v.deathday' => $db->quoteName('v.deathday'),
            'v.position_id' => $db->quoteName('v.position_id'),
        ];
        $ordering = (string) $this->getState('filter_order', 'v.lastname');
        $direction = strtoupper((string) $this->getState('filter_order_Dir', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        $query->group($db->quoteName('v.id'))
            ->order(($orderMap[$ordering] ?? $orderMap['v.lastname']) . ' ' . $direction);

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
