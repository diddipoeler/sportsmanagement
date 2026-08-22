<?php
/** Legacy compatibility bridge for the native Joomla 5/6 sports-type administrator view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Sportstype\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Sportstype/HtmlView.php';
}

if (!class_exists('sportsmanagementViewSportsType', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewSportsType');
}
