<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_club_birthday
 * @file       mod_sportsmanagement_club_birthday.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

//// Get the base version
//$baseVersion = substr(JVERSION, 0, 3);

//if (version_compare($baseVersion, '4.0', 'ge'))
//{
//	// Joomla! 4.0 code here
//	defined('JSM_JVERSION') or define('JSM_JVERSION', 4);
//}

//if (version_compare($baseVersion, '3.0', 'ge'))
//{
//	// Joomla! 3.0 code here
//	defined('JSM_JVERSION') or define('JSM_JVERSION', 3);
//}

//if (version_compare($baseVersion, '2.5', 'ge'))
//{
//	// Joomla! 2.5 code here
//	defined('JSM_JVERSION') or define('JSM_JVERSION', 2);
//}


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


if (!class_exists('sportsmanagementHelper'))
{
	// Add the classes for handling
	$classpath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';
	JLoader::register('sportsmanagementHelper', $classpath);
	BaseDatabaseModel::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

JLoader::import('components.com_sportsmanagement.helpers.route', JPATH_SITE);

/**
 *
 * Include the functions only once
 */
JLoader::register('modSportsmanagementClubBirthdayHelper', __DIR__ . '/helper.php');

// Reference global application object
$app             = Factory::getApplication();
$document        = Factory::getDocument();
$show_debug_info = ComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info', 0);

if (!defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE'))
{
	DEFINE('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database'));
}

if (ComponentHelper::getParams('com_sportsmanagement')->get('cfg_dbprefix'))
{
	$module->picture_server = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database_server');
}
else
{
	if (COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE || Factory::getApplication()->input->getInt('cfg_which_database', 0))
	{
		$module->picture_server = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database_server');
	}
	else
	{
		$module->picture_server = Uri::root();
	}
}

// Add css file
// $document->addStyleSheet(Uri::base().'modules/mod_sportsmanagement_club_birthday/css/mod_sportsmanagement_club_birthday.css');

$mode       = $params->def("mode");
$results    = $params->get('limit');
$limit      = $params->get('limit');
$refresh    = $params->def("refresh");
$minute     = $params->def("minute");
$height     = $params->def("height");
$width      = $params->def("width");
$season_ids = $params->def("s");

$futuremessage = htmlentities(trim(Text::_($params->get('futuremessage'))), ENT_COMPAT, 'UTF-8');

$clubs = modSportsmanagementClubBirthdayHelper::getClubs($limit, $season_ids);

if (count($clubs) > 1)
{
	$clubs = modSportsmanagementClubBirthdayHelper::jsm_birthday_sort($clubs, $params->def("sort_order"));
}

$k       = 0;
$counter = 0;

// Echo 'mode -> '.$mode.'<br>';
// echo 'refresh -> '.$refresh.'<br>';
// echo 'minute -> '.$minute.'<br>';

$layout = isset($attribs['layout']) ? $attribs['layout'] : 'default';

if (count($clubs) > 0)
{
	if (count($clubs) < $results)
	{
		$results = count($clubs);
	}

	$tickerpause = $params->def("tickerpause");
	$scrollspeed = $params->def("scrollspeed");
	$scrollpause = $params->def("scrollpause");

	switch ($mode)
	{
        case 'BC':
		$layout = 'default_carousel';
		// $document->addStyleSheet(Uri::base().'modules/mod_sportsmanagement_club_birthday/css/mod_sportsmanagement_club_birthday.css');
		break;
		case 'T':
			$layout = isset($attribs['layout']) ? $attribs['layout'] : 'default';
			include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'ticker.js';
			$document->addStyleSheet(Uri::base() . 'modules/mod_sportsmanagement_club_birthday/css/mod_sportsmanagement_club_birthday.css');
			break;

		case 'L':
			$layout = isset($attribs['layout']) ? $attribs['layout'] : 'default';

			// Include(dirname(__FILE__).DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'qscrollerh.js');
			// $document->addScript(Uri::base().'modules/mod_sportsmanagement_club_birthday/js/qscrollerh.js');
			// $document->addScript(Uri::base().'modules/mod_sportsmanagement_club_birthday/js/qscroller.js');
			// $document->addScript(Uri::base().'modules/mod_sportsmanagement_club_birthday/js/wowslider.js');
			$document->addStyleSheet(Uri::base() . 'modules/mod_sportsmanagement_club_birthday/css/mod_sportsmanagement_club_birthday.css');
			break;

	}
}
?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>"
     id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
	<?PHP
	require ModuleHelper::getLayoutPath($module->module, $layout);
	?>
</div>
