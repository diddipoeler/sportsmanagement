<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction-entry view. */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\View\Predictionentry\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementPredictionHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Predictionentry/HtmlView.php';
}

if (!class_exists('sportsmanagementViewPredictionEntry', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewPredictionEntry');
}
