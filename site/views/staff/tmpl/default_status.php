<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

if (	( isset($this->inprojectinfo->injury) && $this->inprojectinfo->injury > 0 ) ||
		( isset($this->inprojectinfo->suspension) && $this->inprojectinfo->suspension > 0 ) ||
		( isset($this->inprojectinfo->away) && $this->inprojectinfo->away > 0 ) )
{
	?>
	<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_STATUS');	?></h2>

	<table class="status">
		<?php
		if ($this->inprojectinfo->injury > 0)
		{
			$injury_date = "";
			$injury_end  = "";

			if (is_array($this->roundsdata))
			{
				//injury start
				if (array_key_exists($this->inprojectinfo->injury_date, $this->roundsdata))
				{
					$injury_date = JHtml::date($this->roundsdata[$this->inprojectinfo->injury_date]['date_first'], 'COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE');
					$injury_date .= " - ".$this->inprojectinfo->injury_date.". ".JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME');
				}

				//injury end
				if (array_key_exists($this->inprojectinfo->injury_end, $this->roundsdata))
				{
					$injury_end = JHtml::date($this->roundsdata[$this->inprojectinfo->injury_end]['date_last'], 'COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE');
					$injury_end .= " - ".$this->inprojectinfo->injury_end.". ".JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME');
				}
			}

			if ($this->inprojectinfo->injury_date == $this->inprojectinfo->injury_end)
			{
				?>
				<tr>
					<td class="label">
							<?php
							$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PERSON_INJURED' );
							echo "&nbsp;&nbsp;" . JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/injured.gif',
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
							echo "&nbsp;&nbsp;" . JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/injured.gif',
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
					printf( "%s", htmlspecialchars( $this->inprojectinfo->injury_detail ) );
					?>
				</td>
			</tr>
			<?php
		}

		if ($this->inprojectinfo->suspension > 0)
		{
			$suspension_date = "";
			$suspension_end  = "";

			if (is_array($this->roundsdata))
			{
				//suspension start
				if (array_key_exists($this->inprojectinfo->suspension_date, $this->roundsdata))
				{
					$suspension_date = JHtml::date($this->roundsdata[$this->inprojectinfo->suspension_date]['date_first'], 'COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE');
					$suspension_date .= " - ".$this->inprojectinfo->suspension_date.". ".JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME');
				}

				//suspension end
				if (array_key_exists($this->inprojectinfo->suspension_end, $this->roundsdata))
				{
					$suspension_end = JHtml::date($this->roundsdata[$this->inprojectinfo->suspension_end]['date_last'], 'COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE');
					$suspension_end .= " - ".$this->inprojectinfo->suspension_end.". ".JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME');
				}
			}

			if ($this->inprojectinfo->suspension_date == $this->inprojectinfo->suspension_end)
			{
				?>
				<tr>
					<td class="label">
						
							<?php
							$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PERSON_SUSPENDED' );
							echo "&nbsp;&nbsp;" . JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/suspension.gif',
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
							$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PERSON_SUSPENDED' );
							echo "&nbsp;&nbsp;" . JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/suspension.gif',
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
					
						<?php
						echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_SUSPENSION_REASON' );
						?>
					
				</td>
				<td class="data">
					<?php
					printf( "%s", htmlspecialchars( $this->inprojectinfo->suspension_detail ) );
					?>
				</td>
			</tr>
			<?php
		}

		if ($this->inprojectinfo->away > 0)
		{
			$away_date = "";
			$away_end  = "";

			if (is_array($this->roundsdata))
			{
				//suspension start
				if (array_key_exists($this->inprojectinfo->away_date, $this->roundsdata))
				{
					$away_date = JHtml::date($this->roundsdata[$this->inprojectinfo->away_date]['date_first'], 'COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE');
					$away_date .= " - ".$this->inprojectinfo->away_date.". ".JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME');
				}

				//suspension end
				if (array_key_exists($this->inprojectinfo->away_end, $this->roundsdata))
				{
					$away_end = JHtml::date($this->roundsdata[$this->inprojectinfo->away_end]['date_last'], 'COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE');
					$away_end .= " - ".$this->inprojectinfo->away_end.". ".JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME');
				}
			}

			if ($this->inprojectinfo->away_date == $this->inprojectinfo->away_end)
			{
				?>
				<tr>
					<td class="label">
						
							<?php
							$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PERSON_AWAY' );
							echo "&nbsp;&nbsp;" . JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/away.gif',
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
							$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PERSON_AWAY' );
							echo "&nbsp;&nbsp;" . JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/away.gif',
																$imageTitle,
																array( 'title' => $imageTitle ) );
							?>
						
					</td>
				</tr>
				<tr>
					<td class="label">
						
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_AWAY_DATE' );
							?>
						
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
					printf( "%s", htmlspecialchars( $this->inprojectinfo->away_detail ) );
					?>
				</td>
			</tr>
			<?php
		}
		?>
	</table>
	<br/>
	<?php
}
?>