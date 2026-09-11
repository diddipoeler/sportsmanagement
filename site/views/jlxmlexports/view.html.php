<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend XML export view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Jlxmlexports\HtmlView;

if (!class_exists(HtmlView::class)) {
    $nativeView = JPATH_SITE . '/components/com_sportsmanagement/src/View/Jlxmlexports/HtmlView.php';

    if (is_file($nativeView)) {
        require_once $nativeView;
    }
}

if (!class_exists(HtmlView::class)) {
    throw new \RuntimeException('SportsManagement native frontend XML export view could not be loaded.', 500);
}

if (!class_exists('sportsmanagementViewjlxmlexports', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewjlxmlexports');
}
