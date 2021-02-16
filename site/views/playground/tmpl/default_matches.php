<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage playground
 * @file       default_matches.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

?>

<?php

if ($this->games)
{
	?>
    <!-- Playground next games -->
<?php  
unset($this->notes);
$this->notes[] = Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_NEXT_GAMES');
echo $this->loadTemplate('jsm_notes');
?>
    <div class="<?php echo $this->divclassrow; ?> table-responsive" id="playground_matches">
        <table class="<?php echo $this->config['matches_table_class']; ?>">
			<?php
			// Sort games by dates
			$gamesByDate = Array();

			foreach ($this->games as $game)
			{
				$gamesByDate[substr($game->match_date, 0, 10)][] = $game;
			}

			$colspan = 5;

			if ($this->config['show_logo'])
			{
				$colspan = 7;
			}

			foreach ($gamesByDate as $date => $games)
			{
				?>
                <tr>
                    <td align="left" colspan="<?php echo $colspan; ?>" class="">
						<?php
						echo HTMLHelper::date($date, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE'));
						?>
                    </td>
                </tr>
				<?php
				foreach ($games as $game)
				{
					$home = $this->gamesteams[$game->team1];
					$away = $this->gamesteams[$game->team2];
					?>
                    <tr class="sectiontableentry1">
                        <td>
							<?php
							echo substr($game->match_date, 11, 5);
							?>
                        </td>
                        <td class="nowrap">
							<?php
							echo $game->project_name;
							?>
                        </td>
						<?php
						if ($this->config['show_logo'])
						{
							$home_logo = sportsmanagementModelteam::getTeamLogo($home->id, $this->config['show_logo_small']);
							$away_logo = sportsmanagementModelteam::getTeamLogo($away->id, $this->config['show_logo_small']);
							$teamA     = '<td align="right" valign="top" class="nowrap">';
							$teamA     .= " " . sportsmanagementModelProject::getClubIconHtml($home_logo[0], 1, 0, 'logo_small', Factory::getApplication()->input->getInt('cfg_which_database', 0), 0, $this->modalwidth, $this->modalheight, $this->overallconfig['use_jquery_modal']);
							$teamA     .= '</td>';
							echo $teamA;
						}
						?>
                        <td class="nowrap">
							<?php
							echo $home->name;
							?>
                        </td>
                        <td class="nowrap">-</td>
						<?php
						if ($this->config['show_logo'])
						{
							$teamB = '<td align="right" valign="top" class="nowrap">';
							$teamB .= " " . sportsmanagementModelProject::getClubIconHtml($away_logo[0], 1, 0, 'logo_small', Factory::getApplication()->input->getInt('cfg_which_database', 0), 0, $this->modalwidth, $this->modalheight, $this->overallconfig['use_jquery_modal']);
							$teamB .= '</td>';
							echo $teamB;
						}
						?>
                        <td class="nowrap">
							<?php
							echo $away->name;
							?>
                        </td>
                    </tr>
					<?php
				}
			}
			?>
        </table>
    </div>

    <!-- End of playground next games -->
	<?php
}
