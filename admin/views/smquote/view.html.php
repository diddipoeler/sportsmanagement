<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Smquote view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Smquote\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Smquote/HtmlView.php';
}

if (!class_exists(HtmlView::class)) {
    throw new \RuntimeException('SportsManagement native administrator Smquote view could not be loaded.', 500);
}

if (!class_exists('sportsmanagementViewsmquote', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewsmquote');
}
