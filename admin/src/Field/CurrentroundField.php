<?php
/**
 * Native Joomla 5/6 current round form field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

final class CurrentroundField extends SportsManagementListField
{
    protected $type = 'Currentround';

    /**
     * List the current project's rounds for the administrator selector.
     *
     * Reads the project ID from Joomla's administrator application, binds
     * it to the round query and orders available rounds by roundcode and
     * opening date. Missing or invalid IDs keep inherited field options
     * without triggering a database query.
     *
     * @return array<int,object> Date-labelled round choices and Joomla defaults.
     */
    protected function getOptions(): array
    {
        $projectId = SportsManagementAdministratorApplicationResolver::resolve()->getInput()->getInt('id', 0);

        if ($projectId <= 0) {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('round_date_first'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->order([
                $db->quoteName('roundcode'),
                $db->quoteName('round_date_first'),
            ]);
        $db->setQuery($query);

        $options = [];

        $rows = $db->loadObjectList();
        foreach (is_array($rows) ? $rows : [] as $item) {
            if (!is_object($item) || (!is_int($item->id ?? null) && !is_string($item->id ?? null))
                || !is_string($item->name ?? '') || !is_string($item->round_date_first ?? '')) {
                continue;
            }
            $name = trim($item->name ?? '');
            $label = $name !== ''
                ? $name . ' (' . ($item->round_date_first ?? '') . ')'
                : Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME') . ' ' . (int) $item->id;
            $options[] = (object) [
                'value' => (string) $item->id,
                'text' => $label,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
