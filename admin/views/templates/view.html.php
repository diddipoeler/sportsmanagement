<?php
/** Legacy compatibility bridge for the native Joomla 5/6 project templates view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Templates\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Templates/HtmlView.php';
}

if (!class_exists('sportsmanagementViewTemplates', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewTemplates');
}
