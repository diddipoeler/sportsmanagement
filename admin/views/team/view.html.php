<?php
/** Legacy compatibility bridge for the native administrator team editor. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Team\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Team/HtmlView.php';
}

if (!class_exists('sportsmanagementViewTeam', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewTeam');
}
