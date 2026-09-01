<?php
/** Legacy compatibility bridge for the native Joomla 5/6 prediction templates view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Predictiontemplates\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Predictiontemplates/HtmlView.php';
}

if (!class_exists('sportsmanagementViewPredictionTemplates', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewPredictionTemplates');
}
