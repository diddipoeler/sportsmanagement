<?php
/** Joomla 5/6 compatibility entry point for the birthday module. */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementBirthday\Site\Helper\BirthdayHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Database\DatabaseInterface;

if (!class_exists(BirthdayHelper::class)) {
    require_once __DIR__ . '/src/Helper/BirthdayHelper.php';
}

$app = Factory::getApplication();
$app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);
/** @var DatabaseInterface $database */
$database = Factory::getContainer()->get(DatabaseInterface::class);
$result = (new BirthdayHelper())->getData(
    $params,
    ComponentHelper::getParams('com_sportsmanagement'),
    $app,
    $database
);

$persons = $result['persons'];
$mode = $result['mode'];
$pictureServer = $result['pictureServer'];

if ($mode === 'B') {
    $app->getDocument()->getWebAssetManager()->useScript('bootstrap.carousel');
}

require ModuleHelper::getLayoutPath($module->module, (string) $params->get('layout', 'default'));
