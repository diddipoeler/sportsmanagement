<?php
/** Legacy compatibility bridge for the native Joomla 5/6 image handler view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Imagehandler\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Imagehandler/HtmlView.php';
}

if (!class_exists('sportsmanagementViewImagehandler', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewImagehandler');
}
