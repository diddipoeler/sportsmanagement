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
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );

//require_once(JPATH_SITE.DS.JSM_PATH.DS.'controller.php' );

require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'html.php' );
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'countries.php');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'ranking.php' );
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'route.php' );
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'imageselect.php' );
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'predictionroute.php' );
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'pagination.php' );
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'simpleGMapGeocoder.php' );

require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'project.php' );
require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'results.php');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'person.php');

require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'divisions.php');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'rounds.php');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'leagues.php');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'seasons.php');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'round.php');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'teams.php');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'team.php');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'club.php');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'playground.php');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'projectteams.php');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'projectteam.php');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'match.php');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'databasetool.php');

require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php');    

// sprachdatei aus dem backend laden
$langtag = JFactory::getLanguage();
//echo 'Current language is: ' . $langtag->getTag();

$lang = JFactory::getLanguage();
$extension = 'com_sportsmanagement';
$base_dir = JPATH_ADMINISTRATOR;
$language_tag = $langtag->getTag();
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);
    
// welche tabelle soll genutzt werden
$paramscomponent = JComponentHelper::getParams( 'com_sportsmanagement' );
$database_table	= $paramscomponent->get( 'cfg_which_database_table' );
$show_debug_info = $paramscomponent->get( 'show_debug_info' );  
$show_query_debug_info = $paramscomponent->get( 'show_query_debug_info' ); 
DEFINE( 'COM_SPORTSMANAGEMENT_TABLE',$database_table );
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO',$show_debug_info );
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO',$show_query_debug_info );

$document = JFactory::getDocument();
$mainframe = JFactory::getApplication();
$config = JFactory::getConfig();

//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config <br><pre>'.print_r($config,true).'</pre>'),'');
//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' sitename <br><pre>'.print_r($config->getValue( 'config.sitename' ),true).'</pre>'),'');
//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' MetaKeys <br><pre>'.print_r($config->getValue( 'config.MetaKeys' ),true).'</pre>'),'');

$document->addScript(JUri::root(true).'/administrator/components/com_sportsmanagement/assets/js/sm_functions.js');

// meta daten der komponente setzen
$meta_keys = array();
$meta_keys[] = $config->get( 'config.MetaKeys' );
$project_id = JRequest::getInt( "p") ;

//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_id <br><pre>'.print_r($project_id,true).'</pre>'),'');
//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' MetaKeys <br><pre>'.print_r($meta_keys,true).'</pre>'),'');

if ( $project_id )
{
    sportsmanagementModelProject::$projectid = $project_id; 
    $teams = sportsmanagementModelProject::getTeams();
    //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams <br><pre>'.print_r($teams,true).'</pre>'),'');
    
    if ( $teams )
    {
        foreach( $teams as $team )
        {
            $meta_keys[] = $team->name;
        }
    }
}

//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' MetaKeys <br><pre>'.print_r($meta_keys,true).'</pre>'),'');

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
$cfg_help_server = JComponentHelper::getParams($option)->get('cfg_help_server','') ;
$modal_popup_width = JComponentHelper::getParams($option)->get('modal_popup_width',0) ;
$modal_popup_height = JComponentHelper::getParams($option)->get('modal_popup_height',0) ;
$cfg_bugtracker_server = JComponentHelper::getParams($option)->get('cfg_bugtracker_server','') ;

DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_HELP_SERVER',$cfg_help_server );
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_BUGTRACKER_SERVER',$cfg_bugtracker_server );
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_VIEW',$view );

if ( $mainframe->isAdmin() )
{
if($task == '' && $option == 'com_sportsmanagement') 
{
$js ="registerhome('".JUri::base()."','JSM Sports Management','".$mainframe->getCfg('sitename')."','1');". "\n";
$document->addScriptDeclaration( $js );
}
}
else
{
$js ="registerhome('".JUri::base()."','JSM Sports Management','".$mainframe->getCfg('sitename')."','0');". "\n";
$document->addScriptDeclaration( $js );    
}


require_once( JPATH_SITE.DS.JSM_PATH.DS. 'controller.php' );
// Component Helper
jimport( 'joomla.application.component.helper' );
$controller = null;
if(is_null($controller) && !($controller instanceof JControllerLegacy)) {
	//fallback if no extensions controller has been initialized
	$controller	= JControllerLegacy::getInstance('sportsmanagement');
}
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

/*
// Require the base controller
require_once (JPATH_SITE.DS.JSM_PATH.DS.'controller.php');

// Execute the task.
$controller = JControllerLegacy::getInstance('sportsmanagement');
$controller->execute( JRequest::getCmd('task'));
$controller->redirect();
*/

/*
// Execute the task.
$controller = JControllerLegacy::getInstance('sportsmanagement');
$controller->execute( JRequest::getCmd('task'));
$controller->redirect();
*/

/*
// Require the base controller
require_once( JPATH_SITE.DS.JSM_PATH.DS.'controller.php' );

// Require specific controller if requested
if( $controller = JRequest::getWord('controller'))
{
   $path = JPATH_SITE.DS.JSM_PATH.DS.'controllers'.DS.$controller.'.php';
   if( file_exists($path))
	{
       require_once $path;
   } else
   {
       $controller = '';
   }
}

// Create the controller
$classname    = 'sportsmanagementController'.$controller;
$controller   = new $classname( );

// Perform the Request task
$controller->execute( JRequest::getVar( 'task' ) );

// Redirect if set by the controller
$controller->redirect();
*/




?>