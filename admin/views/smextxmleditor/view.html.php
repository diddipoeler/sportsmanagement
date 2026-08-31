<?php
/** Legacy compatibility bridge for the native administrator Smextxmleditor view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Smextxmleditor\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Smextxmleditor/HtmlView.php';
}

if (!class_exists('sportsmanagementViewsmextxmleditor', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewsmextxmleditor');
}
