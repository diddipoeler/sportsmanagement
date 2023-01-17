<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.1.0
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_teamplayers
 * @file       mod_sportsmanagement_teamplayers.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @modded	   llambion (2020)
 *             Added players carrousel, mins played and position fields
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

$lang    = Factory::getLanguage();
$locales = $lang->getLocale();
setlocale(LC_ALL, $locales[0]);
/** die übersetzungen laden */
$lang->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);
$lang->load('com_sportsmanagement', JPATH_SITE, null, true);

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

/** prüft vor Benutzung ob die gewünschte Klasse definiert ist */
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
	/** add the classes for handling */
	$classpath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';
	JLoader::register('sportsmanagementHelper', $classpath);
	BaseDatabaseModel::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

JLoader::import('components.com_sportsmanagement.models.databasetool', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.helpers.route', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.models.project', JPATH_SITE);

/** Welche tabelle soll genutzt werden */
$paramscomponent = ComponentHelper::getParams('com_sportsmanagement');

if (!defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO'))
{
	DEFINE('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO', $paramscomponent->get('show_debug_info'));
}

if (!defined('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO'))
{
	DEFINE('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO', $paramscomponent->get('show_query_debug_info'));
}

if (!defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE'))
{
	DEFINE('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', $paramscomponent->get('cfg_which_database'));
}

/** Include the functions only once */
JLoader::register('modSportsmanagementTeamPlayersHelper', __DIR__ . '/helper.php');

$list = modSportsmanagementTeamPlayersHelper::getData($params);

$document = Factory::getDocument();
/** add css file */
$document->addStyleSheet(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . $module->module . '.css');

//$document->addScript(Uri::base().'modules' . DIRECTORY_SEPARATOR . $module->module. DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'default.js');


/** add files for slider */
$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/bxslider/4.2.15/jquery.bxslider.min.js');
$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/bxslider/4.2.15/jquery.bxslider.min.css');


$document->addScriptDeclaration("

    var $jQ = jQuery.noConflict();

    $jQ(document).ready(function(){
        $jQ('.bxslider').bxSlider({
            mode: 'fade',
            captions: true,
            touchEnabled: true,
            responsive: true,
            controls: true,
            auto: true,
            pager:true,
            adaptiveHeight: true,
            slideWidth: 603        });
    });

");


?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>"
     id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
	<?PHP
	require ModuleHelper::getLayoutPath($module->module);
	?>
</div>
