<?php
/**
 * Joomla 5/6 compatibility bridge for mod_sportsmanagement_navigation_menu.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementNavigationMenu\Site\Helper\NavigationMenuHelper;
use Diddipoeler\Module\SportsManagementNavigationMenu\Site\Helper\NativeNavigationMenuHelper;

if (!class_exists(NavigationMenuHelper::class)) {
    $baseHelper = __DIR__ . '/src/Helper/NavigationMenuHelper.php';

    if (is_file($baseHelper)) {
        require_once $baseHelper;
    }
}

if (!class_exists(NativeNavigationMenuHelper::class) && class_exists(NavigationMenuHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/NativeNavigationMenuHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(NativeNavigationMenuHelper::class)) {
    throw new \RuntimeException('SportsManagement Navigation Menu helper could not be loaded.', 500);
}

if (!class_exists('modsportsmanagementNavigationMenuHelper', false)) {
    class_alias(NativeNavigationMenuHelper::class, 'modsportsmanagementNavigationMenuHelper');
}
