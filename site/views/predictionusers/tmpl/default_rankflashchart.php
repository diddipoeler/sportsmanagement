<?php 
/**
* @copyright	Copyright (C) 2007-2012 JoomLeague.net. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
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
