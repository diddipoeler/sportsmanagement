<?php
/** Legacy compatibility bridge for the native administrator database-tool view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Databasetool\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Databasetool/HtmlView.php';
}

if (!class_exists('sportsmanagementViewDatabaseTool', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewDatabaseTool');
}
