<?php
/** Legacy compatibility bridge for the native administrator Smextxmleditors view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Smextxmleditors\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Smextxmleditors/HtmlView.php';
}

if (!class_exists('sportsmanagementViewsmextxmleditors', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewsmextxmleditors');
}
