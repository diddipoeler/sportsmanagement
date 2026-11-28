<?php
/**
 * Joomla 5/6 native project position list field.
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
use Joomla\CMS\Language\Text;

final class ProjectpositionlistField extends SportsManagementListField
{
    protected $type = 'projectpositionlist';

    /** Native Joomla searchable list layout; retains the original option values. */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Load project-specific positions using the saved project and person type.
     * Validates both filters, binds them to the native query and retains
     * translated position names and inherited XML options.
     * @return array Joomla selection options.
     */
    protected function getOptions(): array
    {
        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');
        $rawProjectId = $app->getUserState($option . '.pid', 0);
        $projectId = (is_int($rawProjectId) || is_string($rawProjectId))
            ? filter_var($rawProjectId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
        $personType = $app->getUserState($option . '.persontype', 0);

        if ($projectId <= 0) {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pp.id', 'value'),
                $db->quoteName('pos.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_position', 'pp')
                . ' ON ' . $db->quoteName('pp.position_id') . ' = ' . $db->quoteName('pos.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_sports_type', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('pos.sports_type_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_person_project_position', 'ppp')
                . ' ON ' . $db->quoteName('pp.project_id') . ' = ' . $db->quoteName('ppp.project_id')
            )
            ->where($db->quoteName('pp.project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        if ($personType !== null && $personType !== '') {
            $personType = (is_int($personType) || is_string($personType))
                ? filter_var($personType, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) : false;
            if ($personType === false) {
                return parent::getOptions();
            }
            $query->where($db->quoteName('pos.persontype') . ' = :personType')
                ->bind(':personType', $personType, ParameterType::INTEGER);
        }

        $query->group([
            $db->quoteName('pp.id'),
            $db->quoteName('pos.name'),
            $db->quoteName('pos.ordering'),
        ])->order([
            $db->quoteName('pos.ordering'),
            $db->quoteName('pos.name'),
        ]);
        $db->setQuery($query);

        try {
            $items = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $app->enqueueMessage('SportsManagement project positions could not be loaded.', 'warning');
            $items = [];
        }

        $options = [];

        foreach (is_array($items) ? $items : [] as $item) {
            if (!is_object($item)
                || (!is_int($item->value ?? null) && !is_string($item->value ?? null))
                || !is_string($item->text ?? null)) {
                continue;
            }

            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => Text::_((string) $item->text),
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
