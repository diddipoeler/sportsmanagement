<?php
/** Legacy compatibility bridge for the native administrator Jlextassociation view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Jlextassociation\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Jlextassociation/HtmlView.php';
}

if (!class_exists('sportsmanagementViewJlextassociation', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewJlextassociation');
}
