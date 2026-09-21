<?php
/**
 * Legacy Joomla 5/6 administrator compatibility view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/** Legacy compatibility bridge for the native Joomla 5/6 prediction groups view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Predictiongroups\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Predictiongroups/HtmlView.php';
}

if (!class_exists('sportsmanagementViewpredictiongroups', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewpredictiongroups');
}
