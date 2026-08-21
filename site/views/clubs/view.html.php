<?php
/**
 * SportsManagement legacy view bridge.
 * The active Joomla 5/6 implementation lives in site/src/View/Clubs/HtmlView.php.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Clubs\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementProjectHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Clubs/HtmlView.php';
}

if (!class_exists('sportsmanagementViewClubs', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewClubs');
}
