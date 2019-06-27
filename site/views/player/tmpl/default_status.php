<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_status.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage player
 */
 
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>
<div class="<?php echo $this->divclassrow;?> table-responsive" id="player">
<?php
if (	( isset($this->teamPlayer->injury) && $this->teamPlayer->injury > 0 ) ||
		( isset($this->teamPlayer->suspension) && $this->teamPlayer->suspension > 0 ) ||
		( isset($this->teamPlayer->away) && $this->teamPlayer->away > 0 ) )
{
	?>
	<h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_STATUS');	?></h2>
	
	<table class="<?PHP echo $this->config['player_table_class']; ?>" >
		<?php
		if ($this->teamPlayer->injury > 0)
		{
			$injury_date = "";
			$injury_end  = "";

			$injury_date = HTMLHelper::date($this->teamPlayer->injury_date, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->rinjury_from))
			$injury_date .= " - ".$this->teamPlayer->rinjury_from;

			//injury end
			$injury_end = HTMLHelper::date($this->teamPlayer->injury_end, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->rinjury_to))
			$injury_end .= " - ".$this->teamPlayer->rinjury_to;

			if ($this->teamPlayer->injury_date == $this->teamPlayer->injury_end)
			{
				?>
				<tr>
					<td class="label">
							<?php
							$imageTitle = Text::_( 'COM_SPORTSMANAGEMENT_PERSON_INJURED' );
							echo "&nbsp;&nbsp;" . HTMLHelper::image(	'media/com_sportsmanagement/events/'.$this->project->fs_sport_type_name.'/injured.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							echo Text::_( 'COM_SPORTSMANAGEMENT_PERSON_INJURED' );
							?>
					</td>
					<td  class="data">
						<?php
						echo $injury_end;
						?>
					</td>
				</tr>
				<?php
			}
			else
			{
				?>
				<tr>
					<td class="label">
							<?php
							$imageTitle = Text::_( 'COM_SPORTSMANAGEMENT_PERSON_INJURED' );
							echo "&nbsp;&nbsp;" . HTMLHelper::image(	'media/com_sportsmanagement/events/'.$this->project->fs_sport_type_name.'/injured.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							?>
					</td>
				</tr>
				<tr>
					<td class="label">
							<?php
							echo Text::_( 'COM_SPORTSMANAGEMENT_PERSON_INJURY_DATE' );
							?>
					</td>
					<td class="data">
						<?php
						echo $injury_date;
						?>
					</td>
				</tr>
				<tr>
					<td class="label">
							<?php
							echo Text::_( 'COM_SPORTSMANAGEMENT_PERSON_INJURY_END' );
							?>
					</td>
					<td class="data">
						<?php
						echo $injury_end;
						?>
					</td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td class="label">
						<?php
						echo Text::_( 'COM_SPORTSMANAGEMENT_PERSON_INJURY_TYPE' );
						?>
				</td>
				<td class="data">
					<?php
					printf( "%s", htmlspecialchars( $this->teamPlayer->injury_detail ) );
					?>
				</td>
			</tr>
			<?php
		}

		if ($this->teamPlayer->suspension > 0)
		{
			$suspension_date = "";
			$suspension_end  = "";

			//suspension start
			$suspension_date = HTMLHelper::date($this->teamPlayer->suspension_date, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->rsusp_from))
			$suspension_date .= " - ".$this->teamPlayer->rsusp_from;
			
			$suspension_end = HTMLHelper::date($this->teamPlayer->suspension_end	, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->rsusp_to))
			$suspension_end .= " - ".$this->teamPlayer->rsusp_to;
			

			if ($this->teamPlayer->suspension_date == $this->teamPlayer->suspension_end)
			{
				?>
				<tr>
					<td class="label">
							<?php
							$imageTitle = Text::_( 'Suspended' );
							echo "&nbsp;&nbsp;" . HTMLHelper::image(	'media/com_sportsmanagement/events/'.$this->project->fs_sport_type_name.'/suspension.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							echo Text::_( 'COM_SPORTSMANAGEMENT_PERSON_SUSPENDED' );
							?>
					</td>
					<td class="data">
						<?php
						echo $suspension_end;
						?>
					</td>
				</tr>
				<?php
			}
			else
			{
				?>
				<tr>
					<td class="label">
							<?php
							$imageTitle = Text::_( 'Suspended' );
							echo "&nbsp;&nbsp;" . HTMLHelper::image(	'media/com_sportsmanagement/events/'.$this->project->fs_sport_type_name.'/suspension.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							?>
					</td>
				</tr>
				<tr>
					<td class="label">
							<?php
							echo Text::_( 'COM_SPORTSMANAGEMENT_PERSON_SUSPENSION_DATE' );
							?>
					</td>
					<td class="data">
						<?php
						echo $suspension_date;
						?>
					</td>
				</tr>
				<tr>
					<td class="label">
							<?php
							echo Text::_( 'COM_SPORTSMANAGEMENT_PERSON_SUSPENSION_END' );
							?>
					</td>
					<td class="data">
						<?php
						echo $suspension_end;
						?>
					</td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td class="label">
					<b>
						<?php
						echo Text::_( 'COM_SPORTSMANAGEMENT_PERSON_SUSPENSION_REASON' );
						?>
					</b>
				</td>
				<td class="data">
					<?php
					printf( "%s", htmlspecialchars( $this->teamPlayer->suspension_detail ) );
					?>
				</td>
			</tr>
			<?php
		}

		if ($this->teamPlayer->away > 0)
		{
			$away_date = "";
			$away_end  = "";

			//suspension start
			$away_date = HTMLHelper::date($this->teamPlayer->away_date, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->raway_from))
			$away_date .= " - ".$this->teamPlayer->raway_from;

			$away_end = HTMLHelper::date($this->teamPlayer->away_end, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->raway_to))
			$away_end .= " - ".$this->teamPlayer->raway_to;

			if ($this->teamPlayer->away_date == $this->teamPlayer->away_end)
			{
				?>
				<tr>
					<td class="label">
							<?php
							$imageTitle = Text::_( 'Away' );
							echo "&nbsp;&nbsp;" . HTMLHelper::image(	'media/com_sportsmanagement/events/'.$this->project->fs_sport_type_name.'/away.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							echo Text::_( 'COM_SPORTSMANAGEMENT_PERSON_AWAY' );
							?>
					</td>
					<td class="data">
						<?php
						echo $away_end;
						?>
					</td>
				</tr>
				<?php
			}
			else
			{
				?>
				<tr>
					<td class="label">
							<?php
							$imageTitle = Text::_( 'Away' );
							echo "&nbsp;&nbsp;" . HTMLHelper::image(	'media/com_sportsmanagement/events/'.$this->project->fs_sport_type_name.'/away.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							?>
					</td>
				</tr>
				<tr>
					<td class="label">
						<b>
							<?php
							echo Text::_( 'COM_SPORTSMANAGEMENT_PERSON_AWAY_DATE' );
							?>
						</b>
					</td>
					<td class="data">
						<?php
						echo $away_date;
						?>
					</td>
				</tr>
				<tr>
					<td class="label">
							<?php
							echo Text::_( 'COM_SPORTSMANAGEMENT_PERSON_AWAY_END' );
							?>
					</td>
					<td class="data">
						<?php
						echo $away_end;
						?>
					</td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td class="label">
						<?php
						echo Text::_( 'COM_SPORTSMANAGEMENT_PERSON_AWAY_REASON' );
						?>
				</td>
				<td class="data">
					<?php
					printf( "%s", htmlspecialchars( $this->teamPlayer->away_detail ) );
					?>
				</td>
			</tr>
			<?php
		}
		?>
	</table>

	<?php
}
?>
</div>