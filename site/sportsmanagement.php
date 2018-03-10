<?php

/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      sportsmanagement.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// zur unterscheidung von joomla 2.5 und 3
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.view', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_ADMINISTRATOR);

// Get the base version
$baseVersion = substr(JVERSION, 0, 3);

if (version_compare($baseVersion, '4.0', 'ge')) {
// Joomla! 4.0 code here
    defined('JSM_JVERSION') or define('JSM_JVERSION', 4);
}
if (version_compare($baseVersion, '3.0', 'ge')) {
// Joomla! 3.0 code here
    defined('JSM_JVERSION') or define('JSM_JVERSION', 3);
}
if (version_compare($baseVersion, '2.5', 'ge')) {
// Joomla! 2.5 code here
    defined('JSM_JVERSION') or define('JSM_JVERSION', 2);
}

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('JSM_PATH')) {
    DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

$document = JFactory::getDocument();
$app = JFactory::getApplication();
$config = JFactory::getConfig();
$input = $app->input;

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
if (!class_exists('sportsmanagementHelper')) {
//add the classes for handling
    $classpath = JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'helpers' . DS . 'sportsmanagement.php';
    JLoader::register('sportsmanagementHelper', $classpath);
    JModelLegacy::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}


require_once(JPATH_SITE . DS . JSM_PATH . DS . 'helpers' . DS . 'html.php' );
require_once(JPATH_SITE . DS . JSM_PATH . DS . 'helpers' . DS . 'countries.php');
require_once(JPATH_SITE . DS . JSM_PATH . DS . 'helpers' . DS . 'ranking.php' );
require_once(JPATH_SITE . DS . JSM_PATH . DS . 'helpers' . DS . 'route.php' );
require_once(JPATH_SITE . DS . JSM_PATH . DS . 'helpers' . DS . 'imageselect.php' );
require_once(JPATH_SITE . DS . JSM_PATH . DS . 'helpers' . DS . 'predictionroute.php' );
require_once(JPATH_SITE . DS . JSM_PATH . DS . 'helpers' . DS . 'pagination.php' );
require_once(JPATH_SITE . DS . JSM_PATH . DS . 'helpers' . DS . 'simpleGMapGeocoder.php' );
require_once(JPATH_SITE . DS . JSM_PATH . DS . 'models' . DS . 'project.php' );
require_once(JPATH_SITE . DS . JSM_PATH . DS . 'models' . DS . 'results.php');
require_once(JPATH_SITE . DS . JSM_PATH . DS . 'models' . DS . 'person.php');
require_once(JPATH_SITE . DS . JSM_PATH . DS . 'models' . DS . 'prediction.php');

require_once(JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'models' . DS . 'divisions.php');
require_once(JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'models' . DS . 'rounds.php');
require_once(JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'models' . DS . 'leagues.php');
require_once(JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'models' . DS . 'seasons.php');
require_once(JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'models' . DS . 'round.php');
require_once(JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'models' . DS . 'teams.php');
require_once(JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'models' . DS . 'team.php');
require_once(JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'models' . DS . 'club.php');
require_once(JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'models' . DS . 'playground.php');
require_once(JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'models' . DS . 'projectteams.php');
require_once(JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'models' . DS . 'projectteam.php');
require_once(JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'models' . DS . 'match.php');
require_once(JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'models' . DS . 'databasetool.php');
require_once(JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'models' . DS . 'eventtypes.php');
require_once(JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'models' . DS . 'clubnames.php');

/**
 * sprachdatei aus dem backend laden
 */
$langtag = JFactory::getLanguage();

$paramscomponent = JComponentHelper::getParams('com_sportsmanagement');

if (!defined('COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS')) {
    DEFINE('COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS', $paramscomponent->get('boostrap_div_class'));
}

if (!defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE')) {
    DEFINE('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', $paramscomponent->get('cfg_which_database'));
}

if (!defined('COM_SPORTSMANAGEMENT_LOAD_BOOTSTRAP')) {
    DEFINE('COM_SPORTSMANAGEMENT_LOAD_BOOTSTRAP', $paramscomponent->get('cfg_load_bootstrap'));
}


if (!defined('COM_SPORTSMANAGEMENT_TABLE')) {
    DEFINE('COM_SPORTSMANAGEMENT_TABLE', $paramscomponent->get('cfg_which_database_table'));
}
if (!defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO')) {
    DEFINE('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO', $paramscomponent->get('show_debug_info'));
}
if (!defined('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO')) {
    DEFINE('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO', $paramscomponent->get('show_query_debug_info'));
}

if ($paramscomponent->get('cfg_dbprefix') && !defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')) {
    DEFINE('COM_SPORTSMANAGEMENT_PICTURE_SERVER', $paramscomponent->get('cfg_which_database_server'));
} else {
    if (COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE || JFactory::getApplication()->input->getInt('cfg_which_database', 0)) {
        if (!defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')) {
            DEFINE('COM_SPORTSMANAGEMENT_PICTURE_SERVER', $paramscomponent->get('cfg_which_database_server'));
        }
    } else {
        if (!defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')) {
            DEFINE('COM_SPORTSMANAGEMENT_PICTURE_SERVER', JURI::root());
        }
    }
}

$lang = JFactory::getLanguage();
$extension = 'com_sportsmanagement';
$base_dir = JPATH_ADMINISTRATOR;
$language_tag = $langtag->getTag();
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);

