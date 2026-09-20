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

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementProjectMap\Site\Helper\ProjectMapHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    $resolverFile = JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php';

    if (is_file($resolverFile)) {
        require_once $resolverFile;
    }
}

if (!class_exists(ProjectMapHelper::class)) {
    require_once __DIR__ . '/src/Helper/ProjectMapHelper.php';
}

if (!class_exists(ProjectMapHelper::class)) {
    throw new \RuntimeException('SportsManagement Project Map helper could not be loaded.', 500);
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

        public static function getData($seasonIds): array
        {
            $app = SportsManagementSiteApplicationResolver::resolve();

            if (!$app instanceof SiteApplication || !$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement ProjectMap legacy helper requires the Joomla site application.', 500);
            }

            /** @var DatabaseInterface $db */
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            return self::helper()->getData($seasonIds, $db);
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
