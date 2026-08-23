<?php
/** Legacy compatibility bridge for the native Joomla 5/6 curve raw view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Curve\RawView;

if (!class_exists(RawView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Curve/RawView.php';
}

if (!class_exists('sportsmanagementViewCurve', false)) {
    class_alias(RawView::class, 'sportsmanagementViewCurve');
}
