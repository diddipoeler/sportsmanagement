<?php
/**
 * Legacy helper bridge for the Joomla 5/6 SportsManagement playground ticker module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementPlaygroundTicker\Site\Helper\PlaygroundTickerHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    $resolverFile = JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php';

    if (is_file($resolverFile)) {
        require_once $resolverFile;
    }
}

if (!class_exists(PlaygroundTickerHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/PlaygroundTickerHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(PlaygroundTickerHelper::class)) {
    throw new \RuntimeException('SportsManagement native PlaygroundTicker module helper could not be loaded.', 500);
}

class modJSMPlaygroundTicker
{
    public static function getData($params): array
    {
        $app = SportsManagementSiteApplicationResolver::resolve();

        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        return (new PlaygroundTickerHelper())->getData($params, $app, $database);
    }

    public static function getEstadios_Proyecto($params): array
    {
        return self::getData($params);
    }
}
