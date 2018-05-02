<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_rankflashchart.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionusers
 */

defined('_JEXEC') or die('Restricted access');
?>
<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_USERS_SEASON_RANKS'); ?></h2>

<!-- Flash Statistik Start -->
<script type="text/javascript">
	function ranking_chart() {
		var data_ranking_chart = <?php echo $this->rankingchartdata->toPrettyString(); ?>;
		return JSON.stringify(data_ranking_chart);
	}
	swfobject.embedSWF("<?php echo JURI::base().'components/com_sportsmanagement/assets/classes/open-flash-chart/open-flash-chart.swf'; ?>", 
			"ranking_chart", "100%", "200", "9.0.0", false, {"get-data": "ranking_chart"} );
</script>

<div id="ranking_chart"></div>
<!-- Flash Statistik END -->
