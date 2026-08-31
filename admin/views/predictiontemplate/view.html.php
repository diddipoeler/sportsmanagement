<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction template view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Predictiontemplate\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Predictiontemplate/HtmlView.php';
}

if (!class_exists('sportsmanagementViewPredictionTemplate', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewPredictionTemplate');
}
