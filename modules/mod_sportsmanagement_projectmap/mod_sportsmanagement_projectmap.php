<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_projectmap
 * @file       mod_sportsmanagement_projectmap.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

/**
 * prüft vor Benutzung ob die gewünschte Klasse definiert ist
 */
if (!class_exists('JSMModelLegacy'))
{
	JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_SITE);
}


if (!class_exists('JSMCountries'))
{
	JLoader::import('components.com_sportsmanagement.helpers.countries', JPATH_SITE);
}


if (!class_exists('sportsmanagementHelper'))
{
	/**
	 * add the classes for handling
	 */
	$classpath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';
	JLoader::register('sportsmanagementHelper', $classpath);
	BaseDatabaseModel::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

/**
 *
 * Include the functions only once
 */
JLoader::register('modJSMprojectmaphelper', __DIR__ . '/helper.php');

$document = Factory::getDocument();

/**
 * die übersetzungen der länder laden
 */
$language = Factory::getLanguage();
$language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

$season_ids = ComponentHelper::getParams('com_sportsmanagement')->get('current_season');

$list = modJSMprojectmaphelper::getData($season_ids);


/** add css file */
//$document->addStyleSheet(Uri::base().'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR .'dist/jqvmap.css');
//$document->addScript(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'dist/jquery.vmap.js');
//$document->addScript(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'dist/maps/jquery.vmap.world.js');

$document->addScript(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'htmlworldmap/mapdata.js');
$document->addScript(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'htmlworldmap/worldmap.js');

/** Layout */
?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>"
     id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
	<?PHP
	require ModuleHelper::getLayoutPath($module->module);
	?>
</div>
