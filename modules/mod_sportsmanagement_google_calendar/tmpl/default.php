<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_google_calendar
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<ul class="next-events">
	<?php foreach ($events AS $event)
	:
	?>
		<li class="event" itemscope itemtype="http://schema.org/Event">
			<meta itemprop="startDate" content="<?php echo JDate::getInstance($event->startDate)->toISO8601(true); ?>">
			<meta itemprop="endDate" content="<?php echo JDate::getInstance($event->endDate)->toISO8601(true); ?>">
			<div class="event-name">
				<?php if ($params->get('show_link', true))
				:
	?>
				<a href="<?php echo $event->htmlLink; ?>" target="_blank">
				<?php endif; ?>
					<span itemprop="name"><?php echo $event->summary; ?></span>
		<?php
		if ($params->get('show_link', true))
		:
	?>
				</a>
		<?php endif; ?>
			</div>
			<div class="event-duration">
				<?php echo ModGoogleCalendarHelper::duration($event); ?>
			</div>
	<?php if ($params->get('show_location', false) && !empty($event->location))
	:
	?>
				<div class="event-location"><?php echo $event->location; ?></div>
	<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
