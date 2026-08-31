<?php
/** Legacy compatibility bridge for the native Joomla 5/6 administrator Updates view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Updates\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Updates/HtmlView.php';
}

if (!class_exists('sportsmanagementViewUpdates', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewUpdates');
}
