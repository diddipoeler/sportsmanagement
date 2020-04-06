<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       sportsmanagement.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;

/**
 *  Access check.
 */
if (!Factory::getUser()->authorise('core.manage', 'com_sportsmanagement')) {
    return Log::add(Text::_('JERROR_ALERTNOAUTHOR'), Log::WARNING, 'jsmerror');
}

/**
 *  require helper file
 */
if (!class_exists('sportsmanagementHelper') ) {
    JLoader::register('SportsManagementHelper', dirname(__FILE__) .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'sportsmanagement.php');
}
JLoader::register('TVarDumper', dirname(__FILE__) .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'TVarDumper.php');
JLoader::import('components.com_sportsmanagement.libraries.util', JPATH_ADMINISTRATOR);

// zur unterscheidung von joomla 3 und 4
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.view', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.controller', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.table', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.formbehavior2', JPATH_ADMINISTRATOR);

JLoader::import('components.com_sportsmanagement.helpers.countries', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.helpers.imageselect', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.helpers.JSON', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.models.databasetool', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.helpers.csvhelper', JPATH_ADMINISTRATOR);


// Get the base version
$baseVersion = substr(JVERSION, 0, 3);
      
if(version_compare($baseVersion, '4.0', 'ge')) {
    // Joomla! 4.0 code here
    defined('JSM_JVERSION') or define('JSM_JVERSION', 4);
    JLoader::import('components.com_sportsmanagement.libraries.github.github', JPATH_ADMINISTRATOR);  
    JLoader::import('components.com_sportsmanagement.libraries.github.object', JPATH_ADMINISTRATOR);
    JLoader::import('components.com_sportsmanagement.libraries.github.http', JPATH_ADMINISTRATOR);  
    JLoader::import('components.com_sportsmanagement.libraries.github.commits', JPATH_ADMINISTRATOR);      
    JLoader::import('components.com_sportsmanagement.libraries.github.milestones', JPATH_ADMINISTRATOR);  
    JLoader::import('components.com_sportsmanagement.libraries.github.package', JPATH_ADMINISTRATOR);  
    JLoader::import('components.com_sportsmanagement.libraries.github.package.issues', JPATH_ADMINISTRATOR);  
    JLoader::import('components.com_sportsmanagement.libraries.github.package.activity', JPATH_ADMINISTRATOR);  
    JLoader::import('components.com_sportsmanagement.libraries.github.package.issues.milestones', JPATH_ADMINISTRATOR);  
    JLoader::import('components.com_sportsmanagement.libraries.github.package.activity.starring', JPATH_ADMINISTRATOR);  
}
if(version_compare($baseVersion, '3.0', 'ge')) {
    // Joomla! 3.0 code here
    defined('JSM_JVERSION') or define('JSM_JVERSION', 3);
}
if(version_compare($baseVersion, '2.5', 'ge')) {
    // Joomla! 2.5 code here
    defined('JSM_JVERSION') or define('JSM_JVERSION', 2);
}
elseif(version_compare($baseVersion, '1.7.0', 'ge')) {
    // Joomla! 1.7 code here
}
elseif(version_compare($baseVersion, '1.6', 'ge')) {
    // Joomla! 1.6 code here
}
else
{
    // Joomla! 1.5 code here
}

$jinput = Factory::getApplication()->input;
$command = $jinput->get('task', 'display');
$view = $jinput->get('view');
$lang = Factory::getLanguage();
$app = Factory::getApplication();


// welche tabelle soll genutzt werden
$params = ComponentHelper::getParams('com_sportsmanagement');


if ($params->get('cfg_dbprefix') ) {
    $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_SETTINGS_USE_DATABASE_TABLE'), ''); 
           
}


DEFINE('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', $params->get('cfg_which_database'));
DEFINE('COM_SPORTSMANAGEMENT_HELP_SERVER', $params->get('cfg_help_server'));
DEFINE('COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH', $params->get('modal_popup_width'));
DEFINE('COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT', $params->get('modal_popup_height'));

DEFINE('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO', $params->get('show_debug_info'));
DEFINE('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO_TEXT', '');
DEFINE('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO', $params->get('show_query_debug_info'));

