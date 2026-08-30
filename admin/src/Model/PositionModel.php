<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\Language\Text;

final class PositionModel extends SportsManagementAdminModel
{
    public function saveshort(): bool
    {
        $input = $this->administratorApplication()->getInput();
        $ids = array_values(array_filter(array_map('intval', (array) $input->post->get('cid', [], 'array'))));
        $post = $input->post->getArray();
        $result = true;

        foreach ($ids as $id) {
            $table = $this->getTable();

            if (!$table->load($id)) {
                $result = false;
                continue;
            }

            $parentId = (int) ($post['parent_id' . $id] ?? $table->parent_id);

            if ($parentId === $id) {
                $parentId = 0;
            }

            $table->parent_id = $parentId;

            if (!$table->check() || !$table->store()) {
                $result = false;
            }
        }

        return $result;
    }

    /** @return array<int, object> */
    public function getAssignedEvents(int $positionId): array
    {
        if ($positionId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('evt.id', 'value'),
                $db->quoteName('evt.name', 'event_name'),
                $db->quoteName('st.name', 'sport_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position_eventtype', 'pe'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_eventtype', 'evt')
                . ' ON ' . $db->quoteName('evt.id') . ' = ' . $db->quoteName('pe.eventtype_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_sports_type', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('evt.sports_type_id')
            )
            ->where($db->quoteName('pe.position_id') . ' = ' . $positionId)
            ->order($db->quoteName('pe.ordering') . ' ASC');
        $db->setQuery($query);
        $items = $db->loadObjectList() ?: [];

        foreach ($items as $item) {
            $item->text = $this->buildOptionText((string) $item->event_name, (string) $item->sport_name);
        }

        return $items;
    }

    /** @return array<int, object> */
    public function getAvailableEvents(int $positionId, int $sportsTypeId): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('evt.id', 'value'),
                $db->quoteName('evt.name', 'event_name'),
                $db->quoteName('st.name', 'sport_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_eventtype', 'evt'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_sports_type', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('evt.sports_type_id')
            )
            ->where($db->quoteName('evt.published') . ' = 1')
            ->order($db->quoteName('evt.name') . ' ASC');

        if ($sportsTypeId > 0) {
            $query->where($db->quoteName('evt.sports_type_id') . ' = ' . $sportsTypeId);
        }

        if ($positionId > 0) {
            $subQuery = $db->getQuery(true)
                ->select('1')
                ->from($db->quoteName('#__sportsmanagement_position_eventtype', 'pe'))
                ->where($db->quoteName('pe.position_id') . ' = ' . $positionId)
                ->where($db->quoteName('pe.eventtype_id') . ' = ' . $db->quoteName('evt.id'));
            $query->where('NOT EXISTS (' . $subQuery . ')');
        }

        $db->setQuery($query);
        $items = $db->loadObjectList() ?: [];

        foreach ($items as $item) {
            $item->text = $this->buildOptionText((string) $item->event_name, (string) $item->sport_name);
        }

        return $items;
    }

    /** @return array<int, object> */
    public function getAssignedStatistics(int $positionId): array
    {
        if ($positionId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('stat.id', 'value'),
                $db->quoteName('stat.name', 'stat_name'),
                $db->quoteName('st.name', 'sport_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position_statistic', 'ps'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_statistic', 'stat')
                . ' ON ' . $db->quoteName('stat.id') . ' = ' . $db->quoteName('ps.statistic_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_sports_type', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('stat.sports_type_id')
            )
            ->where($db->quoteName('ps.position_id') . ' = ' . $positionId)
            ->order($db->quoteName('ps.ordering') . ' ASC');
        $db->setQuery($query);
        $items = $db->loadObjectList() ?: [];

        foreach ($items as $item) {
            $item->text = $this->buildOptionText((string) $item->stat_name, (string) $item->sport_name);
        }

        return $items;
    }

    /** @return array<int, object> */
    public function getAvailableStatistics(int $positionId): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('stat.id', 'value'),
                $db->quoteName('stat.name', 'stat_name'),
                $db->quoteName('st.name', 'sport_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_statistic', 'stat'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_sports_type', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('stat.sports_type_id')
            )
            ->order($db->quoteName('stat.ordering') . ' ASC');

        if ($positionId > 0) {
            $subQuery = $db->getQuery(true)
                ->select('1')
                ->from($db->quoteName('#__sportsmanagement_position_statistic', 'ps'))
                ->where($db->quoteName('ps.position_id') . ' = ' . $positionId)
                ->where($db->quoteName('ps.statistic_id') . ' = ' . $db->quoteName('stat.id'));
            $query->where('NOT EXISTS (' . $subQuery . ')');
        }

        $db->setQuery($query);
        $items = $db->loadObjectList() ?: [];

        foreach ($items as $item) {
            $item->text = $this->buildOptionText((string) $item->stat_name, (string) $item->sport_name);
        }

        return $items;
    }

    protected function prepareSportsManagementData(array $data): array
    {
        $id = (int) ($data['id'] ?? 0);
        $parentId = max(0, (int) ($data['parent_id'] ?? 0));
        $data['parent_id'] = $id > 0 && $parentId === $id ? 0 : $parentId;
        $data = parent::prepareSportsManagementData($data);

        if (!empty($data['picture'])) {
            $data['picture'] = MediaHelper::getCleanMediaFieldValue((string) $data['picture']);
        }

        return $data;
    }

    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
        if ($id <= 0) {
            return;
        }

        $post = $this->administratorApplication()->getInput()->post->getArray();
        $syncEvents = (int) ($post['sync_position_events'] ?? 0) === 1;
        $syncStatistics = (int) ($post['sync_position_statistics'] ?? 0) === 1;

        if (!$syncEvents && !$syncStatistics) {
            parent::afterSportsManagementSave($data, $id, $isNew);
            return;
        }

        $db = $this->getDatabase();
        $transactionStarted = false;

        try {
            $db->transactionStart();
            $transactionStarted = true;

            if ($syncEvents) {
                $model = $this->getMVCFactory()->createModel(
                    'Positioneventtype',
                    'Administrator',
                    ['ignore_request' => true]
                );

                if (!$model instanceof PositioneventtypeModel) {
                    throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
                }

                $model->setDatabase($db);

                if (!$model->store(
                    ['position_eventslist' => (array) ($post['position_eventslist'] ?? [])],
                    $id,
                    false
                )) {
                    throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
                }
            }

            if ($syncStatistics) {
                $model = $this->getMVCFactory()->createModel(
                    'Positionstatistic',
                    'Administrator',
                    ['ignore_request' => true]
                );

                if (!$model instanceof PositionstatisticModel) {
                    throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
                }

                $model->setDatabase($db);

                if (!$model->store(
                    ['position_statistic' => (array) ($post['position_statistic'] ?? [])],
                    $id,
                    false
                )) {
                    throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
                }
            }

            $db->transactionCommit();
        } catch (\Throwable $e) {
            if ($transactionStarted) {
                try {
                    $db->transactionRollback();
                } catch (\Throwable) {
                    // Preserve the original assignment error.
                }
            }

            throw $e;
        }

        parent::afterSportsManagementSave($data, $id, $isNew);
    }

    private function buildOptionText(string $name, string $sportName): string
    {
        $name = Text::_($name);
        $sportName = Text::_($sportName);

        return $sportName !== '' ? $name . ' (' . $sportName . ')' : $name;
    }
}
