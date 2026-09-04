<?php
/**
 * SportsManagement Joomla 5/6 migration.
 *
 * @version    5.6.0 sportsmanagement
 * @author     diddipoeler <diddipoeler@gmx.de>
 * @copyright  Copyright (C) diddipoeler. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Projects\HtmlView;

if (!class_exists(HtmlView::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/View/Projects/HtmlView.php';
}

if (!class_exists('sportsmanagementViewProjects', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewProjects');
}
