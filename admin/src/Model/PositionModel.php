<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

final class PositionModel extends SportsManagementAdminModel
{
    public function saveshort(): bool
    {
        $input = Factory::getApplication()->getInput();
        $ids = array_values(array_filter(array_map('intval', (array) $input->post->get('cid', [], 'array'))));
        $post = $input->post->getArray();
        $result = true;

        foreach ($ids as $id) {
            $table = $this->getTable();

            if (!$table->load($id)) {
                $result = false;
                continue;
            }

            $parentId = (int) ($post['parent_id' . $id] ?? $table->parent_id);

            if ($parentId === $id) {
                $parentId = 0;
            }

            $table->parent_id = $parentId;

            if (!$table->check() || !$table->store()) {
                $result = false;
            }
        }

        return $result;
    }
}
