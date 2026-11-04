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
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementNewProject\Site\Helper\NewProjectHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    NewProjectHelper::class => __DIR__ . '/src/Helper/NewProjectHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SiteRouteHelper::class,
    SportsManagementDatabaseResolver::class,
    SportsManagementSiteApplicationResolver::class,
    NewProjectHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException('SportsManagement New Project dependencies could not be loaded: ' . $requiredClass, 500);
    }
}

if (!class_exists('modJSMNewProjectHelper', false)) {
    final class modJSMNewProjectHelper
    {
        /**
         * Retrieve project records for older module templates and integrations.
         *
         * Converts the historical article and category parameters into a
         * Joomla Registry, uses the native site helper to query projects,
         * and maps project, league and round fields onto legacy aliases.
         *
         * @param int|string $newProjectArticle Optional article identifier.
         * @param int|string $categoryId Optional category identifier.
         * @return array Project records adapted for the legacy template.
         */
        public static function getData($newProjectArticle = 0, $categoryId = 0): array
        {
            $params = new Registry([
                'new_project_article' => (int) $newProjectArticle,
                'mycategory' => (int) $categoryId,
            ]);
            $app = self::siteApplication();

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

        /**
         * Resolve the Joomla site application for the legacy module adapter.
         *
         * Delegates to the component's shared application resolver and
         * refuses to execute outside the public Joomla site client.
         *
         * @return CMSApplicationInterface Current Joomla frontend application.
         * @throws \RuntimeException If the active client is not the site.
         */
        private static function siteApplication(): CMSApplicationInterface
        {
            $app = SportsManagementSiteApplicationResolver::resolve();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement New Project legacy helper requires the Joomla site application.', 500);
            }

            return $app;
        }
    }
}
