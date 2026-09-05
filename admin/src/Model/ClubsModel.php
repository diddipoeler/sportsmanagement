<?php
/**
 * Native Joomla 5/6 list model for clubs.
 *
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 * @version     5.6.0
 * @author      diddipoeler
 * @copyright   Copyright (C) diddipoeler
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Native Joomla 5/6 list model for clubs.
 */
class ClubsModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'a.name', 'name',
            'a.website', 'website',
            'a.country', 'country',
            'a.state', 'state',
            'a.location', 'location',
            'a.id', 'id',
            'a.published', 'published',
            'a.ordering', 'ordering',
            'a.modified', 'modified',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'a.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = $this->administratorApplication();

        $this->setState(
            'filter.search',
            $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string')
        );
        $this->setState(
            'filter.state',
            $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string')
        );
        $this->setState(
            'filter.search_nation',
            $app->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', '', 'cmd')
        );
        $this->setState(
            'filter.association',
            $app->getUserStateFromRequest($this->context . '.filter.association', 'filter_association', '', 'int')
        );
        $this->setState(
            'filter.season',
            $app->getUserStateFromRequest($this->context . '.filter.season', 'filter_season', '', 'int')
        );
        $this->setState(
            'filter.geo_daten',
            $app->getUserStateFromRequest($this->context . '.filter.geo_daten', 'filter_geo_daten', '', 'string')
        );
        $this->setState(
            'filter.standard_picture',
            $app->getUserStateFromRequest($this->context . '.filter.standard_picture', 'filter_standard_picture', '', 'int')
        );
        $this->setState(
            'filter.search_association',
            $app->getUserStateFromRequest($this->context . '.filter.search_association', 'filter_search_association', '', 'int')
        );

        $order = $app->getUserStateFromRequest(
            $this->context . '.filter_order',
            'filter_order',
            'a.name',
            'cmd'
        );
        $direction = strtoupper((string) $app->getUserStateFromRequest(
            $this->context . '.filter_order_Dir',
            'filter_order_Dir',
            'ASC',
            'cmd'
        ));

        $this->setState('list.ordering', in_array($order, $this->filter_fields, true) ? $order : 'a.name');
        $this->setState('list.direction', $direction === 'DESC' ? 'DESC' : 'ASC');

        // populateState() is still running here. Reading through getState() would
        // re-enter Joomla's lazy state initialisation on Joomla 5/6.
        $app->setUserState(
            'com_sportsmanagement.clubnation',
            (string) $this->state->get('filter.search_nation', '')
        );
        $app->setUserState(
            'com_sportsmanagement.search_association',
            (int) $this->state->get('filter.search_association', 0)
        );
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->createQuery();

        $query
            ->select([
                $db->quoteName('a.name'),
                $db->quoteName('a.website'),
                $db->quoteName('a.twitter'),
                $db->quoteName('a.facebook'),
                $db->quoteName('a.email'),
                $db->quoteName('a.logo_big'),
                $db->quoteName('a.logo_middle'),
                $db->quoteName('a.logo_small'),
                $db->quoteName('a.country'),
                $db->quoteName('a.state'),
                $db->quoteName('a.alias'),
                $db->quoteName('a.zipcode'),
                $db->quoteName('a.location'),
                $db->quoteName('a.address'),
                $db->quoteName('a.latitude'),
                $db->quoteName('a.longitude'),
                $db->quoteName('a.id'),
                $db->quoteName('a.published'),
                $db->quoteName('a.unique_id'),
                $db->quoteName('a.founded_year'),
                $db->quoteName('a.new_club_id'),
                $db->quoteName('a.ordering'),
                $db->quoteName('a.checked_out'),
                $db->quoteName('a.checked_out_time'),
                $db->quoteName('a.modified'),
                $db->quoteName('a.modified_by'),
                $db->quoteName('uc.name', 'editor'),
                $db->quoteName('u1.username', 'modified_by_username'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club', 'a'))
            ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out'))
            ->join('LEFT', $db->quoteName('#__users', 'u1') . ' ON ' . $db->quoteName('u1.id') . ' = ' . $db->quoteName('a.modified_by'));

        $geoData = $this->getState('filter.geo_daten');

        if ($geoData === '0' || $geoData === 0) {
            $query->where('(' . $db->quoteName('a.latitude') . ' IS NULL OR ' . $db->quoteName('a.latitude') . ' = 0)');
        } elseif ($geoData === '1' || $geoData === 1) {
            $query->where($db->quoteName('a.latitude') . ' <> 0');
        }

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $conditions = [
                'LOWER(' . $db->quoteName('a.name') . ') LIKE LOWER(' . $token . ')',
                'LOWER(' . $db->quoteName('a.unique_id') . ') LIKE LOWER(' . $token . ')',
                'LOWER(' . $db->quoteName('a.state') . ') LIKE LOWER(' . $token . ')',
            ];

            if (ctype_digit($search)) {
                $conditions[] = $db->quoteName('a.id') . ' = ' . (int) $search;
            }

            $query->where('(' . implode(' OR ', $conditions) . ')');
        }

        $country = (string) $this->getState('filter.search_nation');

        if ($country !== '' && $country !== '0') {
            $query->where($db->quoteName('a.country') . ' = ' . $db->quote($country));
        }

        $season = (int) $this->getState('filter.season');

        if ($season > 0) {
            $seasonQuery = $db->createQuery()
                ->select('1')
                ->from($db->quoteName('#__sportsmanagement_team', 'season_team'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_team_id', 'season_link')
                    . ' ON ' . $db->quoteName('season_link.team_id') . ' = ' . $db->quoteName('season_team.id')
                )
                ->where($db->quoteName('season_team.club_id') . ' = ' . $db->quoteName('a.id'))
                ->where($db->quoteName('season_link.season_id') . ' = ' . $season);

            $query->where('EXISTS (' . $seasonQuery . ')');
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('a.published') . ' = ' . (int) $state);
        }

        $association = (int) $this->getState('filter.search_association');

        if ($association <= 0) {
            $association = (int) $this->getState('filter.association');
        }

        if ($association > 0) {
            $query->where($db->quoteName('a.associations') . ' = ' . $association);
        }

        if ((int) $this->getState('filter.standard_picture') === 1) {
            $placeholder = $db->quote('%placeholder%');
            $query->where('(' . $db->quoteName('a.logo_big') . ' LIKE ' . $placeholder . ' OR ' . $db->quoteName('a.logo_big') . ' = ' . $db->quote('') . ')');
        }

        $ordering = (string) $this->getState('list.ordering', 'a.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $orderMap = [
            'a.name' => $db->quoteName('a.name'),
            'name' => $db->quoteName('a.name'),
            'a.website' => $db->quoteName('a.website'),
            'website' => $db->quoteName('a.website'),
            'a.country' => $db->quoteName('a.country'),
            'country' => $db->quoteName('a.country'),
            'a.state' => $db->quoteName('a.state'),
            'state' => $db->quoteName('a.state'),
            'a.location' => $db->quoteName('a.location'),
            'location' => $db->quoteName('a.location'),
            'a.id' => $db->quoteName('a.id'),
            'id' => $db->quoteName('a.id'),
            'a.published' => $db->quoteName('a.published'),
            'published' => $db->quoteName('a.published'),
            'a.ordering' => $db->quoteName('a.ordering'),
            'ordering' => $db->quoteName('a.ordering'),
            'a.modified' => $db->quoteName('a.modified'),
            'modified' => $db->quoteName('a.modified'),
        ];

        $query->order(($orderMap[$ordering] ?? $orderMap['a.name']) . ' ' . $direction);

        return $query;
    }

    public function getClubListSelect(): array
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
                $db->quoteName('country'),
                $db->quoteName('standard_playground'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club'))
            ->order($db->quoteName('name'));

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
