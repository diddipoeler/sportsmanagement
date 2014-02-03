<?php
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/
 
defined( '_JEXEC' ) or die( 'Restricted access' );

if (	( isset($this->teamPlayer->injury) && $this->teamPlayer->injury > 0 ) ||
		( isset($this->teamPlayer->suspension) && $this->teamPlayer->suspension > 0 ) ||
		( isset($this->teamPlayer->away) && $this->teamPlayer->away > 0 ) )
{
	?>
	<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_STATUS');	?></h2>
	
	<table class="status">
		<?php
		if ($this->teamPlayer->injury > 0)
		{
			$injury_date = "";
			$injury_end  = "";

			$injury_date = JHtml::date($this->teamPlayer->injury_date, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->rinjury_from))
			$injury_date .= " - ".$this->teamPlayer->rinjury_from;

			//injury end
			$injury_end = JHtml::date($this->teamPlayer->injury_end, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->rinjury_to))
			$injury_end .= " - ".$this->teamPlayer->rinjury_to;

			if ($this->teamPlayer->injury_date == $this->teamPlayer->injury_end)
			{
				?>
				<tr>
					<td class="label">
							<?php
							$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PERSON_INJURED' );
							echo "&nbsp;&nbsp;" . JHtml::image(	'media/com_sportsmanagement/events/'.$this->project->fs_sport_type_name.'/injured.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_INJURED' );
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
							$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PERSON_INJURED' );
							echo "&nbsp;&nbsp;" . JHtml::image(	'media/com_sportsmanagement/events/'.$this->project->fs_sport_type_name.'/injured.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							?>
					</td>
				</tr>
				<tr>
					<td class="label">
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_INJURY_DATE' );
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
							echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_INJURY_END' );
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
						echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_INJURY_TYPE' );
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
			$suspension_date = JHtml::date($this->teamPlayer->suspension_date, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->rsusp_from))
			$suspension_date .= " - ".$this->teamPlayer->rsusp_from;
			
			$suspension_end = JHtml::date($this->teamPlayer->suspension_end	, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->rsusp_to))
			$suspension_end .= " - ".$this->teamPlayer->rsusp_to;
			

			if ($this->teamPlayer->suspension_date == $this->teamPlayer->suspension_end)
			{
				?>
				<tr>
					<td class="label">
							<?php
							$imageTitle = JText::_( 'Suspended' );
							echo "&nbsp;&nbsp;" . JHtml::image(	'media/com_sportsmanagement/events/'.$this->project->fs_sport_type_name.'/suspension.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_SUSPENDED' );
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
							echo "&nbsp;&nbsp;" . JHtml::image(	'media/com_sportsmanagement/events/'.$this->project->fs_sport_type_name.'/suspension.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							?>
					</td>
				</tr>
				<tr>
					<td class="label">
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_SUSPENSION_DATE' );
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
							echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_SUSPENSION_END' );
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
						echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_SUSPENSION_REASON' );
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
			$away_date = JHtml::date($this->teamPlayer->away_date, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->raway_from))
			$away_date .= " - ".$this->teamPlayer->raway_from;

			$away_end = JHtml::date($this->teamPlayer->away_end, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE'));
			if(isset($this->teamPlayer->raway_to))
			$away_end .= " - ".$this->teamPlayer->raway_to;

			if ($this->teamPlayer->away_date == $this->teamPlayer->away_end)
			{
				?>
				<tr>
					<td class="label">
							<?php
							$imageTitle = JText::_( 'Away' );
							echo "&nbsp;&nbsp;" . JHtml::image(	'media/com_sportsmanagement/events/'.$this->project->fs_sport_type_name.'/away.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_AWAY' );
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
							echo "&nbsp;&nbsp;" . JHtml::image(	'media/com_sportsmanagement/events/'.$this->project->fs_sport_type_name.'/away.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							?>
					</td>
				</tr>
				<tr>
					<td class="label">
						<b>
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_AWAY_DATE' );
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
							echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_AWAY_END' );
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
						echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_AWAY_REASON' );
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