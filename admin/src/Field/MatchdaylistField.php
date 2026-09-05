<?php
/**
 * Joomla 5/6 native matchday list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

final class MatchdaylistField extends SportsManagementListField
{
    protected $type = 'Matchdaylist';

    protected function getOptions(): array
    {
        $varname = trim((string) ($this->element['varname'] ?? ''));

        if ($varname === '') {
            return parent::getOptions();
        }

        $projectId = Factory::getApplication()->getInput()->get($varname, null, 'raw');

        if (is_array($projectId)) {
            $projectId = reset($projectId) ?: 0;
        }

        $projectId = (int) $projectId;

        if ($projectId <= 0) {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('roundcode') . ' ASC');
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => (string) $item->text,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
