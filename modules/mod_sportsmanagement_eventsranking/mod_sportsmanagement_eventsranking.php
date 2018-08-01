<?php
/**
 * @ version   1.0.0 2018-07-31
 * @package    Joomla.Tutorials
 * @subpackage Modules
 * @developer  llambion 
 * @license    GNU/GPL, see LICENSE.php
 * @link       http://docs.joomla.org/J3.x:Creating_a_simple_module/Developing_a_Basic_Module
 * mod_helloworld is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// No direct access
defined('_JEXEC') or die;

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if ( !defined('JSM_PATH') )
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

if ( !class_exists('sportsmanagementHelper') ) 
{
//add the classes for handling
$classpath = JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php';
JLoader::register('sportsmanagementHelper', $classpath);
JModelLegacy::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

if (! defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO'))
{
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO',JComponentHelper::getParams('com_sportsmanagement')->get( 'show_debug_info' ) );
}
if (! defined('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO'))
{
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO',JComponentHelper::getParams('com_sportsmanagement')->get( 'show_query_debug_info' ) );
}

require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'libraries'.DS.'sportsmanagement'.DS.'model.php');

/**
 * die übersetzungen laden
 */
$language = JFactory::getLanguage();
$language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

// Reference global application object
$app = JFactory::getApplication();

// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';

$list = modSMEventsrankingHelper::getData($params);

$document = JFactory::getDocument();
//add css file
$document->addStyleSheet(JURI::base().'modules'.DS.$module->module.DS.'css'.DS.$module->module.'.css');

//Layout
require JModuleHelper::getLayoutPath('mod_sportsmanagement_eventsranking');

