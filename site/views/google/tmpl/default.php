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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;

jsmGCalendarUtil::loadLibrary();

$component = JComponentHelper::getComponent('com_gcalendar');
$menu = Factory::getApplication()->getMenu();
$items = $menu->getItems('component_id', $component->id);

$model = & $this->getModel();
if (is_array($items)){
	$app = Factory::getApplication();
	$pathway = $app->getPathway();
	foreach($items as $item) {
		$paramsItem	= $menu->getParams($item->id);
		//if($paramsItem->get('calendars')===$this->params->get('calendars')){
		//	$pathway->addItem($this->params->get('name'), '');
		//}
	}
}
$params = $this->params;
?>

<div class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>"><?php
$variables = '';
$variables .= '?showTitle='.$params->get( 'title' );
$variables .= '&amp;showNav='.$params->get( 'navigation' );
$variables .= '&amp;showDate='.$params->get( 'date' );
$variables .= '&amp;showPrint='.$params->get( 'print' );
$variables .= '&amp;showTabs='.$params->get( 'tabs' );
$variables .= '&amp;showCalendars=0';
$variables .= '&amp;showTz='.$params->get( 'tz' );
$variables .= '&amp;mode='.$params->get( 'view', 'MONTH');
$variables .= '&amp;wkst='.$params->get( 'weekstart', 2);
$variables .= '&amp;bgcolor=%23'.$params->get( 'bgcolor', 'FFFFFF' );
$variables .= '&amp;hl='.jsmGCalendarUtil::getFrLanguage();
$tz = $params->get('timezone');
if(!empty($tz))$tz='&amp;ctz='.$tz;
$variables .= $tz;
$variables .= '&amp;height='.$params->get( 'height', 500);

$domain = 'http://www.google.com/calendar/embed';
$google_apps_domain = $params->get('google_apps_domain');
if(!empty($google_apps_domain)){
	$domain = 'http://www.google.com/calendar/hosted/'.$google_apps_domain.'/embed';
}

$calendar_list = '<div id="gc_google_view_list"><table>';
$calendarids = array();
$tmp = $params->get('calendarids');
if(is_array($tmp))
$calendarids = $tmp;
else if(!empty($tmp))
$calendarids[] = $tmp;
foreach($this->calendars as $calendar) {
	$value = '&amp;src='.$calendar->calendar_id;

	$html_color = '';
	if(!empty($calendar->color)){
		$color = $calendar->color;
		if(strpos($calendar->color, '#') === 0){
			$color = str_replace("#","%23",$calendar->color);
			$html_color = $calendar->color;
		}
		else if(!(strpos($calendar->color, '%23') === 0)){
			$color = '%23'.$calendar->color;
			$html_color = '#'.$calendar->color;
		}
		$value = $value.'&amp;color='.$color;
	}

	if(!empty($calendar->magic_cookie)){
		$value = $value.'&amp;pvttk='.$calendar->magic_cookie;
	}

	$checked = '';
	if(empty($calendarids) || in_array($calendar->id, $calendarids)){
		$variables .= $value;
		$checked = 'checked="checked"';
	}

	$calendar_list .="<tr>\n";
	$calendar_list .="<td><input type=\"checkbox\" name=\"".$calendar->calendar_id."\" value=\"".$value."\" ".$checked." onclick=\"updateGCalendarFrame(this)\"/></td>\n";
	$calendar_list .="<td><font color=\"".$html_color."\">".$calendar->name."</font></td></tr>\n";
}
$calendar_list .="</table></div>\n";
if($params->get('show_selection')==1 || $params->get('show_selection') == 3){
	$document = Factory::getDocument();
	$document->addScript(Uri::base(). 'components/com_gcalendar/views/google/tmpl/gcalendar.js' );
	$document->addStyleSheet(Uri::base().'components/com_gcalendar/views/google/tmpl/gcalendar.css');
	if($params->get('show_selection', 1) == 1) {
		$document->addScriptDeclaration("gcjQuery(document).ready(function() {gcjQuery('#gc_google_view_list').hide();});");
	}
	echo $calendar_list;
	echo "<div align=\"center\" style=\"text-align:center\">\n";
	echo "<a id=\"gc_google_view_toggle\" name=\"gc_google_view_toggle\" href=\"#\">\n";
	$image = Uri::base().'media/com_gcalendar/images/down.png';
	if($params->get('show_selection', 1) == 3) $image = Uri::base().'media/com_gcalendar/images/up.png';
	echo "<img id=\"gc_google_view_toggle_status\" name=\"gc_google_view_toggle_status\" src=\"".$image."\" alt=\"".Text::_('COM_GCALENDAR_GOOGLE_VIEW_CALENDAR_LIST')."\" title=\"".Text::_('COM_GCALENDAR_GOOGLE_VIEW_CALENDAR_LIST')."\"/>\n";
	echo "</a></div>\n";
}
$calendar_url="";
if ($params->get('use_custom_css')) {
	$calendar_url= Uri::base().'components/com_gcalendar/libraries/restylegc/restylegc.php'.$variables;
} else {
	$calendar_url=$domain.$variables;
}
echo $params->get( 'textbefore' );

?> <iframe id="gcalendar_frame" src="<?php echo $calendar_url; ?>"
	width="<?php echo $params->get( 'width', 500); ?>"
	height="<?php echo $params->get( 'height', 500); ?>" align="top"
	frameborder="0"
	class="gcalendar<?php echo $params->get( 'pageclass_sfx' ); ?>"> <?php echo Text::_( 'COM_GCALENDAR_GCALENDAR_VIEW_CALENDAR_NO_IFRAMES' ); ?>
</iframe></div>

<?php
echo $params->get( 'textafter' );
if (!File::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendarap'.DS.'gcalendarap.php'))
	echo "<div style=\"text-align:center;margin-top:10px\" ><a href=\"http://g4j.digital-peak.com\">GCalendar</a></div>\n";