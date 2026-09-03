<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;

final class SportstypeModel extends SportsManagementAdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        $form = parent::getForm($data, $loadData);

        if (!$form) {
            return false;
        }

        $mediaTool = trim((string) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_media_tool', 'media'));

        if ($mediaTool === '' || ctype_digit($mediaTool)) {
            $mediaTool = 'media';
        }

        $form->setFieldAttribute('icon', 'type', $mediaTool);

        try {
            $tableName = $this->getDatabase()->getPrefix() . 'sportsmanagement_sports_type';

            foreach ($this->getDatabase()->getTableColumns($tableName, true) as $fieldName => $type) {
                if (!$form->getField((string) $fieldName)) {
                    continue;
                }

                if (preg_match('/varchar\s*\(\s*(\d+)\s*\)/i', (string) $type, $match)) {
                    $form->setFieldAttribute((string) $fieldName, 'size', (string) (int) $match[1]);
                }
            }
        } catch (\Throwable) {
            // Column-length hints are a UI enhancement only.
        }

        return $form;
    }

    public function getSportstype(int $sportstypeId): ?object
    {
        if ($sportstypeId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_sports_type'))
            ->where($db->quoteName('id') . ' = ' . $sportstypeId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }
}
