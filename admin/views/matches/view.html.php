<?php
/**
 * Legacy Joomla 5/6 matches administrator view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/** Legacy compatibility bridge for the native administrator matches view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Matches\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Matches/HtmlView.php';
}

if (!class_exists('sportsmanagementViewMatches', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewMatches');
}
