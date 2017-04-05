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

foreach ( $this->rows as $position_id => $players ): ?>
<div style="margin:auto; width:100%;">
	<!-- position header -->
	<?php 
		$row = current($players);
		$position	= $row->position;
		$k			= 0;
		$colspan	= ( ( $this->config['show_birthday'] > 0 ) ? '6' : '5' );	?>
<h2><?php	echo '&nbsp;' . JText::_( $row->position );	?></h2>
<?php 

//echo 'getTeamPlayers players<br><pre>'.print_r($players,true).'</pre><br>';

foreach ($players as $row): ?>
<tr	class="">
<div class="mini-player_links">
			<table class="table">
				<tbody>
				<tr>
			    <td style="width:2%;padding: 0px;">
					<div class="player-trikot">
					<?php
					if ( ! empty( $row->position_number ) )
					{
						echo $row->position_number;
					} 
					?>
					</div>
				</td>
				<td  style="width: 55px;padding: 0px;">
				<?php
				$playerName = sportsmanagementHelper::formatName(null ,$row->firstname, $row->nickname, $row->lastname, $this->config["name_format"]);
				$imgTitle = JText::sprintf( $playerName );
				$picture = $row->picture;
				if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player") ))
				{
					$picture = $row->ppic;
				}
				if ( !curl_init( $picture ) )
				{
					$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
				}

echo sportsmanagementHelperHtml::getBootstrapModalImage('rosterplayer'.$row->person_id,$picture,$playerName,$this->config['player_picture_width']);

				?>
				</td>
		<td style="padding-left: 9px;">
		<div class="player-position"><?php	echo JText::_( $row->position );	?></div>
		<div class="player-name">
		<?php
if ($this->config['link_player']==1)
{
$routeparameter = array();
$routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database',0);
$routeparameter['s'] = JRequest::getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['tid'] = $this->team->slug;
$routeparameter['pid'] = $row->person_slug;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter);

echo JHtml::link($link,'<i>'.$playerName.'</i>');
}
else
{
	echo '<i>'.$playerName.'</i>';
}
		?></div>

			</td>
			</tr>
			</tbody></table>
			</div>
			</tr>
	
			<?php	$k = 1 - $k; ?>
	<?php endforeach; ?>
<div class="clear">
</div>
</div>
	<?php endforeach;	?>
