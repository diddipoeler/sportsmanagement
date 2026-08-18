<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/View/Predictionrules/HtmlView.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\View\Predictionrules\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/SportsManagementPredictionHtmlView.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Predictionrules/HtmlView.php';
}

if (!class_exists('sportsmanagementViewPredictionRules', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewPredictionRules');
}
