<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package		GCalendar
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2007 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;

$params = $this->params;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />

<link rel='stylesheet' type='text/css' href='<?php echo JURI::base()?>components/com_sportsmanagement/libraries/fullcalendar/fullcalendar.css' />
<link rel='stylesheet' type='text/css' href='<?php echo JURI::base()?>components/com_sportsmanagement/libraries/fullcalendar/fullcalendar.print.css' media='print' />
<link rel='stylesheet' type='text/css' href='<?php echo JURI::base()?>components/com_sportsmanagement/views/gcalendar/tmpl/gcalendar.css' />

<?php
$theme = $params->get('theme', '');
if(!empty($theme) && $theme > 0){?>
	<link rel='stylesheet' type='text/css' href='<?php echo JURI::base()?>components/com_sportsmanagement/libraries/jquery/themes/<?php echo $theme?>/jquery-ui.custom.css' />
<?php }else{?>
	<link rel='stylesheet' type='text/css' href='<?php echo JURI::base()?>components/com_sportsmanagement/libraries/jquery/themes/aristo/jquery-ui.custom.css' />
<?php }?>
<style type='text/css'>
body {
	text-align: center;
	font-size: 14px;
	font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
	-webkit-print-color-adjust:exact;
}
#gcalendar_component, #gcalendar_view_list {
	width: 900px;
	margin: 0 auto;
}
</style>

<script type='text/javascript' src='<?php echo JURI::base()?>components/com_sportsmanagement/libraries/jquery/jquery.min.js'></script>
<script type='text/javascript' src='<?php echo JURI::base()?>components/com_sportsmanagement/libraries/jquery/gcalendar/gcNoConflict.js'></script>
<script type='text/javascript' src='<?php echo JURI::base()?>components/com_sportsmanagement/views/gcalendar/tmpl/gcalendar.js'></script>
<script type='text/javascript' src='<?php echo JURI::base()?>components/com_sportsmanagement/libraries/fullcalendar/fullcalendar.min.js'></script>
<script type='text/javascript' src='<?php echo JURI::base()?>components/com_sportsmanagement/libraries/jquery/ui/jquery-ui.custom.min.js'></script>
<script type='text/javascript' src='<?php echo JURI::base()?>components/com_gcalendar/libraries/jquery/gcalendar/jquery.gcalendar-all.min.js'></script>

<style type='text/css'>
<?php
$calendarids = $this->calendarids;
$allCalendars = GCalendarDBUtil::getAllCalendars();

$calsSources = "		eventSources: [\n";
foreach($allCalendars as $calendar) {
	$cssClass = "gcal-event_gccal_".$calendar->id;
	$color = jsmGCalendarUtil::getFadedColor($calendar->color);
	echo ".".$cssClass.",.fc-agenda ".$cssClass." .fc-event-time, .".$cssClass." a, .".$cssClass." div{background-color: ".$color." !important; border-color: #".$calendar->color."; color: white;}";
	if(empty($calendarids) || in_array($calendar->id, $calendarids)){
		$value = html_entity_decode(JRoute::_('index.php?option=com_sportsmanagement&view=jsonfeed&format=raw&gcid='.$calendar->id.'&Itemid='.JFactory::getApplication()->input->getInt('Itemid')));
		$calsSources .= "				'".$value."',\n";
	}
}
$calsSources = trim($calsSources, ",\n");
$calsSources .= "	],\n";
?>
</style>
<?php

$defaultView = $params->get('defaultView', 'month');
if ($params->get('defaultView', 'month') == 'week') {
	$defaultView = 'agendaWeek';
} else if ($params->get('defaultView', 'month') == 'day') {
	$defaultView = 'agendaDay';
}
$daysLong = "[";
$daysShort = "[";
$daysMin = "[";
$monthsLong = "[";
$monthsShort = "[";
for ($i=0; $i<7; $i++) {
	$daysLong .= "'".htmlspecialchars(jsmGCalendarUtil::dayToString($i, false), ENT_QUOTES)."'";
	$daysShort .= "'".htmlspecialchars(jsmGCalendarUtil::dayToString($i, true), ENT_QUOTES)."'";
	$daysMin .= "'".htmlspecialchars(mb_substr(jsmGCalendarUtil::dayToString($i, true), 0, 2), ENT_QUOTES)."'";
	if($i < 6){
		$daysLong .= ",";
		$daysShort .= ",";
		$daysMin .= ",";
	}
}
for ($i=1; $i<=12; $i++) {
	$monthsLong .= "'".htmlspecialchars(jsmGCalendarUtil::monthToString($i, false), ENT_QUOTES)."'";
	$monthsShort .= "'".htmlspecialchars(jsmGCalendarUtil::monthToString($i, true), ENT_QUOTES)."'";
	if($i < 12){
		$monthsLong .= ",";
		$monthsShort .= ",";
	}
}
$daysLong .= "]";
$daysShort .= "]";
$daysMin .= "]";
$monthsLong .= "]";
$monthsShort .= "]";

