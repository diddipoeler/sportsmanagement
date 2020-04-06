<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       mod_sportsmanagement_ranking.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage mod_sportsmanagement_ranking
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;

if (! defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('JSM_PATH') ) {
    DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

/**
* 
 * prüft vor Benutzung ob die gewünschte Klasse definiert ist 
*/
if (!class_exists('JSMModelLegacy')) {
    JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_SITE);
}
if (!class_exists('JSMCountries')) {
    JLoader::import('components.com_sportsmanagement.helpers.countries', JPATH_SITE);
}
if (!class_exists('sportsmanagementHelper') ) {
    /**
 * add the classes for handling 
*/
    $classpath = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.JSM_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'sportsmanagement.php';
    JLoader::register('sportsmanagementHelper', $classpath);
    BaseDatabaseModel::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}


if (! defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO')) {
    DEFINE('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO', ComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info'));
}
if (! defined('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO')) {
    DEFINE('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO', ComponentHelper::getParams('com_sportsmanagement')->get('show_query_debug_info'));
}

if (!class_exists('JSMModelLegacy')) {
    JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_SITE);
}

JLoader::import('components.com_sportsmanagement.helpers.route', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.models.databasetool', JPATH_ADMINISTRATOR);

/**
* 
 * Reference global application object 
*/
$app = Factory::getApplication();
/**
* 
 * JInput object 
*/
$jinput = $app->input;
$option = $jinput->getCmd('option');

/**
* 
 * die übersetzungen laden 
*/
$language = Factory::getLanguage();
$language->load('com_sportsmanagement', JPATH_SITE, null, true);

switch ($option)
{
case 'com_dmod':
    include_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.JSM_PATH.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'ajax.json.php';
    include_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.JSM_PATH.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'fields'.DIRECTORY_SEPARATOR.'dependsql.php';
    break;
}

/**
* 
 * Include the functions only once 
*/
JLoader::register('modJSMRankingHelper', __DIR__ . '/helper.php');

/**
 * besonderheit für das inlinehockey update, wenn sich das 
 * modul in einem artikel befindet
 */
if ($params->get('ishd_update')) {
    $projectid = (int)$params->get('p');
    JLoader::import('components.com_sportsmanagement.extensions.jsminlinehockey.admin.models.jsminlinehockey', JPATH_SITE);
    $actionsModel = BaseDatabaseModel::getInstance('jsminlinehockey', 'sportsmanagementModel');   

    $count_games = modJSMRankingHelper::getCountGames($projectid, (int)$params->get('ishd_update_hour', 4)); 
    if ($count_games ) {
        $actionsModel->getmatches($projectid);
    }

}

$list = modJSMRankingHelper::getData($params);

$document = Factory::getDocument();
/**
* 
 * add css file 
*/
$document->addStyleSheet(Uri::base().'modules'.DIRECTORY_SEPARATOR.$module->module.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.$module->module.'.css');

?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require ModuleHelper::getLayoutPath($module->module);
?>
</div>
