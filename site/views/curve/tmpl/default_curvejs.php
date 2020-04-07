<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_curvejs.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage curve
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

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
<div class="<?php echo $this->divclassrow;?> table-responsive" id="curvejs">
<?php
foreach ($this->divisions as $division)
{
	if (empty($this->allteams) || count($this->allteams) == 0)
	{
		continue;
	}
	?>
<form name="curveform<?php echo $division->id; ?>" method="post" action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>" id="curveform<?php echo $division->id; ?>">      
	<table class="table">
	<tr>
		<td class="contentheading"><?php echo $division->name; ?></td>
	</tr>
	<tr>
	<td style="text-align: right">
	<?php echo Text::_('COM_SPORTSMANAGEMENT_CURVE_TEAMS') . ' ' . $division->name; ?>
	</td>  
	<td style="text-align: right">  
		<?php echo $this->team1select[$division->id]; ?>
	</td>
	<td style="text-align: right">  
		<?php echo $this->team2select[$division->id]; ?>
	</td>
	<td style="text-align: right">  
			<input type="hidden" name="option" value="com_sportsmanagement" />
			<input type="hidden" name="view" value="curve" />
			<input type="hidden" name="cfg_which_database" value="<?php echo $this->cfg_which_database; ?>" />
			<input type="hidden" name="s" value="<?php echo $this->season_id; ?>" />
			<input type="hidden" name="p" value="<?php echo $this->project->id; ?>" />
			<input type="hidden" name="tid1" value="<?php echo sportsmanagementModelCurve::$teamid1; ?>" />
			<input type="hidden" name="tid2" value="<?php echo sportsmanagementModelCurve::$teamid2; ?>" />
			<input type="hidden" name="division" value="<?php echo $division->id; ?>" />
			<input type="submit" style="" class="<?PHP echo $this->config['button_style']; ?>"
				value="<?php echo Text::_('COM_SPORTSMANAGEMENT_CURVE_GO'); ?>" />
	<?php echo HTMLHelper::_('form.token'); ?>

		  </td>
	</tr>
	</table>
	</form>
<?php
}
?>

<canvas id="jsmchartcurve"></canvas>

<script>
var ctx = document.getElementById('jsmchartcurve').getContext('2d');
var chart = new Chart(ctx, {
	// The type of chart we want to create
	type: 'line',

	// The data for our dataset
	data: {
		labels: [<?php echo implode(',', $this->round_labels); ?>],
<?php
$teamcount = sizeof($this->teamranking[$division->id]);

foreach ($this->teamranking[$division->id] as $key => $value)
{
	if ($value->team_id == sportsmanagementModelCurve::$teamid1)
	{
		?>      
		datasets: [{
			label: "<?php echo $value->name; ?>",
			fill: false,
	  borderColor: '<?php echo $this->flashconfig['curve_team1_color']; ?>',
			data: [<?php echo implode(",", $value->rankings); ?>],
		},
	<?php
	}
}


foreach ($this->teamranking[$division->id] as $key => $value)
{
	if ($value->team_id == sportsmanagementModelCurve::$teamid2)
	{
		?>
		{
			label: "<?php echo $value->name; ?>",
			fill: false,
	 borderColor: '<?php echo $this->flashconfig['curve_team2_color']; ?>',
			data: [<?php echo implode(",", $value->rankings); ?>],
		}
	<?php
	}
}
?>      
	]},

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
suggestedMax: <?php echo $teamcount; ?>,
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
