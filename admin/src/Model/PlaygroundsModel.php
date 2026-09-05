<?php
/**
 * Native Joomla 5/6 list model for playgrounds.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class PlaygroundsModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'v.name', 'name',
            'v.alias', 'alias',
            'v.short_name', 'short_name',
            'v.max_visitors', 'max_visitors',
            'v.picture', 'picture',
            'v.country', 'country',
            'c.name', 'club',
            'v.published', 'published', 'state',
            'v.ordering', 'ordering',
            'v.id', 'id',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'v.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);
        $app = $this->administratorApplication();

        // populateState() is still running here, so read the state bag directly
        // instead of re-entering Joomla's lazy getState() initialisation.
        if ((string) $this->state->get('filter.search_nation', '') === '') {
            $legacyNation = $app->getInput()->getString('filter_search_nation', '');
            if ($legacyNation !== '') {
                $this->setState('filter.search_nation', $legacyNation);
            }
        }
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('v.id'), $db->quoteName('v.name'), $db->quoteName('v.short_name'),
                $db->quoteName('v.alias'), $db->quoteName('v.address'), $db->quoteName('v.zipcode'),
                $db->quoteName('v.city'), $db->quoteName('v.country'), $db->quoteName('v.max_visitors'),
                $db->quoteName('v.max_visitors_int'), $db->quoteName('v.picture'), $db->quoteName('v.club_id'),
                $db->quoteName('v.ordering'), $db->quoteName('v.checked_out'), $db->quoteName('v.checked_out_time'),
                $db->quoteName('v.modified'), $db->quoteName('v.modified_by'), $db->quoteName('v.latitude'),
                $db->quoteName('v.longitude'), $db->quoteName('v.published'), $db->quoteName('c.name', 'club'),
                $db->quoteName('uc.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_playground', 'v'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('v.club_id'))
            ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('v.checked_out'));

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('(' . $db->quoteName('v.name') . ' LIKE ' . $token . ' OR ' . $db->quoteName('v.short_name') . ' LIKE ' . $token . ' OR ' . $db->quoteName('v.city') . ' LIKE ' . $token . ')');
        }

        $country = trim((string) $this->getState('filter.search_nation'));
        if ($country !== '') {
            $query->where($db->quoteName('v.country') . ' = ' . $db->quote($country));
        }

        $state = $this->getState('filter.state');
        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('v.published') . ' = ' . (int) $state);
        }

        $map = [
            'v.name' => $db->quoteName('v.name'), 'name' => $db->quoteName('v.name'),
            'v.alias' => $db->quoteName('v.alias'), 'alias' => $db->quoteName('v.alias'),
            'v.short_name' => $db->quoteName('v.short_name'), 'short_name' => $db->quoteName('v.short_name'),
            'v.max_visitors' => $db->quoteName('v.max_visitors'), 'max_visitors' => $db->quoteName('v.max_visitors'),
            'v.picture' => $db->quoteName('v.picture'), 'picture' => $db->quoteName('v.picture'),
            'v.country' => $db->quoteName('v.country'), 'country' => $db->quoteName('v.country'),
            'c.name' => $db->quoteName('c.name'), 'club' => $db->quoteName('c.name'),
            'v.published' => $db->quoteName('v.published'), 'published' => $db->quoteName('v.published'), 'state' => $db->quoteName('v.published'),
            'v.ordering' => $db->quoteName('v.ordering'), 'ordering' => $db->quoteName('v.ordering'),
            'v.id' => $db->quoteName('v.id'), 'id' => $db->quoteName('v.id'),
        ];
        $ordering = (string) $this->getState('list.ordering', 'v.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($map[$ordering] ?? $map['v.name']) . ' ' . $direction);

        return $query;
    }

    public function getPlaygroundListSelect(): array
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
                $db->quoteName('country'),
                $db->quoteName('short_name'),
                $db->quoteName('club_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_playground'))
            ->order($db->quoteName('name'));
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getPlaygrounds($picture = false, $projectteams = []): array
    {
        $ids = [];
        foreach ((array) $projectteams as $projectTeam) {
            $id = (int) ($projectTeam->value ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        if (!$ids) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p.id', 'value'),
                $db->quoteName('p.name'),
                $db->quoteName('p.short_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_playground', 'p'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'club') . ' ON ' . $db->quoteName('club.standard_playground') . ' = ' . $db->quoteName('p.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 'team') . ' ON ' . $db->quoteName('team.club_id') . ' = ' . $db->quoteName('club.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'steam') . ' ON ' . $db->quoteName('steam.team_id') . ' = ' . $db->quoteName('team.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pthome') . ' ON ' . $db->quoteName('pthome.team_id') . ' = ' . $db->quoteName('steam.id'))
            ->where($db->quoteName('pthome.id') . ' IN (' . implode(',', array_unique($ids)) . ')')
            ->order([
                $db->quoteName('p.name') . ' ASC',
                $db->quoteName('p.short_name') . ' ASC',
            ]);
        $query->distinct();

        if ($picture) {
            $query->select($db->quoteName('p.picture', 'playgroundpicture'));
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        foreach ($rows as $row) {
            $row->text = $row->name === null || $row->short_name === null
                ? null
                : (string) $row->name . ' (' . (string) $row->short_name . ')';
            unset($row->name, $row->short_name);
        }

        return $rows;
    }
}
