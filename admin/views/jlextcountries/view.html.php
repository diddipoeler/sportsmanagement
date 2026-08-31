<?php
/** Legacy compatibility bridge for the native administrator Jlextcountries view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Jlextcountries\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Jlextcountries/HtmlView.php';
}

if (!class_exists('sportsmanagementViewjlextcountries', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewjlextcountries');
}
