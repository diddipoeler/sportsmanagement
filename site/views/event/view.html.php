<?php
/** Legacy compatibility bridge for the native Joomla 5/6 event view. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\View\Event\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Event/HtmlView.php';
}

if (!class_exists('sportsmanagementViewEvent', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewEvent');
}
