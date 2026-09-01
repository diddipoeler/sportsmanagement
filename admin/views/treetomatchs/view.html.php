<?php
/** Legacy compatibility bridge for the native Joomla 5/6 tournament-tree match assignments view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Treetomatchs\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Treetomatchs/HtmlView.php';
}

if (!class_exists('sportsmanagementViewTreetomatchs', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewTreetomatchs');
}
