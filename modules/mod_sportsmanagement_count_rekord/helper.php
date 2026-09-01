<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 SportsManagement count record module.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementCountRekord\Site\Helper\CountRekordHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(CountRekordHelper::class)) {
    require_once __DIR__ . '/src/Helper/CountRekordHelper.php';
}

if (!class_exists('modJSMStatistikRekordHelper', false)) {
    final class modJSMStatistikRekordHelper
    {
        public static function getData($params, $module): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            $app = Factory::getApplication();
            /** @var DatabaseInterface $database */
            $database = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);

            return (new CountRekordHelper())->getData($registry, $module, $database);
        }
    }
}
