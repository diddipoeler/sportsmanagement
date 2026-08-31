<?php
/** Legacy compatibility bridge for the native administrator Jlextassociations view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Jlextassociations\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Jlextassociations/HtmlView.php';
}

if (!class_exists('sportsmanagementViewjlextassociations', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewjlextassociations');
}
