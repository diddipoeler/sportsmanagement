<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       mod_sportsmanagement_act_season.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage mod_sportsmanagement_act_season
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

if (! defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('JSM_PATH') ) {
    DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

/**
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

if (! defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE')) {
    DEFINE('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database'));
}

/**
* 
 * Include the functions only once 
*/
JLoader::register('modJSMActSeasonHelper', __DIR__ . '/helper.php');

$document = Factory::getDocument();

/**
 * die übersetzungen der länder laden
 */
$language = Factory::getLanguage();
$language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

$season_ids = ComponentHelper::getParams('com_sportsmanagement')->get('current_season');

$list = modJSMActSeasonHelper::getData($season_ids);

/**
 * add css file
 */
//$document->addStyleSheet(Uri::base().'modules/mod_sportsmanagement_new_project/css/mod_sportsmanagement_new_project.css');

/**
 * Layout
 */
?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require ModuleHelper::getLayoutPath($module->module);
?>
</div>
