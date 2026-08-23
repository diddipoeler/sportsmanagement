<?php
/** Legacy compatibility bridge for the native administrator project view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Project\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Project/HtmlView.php';
}

if (!class_exists('sportsmanagementViewProject', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewProject');
}
