<?php
/** Legacy compatibility bridge for the native administrator Jlextfederations view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Jlextfederations\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Jlextfederations/HtmlView.php';
}

if (!class_exists('sportsmanagementViewjlextfederations', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewjlextfederations');
}
