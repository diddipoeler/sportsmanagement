<?php
/**
 * Legacy helper bridge for the Joomla 5/6 SportsManagement playground ticker module.
 */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementPlaygroundTicker\Site\Helper\PlaygroundTickerHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;

if (!class_exists(PlaygroundTickerHelper::class)) {
    require_once __DIR__ . '/src/Helper/PlaygroundTickerHelper.php';
}

class modJSMPlaygroundTicker
{
    public static function getData($params): array
    {
        $container = Factory::getContainer();
        /** @var SiteApplication $app */
        $app = $container->get(SiteApplication::class);

        return (new PlaygroundTickerHelper())->getData($params, $app);
    }

    public static function getEstadios_Proyecto($params): array
    {
        return self::getData($params);
    }
}
