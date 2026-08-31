<?php
/** Legacy compatibility bridge for the native administrator databasetools view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Databasetools\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Databasetools/HtmlView.php';
}

if (!class_exists('sportsmanagementViewDatabaseTools', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewDatabaseTools');
}
