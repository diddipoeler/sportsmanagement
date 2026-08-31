<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction rounds view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Predictionrounds\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Predictionrounds/HtmlView.php';
}

if (!class_exists('sportsmanagementViewPredictionRounds', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewPredictionRounds');
}
