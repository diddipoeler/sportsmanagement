<?php
/**
 * Joomla 5/6 compatibility bridge for the SportsManagement Project Map helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementProjectMap\Site\Helper\ProjectMapHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

$nativeDependencies = [
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    ProjectMapHelper::class => __DIR__ . '/src/Helper/ProjectMapHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SiteRouteHelper::class,
    SportsManagementSiteApplicationResolver::class,
    ProjectMapHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement ProjectMap dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('modJSMprojectmaphelper', false)) {
    final class modJSMprojectmaphelper
    {
        private static function helper(): ProjectMapHelper
        {
            return new ProjectMapHelper();
        }

        public static function getmain_settings(): string
        {
            $helper = self::helper();

            return $helper->toJavascriptObjectBody($helper->getMainSettings());
        }

        public static function getData($seasonIds, ?DatabaseInterface $database = null): array
        {
            $app = SportsManagementSiteApplicationResolver::resolve();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement ProjectMap legacy helper requires the Joomla site application.', 500);
            }

            if ($database === null) {
                /** @var DatabaseInterface $database */
                $database = Factory::getContainer()->get(DatabaseInterface::class);
            }

            return self::helper()->getData($seasonIds, $database);
        }

        public static function createregions($projects): string
        {
            $helper = self::helper();

            return $helper->toJavascriptObjectBody($helper->createRegions((array) $projects));
        }

        public static function createstate_specific($projects): string
        {
            $helper = self::helper();

            return $helper->toJavascriptObjectBody($helper->createStateSpecific((array) $projects));
        }
    }
}
