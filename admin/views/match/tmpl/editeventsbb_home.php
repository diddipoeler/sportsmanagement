<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');

?>
		<fieldset class="adminform">
			<legend>
				<?php
				echo JText::_( $this->teams->team1 );
				?>
			</legend>
	<table class='adminlist'>
		<thead>
			<tr>
				<th style="text-align: left; width: 10px;"></th>
				<th style="text-align:left;"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EEBB_PERSON' ); ?></th>
				<?php
				foreach ( $this->events as $ev)
				{
					?>
					<th style="text-align: center;">
					<?php
					if ( JFile::exists( JPATH_SITE.DS.$ev->icon ) )
					{
						$imageTitle = JText::sprintf( '%1$s', JText::_( $ev->text ) );
						$iconFileName = $ev->icon;
						echo JHtml::_( 'image', $iconFileName, $imageTitle, 'title= "' . $imageTitle . '"' );
					} else {
						echo JText::_( $ev->text ) ;
					}
					?>
					</th>
					<?php
				}
				?>
			</tr>
		</thead>
		<tbody>
			<?php
			$model = $this->getModel();
			$tehp = 0;
			for( $i=0 , $n = count( $this->homeRoster ); $i < $n; $i++ )
			{
					$row =& $this->homeRoster[$i];
					if($row->value == 0) continue;
					?>
					<tr id="row<?php echo $i;?>">
						<td style="text-align: left;">
						<input type="hidden" name="player_id_h_<?php echo $i;?>" value="<?php echo $row->value;?>" />
						<input type="hidden" name="team_id_h_<?php echo $i;?>" value="<?php echo $row->projectteam_id;?>" />
						<input type="checkbox" id="cb_h<?php echo $i;?>" name="cid_h<?php echo $i;?>" value="cb_h" onclick="isChecked(this.checked);"/>
						</td>
						<td style="text-align: left;">
						<?php echo '('.JText::_($row->positionname).') - '.sportsmanagementHelper::formatName(null, $row->firstname, $row->nickname, $row->lastname, 14) ?>
						</td>
						<?php
						//total events home player
						$tehp = 0;
						foreach ( $this->events as $ev)
						{
							$tehp++;	
							$this->assign( 'evbb', $model->getPlayerEventsbb( $row->value, $ev->value, $this->item->id ) );	
							?>
							<td style="text-align: center;">
							<input type="hidden" name="event_type_id_h_<?php echo $i.'_'.$tehp;?>" value="<?php echo $ev->value;?>" />
							
                            <input type="hidden" name="event_id_h_<?php echo $i.'_'.$tehp;?>" value="<?php echo $this->evbb[0]->id;?>" />
							
                            <input type="text" size="1" class="inputbox" name="event_sum_h_<?php echo $i.'_'.$tehp; ?>" value="<?php echo (($this->evbb[0]->event_sum > 0) ? $this->evbb[0]->event_sum : '' ); ?>" title="<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_VALUE_SUM' )?>" onchange="document.getElementById('cb_h<?php echo $i;?>').checked=true" />

                            <input type="text" size="2" class="inputbox" name="event_time_h_<?php echo $i.'_'.$tehp; ?>" value="<?php echo (($this->evbb[0]->event_time > 0) ? $this->evbb[0]->event_time : '' ); ?>" title="<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_TIME' )?>" onchange="document.getElementById('cb_h<?php echo $i;?>').checked=true" />

                            <input type="text" size="2" class="inputbox" name="notice_h_<?php echo $i.'_'.$tehp; ?>" value="<?php echo ((strlen($this->evbb[0]->notice) > 0) ? $this->evbb[0]->notice : '' ); ?>" title="<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_MATCH_NOTICE' )?>" onchange="document.getElementById('cb_h<?php echo $i;?>').checked=true" />
                            &nbsp;&nbsp;
                            </td>

						<?php
						}
						?>
					</tr>
					<?php
			}
			?>
			<input type="hidden" name="total_h_players" value="<?php echo $i;?>" />
			<input type="hidden" name="tehp" value="<?php echo $tehp;?>" />
		</tbody>
	</table>
		</fieldset>
