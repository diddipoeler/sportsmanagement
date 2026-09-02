<?php
/**
 * Joomla 5/6 compatibility bridge for mod_sportsmanagement_navigation_menu.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementNavigationMenu\Site\Helper\NativeNavigationMenuHelper;

if (!class_exists(NativeNavigationMenuHelper::class)) {
    require_once __DIR__ . '/src/Helper/NavigationMenuHelper.php';
    require_once __DIR__ . '/src/Helper/NativeNavigationMenuHelper.php';
}

if (!class_exists('modsportsmanagementNavigationMenuHelper', false)) {
    class_alias(NativeNavigationMenuHelper::class, 'modsportsmanagementNavigationMenuHelper');
}
