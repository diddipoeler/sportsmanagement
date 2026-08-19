<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

/**
 * Native Joomla 5/6 administrator model for project-position assignments.
 */
final class ProjectpositionModel extends SportsManagementAdminModel
{
    public function store(array $data): bool
    {
        $db = $this->getDatabase();
        $projectId = (int) ($data['project_id'] ?? 0);
        $positionIds = isset($data['project_positionslist']) && is_array($data['project_positionslist'])
            ? $data['project_positionslist']
            : [];

        ArrayHelper::toInteger($positionIds);
        $positionIds = array_values(array_unique(array_filter($positionIds, static fn ($id) => $id > 0)));

        try {
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__sportsmanagement_project_position'))
                ->where($db->quoteName('project_id') . ' = ' . $projectId);

            if ($positionIds) {
                $query->where($db->quoteName('position_id') . ' NOT IN (' . implode(',', $positionIds) . ')');
            }

            $db->setQuery($query)->execute();

            foreach ($positionIds as $positionId) {
                $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__sportsmanagement_project_position'))
                    ->where($db->quoteName('project_id') . ' = ' . $projectId)
                    ->where($db->quoteName('position_id') . ' = ' . (int) $positionId);
                $db->setQuery($query);

                if ((int) $db->loadResult() > 0) {
                    continue;
                }

                $assignment = new \stdClass();
                $assignment->project_id = $projectId;
                $assignment->position_id = (int) $positionId;
                $db->insertObject('#__sportsmanagement_project_position', $assignment);
            }
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage(
                Text::_('JLIB_DATABASE_ERROR_FUNCTION_FAILED') . ': ' . $e->getMessage(),
                'error'
            );

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
