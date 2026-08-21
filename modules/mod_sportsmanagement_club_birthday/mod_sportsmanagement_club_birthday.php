<?php
/**
 * Legacy entry bridge for the Joomla 5/6 SportsManagement club birthday module.
 *
 * The active implementation is loaded through services/provider.php.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementClubBirthday\Site\Helper\ClubBirthdayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Database\DatabaseInterface;

if (!class_exists(ClubBirthdayHelper::class)) {
    require_once __DIR__ . '/src/Helper/ClubBirthdayHelper.php';
}

$app = Factory::getApplication();
$app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);
/** @var DatabaseInterface $database */
$database = Factory::getContainer()->get(DatabaseInterface::class);
$result = (new ClubBirthdayHelper())->getData($params, $app, $database);
$clubs = $result['clubs'];
$mode = $result['mode'];

require ModuleHelper::getLayoutPath($module->module, (string) $params->get('layout', 'default'));
