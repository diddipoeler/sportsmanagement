<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Results view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Results\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Results/HtmlView.php';
}

if (!class_exists(HtmlView::class)) {
    throw new \RuntimeException('SportsManagement native frontend Results view could not be loaded.', 500);
}

if (!class_exists('sportsmanagementViewResults', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewResults');
}
