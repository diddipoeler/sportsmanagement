<?php
/** Legacy compatibility bridge for the native administrator Smquotestxt view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Smquotestxt\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Smquotestxt/HtmlView.php';
}

if (!class_exists('sportsmanagementViewsmquotestxt', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewsmquotestxt');
}
