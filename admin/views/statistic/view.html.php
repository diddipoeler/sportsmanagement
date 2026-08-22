<?php
/** Legacy compatibility bridge for the native Joomla 5/6 statistic view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Statistic\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Statistic/HtmlView.php';
}

if (!class_exists('sportsmanagementViewstatistic', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewstatistic');
}
