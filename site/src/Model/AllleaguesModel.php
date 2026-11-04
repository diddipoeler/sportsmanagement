<?php
/**
 * Joomla 5/6 list model for the public all-leagues view.
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
use Joomla\Database\ParameterType;

final class AllleaguesModel extends SportsManagementListModel
{
    private const ORDER_FIELDS = [
        'v.name' => 'v.name',
        'v.picture' => 'v.picture',
        'v.country' => 'v.country',
    ];

    protected $_identifier = 'allleagues';
    public int $limitstart = 0;
    public int $limit = 0;
    public bool $use_current_season = false;

    /**
     * Initialise the public league-list model from Joomla request settings.
     * Reads whether the current-season filter is enabled and the unsigned
     * pagination offset; registers only supported league sorting columns.
     * @param array $config Native Joomla list-model configuration.
     * @param MVCFactoryInterface|null $factory Optional Joomla MVC factory.
     * @return void
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $input = $this->siteApplication()->getInput();
        $this->use_current_season = (bool) $input->getInt('use_current_season', 0);
        $this->limitstart = $input->getUInt('limitstart', 0);

        $config['filter_fields'] = array_keys(self::ORDER_FIELDS);

        parent::__construct($config, $factory);
    }

    /**
     * Calculate the effective first row for league list pagination.
     * Caches the result and adjusts an offset beyond the last page
     * so the user sees the final available page after filtering.
     * @return int Nonnegative list offset for the selected page.
     */
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

    /**
     * Build the public league list query with optional season filtering.
     * Filters by searched league name and country, joins projects only
     * when current-season selection is enabled and applies a strict
     * whitelist of league sorting fields and directions.
     * @return \Joomla\Database\DatabaseQuery Filtered league-list query.
     */
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName([
                'v.id',
                'v.name',
                'v.picture',
                'v.country',
            ]))
            ->from($db->quoteName('#__sportsmanagement_league', 'v'));

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $searchValue = '%' . strtolower($search) . '%';
            $query->where('LOWER(' . $db->quoteName('v.name') . ') LIKE :leagueSearch')
                ->bind(':leagueSearch', $searchValue, ParameterType::STRING);
        }

        $nation = trim((string) $this->getState('filter.search_nation'));

        if ($nation !== '') {
            $query->where($db->quoteName('v.country') . ' = :nation')
                ->bind(':nation', $nation, ParameterType::STRING);
        }

        if ($this->use_current_season) {
            $seasonIds = $this->getCurrentSeasonIds();

            if ($seasonIds) {
                $query->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_project', 'p')
                    . ' ON ' . $db->quoteName('v.id') . ' = ' . $db->quoteName('p.league_id')
                )->whereIn($db->quoteName('p.season_id'), $seasonIds, ParameterType::INTEGER);
            }
        }

        $requestedOrder = (string) $this->getState('filter_order', 'v.name');
        $orderColumn = self::ORDER_FIELDS[$requestedOrder] ?? self::ORDER_FIELDS['v.name'];
        $direction = strtoupper((string) $this->getState('filter_order_Dir', 'ASC')) === 'DESC'
            ? 'DESC'
            : 'ASC';

        $query->group($db->quoteName('v.id'))
            ->order($db->quoteName($orderColumn) . ' ' . $direction);

        return $query;
    }

    /**
     * Restore league search, country, page and ordering state.
     * Reads Joomla user-state filters, uses the configured page size
     * and validates the requested sort column and direction.
     * @param string|null $ordering Optional Joomla default ordering.
     * @param string|null $direction Optional default order direction.
     * @return void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = $this->siteApplication();
        $defaultLimit = (int) $app->getConfig()->get('list_limit', 20);
        $this->setState('list.limit', $this->getUserStateFromRequest($this->context . '.limit', 'limit', $defaultLimit, 'int'));
        $this->setState('list.start', $app->getInput()->getUInt('limitstart', 0));
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search'));
        $this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string'));
        $this->setState('filter.search_nation', $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', ''));

        $filterOrder = (string) $this->getUserStateFromRequest(
            $this->context . '.filter_order',
            'filter_order',
            'v.name',
            'string'
        );

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

    /**
     * Validate configured current-season identifiers for league filters.
     * Accepts positive decimal IDs, Joomla ID:alias slugs and delimited
     * legacy selections; rejects malformed, overflowing and oversized
     * values, deduplicates IDs and caps the number bound to SQL.
     * @return array<int> Up to 100 unique valid positive season IDs.
     */
    private function getCurrentSeasonIds(): array
    {
        $currentSeason = ComponentHelper::getParams('com_sportsmanagement')->get('current_season', []);
        $values = is_array($currentSeason) ? $currentSeason : [$currentSeason];
        $ids = [];

        // A legacy current_season value may be a numeric ID, an ID:alias
        // slug or a comma/semicolon-delimited selection of seasons.
        foreach (array_slice($values, 0, 100) as $value) {
            if (!is_int($value) && !is_string($value)) {
                continue;
            }

            if (is_string($value) && strlen($value) > 4096) {
                continue;
            }

            $parts = is_int($value)
                ? [$value]
                : (preg_split('/[\\s,;|]+/', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: []);

            foreach ($parts as $part) {
                if (!preg_match('/^([0-9]+)(?::[^:\\s,;|]+)?$/D', (string) $part, $matches)) {
                    continue;
                }

                $digits = ltrim($matches[1], '0');
                if ($digits === '') {
                    continue;
                }

                $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($id !== false) {
                    $ids[$id] = $id;
                }

                if (count($ids) >= 100) {
                    return array_values($ids);
                }
            }
        }

        return array_values($ids);
    }
}
