<?php
/**
 * Joomla 5/6 compatibility entry point for mod_sportsmanagement_eventsranking.
 *
 * Normal module execution is handled by services/provider.php and the native
 * dispatcher. This file keeps direct legacy includes on the same data/layout path.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementEventsRanking\Site\Helper\EventsRankingHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Database\DatabaseInterface;

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    $resolver = JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php';

    if (is_file($resolver)) {
        require_once $resolver;
    }
}

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    throw new \RuntimeException('SportsManagement site application resolver could not be loaded.', 500);
}

if (!class_exists(EventsRankingHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/EventsRankingHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(EventsRankingHelper::class)) {
    throw new \RuntimeException('SportsManagement native EventsRanking helper could not be loaded.', 500);
}

$app = SportsManagementSiteApplicationResolver::resolve();
$app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

/** @var DatabaseInterface $database */
$database = Factory::getContainer()->get(DatabaseInterface::class);
$rankingData = (new EventsRankingHelper())->getData($params, $database);
$style = 'modules/' . $module->module . '/css/' . $module->module . '.css';

if (is_file(JPATH_ROOT . '/' . $style)) {
    $app->getDocument()
        ->getWebAssetManager()
        ->registerAndUseStyle('mod_sportsmanagement_eventsranking', $style);
}

require ModuleHelper::getLayoutPath($module->module);
