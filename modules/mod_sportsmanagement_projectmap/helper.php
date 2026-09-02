<?php
/**
 * Joomla 5/6 compatibility bridge for the SportsManagement Project Map helper.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
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
