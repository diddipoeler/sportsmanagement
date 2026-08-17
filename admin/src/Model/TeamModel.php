<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

final class TeamModel extends SportsManagementAdminModel
{
    public function saveshort(): bool
    {
        $input = Factory::getApplication()->input;
        $ids = array_values(array_filter(array_map('intval', (array) $input->post->get('cid', [], 'array'))));
        $post = $input->post->getArray();
        $result = true;

        if (!$ids) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE_NO_SELECT'));
            return false;
        }

        foreach ($ids as $id) {
            $table = $this->getTable();

            if (!$table->load($id)) {
                $result = false;
                continue;
            }

            $table->sports_type_id = (int) ($post['sportstype' . $id] ?? $table->sports_type_id);
            $table->agegroup_id = (int) ($post['agegroup' . $id] ?? $table->agegroup_id);
            $table->modified = Factory::getDate()->toSql();
            $table->modified_by = (int) Factory::getApplication()->getIdentity()->id;

            if (!$table->check() || !$table->store()) {
                $result = false;
            }
        }

        return $result;
    }

    public function copySelected(): bool
    {
        $app = Factory::getApplication();
        $ids = array_values(array_filter(array_map('intval', (array) $app->input->post->get('cid', [], 'array'))));
        $result = true;

        if (!$ids) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE_NO_SELECT'));
            return false;
        }

        foreach ($ids as $id) {
            $source = $this->getTable();

            if (!$source->load($id)) {
                $result = false;
                continue;
            }

            $data = get_object_vars($source);
            unset($data['_db'], $data['_tbl'], $data['_tbl_key'], $data['_trackAssets'], $data['_rules'], $data['_errors']);
            $data['id'] = 0;
            $data['name'] = trim((string) $source->name) . ' (' . Text::_('JGLOBAL_COPY') . ')';
            $data['alias'] = '';
            $data['checked_out'] = 0;
            $data['checked_out_time'] = $this->getDatabase()->getNullDate();
            $data['modified'] = Factory::getDate()->toSql();
            $data['modified_by'] = (int) $app->getIdentity()->id;

            $copy = $this->getTable();

            if (!$copy->bind($data) || !$copy->check() || !$copy->store()) {
                $result = false;
            }
        }

        return $result;
    }
}
