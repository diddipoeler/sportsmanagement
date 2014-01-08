<?php defined( '_JEXEC' ) or die( 'Restricted access' );

if (	( isset($this->teamPlayer->injury) && $this->teamPlayer->injury > 0 ) ||
		( isset($this->teamPlayer->suspension) && $this->teamPlayer->suspension > 0 ) ||
		( isset($this->teamPlayer->away) && $this->teamPlayer->away > 0 ) )
{
	?>
	<h2><?php echo JText::_('COM_JOOMLEAGUE_PERSON_STATUS');	?></h2>
	
	<table class="status">
		<?php
		if ($this->teamPlayer->injury > 0)
		{
			$injury_date = "";
			$injury_end  = "";

			$injury_date = JHTML::date($this->teamPlayer->injury_date, JText::_('COM_JOOMLEAGUE_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->rinjury_from))
			$injury_date .= " - ".$this->teamPlayer->rinjury_from;

			//injury end
			$injury_end = JHTML::date($this->teamPlayer->injury_end, JText::_('COM_JOOMLEAGUE_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->rinjury_to))
			$injury_end .= " - ".$this->teamPlayer->rinjury_to;

			if ($this->teamPlayer->injury_date == $this->teamPlayer->injury_end)
			{
				?>
				<tr>
					<td class="label">
							<?php
							$imageTitle = JText::_( 'COM_JOOMLEAGUE_PERSON_INJURED' );
							echo "&nbsp;&nbsp;" . JHTML::image(	'media/com_joomleague/events/'.$this->project->fs_sport_type_name.'/injured.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							echo JText::_( 'COM_JOOMLEAGUE_PERSON_INJURED' );
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
							$imageTitle = JText::_( 'COM_JOOMLEAGUE_PERSON_INJURED' );
							echo "&nbsp;&nbsp;" . JHTML::image(	'media/com_joomleague/events/'.$this->project->fs_sport_type_name.'/injured.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							?>
					</td>
				</tr>
				<tr>
					<td class="label">
							<?php
							echo JText::_( 'COM_JOOMLEAGUE_PERSON_INJURY_DATE' );
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
							echo JText::_( 'COM_JOOMLEAGUE_PERSON_INJURY_END' );
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
						echo JText::_( 'COM_JOOMLEAGUE_PERSON_INJURY_TYPE' );
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
			$suspension_date = JHTML::date($this->teamPlayer->suspension_date, JText::_('COM_JOOMLEAGUE_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->rsusp_from))
			$suspension_date .= " - ".$this->teamPlayer->rsusp_from;
			
			$suspension_end = JHTML::date($this->teamPlayer->suspension_end	, JText::_('COM_JOOMLEAGUE_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->rsusp_to))
			$suspension_end .= " - ".$this->teamPlayer->rsusp_to;
			

			if ($this->teamPlayer->suspension_date == $this->teamPlayer->suspension_end)
			{
				?>
				<tr>
					<td class="label">
							<?php
							$imageTitle = JText::_( 'Suspended' );
							echo "&nbsp;&nbsp;" . JHTML::image(	'media/com_joomleague/events/'.$this->project->fs_sport_type_name.'/suspension.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							echo JText::_( 'COM_JOOMLEAGUE_PERSON_SUSPENDED' );
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
							$imageTitle = JText::_( 'Suspended' );
							echo "&nbsp;&nbsp;" . JHTML::image(	'media/com_joomleague/events/'.$this->project->fs_sport_type_name.'/suspension.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							?>
					</td>
				</tr>
				<tr>
					<td class="label">
							<?php
							echo JText::_( 'COM_JOOMLEAGUE_PERSON_SUSPENSION_DATE' );
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
							echo JText::_( 'COM_JOOMLEAGUE_PERSON_SUSPENSION_END' );
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
						echo JText::_( 'COM_JOOMLEAGUE_PERSON_SUSPENSION_REASON' );
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
			$away_date = JHTML::date($this->teamPlayer->away_date, JText::_('COM_JOOMLEAGUE_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->raway_from))
			$away_date .= " - ".$this->teamPlayer->raway_from;

			$away_end = JHTML::date($this->teamPlayer->away_end, JText::_('COM_JOOMLEAGUE_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->raway_to))
			$away_end .= " - ".$this->teamPlayer->raway_to;

			if ($this->teamPlayer->away_date == $this->teamPlayer->away_end)
			{
				?>
				<tr>
					<td class="label">
							<?php
							$imageTitle = JText::_( 'Away' );
							echo "&nbsp;&nbsp;" . JHTML::image(	'media/com_joomleague/events/'.$this->project->fs_sport_type_name.'/away.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							echo JText::_( 'COM_JOOMLEAGUE_PERSON_AWAY' );
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
							$imageTitle = JText::_( 'Away' );
							echo "&nbsp;&nbsp;" . JHTML::image(	'media/com_joomleague/events/'.$this->project->fs_sport_type_name.'/away.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							?>
					</td>
				</tr>
				<tr>
					<td class="label">
						<b>
							<?php
							echo JText::_( 'COM_JOOMLEAGUE_PERSON_AWAY_DATE' );
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
							echo JText::_( 'COM_JOOMLEAGUE_PERSON_AWAY_END' );
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
						echo JText::_( 'COM_JOOMLEAGUE_PERSON_AWAY_REASON' );
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