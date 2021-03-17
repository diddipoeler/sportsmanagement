<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.00
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_matchesslider
 * @file       mod_sportsmanagement_matchesslider.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

/**
 * prüft vor Benutzung ob die gewünschte Klasse definiert ist
 */
if (!class_exists('JSMModelLegacy'))
{
	JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_SITE);
}


if (!class_exists('JSMCountries'))
{
	JLoader::import('components.com_sportsmanagement.helpers.countries', JPATH_SITE);
}

/**
 * soll die externe datenbank genutzt werden ?
 */
if (ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database'))
{
	$module->picture_server = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database_server');
}
else
{
	$module->picture_server = Uri::root();
}

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

JLoader::import('components.com_sportsmanagement.models.project', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.models.results', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.helpers.route', JPATH_SITE);

// If (!defined('_JLMATCHLISTSLIDERMODPATH')) { define('_JLMATCHLISTSLIDERMODPATH', dirname( __FILE__ ));}
if (!defined('_JLMATCHLISTSLIDERMODURL'))
{
	define('_JLMATCHLISTSLIDERMODURL', Uri::base() . 'modules/' . $module->module . '/');
}

/**
 *
 * Include the functions only once
 */
JLoader::register('modMatchesSliderHelper', __DIR__ . '/helper.php');
JLoader::register('MatchesSliderSportsmanagementConnector', __DIR__ . '/connectors/sportsmanagement.php');

// Welche joomla version ?
if (version_compare(JVERSION, '3.0.0', 'ge'))
{
	HTMLHelper::_('behavior.framework', true);
}
else
{
	HTMLHelper::_('behavior.mootools');
}

$app    = Factory::getApplication();
$jinput = $app->input;
$doc    = Factory::getDocument();
$doc->addScript(_JLMATCHLISTSLIDERMODURL . 'assets/js/jquery.simplyscroll.js');
$doc->addStyleSheet(_JLMATCHLISTSLIDERMODURL . 'assets/css/' . $module->module . '.css');

if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
}
elseif (version_compare(substr(JVERSION, 0, 3), '3.0', 'ge'))
{	
HTMLHelper::_('behavior.tooltip');
}

$config        = array();
$slidermatches = array();

$cfg_which_database = $jinput->getInt('cfg_which_database', 0);
$s                  = $jinput->getInt('s', 0);
$projectid          = $jinput->getInt('p', 0);

if (!$projectid)
{
	$cfg_which_database = $params->get('cfg_which_database');
	$s                  = $params->get('s');

	foreach ($params->get('p') as $key => $value)
	{
		$projectid = (int) $value;

		if ($params->get('teams'))
		{
			foreach ($params->get('teams') as $keyteam => $valueteam)
			{
				sportsmanagementModelProject::$projectid          = $projectid;
				sportsmanagementModelProject::$cfg_which_database = $cfg_which_database;
				sportsmanagementModelResults::$projectid          = $projectid;
				sportsmanagementModelResults::$cfg_which_database = $cfg_which_database;
				$matches                                          = sportsmanagementModelResults::getResultsRows(0, 0, $config, $params, $cfg_which_database, (int) $valueteam);
				$slidermatches                                    = array_merge($slidermatches, $matches);
			}
		}
		else
		{
			sportsmanagementModelProject::$projectid          = $projectid;
			sportsmanagementModelProject::$cfg_which_database = $cfg_which_database;
			sportsmanagementModelResults::$projectid          = $projectid;
			sportsmanagementModelResults::$cfg_which_database = $cfg_which_database;
			$matches                                          = sportsmanagementModelResults::getResultsRows(0, 0, $config, $params, $cfg_which_database);
			$slidermatches                                    = array_merge($slidermatches, $matches);
		}
	}
}
else
{
	sportsmanagementModelProject::$projectid          = $projectid;
	sportsmanagementModelProject::$cfg_which_database = $cfg_which_database;
	sportsmanagementModelResults::$projectid          = $projectid;
	sportsmanagementModelResults::$cfg_which_database = $cfg_which_database;
	$matches                                          = sportsmanagementModelResults::getResultsRows(0, 0, $config, $params, $cfg_which_database);
	$slidermatches                                    = array_merge($slidermatches, $matches);
}

usort(
	$slidermatches, function ($a, $b) {
	return $a->match_timestamp - $b->match_timestamp;
}
);


foreach ($slidermatches as $match)
{
	$routeparameter                       = array();
	$routeparameter['cfg_which_database'] = $cfg_which_database;
	$routeparameter['s']                  = $s;
	$routeparameter['p']                  = $match->project_slug;

	switch ($params->get('p_link_func'))
	{
		case 'results':
			$routeparameter['r']        = $match->round_slug;
			$routeparameter['division'] = 0;
			$routeparameter['mode']     = 0;
			$routeparameter['order']    = '';
			$routeparameter['layout']   = '';
			$link                       = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);
			break;
		case 'ranking':
			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $cfg_which_database;
			$routeparameter['s']                  = $s;
			$routeparameter['p']                  = $match->project_slug;
			$routeparameter['type']               = 0;
			$routeparameter['r']                  = $match->round_slug;
			$routeparameter['from']               = 0;
			$routeparameter['to']                 = 0;
			$routeparameter['division']           = 0;
			$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);
			break;
		case 'resultsrank':
			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $cfg_which_database;
			$routeparameter['s']                  = $s;
			$routeparameter['p']                  = $match->project_slug;
			$routeparameter['r']                  = $match->round_slug;
			$routeparameter['division']           = 0;
			$routeparameter['mode']               = 0;
			$routeparameter['order']              = '';
			$routeparameter['layout']             = '';
			$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsranking', $routeparameter);
			break;
	}
}


?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>"
     id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
	<?PHP
	require ModuleHelper::getLayoutPath($module->module);
	?>
</div>
