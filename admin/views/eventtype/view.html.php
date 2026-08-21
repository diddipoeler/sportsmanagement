<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/View/Eventtype/HtmlView.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\View\Eventtype\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Eventtype/HtmlView.php';
}

if (!class_exists('sportsmanagementVieweventtype', false)) {
    class_alias(HtmlView::class, 'sportsmanagementVieweventtype');
}
