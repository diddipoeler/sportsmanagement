<?php
/**
 * Joomla 5/6 Arrobe calendar layout.
 */
\defined('_JEXEC') or die;

$moduleId = (int) ($module->id ?? 0);
$calendarId = 'jsm-arrobe-calendar-' . $moduleId;
$events = is_array($arrobe_events ?? null) ? $arrobe_events : [];
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/arrobefr-jquery-calendar-bs4@1.0.3/dist/css/jquery-calendar.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment-with-locales.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.touchswipe/1.6.19/jquery.touchSwipe.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/arrobefr-jquery-calendar-bs4@1.0.3/dist/js/jquery-calendar.min.js"></script>

<div id="<?php echo $calendarId; ?>" class="jsm-arrobe-calendar" style="width:100%;min-height:400px"></div>

<script>
(() => {
    const initialise = () => {
        if (!window.jQuery || !window.moment || typeof window.jQuery.fn.Calendar !== 'function') {
            return;
        }

        const selector = <?php echo json_encode('#' . $calendarId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
        const events = <?php echo json_encode($events, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
        const calendar = window.jQuery(selector);

        if (calendar.data('jsmCalendarInitialised')) {
            return;
        }

        calendar.data('jsmCalendarInitialised', true);
        window.moment.locale(document.documentElement.lang || 'en');
        calendar.Calendar({
            locale: document.documentElement.lang || 'en',
            defaultView: {
                largeScreen: 'month',
                smallScreen: 'month'
            },
            weekday: {
                timeline: {
                    intervalMinutes: 30,
                    fromHour: 9
                }
            },
            events,
            daynotes: []
        }).init();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, {once: true});
    } else {
        initialise();
    }
})();
</script>
