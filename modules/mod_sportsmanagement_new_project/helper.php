<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 SportsManagement New Project module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementNewProject\Site\Helper\NewProjectHelper;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    NewProjectHelper::class => __DIR__ . '/src/Helper/NewProjectHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(NewProjectHelper::class)) {
    throw new \RuntimeException('SportsManagement New Project helper could not be loaded.', 500);
}

if (!class_exists('modJSMNewProjectHelper', false)) {
    final class modJSMNewProjectHelper
    {
        /**
         * Compatibility adapter for extensions still calling the historical helper signature.
         */
        public static function getData($newProjectArticle = 0, $categoryId = 0): array
        {
            $params = new Registry([
                'new_project_article' => (int) $newProjectArticle,
                'mycategory' => (int) $categoryId,
            ]);
            $app = SportsManagementSiteApplicationResolver::resolve();

            $rows = (new NewProjectHelper())->getData($params, $app);
            $result = [];

            foreach ($rows as $row) {
                $legacy = clone $row;
                $legacy->id = (string) ($row->project_slug ?? $row->id ?? '');
                $legacy->liganame = (string) ($row->league_name ?? '');
                $legacy->roundcode = (string) ($row->round_slug ?? '');
                $result[] = $legacy;
            }

            return $result;
        }
    }
}
