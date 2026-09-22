<?php
/**
 * SportsManagement TrainingsData legacy helper bridge for Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) 2015 diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementTrainingsData\Site\Helper\TrainingsDataHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    $resolverFile = JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php';

    if (is_file($resolverFile)) {
        require_once $resolverFile;
    }
}

if (!class_exists(TrainingsDataHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/TrainingsDataHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(TrainingsDataHelper::class)) {
    throw new \RuntimeException('SportsManagement native TrainingsData helper could not be loaded.', 500);
}

if (!class_exists('modJSMTrainingsData', false)) {
    final class modJSMTrainingsData
    {
        public static function getData($params, ?DatabaseInterface $database = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            $app = SportsManagementSiteApplicationResolver::resolve();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement TrainingsData legacy helper requires the Joomla site application.', 500);
            }

            if ($database === null) {
                /** @var DatabaseInterface $database */
                $database = $app->getContainer()->get(DatabaseInterface::class);
            }

            return (new TrainingsDataHelper())->getData($registry, $database);
        }
    }
}
