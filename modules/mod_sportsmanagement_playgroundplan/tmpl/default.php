<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_playgroundplan
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$teamformat = $params->get('teamformat', 'name');
$dateformat = $params->get('dateformat');
$timeformat = $params->get('timeformat');
$mode       = $params->get('mode', 0);
$textdiv    = "";

$n = 1;
?>
<div class="<?php echo $params->get('divclassrow'); ?>" id="modjlplaygroundplan<?php echo $mode; ?>">
    <table class="<?php echo $params->get('table_class'); ?>">
		<?php
		foreach ($list as $match)
		{
			$playgroundname = "";
			$playground_id  = 0;
			$picture        = "";

			if ($mode == 0)
			{
				$textdiv .= '<tr><td><div class="qslidejl">';
			}


			if ($mode == 1)
			{
				$odd     = $n & 1;
				$textdiv .= '<div id="jlplaygroundplanis' . $odd . '" class="jlplaygroundplantextdivlist">';
			}

			$n++;

			if ($params->get('show_playground_name', 0))
			{
				$textdiv .= '<div class="jlplplaneplname"> ';

				if ($match->playground_id != "")
				{
					$playgroundname = $match->playground_name;
					$playground_id  = $match->playground_slug;
				}
                elseif ($match->team_playground_id != "")
				{
					$playgroundname = $match->team_playground_name;
					$playground_id  = $match->playground_team_slug;
				}
                elseif ($match->club_playground_id != "")
				{
					$playgroundname = $match->club_playground_name;
					$playground_id  = $match->playground_club_slug;
				}

				if ($params->get('show_playground_link'))
				{
					$routeparameter                       = array();
					$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
					$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
					$routeparameter['p']                  = $match->project_slug;
					$routeparameter['pgid']               = $playground_id;
					$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('playground', $routeparameter);

					$playgroundname = HTMLHelper::link($link, Text::sprintf('%1$s', $playgroundname));
				}
				else
				{
					$playgroundname = Text::sprintf('%1$s', $playgroundname);
				}

				$textdiv .= $playgroundname . '</div>';
			}

			if ($params->get('show_playground_picture', 0))
			{
				$textdiv .= '<div class="jlplplaneplpicture"> ';

				if ($match->playground_id != "")
				{
					$picture = $match->playground_picture;
				}
                elseif ($match->team_playground_id != "")
				{
					$picture = $match->playground_team_picture;
				}
                elseif ($match->club_playground_id != "")
				{
					$picture = $match->playground_club_picture;
				}

				if ($picture)
				{
					$textdiv .= '<p>' . HTMLHelper::image($picture, "", "width=" . $params->get('picture_playground_width')) . '</p>';
				}

				$textdiv .= '</div>';
			}

			$textdiv .= '<div class="jlplplanedate">';
			$textdiv .= HTMLHelper::date($match->match_date, $dateformat);
			$textdiv .= " " . Text::_('MOD_SPORTSMANAGEMENT_PLAYGROUNDPLAN_JL_START_TIME') . " ";
			list($date, $time) = explode(" ", $match->match_date);
			$time    = strftime("%H:%M", strtotime($time));
			$textdiv .= $time;
			$textdiv .= '</div>';

			if ($params->get('show_project_name', 0))
			{
				$textdiv .= '<div class="jlplplaneleaguename">';

				$textdiv .= $match->project_name;
				$textdiv .= '</div>';
			}

			if ($params->get('show_league_name', 0))
			{
				$textdiv .= '<div class="jlplplaneleaguename">';
				$textdiv .= $match->league_name;
				$textdiv .= '</div>';
			}

			$textdiv .= '<div>';
			$textdiv .= '<div class="jlplplanetname">';

			if ($params->get('show_club_logo'))
			{
				$team1logo = modSportsmanagementPlaygroundplanHelper::getTeamLogo($match->team1, $params->get('show_picture'));

				if ($params->get('show_picture') == 'logo_big')
				{
					$textdiv .= '<p>' . HTMLHelper::image($team1logo, "", "width=" . $params->get('picture_width')) . '</p>';
				}
				else
				{
					$textdiv .= '<p>' . HTMLHelper::image($team1logo, "") . '</p>';
				}
			}

			$textdiv .= '<p>' . modSportsmanagementPlaygroundplanHelper::getTeams($match->team1, $teamformat) . '</p>';
			$textdiv .= '</div>';
			$textdiv .= '<div class="jlplplanetnamesep"> - </div>';
			$textdiv .= '<div class="jlplplanetname">';

			if ($params->get('show_club_logo'))
			{
				$team2logo = modSportsmanagementPlaygroundplanHelper::getTeamLogo($match->team2, $params->get('show_picture'));

				if ($params->get('show_picture') == 'logo_big')
				{
					$textdiv .= '<p>' . HTMLHelper::image($team2logo, "", "width=" . $params->get('picture_width')) . '</p>';
				}
				else
				{
					$textdiv .= '<p>' . HTMLHelper::image($team2logo, "") . '</p>';
				}
			}

			$textdiv .= '<p>' . modSportsmanagementPlaygroundplanHelper::getTeams($match->team2, $teamformat) . '</p>';
			$textdiv .= '</div>';
			$textdiv .= '</div>';
			$textdiv .= '<div style="clear:both"></div>';
			$textdiv .= '</div></td></tr>';
		}

		echo $textdiv;
		?>
    </table>
</div>
