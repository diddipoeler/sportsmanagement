<?php
/** Legacy compatibility bridge for the native administrator agegroups view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Agegroups\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Agegroups/HtmlView.php';
}

if (!class_exists('sportsmanagementViewagegroups', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewagegroups');
}
