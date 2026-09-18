<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 SportsManagement administrator list view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) 2013-2026 Fussball in Europa
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Sportsmanagements\HtmlView;

if (!class_exists(HtmlView::class)) {
    $nativeView = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Sportsmanagements/HtmlView.php';

    if (is_file($nativeView)) {
        require_once $nativeView;
    }
}

if (!class_exists(HtmlView::class)) {
    throw new \RuntimeException('SportsManagement native administrator list view could not be loaded.', 500);
}

if (!class_exists('sportsmanagementViewsportsmanagements', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewsportsmanagements');
}
