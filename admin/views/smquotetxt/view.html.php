<?php
/** Legacy compatibility bridge for the native administrator Smquotetxt view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Smquotetxt\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Smquotetxt/HtmlView.php';
}

if (!class_exists('sportsmanagementViewsmquotetxt', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewsmquotetxt');
}
