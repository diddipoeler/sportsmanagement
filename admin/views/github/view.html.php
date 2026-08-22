<?php
/** Legacy compatibility bridge for the native Joomla 5/6 GitHub administrator view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Github\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Github/HtmlView.php';
}

if (!class_exists('sportsmanagementViewgithub', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewgithub');
}
