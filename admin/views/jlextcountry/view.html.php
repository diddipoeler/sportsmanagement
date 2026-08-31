<?php
/** Legacy compatibility bridge for the native administrator Jlextcountry view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Jlextcountry\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Jlextcountry/HtmlView.php';
}

if (!class_exists('sportsmanagementViewJlextcountry', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewJlextcountry');
}
