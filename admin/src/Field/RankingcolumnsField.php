<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** Joomla 5/6 ranking-column multi-select. */
final class RankingcolumnsField extends FormField
{
    protected $type = 'rankingcolumns';

    private const COLUMNS = [
        'PLAYED', 'WINS', 'LOSSES', 'TIES', 'WOT', 'WSO', 'LOT', 'LSO',
        'SCOREFOR', 'SCOREAGAINST', 'SCOREPCT', 'RESULTS', 'DIFF', 'POINTS',
        'BONUS', 'START', 'LEGS', 'LEGS_DIFF', 'GB', 'LEGS_RATIO', 'WINPCT',
        'QUOT', 'NEGPOINTS', 'PENALTYPOINTS', 'OLDNEGPOINTS', 'POINTS_RATIO',
        'TADMIN', 'GFA', 'GAA', 'PPG', 'PPP', 'LASTGAMES', 'BALLS', 'BALLS_DIFF',
    ];

    protected function getInput(): string
    {
        $selected = is_array($this->value)
            ? array_values(array_map('strval', $this->value))
            : array_values(array_filter(array_map('trim', explode(',', (string) $this->value))));
        $showAll = (bool) (int) ($this->element['selrankingcol'] ?? 0);
        $columns = $showAll ? self::COLUMNS : $selected;
        $options = [];

        foreach (array_values(array_unique($columns)) as $column) {
            if ($column === '') {
                continue;
            }

            $options[] = HTMLHelper::_(
                'select.option',
                $column,
                Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_' . $column)
            );
        }

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            $this->name,
            'class="form-select" size="10" multiple="multiple"',
            'value',
            'text',
            $selected,
            $this->id
        );
    }
}
