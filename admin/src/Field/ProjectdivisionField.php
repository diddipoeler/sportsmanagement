<?php
/**
 * Joomla 5/6 native project-division field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

final class ProjectdivisionField extends SportsManagementListField
{
    protected $type = 'Projectdivision';

    protected function getOptions(): array
    {
        $app = Factory::getApplication();
        $projectId = $app->getInput()->getInt('pid', 0)
            ?: (int) $app->getUserState('com_sportsmanagement.pid', 0);

        if ($projectId <= 0) {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([$db->quoteName('id', 'value'), $db->quoteName('name', 'text')])
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->order($db->quoteName('name') . ' ASC');
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
