<?php
/**
 * Native Joomla 5/6 published project selector field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** Joomla 5/6 published project selector. */
final class ProjectField extends SportsManagementListField
{
    protected $type = 'project';

    /**
     * Load published projects with their league and season labels.
     * Keeps the translated placeholder and historical project ordering,
     * accepting absent optional joined names without coercing invalid data.
     * @return array Joomla project selection options.
     */
    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.name'),
                $db->quoteName('l.name', 'league_name'),
                $db->quoteName('s.name', 'season_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->where($db->quoteName('p.published') . ' = 1')
            ->order($db->quoteName('p.ordering') . ' DESC');
        $db->setQuery($query);

        $options = [
            (object) [
                'value' => '',
                'text' => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'),
            ],
        ];

        $rows = $db->loadObjectList();
        foreach (is_array($rows) ? $rows : [] as $project) {
            if (!is_object($project) || (!is_int($project->id ?? null) && !is_string($project->id ?? null))
                || !is_string($project->name ?? null) || !is_string($project->league_name ?? '')
                || !is_string($project->season_name ?? '')) {
                continue;
            }
            $label = (string) $project->name
                . ' (' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LEAGUE') . ': ' . ($project->league_name ?? '') . ')'
                . ' (' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SEASON') . ': ' . ($project->season_name ?? '') . ' )';
            $options[] = (object) [
                'value' => (string) $project->id,
                'text' => $label,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
