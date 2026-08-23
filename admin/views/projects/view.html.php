<?php
/** Legacy compatibility bridge for the native administrator projects view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Projects\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Projects/HtmlView.php';
}

if (!class_exists('sportsmanagementViewProjects', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewProjects');
}
