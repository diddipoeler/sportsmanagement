<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      mod_sportsmanagement_playground_ticker.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_playground_ticker
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if ( !defined('JSM_PATH') )
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

// prüft vor Benutzung ob die gewünschte Klasse definiert ist
if ( !class_exists('sportsmanagementHelper') ) 
{
//add the classes for handling
$classpath = JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php';
JLoader::register('sportsmanagementHelper', $classpath);
JModelLegacy::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

// get helper
require_once (dirname(__FILE__).DS.'helper.php');

//if (JComponentHelper::getParams('com_sportsmanagement')->get( 'cfg_dbprefix' ))
//{
//$module->picture_server = JComponentHelper::getParams('com_sportsmanagement')->get( 'cfg_which_database_server' ) ;    
//}
//else
//{
//if ( COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE || JRequest::getInt( 'cfg_which_database', 0 ) )
//{
//$module->picture_server = JComponentHelper::getParams('com_sportsmanagement')->get( 'cfg_which_database_server' ) ;
//}
//else
//{
//$module->picture_server = JURI::root() ;
//}
//}

/**
 * soll die externe datenbank genutzt werden ?
 */
if ( JComponentHelper::getParams('com_sportsmanagement')->get( 'cfg_which_database' ) )
{
$module->picture_server = JComponentHelper::getParams('com_sportsmanagement')->get( 'cfg_which_database_server' ) ;    
}
else
{
$module->picture_server = JURI::root();    
}

$playgrounds = modJSMPlaygroundTicker::getData($params);

$document = JFactory::getDocument();
//add css file
$document->addStyleSheet(JUri::base().'modules'.DS.$module->module.DS.'css'.DS.$module->module.'.css');

?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require(JModuleHelper::getLayoutPath($module->module));
?>
</div>