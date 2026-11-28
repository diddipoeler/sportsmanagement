<?php
/**
 * Joomla 5/6 administrator model for position/statistic assignments.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PositionstatisticTable;
use Joomla\Database\ParameterType;

/**
 * Native Joomla 5/6 administrator model for position/statistic assignments.
 */
final class PositionstatisticModel extends SportsManagementAdminModel
{
    /**
     * Resolve the native position-statistic association table.
     * @param string $type Requested table.
     * @param string $prefix Legacy Joomla table prefix.
     * @param array $config Optional table options.
     * @return mixed Native statistic table or parent result.
     */
    public function getTable($type = 'positionstatistic', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'positionstatistic') === 0) {
            return new PositionstatisticTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Update ordering of selected position-statistic relations.
     * @param array|null $pks Relation identifiers.
     * @param array|null $order Matching ordering values.
     * @return bool Whether all changes succeeded.
     */
    public function saveorder($pks = null, $order = null)
    {
        $identity = $this->administratorApplication()->getIdentity();

        if ($identity === null || !$identity->authorise('core.edit', 'com_sportsmanagement')) {
            throw new \RuntimeException(\Joomla\CMS\Language\Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

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

    /**
     * Synchronise statistics assigned to the selected playing position.
     * Deletes obsolete links and reorders or inserts current relations
     * within an optional database transaction.
     * Rejects malformed selections before deleting links; an explicit empty array clears them.
     * @param array $data Selected position statistic IDs.
     * @param int $position_id Parent position identifier.
     * @param bool $manageTransaction Whether to manage the transaction here.
     * @return bool True when the assignment update succeeds.
     */
    public function store($data, $position_id, bool $manageTransaction = true)
    {
        $identity = $this->administratorApplication()->getIdentity();

        if ($identity === null || !$identity->authorise('core.edit', 'com_sportsmanagement')) {
            throw new \RuntimeException(\Joomla\CMS\Language\Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        // Never clear associations for an absent or malformed position ID.
        if ((!is_int($position_id) && !is_string($position_id))
            || !preg_match('/^[1-9][0-9]*$/D', (string) $position_id)
            || filter_var((string) $position_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            $this->setError(\Joomla\CMS\Language\Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));

            return false;
        }

        $positionId = (int) $position_id;
        if (!is_array($data) || !array_key_exists('position_statistic', $data) || !is_array($data['position_statistic'])) {
            $this->setError(\Joomla\CMS\Language\Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
            return false;
        }

        $statisticIds = [];
        foreach ($data['position_statistic'] as $raw) {
            if ($raw === '' || $raw === 0 || $raw === '0') {
                continue;
            }

            if (is_string($raw) && strlen($raw) <= 64 && preg_match('/^[0-9]+$/D', $raw) === 1) {
                $raw = ltrim($raw, '0');
                if ($raw === '') {
                    continue;
                }
            } elseif (!is_int($raw)) {
                $this->setError(\Joomla\CMS\Language\Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
                return false;
            }

            $id = filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) {
                $this->setError(\Joomla\CMS\Language\Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
                return false;
            }
            $statisticIds[$id] = $id;
        }
        $statisticIds = array_values($statisticIds);
        $db = $this->getDatabase();
        $transactionStarted = false;

        try {
            if ($manageTransaction) {
                $db->transactionStart();
                $transactionStarted = true;
            }

            $delete = $db->createQuery()
                ->delete($db->quoteName('#__sportsmanagement_position_statistic'))
                ->where($db->quoteName('position_id') . ' = :deletePositionId')
                ->bind(':deletePositionId', $positionId, ParameterType::INTEGER);

            if ($statisticIds) {
                $delete->whereNotIn($db->quoteName('statistic_id'), $statisticIds, ParameterType::INTEGER);
            }

            $db->setQuery($delete)->execute();

            foreach ($statisticIds as $ordering => $statisticId) {
                $query = $db->createQuery()
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__sportsmanagement_position_statistic'))
                    ->where($db->quoteName('position_id') . ' = :positionId')
                    ->where($db->quoteName('statistic_id') . ' = :statisticId')
                    ->bind(':positionId', $positionId, ParameterType::INTEGER)
                    ->bind(':statisticId', $statisticId, ParameterType::INTEGER);
                $db->setQuery($query);
                $id = (int) $db->loadResult();

                if ($id > 0) {
                    $orderingValue = (int) $ordering;
                    $query = $db->createQuery()
                        ->update($db->quoteName('#__sportsmanagement_position_statistic'))
                        ->set($db->quoteName('ordering') . ' = :ordering')
                        ->where($db->quoteName('id') . ' = :id')
                        ->bind(':ordering', $orderingValue, ParameterType::INTEGER)
                        ->bind(':id', $id, ParameterType::INTEGER);
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

            if ($transactionStarted) {
                $db->transactionCommit();
            }
        } catch (\Throwable $e) {
            if ($transactionStarted) {
                try {
                    $db->transactionRollback();
                } catch (\Throwable) {
                    // Preserve the original database error below.
                }
            }

            \Joomla\CMS\Log\Log::add(
                'SportsManagement positionstatistic database operation failed: ' . $e->getMessage(),
                \Joomla\CMS\Log\Log::ERROR,
                'com_sportsmanagement'
            );
            $this->administratorApplication()->enqueueMessage(\Joomla\CMS\Language\Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

            return false;
        }

        return true;
    }

    /**
     * Check component-level Joomla edit permission for statistic relations.
     * @param array $data Current relation data.
     * @param string $key Relation ID field.
     * @return bool Whether editing is allowed.
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $identity = $this->administratorApplication()->getIdentity();

        return $identity !== null && (int) $identity->id > 0
            && $identity->authorise('core.edit', 'com_sportsmanagement');
    }
}
