<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

/**
 * Native Joomla 5/6 administrator model for position/statistic assignments.
 */
final class PositionstatisticModel extends SportsManagementAdminModel
{
    public function getTable($type = 'positionstatistic', $prefix = 'sportsmanagementTable', $config = [])
    {
        $config['dbo'] = $this->getDatabase();

        return Table::getInstance($type, $prefix, $config);
    }

    public function getScript(): string
    {
        return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
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
        $statisticIds = isset($data['position_statistic']) && is_array($data['position_statistic'])
            ? $data['position_statistic']
            : [];
        $statisticIds = array_values(array_unique(array_filter(array_map('intval', $statisticIds), static fn (int $id): bool => $id > 0)));
        $db = $this->getDatabase();

        try {
            $delete = $db->getQuery(true)
                ->delete($db->quoteName('#__sportsmanagement_position_statistic'))
                ->where($db->quoteName('position_id') . ' = ' . $positionId);

            if ($statisticIds) {
                $delete->where($db->quoteName('statistic_id') . ' NOT IN (' . implode(',', $statisticIds) . ')');
            }

            $db->setQuery($delete)->execute();

            foreach ($statisticIds as $ordering => $statisticId) {
                $query = $db->getQuery(true)
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__sportsmanagement_position_statistic'))
                    ->where($db->quoteName('position_id') . ' = ' . $positionId)
                    ->where($db->quoteName('statistic_id') . ' = ' . $statisticId);
                $db->setQuery($query);
                $id = (int) $db->loadResult();

                if ($id > 0) {
                    $query = $db->getQuery(true)
                        ->update($db->quoteName('#__sportsmanagement_position_statistic'))
                        ->set($db->quoteName('ordering') . ' = ' . (int) $ordering)
                        ->where($db->quoteName('id') . ' = ' . $id);
                    $db->setQuery($query)->execute();
                    continue;
                }

                $record = (object) [
                    'position_id' => $positionId,
                    'statistic_id' => $statisticId,
                    'ordering' => (int) $ordering,
                ];
                $db->insertObject('#__sportsmanagement_position_statistic', $record);
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
