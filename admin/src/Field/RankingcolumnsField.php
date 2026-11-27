<?php
/**
 * Native Joomla 5/6 ranking columns field.
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

/** Joomla 5/6 ranking-column multi-select. */
final class RankingcolumnsField extends ListField
{
    protected $type = 'rankingcolumns';

    private const COLUMNS = [
        'PLAYED', 'WINS', 'LOSSES', 'TIES', 'WOT', 'WSO', 'LOT', 'LSO',
        'SCOREFOR', 'SCOREAGAINST', 'SCOREPCT', 'RESULTS', 'DIFF', 'POINTS',
        'BONUS', 'START', 'LEGS', 'LEGS_DIFF', 'GB', 'LEGS_RATIO', 'WINPCT',
        'QUOT', 'NEGPOINTS', 'PENALTYPOINTS', 'OLDNEGPOINTS', 'POINTS_RATIO',
        'TADMIN', 'GFA', 'GAA', 'PPG', 'PPP', 'LASTGAMES', 'BALLS', 'BALLS_DIFF',
    ];

    /**
     * Initialise the ordered ranking column multi-select.
     * Accepts historical comma-separated strings and arrays of column names,
     * removes malformed entries and delegates field setup to Joomla.
     * @param \SimpleXMLElement $element Field definition.
     * @param mixed $value Stored column selection.
     * @param string|null $group Optional form group.
     * @return bool Whether Joomla accepted setup.
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $value = self::normaliseColumns($value);

        $element['multiple'] = 'true';
        $element['size'] = '10';
        $element['class'] = 'form-select';

        return parent::setup($element, $value, $group);
    }

    /**
     * Build translated column options for the configured selector mode.
     * Preserves the order of stored custom column names when showing only
     * selected columns, or uses the complete built-in column catalogue.
     * @return array<int,object> Ranking column options.
     */
    protected function getOptions(): array
    {
        $selected = self::normaliseColumns($this->value);
        $showAll = (bool) (int) ($this->element['selrankingcol'] ?? 0);
        $columns = $showAll ? self::COLUMNS : $selected;
        $options = [];

        foreach (array_values(array_unique($columns)) as $column) {
            if ($column === '') {
                continue;
            }

            $options[] = (object) [
                'value' => $column,
                'text' => Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_' . $column),
            ];
        }

        return $options;
    }
    /**
     * Normalise legacy column selections without coercing nested values.
     * Keeps nonempty string identifiers in their original order.
     * @param mixed $value Comma-separated string or saved array.
     * @return array<int,string> Valid column identifiers.
     */
    private static function normaliseColumns(mixed $value): array
    {
        $columns = is_string($value) ? explode(',', $value) : (is_array($value) ? $value : []);
        $result = [];
        foreach ($columns as $column) {
            if (is_string($column) && trim($column) !== '') {
                $result[] = trim($column);
            }
        }

        return $result;
    }
}
