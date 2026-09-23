<?php
/**
 * Native Joomla 5/6 team statistics chart layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$labels = array_map(
    static function (mixed $label): string {
        $decoded = json_decode((string) $label, true);

        return is_string($decoded) ? $decoded : (string) $label;
    },
    $this->round_labels
);

$document = $this->getDocument();
$document->addScriptOptions('com_sportsmanagement.teamstats.goals', [
    'type' => 'bar',
    'data' => [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_FOR'),
                'borderColor' => (string) ($this->flashconfig['teamstats_goalshome_color'] ?? '#000000'),
                'backgroundAlpha' => 0.5,
                'borderWidth' => 1,
                'data' => array_values(array_map('intval', $this->forSum)),
            ],
            [
                'label' => Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_AGAINST'),
                'borderColor' => (string) ($this->flashconfig['teamstats_goalsaway_color'] ?? '#666666'),
                'backgroundAlpha' => 0.5,
                'borderWidth' => 1,
                'data' => array_values(array_map('intval', $this->againstSum)),
            ],
        ],
    ],
    'options' => [
        'responsive' => true,
        'legend' => [
            'display' => true,
            'labels' => ['padding' => 20],
        ],
        'tooltips' => ['enabled' => true],
        'scales' => [
            'yAxes' => [[
                'ticks' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => max(1, (int) $this->matchDayGoalsCountMax),
                    'beginAtZero' => true,
                    'stepSize' => 1,
                ],
            ]],
        ],
    ],
]);
$document->getWebAssetManager()->registerAndUseScript(
    'com_sportsmanagement.teamstats.chart.renderer',
    'components/com_sportsmanagement/assets/js/chart-renderer.js',
    ['version' => 'auto'],
    ['defer' => true],
    ['core', 'com_sportsmanagement.teamstats.chartjs']
);
?>
<div class="<?php echo $this->escape($this->divclassrow); ?> table-responsive" id="flashchart">
    <h4><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_STATISTIC'); ?></h4>
    <canvas
        id="jsm-teamstats-goals-chart"
        data-jsm-chart
        data-jsm-chart-options="com_sportsmanagement.teamstats.goals"
    ></canvas>
</div>
