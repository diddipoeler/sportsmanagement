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
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
$params = $this->params;

if ($this->params->get('show_page_heading', 1)) { ?>
	<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
<?php }

$document = Factory::getDocument();

$theme = $params->get('theme', '');
if (empty($theme) || $theme == -1) {
	$document->addStyleDeclaration('.ui-datepicker, .ui-timepicker-list { font:'.(jsmGCalendarUtil::isJoomlaVersion('2.5') ? '75' : '90').'% Arial,sans-serif; }');
}
jsmGCalendarUtil::loadLibrary(array('jquery' => true, 'jqueryui' => $theme, 'bootstrap' => true, 'gcalendar' => true, 'fullcalendar' => true));

$document->addStyleSheet(Uri::base().'components/com_sportsmanagement/views/gcalendar/tmpl/gcalendar.css');
$document->addScript(Uri::base().'components/com_sportsmanagement/views/gcalendar/tmpl/gcalendar.js');


$calendarids = $this->calendarids;
$allCalendars = jsmGCalendarDBUtil::getAllCalendars();

$calsSources = "		eventSources: [\n";
foreach($allCalendars as $calendar) {
	if(empty($calendarids) || in_array($calendar->id, $calendarids)){
		$value = html_entity_decode(Route::_('index.php?option=com_sportsmanagement&view=jsonfeed&format=raw&gcid='.$calendar->id.'&Itemid='.Factory::getApplication()->input->getInt('Itemid')));
		$calsSources .= "				'".$value."',\n";
	}
}
$calsSources = trim($calsSources, ",\n");
$calsSources .= "	],\n";

$defaultView = $params->get('defaultView', 'month');
if($params->get('defaultView', 'month') == 'week')
	$defaultView = 'agendaWeek';
else if($params->get('defaultView', 'month') == 'day')
	$defaultView = 'agendaDay';

$daysLong = "[";
$daysShort = "[";
$daysMin = "[";
$monthsLong = "[";
$monthsShort = "[";
for ($i=0; $i<7; $i++) {
	$daysLong .= "'".jsmGCalendarUtil::dayToString($i, false)."'";
	$daysShort .= "'".jsmGCalendarUtil::dayToString($i, true)."'";
	$daysMin .= "'".mb_substr(jsmGCalendarUtil::dayToString($i, true), 0, 2)."'";
	if($i < 6){
		$daysLong .= ",";
		$daysShort .= ",";
		$daysMin .= ",";
	}
}
for ($i=1; $i<=12; $i++) {
	$monthsLong .= "'".jsmGCalendarUtil::monthToString($i, false)."'";
	$monthsShort .= "'".jsmGCalendarUtil::monthToString($i, true)."'";
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
$calCode .= "	if (gcjQuery(document).width() < 500) {tmpView = 'list';}\n";
$calCode .= "	gcjQuery('#gcalendar_component').fullCalendar({\n";
$calCode .= "		header: {\n";
$calCode .= "			left: 'prev,next ',\n";
$calCode .= "			center: 'title',\n";
$calCode .= "			right: 'month,agendaWeek,agendaDay,list'\n";
$calCode .= "		},\n";
$calCode .= "		year: tmpYear,\n";
$calCode .= "		month: tmpMonth,\n";
$calCode .= "		date: tmpDay,\n";
$calCode .= "		defaultView: tmpView,\n";
$calCode .= "		weekNumbers: ".($params->get('week_numbers', 0)==1?'true':'false').",\n";
$calCode .= "		weekNumberTitle: '',\n";
$calCode .= "		editable: false, theme: ".(!empty($theme)?'true':'false').",\n";
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
$calCode .= "		allDayText: '".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_ALL_DAY', true)."',\n";
$calCode .= "		buttonText: {\n";
$calCode .= "			today:    '".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TOOLBAR_TODAY', true)."',\n";
$calCode .= "			month:    '".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_VIEW_MONTH', true)."',\n";
$calCode .= "			week:     '".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_VIEW_WEEK', true)."',\n";
$calCode .= "			day:      '".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_VIEW_DAY', true)."',\n";
$calCode .= "			list:      '".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_VIEW_LIST', true)."'\n";
$calCode .= "		},\n";
$calCode .= "		listSections: 'smart',\n";
$calCode .= "		listRange: 30,\n";
$calCode .= "		listPage: 30,\n";
$calCode .= "		listTexts: {
						until: '".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_UNTIL', true)."',
						past: '".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_PAST', true)."',
						today: '".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_TODAY', true)."',
						tomorrow: '".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_TOMORROW', true)."',
						thisWeek: '".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_THIS_WEEK', true)."',
						nextWeek: '".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_NEXT_WEEK', true)."',
						thisMonth: '".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_THIS_MONTH', true)."',
						nextMonth: '".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_NEXT_MONTH', true)."',
						future: '".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_FUTURE', true)."',
						week: '".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TEXTS_WEEK', true)."'
					},\n";
