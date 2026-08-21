<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\ProjectrefereeTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 administrator form model for project referees.
 */
final class ProjectrefereeModel extends SportsManagementAdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        return $this->loadForm(
            'com_sportsmanagement.projectreferee',
            'projectreferee',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    public function getTable($type = 'ProjectReferee', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'ProjectReferee') === 0) {
            return new ProjectrefereeTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    public function saveshort(array $pks = [], array $post = [])
    {
        $app = Factory::getApplication();
        $input = $app->getInput();

        if (!$pks) {
            $pks = $input->post->get('cid', [], 'array');
        }

        $pks = $this->normaliseIds($pks);

        if (!$pks) {
            return false;
        }

        if (!$post) {
            $post = $input->post->getArray();
        }

        $db = $this->getDatabase();
        $modified = Factory::getDate()->toSql();
        $userId = (int) $app->getIdentity()->id;

        try {
            foreach ($pks as $id) {
                $record = (object) [
                    'id' => $id,
                    'project_position_id' => (int) ($post['project_position_id' . $id] ?? 0),
                    'modified' => $modified,
                    'modified_by' => $userId,
                ];
                $db->updateObject('#__sportsmanagement_project_referee', $record, 'id', true);
            }
        } catch (\Throwable $e) {
            $app->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                'error'
            );

            return false;
        }

        return true;
    }

    public function delete(&$pks)
    {
        $ids = $this->normaliseIds((array) $pks);

        if (!$ids) {
            return true;
        }

        $db = $this->getDatabase();

        try {
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__sportsmanagement_match_referee'))
                ->where($db->quoteName('project_referee_id') . ' IN (' . implode(',', $ids) . ')');
            $db->setQuery($query)->execute();
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        $pks = $ids;

        return parent::delete($pks);
    }

    private function normaliseIds(array $ids): array
    {
        return array_values(
            array_unique(
                array_filter(
                    array_map('intval', $ids),
                    static fn (int $id): bool => $id > 0
                )
            )
        );
    }
}
