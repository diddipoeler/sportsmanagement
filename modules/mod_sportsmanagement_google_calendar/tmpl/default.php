<?php
/**
 * SportsManagement Google Calendar module layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<ul class="next-events">
    <?php foreach ($events ?? [] as $event) : ?>
        <li class="event" itemscope itemtype="https://schema.org/Event">
            <?php if (!empty($event->jsmStartIso)) : ?>
                <meta itemprop="startDate" content="<?php echo $escape($event->jsmStartIso); ?>">
            <?php endif; ?>
            <?php if (!empty($event->jsmEndIso)) : ?>
                <meta itemprop="endDate" content="<?php echo $escape($event->jsmEndIso); ?>">
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
                <?php echo $escape($event->jsmDuration ?? ''); ?>
            </div>
            <?php if ($params->get('show_location', false) && !empty($event->location)) : ?>
                <div class="event-location"><?php echo $escape($event->location); ?></div>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
