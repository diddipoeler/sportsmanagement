<?php
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

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        if (!is_array($value)) {
            $value = array_values(array_filter(array_map('trim', explode(',', (string) $value))));
        } else {
            $value = array_values(array_map('strval', $value));
        }

        $element['multiple'] = 'true';
        $element['size'] = '10';
        $element['class'] = 'form-select';

        return parent::setup($element, $value, $group);
    }

    protected function getOptions(): array
    {
        $selected = is_array($this->value)
            ? array_values(array_map('strval', $this->value))
            : [];
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
}
