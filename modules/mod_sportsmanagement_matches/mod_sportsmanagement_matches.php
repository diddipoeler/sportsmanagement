<?php
/**
 * Joomla 5/6 runtime entry for mod_sportsmanagement_matches.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\MatchReadService;
use Diddipoeler\Module\SportsManagementMatches\Site\Helper\MatchesHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

$app = Factory::getApplication();
$app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);
$app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

if (!class_exists(MatchReadService::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Service/MatchReadService.php';
}

if (!class_exists(MatchesHelper::class)) {
    require_once __DIR__ . '/connectors/native.php';
}

$result = (new MatchesHelper())->getData($params, $app, $module);
$matches = $result['matches'];
$legacyUpdateRequested = $result['legacy_update_requested'];

$template = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $params->get('template', 'default_tableless'));
$template = $template !== '' ? $template : 'default_tableless';

$wam = $app->getDocument()->getWebAssetManager();
$wam->registerAndUseStyle(
    'mod_sportsmanagement_matches.native',
    'modules/mod_sportsmanagement_matches/assets/css/native.css'
);
$wam->registerAndUseStyle(
    'mod_sportsmanagement_matches.template.' . $template,
    'modules/mod_sportsmanagement_matches/tmpl/' . $template . '/mod_sportsmanagement_matches.css'
);

require ModuleHelper::getLayoutPath($module->module, 'native');
