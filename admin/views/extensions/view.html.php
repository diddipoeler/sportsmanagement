<?php
/** Legacy compatibility bridge for the native administrator Extensions view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Extensions\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Extensions/HtmlView.php';
}

if (!class_exists('sportsmanagementViewextensions', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewextensions');
}
