<?php
/**
 * Joomla 5/6 native project team list field.
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

final class ProjectteamlistField extends SportsManagementListField
{
    protected $type = 'projectteamlist';

    /**
     * Load teams belonging to the currently selected administrator project.
     * Resolves the saved project identifier, joins season and project team
     * records and preserves XML options when no valid project is selected.
     * @return array Joomla selection options.
     */
    protected function getOptions(): array
    {
        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');
        $rawProjectId = $app->getUserState($option . '.pid', 0);
        $projectId = (is_int($rawProjectId) || is_string($rawProjectId))
            ? filter_var($rawProjectId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : false;

        if ($projectId <= 0) {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pt.id', 'value'),
                $db->quoteName('t.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('t.name'));
        $db->setQuery($query);

        $options = [];

        $rows = $db->loadObjectList();
        foreach (is_array($rows) ? $rows : [] as $item) {
            if (!is_object($item)
                || (!is_int($item->value ?? null) && !is_string($item->value ?? null))
                || !is_string($item->text ?? null)) {
                continue;
            }

            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => (string) $item->text,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
