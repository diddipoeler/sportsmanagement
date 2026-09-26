<?php
/**
 * Legacy helper facade for the Joomla 5/6 current-season module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementActSeason\Site\Helper\ActSeasonHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

$nativeDependencies = [
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    ActSeasonHelper::class => __DIR__ . '/src/Helper/ActSeasonHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SportsManagementDatabaseResolver::class,
    SportsManagementSiteApplicationResolver::class,
    ActSeasonHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException('SportsManagement ActSeason dependency could not be loaded: ' . $requiredClass, 500);
    }
}

if (!class_exists('modJSMActSeasonHelper', false)) {
    final class modJSMActSeasonHelper
    {
    public static function getData($seasonIds, ?DatabaseInterface $database = null): array
    {
        return self::result($seasonIds, $database)['list'];
    }

    public static function getDataFederation($data): array
    {
        $federations = [];

        foreach ((array) $data as $row) {
            if (!is_object($row)) {
                continue;
            }

            $id = (int) ($row->federation ?? 0);
            if ($id <= 0 || isset($federations[$id])) {
                continue;
            }

            $federations[$id] = (object) [
                'id' => $id,
                'name' => (string) ($row->federation_name ?? $id),
            ];
        }

        return $federations;
    }

    public static function getDataCcountryFederation(?DatabaseInterface $database = null): array
    {
        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $result = self::result($componentParams->get('current_season', []), $database);
        $rows = [];

        foreach ($result['countriesByFederation'] as $federationId => $countries) {
            foreach ($countries as $country) {
                $rows[] = (object) [
                    'alpha3' => (string) ($country->alpha3 ?? ''),
                    'federation' => (int) $federationId,
                ];
            }
        }

        return $rows;
    }

    private static function result($seasonIds, ?DatabaseInterface $database = null): array
    {
        $app = SportsManagementSiteApplicationResolver::resolve();

        $componentParams = ComponentHelper::getParams('com_sportsmanagement');

        if ($database === null) {
            /** @var DatabaseInterface $database */
            $database = Factory::getContainer()->get(DatabaseInterface::class);
        }

        return (new ActSeasonHelper())->getData(
            $seasonIds,
            $componentParams,
            $app,
            $database
        );
    }
    }
}