if ($params->get('cfg_dbprefix') ) {
    DEFINE('COM_SPORTSMANAGEMENT_PICTURE_SERVER', $params->get('cfg_which_database_server'));
}
else
{  
    if (COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE ) {
        DEFINE('COM_SPORTSMANAGEMENT_PICTURE_SERVER', $params->get('cfg_which_database_server'));  
    }
    else
    {
        DEFINE('COM_SPORTSMANAGEMENT_PICTURE_SERVER', JURI::root());  
    }
}

DEFINE('COM_SPORTSMANAGEMENT_FIELDSETS_TEMPLATE', dirname(__FILE__).DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.'edit_fieldsets.php');

if ($params->get('cfg_which_database_table') == 'sportsmanagement' ) {
    DEFINE('COM_SPORTSMANAGEMENT_USE_NEW_TABLE', true);  
}
else
{
    DEFINE('COM_SPORTSMANAGEMENT_USE_NEW_TABLE', false);    
}

$controller = '';
$type = '';
$task = '';
$arrExtensions = sportsmanagementHelper::getExtensions();
$model_pathes[]    = array();
$view_pathes[]    = array();
$template_pathes[]    = array();

/**
 *  Check for array format.
 */
$filter = JFilterInput::getInstance();

if (is_array($command)) {
    $command = $filter->clean(array_pop(array_keys($command)), 'cmd');
}
else
{
    $command = $filter->clean($command, 'cmd');
}

/**
 *  Check for a controller.task command.
 */
if (strpos($command, '.') !== false) {
    /**
 *      Explode the controller.task command.
 */
    list ($type, $task) = explode('.', $command);
}

for ($e = 0; $e < count($arrExtensions); $e++)
{
    $extension = $arrExtensions[$e];
    $extensionname = $arrExtensions[$e];
    $extensionpath = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_sportsmanagement'.DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$extension;  

    if($app->isClient('administrator') ) {
          $base_path = $extensionpath.DIRECTORY_SEPARATOR.'admin';
        /**
 *          language file
 */
          $lang->load('com_sportsmanagement_'.$extension, $base_path);
    }

    /**
 * set the base_path to the extension controllers directory
 */
    if(is_dir($base_path)) {
        $params = array('base_path'=>$base_path);
    }
    else
    {
        $params = array();
    }

    /**
  *  own controllers
  */
    if (!file_exists($base_path.DIRECTORY_SEPARATOR.'controller.php') ) {
        if($type!=$extension) {
            $params = array();
        }
        $extension = "sportsmanagement";
    }
    elseif (!file_exists($base_path.DIRECTORY_SEPARATOR.$extension.'.php')) {
        if($type!=$extension) {
            $params = array();
        }
        $extension = "sportsmanagement";
    }

    /**
 *  import joomla controller library
 */
    jimport('joomla.application.component.controller');
    try
    {
          $controller = BaseController::getInstance(ucfirst($extension), $params);
    }
    catch (Exception $exc)
    {
        $controller    = BaseController::getInstance('sportsmanagement');
    }
 
    if (is_dir($base_path.DIRECTORY_SEPARATOR.'models')) {
        $model_pathes[] = $base_path.DIRECTORY_SEPARATOR.'models';
    }

    if (is_dir($base_path.DIRECTORY_SEPARATOR.'views')) {
        $view_pathes[] = $base_path.DIRECTORY_SEPARATOR.'views';
        $template_pathes[] = $base_path.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$extensionname.DIRECTORY_SEPARATOR.'tmpl';
    }

}

/**
 *  import joomla controller library
 */
jimport('joomla.application.component.controller');
$controller    = BaseController::getInstance('sportsmanagement');

if(is_null($controller) && !($controller instanceof BaseController)) {
    $controller    = BaseController::getInstance('sportsmanagement');
}

foreach ($model_pathes as $path)
{
    if(!empty($path)) {
        $controller->addModelPath($path, 'sportsmanagementModel');
    }
}

foreach ($view_pathes as $path)
{
    if(!empty($path)) {
        $controller->addViewPath($path, 'sportsmanagementView');
    }
}

for ($e = 0; $e < count($arrExtensions); $e++)
{
    $extension = $arrExtensions[$e];
    $extensionname = $arrExtensions[$e];
    foreach ($template_pathes as $path)
    {
        if(!empty($path)) {
            /**
 *         get view and set template context
 */
              $view = $controller->getView($extensionname, "html", "sportsmanagementView");
              $view->addTemplatePath($path);
      
        }
    }
}

/**
 *  Perform the Request task
 */
$controller->execute($task);

/**
 *  Redirect if set by the controller
 */
$controller->redirect();
