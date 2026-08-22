<?php
/** Joomla 5/6 compatibility bridge for mod_sportsmanagement_navigation_menu. */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementNavigationMenu\Site\Helper\NavigationMenuHelper;

if (!class_exists(NavigationMenuHelper::class)) {
    require_once __DIR__ . '/src/Helper/NavigationMenuHelper.php';
}

if (!class_exists('modsportsmanagementNavigationMenuHelper', false)) {
    class_alias(NavigationMenuHelper::class, 'modsportsmanagementNavigationMenuHelper');
}
