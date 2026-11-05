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
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

$nativeDependencies = [
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    ActSeasonHelper::class => __DIR__ . '/src/Helper/ActSeasonHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (class_exists($class)) {
        continue;
    }

    if (!is_file($file)) {
        throw new \RuntimeException('SportsManagement ActSeason dependency file is missing: ' . basename($file), 500);
    }

    require_once $file;
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
    /**
     * Retrieve the current-season project list for legacy module templates.
     *
     * Delegates to the native helper through result() and returns its
     * list collection, preserving the historic static module interface.
     *
     * @param mixed $seasonIds Selected seasons in the legacy input format.
     * @param DatabaseInterface|null $database Optional database override.
     * @return array Current-season project records for display.
     */
    public static function getData($seasonIds, ?DatabaseInterface $database = null): array
    {
        return self::result($seasonIds, $database)['list'];
    }

    /**
     * Build a unique federation index from current-season project records.
     *
     * Ignores invalid rows and federation IDs, then creates one entry
     * per federation with its identifier and available display name.
     *
     * @param array|mixed $data Project or season records containing federations.
     * @return array Federation objects indexed by their positive ID.
     */
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

    /**
     * Map current-season countries back to their associated federation IDs.
     *
     * Reads the component's active season setting, obtains country groups
     * from the native helper and converts them into legacy alpha3 and
     * federation pairs expected by existing module templates.
     *
     * @param DatabaseInterface|null $database Optional database service.
     * @return array Country and federation association records.
     */
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

    /**
     * Resolve all data required by the legacy current-season facade.
     *
     * Requires Joomla's frontend application, loads component settings,
     * obtains a validated database and delegates the season query to
     * the namespaced Joomla 5/6 ActSeasonHelper implementation.
     *
     * @param mixed $seasonIds Requested season identifiers.
     * @param DatabaseInterface|null $database Optional database override.
     * @return array Native current-season result collections.
     * @throws \RuntimeException If the site client or database is unavailable.
     */
    private static function result($seasonIds, ?DatabaseInterface $database = null): array
    {
        $app = self::siteApplication();

        $componentParams = ComponentHelper::getParams('com_sportsmanagement');

        $database = self::joomlaDatabase($database);

        return (new ActSeasonHelper())->getData(
            $seasonIds,
            $componentParams,
            $app,
            $database
        );
    }

    /**
     * Resolve a valid Joomla site application for current-season queries.
     *
     * Uses the shared component application resolver and rejects execution
     * in a non-site client before reading frontend module data.
     *
     * @return CMSApplicationInterface Current Joomla frontend application.
     * @throws \RuntimeException If the active client is not the site.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        $app = SportsManagementSiteApplicationResolver::resolve();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement ActSeason legacy helper requires the Joomla site application.', 500);
        }

        return $app;
    }

    /**
     * Resolve the effective Joomla database service for season queries.
     *
     * Accepts an injected DatabaseInterface or loads the service from
     * Joomla's container and rejects unexpected instances.
     *
     * @param DatabaseInterface|null $database Optional database override.
     * @return DatabaseInterface Validated database connection.
     * @throws \RuntimeException If no valid database service is available.
     */
    private static function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
    {
        if ($database !== null) {
            return $database;
        }

        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement ActSeason database service is unavailable.', 500);
        }

        return $database;
    }
    }
}


if (!class_exists('ModSportsmanagementActSeasonHelper', false)) {
    class_alias('modJSMActSeasonHelper', 'ModSportsmanagementActSeasonHelper');
}
