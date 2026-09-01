<?php
/** Legacy compatibility bridge for the native Joomla 5/6 administrator JoomLeague imports view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Joomleagueimports\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Joomleagueimports/HtmlView.php';
}

if (!class_exists('sportsmanagementViewjoomleagueimports', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewjoomleagueimports');
}
