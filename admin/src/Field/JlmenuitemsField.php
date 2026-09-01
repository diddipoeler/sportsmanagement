<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;

final class JlmenuitemsField extends ListField
{
    protected $type = 'JLMenuItems';

    protected function getOptions(): array
    {
        $items = [
            '' => 'JNONE',
            'separator' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_SEPARATOR',
            'calendar' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_CALENDAR',
            'curve' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_CURVE',
            'eventsranking' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_EVENTSRANKING',
            'matrix' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_MATRIX',
            'ranking' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TABLE',
            'referees' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_REFEREES',
            'results' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_RESULTS',
            'resultsmatrix' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_RESULTSMATRIX',
            'resultsranking' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TABLE_AND_RESULTS',
            'roster' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_ROSTER',
            'rosteralltime' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_ROSTERALLTIME',
            'stats' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_STATS',
            'statsranking' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_STATSRANKING',
            'clubinfo' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_CLUBINFO',
            'clubplan' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_CLUBPLAN',
            'teaminfo' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMINFO',
            'teams' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMS',
            'teamstree' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMSTREE',
            'treetonode' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TREETONODE',
            'teamplan' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMPLAN',
            'teamstats' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMSTATS',
            'jltournamenttree' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_JLTOURNAMENTTREE',
            'jlallprojectrounds' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_JLALLPROJECTROUNDS',
            'jlxmlexports' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_XMLEXPORT',
        ];
        $options = [];

        foreach ($items as $value => $label) {
            $options[] = (object) [
                'value' => (string) $value,
                'text' => Text::_($label),
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