$calCode .= $calsSources;
$calCode .= "		viewDisplay: function(view) {\n";
$calCode .= "			var d = gcjQuery('#gcalendar_component').fullCalendar('getDate');\n";
$calCode .= "			var newHash = 'year='+d.getFullYear()+'&month='+(d.getMonth()+1)+'&day='+d.getDate()+'&view='+view.name;\n";
$calCode .= "			if(window.location.hash.replace(/&amp;/gi, \"&\") != newHash)\n";
$calCode .= "			window.location.hash = newHash;\n";
$calCode .= "		},\n";
$calCode .= "		eventRender: function(event, element) {\n";
$calCode .= "			if (event.description){\n";
$calCode .= "				element.tipTip({content: event.description, defaultPosition: 'top'});}\n";
$calCode .= "		},\n";

$calCode .= "		eventClick: function(event, jsEvent, view) {gcjQuery('#tiptip_holder').hide();\n";
if($params->get('show_event_as_popup', 1) == 1){
	$calCode .= "		        if (!Modernizr.touch) {\n";
	$calCode .= "		        gcjQuery.fancybox({\n";
	$calCode .= "		           href: event.url + (event.url.indexOf('?') != -1 ? '&' : '?')+'tmpl=component',\n";
	$calCode .= "		           width: ".$params->get('popup_width', 650).",\n";
	$calCode .= "		           height: ".$params->get('popup_height', 500).",\n";
	$calCode .= "		           autoScale : false,\n";
	$calCode .= "		           autoDimensions : false, \n";
	$calCode .= "		           transitionIn : 'elastic',\n";
	$calCode .= "		           transitionOut : 'elastic',\n";
	$calCode .= "		           speedIn : 600,\n";
	$calCode .= "		           speedOut : 200,\n";
	$calCode .= "		           type : 'iframe',\n";
	if (jsmGCalendarUtil::isJoomlaVersion('2.5')) {
		$calCode .= "		           onCleanup : function(){if(gcjQuery('#fancybox-frame').contents().find('#system-message dt').length > 0){gcjQuery('#gcalendar_component').fullCalendar('refetchEvents');}}\n";
	}
	if (jsmGCalendarUtil::isJoomlaVersion('3')) {
		$calCode .= "		           onCleanup : function(){if(gcjQuery('#fancybox-frame').contents().find('#system-message div').length > 0){gcjQuery('#gcalendar_component').fullCalendar('refetchEvents');}}\n";
	}
	$calCode .= "		        });\n";
	$calCode .= "		        return false;\n";
	$calCode .= "		        } else {\n";
	$calCode .= "		        	window.location = gcEncode(event.url); return false;\n";
	$calCode .= "		        }\n";
} else {
	$calCode .= "		        window.location = gcEncode(event.url); return false;\n";
}
$calCode .= "		},\n";

