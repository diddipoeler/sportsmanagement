<?php
/** Legacy compatibility bridge for the native administrator rounds view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Rounds\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Rounds/HtmlView.php';
}

if (!class_exists('sportsmanagementViewRounds', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewRounds');
}
