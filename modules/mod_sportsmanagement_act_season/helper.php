<?php
/** Legacy helper facade for the Joomla 5/6 current-season module. */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementActSeason\Site\Helper\ActSeasonHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

if (!class_exists(ActSeasonHelper::class)) {
    require_once __DIR__ . '/src/Helper/ActSeasonHelper.php';
}

class modJSMActSeasonHelper
{
    public static function getData($seasonIds): array
    {
        return self::result($seasonIds)['list'];
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

    public static function getDataCcountryFederation(): array
    {
        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $result = self::result($componentParams->get('current_season', []));
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

    private static function result($seasonIds): array
    {
        $container = Factory::getContainer();
        /** @var SiteApplication $app */
        $app = $container->get(SiteApplication::class);
        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        /** @var DatabaseInterface $database */
        $database = $container->get(DatabaseInterface::class);

        return (new ActSeasonHelper())->getData(
            $seasonIds,
            $componentParams,
            $app,
            $database
        );
    }
}
