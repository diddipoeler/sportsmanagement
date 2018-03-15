<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
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

