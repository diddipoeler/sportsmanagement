<?php
/**
 * Joomla 5/6 administrator model for project-position assignments.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * Native Joomla 5/6 administrator model for project-position assignments.
 */
final class ProjectpositionModel extends SportsManagementAdminModel
{
    /**
     * Return the native table on the model's already resolved connection.
     * Retain Joomla's MVC factory for custom table names and configurations.
     * @param string $name Requested table name, or empty for the default.
     * @param string $prefix Optional table prefix.
     * @param array $options Optional factory configuration.
     * @return mixed Native table or parent factory result.
     */
    public function getTable($name = '', $prefix = '', $options = [])
    {
        if (is_string($name) && ($name === '' || strcasecmp($name, 'Projectposition') === 0)
            && in_array($prefix, ['', 'Administrator', 'sportsmanagementTable'], true)
            && $options === []) {
            return $this->createResolvedTable(\Diddipoeler\Component\SportsManagement\Administrator\Table\ProjectpositionTable::class);
        }

        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Synchronise project-to-position assignments in a transaction.
     * Deletes obsolete links, inserts missing records and rolls back
     * all changes when a database operation fails.
     * @param array $data Project ID and selected position list.
     * @return bool Whether the assignment update succeeded.
     */
    public function store(array $data): bool
    {
        $db = $this->getDatabase();
        $projectId = (int) ($data['project_id'] ?? 0);
        $positionIds = isset($data['project_positionslist']) && is_array($data['project_positionslist'])
            ? $data['project_positionslist']
            : [];

        ArrayHelper::toInteger($positionIds);
        $positionIds = array_values(array_unique(array_filter($positionIds, static fn ($id) => $id > 0)));
        $transactionStarted = false;

        try {
            $db->transactionStart();
            $transactionStarted = true;

            $query = $db->createQuery()
                ->delete($db->quoteName('#__sportsmanagement_project_position'))
                ->where($db->quoteName('project_id') . ' = :deleteProjectId')
                ->bind(':deleteProjectId', $projectId, ParameterType::INTEGER);

            if ($positionIds) {
                $query->whereNotIn($db->quoteName('position_id'), $positionIds, ParameterType::INTEGER);
            }

            $db->setQuery($query)->execute();

            foreach ($positionIds as $positionId) {
                $query = $db->createQuery()
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__sportsmanagement_project_position'))
                    ->where($db->quoteName('project_id') . ' = :projectId')
                    ->where($db->quoteName('position_id') . ' = :positionId')
                    ->bind(':projectId', $projectId, ParameterType::INTEGER)
                    ->bind(':positionId', $positionId, ParameterType::INTEGER);
                $db->setQuery($query);

                if ((int) $db->loadResult() > 0) {
                    continue;
                }

                $assignment = new \stdClass();
                $assignment->project_id = $projectId;
                $assignment->position_id = (int) $positionId;
                $db->insertObject('#__sportsmanagement_project_position', $assignment);
            }

            $db->transactionCommit();

            return true;
        } catch (\Throwable $e) {
            if ($transactionStarted) {
                try {
                    $db->transactionRollback();
                } catch (\Throwable) {
                    // Preserve the original database error.
                }
            }

            $this->setError($e->getMessage());
            $this->administratorApplication()->enqueueMessage(
                Text::_('JLIB_DATABASE_ERROR_FUNCTION_FAILED') . ': ' . $e->getMessage(),
                'error'
            );

            return false;
        }
    }

    /**
     * Determine whether the administrator may edit a project position.
     * Uses Joomla's record-specific ACL inheritance and rejects unauthenticated identities.
     * @param array $data Current assignment record.
     * @param string $key Identifier field.
     * @return bool Whether editing is permitted.
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $identity = $this->administratorApplication()->getIdentity();
        if ($identity === null || (int) $identity->id <= 0) {
            return false;
        }

        $id = (int) ($data[$key] ?? 0);
        return $identity->authorise('core.edit', 'com_sportsmanagement.projectposition.' . $id);
    }
}
