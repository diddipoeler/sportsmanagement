<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}
 
// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_sportsmanagement')) 
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}
 
// require helper file
if ( !class_exists('sportsmanagementHelper') ) 
{
JLoader::register('SportsManagementHelper', dirname(__FILE__) . DS . 'helpers' . DS . 'sportsmanagement.php');
}

JLoader::import('components.com_sportsmanagement.libraries.util', JPATH_ADMINISTRATOR);

// zur unterscheidung von joomla 2.5 und 3
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.view', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.controller', JPATH_ADMINISTRATOR);
 
require_once(JPATH_ROOT.DS.'components'.DS.'com_sportsmanagement'.DS. 'helpers' . DS . 'countries.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_sportsmanagement'.DS. 'helpers' . DS . 'imageselect.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_sportsmanagement'.DS. 'helpers' . DS . 'JSON.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'models'.DS.'databasetool.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'helpers'.DS.'csvhelper.php');

// Get the base version
$baseVersion = substr(JVERSION, 0, 3);
        
if(version_compare( $baseVersion,'4.0','ge')) 
{
// Joomla! 4.0 code here
defined('JSM_JVERSION') or define('JSM_JVERSION', 4);
}
if(version_compare($baseVersion,'3.0','ge')) 
{
// Joomla! 3.0 code here
defined('JSM_JVERSION') or define('JSM_JVERSION', 3);
}
if(version_compare($baseVersion,'2.5','ge')) 
{
// Joomla! 2.5 code here
defined('JSM_JVERSION') or define('JSM_JVERSION', 2);
//JFactory::getDocument()->addStyleSheet(JURI::root().'administrator/components/com_sportsmanagement/libraries/bootstrap/css/bootstrap.min.css');
//JFactory::getDocument()->addStyleSheet(JURI::root().'administrator/components/com_sportsmanagement/libraries/bootstrap/css/bootstrap-responsive.min.css');
//JFactory::getDocument()->addStyleSheet(JURI::root().'administrator/components/com_sportsmanagement/libraries/bootstrap/js/bootstrap.min.js');
} 
elseif(version_compare($baseVersion,'1.7.0','ge')) 
{
// Joomla! 1.7 code here
} 
elseif(version_compare($baseVersion,'1.6','ge')) 
{
// Joomla! 1.6 code here
} 
else 
{
// Joomla! 1.5 code here
}


// welche joomla version ?
//sportsmanagementHelper::isJoomlaVersion('2.5');

$jinput = JFactory::getApplication()->input;
$command = $jinput->get('task', 'display');
$view = $jinput->get('view');
$lang = JFactory::getLanguage();
$app = JFactory::getApplication();


// welche tabelle soll genutzt werden
$params = JComponentHelper::getParams( 'com_sportsmanagement' );


if ( $params->get( 'cfg_dbprefix' ) )
{
$app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_SETTINGS_USE_DATABASE_TABLE'),'');   
             
}


DEFINE( 'COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE',$params->get( 'cfg_which_database' ) );
DEFINE( 'COM_SPORTSMANAGEMENT_HELP_SERVER',$params->get( 'cfg_help_server' ) );
DEFINE( 'COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH',$params->get( 'modal_popup_width' ) );
DEFINE( 'COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT',$params->get( 'modal_popup_height' ) );

DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO',$params->get( 'show_debug_info' ) );
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO_TEXT','' );
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO',$params->get( 'show_query_debug_info' ) );

if ( $params->get( 'cfg_dbprefix' ) )
{
DEFINE( 'COM_SPORTSMANAGEMENT_PICTURE_SERVER',$params->get( 'cfg_which_database_server' ) );
}
else
{    
if ( COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE )
{
DEFINE( 'COM_SPORTSMANAGEMENT_PICTURE_SERVER',$params->get( 'cfg_which_database_server' ) );    
}
else
{
DEFINE( 'COM_SPORTSMANAGEMENT_PICTURE_SERVER',JURI::root() );    
}
}

if ( $params->get( 'cfg_which_database_table' ) && !defined('COM_SPORTSMANAGEMENT_TABLE') )
{
DEFINE( 'COM_SPORTSMANAGEMENT_TABLE',$params->get( 'cfg_which_database_table' ) );
}
else
{
if ( !defined('COM_SPORTSMANAGEMENT_TABLE') )
{    
DEFINE( 'COM_SPORTSMANAGEMENT_TABLE','sportsmanagement' );
}    
}

DEFINE( 'COM_SPORTSMANAGEMENT_FIELDSETS_TEMPLATE',dirname(__FILE__).DS.'helpers'.DS.'tmpl'.DS.'edit_fieldsets.php' );

if ( $params->get( 'cfg_which_database_table' ) == 'sportsmanagement' )		
{
DEFINE( 'COM_SPORTSMANAGEMENT_USE_NEW_TABLE',true);    
}
else
{
DEFINE( 'COM_SPORTSMANAGEMENT_USE_NEW_TABLE',false);      
}

