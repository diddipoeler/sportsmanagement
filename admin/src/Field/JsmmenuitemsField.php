<?php
/**
 * Native Joomla 5/6 list field for SportsManagement navigation menu items.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;

final class JsmmenuitemsField extends ListField
{
    protected $type = 'JSMMenuItems';

    /**
     * Build the Joomla 5/6 form options for SportsManagement navigation links.
     *
     * Preserves the historical stored view identifiers and translates their
     * labels through Joomla's language service, so an existing administrator
     * module configuration remains selectable after the native MVC migration.
     * Combines the predefined targets with parent XML field options; the site
     * navigation helper decides whether a target needs a selected project or
     * team before rendering its link. Newly supported native views include
     * the all-rounds, all-time roster, team tree, statistics and tournament
     * destinations that were previously missing from module routing.
     *
     * @return array<int,object> Joomla list entries with translated text and stored view values.
     */
    protected function getOptions(): array
    {
        $items = [
            '' => 'JNONE',
            'separator' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_SEPARATOR',
            'calendar' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_CALENDAR',
            'curve' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_CURVE',
            'eventsranking' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_EVENTSRANKING',
            'matrix' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_MATRIX',
            'rankingmatrix' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_RANKINGMATRIX',
            'ranking' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TABLE',
            'referees' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_REFEREES',
            'results' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_RESULTS',
            'resultsmatrix' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_RESULTSMATRIX',
            'resultsranking' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TABLE_AND_RESULTS',
            'roster' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_ROSTER',
            'rosteralltime' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_ROSTERALLTIME',
            'stats' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_STATS',
            'statsranking' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_STATSRANKING',
            'statsrankingteams' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_STATSRANKING_TEAMS',
            'clubinfo' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_CLUBINFO',
            'clubplan' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_CLUBPLAN',
            'teaminfo' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMINFO',
            'teams' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMS',
            'teamstree' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMSTREE',
            'teamplan' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMPLAN',
            'teamstats' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TEAMSTATS',
            'jltournamenttree' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_JLTOURNAMENTTREE',
            'treetonode' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_TREETONODE',
            'allprojectrounds' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_JLALLPROJECTROUNDS',
            'jlxmlexports' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_XMLEXPORT',
            'leaguechampionoverview' => 'MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_LEAGUECHAMPIONOVERVIEW',
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
