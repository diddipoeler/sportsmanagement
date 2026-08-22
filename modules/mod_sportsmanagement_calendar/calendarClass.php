<?php
/** Legacy compatibility bridge for the native Joomla 5/6 calendar renderer. */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarRenderer;

if (!class_exists(CalendarRenderer::class)) {
    require_once __DIR__ . '/src/Runtime/CalendarRenderer.php';
}

if (!class_exists('PHPCalendar', false)) {
    class_alias(CalendarRenderer::class, 'PHPCalendar');
}
