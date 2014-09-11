<?php defined( '_JEXEC' ) or die( 'Restricted access' );
// Show referees as defined
if ( !empty( $this->rows  ) )
{
	?>
	<table width="96%" border="0" cellpadding="0" cellspacing="0" style="text-align:center; ">
		<?php
		$k				= 0;
		$position		= '';
		$totalEvents	= array();

		foreach ( $this->rows as $row )
		{
			if ( $position != $row->position )
			{
				$position	= $row->position;
				$k			= 0;
				$colspan	= ( ( $this->config['show_birthday'] > 0 ) ? '5' : '4' );
				?>
				<tr class="sectiontableheader">
					<td width="60%" colspan="<?php echo $colspan; ?>">
						<?php
						echo '&nbsp;' . JText::_( $row->position );
						?>
					</td>
					<?php	if ( $this->config['show_games_count'] ): ?>
								<td style="text-align:center; ">
									<?php
									$imageTitle = JText::_( 'COM_JOOMLEAGUE_REFEREES_GAMES' );
									echo JHtml::image(	'images/com_joomleague/database/events/'.$this->project->fs_sport_type_name.'/refereed.png',
														$imageTitle, array( 'title' => $imageTitle, 'height' => 20 ) );
									?>
								</td>
					<?php endif;	?>
				</tr>
				<?php
			}
			?>
			<tr class="<?php echo ($k==0)? $this->config['style_class1'] : $this->config['style_class2']; ?>">
				<td width="30" style="text-align:center; ">
					<?php
						echo '&nbsp;';
					?>
				</td>
				<td width="40" style="text-align:center; " class="nowrap">
					<?php
					$refereeName = JoomleagueHelper::formatName(null, $row->firstname, $row->nickname, $row->lastname, $this->config["name_format"] );
					if ( $this->config['show_icon'] == 1)
					{
						echo JoomleagueHelper::getPictureThumb( $row->picture, $refereeName,
																$this->config['referee_picture_width'],
																$this->config['referee_picture_height']);
					}
					?>
				</td>
				<td style="width:20%;">
					<?php
					if ( $this->config['link_name'] == 1 )
					{
						$link = JoomleagueHelperRoute::getRefereeRoute( $this->project->slug, $row->slug );
						echo JHtml::link( $link, '<i>' . $refereeName . '</i>' );
					}
					else
					{
						echo '<i>' . $refereeName . '</i>';
					}
					?>
				</td>
				<td style="width:16px; text-align:center; " class="nowrap" >
					<?php
					echo Countries::getCountryFlag( $row->country );
					?>
				</td>
				<?php
				if ( $this->config['show_birthday'] > 0 )
				{
					?>
					<td width="10%" class="nowrap" style="text-align:left; ">
						<?php
						#$this->config['show_birthday'] = 4;
						switch ( $this->config['show_birthday'] )
						{
							case 1:	 // show Birthday and Age
										$birthdateStr  = $row->birthday != "0000-00-00" ? JHtml::date($row->birthday .' UTC', 
																										JText::_( 'COM_JOOMLEAGUE_GLOBAL_DAYDATE' ), 
																										JoomleagueHelper::getTimezone($this->project, $this->overallconfig)) : "-";
										$birthdateStr .= "&nbsp;(" . JoomleagueHelper::getAge( $row->birthday,$row->deathday ) . ")";
										break;

							case 2:	 // show Only Birthday
										$birthdateStr = $row->birthday != "0000-00-00" ? JHtml::date($row->birthday .' UTC',
																										JText::_( 'COM_JOOMLEAGUE_GLOBAL_DAYDATE' ), 
																										JoomleagueHelper::getTimezone($this->project, $this->overallconfig)) : "-";
										break;

							case 3:	 // show Only Age
										$birthdateStr = "(" . JoomleagueHelper::getAge( $row->birthday,$row->deathday ) . ")";
										break;

							case 4:	 // show Only Year of birth
										$birthdateStr  = $row->birthday != "0000-00-00" ? JHtml::date($row->birthday .' UTC',
																										JText::_( '%Y' ), 
																										JoomleagueHelper::getTimezone($this->project, $this->overallconfig) ) : "-";
										break;

							default:	$birthdateStr = "";
										break;
						}
						echo $birthdateStr;
						?>
					</td>
					<?php
				}
				?>
				<?php if ( $this->config['show_games_count'] ): ?>
					<td>
					<?php echo $row->countGames; ?>
					</td>
				<?php endif;	?>
			</tr>
			<?php
			$k = 1 - $k;
		}
		$colspan = 9;
		if ( $this->config['show_birthday'] > 0 )
		{
			$colspan++;
		}
		?>
	</table>
	<?php
}
?>
<br />
