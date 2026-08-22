<?php
/** Legacy compatibility bridge for the native Joomla 5/6 league administrator view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\League\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/League/HtmlView.php';
}

if (!class_exists('sportsmanagementViewLeague', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewLeague');
}
