<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Eventsranking\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementProjectHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Eventsranking/HtmlView.php';
}

if (!class_exists('sportsmanagementViewEventsRanking', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewEventsRanking');
}
