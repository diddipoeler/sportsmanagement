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

defined( '_JEXEC' ) or die( 'Restricted access' );


?>
<script>
function findSWF(movieName) {
	if (navigator.appName.indexOf("Microsoft")!= -1) {
		return window[movieName];
	} else {
		return document[movieName];
	}
}
</script>
<?php
foreach ($this->divisions as $division)
{
	$chart = 'chartdata_'.$division->id;
	if(empty($this->$chart)) continue;
	if(empty($this->allteams) || count($this->allteams)==0) continue;
		?>
	<table class="table">
	<tr>
		<td class="contentheading"><?php echo $division->name; ?></td>
	</tr>
	<tr>
	<td style="text-align: right">
		<?php echo JText::_('COM_SPORTSMANAGEMENT_CURVE_TEAMS').' '.$division->name; ?>
	</td>	
    <td style="text-align: right">    
        <?php echo $this->team1select[$division->id]; ?>
	</td>
    <td style="text-align: right">	
        <?php echo $this->team2select[$division->id]; ?>
	</td>
    <td style="text-align: right">	
		<form name="curveform<?php echo $division->id; ?>" method="get"	id="curveform<?php echo $division->id; ?>">
			<input type="hidden" name="option" value="com_sportsmanagement" />
			<input type="hidden" name="view" value="curve" />
			<input type="hidden" name="cfg_which_database" value="<?php echo $this->cfg_which_database; ?>" />  
			<input type="hidden" name="s" value="<?php echo $this->season_id; ?>" />  
			<input type="hidden" name="p" value="<?php echo $this->project->id; ?>" />  
			
			
			<input type="hidden" name="tid1" value="" /> 
			<input type="hidden" name="tid2" value="" />  
			<input type="hidden" name="division" value="<?php echo $division->id; ?>" /> 
			<input type="submit" style="" class="<?PHP echo $this->config['button_style']; ?>"
				value="<?php echo JText::_('COM_SPORTSMANAGEMENT_CURVE_GO'); ?>" />
			<?php echo JHtml::_( 'form.token' ); ?>
		</form>
	</td>
	</tr>
	</table>
<script type="text/javascript">
function get_curve_chart_<?php echo $division->id; ?>() {
	var data_curve_chart_<?php echo $division->id; ?> = <?php $chart = 'chartdata_'.$division->id; 
	echo $this->$chart->toString(); ?>;
	return JSON.stringify(data_curve_chart_<?php echo $division->id; ?>);
}
swfobject.embedSWF("<?php echo JUri::base().'components/com_sportsmanagement/assets/classes/open-flash-chart/open-flash-chart.swf'; ?>", 
		"curve_chart_<?php echo $division->id; ?>", "100%", "400", "9.0.0", false, 
		{"loading": "loading <?php echo $division->name; ?>","get-data": "get_curve_chart_<?php echo $division->id; ?>", "wmode" : "transparent"} );

function reload_curve_chart_<?php echo $division->id; ?>() {
	var tmp = findSWF("curve_chart_<?php echo $division->id; ?>");
	var baseurl = '<?php echo JUri::base() ?>/';
	var reloadstring = 'index.php?option=com_sportsmanagement&format=raw&view=curve&p=<?php echo $this->project->slug?>&division=<?php echo $division->id;?>'+
	'&tid1='+document.getElementById('tid1_<?php echo $division->id; ?>').options[document.getElementById('tid1_<?php echo $division->id; ?>').selectedIndex].value+
	'&tid2='+document.getElementById('tid2_<?php echo $division->id; ?>').options[document.getElementById('tid2_<?php echo $division->id; ?>').selectedIndex].value;
	document.forms['curveform<?php echo $division->id; ?>'].tid1.value = document.getElementById('tid1_<?php echo $division->id; ?>').options[document.getElementById('tid1_<?php echo $division->id; ?>').selectedIndex].value;
	document.forms['curveform<?php echo $division->id; ?>'].tid2.value = document.getElementById('tid2_<?php echo $division->id; ?>').options[document.getElementById('tid2_<?php echo $division->id; ?>').selectedIndex].value;
	x = tmp.reload(baseurl+reloadstring);
//    alert(reloadstring);
}
</script>

<div id="curve_chart_<?php echo $division->id; ?>"></div>
<?php 
}
?>
