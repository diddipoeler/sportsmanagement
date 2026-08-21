<?php
/**
 * SportsManagement calendar module bootstrap for Joomla 5/6.
 *
 * @package     Sportsmanagement
 * @subpackage  mod_sportsmanagement_calendar
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('jquery.framework');

$app = Factory::getApplication();
$input = $app->getInput();

if (!defined('JSM_PATH')) {
    define('JSM_PATH', 'components/com_sportsmanagement');
}

if (!defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO')) {
    define(
        'COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO',
        ComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info', 0)
    );
}

if (!defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE')) {
    define(
        'COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE',
        ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database')
    );
}

$legacyClasses = [
    'sportsmanagementHelper' => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php',
    'sportsmanagementHelperRoute' => JPATH_SITE . '/components/com_sportsmanagement/helpers/route.php',
    'JSMCountries' => JPATH_SITE . '/components/com_sportsmanagement/helpers/countries.php',
];

foreach ($legacyClasses as $class => $path) {
    if (!class_exists($class) && is_file($path)) {
        require_once $path;
    }
}

require_once __DIR__ . '/helper.php';

$ajax = $input->post->getInt('ajaxCalMod', 0);
$ajaxmod = $input->post->getInt('ajaxmodid', 0);

if (!$params->get('cal_start_date')) {
    $year = $input->getInt('year', (int) date('Y'));
    $month = $input->getInt('month', (int) date('m'));
    $day = $input->getInt('day', 0);
} else {
    $startDate = new Date((string) $params->get('cal_start_date'));
    $year = $input->getInt('year', (int) $startDate->format('Y'));
    $month = $input->getInt('month', (int) $startDate->format('m'));
    $day = $ajax ? '' : $input->getInt('day', (int) $startDate->format('d'));
}

$helper = new modJSMCalendarHelper();
$document = Factory::getDocument();
$lightbox = $params->get('lightbox', 1);
$inject_container = (int) $params->get('inject', 0) === 1
    ? (string) $params->get('inject_container', 'sportsmanagement')
    : '';

if (!defined('JLC_MODULESCRIPTLOADED')) {
    $assetBase = 'modules/' . $module->module . '/assets';
    $wa = $document->getWebAssetManager();

    $wa->registerAndUseScript(
        $module->module . '.calendar',
        $assetBase . '/js/' . $module->module . '.js',
        [],
        ['defer' => true]
    );
    $wa->registerAndUseStyle(
        $module->module . '.calendar',
        $assetBase . '/css/' . $module->module . '.css'
    );
    $wa->useScript('bootstrap.modal');

    define('JLC_MODULESCRIPTLOADED', 1);
}

$calendar = $helper->showCal($params, $year, $month, $module->id, $ajax);
?>
<div id="<?php echo htmlspecialchars($module->module . '-' . $module->id, ENT_QUOTES, 'UTF-8'); ?>">
    <?php require ModuleHelper::getLayoutPath($module->module, $params->get('which_layout', 'default')); ?>
</div>
