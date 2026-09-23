<?php
/**
 * Native Joomla 5/6 statistics chart layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$this->tips = [Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_STATISTIC')];
echo $this->loadTemplate('jsm_tips');

$labels = array_map(
    static function (mixed $label): string {
        $decoded = json_decode((string) $label, true);

        return is_string($decoded) ? $decoded : (string) $label;
    },
    $this->round_labels
);

$document = $this->getDocument();
$document->addScriptOptions('com_sportsmanagement.stats.goals', [
    'type' => 'bar',
    'data' => [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => Text::_('COM_SPORTSMANAGEMENT_STATS_HOME'),
                'borderColor' => (string) ($this->flashconfig['stats_home_color'] ?? '#000000'),
                'backgroundAlpha' => 0.5,
                'borderWidth' => 1,
                'data' => array_values(array_map('intval', $this->homeSum)),
            ],
            [
                'label' => Text::_('COM_SPORTSMANAGEMENT_STATS_AWAY'),
                'borderColor' => (string) ($this->flashconfig['stats_away_color'] ?? '#666666'),
                'backgroundAlpha' => 0.5,
                'borderWidth' => 1,
                'data' => array_values(array_map('intval', $this->awaySum)),
            ],
            [
                'label' => Text::_('COM_SPORTSMANAGEMENT_STATS_TOTAL'),
                'borderColor' => (string) ($this->flashconfig['stats_overall_color'] ?? '#999999'),
                'backgroundAlpha' => 0.5,
                'borderWidth' => 1,
                'data' => array_values($this->matchDayGoalsCount),
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
    'com_sportsmanagement.chart.renderer',
    'components/com_sportsmanagement/assets/js/chart-renderer.js',
    ['version' => 'auto'],
    ['defer' => true],
    ['core', 'com_sportsmanagement.stats.chartjs']
);
?>
<div class="<?php echo $this->escape($this->divclassrow); ?> table-responsive" id="flashchart">
    <canvas
        id="jsm-stats-goals-chart"
        data-jsm-chart
        data-jsm-chart-options="com_sportsmanagement.stats.goals"
    ></canvas>
</div>
