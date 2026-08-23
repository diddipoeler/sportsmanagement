<?php
/** Legacy compatibility bridge for the native administrator projectteams view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Projectteams\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Projectteams/HtmlView.php';
}

if (!class_exists('sportsmanagementViewprojectteams', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewprojectteams');
}
