<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Roster\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementProjectHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Roster/HtmlView.php';
}

if (!class_exists('sportsmanagementViewRoster', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewRoster');
}
