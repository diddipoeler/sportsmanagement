<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      mod_sportsmanagement_ranking.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_ranking
 */


//no direct access
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


if (! defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO'))
{
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO',JComponentHelper::getParams('com_sportsmanagement')->get( 'show_debug_info' ) );
}
if (! defined('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO'))
{
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO',JComponentHelper::getParams('com_sportsmanagement')->get( 'show_query_debug_info' ) );
}

require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'libraries'.DS.'sportsmanagement'.DS.'model.php');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'route.php' );
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'countries.php');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'databasetool.php');


// Reference global application object
$app = JFactory::getApplication();
// JInput object
$jinput = $app->input;
$option = $jinput->getCmd('option');

switch ($option)
{
    case 'com_dmod':
    require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'controllers'.DS.'ajax.json.php');
    require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'fields'.DS.'dependsql.php');
    break;
}

//get helper
require_once (dirname(__FILE__).DS.'helper.php');

/**
 * besonderheit für das inlinehockey update, wenn sich das 
 * modul in einem artikel befindet
 * 
 */
if ($params->get('ishd_update'))
{
$projectid = (int)$params->get('p');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'extensions'.DS.'jsminlinehockey'.DS.'admin'.DS.'models'.DS.'jsminlinehockey.php');
$actionsModel = JModelLegacy::getInstance('jsminlinehockey', 'sportsmanagementModel');   

$count_games = modJSMRankingHelper::getCountGames($projectid,(int)$params->get('ishd_update_hour',4)); 
if ( $count_games )
{
$actionsModel->getmatches($projectid);
}

}

$list = modJSMRankingHelper::getData($params);

$document = JFactory::getDocument();
//add css file
$document->addStyleSheet(JURI::base().'modules'.DS.$module->module.DS.'css'.DS.$module->module.'.css');

?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require(JModuleHelper::getLayoutPath($module->module));
?>
</div>