$calCode = "// <![CDATA[ \n";
$calCode .= "gcjQuery(document).ready(function(){\n";
$calCode .= "	var today = new Date();\n";
$calCode .= "	var tmpYear = today.getFullYear();\n";
$calCode .= "	var tmpMonth = today.getMonth();\n";
$calCode .= "	var tmpDay = today.getDate();\n";
$calCode .= "	var tmpView = '".$defaultView."';\n";
$calCode .= "	var vars = window.location.hash.replace(/&amp;/gi, \"&\").split(\"&\");\n";
$calCode .= "	for ( var i = 0; i < vars.length; i++ ){\n";
$calCode .= "		if(vars[i].match(\"^#year\"))tmpYear = vars[i].substring(6);\n";
$calCode .= "		if(vars[i].match(\"^month\"))tmpMonth = vars[i].substring(6)-1;\n";
$calCode .= "		if(vars[i].match(\"^day\"))tmpDay = vars[i].substring(4);\n";
$calCode .= "		if(vars[i].match(\"^view\"))tmpView = vars[i].substring(5);\n";
$calCode .= "	}\n";
$calCode .= "	gcjQuery('#gcalendar_component').fullCalendar({\n";
$calCode .= "		header: {\n";
$calCode .= "			left: '',\n";
$calCode .= "			center: 'title',\n";
$calCode .= "			right: ''\n";
$calCode .= "		},\n";
$calCode .= "		year: tmpYear,\n";
$calCode .= "		month: tmpMonth,\n";
$calCode .= "		date: tmpDay,\n";
$calCode .= "		defaultView: tmpView,\n";
$calCode .= "		weekMode: 'liquid',\n";
$calCode .= "		theme: ".(!empty($theme) && $theme > 0?'true':'false').",\n";
$calCode .= "		weekends: ".($params->get('weekend', 1)==1?'true':'false').",\n";
$calCode .= "		titleFormat: { \n";
$calCode .= "			month: '".jsmFullcalendar::convertFromPHPDate($params->get('titleformat_month', 'F Y'))."',\n";
$calCode .= "			week: \"".jsmFullcalendar::convertFromPHPDate($params->get('titleformat_week', "M j[ Y]{ '&#8212;'[ M] j o}"))."\",\n";
$calCode .= "			day: '".jsmFullcalendar::convertFromPHPDate($params->get('titleformat_day', 'l, M j, Y'))."',\n";
$calCode .= "			list: '".jsmFullcalendar::convertFromPHPDate($params->get('titleformat_list', 'M j Y'))."'},\n";
$calCode .= "		firstDay: ".$params->get('weekstart', 0).",\n";
$calCode .= "		firstHour: ".$params->get('first_hour', 6).",\n";
$calCode .= "		maxTime: ".$params->get('max_time', 24).",\n";
$calCode .= "		minTime: ".$params->get('min_time', 0).",\n";
$calCode .= "		weekNumbers: ".($params->get('weeknumbers', 1)==1?'true':'false').",\n";
$calCode .= "		monthNames: ".$monthsLong.",\n";
$calCode .= "		monthNamesShort: ".$monthsShort.",\n";
$calCode .= "		dayNames: ".$daysLong.",\n";
$calCode .= "		dayNamesShort: ".$daysShort.",\n";
if($params->get('calendar_height', 0) > 0){
	$calCode .= "		contentHeight: ".$params->get('calendar_height', 0).",\n";
}
$calCode .= "		dayNamesShort: ".$daysShort.",\n";
$calCode .= "		timeFormat: { \n";
$calCode .= "			month: '".jsmFullcalendar::convertFromPHPDate($params->get('timeformat_month', 'g:i a{ - g:i a}'))."',\n";
$calCode .= "			week: \"".jsmFullcalendar::convertFromPHPDate($params->get('timeformat_week', "g:i a{ - g:i a}"))."\",\n";
$calCode .= "			day: '".jsmFullcalendar::convertFromPHPDate($params->get('timeformat_day', 'g:i a{ - g:i a}'))."',\n";
$calCode .= "			list: '".jsmFullcalendar::convertFromPHPDate($params->get('timeformat_list', 'g:i a{ - g:i a}'))."'},\n";
$calCode .= "		columnFormat: { month: 'ddd', week: 'ddd d', day: 'dddd d'},\n";
$calCode .= "		axisFormat: '".jsmFullcalendar::convertFromPHPDate($params->get('axisformat', 'g:i a'))."',\n";
$calCode .= "		allDayText: '".htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_ALL_DAY'), ENT_QUOTES)."',\n";
$calCode .= "			buttonText: {\n";
$calCode .= "			prev:     '&nbsp;&#9668;&nbsp;',\n";  // left triangle
$calCode .= "			next:     '&nbsp;&#9658;&nbsp;',\n";  // right triangle
$calCode .= "			prevYear: '&nbsp;&lt;&lt;&nbsp;',\n"; // <<
$calCode .= "			nextYear: '&nbsp;&gt;&gt;&nbsp;',\n"; // >>
$calCode .= "			today:    '".htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_TOOLBAR_TODAY'), ENT_QUOTES)."',\n";
$calCode .= "			month:    '".htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_MONTH'), ENT_QUOTES)."',\n";
$calCode .= "			week:     '".htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_WEEK'), ENT_QUOTES)."',\n";
$calCode .= "			day:      '".htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_DAY'), ENT_QUOTES)."',\n";
$calCode .= "			list:     '".htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_LIST'), ENT_QUOTES)."'\n";
$calCode .= "		},\n";
$calCode .= "		listSections: 'smart',\n";
$calCode .= "		listRange: 30,\n";
$calCode .= "		listPage: 30,\n";
$calCode .= "		listTexts: {
						until: '".htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_UNTIL'), ENT_QUOTES)."',
						past: '".htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_PAST'), ENT_QUOTES)."',
						today: '".htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_TODAY'), ENT_QUOTES)."',
						tomorrow: '".htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_TOMORROW'), ENT_QUOTES)."',
						thisWeek: '".htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_THIS_WEEK'), ENT_QUOTES)."',
						nextWeek: '".htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_NEXT_WEEK'), ENT_QUOTES)."',
						thisMonth: '".htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_THIS_MONTH'), ENT_QUOTES)."',
						nextMonth: '".htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_NEXT_MONTH'), ENT_QUOTES)."',
						future: '".htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_FUTURE'), ENT_QUOTES)."',
						week: '".htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_WEEK'), ENT_QUOTES)."'
					},\n";
