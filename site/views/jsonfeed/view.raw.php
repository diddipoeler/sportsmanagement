<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 JSON raw view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Jsonfeed\RawView;

if (!class_exists(RawView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Jsonfeed/RawView.php';
}

if (!class_exists('sportsmanagementViewJSONFeed', false)) {
    class_alias(RawView::class, 'sportsmanagementViewJSONFeed');
}
