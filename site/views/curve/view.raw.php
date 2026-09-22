<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 curve raw view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Curve\RawView;

if (!class_exists(RawView::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/View/Curve/RawView.php';
}

if (!class_exists('sportsmanagementViewCurve', false)) {
    class_alias(RawView::class, 'sportsmanagementViewCurve');
}
