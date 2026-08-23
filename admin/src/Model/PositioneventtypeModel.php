<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PositioneventtypeTable;
use Joomla\CMS\Factory;

/**
 * Native Joomla 5/6 administrator model for position/event-type assignments.
 */
final class PositioneventtypeModel extends SportsManagementAdminModel
{
    public function getTable($type = 'positioneventtype', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'positioneventtype') === 0) {
            return new PositioneventtypeTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    public function saveorder($pks = null, $order = null)
    {
        $pks = array_values((array) $pks);
        $order = array_values((array) $order);
        $row = $this->getTable();
        $count = min(count($pks), count($order));

        for ($i = 0; $i < $count; $i++) {
            if (!$row->load((int) $pks[$i])) {
                return false;
            }

            if ((int) $row->ordering === (int) $order[$i]) {
                continue;
            }

            $row->ordering = (int) $order[$i];

            if (!$row->store()) {
                return false;
            }
        }

        return true;
    }

    public function store($data, $position_id)
    {
        $positionId = (int) $position_id;
        $eventIds = isset($data['position_eventslist']) && is_array($data['position_eventslist'])
            ? $data['position_eventslist']
            : [];
        $eventIds = array_values(array_unique(array_filter(array_map('intval', $eventIds), static fn (int $id): bool => $id > 0)));
        $db = $this->getDatabase();

        try {
            $delete = $db->getQuery(true)
                ->delete($db->quoteName('#__sportsmanagement_position_eventtype'))
                ->where($db->quoteName('position_id') . ' = ' . $positionId);

            if ($eventIds) {
                $delete->where($db->quoteName('eventtype_id') . ' NOT IN (' . implode(',', $eventIds) . ')');
            }

            $db->setQuery($delete)->execute();

            foreach ($eventIds as $ordering => $eventId) {
                $query = $db->getQuery(true)
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__sportsmanagement_position_eventtype'))
                    ->where($db->quoteName('position_id') . ' = ' . $positionId)
                    ->where($db->quoteName('eventtype_id') . ' = ' . $eventId);
                $db->setQuery($query);
                $id = (int) $db->loadResult();

                if ($id > 0) {
                    $query = $db->getQuery(true)
                        ->update($db->quoteName('#__sportsmanagement_position_eventtype'))
                        ->set($db->quoteName('ordering') . ' = ' . (int) $ordering)
                        ->where($db->quoteName('id') . ' = ' . $id);
                    $db->setQuery($query)->execute();
                    continue;
                }

                $record = (object) [
                    'position_id' => $positionId,
                    'eventtype_id' => $eventId,
                    'ordering' => (int) $ordering,
                ];
                $db->insertObject('#__sportsmanagement_position_eventtype', $record);
            }
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        return true;
    }

    protected function allowEdit($data = [], $key = 'id')
    {
        $id = (int) ($data[$key] ?? 0);

        return Factory::getApplication()->getIdentity()->authorise(
            'core.edit',
            'com_sportsmanagement.message.' . $id
        ) || parent::allowEdit($data, $key);
    }
}
