<?php
/** Legacy compatibility bridge for the native administrator Smquote view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Smquote\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Smquote/HtmlView.php';
}

if (!class_exists('sportsmanagementViewsmquote', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewsmquote');
}
