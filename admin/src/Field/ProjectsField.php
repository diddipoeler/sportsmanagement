<?php
/**
 * Joomla 5/6 administrator project selector field.
 *
 * @version   5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

final class ProjectsField extends SportsManagementListField
{
    protected $type = 'projects';

    protected function getOptions(): array
    {
        $language = Factory::getApplication()->getLanguage();
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $language->getTag(), true);

        $valueField = (string) ($this->element['value_field'] ?? $this->name);
        $context = 'request';
        $value = $this->form->getValue($valueField, $context);

        if (!$value) {
            $context = 'params';
            $value = $this->form->getValue($valueField, $context);
        }

        $whichDatabase = $this->form->getValue('cfg_which_database', $context);
        $db = $this->getSportsManagementDatabase($whichDatabase);
        $tablePrefix = preg_replace(
            '/[^A-Za-z0-9_]/',
            '',
            (string) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database_table', 'sportsmanagement')
        );

        if ($tablePrefix === '') {
            $tablePrefix = 'sportsmanagement';
        }

        $projectTable = '#__' . $tablePrefix . '_project';
        $seasonTable = '#__' . $tablePrefix . '_season';
        $leagueTable = '#__' . $tablePrefix . '_league';
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.name'),
                $db->quoteName('l.name', 'league_name'),
                $db->quoteName('s.name', 'season_name'),
            ])
            ->from($db->quoteName($projectTable, 'p'))
            ->join(
                'LEFT',
                $db->quoteName($seasonTable, 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id')
            )
            ->join(
                'LEFT',
                $db->quoteName($leagueTable, 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id')
            )
            ->where($db->quoteName('p.published') . ' = 1')
            ->order($db->quoteName('p.id') . ' DESC');
        $db->setQuery($query);

        $options = [
            (object) [
                'value' => '0',
                'text' => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'),
            ],
        ];

        foreach ($db->loadObjectList() ?: [] as $project) {
            $label = (string) $project->name
                . ' (' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LEAGUE') . ': ' . (string) $project->league_name . ')'
                . ' (' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SEASON') . ': ' . (string) $project->season_name . ' )';
            $options[] = (object) [
                'value' => (string) $project->id,
                'text' => "\u{00A0}\u{00A0}\u{00A0}" . $label,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
