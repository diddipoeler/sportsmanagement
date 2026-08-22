<?php
/** Legacy compatibility bridge for the native Joomla 5/6 Teams view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Teams\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementProjectHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Teams/HtmlView.php';
}

if (!class_exists('sportsmanagementViewTeams', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewTeams');
}
