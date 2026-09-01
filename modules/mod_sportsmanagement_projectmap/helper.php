<?php
/**
 * Legacy helper bridge for third-party overrides.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementProjectMap\Site\Helper\ProjectMapHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

if (!class_exists(ProjectMapHelper::class)) {
    require_once __DIR__ . '/src/Helper/ProjectMapHelper.php';
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
            $app = Factory::getApplication();
            /** @var DatabaseInterface $db */
            $db = $app->getContainer()->get(DatabaseInterface::class);

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
