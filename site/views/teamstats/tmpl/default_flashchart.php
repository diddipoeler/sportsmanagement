<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_flashchart.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage teamstats
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
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
<div class="<?php echo $this->divclassrow;?> table-responsive" id="flashchart">
<canvas id="jsmchartcurve"></canvas>
<script>
var ctx = document.getElementById('jsmchartcurve').getContext('2d');
var chart = new Chart(ctx, {
    // The type of chart we want to create
    type: 'line',

    // The data for our dataset
    data: {
        labels: [<?php echo implode(',', $this->round_labels); ?>],



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
suggestedMax: <?php echo $this->matchDayGoalsCountMax; ?>, 
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