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
use Diddipoeler\Module\SportsManagementClubicons\Site\Helper\ClubiconsHelper;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;

$nativeDependencies = [
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    RankingEngine::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/RankingEngine.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(ClubiconsHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/ClubiconsHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(ClubiconsHelper::class)) {
    throw new \RuntimeException('SportsManagement native Clubicons module helper could not be loaded.', 500);
}

class modJSMClubiconsHelper
{
    public ?object $project = null;
    public array $ranking = [];
    public array $teams = [];

    public function __construct($params, $module)
    {
        $app = Factory::getApplication();

        if (!$app instanceof CMSApplication || !$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Clubicons requires the Joomla site application.', 500);
        }

        $result = (new ClubiconsHelper())->getData($params, $module, $app);
        $this->project = $result['project'];
        $this->ranking = $result['ranking'];
        $this->teams = $result['teams'];
    }
}
