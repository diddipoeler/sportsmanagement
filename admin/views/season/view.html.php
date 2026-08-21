<?php
/**
 * SportsManagement legacy administrator view bridge.
 * The active Joomla 5/6 implementation lives in admin/src/View/Season/HtmlView.php.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Season\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Season/HtmlView.php';
}

if (!class_exists('sportsmanagementViewSeason', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewSeason');
}
