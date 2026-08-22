<?php
/** Joomla 5/6 compatibility entry point for mod_sportsmanagement_matchesslider. */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementMatchesSlider\Site\Helper\MatchesSliderHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

$app = Factory::getApplication();
$app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

if (!class_exists(MatchesSliderHelper::class)) {
    require_once __DIR__ . '/src/Helper/MatchesSliderHelper.php';
}

$slidermatches = (new MatchesSliderHelper())->getData($params, $module, $app);
$wam = $app->getDocument()->getWebAssetManager();
$wam->useScript('jquery');
$wam->registerAndUseScript(
    'mod_sportsmanagement_matchesslider.simplyscroll',
    'modules/mod_sportsmanagement_matchesslider/assets/js/jquery.simplyscroll.js',
    [],
    ['defer' => true],
    ['jquery']
);
$wam->registerAndUseStyle(
    'mod_sportsmanagement_matchesslider',
    'modules/mod_sportsmanagement_matchesslider/assets/css/mod_sportsmanagement_matchesslider.css'
);

require ModuleHelper::getLayoutPath(
    $module->module,
    (string) $params->get('layout', 'default')
);
