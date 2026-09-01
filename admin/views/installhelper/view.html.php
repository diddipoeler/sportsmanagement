<?php
/** Legacy compatibility bridge for the native Joomla 5/6 administrator installation-helper view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Installhelper\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Installhelper/HtmlView.php';
}

if (!class_exists('sportsmanagementViewinstallhelper', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewinstallhelper');
}
