<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      mod_sportsmanagement_act_season.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_act_season
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if ( !defined('JSM_PATH') )
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
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
require_once(JPATH_SITE . DS . JSM_PATH . DS . 'helpers' . DS . 'countries.php');
}
if ( !class_exists('sportsmanagementHelper') ) 
{
/**
 * add the classes for handling
 */
$classpath = JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php';
JLoader::register('sportsmanagementHelper', $classpath);
BaseDatabaseModel::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

if (! defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE'))
{
DEFINE( 'COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE',ComponentHelper::getParams('com_sportsmanagement')->get( 'cfg_which_database' ) );
}

/**
 * get helper
 */
require_once (dirname(__FILE__).DS.'helper.php');

$document = Factory::getDocument();

/**
 * die übersetzungen der länder laden
 */
$language = Factory::getLanguage();
$language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

$season_ids = ComponentHelper::getParams('com_sportsmanagement')->get( 'current_season' );

$list = modJSMActSeasonHelper::getData($season_ids);

//add css file
//$document->addStyleSheet(Uri::base().'modules/mod_sportsmanagement_new_project/css/mod_sportsmanagement_new_project.css');
?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require(ModuleHelper::getLayoutPath($module->module));
?>
</div>
