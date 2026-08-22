<?php
/**
 * Joomla 5/6 compatibility entry point for mod_sportsmanagement_ranking.
 *
 * Normal module execution uses services/provider.php and the native dispatcher.
 * This bridge keeps direct legacy includes on the same namespaced helper/layout.
 */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementRanking\Site\Helper\RankingHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

$app = Factory::getApplication();
$app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

if (!class_exists(RankingHelper::class)) {
    require_once __DIR__ . '/src/Helper/RankingHelper.php';
}

$list = (new RankingHelper())->getData($params, $module, $app);

$style = 'modules/' . $module->module . '/css/' . $module->module . '.css';

if (is_file(JPATH_ROOT . '/' . $style)) {
    $app->getDocument()
        ->getWebAssetManager()
        ->registerAndUseStyle('mod_sportsmanagement_ranking', $style);
}

require ModuleHelper::getLayoutPath($module->module, 'native');
