<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage stats
 * @file       default_flashchart.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

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
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="flashchart">
    <h4>
		<?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_STATISTIC'); ?>
    </h4>
    <canvas id="jsmchartcurve"></canvas>
    <script>
        var ctx = document.getElementById('jsmchartcurve').getContext('2d');
        var color = Chart.helpers.color;
        var chart = new Chart(ctx, {
            // The type of chart we want to create
            type: 'bar',

            // The data for our dataset
            data: {
                labels: [<?php echo implode(',', $this->round_labels); ?>],

                datasets: [{
                    label: '<?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_HOME'); ?>',
                    backgroundColor: color('<?php echo $this->flashconfig['stats_home_color']; ?>').alpha(0.5).rgbString(),
                    borderColor: '<?php echo $this->flashconfig['stats_home_color']; ?>',
                    borderWidth: 1,
                    data: [<?php echo implode(',', $this->homeSum); ?>
                    ]
                }, {
                    label: '<?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_AWAY'); ?>',
                    backgroundColor: color('<?php echo $this->flashconfig['stats_away_color']; ?>').alpha(0.5).rgbString(),
                    borderColor: '<?php echo $this->flashconfig['stats_away_color']; ?>',
                    borderWidth: 1,
                    data: [<?php echo implode(',', $this->awaySum); ?>
                    ]
                }, {
                    label: '<?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_TOTAL'); ?>',
                    backgroundColor: color('<?php echo $this->flashconfig['stats_overall_color']; ?>').alpha(0.5).rgbString(),
                    borderColor: '<?php echo $this->flashconfig['stats_overall_color']; ?>',
                    borderWidth: 1,
                    data: [<?php echo implode(',', $this->matchDayGoalsCount); ?>
                    ]
                }

                ]
            },

            // Configuration options go here
            options: {
                responsive: true,
                legend: {
                    display: true,
                    labels: {
                        padding: 20
                    },
                },
                tooltips: {
                    enabled: true,
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            suggestedMin: 0,
                            suggestedMax: <?php echo $this->matchDayGoalsCountMax; ?>,
                            beginAtZero: false,
                            reverse: false,
                            stepSize: 1,
                            callback: function (value) {
                                if (value == 0) {
                                    return "";
                                } else {
                                    value = value * 1;
                                    return value;
                                }
                            }
                        }
                    }]
                }
            }
        });

    </script>
</div>
