<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction games view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Predictiongames\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Predictiongames/HtmlView.php';
}

if (!class_exists('sportsmanagementViewPredictionGames', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewPredictionGames');
}
