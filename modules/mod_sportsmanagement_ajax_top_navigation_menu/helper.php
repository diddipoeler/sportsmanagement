<?php
/**
 * Legacy compatibility facade for the namespaced Joomla 5/6 AJAX navigation data helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementAjaxTopNavigationMenu\Site\Helper\NavigationDataHelper;

if (!class_exists(NavigationDataHelper::class)) {
    require_once __DIR__ . '/src/Helper/NavigationDataHelper.php';
}

if (!class_exists('modSportsmanagementAjaxTopNavigationMenuHelper', false)) {
    class_alias(NavigationDataHelper::class, 'modSportsmanagementAjaxTopNavigationMenuHelper');
}
