<?php
/**
 * Standalone Joomla 5/6 administrator field and calendar settings regressions.
 *
 * Usage: php .github/tests/joomla56-extra-fields-calendar-settings.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

defined('_JEXEC') || define('_JEXEC', 1);

require_once dirname(__DIR__, 2) . '/admin/src/Helper/ExtraFieldsReadHelper.php';
require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarRenderer.php';

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraFieldsReadHelper;
use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarRenderer;

/**
 * Verify the administrator reader rejects invalid template contexts before SQL.
 *
 * @param ExtraFieldsReadHelper $reader Administrator custom-field reader.
 * @param string $template Extra-field template identifier.
 * @return void
 * @throws RuntimeException When a disallowed template returns any fields.
 */
function smAssertInvalidExtraFieldTemplate(ExtraFieldsReadHelper $reader, string $template): void
{
    $rows = $reader->getFields(17, 'clubinfo', $template);

    if ($rows !== []) {
        throw new RuntimeException('Invalid administrator extra-field template accepted: ' . $template);
    }
}

/**
 * Verify a calendar setter clamps malformed module settings to safe defaults.
 *
 * @param CalendarRenderer $renderer Calendar renderer being configured.
 * @param mixed $value Invalid start day or month from legacy settings.
 * @return void
 * @throws RuntimeException When the renderer retains malformed settings.
 */
function smAssertInvalidCalendarSetting(CalendarRenderer $renderer, mixed $value): void
{
    $renderer->setStartDay($value);
    $renderer->setStartMonth($value);

    if ($renderer->getStartDay() !== 0 || $renderer->getStartMonth() !== 1) {
        throw new RuntimeException('Invalid calendar settings were not rejected.');
    }
}

$reader = new ExtraFieldsReadHelper();

foreach (['', 'custom', 'frontend;DROP', 'back-end', 'backend_1'] as $template) {
    smAssertInvalidExtraFieldTemplate($reader, $template);
}

$renderer = new CalendarRenderer();

foreach ([null, false, true, [], new stdClass(), '-1', '13', '42invalid'] as $invalidSetting) {
    smAssertInvalidCalendarSetting($renderer, $invalidSetting);
}

$renderer->setStartDay('6');
$renderer->setStartMonth('12');

if ($renderer->getStartDay() !== 6 || $renderer->getStartMonth() !== 12) {
    throw new RuntimeException('Valid calendar settings were not retained.');
}

echo "Joomla 5/6 administrator extra-field and calendar settings regressions passed.\n";
