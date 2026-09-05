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

use Diddipoeler\Module\SportsManagementPlaygroundTicker\Site\Helper\PlaygroundTickerHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;

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
