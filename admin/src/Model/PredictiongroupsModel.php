<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Native Joomla 5/6 administrator list model for prediction groups.
 */
final class PredictiongroupsModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            's.name', 'name',
            's.ordering', 'ordering',
            's.id', 'id',
            's.modified', 'modified',
            's.modified_by', 'modified_by',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 's.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = Factory::getApplication();
        $this->setState(
            'filter.search',
            $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string')
        );
        $this->setState(
            'filter.state',
            $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string')
        );
        $this->setState(
            'filter.prediction_id',
            $app->getUserStateFromRequest(
                $this->context . '.filter.prediction_id',
                'filter_prediction_id',
                '',
                'string'
            )
        );
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('s') . '.*',
                $db->quoteName('u.name', 'editor'),
                $db->quoteName('u1.username'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_groups', 's'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u')
                . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('s.checked_out')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u1')
                . ' ON ' . $db->quoteName('u1.id') . ' = ' . $db->quoteName('s.modified_by')
            );

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('s.name') . ') LIKE LOWER(' . $token . ')');
        }

        $orderMap = [
            's.name' => $db->quoteName('s.name'),
            'name' => $db->quoteName('s.name'),
            's.ordering' => $db->quoteName('s.ordering'),
            'ordering' => $db->quoteName('s.ordering'),
            's.id' => $db->quoteName('s.id'),
            'id' => $db->quoteName('s.id'),
            's.modified' => $db->quoteName('s.modified'),
            'modified' => $db->quoteName('s.modified'),
            's.modified_by' => $db->quoteName('s.modified_by'),
            'modified_by' => $db->quoteName('s.modified_by'),
        ];

        $ordering = (string) $this->getState('list.ordering', 's.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($orderMap[$ordering] ?? $orderMap['s.name']) . ' ' . $direction);

        return $query;
    }
}
