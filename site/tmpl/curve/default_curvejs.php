<?php
/** SportsManagement curve chart for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
<script>
    window.chartColors = {
        red: 'rgb(255, 99, 132)',
        orange: 'rgb(255, 159, 64)',
        yellow: 'rgb(255, 205, 86)',
        green: 'rgb(75, 192, 192)',
        blue: 'rgb(54, 162, 235)',
        purple: 'rgb(153, 102, 255)',
        grey: 'rgb(201, 203, 207)'
    };
</script>
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

    <?php
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

    $chartConfig = json_encode(
        [
            'labels' => array_map(
                static fn (string $label): mixed => json_decode($label, true),
                $this->round_labels
            ),
            'datasets' => $datasets,
            'teamCount' => count($chartTeams),
        ],
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
    );
    ?>
    <?php if ($chartConfig !== false && $chartTeams !== []) : ?>
        <canvas id="jsmchartcurve"></canvas>
        <script>
            (() => {
                const curveData = <?php echo $chartConfig; ?>;
                const canvas = document.getElementById('jsmchartcurve');
                if (!canvas || typeof Chart === 'undefined') return;

                new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: curveData.labels,
                        datasets: curveData.datasets
                    },
                    options: {
                        responsive: true,
                        legend: {
                            display: true,
                            labels: {padding: 20}
                        },
                        tooltips: {enabled: true},
                        scales: {
                            yAxes: [{
                                ticks: {
                                    suggestedMin: 1,
                                    suggestedMax: curveData.teamCount,
                                    beginAtZero: false,
                                    reverse: true,
                                    stepSize: 1,
                                    callback: function (value) {
                                        return value == 0 ? '' : value * 1;
                                    }
                                }
                            }]
                        }
                    }
                });
            })();
        </script>
    <?php endif; ?>
</div>