$document->addScript(JURI::root(true) . '/components/com_sportsmanagement/assets/js/sm_functions.js');

//$document->addScriptDeclaration('jQuery.noConflict();');

/**
 * meta daten der komponente setzen
 */
$meta_keys = array();

if (version_compare(JVERSION, '3.0.0', 'ge')) {
    $meta_keys[] = $config->get('config.MetaKeys');
} else {
    $meta_keys[] = $config->getValue('config.MetaKeys');
}

$project_id = $input->getInt("p");

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_id <br><pre>'.print_r($project_id,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' MetaKeys <br><pre>'.print_r($meta_keys,true).'</pre>'),'');

if ($project_id) {
    sportsmanagementModelProject::$projectid = $project_id;
    $teams = sportsmanagementModelProject::getTeams();
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams <br><pre>'.print_r($teams,true).'</pre>'),'');

    if ($teams) {
        foreach ($teams as $team) {
            $meta_keys[] = $team->name;
        }
    }
}

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' MetaKeys <br><pre>'.print_r($meta_keys,true).'</pre>'),'');

$document->setMetaData('author', 'Dieter Ploeger');
$document->setMetaData('revisit-after', '2 days');
$document->setMetaData('robots', 'index,follow');

/** meta name
 * keywords
 * description
 * generator
 * 
 */
$document->setMetaData('keywords', implode(",", $meta_keys));
$document->setMetaData('generator', "JSM - Joomla Sports Management");

$task = $input->getCmd('task');
$option = $input->getCmd('option');
$view = $input->getVar("view");
$view = ucfirst(strtolower($view));
//$cfg_help_server = JComponentHelper::getParams($option)->get('cfg_help_server','') ;
$modal_popup_width = JComponentHelper::getParams($option)->get('modal_popup_width', 0);
$modal_popup_height = JComponentHelper::getParams($option)->get('modal_popup_height', 0);
//$cfg_bugtracker_server = JComponentHelper::getParams($option)->get('cfg_bugtracker_server','') ;

DEFINE('COM_SPORTSMANAGEMENT_SHOW_HELP_SERVER', JComponentHelper::getParams($option)->get('cfg_help_server', ''));
DEFINE('COM_SPORTSMANAGEMENT_SHOW_BUGTRACKER_SERVER', JComponentHelper::getParams($option)->get('cfg_bugtracker_server', ''));
DEFINE('COM_SPORTSMANAGEMENT_SHOW_VIEW', $view);

require_once( JPATH_SITE . DS . JSM_PATH . DS . 'controller.php' );
// Component Helper
jimport('joomla.application.component.helper');
$controller = null;
if (is_null($controller) && !($controller instanceof JControllerLegacy)) {
    //fallback if no extensions controller has been initialized
    $controller = JControllerLegacy::getInstance('sportsmanagement');
}

$task = $input->getCmd('task');
// Den 'task' der im Request übergeben wurde ausführen
$controller->execute($task);
//$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();
?>
