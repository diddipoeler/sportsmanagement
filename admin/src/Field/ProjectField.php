<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** Joomla 5/6 published project selector. */
final class ProjectField extends SportsManagementListField
{
    protected $type = 'project';

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
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

        foreach ($db->loadObjectList() ?: [] as $project) {
            $label = (string) $project->name
                . ' (' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LEAGUE') . ': ' . (string) $project->league_name . ')'
                . ' (' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SEASON') . ': ' . (string) $project->season_name . ' )';
            $options[] = (object) [
                'value' => (string) $project->id,
                'text' => $label,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
