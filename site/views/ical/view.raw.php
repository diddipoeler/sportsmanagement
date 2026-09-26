<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 iCal raw view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\Ical\RawView;

if (!class_exists(RawView::class)) {
    $nativeView = JPATH_SITE . '/components/com_sportsmanagement/src/View/Ical/RawView.php';

    if (is_file($nativeView)) {
        require_once $nativeView;
    }
}

if (!class_exists(RawView::class)) {
    throw new \RuntimeException('SportsManagement native iCal raw view could not be loaded.', 500);
}

if (!class_exists('sportsmanagementViewIcal', false)) {
    class_alias(RawView::class, 'sportsmanagementViewIcal');
}
