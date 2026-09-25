<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend JSON view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Component\SportsManagement\Site\View\Json\HtmlView;

if (!class_exists(HtmlView::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/View/Json/HtmlView.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(HtmlView::class)) {
    throw new \RuntimeException('SportsManagement native frontend JSON view could not be loaded.', 500);
}

if (!class_exists('sportsmanagementViewjson', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewjson');
}
