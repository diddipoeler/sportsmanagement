<?php
/**
 * Legacy Joomla 5/6 administrator compatibility view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/** Legacy compatibility bridge for the native Joomla 5/6 administrator image list view. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Imagelist\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Imagelist/HtmlView.php';
}

if (!class_exists('sportsmanagementViewimagelist', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewimagelist');
}
