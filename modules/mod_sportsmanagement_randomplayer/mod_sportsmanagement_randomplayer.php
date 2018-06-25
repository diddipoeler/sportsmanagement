<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      mod_sportsmanagement_randomplayer.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_randomplayer
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

require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'databasetool.php');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'route.php');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'countries.php');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'project.php');

$paramscomponent = JComponentHelper::getParams( 'com_sportsmanagement' );
//$database_table	= $paramscomponent->get( 'cfg_which_database_table' );
//$show_debug_info = $paramscomponent->get( 'show_debug_info' );  
//$show_query_debug_info = $paramscomponent->get( 'show_query_debug_info' ); 
if ( !defined('COM_SPORTSMANAGEMENT_TABLE') )
{
DEFINE( 'COM_SPORTSMANAGEMENT_TABLE',$paramscomponent->get( 'cfg_which_database_table' ) );
}

if ( !defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO') )
{
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO',$paramscomponent->get( 'show_debug_info' ) );
}

if ( !defined('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO') )
{
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO',$paramscomponent->get( 'show_query_debug_info' ) );
}

if (! defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE'))
{
DEFINE( 'COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE',$paramscomponent->get( 'cfg_which_database' ) );
}




// get helper
require_once (dirname(__FILE__).DS.'helper.php');



$list = modJSMRandomplayerHelper::getData($params);

$document = JFactory::getDocument();
/**
 * da wir komplett mit bootstrap arbeiten benötigen wir das nicht mehr  
 * //add css file
 * $document->addStyleSheet(JURI::base().'modules/mod_sportsmanagement_randomplayer/css/mod_sportsmanagement_randomplayer.css');
 */

?>           
<div class="<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require(JModuleHelper::getLayoutPath($module->module));
?>
</div>