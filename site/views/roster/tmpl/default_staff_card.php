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

defined('_JEXEC') or die('Restricted access');

//echo 'getTeamPlayers stafflist<br><pre>'.print_r($this->stafflist,true).'</pre><br>';

?>
<div class="mini-team clear">
	<table class="table">
		<tr>
			<td class="contentheading">
				<?php
				echo '&nbsp;';
				if ( $this->config['show_team_shortform'] == 1 )
				{
					echo JText::sprintf( 'COM_SPORTSMANAGEMENT_ROSTER_STAFF_OF2', $this->team->name, $this->team->short_name );
				}
				else
				{
					echo JText::sprintf( 'COM_SPORTSMANAGEMENT_ROSTER_STAFF_OF', $this->team->name );
				}
				?>
			</td>
		</tr>
	</table>
<?php
			$k = 0;
			for ( $i = 0, $n = count( $this->stafflist ); $i < $n; $i++ )
			{
				$row =& $this->stafflist[$i];
				?>
				<tr class="<?php echo ($k == 0)? '' : 'sectiontableentry2'; ?>"></td><div class="mini-team-toggler">
			<div class="short-team clear">
				<table class="table">
				  <tbody><tr>
				    <td style="padding: 5px 10px; color: rgb(173, 173, 173); font-weight: bold; width: 200px; text-transform: uppercase;">
				      <?php
				echo $row->position;
				?>
				    </td>
				    <td>
					  <div class="player-name">
					  <?php 
					  	$playerName = sportsmanagementHelper::formatName(null ,$row->firstname, $row->nickname, $row->lastname, $this->config["name_format"]);
						if ($this->config['link_player']==1)
						{
							$link=sportsmanagementHelperRoute::getPlayerRoute($this->project->slug,$this->team->slug,$row->slug);
							echo JHtml::link($link,'<i>'.$playerName.'</i>');
						}
						else
						{
							echo '<i>'.$playerName.'</i>';
						}
						?>
					</td>
				  </tr>
				</tbody></table>
			</div>
			<div onclick="window.location.href=''" style="cursor: pointer;" class="quick-team clear">
				<table class="table" >
				  <tbody><tr>
				    <td style="padding: 5px 10px; color: rgb(173, 173, 173); font-weight: bold; width: 200px; text-transform: uppercase;">
				      <?php
				echo $row->position;
				?>
				    </td>
				    <td style="width: 55px;">
					  
						  <?php
		
		$imgTitle = JText::sprintf( $playerName );
		$picture = $row->picture;
		if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player") ))
		{
		$picture = $row->ppic;
		}
		if ( !file_exists( $picture ) )
		{
			$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
		}
		//echo JHtml::image( $picture, $imgTitle, array( ' title' => $imgTitle ) );
?>                                    
<a href="#" title="<?php echo $playerName;?>" data-toggle="modal" data-target=".rosterstaff<?php echo $row->person_id;?>">
<img src="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" alt="<?php echo $playerName;?>" width="<?php echo $this->config['staff_picture_width'];?>" />
</a>

<div id="" style="display: none;" class="modal fade rosterstaff<?php echo $row->person_id;?>" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
<!--  <div class="modal-dialog"> -->
    <div class="modal-content">
    
    <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
          <h4 class="modal-title" id="myLargeModalLabel"><?php echo $playerName;?></h4>
        </div>
        
        <div class="modal-body">
            <img src="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" class="img-responsive img-rounded center-block">
        </div>
        <div class="modal-footer">
<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE');?> </button>
</div>
    </div>
<!--  </div> -->
  </div>
</div> 


<?PHP        
		?>
					  
					</td>
				    <td style="padding-left: 10px;">
				      <div class="player-position"><?php
				echo  $row->position;
				?></div>
					  <div class="player-name"><?php $projectid = $this->project->id;
					  $routeparameter = array();
       $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database',0);
       $routeparameter['s'] = JRequest::getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['tid'] = $this->team->slug;
       $routeparameter['pid'] = $row->slug;
							$link = sportsmanagementHelperRoute::getSportsmanagementRoute('staff',$routeparameter);
							echo JHtml::link($link,'<i>'.$playerName.'</i>'); ?></div>	  	  
					</td>
				  </tr>
				</tbody></table>
			</div>
		</div></td></tr><?php
				$k = 1 - $k;
			}
			?>
		</div>