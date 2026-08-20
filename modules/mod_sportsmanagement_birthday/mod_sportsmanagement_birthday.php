<?php
/** Joomla 5/6 compatibility entry point for the birthday module. */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementBirthday\Site\Helper\BirthdayHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

if (!class_exists(BirthdayHelper::class)) {
    require_once __DIR__ . '/src/Helper/BirthdayHelper.php';
}

$app = Factory::getApplication();
$app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);
$componentParams = ComponentHelper::getParams('com_sportsmanagement');
$result = (new BirthdayHelper())->getData($params, $componentParams, $app);

$persons = $result['persons'];
$mode = $result['mode'];
$pictureServer = $result['pictureServer'];

require ModuleHelper::getLayoutPath($module->module, (string) $params->get('layout', 'default'));
