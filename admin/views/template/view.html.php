<?php
/** Legacy compatibility bridge for the native Joomla 5/6 project template edit view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Template\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Template/HtmlView.php';
}

if (!class_exists('sportsmanagementViewTemplate', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewTemplate');
}
