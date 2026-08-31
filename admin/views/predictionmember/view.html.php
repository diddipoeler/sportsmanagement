<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction member edit view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Predictionmember\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Predictionmember/HtmlView.php';
}

if (!class_exists('sportsmanagementViewpredictionmember', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewpredictionmember');
}
