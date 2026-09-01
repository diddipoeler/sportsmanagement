<?php
/** Legacy compatibility bridge for the native Joomla 5/6 predictions dashboard. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Predictions\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Predictions/HtmlView.php';
}

if (!class_exists('sportsmanagementViewpredictions', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewpredictions');
}
