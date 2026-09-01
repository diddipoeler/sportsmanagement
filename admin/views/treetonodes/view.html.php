<?php
/** Legacy compatibility bridge for the native Joomla 5/6 tournament tree nodes view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Treetonodes\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Treetonodes/HtmlView.php';
}

if (!class_exists('sportsmanagementViewTreetonodes', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewTreetonodes');
}
