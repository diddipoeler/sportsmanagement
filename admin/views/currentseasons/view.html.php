<?php
/**
 * SportsManagement legacy administrator view bridge.
 * The active Joomla 5/6 implementation lives in admin/src/View/Currentseasons/HtmlView.php.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Currentseasons\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Currentseasons/HtmlView.php';
}

if (!class_exists('sportsmanagementViewCurrentseasons', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewCurrentseasons');
}