$calCode .= $calsSources;
$calCode .= "		viewRender: function(view) {\n";
$calCode .= "			var d = gcjQuery('#gcalendar_component').fullCalendar('getDate');\n";
$calCode .= "			var newHash = 'year='+d.getFullYear()+'&month='+(d.getMonth()+1)+'&day='+d.getDate()+'&view='+view.name;\n";
$calCode .= "			if(window.location.hash.replace(/&amp;/gi, \"&\") != newHash)\n";
$calCode .= "			window.location.hash = newHash;\n";
$calCode .= "		},\n";
$calCode .= "		eventRender: function(event, element) {\n";
$calCode .= "		},\n";
$calCode .= "		eventClick: function(event, jsEvent, view) {\n";
$calCode .= "		        return false;\n";
$calCode .= "		},\n";

$calCode .= "		dayClick: function(date, allDay, jsEvent, view) {\n";
$calCode .= "		},\n";
$calCode .= "	});\n";
if($params->get('show_selection', 1) == 1) {
	$calCode .= "gcjQuery('#gc_gcalendar_view_list').hide();\n";
}
$calCode .= "});\n";
$calCode .= "// ]]>\n";

?>
<script type='text/javascript'><?php echo $calCode?></script>
</head>
<body>
<?php
if ($params->get('show_page_heading', 1)) { ?>
	<h1>
	<?php echo $this->escape($params->get('page_heading')); ?>
	</h1>
<?php }

echo JHTML::_('content.prepare', $params->get('textbefore'));
if($params->get('show_selection', 1) == 1 || $params->get('show_selection', 1) == 3){
	$calendar_list = '<div id="gcalendar_view_list"><table class="gcalendar-table">';
	foreach($allCalendars as $calendar) {
		$value = html_entity_decode(JRoute::_('index.php?option=com_sportsmanagement&view=jsonfeed&format=raw&gcid='.$calendar->id));
		$checked = '';
		if(empty($calendarids) || in_array($calendar->id, $calendarids)){
			$checked = 'checked="checked"';
		}

		$calendar_list .="<tr>\n";
		$calendar_list .="<td><input type=\"checkbox\" name=\"".$calendar->calendar_id."\" value=\"".$value."\" ".$checked." onclick=\"updateGCalendarFrame(this)\"/></td>\n";
		$calendar_list .="<td><font color=\"".jsmGCalendarUtil::getFadedColor($calendar->color)."\">".$calendar->name."</font></td></tr>\n";
	}
	$calendar_list .="</table></div>\n";
	echo $calendar_list;
	echo "<div align=\"center\" style=\"text-align:center\">\n";
	$image = JURI::base().'components/com_sportsmanagement/assets/images/down.png';
	if($params->get('show_selection', 1) == 3) $image = JURI::base().'components/com_sportsmanagement/assets/images/up.png';
	echo "<img id=\"gc_gcalendar_view_toggle_status\" name=\"gc_gcalendar_view_toggle_status\" src=\"".$image."\" alt=\"".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_CALENDAR_LIST')."\" title=\"".Text::_('COM_GCALENDAR_GCALENDAR_VIEW_CALENDAR_LIST')."\"/>\n";
	echo "</div>\n";
}

echo "<div id='gcalendar_component'></div><div id='gcalendar_component_popup' style=\"visibility:hidden\" ></div>";
echo JHTML::_('content.prepare', $params->get('textafter'));

$dispatcher = JDispatcher::getInstance();
//JPluginHelper::importPlugin('gcalendar');
$dispatcher->trigger('onGCCalendarLoad', array('gcalendar_component'));

//if(!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendarap'.DS.'gcalendarap.php'))
//	echo "<div style=\"text-align:center;margin-top:10px\" ><a href=\"http://g4j.digital-peak.com\">GCalendar</a></div>\n";
?>
</body>
</html>