<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Teaminfo\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementProjectHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Teaminfo/HtmlView.php';
}

if (!class_exists('sportsmanagementViewTeamInfo', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewTeamInfo');
}
