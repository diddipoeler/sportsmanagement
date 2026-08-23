<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 roster-position form model. */
final class RosterpositionModel extends SportsManagementAdminModel
{
    protected function prepareSportsManagementData(array $data): array
    {
        $post = Factory::getApplication()->getInput()->post->getArray();

        if (array_key_exists('extended', $post) && is_array($post['extended'])) {
            $parameter = new Registry();
            $parameter->loadArray($post['extended']);
            $data['extended'] = (string) $parameter;
        }

        if (array_key_exists('short_name', $data)) {
            $data['alias'] = (string) $data['short_name'];
        }

        return parent::prepareSportsManagementData($data);
    }

    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
        if ($isNew) {
            Factory::getApplication()->enqueueMessage(
                Text::plural('COM_SPORTSMANAGEMENT_N_ITEMS_CREATED', $id),
                'message'
            );
        }

        parent::afterSportsManagementSave($data, $id, $isNew);
    }
}
