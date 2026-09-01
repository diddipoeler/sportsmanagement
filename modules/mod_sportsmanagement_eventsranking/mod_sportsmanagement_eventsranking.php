<?php
/**
 * Joomla 5/6 compatibility entry point for mod_sportsmanagement_eventsranking.
 *
 * Normal module execution is handled by services/provider.php and the native
 * dispatcher. This file keeps direct legacy includes on the same data/layout path.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementEventsRanking\Site\Helper\EventsRankingHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Database\DatabaseInterface;

$app = Factory::getApplication();
$app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

if (!class_exists(EventsRankingHelper::class)) {
    require_once __DIR__ . '/src/Helper/EventsRankingHelper.php';
}

/** @var DatabaseInterface $database */
$database = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);
$rankingData = (new EventsRankingHelper())->getData($params, $app, $database);
$style = 'modules/' . $module->module . '/css/' . $module->module . '.css';

if (is_file(JPATH_ROOT . '/' . $style)) {
    $app->getDocument()
        ->getWebAssetManager()
        ->registerAndUseStyle('mod_sportsmanagement_eventsranking', $style);
}

require ModuleHelper::getLayoutPath($module->module);
