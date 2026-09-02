<?php
/**
 * Joomla 5/6 native calendar layout for SportsManagement Google calendars.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$moduleId = (int) $module->id;
$moduleClass = trim((string) $params->get('moduleclass_sfx', ''));
$height = max(0, (int) ($calendarConfig['calendarHeight'] ?? 0));
$optionsKey = (string) ($calendarOptionsKey ?? ('mod_sportsmanagement_gcalendar.' . $moduleId));
?>
<div
    id="gcalendar_module_<?php echo $moduleId; ?>"
    class="jsm-gcalendar<?php echo $moduleClass !== '' ? ' ' . htmlspecialchars($moduleClass, ENT_QUOTES, 'UTF-8') : ''; ?>"
    data-jsm-gcalendar
    data-calendar-options-key="<?php echo htmlspecialchars($optionsKey, ENT_QUOTES, 'UTF-8'); ?>"
    <?php echo $height > 0 ? 'style="--jsm-gcalendar-height:' . $height . 'px"' : ''; ?>
>
    <div class="jsm-gcalendar-toolbar">
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary" data-calendar-action="prev"
                    aria-label="<?php echo htmlspecialchars((string) ($calendarConfig['previousLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">&lsaquo;</button>
            <button type="button" class="btn btn-outline-secondary" data-calendar-action="today">
                <?php echo htmlspecialchars((string) ($calendarConfig['todayLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
            </button>
            <button type="button" class="btn btn-outline-secondary" data-calendar-action="next"
                    aria-label="<?php echo htmlspecialchars((string) ($calendarConfig['nextLabel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">&rsaquo;</button>
        </div>
        <strong class="jsm-gcalendar-title" data-calendar-title aria-live="polite"></strong>
    </div>

    <div class="jsm-gcalendar-weekdays" data-calendar-weekdays aria-hidden="true"></div>
    <div class="jsm-gcalendar-grid" data-calendar-grid></div>
    <div class="jsm-gcalendar-loading" data-calendar-loading hidden role="status" aria-live="polite"></div>
</div>
