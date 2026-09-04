<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 SportsManagement count record module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementCountRekord\Site\Helper\CountRekordHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(CountRekordHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/CountRekordHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(CountRekordHelper::class)) {
    throw new \RuntimeException('SportsManagement CountRekord helper could not be loaded.', 500);
}

if (!class_exists('modJSMStatistikRekordHelper', false)) {
    final class modJSMStatistikRekordHelper
    {
        public static function getData($params, $module): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            /** @var DatabaseInterface $database */
            $database = Factory::getContainer()->get(DatabaseInterface::class);

            return (new CountRekordHelper())->getData($registry, $module, $database);
        }
    }
}
