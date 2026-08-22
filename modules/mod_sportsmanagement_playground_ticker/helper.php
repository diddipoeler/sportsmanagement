<?php
/**
 * Legacy helper bridge for the Joomla 5/6 SportsManagement playground ticker module.
 */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementPlaygroundTicker\Site\Helper\PlaygroundTickerHelper;
use Joomla\CMS\Factory;

if (!class_exists(PlaygroundTickerHelper::class)) {
    require_once __DIR__ . '/src/Helper/PlaygroundTickerHelper.php';
}

class modJSMPlaygroundTicker
{
    public static function getData($params): array
    {
        return (new PlaygroundTickerHelper())->getData($params, Factory::getApplication());
    }

    public static function getEstadios_Proyecto($params): array
    {
        return self::getData($params);
    }
}
