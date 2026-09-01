<?php
/** Legacy compatibility bridge for the native Joomla 5/6 statistics view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Statistics\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Statistics/HtmlView.php';
}

if (!class_exists('sportsmanagementViewStatistics', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewStatistics');
}
