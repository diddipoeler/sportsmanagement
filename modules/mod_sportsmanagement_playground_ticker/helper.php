<?php
/**
 * Legacy helper bridge for the Joomla 5/6 SportsManagement playground ticker module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementPlaygroundTicker\Site\Helper\PlaygroundTickerHelper;
use Joomla\Database\DatabaseInterface;

$nativeDependencies = [
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    PlaygroundTickerHelper::class => __DIR__ . '/src/Helper/PlaygroundTickerHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SportsManagementDatabaseResolver::class,
    SportsManagementSiteApplicationResolver::class,
    PlaygroundTickerHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException('SportsManagement PlaygroundTicker dependency could not be loaded: ' . $requiredClass, 500);
    }
}

class modJSMPlaygroundTicker
{
    public static function getData($params, ?DatabaseInterface $database = null): array
    {
        $app = SportsManagementSiteApplicationResolver::resolve();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement PlaygroundTicker legacy helper requires the Joomla site application.', 500);
        }

        if ($database === null) {
            /** @var DatabaseInterface $database */
            $database = Factory::getContainer()->get(DatabaseInterface::class);
        }

        return (new PlaygroundTickerHelper())->getData($params, $app, $database);
    }

    public static function getEstadios_Proyecto($params, ?DatabaseInterface $database = null): array
    {
        return self::getData($params, $database);
    }
}
