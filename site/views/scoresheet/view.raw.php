<?php
/** Legacy compatibility bridge for the native Joomla 5/6 scoresheet raw view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Scoresheet\RawView;

if (!class_exists(RawView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Scoresheet/RawView.php';
}

if (!class_exists('sportsmanagementViewScoresheet', false)) {
    class_alias(RawView::class, 'sportsmanagementViewScoresheet');
}
