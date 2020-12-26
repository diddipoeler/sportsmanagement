<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @file       sportsmanagement.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Component\ComponentHelper;

/** zur unterscheidung von joomla 3 und 4 */
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.view', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.controller', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.table', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.formbehavior2', JPATH_ADMINISTRATOR);

/** Get the base version */
$baseVersion = substr(JVERSION, 0, 3);

if (version_compare($baseVersion, '4.0', 'ge'))
{
	/** Joomla! 4.0 code here */
	defined('JSM_JVERSION') or define('JSM_JVERSION', 4);
}


if (version_compare($baseVersion, '3.0', 'ge'))
{
	/** Joomla! 3.0 code here */
	defined('JSM_JVERSION') or define('JSM_JVERSION', 3);
}


if (version_compare($baseVersion, '2.5', 'ge'))
{
	/** Joomla! 2.5 code here */
	defined('JSM_JVERSION') or define('JSM_JVERSION', 2);
}

//if (!defined('DS'))
//{
//	define('DS', DIRECTORY_SEPARATOR);
//}

if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

$document = Factory::getDocument();
$app      = Factory::getApplication();
$config   = Factory::getConfig();
$input    = $app->input;

/**
 *  prüft vor Benutzung ob die gewünschte Klasse definiert ist
 */
if (!class_exists('sportsmanagementHelper'))
{
	/**
	 * add the classes for handling
	 */
	$classpath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';
	JLoader::register('sportsmanagementHelper', $classpath);
	BaseDatabaseModel::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

/**
 * was wird benöigt ?
 */
JLoader::import('components.com_sportsmanagement.helpers.route', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.helpers.html', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.helpers.countries', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.helpers.simpleGMapGeocoder', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.models.project', JPATH_SITE);

$view = $input->getVar("view");

switch ($view)
{
	case 'allprojects':
		JLoader::import('components.com_sportsmanagement.models.leagues', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_sportsmanagement.models.seasons', JPATH_ADMINISTRATOR);
		break;
	case 'ranking':
	case 'curve':
    case 'leaguechampionoverview':
		JLoader::import('components.com_sportsmanagement.helpers.ranking', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.models.clubnames', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_sportsmanagement.models.rounds', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_sportsmanagement.models.projectteams', JPATH_ADMINISTRATOR);
		break;
	case 'results':
		JLoader::import('components.com_sportsmanagement.helpers.comments', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.helpers.pagination', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.models.rounds', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_sportsmanagement.models.round', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_sportsmanagement.models.match', JPATH_ADMINISTRATOR);
		break;
	case 'editmatch':
	case 'jltournamenttree':
		JLoader::import('components.com_sportsmanagement.models.match', JPATH_ADMINISTRATOR);
		break;
	case 'matchreport':
		JLoader::import('components.com_sportsmanagement.helpers.comments', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.models.playground', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_sportsmanagement.models.match', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_sportsmanagement.models.round', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_sportsmanagement.models.player', JPATH_SITE);
		break;
	case 'resultsranking':
		JLoader::import('components.com_sportsmanagement.models.ranking', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.models.results', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.helpers.ranking', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.models.rounds', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_sportsmanagement.models.round', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_sportsmanagement.models.projectteams', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_sportsmanagement.helpers.pagination', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.helpers.comments', JPATH_SITE);
		break;
	case 'resultsmatrix':
		JLoader::import('components.com_sportsmanagement.models.projectteams', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_sportsmanagement.helpers.pagination', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.models.matrix', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.models.results', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.helpers.comments', JPATH_SITE);
		break;
	case 'roster':
    case 'rosteralltime':
		JLoader::import('components.com_sportsmanagement.models.player', JPATH_SITE);
		break;
	case 'teamplan':
		JLoader::import('components.com_sportsmanagement.helpers.comments', JPATH_SITE);
		break;
	case 'teams':
	case 'teamstats':
	case 'teamstree':
	case 'matrix':
	case 'rankingalltime':
	case 'clubinfo':
	case 'clubplan':
	case 'eventsranking':
	case 'stats':
	case 'about':
		break;
	case 'editclub':
		JLoader::import('components.com_sportsmanagement.models.clubinfo', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.helpers.imageselect', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.helpers.JSON', JPATH_SITE);
		break;
	case 'editperson':
		JLoader::import('components.com_sportsmanagement.models.person', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.helpers.imageselect', JPATH_SITE);
		break;
	case 'player':
	case 'staff':
	case 'referee':
		JLoader::import('components.com_sportsmanagement.models.person', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.models.eventtypes', JPATH_ADMINISTRATOR);
		break;
	case 'teaminfo':
		JLoader::import('components.com_sportsmanagement.helpers.ranking', JPATH_SITE);
		break;
	case 'playground':
		JLoader::import('components.com_sportsmanagement.models.playground', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_sportsmanagement.models.teams', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_sportsmanagement.models.team', JPATH_ADMINISTRATOR);
		break;
	case 'nextmatch':
		JLoader::import('components.com_sportsmanagement.helpers.comments', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.helpers.ranking', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.models.playground', JPATH_ADMINISTRATOR);
		JLoader::import('components.com_sportsmanagement.models.match', JPATH_ADMINISTRATOR);
		break;
	case 'ical':
		JLoader::import('components.com_sportsmanagement.helpers.iCalcreator', JPATH_SITE);
		break;
	case 'scoresheet':
		JLoader::import('components.com_sportsmanagement.helpers.scoresheet', JPATH_SITE);
		break;
	case 'predictionrules':
	case 'predictionranking':
	case 'predictionusers':
	case 'predictionuser':
	case 'predictionentry':
	case 'predictionresults':
		JLoader::import('components.com_sportsmanagement.helpers.predictionroute', JPATH_SITE);
		JLoader::import('components.com_sportsmanagement.models.prediction', JPATH_SITE);
		break;
}

$paramscomponent = ComponentHelper::getParams('com_sportsmanagement');

if (!defined('COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS'))
{
	DEFINE('COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS', $paramscomponent->get('boostrap_div_class'));
}

if (!defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE'))
{
	DEFINE('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', $paramscomponent->get('cfg_which_database'));
}

if (!defined('COM_SPORTSMANAGEMENT_LOAD_BOOTSTRAP'))
{
	DEFINE('COM_SPORTSMANAGEMENT_LOAD_BOOTSTRAP', $paramscomponent->get('cfg_load_bootstrap'));
}

if (!defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO'))
{
	DEFINE('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO', $paramscomponent->get('show_debug_info'));
}


if (!defined('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO'))
{
	DEFINE('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO', $paramscomponent->get('show_query_debug_info'));
}

if ($paramscomponent->get('cfg_dbprefix') && !defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER'))
{
	DEFINE('COM_SPORTSMANAGEMENT_PICTURE_SERVER', $paramscomponent->get('cfg_which_database_server'));
}
else
{
	if (COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE || Factory::getApplication()->input->getInt('cfg_which_database', 0))
	{
		if (!defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER'))
		{
			DEFINE('COM_SPORTSMANAGEMENT_PICTURE_SERVER', $paramscomponent->get('cfg_which_database_server'));
		}
	}
	else
	{
		if (!defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER'))
		{
			DEFINE('COM_SPORTSMANAGEMENT_PICTURE_SERVER', Uri::root());
		}
	}
}

/**
 * sprachdatei aus dem backend laden
 */
$lang         = Factory::getLanguage();
$extension    = 'com_sportsmanagement';
$base_dir     = JPATH_ADMINISTRATOR;
$language_tag = $lang->getTag();
$reload       = true;
$lang->load($extension, $base_dir, $language_tag, $reload);

$document->addScript(Uri::root(true) . '/components/com_sportsmanagement/assets/js/sm_functions.js');

/**
 * meta daten der komponente setzen
 */
$meta_keys = array();

if (version_compare(JVERSION, '3.0.0', 'ge'))
{
	$meta_keys[] = $config->get('config.MetaKeys');
}
else
{
	$meta_keys[] = $config->getValue('config.MetaKeys');
}

$project_id = $input->getInt("p");

if (!empty($project_id))
{
	sportsmanagementModelProject::$projectid = $project_id;
	$teams                                   = sportsmanagementModelProject::getTeams();


	if ($teams)
	{
		foreach ($teams as $team)
		{
			$meta_keys[] = $team->name;
		}
	}
}


$document->setMetaData('author', 'Dieter Ploeger');
$document->setMetaData('revisit-after', '2 days');
$document->setMetaData('robots', 'index,follow');

/**
 *
 * meta name
 * keywords
 * description
 * generator
 */
$document->setMetaData('keywords', implode(",", $meta_keys));
$document->setMetaData('generator', "JSM - Sports Management");

unset($meta_keys);

$task   = $input->getCmd('task');
$option = $input->getCmd('option');
$view   = $input->getVar("view");
$view   = ucfirst(strtolower($view));

// $modal_popup_width = ComponentHelper::getParams($option)->get('modal_popup_width', 0);
// $modal_popup_height = ComponentHelper::getParams($option)->get('modal_popup_height', 0);


DEFINE('COM_SPORTSMANAGEMENT_SHOW_HELP_SERVER', ComponentHelper::getParams($option)->get('cfg_help_server', ''));
DEFINE('COM_SPORTSMANAGEMENT_SHOW_BUGTRACKER_SERVER', ComponentHelper::getParams($option)->get('cfg_bugtracker_server', ''));
DEFINE('COM_SPORTSMANAGEMENT_SHOW_VIEW', $view);

require_once JPATH_SITE . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'controller.php';

// Component Helper
jimport('joomla.application.component.helper');
$controller = null;

if (is_null($controller) && !($controller instanceof BaseController))
{
	// Fallback if no extensions controller has been initialized
	$controller = BaseController::getInstance('sportsmanagement');
}

$task = $input->getCmd('task');

// Den 'task' der im Request übergeben wurde ausführen
$controller->execute($task);
$controller->redirect();


