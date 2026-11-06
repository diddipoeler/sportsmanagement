<?php
/**
 * Joomla 5/6 administrator form model for project referees.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\ProjectrefereeTable;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

/**
 * Native Joomla 5/6 administrator form model for project referees.
 */
final class ProjectrefereeModel extends SportsManagementAdminModel
{
    /**
     * Resolve the Joomla project-referee table used by administrator forms.
     * @param string $type Requested table name.
     * @param string $prefix Legacy Joomla table prefix.
     * @param array $config Additional table factory options.
     * @return mixed Native ProjectrefereeTable or parent table result.
     */
    public function getTable($type = 'ProjectReferee', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'ProjectReferee') === 0) {
            return new ProjectrefereeTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Save referee position assignments from the compact administrator form.
     * Normalises selected referee relation IDs and updates audit fields
     * within a database transaction, rolling back any unsuccessful update.
     * @param array $pks Selected relation identifiers, or POST selection.
     * @param array $post Submitted position values, or current POST data.
     * @return bool Whether the update was committed.
     */
    public function saveshort(array $pks = [], array $post = [])
    {
        $app = $this->administratorApplication();
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
        $modified = (new Date('now', new \DateTimeZone('UTC')))->toSql();
        $userId = (int) ($app->getIdentity()?->id ?? 0);
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

    /**
     * Remove project referee relations and dependent match assignments.
     * Deletes dependent match-referee entries and parent rows in one
     * database transaction, rolling back on failure.
     * @param array $pks Selected project-referee identifiers.
     * @return bool Whether deletion succeeded.
     */
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

            $query = $db->createQuery()
                ->delete($db->quoteName('#__sportsmanagement_match_referee'))
                ->whereIn($db->quoteName('project_referee_id'), $ids, ParameterType::INTEGER);
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
            $this->administratorApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Accept only bounded positive project-referee identifiers.
     * Rejects malformed and overflowing strings rather than converting
     * them via permissive integer casts, and removes duplicates.
     * @param array $ids Submitted record identifiers.
     * @return array<int> Up to 500 unique positive IDs.
     */
    private function normaliseIds(array $ids): array
    {
        $valid = [];

        foreach (array_slice($ids, 0, 500) as $rawId) {
            if (!is_int($rawId) && !is_string($rawId)) {
                continue;
            }

            $rawId = trim((string) $rawId);

            if (strlen($rawId) > 20 || preg_match('/^[0-9]+$/D', $rawId) !== 1) {
                continue;
            }

            $digits = ltrim($rawId, '0');
            $id = $digits !== ''
                ? filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
                : false;

            if ($id !== false) {
                $valid[$id] = $id;
            }
        }

        return array_values($valid);
    }
}
