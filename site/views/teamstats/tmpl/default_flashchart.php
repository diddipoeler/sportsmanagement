<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_flashchart.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teamstats
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<!-- Flash Statistik Start -->
<script	type="text/javascript" src="<?php echo JURI::base().'components/com_sportsmanagement/assets/js/json2.js'; ?>"></script>
<script	type="text/javascript" src="<?php echo JURI::base().'components/com_sportsmanagement/assets/js/swfobject.js'; ?>"></script>
<script type="text/javascript">
	function get_teamstats_chart() {
		var data_teamstats_chart = <?php echo $this->chartdata->toPrettyString(); ?>;
		return JSON.stringify(data_teamstats_chart);
	}
	swfobject.embedSWF("<?php echo JURI::base().'components/com_sportsmanagement/assets/classes/open-flash-chart/open-flash-chart.swf'; ?>", 
			"teamstats_chart", "100%", "200", "9.0.0", false, {"get-data": "get_teamstats_chart"} );
</script>

<table width="100%" cellspacing="0" border="0">
	<tbody>
	<tr class="sectiontableheader">
		<th><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_STATISTIC'); ?></th>
	</tr>
	<tr>
		<td>
			<div id="teamstats_chart"></div>
			<!-- Flash Statistik END -->
		</td>
	</tr>
	</tbody>
</table>
