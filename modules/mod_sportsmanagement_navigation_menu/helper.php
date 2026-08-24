<?php
/** Joomla 5/6 compatibility bridge for mod_sportsmanagement_navigation_menu. */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementNavigationMenu\Site\Helper\NativeNavigationMenuHelper;

if (!class_exists(NativeNavigationMenuHelper::class)) {
    require_once __DIR__ . '/src/Helper/NavigationMenuHelper.php';
    require_once __DIR__ . '/src/Helper/NativeNavigationMenuHelper.php';
}

if (!class_exists('modsportsmanagementNavigationMenuHelper', false)) {
    class_alias(NativeNavigationMenuHelper::class, 'modsportsmanagementNavigationMenuHelper');
}
