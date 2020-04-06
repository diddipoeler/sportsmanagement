<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_rankflashchart.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage predictionusers
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
<h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_SEASON_RANKS'); ?></h2>
<div class="<?php echo $this->divclassrow;?> table-responsive" id="rankflashchart">
<canvas id="jsmrankflashchart"></canvas>
<script>
var ctx = document.getElementById('jsmrankflashchart').getContext('2d');
var color = Chart.helpers.color;
var chart = new Chart(ctx, {
    // The type of chart we want to create
    type: 'line',

    // The data for our dataset
    data: {
        labels: [<?php echo implode(',', $this->round_labels); ?>],

datasets: [{
                label: '<?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK'); ?>',
                backgroundColor: color(window.chartColors.blue).alpha(0.5).rgbString(),
                borderColor: window.chartColors.blue,
                borderWidth: 1,
                data: [<?php echo implode(',', $this->userranking); ?>
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
suggestedMin: 1,   
suggestedMax: <?php echo $this->RankingCountMax; ?>, 
beginAtZero:false,
reverse: true,
stepSize:1,
callback: function(value) {if (value == 0) {return "";} else {value = value * 1; return value;}}
}
}]
}
    }
});

</script>
</div>
