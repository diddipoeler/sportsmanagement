<?php
/**
 * Legacy entry bridge for the Joomla 5/6 SportsManagement New Project module.
 *
 * The active implementation is loaded through services/provider.php.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementNewProject\Site\Helper\NewProjectHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

if (!class_exists(NewProjectHelper::class)) {
    require_once __DIR__ . '/src/Helper/NewProjectHelper.php';
}

$app = Factory::getApplication();
$helper = new NewProjectHelper();
$list = $helper->getData($params, $app);
$canCreateArticles = $helper->canCreateArticles($params, $app);

require ModuleHelper::getLayoutPath('mod_sportsmanagement_new_project', 'native');
