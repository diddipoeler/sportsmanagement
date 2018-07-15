<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_flashchart.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage stats
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<!-- Flash Statistik Start -->
<script	type="text/javascript" src="<?php echo JURI::base().'components/com_sportsmanagement/assets/js/json2.js'; ?>"></script>
<script	type="text/javascript" src="<?php echo JURI::base().'components/com_sportsmanagement/assets/js/swfobject.js'; ?>"></script>
<script type="text/javascript">
	function get_stats_chart() {
		var data_stats_chart = <?php echo $this->chartdata->toPrettyString(); ?>;
		return JSON.stringify(data_stats_chart);
	}
	swfobject.embedSWF("<?php echo JURI::base().'components/com_sportsmanagement/assets/classes/open-flash-chart/open-flash-chart.swf'; ?>", 
			"stats_chart", "100%", "200", "9.0.0", false, {"get-data": "get_stats_chart"} );
</script>


<table class="<?php echo $this->config['goals_table_class'];?>">
	<tr class="sectiontableheader">
		<th colspan="2"><?php	echo JText::_('COM_SPORTSMANAGEMENT_STATS_GOALS_STATISTIC'); ?></th>
	</tr>
</table>

<div id="stats_chart"></div>
<!-- Flash Statistik END -->

