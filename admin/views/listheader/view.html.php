<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Listheader view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Listheader\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Listheader/HtmlView.php';
}

if (!class_exists(HtmlView::class)) {
    throw new \RuntimeException('SportsManagement native administrator Listheader view could not be loaded.', 500);
}

if (!class_exists('sportsmanagementViewlistheader', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewlistheader');
}
