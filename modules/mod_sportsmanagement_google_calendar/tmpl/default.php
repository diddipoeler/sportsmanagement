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

/**
 * Escape one Calendar event attribute or display value as UTF-8 HTML.
 *
 * Handles malformed cached API fields without converting arrays to strings
 * and leaves unsupported values empty in the frontend output.
 *
 * @param mixed $value Calendar event field from Joomla's callback cache.
 * @return string Safely encoded HTML text for the module template.
 */
$escape = static fn ($value): string => htmlspecialchars(
    is_string($value) || is_numeric($value) ? (string) $value : '',
    ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'
);
?>
<ul class="next-events">
    <?php foreach (is_array($events ?? null) ? $events : [] as $event) :
        if (!is_object($event)) {
            continue;
        }
        $rawEventLink = $event->htmlLink ?? '';
        $eventLink = is_string($rawEventLink) ? trim($rawEventLink) : '';
        $eventParts = $eventLink !== '' ? parse_url($eventLink) : false;
        $eventScheme = is_array($eventParts) ? strtolower((string) ($eventParts['scheme'] ?? '')) : '';
        // Escaping href attributes alone cannot block javascript: or data: links.
        $hasEventLink = $params->get('show_link', true)
            && filter_var($eventLink, FILTER_VALIDATE_URL) !== false
            && in_array($eventScheme, ['http', 'https'], true)
            && is_array($eventParts)
            && !isset($eventParts['user'])
            && !isset($eventParts['pass']);
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
