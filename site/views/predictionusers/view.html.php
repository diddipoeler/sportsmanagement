<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction-users view. */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\View\Predictionusers\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementPredictionHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Predictionusers/HtmlView.php';
}

if (!class_exists('sportsmanagementViewPredictionUsers', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewPredictionUsers');
}
