<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_club_birthday
 * @file       mod_sportsmanagement_club_birthday.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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

// Get the base version
$baseVersion = substr(JVERSION, 0, 3);

if (version_compare($baseVersion, '4.0', 'ge'))
{
	// Joomla! 4.0 code here
	defined('JSM_JVERSION') or define('JSM_JVERSION', 4);
}

if (version_compare($baseVersion, '3.0', 'ge'))
{
	// Joomla! 3.0 code here
	defined('JSM_JVERSION') or define('JSM_JVERSION', 3);
}

if (version_compare($baseVersion, '2.5', 'ge'))
{
	// Joomla! 2.5 code here
	defined('JSM_JVERSION') or define('JSM_JVERSION', 2);
}

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
		case 'B':
			$layout = isset($attribs['layout']) ? $attribs['layout'] : 'default';

			// $document->addStyleSheet(Uri::base().'modules/mod_sportsmanagement_club_birthday/css/mod_sportsmanagement_club_birthday.css');
			break;
		case 'T':
			$layout = isset($attribs['layout']) ? $attribs['layout'] : 'default';
			include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'ticker.js';
			$document->addStyleSheet(Uri::base() . 'modules/mod_sportsmanagement_club_birthday/css/mod_sportsmanagement_club_birthday.css');
			break;
		case 'V':

			$layout = isset($attribs['layout']) ? $attribs['layout'] : 'wowslider';

			// $layout = isset($attribs['layout'])?$attribs['layout']:'default';

			$html_li    = '';
			$html_ahref = '';
			$id         = 0;

			foreach ($clubs AS $club)
			{
				$club->default_picture = sportsmanagementHelper::getDefaultPlaceholder('clublogobig');
				$thispic               = "";
				$whenmessage           = "";
				$birthdaytext2         = "";
				$flag                  = $params->get('show_club_flag') ? JSMCountries::getCountryFlag($club->country) . "&nbsp;" : "";
				$text                  = $club->name;
				$usedname              = $flag . $text;
				$club_link             = "";
				$club_link             = sportsmanagementHelperRoute::getClubInfoRoute($club->project_id, $club->id);
				$showname              = HTMLHelper::link($club_link, $usedname);

				if ($params->get('show_picture') == 1)
				{
					if (file_exists(JPATH_BASE . '/' . $club->picture) && $club->picture != '')
					{
						$thispic = $club->picture;
					}
                    elseif (file_exists(JPATH_BASE . '/' . $club->default_picture) && $club->default_picture != '')
					{
						$thispic = $club->default_picture;
					}
				}

				switch ($club->days_to_birthday)
				{
					case 0:
						$whenmessage = $params->get('todaymessage');
						break;
					case 1:
						$whenmessage = $params->get('tomorrowmessage');
						break;
					default:
						$whenmessage = str_replace('%DAYS_TO%', $club->days_to_birthday, trim($futuremessage));
						break;
				}

				if ($club->founded != '0000-00-00')
				{
					$birthdaytext2  = htmlentities(trim(Text::_($params->get('birthdaytext'))), ENT_COMPAT, 'UTF-8');
					$dayformat      = htmlentities(trim($params->get('dayformat')));
					$birthdayformat = htmlentities(trim($params->get('birthdayformat')));
					$birthdaytext2  = str_replace('%WHEN%', $whenmessage, $birthdaytext2);
					$birthdaytext2  = str_replace('%AGE%', $club->age, $birthdaytext2);
					$birthdaytext2  = str_replace('%DATE%', strftime($dayformat, strtotime($club->year . '-' . $club->daymonth)), $birthdaytext2);
					$birthdaytext2  = str_replace('%DATE_OF_BIRTH%', strftime($birthdayformat, strtotime($club->date_of_birth)), $birthdaytext2);
				}
				else
				{
					$birthdaytext2 = htmlentities(trim(Text::_($params->get('birthdaytextyear'))), ENT_COMPAT, 'UTF-8');
					$birthdaytext2 = str_replace('%AGE%', $club->age_year, $birthdaytext2);
				}

				$birthdaytext2 = str_replace('%BR%', '<br />', $birthdaytext2);
				$birthdaytext2 = str_replace('%BOLD%', '<b>', $birthdaytext2);
				$birthdaytext2 = str_replace('%BOLDEND%', '</b>', $birthdaytext2);

				$text    .= '<br> ' . $birthdaytext2;
				$html_li .= '<li><a href="' . $club_link . '"><img src="' . $thispic . '" alt="' . $text . '" title="' . $text . '" id="wows1_' . $id . '" /></a></li>';

				// $html_li .= '<li><img src="'.$thispic.'" alt="" title="" id="wows1_'.$id.'" /></li>';
				$id++;
				$html_ahref .= '<a href="#" title=""><img src="' . $thispic . '" alt=""    />' . $id . '</a>';
			}

			// $wowslider_style = "basic_linear";
			// $wowslider_style = "squares";
			// $wowslider_style = "fade";
			$wowslider_style = $params->def("wowsliderstyle");

			// Include(dirname(__FILE__).DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'qscrollerv.js');
			// $document->addScript(Uri::base().'modules/mod_sportsmanagement_club_birthday/js/qscrollerv.js');
			// $document->addScript(Uri::base().'modules/mod_sportsmanagement_club_birthday/js/qscroller.js');
			// $document->addScript(Uri::base().'modules/mod_sportsmanagement_club_birthday/js/jquery.simplyscroll.js');
			$document->addStyleSheet(Uri::base() . 'modules/mod_sportsmanagement_club_birthday/wowslider/' . $wowslider_style . '/style.css');

			// $document->addScript(Uri::base().'modules/mod_sportsmanagement_club_birthday/js/wowslider.js');
			// $document->addScript(Uri::base().'modules/mod_sportsmanagement_club_birthday/js/'.$wowslider_style.'.js');

			break;
		case 'L':
			$layout = isset($attribs['layout']) ? $attribs['layout'] : 'default';

			// Include(dirname(__FILE__).DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'qscrollerh.js');
			// $document->addScript(Uri::base().'modules/mod_sportsmanagement_club_birthday/js/qscrollerh.js');
			// $document->addScript(Uri::base().'modules/mod_sportsmanagement_club_birthday/js/qscroller.js');
			// $document->addScript(Uri::base().'modules/mod_sportsmanagement_club_birthday/js/wowslider.js');
			$document->addStyleSheet(Uri::base() . 'modules/mod_sportsmanagement_club_birthday/css/mod_sportsmanagement_club_birthday.css');
			break;

		case 'J':
			$container = 'slider' . $module->id . '_container';
			$layout    = isset($attribs['layout']) ? $attribs['layout'] : 'jssor';
			$document->addScript(Uri::base() . 'modules/mod_sportsmanagement_club_birthday/js/jssor.slider.mini.js');

			$html_li    = '';
			$html_ahref = '';
			$id         = 0;

			foreach ($clubs AS $club)
			{
				$club->default_picture = sportsmanagementHelper::getDefaultPlaceholder('clublogobig');
				$thispic               = "";
				$whenmessage           = "";
				$birthdaytext2         = "";
				$flag                  = $params->get('show_club_flag') ? JSMCountries::getCountryFlag($club->country) . "&nbsp;" : "";
				$text                  = '';
				$usedname              = $flag . $text;
				$club_link             = "";
				$club_link             = sportsmanagementHelperRoute::getClubInfoRoute($club->project_id, $club->id);
				$showname              = HTMLHelper::link($club_link, $usedname);

				if ($params->get('show_picture') == 1)
				{
					if (file_exists(JPATH_BASE . '/' . $club->picture) && $club->picture != '')
					{
						$thispic = $club->picture;
					}
                    elseif (file_exists(JPATH_BASE . '/' . $club->default_picture) && $club->default_picture != '')
					{
						$thispic = $club->default_picture;
					}
				}

				switch ($club->days_to_birthday)
				{
					case 0:
						$whenmessage = $params->get('todaymessage');
						break;
					case 1:
						$whenmessage = $params->get('tomorrowmessage');
						break;
					default:
						$whenmessage = str_replace('%DAYS_TO%', $club->days_to_birthday, trim($futuremessage));
						break;
				}

				if ($club->founded != '0000-00-00')
				{
					$birthdaytext2  = htmlentities(trim(Text::_($params->get('birthdaytext'))), ENT_COMPAT, 'UTF-8');
					$dayformat      = htmlentities(trim($params->get('dayformat')));
					$birthdayformat = htmlentities(trim($params->get('birthdayformat')));
					$birthdaytext2  = str_replace('%WHEN%', $whenmessage, $birthdaytext2);
					$birthdaytext2  = str_replace('%AGE%', $club->age, $birthdaytext2);
					$birthdaytext2  = str_replace('%DATE%', strftime($dayformat, strtotime($club->year . '-' . $club->daymonth)), $birthdaytext2);
					$birthdaytext2  = str_replace('%DATE_OF_BIRTH%', strftime($birthdayformat, strtotime($club->date_of_birth)), $birthdaytext2);
				}
				else
				{
					$birthdaytext2 = htmlentities(trim(Text::_($params->get('birthdaytextyear'))), ENT_COMPAT, 'UTF-8');
					$birthdaytext2 = str_replace('%AGE%', $club->age_year, $birthdaytext2);
				}

				$birthdaytext2 = str_replace('%BR%', '<br />', $birthdaytext2);
				$birthdaytext2 = str_replace('%BOLD%', '<b>', $birthdaytext2);
				$birthdaytext2 = str_replace('%BOLDEND%', '</b>', $birthdaytext2);

				$text .= '<br> ' . $birthdaytext2;

				$html_li .= '<div><a href="' . $club_link . '"><img u="image" src="' . $thispic . '" /></a>';

				$html_li .= '<div u="caption" t="' . $params->get('jssor_captiontransitions') . '" style="position:absolute;left:10px;top:80px;width:600px;height:40px;font-size:36px;color:#000;line-height:40px;">' . $club->name . '</div>';
				$html_li .= '<div u="caption" t="' . $params->get('jssor_captiontransitions') . '" style="position:absolute;left:10px;top:130px;width:600px;height:40px;font-size:36px;color:#000;line-height:40px;">' . $text . '</div>';

				$html_li .= '</div>';

				$id++;
				$html_ahref .= '<a href="#" title=""><img src="' . $thispic . '" alt=""    />' . $id . '</a>';
			}


			$document->addStyleSheet(Uri::base() . 'modules/' . $module->module . '/css/' . $module->module . '.css');
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
