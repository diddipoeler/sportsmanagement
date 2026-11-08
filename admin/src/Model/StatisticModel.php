<?php
/**
 * Joomla 5/6 administrator statistic form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;

/** Native Joomla 5/6 administrator form model for a statistic definition. */
final class StatisticModel extends SportsManagementAdminModel
{
    /**
     * Configure the Joomla 5/6 statistic editor's icon media field.
     * Loads the inherited form and applies validated media tool, directory
     * and default icon options from the component configuration.
     *
     * @param array $data Optional form input values.
     * @param bool $loadData Whether saved model data should be loaded.
     * @return mixed Configured form or false when unavailable.
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

        $directory = ($mediaTool === 'media' ? 'local-0:/' : '') . 'com_sportsmanagement/database/statistics';

        $form->setFieldAttribute('icon', 'default', (string) $params->get('ph_icon', ''));
        $form->setFieldAttribute('icon', 'directory', $directory);
        $form->setFieldAttribute('icon', 'type', $mediaTool);

        return $form;
    }

    /**
     * Prepare statistic settings before the shared administrator save routine.
     * Supplies defaults for new statistics and JSON-encodes submitted parameter
     * arrays only when serialisation succeeds, then calls the parent normaliser.
     *
     * @param array $data Submitted model values.
     * @return array Normalised values for persistence.
     */
    protected function prepareSportsManagementData(array $data): array
    {
        $post = $this->administratorApplication()->getInput()->post->getArray();

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
