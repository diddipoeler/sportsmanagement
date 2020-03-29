<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      mod_sportsmanagement_matches.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_matches
 */

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;

$app = Factory::getApplication();

$maxImportTime = 480;
if ((int)ini_get('max_execution_time') < $maxImportTime) {
	@set_time_limit($maxImportTime);
}
$maxImportMemory = '350M';
if ((int)ini_get('memory_limit') < (int)$maxImportMemory) {
	@ini_set('memory_limit', $maxImportMemory);
}

if (!defined('JSM_PATH')) {
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

/**
 * prüft vor Benutzung ob die gewünschte Klasse definiert ist
 */
if (!class_exists('JSMModelLegacy')) {
	JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_SITE);
}
if (!class_exists('JSMCountries')) {
	JLoader::import('components.com_sportsmanagement.helpers.countries', JPATH_SITE);
}
if (!class_exists('sportsmanagementHelper')) {
//add the classes for handling
	$classpath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';
	JLoader::register('sportsmanagementHelper', $classpath);
	BaseDatabaseModel::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

if (!defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE')) {
	DEFINE('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database'));
}

if (!defined('_JSMMATCHLISTMODURL')) {
	define('_JSMMATCHLISTMODURL', Uri::base() . 'modules/' . $module->module . '/');
}

/** Include the functions only once */
JLoader::register('modMatchesSportsmanagementHelper', __DIR__ . '/helper.php');
JLoader::register('MatchesSportsmanagementConnector', __DIR__ . '/connectors/sportsmanagement.php');

/**
 * besonderheit für das inlinehockey update, wenn sich das
 * modul in einem artikel befindet
 *
 */
if ($params->get('ishd_update')) {
	$app = Factory::getApplication();
	$projectid = $params->get('p');
	JLoader::import('components.com_sportsmanagement.extensions.jsminlinehockey.admin.models.jsminlinehockey', JPATH_SITE);
	$actionsModel = BaseDatabaseModel::getInstance('jsminlinehockey', 'sportsmanagementModel');
	for ($a = 0; $a < sizeof($projectid); $a++) {
		$project_id = (int)$projectid[$a];
		$count_games = MatchesSportsmanagementConnector::getCountGames($project_id, (int)$params->get('ishd_update_hour', 4));
		if ($count_games) {
			$actionsModel->getmatches($project_id);
		}

	}

}

$ajax = $app->input->post->get('ajaxMListMod', 0);
$match_id = $app->input->post->get('match_id', 0);
$nr = $app->input->post->get('nr', -1);
$ajaxmod = $app->input->post->get('ajaxmodid', 0);

$template = $params->get('template', 'default');

if (version_compare(JVERSION, '3.0.0', 'ge')) {
} else {
	HTMLHelper::_('behavior.mootools');
}

$doc = Factory::getDocument();

if ($params->get('mootools')) {
	$doc->addScript(Uri::root() . '/media/system/js/mootools-core.js');
}

if (version_compare(JVERSION, '3.0.0', 'ge')) {
// Joomla! 3.0 code here
	$doc->addScript(_JSMMATCHLISTMODURL . 'assets/js/' . $module->module . '_joomla_3.js');
} elseif (version_compare(JVERSION, '2.5.0', 'ge')) {
// Joomla! 2.5 code here
	$doc->addScript(_JSMMATCHLISTMODURL . 'assets/js/' . $module->module . '_joomla_2.js');
}


$doc->addStyleSheet(_JSMMATCHLISTMODURL . 'tmpl/' . $template . DIRECTORY_SEPARATOR . $module->module . '.css');
$cssimgurl = ($params->get('use_icons') != '-1') ? _JSMMATCHLISTMODURL . 'assets/images/' . $params->get('use_icons') . '/'
	: _JSMMATCHLISTMODURL . 'assets/images/';
$doc->addStyleDeclaration('
div.tool-tip div.tool-title a.sticky_close{
	display:block;
	position:absolute;
	background:url(' . $cssimgurl . 'cancel.png) !important;
	width:16px;
	height:16px;
}
');
HTMLHelper::_('behavior.tooltip');
$doc->addScriptDeclaration('
  window.addEvent(\'domready\', function() {
    if ($$(\'#modJLML' . $module->id . 'holder .jlmlTeamname\')) addJLMLtips(\'#modJLML' . $module->id . 'holder .jlmlTeamname\', \'over\');
  }
  );
  ');
$mod = new MatchesSportsmanagementConnector($params, $module->id, $match_id);
$lastheading = '';
$oldprojectid = 0;
$oldround_id = 0;
if ($ajax == 0) {
	echo '<div id="modJLML' . $module->id . 'holder" class="modJLMLholder">';
}
$matches = $mod->getMatches();

$cnt = ($nr >= 0) ? $nr : 0;
if (count($matches) > 0) {
	//$user = Factory::getUser();
	foreach ($matches AS $key => $match) {
		if (!isset($match['project_id'])) continue;
		$styleclass = ($cnt % 2 == 1) ? $params->get('sectiontableentry1') : $params->get('sectiontableentry2');
		$show_pheading = false;
		$pheading = '';
		if (isset($match['type'])) {
			$heading = $params->get($match['type'] . '_notice');
		} else {
			$heading = '';
		}
		if ($match['project_id'] != $oldprojectid OR $match['round_id'] != $oldround_id) {
			if (!empty($match['heading'])) $show_pheading = true;
			$pheading .= $match['heading'];
		}
		include(ModuleHelper::getLayoutPath($module->module, $template . DIRECTORY_SEPARATOR . 'match'));
		$lastheading = $heading;
		$oldprojectid = $match['project_id'];
		$oldround_id = $match['round_id'];
		$cnt++;
	}
} elseif ($params->get('show_no_matches_notice')) {
	echo '<br />' . $params->get('no_matches_notice') . '<br />';
}
if ($ajax == 0) {
	echo '</div>';
}
