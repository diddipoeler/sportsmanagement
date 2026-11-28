<?php
/**
 * Joomla 5/6 native project referee position field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\Database\ParameterType;

final class ProjectpositionrefereeField extends SportsManagementListField
{
    protected $type = 'projectpositionreferee';

    /** Native Joomla searchable list layout; retains the original option values. */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Load referee positions assigned to the active project.
     * Validates and binds the saved project identifier and limits the query
     * to referee person types, retaining inherited XML selection options.
     * @return array<int,object> Project referee position options.
     */
    protected function getOptions(): array
    {
        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');
        $rawId = $app->getUserState($option . '.pid', 0);
        $projectId = (is_int($rawId) || is_string($rawId))
            ? filter_var($rawId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;

        if ($projectId <= 0) {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('ppos.id', 'value'),
                $db->quoteName('pos.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id')
            )
            ->where($db->quoteName('ppos.project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->where($db->quoteName('pos.persontype') . ' = 3');
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
