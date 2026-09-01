<?php
/** Legacy compatibility bridge for the native Joomla 5/6 tournament tree view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Treeto\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Treeto/HtmlView.php';
}

if (!class_exists('sportsmanagementViewTreeto', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewTreeto');
}
