<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// zur unterscheidung von joomla 2.5 und 3
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.view', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_ADMINISTRATOR);

if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
} 
elseif(version_compare(JVERSION,'1.7.0','ge')) 
{
// Joomla! 1.7 code here
} 
elseif(version_compare(JVERSION,'1.6.0','ge')) 
{
// Joomla! 1.6 code here
} 
else 
{
// Joomla! 1.5 code here
}

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if (! defined('JSM_PATH'))
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

$document = JFactory::getDocument();
$app = JFactory::getApplication();
$config = JFactory::getConfig();

/*
// zur unterscheidung von joomla 2.5 und 3
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.view', JPATH_ADMINISTRATOR);
*/

/*
//require_once(JPATH_SITE.DS.JSM_PATH.DS.'controller.php' );
if ( !class_exists('sportsmanagementHelper') ) 
{
    require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php');
}
*/

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');

// prüft vor Benutzung ob die gewünschte Klasse definiert ist
if ( !class_exists('sportsmanagementHelper') ) 
{
//add the classes for handling
$classpath = JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php';
JLoader::register('sportsmanagementHelper', $classpath);
JModelLegacy::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'html.php' );
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'countries.php');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'ranking.php' );
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'route.php' );
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'imageselect.php' );
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'predictionroute.php' );
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'pagination.php' );
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'simpleGMapGeocoder.php' );
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'project.php' );
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'results.php');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'person.php');

require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'prediction.php');

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'divisions.php');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'rounds.php');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'leagues.php');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'seasons.php');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'round.php');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'teams.php');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'team.php');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'club.php');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'playground.php');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'projectteams.php');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'projectteam.php');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'match.php');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'databasetool.php');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'eventtypes.php');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');    


// sprachdatei aus dem backend laden
$langtag = JFactory::getLanguage();
//echo 'Current language is: ' . $langtag->getTag();

$paramscomponent = JComponentHelper::getParams( 'com_sportsmanagement' );

// update plugins
// Check if plugin has been enabled
$plugin_enabled = JPluginHelper::isEnabled('system', 'jsm_kickerde');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' plugin isEnabled jsm_kickerde <br><pre>'.print_r($plugin_enabled,true).'</pre>'),'');

$plugin_enabled = JPluginHelper::importPlugin('system', 'jsm_kickerde');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' plugin importPlugin jsm_kickerde <br><pre>'.print_r($plugin_enabled,true).'</pre>'),'');

$plugin_enabled = JPluginHelper::getPlugin('system', 'jsm_kickerde');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' plugin getPlugin jsm_kickerde <br><pre>'.print_r($plugin_enabled,true).'</pre>'),'');

    
if (! defined('COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS'))
{
DEFINE( 'COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS',$paramscomponent->get( 'boostrap_div_class' ) );
}

