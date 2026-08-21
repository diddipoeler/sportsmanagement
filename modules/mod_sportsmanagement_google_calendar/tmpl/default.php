<?php
/**
 * SportsManagement Google Calendar module layout.
 */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementGoogleCalendar\Site\Helper\GoogleCalendarHelper;
use Joomla\CMS\Date\Date;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<ul class="next-events">
    <?php foreach ($events ?? [] as $event) : ?>
        <?php
        $startDate = isset($event->startDate) ? Date::getInstance($event->startDate) : null;
        $endDate = isset($event->endDate) ? Date::getInstance($event->endDate) : null;
        ?>
        <li class="event" itemscope itemtype="https://schema.org/Event">
            <?php if ($startDate !== null) : ?>
                <meta itemprop="startDate" content="<?php echo $escape($startDate->toISO8601(true)); ?>">
            <?php endif; ?>
            <?php if ($endDate !== null) : ?>
                <meta itemprop="endDate" content="<?php echo $escape($endDate->toISO8601(true)); ?>">
            <?php endif; ?>
            <div class="event-name">
                <?php if ($params->get('show_link', true) && !empty($event->htmlLink)) : ?>
                    <a href="<?php echo $escape($event->htmlLink); ?>" target="_blank" rel="noopener noreferrer">
                <?php endif; ?>
                    <span itemprop="name"><?php echo $escape($event->summary ?? ''); ?></span>
                <?php if ($params->get('show_link', true) && !empty($event->htmlLink)) : ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="event-duration">
                <?php echo $escape(GoogleCalendarHelper::duration($event)); ?>
            </div>
            <?php if ($params->get('show_location', false) && !empty($event->location)) : ?>
                <div class="event-location"><?php echo $escape($event->location); ?></div>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
