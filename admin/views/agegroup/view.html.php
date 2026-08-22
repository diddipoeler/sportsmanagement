<?php
/** Legacy compatibility bridge for the native Joomla 5/6 age-group administrator view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Agegroup\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Agegroup/HtmlView.php';
}

if (!class_exists('sportsmanagementViewagegroup', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewagegroup');
}
