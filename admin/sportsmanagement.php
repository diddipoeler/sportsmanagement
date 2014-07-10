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
// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_sportsmanagement')) 
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}
 
// require helper file
JLoader::register('SportsManagementHelper', dirname(__FILE__) . DS . 'helpers' . DS . 'sportsmanagement.php');
JLoader::import('components.com_sportsmanagement.libraries.util', JPATH_ADMINISTRATOR);
 
require_once(JPATH_ROOT.DS.'components'.DS.'com_sportsmanagement'.DS. 'helpers' . DS . 'countries.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_sportsmanagement'.DS. 'helpers' . DS . 'imageselect.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_sportsmanagement'.DS. 'helpers' . DS . 'JSON.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'models'.DS.'databasetool.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'helpers'.DS.'csvhelper.php');

// welche tabelle soll genutzt werden
$params = JComponentHelper::getParams( 'com_sportsmanagement' );
$database_table	= $params->get( 'cfg_which_database_table' ); 
$show_debug_info = $params->get( 'show_debug_info' );  
$show_query_debug_info = $params->get( 'show_query_debug_info' );  

$cfg_help_server = $params->get( 'cfg_help_server' );
$modal_popup_width = $params->get( 'modal_popup_width' );
$modal_popup_height = $params->get( 'modal_popup_height' );

DEFINE( 'COM_SPORTSMANAGEMENT_HELP_SERVER',$cfg_help_server );
DEFINE( 'COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH',$modal_popup_width );
DEFINE( 'COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT',$modal_popup_height );

DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO',$show_debug_info );
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO',$show_query_debug_info );
DEFINE( 'COM_SPORTSMANAGEMENT_TABLE',$database_table );
DEFINE( 'COM_SPORTSMANAGEMENT_FIELDSETS_TEMPLATE',dirname(__FILE__).DS.'helpers'.DS.'tmpl'.DS.'edit_fieldsets.php' );

if ( $database_table == 'sportsmanagement' )		
{
DEFINE( 'COM_SPORTSMANAGEMENT_USE_NEW_TABLE',true);    
}
else
{
DEFINE( 'COM_SPORTSMANAGEMENT_USE_NEW_TABLE',false);      
}

//$command = JRequest::getVar('task');
$command = JRequest::getVar('task', 'display');

$view = JRequest::getVar('view');
$lang = JFactory::getLanguage();
$app = JFactory::getApplication();
$type = '';
$arrExtensions = sportsmanagementHelper::getExtensions();
$model_pathes[]	= array();
$view_pathes[]	= array();
$template_pathes[]	= array();
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' command<br><pre>'.print_r($command,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' view<br><pre>'.print_r($view,true).'</pre>'),'');


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
		$controller = JControllerLegacy::getInstance(ucfirst($extension), $params);
	}
	catch (Exception $exc)
	{
		//fallback if no extensions controller has been initialized
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

foreach ($template_pathes as $path)
{
	if(!empty($path))
	{
	   // get view and set template context 
        $view = $controller->getView( $extensionname, "html", "sportsmanagementView"); 
        $view->addTemplatePath($path); 
        
//	   $view = new JViewLegacy;
//		$view->addTemplatePath($path);
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' addTemplatePath<br><pre>'.print_r($path,true).'</pre>'),'');
	}
}


//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' controller<br><pre>'.print_r($controller,true).'</pre>'),'');

 
// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
 
// Redirect if set by the controller
$controller->redirect();
