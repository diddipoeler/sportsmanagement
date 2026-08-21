<?php
/** Legacy compatibility bridge for the native administrator Teams view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Teams\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Teams/HtmlView.php';
}

if (!class_exists('sportsmanagementViewTeams', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewTeams');
}
