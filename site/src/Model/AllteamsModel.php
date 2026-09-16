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
use Joomla\Database\ParameterType;

final class AllteamsModel extends SportsManagementListModel
{
    protected $_identifier = 'allteams';
    public int $limitstart = 0;
    public int $limit = 0;
    public bool $use_current_season = false;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $input = $this->siteApplication()->getInput();
        $this->use_current_season = (bool) $input->getInt('use_current_season', 0);
        $this->limitstart = $input->getInt('limitstart', 0);
        $config['filter_fields'] = [
            'v.name', 'v.picture', 'v.website', 'c.address', 'c.zipcode', 'c.location', 'c.country',
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
        $query = $db->createQuery()
            ->select([
                $db->quoteName('v.id'),
                $db->quoteName('v.name'),
                $db->quoteName('v.picture'),
                $db->quoteName('v.website'),
                "CONCAT_WS(':', " . $db->quoteName('v.id') . ', ' . $db->quoteName('v.alias') . ') AS ' . $db->quoteName('slug'),
                "CONCAT_WS(':', " . $db->quoteName('p.id') . ', ' . $db->quoteName('p.alias') . ') AS ' . $db->quoteName('projectslug'),
                $db->quoteName('c.name', 'club'),
                $db->quoteName('c.address'),
                $db->quoteName('c.zipcode'),
                $db->quoteName('c.country'),
                $db->quoteName('c.location'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 'v'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_club', 'c')
                . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('v.club_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('v.id')
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
            );

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $searchValue = '%' . strtolower($search) . '%';
            $query->where('LOWER(' . $db->quoteName('v.name') . ') LIKE :teamSearch')
                ->bind(':teamSearch', $searchValue, ParameterType::STRING);
        }

        $nation = trim((string) $this->getState('filter.search_nation'));
        if ($nation !== '') {
            $query->where($db->quoteName('c.country') . ' = :nation')
                ->bind(':nation', $nation, ParameterType::STRING);
        }

        if ($this->use_current_season) {
            $currentSeason = ComponentHelper::getParams('com_sportsmanagement')->get('current_season', []);
            $seasonIds = is_array($currentSeason) ? $currentSeason : [$currentSeason];
            $seasonIds = array_values(array_filter(array_map('intval', $seasonIds), static fn($id) => $id > 0));
            if ($seasonIds) {
                $query->whereIn($db->quoteName('p.season_id'), $seasonIds, ParameterType::INTEGER);
            }
        }

        $orderMap = [
            'v.name' => $db->quoteName('v.name'),
            'v.picture' => $db->quoteName('v.picture'),
            'v.website' => $db->quoteName('v.website'),
            'c.address' => $db->quoteName('c.address'),
            'c.zipcode' => $db->quoteName('c.zipcode'),
            'c.location' => $db->quoteName('c.location'),
            'c.country' => $db->quoteName('c.country'),
        ];
        $ordering = (string) $this->getState('filter_order', 'v.name');
        $direction = strtoupper((string) $this->getState('filter_order_Dir', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        $query->group($db->quoteName('v.id'))
            ->order(($orderMap[$ordering] ?? $orderMap['v.name']) . ' ' . $direction);

        return $query;
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $app = $this->siteApplication();
        $defaultLimit = (int) $app->getConfig()->get('list_limit', 20);
        $this->setState('list.limit', $this->getUserStateFromRequest($this->context . '.limit', 'limit', $defaultLimit, 'int'));
        $this->setState('list.start', $app->getInput()->getUInt('limitstart', 0));
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
