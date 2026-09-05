<?php
/**
 * Native Joomla 5/6 event-types list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/** Native Joomla 5/6 list model for event types. */
final class EventtypesModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'obj.name', 'name',
            'obj.icon', 'icon',
            'obj.sports_type_id', 'sports_type_id',
            'obj.published', 'published', 'state',
            'obj.id', 'id',
            'obj.ordering', 'ordering',
            'st.name', 'sports_type',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'obj.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);
        $app = $this->administratorApplication();

        $this->setState('filter.search', $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
        $this->setState('filter.state', $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));
        $this->setState('filter.sports_type', $app->getUserStateFromRequest($this->context . '.filter.sports_type', 'filter_sports_type', '', 'int'));
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('obj.id'),
                $db->quoteName('obj.name'),
                $db->quoteName('obj.alias'),
                $db->quoteName('obj.icon'),
                $db->quoteName('obj.sports_type_id'),
                $db->quoteName('obj.published'),
                $db->quoteName('obj.ordering'),
                $db->quoteName('obj.checked_out'),
                $db->quoteName('obj.checked_out_time'),
                $db->quoteName('obj.modified'),
                $db->quoteName('obj.modified_by'),
                $db->quoteName('st.name', 'sportstype'),
                $db->quoteName('uc.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_eventtype', 'obj'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('obj.sports_type_id'))
            ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('obj.checked_out'));

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('obj.name') . ') LIKE LOWER(' . $token . ')');
        }

        $state = $this->getState('filter.state');
        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('obj.published') . ' = ' . (int) $state);
        }

        $sportsType = (int) $this->getState('filter.sports_type');
        if ($sportsType > 0) {
            $query->where($db->quoteName('obj.sports_type_id') . ' = ' . $sportsType);
        }

        $ordering = (string) $this->getState('list.ordering', 'obj.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $orderMap = [
            'obj.name' => $db->quoteName('obj.name'), 'name' => $db->quoteName('obj.name'),
            'obj.icon' => $db->quoteName('obj.icon'), 'icon' => $db->quoteName('obj.icon'),
            'obj.sports_type_id' => $db->quoteName('obj.sports_type_id'), 'sports_type_id' => $db->quoteName('obj.sports_type_id'),
            'st.name' => $db->quoteName('st.name'), 'sports_type' => $db->quoteName('st.name'),
            'obj.published' => $db->quoteName('obj.published'), 'published' => $db->quoteName('obj.published'), 'state' => $db->quoteName('obj.published'),
            'obj.ordering' => $db->quoteName('obj.ordering'), 'ordering' => $db->quoteName('obj.ordering'),
            'obj.id' => $db->quoteName('obj.id'), 'id' => $db->quoteName('obj.id'),
        ];
        $query->order(($orderMap[$ordering] ?? $orderMap['obj.name']) . ' ' . $direction);

        return $query;
    }

    /**
     * Native replacement for legacy MatchModel::getEventsOptions().
     *
     * @return array<int,object>
     */
    public function getEventsOptions($projectId = 0, $matchId = 0): array
    {
        $projectId = (int) $projectId;
        $matchId = (int) $matchId;

        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();

        if ($matchId > 0) {
            $matchQuery = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__sportsmanagement_match'))
                ->where($db->quoteName('id') . ' = ' . $matchId);
            $db->setQuery($matchQuery);

            if ((int) $db->loadResult() === 0) {
                return [];
            }
        }

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('et.id', 'value'),
                $db->quoteName('et.name', 'text'),
                $db->quoteName('et.icon', 'icon'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_position', 'ppos'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_position_eventtype', 'pet')
                . ' ON ' . $db->quoteName('pet.position_id') . ' = ' . $db->quoteName('ppos.position_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_eventtype', 'et')
                . ' ON ' . $db->quoteName('et.id') . ' = ' . $db->quoteName('pet.eventtype_id')
            )
            ->where($db->quoteName('ppos.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('et.published') . ' = 1')
            ->group([
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
                $db->quoteName('et.ordering'),
            ])
            ->order($db->quoteName('et.ordering') . ' ASC')
            ->order($db->quoteName('et.id') . ' ASC');

        try {
            $db->setQuery($query);
            $items = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return [];
        }

        foreach ($items as $item) {
            $item->text = Text::_((string) $item->text);
        }

        return $items;
    }

    public static function getEvents(int $sportsTypeId = 0): array
    {
        $db = (new SportsManagementDatabaseResolver())->resolve();
        $query = $db->getQuery(true)
            ->select(['evt.id AS value', 'evt.name AS posname', 'st.name AS stname'])
            ->from($db->quoteName('#__sportsmanagement_eventtype', 'evt'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON st.id = evt.sports_type_id')
            ->where('evt.published = 1')
            ->order('evt.name ASC');

        if ($sportsTypeId > 0) {
            $query->where('evt.sports_type_id = ' . $sportsTypeId);
        }

        $db->setQuery($query);
        $items = $db->loadObjectList() ?: [];

        foreach ($items as $item) {
            $item->text = Text::_($item->posname) . ' (' . Text::_($item->stname) . ')';
        }

        return $items;
    }

    /**
     * Return event types already assigned to a position.
     *
     * @param int $positionId Position ID.
     *
     * @return array|false
     */
    public function getEventsPosition($positionId = 0)
    {
        $db = $this->getDatabase();
        $positionId = (int) $positionId;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id', 'value'),
                $db->quoteName('p.name', 'posname'),
                $db->quoteName('st.name', 'stname'),
            ])
            ->from($db->quoteName('#__sportsmanagement_eventtype', 'p'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position_eventtype', 'pe') . ' ON ' . $db->quoteName('pe.eventtype_id') . ' = ' . $db->quoteName('p.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('p.sports_type_id'))
            ->order($db->quoteName('pe.ordering') . ' ASC');

        if ($positionId > 0) {
            $query->where($db->quoteName('pe.position_id') . ' = ' . $positionId);
        }

        try {
            $db->setQuery($query);
            $items = $db->loadObjectList() ?: [];

            foreach ($items as $item) {
                $item->text = Text::_($item->posname) . ' (' . Text::_($item->stname) . ')';
            }

            return $items;
        } catch (\RuntimeException $e) {
            $this->administratorApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Return all event types as value/text options.
     */
    public function getEventList(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_eventtype'))
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
