<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_sports_type_statistics
 * @file       mod_sportsmanagement_sports_type_statistics.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

// Check if any results returned
if ($data['projectscount'] == 0)
{
	echo '<p class="modjlgsports">' . Text::_('MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_NO_PROJECTS') . '</p>';

	return;
}
else
{
	?>
	<div class="">
		<h4>
			<?php
			if ($data['sportstype'][$sportstypes]->icon)
			{
	?><img
				src="<?php echo $data['sportstype'][$sportstypes]->icon; ?>"
alt=""/>    <?php } ?><?php echo Text::_($data['sportstype'][$sportstypes]->name); ?>
		</h4>
		<ul class="list-group">
			<?php if ($params->get('show_project', 1) == 1)
			{
	?>

				<li class="list-group-item"><?php
				if ($params->get('show_icon', 1) == 1)
				{
					echo '<img alt="' . Text::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_PROJECTS") . '" src="administrator/components/com_sportsmanagement/assets/images/projects.png"/>';
					echo ' ' . Text::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_PROJECTS");
				}
				else
				{
					echo Text::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_PROJECTS");
				}
					?>
					<span class="badge"><?php echo $data['projectscount'] ?></span>
				</li>
			<?php } ?>

			<?php if ($params->get('show_leagues', 1) == 1)
			{
	?>

				<li class="list-group-item"><?php
				if ($params->get('show_icon', 1) == 1)
				{
					echo '<img alt="' . Text::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_LEAGUES") . '" src="administrator/components/com_sportsmanagement/assets/images/leagues.png"/>';
					echo ' ' . Text::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_LEAGUES");
				}
				else
				{
					echo Text::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_LEAGUES");
				}
					?>
					<span class="badge"><?php echo $data['leaguescount'] ?></span>
				</li>
			<?php } ?>

			<?php if ($params->get('show_seasons', 1) == 1)
			{
	?>

				<li class="list-group-item"><?php
				if ($params->get('show_icon', 1) == 1)
				{
					echo '<img alt="' . Text::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_SEASONS") . '" src="administrator/components/com_sportsmanagement/assets/images/seasons.png"/>';
					echo ' ' . Text::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_SEASONS");
				}
				else
				{
					echo Text::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_SEASONS");
				}
					?>
					<span class="badge"><?php echo $data['seasonscount'] ?></span>
				</li>
			<?php } ?>

			<?php if ($params->get('show_playgrounds', 1) == 1)
			{
	?>

				<li class="list-group-item"><?php
				if ($params->get('show_icon', 1) == 1)
				{
					echo '<img alt="' . Text::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_PLAYGROUNDS") . '" src="administrator/components/com_sportsmanagement/assets/images/playground.png"/>';
					echo ' ' . Text::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_PLAYGROUNDS");
				}
				else
				{
					echo Text::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_PLAYGROUNDS");
				}
					?>
					<span class="badge"><?php echo $data['playgroundscount'] ?></span>
				</li>
			<?php } ?>


			<?php if ($params->get('show_clubs', 1) == 1)
			{
	?>

				<li class="list-group-item"><?php
				if ($params->get('show_icon', 1) == 1)
				{
					echo '<img alt="' . Text::_($params->get('text_clubs')) . '" src="administrator/components/com_sportsmanagement/assets/images/clubs.png"/>';
					echo ' ' . Text::_($params->get('text_clubs'));
				}
				else
				{
					echo Text::_($params->get('text_clubs'));
				}
					?>
					<span class="badge"><?php echo $data['clubscount'] ?></span>
				</li>
			<?php } ?>


			<?php if ($params->get('show_teams', 1) == 1)
			{
	?>

				<li class="list-group-item"><?php
				if ($params->get('show_icon', 1) == 1)
				{
					echo '<img alt="' . Text::_($params->get('text_teams')) . '" src="administrator/components/com_sportsmanagement/assets/images/teams.png"/>';
					echo ' ' . Text::_($params->get('text_teams'));
				}
				else
				{
					echo Text::_($params->get('text_teams'));
				}
					?>
					<span class="badge"><?php echo $data['projectteamscount'] ?></span>
				</li>
			<?php } ?>
			<?php
			if ($params->get('show_players', 1) == 1)
			{
	?>

				<li class="list-group-item"><?php
				if ($params->get('show_icon', 1) == 1)
				{
					echo '<img alt="' . Text::_($params->get('text_players')) . '" src="administrator/components/com_sportsmanagement/assets/images/players.png"/>';
					echo ' ' . Text::_($params->get('text_players'));
				}
				else
				{
					echo Text::_($params->get('text_players'));
				}
					?>
					<span class="badge"><?php echo $data['personscount'] ?></span>
							</li>
			<?php } ?>
			<?php
			if ($params->get('show_divisions', 1) == 1)
			{
	?>

				<li class="list-group-item"><?php
				if ($params->get('show_icon', 1) == 1)
				{
					echo '<img alt="' . Text::_($params->get('text_divisions')) . '" src="administrator/components/com_sportsmanagement/assets/images/division.png"/>';
					echo ' ' . Text::_($params->get('text_divisions'));
				}
				else
				{
					echo Text::_($params->get('text_divisions'));
				}
					?>
					<span class="badge"><?php echo $data['projectdivisionscount'] ?></span>
							</li>
			<?php } ?>
			<?php
			if ($params->get('show_rounds', 1) == 1)
			{
	?>

				<li class="list-group-item"><?php
				if ($params->get('show_icon', 1) == 1)
				{
					echo '<img alt="' . Text::_($params->get('text_rounds')) . '" src="administrator/components/com_sportsmanagement/assets/images/icon-16-Matchdays.png"/>';
					echo ' ' . Text::_($params->get('text_rounds'));
				}
				else
				{
					echo Text::_($params->get('text_rounds'));
				}
					?>
					<span class="badge"><?php echo $data['projectroundscount'] ?></span>
							</li>
			<?php } ?>
			<?php
			if ($params->get('show_matches', 1) == 1)
			{
	?>

				<li class="list-group-item"><?php
				if ($params->get('show_icon', 1) == 1)
				{
					echo '<img alt="' . Text::_($params->get('text_matches')) . '" src="administrator/components/com_sportsmanagement/assets/images/matches.png"/>';
					echo ' ' . Text::_($params->get('text_matches'));
				}
				else
				{
					echo Text::_($params->get('text_matches'));
				}
					?>
					<span class="badge"><?php echo $data['projectmatchescount'] ?></span>
							</li>
			<?php } ?>
			<?php
			if ($params->get('show_player_events', 1) == 1)
			{
	?>

				<li class="list-group-item"><?php
				if ($params->get('show_icon', 1) == 1)
				{
					echo '<img alt="' . Text::_($params->get('text_player_events')) . '" src="administrator/components/com_sportsmanagement/assets/images/events.png"/>';
					echo ' ' . Text::_($params->get('text_player_events'));
				}
				else
		{
					echo Text::_($params->get('text_player_events'));
				}
					?>
					<span class="badge"><?php echo $data['projectmatcheseventscount'] ?></span>
							</li>
							<?PHP
							if (isset($data['projectmatcheseventsnamecount']))
		{
								foreach ($data['projectmatcheseventsnamecount'] as $row)
			{
									?>

									<li class="list-group-item"><?php
									if ($params->get('show_icon', 1) == 1)
				{
										echo '<img alt="' . Text::_($row->name) . '" src="' . $row->icon . '"/>';
										echo ' ' . Text::_($row->name);
									}
									else
				{
													echo Text::_($row->name);
									}
							?>
							<span class="badge"><?php echo $row->total ?></span>
									</li>
									<?PHP
								}
							}
							?>

			<?php } ?>
			<?php
			if ($params->get('show_player_stats', 1) == 1)
	{
	?>

				<li class="list-group-item"><?php
				if ($params->get('show_icon', 1) == 1)
		{
					echo '<img alt="' . Text::_($params->get('text_player_stats')) . '" src="administrator/components/com_sportsmanagement/assets/images/icon-48-statistics.png"/>';
					echo ' ' . Text::_($params->get('text_player_stats'));
				}
				else
		{
					echo Text::_($params->get('text_player_stats'));
				}
					?>
					<span class="badge"><?php echo $data['projectmatchesstatscount'] ?></span>
							</li>
			<?php } ?>
		</ul>
	</div>
	<?php
}
