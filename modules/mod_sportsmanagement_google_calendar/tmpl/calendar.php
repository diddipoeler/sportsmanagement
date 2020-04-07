<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       calendar.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage mod_sportsmanagement_google_calendar
 */

defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

// Load the FullCalendar assets
$document = Factory::getDocument();
$document->addStyleSheet(Uri::root() . '/modules/mod_google_calendar/media/fullcalendar/fullcalendar.min.css');
$document->addScript(Uri::root() . '/modules/mod_google_calendar/media/fullcalendar/lib/moment.min.js');
$document->addScript(Uri::root() . '/modules/mod_google_calendar/media/fullcalendar/fullcalendar.min.js');
$document->addScript(Uri::root() . '/modules/mod_google_calendar/media/fullcalendar/lang/de.js');
$document->addScript(Uri::root() . '/modules/mod_google_calendar/media/fullcalendar/gcal.js');
?>
<script>
	jQuery(document).ready(function($) {
		jQuery('#calendar-<?php echo $module->id; ?>').fullCalendar({
			googleCalendarApiKey: '<?php echo $params->get('api_key', null); ?>',
			events: {
				googleCalendarId: '<?php echo $params->get('calendar_id', null); ?>'
			},
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			timeFormat: 'H:mm',
			eventClick: function(event) {
				window.open(event.url, 'gcalevent', 'width=700,height=600');
				return false;
			},
			loading: function(bool) {
				$('#loading').toggle(bool);
			}
		});
	});
</script>
<div id="calendar-<?php echo $module->id; ?>"></div>
