<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Close view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Close\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Close/HtmlView.php';
}

if (!class_exists(HtmlView::class)) {
    throw new \RuntimeException('SportsManagement native administrator Close view could not be loaded.', 500);
}

if (!class_exists('sportsmanagementViewClose', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewClose');
}
