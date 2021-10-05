<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_clubicons
 * @file       mod_sportsmanagement_clubicons.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

//if (!defined('DS'))
//{
//	define('DS', DIRECTORY_SEPARATOR);
//}

if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

JLoader::import('components.com_sportsmanagement.helpers.route', JPATH_SITE);

/** prüft vor Benutzung ob die gewünschte Klasse definiert ist */
if (!class_exists('JSMModelLegacy'))
{
	JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_SITE);
}


if (!class_exists('JSMCountries'))
{
	JLoader::import('components.com_sportsmanagement.helpers.countries', JPATH_SITE);
}


if (!class_exists('sportsmanagementModeldatabasetool'))
{
	include_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'databasetool.php';
}

JLoader::import('components.com_sportsmanagement.helpers.sportsmanagement', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.models.project', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.models.ranking', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.helpers.ranking', JPATH_SITE);

/** welche tabelle soll genutzt werden */
$paramscomponent       = ComponentHelper::getParams('com_sportsmanagement');
$database_table        = $paramscomponent->get('cfg_which_database_table');
$show_debug_info       = $paramscomponent->get('show_debug_info');
$show_query_debug_info = $paramscomponent->get('show_query_debug_info');

if (!defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO'))
{
	DEFINE('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO', $show_debug_info);
}


if (!defined('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO'))
{
	DEFINE('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO', $show_query_debug_info);
}

if (!defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE'))
{
	DEFINE('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database'));
}

/** Include the functions only once */
JLoader::register('modJSMClubiconsHelper', __DIR__ . '/helper.php');

/** soll die externe datenbank genutzt werden ? */
if (ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database'))
{
	$module->picture_server = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database_server');
}
else
{
	$module->picture_server = Uri::root();
}

$data = new modJSMClubiconsHelper($params, $module);

$cnt = count($data->teams);
$cnt = ($cnt < $params->get('iconsperrow', 20)) ? $cnt : $params->get('iconsperrow', 20);

/** die übersetzungen laden */
$language = Factory::getLanguage();
$language->load('com_sportsmanagement', JPATH_SITE, null, true);


/** welche joomla version ? */
if (version_compare(JVERSION, '4.0.0', 'ge'))
{
	//HTMLHelper::_('behavior.framework', true);
}
else if (version_compare(JVERSION, '3.0.0', 'ge'))
{
	HTMLHelper::_('behavior.framework', true);
}
else
{
	HTMLHelper::_('behavior.mootools');
}

$doc = Factory::getDocument();
/** Add styles */
$style = '
.img-zoom {
width: auto;
    height: ' . $params->get('picture_height', '50') . ';
    -webkit-transition: all .2s ease-in-out;
    -moz-transition: all .2s ease-in-out;
    -o-transition: all .2s ease-in-out;
    -ms-transition: all .2s ease-in-out;
}

.img-zoom:hover {
    -webkit-transform: scale(1.' . $params->get('max_width_after_mouse_over', '10') . ');
    -moz-transform: scale(1.' . $params->get('max_width_after_mouse_over', '10') . ');
    -o-transform: scale(1.' . $params->get('max_width_after_mouse_over', '10') . ');
    transform: scale(1.' . $params->get('max_width_after_mouse_over', '10') . ');
}

.transition {
    -webkit-transform: scale(1.' . $params->get('max_width_after_mouse_over', '10') . ');
    -moz-transform: scale(1.' . $params->get('max_width_after_mouse_over', '10') . ');
    -o-transform: scale(1.' . $params->get('max_width_after_mouse_over', '10') . ');
    transform: scale(1.' . $params->get('max_width_after_mouse_over', '10') . ');
}
';
$doc->addStyleDeclaration($style);

if ($cnt)
{
	$script = 'script';
	$doc->addScript(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'js/' . $script . '.js');
	?>
    <div id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
		<?PHP
		include ModuleHelper::getLayoutPath($module->module, $params->get('template', 'default'));
		?>
    </div>
	<?PHP
}


