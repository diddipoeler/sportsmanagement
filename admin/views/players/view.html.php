<?php
/** Legacy compatibility bridge for the native administrator players view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Players\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Players/HtmlView.php';
}

if (!class_exists('sportsmanagementViewplayers', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewplayers');
}
