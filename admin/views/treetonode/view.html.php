<?php
/** Legacy compatibility bridge for the native Joomla 5/6 tournament tree node view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Treetonode\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Treetonode/HtmlView.php';
}

if (!class_exists('sportsmanagementViewTreetonode', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewTreetonode');
}
