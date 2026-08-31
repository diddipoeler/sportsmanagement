<?php
/** Legacy compatibility bridge for the native Joomla 5/6 administrator Sportstypes view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Sportstypes\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Sportstypes/HtmlView.php';
}

if (!class_exists('sportsmanagementViewSportsTypes', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewSportsTypes');
}
