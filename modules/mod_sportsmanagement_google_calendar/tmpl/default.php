<?php
/**
 * SportsManagement Google Calendar module layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<ul class="next-events">
    <?php foreach ($events ?? [] as $event) :
        $eventLink = trim((string) ($event->htmlLink ?? ''));
        $eventScheme = strtolower((string) parse_url($eventLink, PHP_URL_SCHEME));
        // Escaping href attributes alone cannot block javascript: or data: links.
        $hasEventLink = $params->get('show_link', true)
            && filter_var($eventLink, FILTER_VALIDATE_URL) !== false
            && in_array($eventScheme, ['http', 'https'], true);
    ?>
        <li class="event" itemscope itemtype="https://schema.org/Event">
            <?php if (!empty($event->jsmStartIso)) : ?>
                <meta itemprop="startDate" content="<?php echo $escape($event->jsmStartIso); ?>">
            <?php endif; ?>
            <?php if (!empty($event->jsmEndIso)) : ?>
                <meta itemprop="endDate" content="<?php echo $escape($event->jsmEndIso); ?>">
            <?php endif; ?>
            <div class="event-name">
                <?php if ($hasEventLink) : ?>
                    <a href="<?php echo $escape($eventLink); ?>" target="_blank" rel="noopener noreferrer">
                <?php endif; ?>
                    <span itemprop="name"><?php echo $escape($event->summary ?? ''); ?></span>
                <?php if ($hasEventLink) : ?>
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