$calCode .= "		dayClick: function(date, allDay, jsEvent, view) {\n";
$calCode .= "			dayClickCustom(date, allDay, jsEvent, view);\n";
$calCode .= "		},\n";
$calCode .= "		eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view) {\n";
$calCode .= "			eventDropCustom(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view);\n";
$calCode .= "		},\n";
$calCode .= "		eventResize: function(event, dayDelta, minuteDelta, revertFunc, jsEvent, ui, view) {\n";
$calCode .= "			eventResizeCustom(event, dayDelta, minuteDelta, revertFunc, jsEvent, ui, view);\n";
$calCode .= "		},\n";
$calCode .= "		loading: function(bool) {\n";
$calCode .= "			if (bool) {\n";
$calCode .= "				gcjQuery('#gcalendar_component_loading').show();\n";
$calCode .= "			}else{\n";
$calCode .= "				gcjQuery('#gcalendar_component_loading').hide();\n";
$calCode .= "			}\n";
$calCode .= "		}\n";
$calCode .= "	});\n";
$class = empty($theme)?'fc':'ui';
$calCode .= "	var custom_buttons = '<span class=\"fc-button fc-button-datepicker ".$class."-state-default ".$class."-corner-left ".$class."-corner-right\">'+\n";
$calCode .= "			'<span class=\"fc-button-inner\"><span class=\"fc-button-content\" id=\"gcalendar_component_date_picker_button\">'+\n";
$calCode .= "			'<input type=\"hidden\" id=\"gcalendar_component_date_picker\" value=\"\">'+\n";
$calCode .= "			'<i class=\"icon-calendar\" title=\"".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_SHOW_DATEPICKER')."\"></i>'+\n";
$calCode .= "			'</span></span></span></span>';\n";
$calCode .= "		custom_buttons +='<span class=\"hidden-phone fc-button fc-button-print ".$class."-state-default ".$class."-corner-left ".$class."-corner-right\">'+\n";
$calCode .= "			'<span class=\"fc-button-inner\"><span class=\"fc-button-content\" id=\"gcalendar_component_print_button\">'+\n";
$calCode .= "			'<i class=\"icon-print\" title=\"".Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_TOOLBAR_PRINT')."\"></i>'+\n";
$calCode .= "			'</span></span></span></span>';\n";
$calCode .= "	gcjQuery('span.fc-header-space').after(custom_buttons);\n";
$calCode .= "	if (gcjQuery('table').disableSelection) gcjQuery('div.fc-header-space').closest('table.fc-header').disableSelection();\n";
$calCode .= "	gcjQuery(\"#gcalendar_component_date_picker\").datepicker({\n";
$calCode .= "		dateFormat: 'dd-mm-yy',\n";
$calCode .= "		changeYear: true, \n";
$calCode .= "		dayNames: ".$daysLong.",\n";
$calCode .= "		dayNamesShort: ".$daysShort.",\n";
$calCode .= "		dayNamesMin: ".$daysMin.",\n";
$calCode .= "		monthNames: ".$monthsLong.",\n";
$calCode .= "		monthNamesShort: ".$monthsShort.",\n";
$calCode .= "		onSelect: function(dateText, inst) {\n";
$calCode .= "			var d = gcjQuery('#gcalendar_component_date_picker').datepicker('getDate');\n";
$calCode .= "			var view = gcjQuery('#gcalendar_component').fullCalendar('getView').name;\n";
$calCode .= "			gcjQuery('#gcalendar_component').fullCalendar('gotoDate', d);\n";
$calCode .= "		}\n";
$calCode .= "	});\n";
$calCode .= "	gcjQuery(window).bind( 'hashchange', function(){\n";
$calCode .= "		var today = new Date();\n";
$calCode .= "		var tmpYear = today.getFullYear();\n";
$calCode .= "		var tmpMonth = today.getMonth();\n";
$calCode .= "		var tmpDay = today.getDate();\n";
$calCode .= "		var tmpView = '".$defaultView."';\n";
$calCode .= "		var vars = window.location.hash.replace(/&amp;/gi, \"&\").split(\"&\");\n";
$calCode .= "		for ( var i = 0; i < vars.length; i++ ){\n";
$calCode .= "			if(vars[i].match(\"^#year\"))tmpYear = vars[i].substring(6);\n";
$calCode .= "			if(vars[i].match(\"^month\"))tmpMonth = vars[i].substring(6)-1;\n";
$calCode .= "			if(vars[i].match(\"^day\"))tmpDay = vars[i].substring(4);\n";
$calCode .= "			if(vars[i].match(\"^view\"))tmpView = vars[i].substring(5);\n";
$calCode .= "		}\n";
$calCode .= "		var date = new Date(tmpYear, tmpMonth, tmpDay,0,0,0);\n";
$calCode .= "		var d = gcjQuery('#gcalendar_component').fullCalendar('getDate');\n";
$calCode .= "		var view = gcjQuery('#gcalendar_component').fullCalendar('getView');\n";
$calCode .= "		if(date.getFullYear() != d.getFullYear() || date.getMonth() != d.getMonth() || date.getDate() != d.getDate())\n";
$calCode .= "			gcjQuery('#gcalendar_component').fullCalendar('gotoDate', date);\n";
$calCode .= "		if(view.name != tmpView)\n";
$calCode .= "			gcjQuery('#gcalendar_component').fullCalendar('changeView', tmpView);\n";
$calCode .= "	});\n";
if($params->get('show_selection', 1) == 1) {
	$calCode .= "gcjQuery('#gcalendar_view_list').hide();\n";
}
$calCode .= "});\n";
$calCode .= "var dayClickCustom = function(date, allDay, jsEvent, view){gcjQuery('#gcalendar_component').fullCalendar('gotoDate', date).fullCalendar('changeView', 'agendaDay');}\n";
$calCode .= "var eventDropCustom = function(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view){};\n";
$calCode .= "var eventResizeCustom = function(event, dayDelta, minuteDelta, revertFunc, jsEvent, ui, view){};\n";
$calCode .= "// ]]>\n";
$document->addScriptDeclaration($calCode);
?>

