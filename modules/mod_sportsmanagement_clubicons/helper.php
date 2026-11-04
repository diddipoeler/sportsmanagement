<?php
/**
 * Legacy helper adapter for callers that still instantiate modJSMClubiconsHelper.
 * The active Joomla 5/6 implementation lives in src/Helper/ClubiconsHelper.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\RankingEngine;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementClubicons\Site\Helper\ClubiconsHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    RankingEngine::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/RankingEngine.php',
    ClubiconsHelper::class => __DIR__ . '/src/Helper/ClubiconsHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class)) {
        if (!is_file($file)) {
            throw new \RuntimeException('SportsManagement Clubicons dependency file is missing: ' . $class, 500);
        }

        require_once $file;
    }
}

foreach ([
    SiteRouteHelper::class,
    SportsManagementDatabaseResolver::class,
    SportsManagementSiteApplicationResolver::class,
    RankingEngine::class,
    ClubiconsHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement Clubicons dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('modJSMClubiconsHelper', false)) {
    final class modJSMClubiconsHelper
    {
    public ?object $project = null;
    public array $ranking = [];
    public array $teams = [];

        /**
         * Resolve the active Joomla frontend application for legacy callers.
         *
         * Uses the shared site application resolver and rejects administrator
         * or other application clients before the module loads its data.
         *
         * @return CMSApplicationInterface The active site application.
         * @throws \RuntimeException When no frontend client is active.
         */
    private static function siteApplication(): CMSApplicationInterface
    {
        $app = SportsManagementSiteApplicationResolver::resolve();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Clubicons legacy helper requires the Joomla site application.', 500);
        }

        return $app;
    }

        /**
         * Initialise the legacy club-icons helper from the native module API.
         *
         * Converts legacy parameter arrays to Joomla Registry objects,
         * obtains the site application and delegates all project, team and
         * ranking queries to the Joomla 5/6 ClubiconsHelper implementation.
         * Exposes the resulting values through the historical public fields.
         *
         * @param Registry|array $params Module parameters from old or new callers.
         * @param object $module Joomla module record for the rendered instance.
         * @throws \RuntimeException If the required site application is absent.
         */
    public function __construct($params, $module)
    {
        $registry = $params instanceof Registry ? $params : new Registry((array) $params);
        $app = self::siteApplication();

        $result = (new ClubiconsHelper())->getData($registry, $module, $app);
        $this->project = $result['project'];
        $this->ranking = $result['ranking'];
        $this->teams = $result['teams'];
    }
    }
}
