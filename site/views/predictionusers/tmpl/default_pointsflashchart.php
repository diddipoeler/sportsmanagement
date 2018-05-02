<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_pointsflashcharts.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionusers
 */

defined('_JEXEC') or die('Restricted access');
?>
<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_USERS_SEASON_POINTS'); ?></h2>

<!-- Flash Statistik Start -->
<script type="text/javascript">
	function points_chart() {
		var data_points_chart = <?php echo $this->pointschartdata->toPrettyString(); ?>;
		return JSON.stringify(data_points_chart);
	}
	swfobject.embedSWF("<?php echo JURI::base().'components/com_sportsmanagement/assets/classes/open-flash-chart/open-flash-chart.swf'; ?>", 
			"points_chart", "100%", "200", "9.0.0", false, {"get-data": "points_chart"} );
</script>

<div id="points_chart"></div>
<!-- Flash Statistik END -->