$controller = '';
$type = '';
$task = '';
$arrExtensions = sportsmanagementHelper::getExtensions();
$model_pathes[]	= array();
$view_pathes[]	= array();
$template_pathes[]	= array();

//JFactory::$database = sportsmanagementHelper::getDBConnection();

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm joomlaversion<br><pre>'.print_r(JSM_JVERSION,true).'</pre>'),'notice');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getDBConnection<br><pre>'.print_r(sportsmanagementHelper::getDBConnection(),true).'</pre>'),'');


// Check for array format.
$filter = JFilterInput::getInstance();

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' filter<br><pre>'.print_r($filter,true).'</pre>'),'');

if (is_array($command))
{
	$command = $filter->clean(array_pop(array_keys($command)), 'cmd');
}
else
{
	$command = $filter->clean($command, 'cmd');
}

// Check for a controller.task command.
if (strpos($command, '.') !== false)
{
	// Explode the controller.task command.
	list ($type, $task) = explode('.', $command);
}

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' command<br><pre>'.print_r($command,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' view<br><pre>'.print_r($view,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' type<br><pre>'.print_r($type,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' task<br><pre>'.print_r($task,true).'</pre>'),'');

for ($e = 0; $e < count($arrExtensions); $e++)
{
$extension = $arrExtensions[$e];
$extensionname = $arrExtensions[$e];
$extensionpath = JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'extensions'.DS.$extension;    

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' extensionpath<br><pre>'.print_r($extensionpath,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' extension<br><pre>'.print_r($extension,true).'</pre>'),'');

if($app->isAdmin()) 
{
		$base_path = $extensionpath.DS.'admin';
		// language file
		$lang->load('com_sportsmanagement_'.$extension, $base_path);
	}

//set the base_path to the extension controllers directory
	if(is_dir($base_path))
	{
		$params = array('base_path'=>$base_path);
	}
	else
	{
		$params = array();
	}

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params<br><pre>'.print_r($params,true).'</pre>'),'');
 
 
// own controllers 
	if (!file_exists($base_path.DS.'controller.php') )
	{
		if($type!=$extension) {
			$params = array();
		}
		$extension = "sportsmanagement";
	}
	elseif (!file_exists($base_path.DS.$extension.'.php'))
	{
		if($type!=$extension) {
			$params = array();
		}
		$extension = "sportsmanagement";
	}

// import joomla controller library
jimport('joomla.application.component.controller');
	try
	{
	   //$controller = JController::getInstance(ucfirst($extension), $params);
	   $controller = JControllerLegacy::getInstance(ucfirst($extension), $params);
	}
	catch (Exception $exc)
	{
		//fallback if no extensions controller has been initialized
		//$controller	= JController::getInstance('sportsmanagement');
        $controller	= JControllerLegacy::getInstance('sportsmanagement');
	}
     
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' controller<br><pre>'.print_r($controller,true).'</pre>'),'');
    
	if (is_dir($base_path.DS.'models')) {
		$model_pathes[] = $base_path.DS.'models';
	}

	if (is_dir($base_path.DS.'views')) {
		$view_pathes[] = $base_path.DS.'views';
        $template_pathes[] = $base_path.DS.'views'.DS.$extensionname.DS.'tmpl';
	}


}

// import joomla controller library
jimport('joomla.application.component.controller');
$controller	= JControllerLegacy::getInstance('sportsmanagement');

//if(is_null($controller) && !($controller instanceof JController)) {
//	//fallback if no extensions controller has been initialized
//	$controller	= JController::getInstance('sportsmanagement');
//}
if(is_null($controller) && !($controller instanceof JControllerLegacy)) {
	//fallback if no extensions controller has been initialized
	$controller	= JControllerLegacy::getInstance('sportsmanagement');
}

foreach ($model_pathes as $path)
{
	if(!empty($path))
	{
		$controller->addModelPath($path, 'sportsmanagementModel');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' addModelPath<br><pre>'.print_r($path,true).'</pre>'),'');
	}
}

foreach ($view_pathes as $path)
{
	if(!empty($path))
	{
		$controller->addViewPath($path, 'sportsmanagementView');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' addViewPath<br><pre>'.print_r($path,true).'</pre>'),'');
	}
}

for ($e = 0; $e < count($arrExtensions); $e++)
{
$extension = $arrExtensions[$e];
$extensionname = $arrExtensions[$e];
foreach ($template_pathes as $path)
{
	if(!empty($path))
	{
	   // get view and set template context 
        $view = $controller->getView( $extensionname, "html", "sportsmanagementView"); 
        $view->addTemplatePath($path); 
        
//	   $view = new JView;
//		$view->addTemplatePath($path);
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' addTemplatePath<br><pre>'.print_r($path,true).'</pre>'),'');
	}
}
}

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' controller<br><pre>'.print_r($controller,true).'</pre>'),'');

 
// Perform the Request task
$controller->execute($task);
 
// Redirect if set by the controller
$controller->redirect();
