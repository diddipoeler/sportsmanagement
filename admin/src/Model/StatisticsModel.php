<?php
/**
 * Joomla 5/6 administrator statistics list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Native Joomla 5/6 administrator statistics list model.
 */
final class StatisticsModel extends SportsManagementListModel
{
    protected $_identifier = 'statistics';

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'obj.name', 'name',
            'obj.short', 'short',
            'obj.icon', 'icon',
            'obj.sports_type_id', 'sports_type_id',
            'obj.published', 'published', 'state',
            'obj.id', 'id',
            'obj.ordering', 'ordering',
        ];

        parent::__construct($config, $factory);
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                'obj.*',
                $db->quoteName('st.name', 'sportstype'),
                $db->quoteName('uc.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_statistic', 'obj'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_sports_type', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('obj.sports_type_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'uc')
                . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('obj.checked_out')
            );

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('obj.name') . ') LIKE LOWER(' . $token . ')');
        }

        $sportsType = (int) $this->getState('filter.sports_type');
        if ($sportsType > 0) {
            $query->where($db->quoteName('obj.sports_type_id') . ' = ' . $sportsType);
        }

        $state = $this->getState('filter.state');
        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('obj.published') . ' = ' . (int) $state);
        }

        $orderingMap = [
            'obj.name' => $db->quoteName('obj.name'),
            'name' => $db->quoteName('obj.name'),
            'obj.short' => $db->quoteName('obj.short'),
            'short' => $db->quoteName('obj.short'),
            'obj.icon' => $db->quoteName('obj.icon'),
            'icon' => $db->quoteName('obj.icon'),
            'obj.sports_type_id' => $db->quoteName('obj.sports_type_id'),
            'sports_type_id' => $db->quoteName('obj.sports_type_id'),
            'obj.published' => $db->quoteName('obj.published'),
            'published' => $db->quoteName('obj.published'),
            'state' => $db->quoteName('obj.published'),
            'obj.id' => $db->quoteName('obj.id'),
            'id' => $db->quoteName('obj.id'),
            'obj.ordering' => $db->quoteName('obj.ordering'),
            'ordering' => $db->quoteName('obj.ordering'),
        ];
        $ordering = (string) $this->getState('list.ordering', 'obj.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($orderingMap[$ordering] ?? $orderingMap['obj.name']) . ' ' . $direction);

        return $query;
    }

    public function getPositionStatsOptions($id): array
    {
        $positionId = max(0, (int) $id);
        if ($positionId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('s.id', 'value'),
                "CONCAT(" . $db->quoteName('s.name') . ", ' (' , " . $db->quoteName('st.name') . ", ')') AS " . $db->quoteName('text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_statistic', 's'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_position_statistic', 'ps')
                . ' ON ' . $db->quoteName('ps.statistic_id') . ' = ' . $db->quoteName('s.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_sports_type', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('s.sports_type_id')
            )
            ->where($db->quoteName('ps.position_id') . ' = ' . $positionId)
            ->order($db->quoteName('ps.ordering') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getAvailablePositionStatsOptions($id): array
    {
        $positionId = max(0, (int) $id);
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('s.id', 'value'),
                "CONCAT(" . $db->quoteName('s.name') . ", ' (' , " . $db->quoteName('st.name') . ", ')') AS " . $db->quoteName('text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_statistic', 's'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_position_statistic', 'ps')
                . ' ON ' . $db->quoteName('ps.statistic_id') . ' = ' . $db->quoteName('s.id')
                . ' AND ' . $db->quoteName('ps.position_id') . ' = ' . $positionId
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_sports_type', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('s.sports_type_id')
            )
            ->where($db->quoteName('ps.id') . ' IS NULL')
            ->order($db->quoteName('s.ordering') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getStatisticListSelect(): array
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
                $db->quoteName('short'),
                $db->quoteName('class'),
                $db->quoteName('note'),
            ])
            ->from($db->quoteName('#__sportsmanagement_statistic'))
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    protected function populateState($ordering = 'obj.name', $direction = 'ASC')
    {
        $app = Factory::getApplication();

        $this->setState(
            'filter.search',
            $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string')
        );
        $this->setState(
            'filter.state',
            $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string')
        );
        $this->setState(
            'filter.sports_type',
            $app->getUserStateFromRequest(
                $this->context . '.filter.sports_type',
                'filter_sports_type',
                '',
                'string'
            )
        );

        parent::populateState($ordering, $direction);
        $this->setState('list.start', max(0, $app->getInput()->getUInt('limitstart', 0)));
    }
}
