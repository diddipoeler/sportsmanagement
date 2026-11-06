<?php
/**
 * Native Joomla 5/6 administrator form model for team staff assignments.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\TeamstaffTable;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 administrator form model for team staff assignments. */
final class TeamstaffModel extends SportsManagementAdminModel
{
    /**
     * Load the team-staff administrator form and configured picture fields.
     * Reads component media settings and assigns the default picture,
     * directory and media control type.
     * @param array $data Optional form data.
     * @param bool $loadData Whether stored values should be loaded.
     * @return mixed Joomla form or false when unavailable.
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = parent::getForm($data, $loadData);

        if (!$form) {
            return false;
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $mediaTool = trim((string) $params->get('cfg_which_media_tool', 'media'));

        if ($mediaTool === '' || ctype_digit($mediaTool)) {
            $mediaTool = 'media';
        }

        $form->setFieldAttribute('picture', 'default', (string) $params->get('ph_player', ''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/teamstaffs');
        $form->setFieldAttribute('picture', 'type', $mediaTool);

        return $form;
    }

    /**
     * Resolve the native team-staff database table.
     * @param string $type Requested Joomla table name.
     * @param string $prefix Legacy table prefix.
     * @param array $config Optional table settings.
     * @return mixed Native TeamstaffTable or parent table.
     */
    public function getTable($type = 'teamstaff', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'teamstaff') === 0) {
            return new TeamstaffTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Save selected project-position assignments from the compact staff list.
     *
     * Reads staff IDs and position changes from Joomla POST and writes
     * the assignments in a transaction with rollback on database failure.
     *
     * @return bool Whether all selected assignments were saved.
     */
    public function saveshort(): bool
    {
        $input = $this->administratorApplication()->getInput();
        $ids = array_values(array_unique(array_filter(array_map(
            'intval',
            (array) $input->post->get('cid', [], 'array')
        ))));
        $post = $input->post->getArray();
        $db = $this->getDatabase();
        $db->transactionStart();

        try {
            foreach ($ids as $id) {
                $row = (object) [
                    'id' => $id,
                    'project_position_id' => (int) ($post['project_position_id' . $id] ?? 0),
                ];
                $db->updateObject('#__sportsmanagement_team_staff', $row, 'id');
            }

            $db->transactionCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());

            return false;
        }
    }

    /**
     * Update ordering of selected team-staff relationships.
     * Updates only positive team-staff record identifiers within
     * a transaction and rolls back when database writes fail.
     * @param array|null $pks Selected staff IDs.
     * @param array|null $order Desired ordering values.
     * @return bool Whether the update succeeded.
     */
    public function saveorder($pks = null, $order = null)
    {
        $ids = array_values(array_map('intval', (array) $pks));
        $ordering = array_values(array_map('intval', (array) $order));
        $count = min(count($ids), count($ordering));

        if ($count === 0) {
            return true;
        }

        $db = $this->getDatabase();
        $db->transactionStart();

        try {
            for ($i = 0; $i < $count; ++$i) {
                if ($ids[$i] <= 0) {
                    continue;
                }

                $row = (object) [
                    'id' => $ids[$i],
                    'ordering' => $ordering[$i],
                ];
                $db->updateObject('#__sportsmanagement_team_staff', $row, 'id');
            }

            $db->transactionCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());

            return false;
        }
    }

    /**
     * Remove team-staff assignments and dependent match-staff records.
     *
     * Deletes match staff and statistic relations before delegating the
     * parent staff deletion, all within one database transaction.
     *
     * @param array $pks Selected team-staff IDs, updated on success.
     * @return bool Whether all related records were deleted.
     */
    public function delete(&$pks)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $pks))));

        if (!$ids) {
            return true;
        }

        $db = $this->getDatabase();
        $db->transactionStart();

        try {
            foreach (['#__sportsmanagement_match_staff', '#__sportsmanagement_match_staff_statistic'] as $table) {
                $query = $db->createQuery()
                    ->delete($db->quoteName($table))
                    ->whereIn($db->quoteName('team_staff_id'), $ids, ParameterType::INTEGER);
                $db->setQuery($query)->execute();
            }

            if (!parent::delete($ids)) {
                throw new \RuntimeException($this->getError() ?: 'Unable to delete team staff.');
            }

            $db->transactionCommit();
            $pks = $ids;

            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());

            return false;
        }
    }

    /**
     * Serialise extended team-staff form fields into registry data.
     * @param array $data Submitted team-staff values.
     * @return array Updated model data for saving.
     */
    protected function prepareSportsManagementData(array $data): array
    {
        $extended = $this->administratorApplication()->getInput()->post->get('extended', null, 'array');

        if (is_array($extended)) {
            $params = new Registry();
            $params->loadArray($extended);
            $data['extended'] = $params->toString();
        }

        return $data;
    }

    /**
     * Check edit permission for a team-staff record.
     * Falls back to Joomla's standard model ACL when no record-specific
     * edit permission is granted or an identity is absent.
     * @param array $data Team-staff row data.
     * @param string $key Record identifier key.
     * @return bool Whether the record can be edited.
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $id = (int) ($data[$key] ?? 0);

        $identity = $this->administratorApplication()->getIdentity();

        return ($identity !== null && $identity->authorise(
            'core.edit',
            'com_sportsmanagement.teamstaff.' . $id
        )) || parent::allowEdit($data, $key);
    }
}
