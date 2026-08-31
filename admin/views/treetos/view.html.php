<?php
/** Legacy compatibility bridge for the native Joomla 5/6 tournament trees view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Treetos\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Treetos/HtmlView.php';
}

if (!class_exists('sportsmanagementViewTreetos', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewTreetos');
}
