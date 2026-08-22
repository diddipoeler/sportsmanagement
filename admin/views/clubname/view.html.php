<?php
/** Legacy compatibility bridge for the native Joomla 5/6 club-name administrator view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Clubname\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Clubname/HtmlView.php';
}

if (!class_exists('sportsmanagementViewclubname', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewclubname');
}
