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

jsmGCalendarUtil::loadLibrary(array('jquery' => true, 'maps' => true, 'bootstrap' => true, 'gcalendar' => true));

$document = Factory::getDocument();
$document->addStyleSheet(Uri::base() . 'components/com_sportsmanagement/views/event/tmpl/default.css');
$document->addScript(Uri::base() . 'components/com_sportsmanagement/views/event/tmpl/default.js');

if (Factory::getApplication()->input->getCmd('tmpl', '') == 'component')
{
	$document->addStyleSheet(Uri::base() . 'components/com_sportsmanagement/views/event/tmpl/none-responsive.css');
}

$dispatcher = JDispatcher::getInstance();

$content = '{{#events}}
<div id="gcal-event-container" class="dp-container">
{{#pluginsBefore}} {{{.}}} {{/pluginsBefore}}
<div class="clearfix"></div>
<h2>{{eventLabel}}</h2>
<div class="row-fluid">
	<div class="span7">
		<div class="row-fluid">
			<div class="span3 event-label">{{titleLabel}}: </div>
			<div class="span9 event-content">{{title}}</div>
		</div>
		<div class="row-fluid">
			<div class="span3 event-label">{{calendarNameLabel}}: </div>
			<div class="span9 event-content">{{#calendarLink}}<a href="{{calendarLink}}">{{calendarName}}</a>{{/calendarLink}}{{^calendarLink}}{{calendarName}}{{/calendarLink}}</div>
		</div>
		<div class="row-fluid">
			<div class="span3 event-label">{{dateLabel}}: </div>
			<div class="span9 event-content">{{date}}</div>
		</div>
		<div class="row-fluid">
			<div class="span3 event-label">{{locationLabel}}: </div>
			<div class="span9 event-content">{{#location}}<a href="http://maps.google.com/?q={{location}}" target="_blank" id="gc-event-details-location">{{location}}</a>{{/location}}</div>
		</div>
		<div class="row-fluid">
			<div class="span3 event-label">{{copyLabel}}: </div>
			<div class="span9"><a target="_blank" href="{{copyGoogleUrl}}">{{copyGoogleLabel}}</a></div>
		</div>
		<div class="row-fluid">
			<div class="span3 event-label"></div>
			<div class="span9 event-content"><a target="_blank" href="{{copyOutlookUrl}}">{{copyOutlookLabel}}</a></div>
		</div>
	</div>
	<div class="span5">{{#maplink}}<div id="gc-event-details-map" class="pull-right gcalendar-fixed-map" data-zoom="4"></div>{{/maplink}}</div>
</div>
{{#description}}
<h2>{{descriptionLabel}}</h2>
{{{description}}}
{{/description}}
{{#pluginsAfter}} {{{.}}} {{/pluginsAfter}}
</div>
{{/events}}
{{^events}}
{{emptyText}}
{{/events}}';

$plugins                  = array();
$plugins['pluginsBefore'] = array();
$plugins['pluginsAfter']  = array();
$dispatcher->trigger('onBeforeDisplayEvent', array($this->event, &$content, &$plugins['pluginsBefore']));
$dispatcher->trigger('onAfterDisplayEvent', array($this->event, &$content, &$plugins['pluginsAfter']));

echo jsmGCalendarUtil::renderEvents(array($this->event), $content, Factory::getApplication()->getParams(), $plugins);

// Echo "<div style=\"text-align:center;margin-top:10px\" ><a href=\"http://g4j.digital-peak.com\">GCalendar</a></div>\n";
