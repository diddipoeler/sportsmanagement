<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_clubicons
 * @file       mod_sportsmanagement_clubicons.php
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

if (!defined('JSM_PATH')) {
    define('JSM_PATH', 'components/com_sportsmanagement');
}

$siteComponent = JPATH_SITE . '/components/com_sportsmanagement';
$adminComponent = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement';

require_once $siteComponent . '/helpers/route.php';
require_once $siteComponent . '/libraries/sportsmanagement/model.php';
require_once $siteComponent . '/helpers/countries.php';
require_once $adminComponent . '/models/databasetool.php';
require_once $adminComponent . '/helpers/sportsmanagement.php';
require_once $siteComponent . '/models/project.php';
require_once $siteComponent . '/models/ranking.php';
require_once $siteComponent . '/helpers/ranking.php';
require_once __DIR__ . '/helper.php';

$componentParams = ComponentHelper::getParams('com_sportsmanagement');

if (!defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO')) {
    define('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO', $componentParams->get('show_debug_info'));
}

if (!defined('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO')) {
    define('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO', $componentParams->get('show_query_debug_info'));
}

if (!defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE')) {
    define('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', $componentParams->get('cfg_which_database'));
}

$app = Factory::getApplication();
$app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

$data = new modJSMClubiconsHelper($params, $module);
$iconsPerRow = max(1, (int) $params->get('iconsperrow', 20));
$count = min(count($data->teams), $iconsPerRow);
$template = (string) $params->get('template', 'default');
$document = $app->getDocument();
$webAssetManager = $document->getWebAssetManager();

if ($template === 'default') {
    $assetName = 'mod_sportsmanagement_clubicons.default';
    $webAssetManager->registerAndUseStyle(
        $assetName,
        'modules/' . $module->module . '/css/default.css'
    );

    $percent = (float) $params->get('max_width_after_mouse_over', 10);
    $transition = max(0.1, (100 + $percent) / 100);
    $height = max(1, (int) $params->get('picture_height', 50));
    $webAssetManager->addInlineStyle(
        '.mod-sportsmanagement-clubicons .img-zoom{' .
        'width:auto;height:' . $height . 'px;transition:transform .2s ease-in-out}' .
        '.mod-sportsmanagement-clubicons .img-zoom:hover{' .
        'transform:scale(' . rtrim(rtrim(number_format($transition, 4, '.', ''), '0'), '.') . ')}'
    );
}

if ($count > 0) {
    echo '<div class="mod-sportsmanagement-clubicons" id="' . htmlspecialchars($module->module . '-' . $module->id, ENT_QUOTES, 'UTF-8') . '">';
    require ModuleHelper::getLayoutPath($module->module, $template);
    echo '</div>';
}
