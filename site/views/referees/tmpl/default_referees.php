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

//echo 'rows <pre>',print_r($this->rows,true),'</pre>';

// Show referees as defined
if ( !empty( $this->rows  ) )
{
	?>
	<table class="<?php echo $this->config['table_class'];?>">
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
									$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_REFEREES_GAMES' );
									echo JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/refereed.png',
														$imageTitle, array( 'title' => $imageTitle, 'height' => 20 ) );
									?>
								</td>
					<?php endif;	?>
				</tr>
				<?php
			}
			?>
			<tr class="">
				<td width="30" style="text-align:center; ">
					<?php
						echo '&nbsp;';
					?>
				</td>
				<td width="40" style="text-align:center; " class="nowrap">
					<?php
					$refereeName = sportsmanagementHelper::formatName(null, $row->firstname, $row->nickname, $row->lastname, $this->config["name_format"] );
					if ( $this->config['show_icon'] == 1)
					{
echo sportsmanagementHelperHtml::getBootstrapModalImage('referee'.$row->id,$row->picture,$refereeName,$this->config['referee_picture_width']);

					}
					?>
				</td>
				<td style="width:20%;">
					<?php
					if ( $this->config['link_name'] == 1 )
					{
					   $routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['pid'] = $row->slug;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('referee',$routeparameter);

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
					echo JSMCountries::getCountryFlag( $row->country );
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
																										JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE' ), 
																										sportsmanagementHelper::getTimezone($this->project, $this->overallconfig)) : "-";
										$birthdateStr .= "&nbsp;(" . sportsmanagementHelper::getAge( $row->birthday,$row->deathday ) . ")";
										break;

							case 2:	 // show Only Birthday
										$birthdateStr = $row->birthday != "0000-00-00" ? JHtml::date($row->birthday .' UTC',
																										JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE' ), 
																										sportsmanagementHelper::getTimezone($this->project, $this->overallconfig)) : "-";
										break;

							case 3:	 // show Only Age
										$birthdateStr = "(" . sportsmanagementHelper::getAge( $row->birthday,$row->deathday ) . ")";
										break;

							case 4:	 // show Only Year of birth
										$birthdateStr  = $row->birthday != "0000-00-00" ? JHtml::date($row->birthday .' UTC',
																										JText::_( '%Y' ), 
																										sportsmanagementHelper::getTimezone($this->project, $this->overallconfig) ) : "-";
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
