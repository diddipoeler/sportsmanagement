<?php
/** Joomla 5/6 compatibility entry point for the birthday module. */
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

require_once __DIR__ . '/helper.php';

$app = Factory::getApplication();
$app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);
$result = (new modSportsmanagementBirthdayDataHelper())->getData(
    $params,
    ComponentHelper::getParams('com_sportsmanagement'),
    $app
);

$persons = $result['persons'];
$mode = $result['mode'];
$pictureServer = $result['pictureServer'];

require ModuleHelper::getLayoutPath($module->module, (string) $params->get('layout', 'default'));
