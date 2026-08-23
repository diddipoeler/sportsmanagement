<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction ranking view. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\View\Predictionranking\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementPredictionHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Predictionranking/HtmlView.php';
}

if (!class_exists('sportsmanagementViewpredictionranking', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewpredictionranking');
}
