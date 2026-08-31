<?php
/** Legacy compatibility bridge for the native administrator Clubnames view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Clubnames\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Clubnames/HtmlView.php';
}

if (!class_exists('sportsmanagementViewClubnames', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewClubnames');
}
