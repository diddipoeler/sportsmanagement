<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/** Legacy compatibility bridge for the native Joomla 5/6 Google Calendar landing view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Jsmgooglecalendar\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Jsmgooglecalendar/HtmlView.php';
}

if (!class_exists('sportsmanagementViewjsmgooglecalendar', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewjsmgooglecalendar');
}
