<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 calendar renderer.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarRenderer;

if (!class_exists(CalendarRenderer::class)) {
    require_once __DIR__ . '/src/Runtime/CalendarRenderer.php';
}

if (!class_exists('PHPCalendar', false)) {
    class_alias(CalendarRenderer::class, 'PHPCalendar');
}
