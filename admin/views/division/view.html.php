<?php
/** Legacy compatibility bridge for the native Joomla 5/6 division administrator view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Division\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Division/HtmlView.php';
}

if (!class_exists('sportsmanagementViewDivision', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewDivision');
}
