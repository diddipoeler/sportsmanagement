<?php
/** Legacy compatibility bridge for the native Joomla 5/6 playground view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Playground\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Playground/HtmlView.php';
}

if (!class_exists('sportsmanagementViewPlayground', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewPlayground');
}
