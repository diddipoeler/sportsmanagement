<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction members view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Predictionmembers\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Predictionmembers/HtmlView.php';
}

if (!class_exists('sportsmanagementViewPredictionMembers', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewPredictionMembers');
}
