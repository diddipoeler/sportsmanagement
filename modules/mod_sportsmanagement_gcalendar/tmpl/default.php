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
 * @package   GCalendar
 * @author    Digital Peak http://www.digital-peak.com
 * @copyright Copyright (C) 2007 - 2013 Digital Peak. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

jsmGCalendarUtil::loadLibrary(array('jquery' => true, 'fullcalendar' => true));

$document = Factory::getDocument();
$document->addStyleSheet(Uri::base() . 'modules/mod_sportsmanagement_gcalendar/tmpl/gcalendar.css');

$color      = $params->get('event_color', '135CAE');
$fadedColor = jsmGCalendarUtil::getFadedColor($color);
$cssClass   = "gcal-module_event_gccal_" . $module->id;
$document->addStyleDeclaration("." . $cssClass . ",." . $cssClass . " a, ." . $cssClass . " div{background-color: " . $fadedColor . " !important; border-color: #" . $color . "; color: " . $fadedColor . ";} .fc-header-center{vertical-align: middle !important;} #gcalendar_module_" . $module->id . " .fc-state-default span, #gcalendar_module_" . $module->id . " .ui-state-default{padding:0px !important;}");

$theme = $params->get('theme', jsmGCalendarUtil::getComponentParameter('theme'));

if (!empty($theme))
{
	jsmGCalendarUtil::loadLibrary(array('jqueryui' => $theme));
}

$daysLong    = "[";
$daysShort   = "[";
$daysMin     = "[";
$monthsLong  = "[";
$monthsShort = "[";

for ($i = 0; $i < 7; $i++)
{
	$daysLong  .= "'" . jsmGCalendarUtil::dayToString($i, false) . "'";
	$daysShort .= "'" . jsmGCalendarUtil::dayToString($i, true) . "'";
	$daysMin   .= "'" . mb_substr(jsmGCalendarUtil::dayToString($i, true), 0, 2) . "'";

	if ($i < 6)
	{
		$daysLong  .= ",";
		$daysShort .= ",";
		$daysMin   .= ",";
	}
}


for ($i = 1; $i <= 12; $i++)
{
	$monthsLong  .= "'" . jsmGCalendarUtil::monthToString($i, false) . "'";
	$monthsShort .= "'" . jsmGCalendarUtil::monthToString($i, true) . "'";

	if ($i < 12)
	{
		$monthsLong  .= ",";
		$monthsShort .= ",";
	}
}

$daysLong    .= "]";
$daysShort   .= "]";
$daysMin     .= "]";
$monthsLong  .= "]";
$monthsShort .= "]";

$ids = '';

foreach ($calendars as $calendar)
{
	$ids .= $calendar->id . ',';
}

$ids = rtrim($ids, ',');

$calCode = "// <![CDATA[ \n";
$calCode .= "gcjQuery(document).ready(function(){\n";
$calCode .= "   gcjQuery('#gcalendar_module_" . $module->id . "').fullCalendar({\n";
$calCode .= "		events: '" . html_entity_decode(Route::_('index.php?option=com_sportsmanagement&view=jsonfeed&compact=' . $params->get('compact_events', 1) . '&format=raw&gcids=' . $ids)) . "',\n";
$calCode .= "       header: {\n";
$calCode .= "				left: 'prev,next ',\n";
$calCode .= "				center: 'title',\n";
$calCode .= "				right: ''\n";
$calCode .= "		},\n";
$calCode .= "		defaultView: 'month',\n";

$height = $params->get('calendar_height', null);

if (!empty($height))
{
	$calCode .= "		contentHeight: " . $height . ",\n";
}

$calCode .= "		editable: false, theme: " . (!empty($theme) ? 'true' : 'false') . ",\n";
$calCode .= "		titleFormat: { \n";
$calCode .= "		        month: '" . jsmFullcalendar::convertFromPHPDate($params->get('titleformat_month', 'M Y')) . "'},\n";
$calCode .= "		firstDay: " . $params->get('weekstart', 0) . ",\n";
$calCode .= "		monthNames: " . $monthsLong . ",\n";
$calCode .= "		monthNamesShort: " . $monthsShort . ",\n";
$calCode .= "		dayNames: " . $daysLong . ",\n";
$calCode .= "		dayNamesShort: " . $daysShort . ",\n";
$calCode .= "		timeFormat: { \n";
$calCode .= "		        month: '" . jsmFullcalendar::convertFromPHPDate($params->get('timeformat_month', 'g:i a')) . "'},\n";
$calCode .= "		columnFormat: { month: 'ddd', week: 'ddd d', day: 'dddd d'},\n";
$calCode .= "		eventRender: function(event, element) {\n";
$calCode .= "			event.editable = false;\n";
$calCode .= "			if (event.description){\n";
$calCode .= "				element.tipTip({content: event.description, defaultPosition: 'top'});}\n";
$calCode .= "		},\n";
$calCode .= "		loading: function(bool) {\n";
$calCode .= "			if (bool) {\n";
$calCode .= "				gcjQuery('#gcalendar_module_" . $module->id . "_loading').show();\n";
$calCode .= "			}else{\n";
$calCode .= "				gcjQuery('#gcalendar_module_" . $module->id . "_loading').hide();\n";
$calCode .= "			}\n";
$calCode .= "		}\n";
$calCode .= "	});\n";
$calCode .= "});\n";
$calCode .= "// ]]>\n";
$document->addScriptDeclaration($calCode);

echo "<div id='gcalendar_module_" . $module->id . "_loading' style=\"text-align: center;\"><img src=\"" . Uri::base() . "administrator/components/com_sportsmanagement/assets/images/ajax-loader.gif\"  alt=\"loader\" /></div>";
echo "<div id='gcalendar_module_" . $module->id . "'></div><div id='gcalendar_module_" . $module->id . "_popup' style=\"visibility:hidden\" ></div>";
