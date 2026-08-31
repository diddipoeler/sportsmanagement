<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction project view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Predictionproject\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Predictionproject/HtmlView.php';
}

if (!class_exists('sportsmanagementViewpredictionproject', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewpredictionproject');
}
