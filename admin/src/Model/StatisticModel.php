<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

/** Native Joomla 5/6 administrator form model for a statistic definition. */
final class StatisticModel extends SportsManagementAdminModel
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

        $directory = ($mediaTool === 'media' ? 'local-0:/' : '') . 'com_sportsmanagement/database/statistics';

        $form->setFieldAttribute('icon', 'default', (string) $params->get('ph_icon', ''));
        $form->setFieldAttribute('icon', 'directory', $directory);
        $form->setFieldAttribute('icon', 'type', $mediaTool);

        return $form;
    }

    protected function prepareSportsManagementData(array $data): array
    {
        $post = Factory::getApplication()->getInput()->post->getArray();

        if (empty($data['id'])) {
            $data['class'] = trim((string) ($data['class'] ?? '')) ?: 'basic';
            $data['calculated'] = (int) ($data['calculated'] ?? 0);
        }

        if (isset($post['params']) && is_array($post['params'])) {
            $encoded = json_encode($post['params'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if ($encoded !== false) {
                $data['params'] = $encoded;
            }
        }

        return parent::prepareSportsManagementData($data);
    }
}