<div class="dp-container">

<?php
echo HTMLHelper::_('content.prepare', $params->get('textbefore'));
if($params->get('show_selection', 1) == 1 || $params->get('show_selection', 1) == 3){?>
<dl id="gcalendar_view_list">
<?php foreach($allCalendars as $calendar) {
		$value = html_entity_decode(Route::_('index.php?option=com_sportsmanagement&view=jsonfeed&format=raw&gcid='.$calendar->id));
		$checked = '';
		if(empty($calendarids) || in_array($calendar->id, $calendarids)){
			$checked = 'checked="checked"';
		}?>
		<dt>
			<label class="checkbox">
				<input type="checkbox" name="<?php echo $calendar->id?>" value="<?php echo $value.'" '.$checked?> onclick="updateGCalendarFrame(this)"/>
				<font color="<?php echo jsmGCalendarUtil::getFadedColor($calendar->color)?>"><?php echo $calendar->name;?></font>
			</label>
		</dt>
		<dd></dd>
<?php }?>
</dl>
<?php
$image = Uri::base().'components/com_sportsmanagement/assets/images/down.png';
if($params->get('show_selection', 1) == 3) $image = Uri::base().'components/com_sportsmanagement/assets/images/up.png';?>
<div style="text-align:center">
<img id="gcalendar_view_toggle_status" src="<?php echo $image?>" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_CALENDAR_LIST')?>" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_GCALENDAR_VIEW_CALENDAR_LIST')?>"/>
</div>
<?php }?>

<div id='gcalendar_component_loading' style="text-align: center;<?php if (empty($allCalendars)) echo 'visibility:hidden';?>">
	<img src="<?php echo Uri::base()?>components/com_sportsmanagement/assets/images/ajax-loader.gif"  alt="loader" />
</div>
<div id="gcalendar_component"></div>
<div id='gcalendar_component_popup' style="visibility:hidden" ></div>
</div>
<?php
echo HTMLHelper::_('content.prepare', $params->get('textafter'));

$dispatcher = JDispatcher::getInstance();
$dispatcher->trigger('onGCCalendarLoad', array('gcalendar_component'));

//echo 'dispatcher<br><pre>'.print_r($dispatcher,true).'</pre>';



	//echo "<div style=\"text-align:center;margin-top:10px\" ><a href=\"http://g4j.digital-peak.com\">GCalendar</a></div>\n";