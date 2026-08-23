<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\TeamstaffTable;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 administrator form model for team staff assignments. */
final class TeamstaffModel extends SportsManagementAdminModel
{
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

    public function getTable($type = 'teamstaff', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'teamstaff') === 0) {
            return new TeamstaffTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /** Update selected project-position assignments from a compact list form. */
    public function saveshort(): bool
    {
        $input = Factory::getApplication()->getInput();
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

    /** Delete dependent match-staff rows before removing team-staff assignments. */
    public function delete(&$pks)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $pks))));

        if (!$ids) {
            return true;
        }

        $db = $this->getDatabase();
        $idList = implode(',', $ids);
        $db->transactionStart();

        try {
            foreach (['#__sportsmanagement_match_staff', '#__sportsmanagement_match_staff_statistic'] as $table) {
                $query = $db->getQuery(true)
                    ->delete($db->quoteName($table))
                    ->where($db->quoteName('team_staff_id') . ' IN (' . $idList . ')');
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

    protected function prepareSportsManagementData(array $data): array
    {
        $extended = Factory::getApplication()->getInput()->post->get('extended', [], 'array');

        if ($extended) {
            $params = new Registry();
            $params->loadArray($extended);
            $data['extended'] = $params->toString();
        }

        return $data;
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
