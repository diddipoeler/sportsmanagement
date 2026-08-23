<?php
/** Legacy compatibility bridge for the native administrator teamplayers view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Teamplayers\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Teamplayers/HtmlView.php';
}

if (!class_exists('sportsmanagementViewteamplayers', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewteamplayers');
}
