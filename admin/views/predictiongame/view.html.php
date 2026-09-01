<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction game editor. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Predictiongame\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Predictiongame/HtmlView.php';
}

if (!class_exists('sportsmanagementViewPredictionGame', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewPredictionGame');
}
