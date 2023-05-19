<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_club_birthday
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

$refresh = $params->def("refresh");
$minute  = $params->def("minute");
$height  = $params->def("height");
$width   = $params->def("width");


if ($refresh == 1)
{
	$birthdaytext = "<script type=\"text/javascript\" language=\"javascript\">
			var reloadTimer = null;
			window.onload = function()
			{
			    setReloadTime($minute);
		    }
			function setReloadTime(secs)
				{
				    if (arguments.length == 1) {
			        if (reloadTimer) clearTimeout(reloadTimer);
			        reloadTimer = setTimeout(\"setReloadTime()\", Math.ceil(parseFloat(secs) * 1000));
			    }
		    else {
		        location.reload();
		    }
			}
		 </script>
<div align=\"center\"><a href=\"javascript:location.reload();\"><img src=\"modules/mod_sportsmanagement_club_birthday/css/icon_refresh.gif\" border=\"0\" title=\"Refresh\">&nbsp;&nbsp;&nbsp;&nbsp;<b>Refresh</b></a></div><br>";
}
else
{
	$birthdaytext = "";
}

$id       = 1;
$idstring = '';
$idstring = $id . $params->get('moduleclass_sfx');


?>

<?PHP
if (count($clubs) > 0)
{
	switch ($mode)
	{

		case 'T':
		case 'L':
			foreach ($clubs AS $club)
			{
				$idstring = $id . $params->get('moduleclass_sfx');

				if ($mode == 'T')
				{
					$birthdaytext .= '<div id="' . $idstring . '" class="textdiv">';
				}

				$club->default_picture = sportsmanagementHelper::getDefaultPlaceholder('clublogobig');

				if (($params->get('limit') > 0) && ($counter == intval($params->get('limit'))))
				{
					break;
				}

				$class = ($k == 0) ? $params->get('sectiontableentry1') : $params->get('sectiontableentry2');

				$thispic = "";
				$flag    = $params->get('show_club_flag') ? JSMCountries::getCountryFlag($club->country) . "&nbsp;" : "";

				$text     = $club->name;
				$usedname = $flag . $text;

				$club_link = "";
				$club_link = sportsmanagementHelperRoute::getClubInfoRoute($club->project_id,
					$club->id
				);

				$showname = HTMLHelper::link($club_link, $usedname);

				$birthdaytext .= '<div class="qslide">';

				$birthdaytext .= '<div class="tckproject"><p>' . $showname . '</p></div>';

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

					$birthdaytext .= '<div style="width:100%"><center><img style="" src="' . Uri::base() . '/' . $thispic . '" alt="' . $text . '" title="' . $text . '"';

					if ($params->get('picture_width') != '')
					{
						$birthdaytext .= ' width="' . $params->get('picture_width') . '"';
					}

					$birthdaytext .= ' /></center></div><br />';
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
					$birthdaytext2  = str_replace('%DATE%', date($dayformat, strtotime($club->year . '-' . $club->daymonth)), $birthdaytext2);
					$birthdaytext2  = str_replace('%DATE_OF_BIRTH%', date($birthdayformat, strtotime($club->date_of_birth)), $birthdaytext2);
				}
				else
				{
					$birthdaytext2 = htmlentities(trim(Text::_($params->get('birthdaytextyear'))), ENT_COMPAT, 'UTF-8');
					$birthdaytext2 = str_replace('%AGE%', $club->age_year, $birthdaytext2);
				}

				$birthdaytext2 = str_replace('%BR%', '<br />', $birthdaytext2);
				$birthdaytext2 = str_replace('%BOLD%', '<b>', $birthdaytext2);
				$birthdaytext2 = str_replace('%BOLDEND%', '</b>', $birthdaytext2);
				$birthdaytext  .= '<div style="width:100%"><center>' . $birthdaytext2 . '</center></div><br />';

				$birthdaytext .= '</div>';


				if ($mode == 'T')
				{
					$birthdaytext .= "</div>";
				}

				$id++;
			}
			break;
	}
}


switch ($mode)
{
	// Ticker mode template
	case 'T':
		echo $birthdaytext;
		break;

	// List mode template
	case 'L':
		?>
        <div id="qscroller<?php echo $params->get('moduleclass_sfx'); ?>"><?php echo $birthdaytext; ?></div>
		<?php
		break;


	// Horizontal scroll mode template
	case 'H':
		?>
        <div id="qscroller<?php echo $params->get('moduleclass_sfx'); ?>"
             style="width:<?php echo $width; ?>px;height:<?php echo $height; ?>px"></div>
        <div class="hide"><?php echo $birthdaytext; ?></div>

		<?php
		break;
}
