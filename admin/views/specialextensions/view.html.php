<?php
/** Legacy compatibility bridge for the native Joomla 5/6 administrator special extensions view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Specialextensions\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Specialextensions/HtmlView.php';
}

if (!class_exists('sportsmanagementViewspecialextensions', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewspecialextensions');
}
