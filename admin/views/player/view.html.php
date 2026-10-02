<?php
/**
 * Legacy Joomla 5/6 player administrator view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/** Legacy compatibility bridge for the native administrator player view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Player\HtmlView;

if (!class_exists(HtmlView::class)) {
    $nativeView = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Player/HtmlView.php';

    if (is_file($nativeView)) {
        require_once $nativeView;
    }
}

if (!class_exists(HtmlView::class)) {
    throw new \RuntimeException('SportsManagement native administrator Player view could not be loaded.', 500);
}

if (!class_exists('sportsmanagementViewplayer', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewplayer');
}
