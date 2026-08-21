<?php
/**
 * SportsManagement legacy administrator view bridge.
 * The active Joomla 5/6 implementation lives in admin/src/View/Playgrounds/HtmlView.php.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Playgrounds\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Playgrounds/HtmlView.php';
}

if (!class_exists('sportsmanagementViewPlaygrounds', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewPlaygrounds');
}
