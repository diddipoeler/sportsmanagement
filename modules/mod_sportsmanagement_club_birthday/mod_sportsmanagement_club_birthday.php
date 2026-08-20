<?php
/** Joomla 5/6 runtime entry point for club anniversaries. */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

require_once __DIR__ . '/helper.php';

$app = Factory::getApplication();
$app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);
$result = modSportsmanagementClubBirthdayHelper::getData($params);
$clubs = $result['clubs'];
$mode = $result['mode'];

require ModuleHelper::getLayoutPath($module->module, (string) $params->get('layout', 'default'));
