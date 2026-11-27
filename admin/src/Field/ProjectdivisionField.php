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

use Joomla\Database\ParameterType;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;

final class ProjectdivisionField extends SportsManagementListField
{
    protected $type = 'Projectdivision';

    /**
     * Load divisions belonging to the active project, using request or saved state.
     * Rejects invalid project identifiers before querying and binds the selected
     * project to the native query, preserving inherited XML options.
     * @return array Joomla selection options.
     */
    protected function getOptions(): array
    {
        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $rawId = $app->getInput()->getInt('pid', 0) ?: $app->getUserState('com_sportsmanagement.pid', 0);
        $projectId = (is_int($rawId) || is_string($rawId))
            ? filter_var($rawId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;

        if ($projectId <= 0) {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([$db->quoteName('id', 'value'), $db->quoteName('name', 'text')])
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
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
