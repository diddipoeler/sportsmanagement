<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend SportsManagement root view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Sportsmanagement\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Sportsmanagement/HtmlView.php';
}

if (!class_exists(HtmlView::class)) {
    throw new \RuntimeException('SportsManagement native frontend root view could not be loaded.', 500);
}

if (!class_exists('sportsmanagementViewsportsmanagement', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewsportsmanagement');
}
