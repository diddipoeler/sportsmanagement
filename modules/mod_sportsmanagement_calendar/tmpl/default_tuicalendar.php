<?php
/**
 * Joomla 5/6 read-only TOAST UI calendar layout.
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

$moduleId = (int) ($module->id ?? 0);
$calendarId = 'jsm-tui-calendar-' . $moduleId;
$rangeId = 'jsm-tui-range-' . $moduleId;
$events = is_array($tui_events ?? null) ? $tui_events : [];
$base = rtrim((string) Uri::base(), '/') . '/modules/' . rawurlencode((string) $module->module) . '/tuicalendar';
?>
<link rel="stylesheet" href="https://uicdn.toast.com/tui.time-picker/latest/tui-time-picker.css">
<link rel="stylesheet" href="https://uicdn.toast.com/tui.date-picker/latest/tui-date-picker.css">
<link rel="stylesheet" href="<?php echo htmlspecialchars($base . '/dist/tui-calendar.min.css', ENT_QUOTES, 'UTF-8'); ?>">

<div class="jsm-tui-calendar-wrap">
    <div class="d-flex flex-wrap gap-2 align-items-center mb-2" data-jsm-tui-toolbar="<?php echo $moduleId; ?>">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-action="prev" aria-label="Previous month">‹</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-action="today">Today</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-action="next" aria-label="Next month">›</button>
        <strong id="<?php echo $rangeId; ?>" class="ms-2"></strong>
    </div>
    <div id="<?php echo $calendarId; ?>" style="height:600px;width:100%"></div>
</div>

<script src="https://uicdn.toast.com/tui.code-snippet/latest/tui-code-snippet.min.js"></script>
<script src="https://uicdn.toast.com/tui.time-picker/latest/tui-time-picker.min.js"></script>
<script src="https://uicdn.toast.com/tui.date-picker/latest/tui-date-picker.min.js"></script>
<script src="<?php echo htmlspecialchars($base . '/dist/tui-calendar.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script>
(() => {
    const initialise = () => {
        if (!window.tui || typeof window.tui.Calendar !== 'function') {
            return;
        }

        const selector = <?php echo json_encode('#' . $calendarId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
        const range = document.getElementById(<?php echo json_encode($rangeId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>);
        const toolbar = document.querySelector('[data-jsm-tui-toolbar="<?php echo $moduleId; ?>"]');
        const events = <?php echo json_encode($events, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
        const calendar = new window.tui.Calendar(selector, {
            defaultView: 'month',
            useCreationPopup: false,
            useDetailPopup: false,
            month: {
                startDayOfWeek: <?php echo (int) $params->get('cal_start_day', 1); ?>,
                visibleWeeksCount: 0
            },
            calendars: [{
                id: '1',
                name: 'SportsManagement',
                color: '#ffffff',
                bgColor: '#69BB2D',
                dragBgColor: '#69BB2D',
                borderColor: '#69BB2D'
            }]
        });

        if (typeof calendar.setDate === 'function') {
            calendar.setDate(new Date(<?php echo (int) $year; ?>, <?php echo max(0, (int) $month - 1); ?>, <?php echo max(1, (int) $day ?: 1); ?>));
        }

        if (events.length > 0) {
            calendar.createSchedules(events);
        }

        const updateRange = () => {
            if (!range || typeof calendar.getDate !== 'function') {
                return;
            }

            const date = new Date(calendar.getDate().getTime());
            range.textContent = new Intl.DateTimeFormat(document.documentElement.lang || undefined, {
                year: 'numeric',
                month: 'long'
            }).format(date);
        };

        if (toolbar) {
            toolbar.addEventListener('click', (event) => {
                const button = event.target.closest('button[data-action]');
                if (!button) {
                    return;
                }

                const action = button.dataset.action;
                if (action === 'prev') {
                    calendar.prev();
                } else if (action === 'next') {
                    calendar.next();
                } else if (action === 'today') {
                    calendar.today();
                }

                updateRange();
            });
        }

        updateRange();
        window.addEventListener('resize', () => calendar.render(), {passive: true});
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, {once: true});
    } else {
        initialise();
    }
})();
</script>
