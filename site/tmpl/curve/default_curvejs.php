<?php
/**
 * Native Joomla 5/6 curve chart layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$chartDivision = $this->divisions[0] ?? null;
$chartDivisionId = (int) ($chartDivision->id ?? 0);
$chartTeams = $this->teamranking[$chartDivisionId] ?? [];
$datasets = [];

foreach ($chartTeams as $team) {
    $teamId = (int) ($team->team_id ?? $team->id ?? 0);
    if ($teamId !== $this->selectedTeamId1 && $teamId !== $this->selectedTeamId2) {
        continue;
    }

    $datasets[] = [
        'label' => (string) ($team->name ?? ''),
        'fill' => false,
        'borderColor' => $teamId === $this->selectedTeamId1
            ? (string) ($this->flashconfig['curve_team1_color'] ?? '#000000')
            : (string) ($this->flashconfig['curve_team2_color'] ?? '#666666'),
        'data' => array_values(array_map('intval', (array) ($team->rankings ?? []))),
    ];
}

$labels = array_map(
    static function (mixed $label): string {
        $decoded = json_decode((string) $label, true);

        return is_string($decoded) ? $decoded : (string) $label;
    },
    $this->round_labels
);

if ($chartTeams !== [] && $datasets !== []) {
    $document = $this->getDocument();
    $document->addScriptOptions('com_sportsmanagement.curve.chart', [
        'type' => 'line',
        'data' => [
            'labels' => $labels,
            'datasets' => $datasets,
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
                        'suggestedMin' => 1,
                        'suggestedMax' => max(1, count($chartTeams)),
                        'beginAtZero' => false,
                        'reverse' => true,
                        'stepSize' => 1,
                    ],
                ]],
            ],
        ],
    ]);
    $document->getWebAssetManager()->registerAndUseScript(
        'com_sportsmanagement.curve.render',
        'components/com_sportsmanagement/assets/js/curve-chart.js',
        ['version' => 'auto'],
        ['defer' => true],
        ['core', 'com_sportsmanagement.curve.chartjs']
    );
}
?>
<div class="<?php echo $this->escape($this->divclassrow); ?> table-responsive" id="curvejs">
    <?php foreach ($this->divisions as $division) : ?>
        <?php if (empty($this->allteams)) { continue; } ?>
        <form
            name="curveform<?php echo (int) $division->id; ?>"
            method="post"
            action="<?php echo $this->escape($this->uri->toString()); ?>"
            id="curveform<?php echo (int) $division->id; ?>"
        >
            <table class="table">
                <tr>
                    <td class="contentheading"><?php echo $this->escape((string) $division->name); ?></td>
                </tr>
                <tr>
                    <td class="text-end">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_CURVE_TEAMS') . ' ' . $this->escape((string) $division->name); ?>
                    </td>
                    <td class="text-end"><?php echo $this->team1select[(int) $division->id] ?? ''; ?></td>
                    <td class="text-end"><?php echo $this->team2select[(int) $division->id] ?? ''; ?></td>
                    <td class="text-end">
                        <input type="hidden" name="option" value="com_sportsmanagement">
                        <input type="hidden" name="view" value="curve">
                        <input type="hidden" name="cfg_which_database" value="<?php echo (int) $this->cfg_which_database; ?>">
                        <input type="hidden" name="s" value="<?php echo (int) $this->season_id; ?>">
                        <input type="hidden" name="p" value="<?php echo (int) $this->project->id; ?>">
                        <input type="hidden" name="division" value="<?php echo (int) $division->id; ?>">
                        <input
                            type="submit"
                            class="<?php echo $this->escape((string) ($this->config['button_style'] ?? 'btn btn-primary')); ?>"
                            value="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_CURVE_GO')); ?>"
                        >
                        <?php echo HTMLHelper::_('form.token'); ?>
                    </td>
                </tr>
            </table>
        </form>
    <?php endforeach; ?>

    <?php if ($chartTeams !== [] && $datasets !== []) : ?>
        <canvas id="jsmchartcurve" data-jsm-curve-chart></canvas>
    <?php endif; ?>
</div>