if (! defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE'))
{
DEFINE( 'COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE',$paramscomponent->get( 'cfg_which_database' ) );
}

if (! defined('COM_SPORTSMANAGEMENT_LOAD_BOOTSTRAP'))
{
DEFINE( 'COM_SPORTSMANAGEMENT_LOAD_BOOTSTRAP',$paramscomponent->get( 'cfg_load_bootstrap' ) );
}


if (! defined('COM_SPORTSMANAGEMENT_TABLE'))
{
DEFINE( 'COM_SPORTSMANAGEMENT_TABLE',$paramscomponent->get( 'cfg_which_database_table' ) );
}
if (! defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO'))
{
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO',$paramscomponent->get( 'show_debug_info' ) );
}
if (! defined('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO'))
{
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO',$paramscomponent->get( 'show_query_debug_info' ) );
}

if ( $paramscomponent->get('cfg_dbprefix') && !defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER') )
{
DEFINE( 'COM_SPORTSMANAGEMENT_PICTURE_SERVER',$paramscomponent->get( 'cfg_which_database_server' ) );    
}
else
{
if ( COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE || JRequest::getInt( 'cfg_which_database', 0 ) )
{
if (! defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER'))
{    
DEFINE( 'COM_SPORTSMANAGEMENT_PICTURE_SERVER',$paramscomponent->get( 'cfg_which_database_server' ) );
}    
}
else
{
if (! defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER'))
{        
DEFINE( 'COM_SPORTSMANAGEMENT_PICTURE_SERVER',JURI::root() );
}    
}

}

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');

//$document = JFactory::getDocument();
//$app = JFactory::getApplication();
//$config = JFactory::getConfig();

$lang = JFactory::getLanguage();



//$document->setMetaData( 'viewport', "width=device-width, initial-scale=1.0" );

$extension = 'com_sportsmanagement';
$base_dir = JPATH_ADMINISTRATOR;
$language_tag = $langtag->getTag();
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);
    
// welche tabelle soll genutzt werden
//$paramscomponent = JComponentHelper::getParams( 'com_sportsmanagement' );
//$database_table	= $paramscomponent->get( 'cfg_which_database_table' );
//$show_debug_info = $paramscomponent->get( 'show_debug_info' );  
//$show_query_debug_info = $paramscomponent->get( 'show_query_debug_info' ); 
//$cfg_which_database_server = $paramscomponent->get( 'cfg_which_database_server' );





//JFactory::$database = sportsmanagementHelper::getDBConnection();

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config <br><pre>'.print_r($config,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' sitename <br><pre>'.print_r($config->getValue( 'config.sitename' ),true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' MetaKeys <br><pre>'.print_r($config->getValue( 'config.MetaKeys' ),true).'</pre>'),'');

$document->addScript(JURI::root(true).'/components/com_sportsmanagement/assets/js/sm_functions.js');

//$document->addScriptDeclaration('jQuery.noConflict();');
        
// meta daten der komponente setzen
$meta_keys = array();

if(version_compare(JVERSION,'3.0.0','ge')) 
{
$meta_keys[] = $config->get( 'config.MetaKeys' );
}
else
{
$meta_keys[] = $config->getValue( 'config.MetaKeys' );    
}

$project_id = JRequest::getInt( "p") ;

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_id <br><pre>'.print_r($project_id,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' MetaKeys <br><pre>'.print_r($meta_keys,true).'</pre>'),'');

if ( $project_id )
{
    sportsmanagementModelProject::$projectid = $project_id; 
    $teams = sportsmanagementModelProject::getTeams();
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams <br><pre>'.print_r($teams,true).'</pre>'),'');
    
    if ( $teams )
    {
        foreach( $teams as $team )
        {
            $meta_keys[] = $team->name;
        }
    }
}

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' MetaKeys <br><pre>'.print_r($meta_keys,true).'</pre>'),'');

$document->setMetaData( 'author', 'Dieter Ploeger' );
$document->setMetaData( 'revisit-after', '2 days' );
$document->setMetaData( 'robots', 'index,follow' );

/** meta name
 * keywords
 * description
 * generator
 * 
 */
$document->setMetaData( 'keywords', implode(",",$meta_keys) );
$document->setMetaData( 'generator', "JSM - Joomla Sports Management" );
 
$task = JRequest::getCmd('task');
$option = JRequest::getCmd('option');

$view = JRequest::getVar( "view") ;
$view = ucfirst(strtolower($view));
//$cfg_help_server = JComponentHelper::getParams($option)->get('cfg_help_server','') ;
$modal_popup_width = JComponentHelper::getParams($option)->get('modal_popup_width',0) ;
$modal_popup_height = JComponentHelper::getParams($option)->get('modal_popup_height',0) ;
//$cfg_bugtracker_server = JComponentHelper::getParams($option)->get('cfg_bugtracker_server','') ;

DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_HELP_SERVER',JComponentHelper::getParams($option)->get('cfg_help_server','') );
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_BUGTRACKER_SERVER',JComponentHelper::getParams($option)->get('cfg_bugtracker_server','') );
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_VIEW',$view );

//if ( $app->isAdmin() )
//{
//if($task == '' && $option == 'com_sportsmanagement') 
//{
//$js ="registerhome('".JURI::base()."','JSM Sports Management','".$app->getCfg('sitename')."','1');". "\n";
//$document->addScriptDeclaration( $js );
//}
//}
//else
//{
//$js ="registerhome('".JURI::base()."','JSM Sports Management','".$app->getCfg('sitename')."','0');". "\n";
//$document->addScriptDeclaration( $js );    
//}


require_once( JPATH_SITE.DS.JSM_PATH.DS. 'controller.php' );
// Component Helper
jimport( 'joomla.application.component.helper' );
$controller = null;
if(is_null($controller) && !($controller instanceof JControllerLegacy)) {
	//fallback if no extensions controller has been initialized
	$controller	= JControllerLegacy::getInstance('sportsmanagement');
}

$task = JFactory::getApplication()->input->getCmd('task');
// Den 'task' der im Request übergeben wurde ausführen
$controller->execute($task);
//$controller->execute(JRequest::getCmd('task'));
$controller->redirect();



?>
