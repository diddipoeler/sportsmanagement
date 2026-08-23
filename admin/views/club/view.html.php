<?php
/** Legacy compatibility bridge for the native Joomla 5/6 club edit view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Club\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Club/HtmlView.php';
}

if (!class_exists('sportsmanagementViewClub', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewClub');
}
