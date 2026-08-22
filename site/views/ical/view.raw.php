<?php
/** Legacy compatibility bridge for the native Joomla 5/6 iCal raw view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Ical\RawView;

if (!class_exists(RawView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Ical/RawView.php';
}

if (!class_exists('sportsmanagementViewIcal', false)) {
    class_alias(RawView::class, 'sportsmanagementViewIcal');
}
