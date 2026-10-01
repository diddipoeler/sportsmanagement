<?php
/**
 * Legacy Joomla 5/6 administrator compatibility view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/** Legacy compatibility bridge for the native Joomla 5/6 round administrator view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Round\HtmlView;

if (!class_exists(HtmlView::class)) {
    $nativeView = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Round/HtmlView.php';

    if (is_file($nativeView)) {
        require_once $nativeView;
    }
}

if (!class_exists(HtmlView::class)) {
    throw new \RuntimeException('SportsManagement native administrator Round view could not be loaded.', 500);
}

if (!class_exists('sportsmanagementViewRound', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewRound');
}
