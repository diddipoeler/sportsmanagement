<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

final class LeagueModel extends SportsManagementAdminModel
{
    public function saveshort(): bool
    {
        $input = Factory::getApplication()->input;
        $ids = array_map('intval', (array) $input->post->get('cid', [], 'array'));
        $post = $input->post->getArray();
        $result = true;

        foreach ($ids as $id) {
            if ($id <= 0) {
                continue;
            }

            $table = $this->getTable();

            if (!$table->load($id)) {
                $result = false;
                continue;
            }

            $table->country = (string) ($post['country' . $id] ?? $table->country);
            $table->associations = (int) ($post['association' . $id] ?? $table->associations);
            $table->agegroup_id = (int) ($post['agegroup' . $id] ?? $table->agegroup_id);
            $table->published_act_season = (int) ($post['published_act_season' . $id] ?? $table->published_act_season);
            $table->champions_complete = (int) ($post['champions_complete' . $id] ?? $table->champions_complete);

            if (!$table->check() || !$table->store()) {
                $result = false;
            }
        }

        return $result;
    }
}
