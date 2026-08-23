<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\ProjectrefereeTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 administrator form model for project referees.
 */
final class ProjectrefereeModel extends SportsManagementAdminModel
{
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
        $transactionStarted = false;

        try {
            $db->transactionStart();
            $transactionStarted = true;

            foreach ($pks as $id) {
                $record = (object) [
                    'id' => $id,
                    'project_position_id' => (int) ($post['project_position_id' . $id] ?? 0),
                    'modified' => $modified,
                    'modified_by' => $userId,
                ];
                $db->updateObject('#__sportsmanagement_project_referee', $record, 'id', true);
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
            $app->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                'error'
            );

            return false;
        }
    }

    public function delete(&$pks)
    {
        $ids = $this->normaliseIds((array) $pks);

        if (!$ids) {
            return true;
        }

        $db = $this->getDatabase();
        $transactionStarted = false;

        try {
            $db->transactionStart();
            $transactionStarted = true;

            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__sportsmanagement_match_referee'))
                ->where($db->quoteName('project_referee_id') . ' IN (' . implode(',', $ids) . ')');
            $db->setQuery($query)->execute();

            $pks = $ids;

            if (!parent::delete($pks)) {
                throw new \RuntimeException((string) $this->getError());
            }

            $db->transactionCommit();

            return true;
        } catch (\Throwable $e) {
            if ($transactionStarted) {
                try {
                    $db->transactionRollback();
                } catch (\Throwable) {
                    // Preserve the original delete error.
                }
            }

            $this->setError($e->getMessage());
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
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
