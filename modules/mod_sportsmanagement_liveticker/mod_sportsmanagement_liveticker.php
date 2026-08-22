<?php
/** Legacy entry bridge for the Joomla 5/6 SportsManagement liveticker module. */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementLiveticker\Site\Helper\LivetickerHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

if (!class_exists(LivetickerHelper::class)) {
    require_once __DIR__ . '/src/Helper/LivetickerHelper.php';
}

$app = Factory::getApplication();
$data = (new LivetickerHelper())->getData($params, $app, $app->getInput());
extract($data, EXTR_SKIP);

require ModuleHelper::getLayoutPath(
    $module->module,
    (string) $params->get('layout', 'default')
);
