<?php
/** Legacy compatibility bridge for the native administrator Eventtypes view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Eventtypes\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Eventtypes/HtmlView.php';
}

if (!class_exists('sportsmanagementViewEventtypes', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewEventtypes');
}
