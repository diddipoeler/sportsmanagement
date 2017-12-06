<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');
$colspan	= 4;
$show_icons	= 0;
if ($this->config['show_picture_thumb'] == 1) $colspan++;
if ($this->config['show_nation'] == 1) $colspan++;
if ($this->config['show_icons'] == 1) $show_icons = 1;
?>

<?php foreach ($this->eventtypes AS $rows): ?>
<?php if ($this->multiple_events == 1) :?>
<h2><?php echo JText::_($rows->name); ?></h2>
<?php endif; ?>
<table class="<?php echo $this->config['table_class']; ?>">
	<thead>
	<tr class="sectiontableheader">
		<th class="rank"><?php echo JText::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_RANK'); ?></th>

		<?php if ( $this->config['show_picture_thumb'] ): ?>
		<th class="td_c">&nbsp;</th>
		<?php endif; ?>

		<th class="td_l"><?php echo JText::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_PLAYER_NAME'); ?></th>

		<?php if( $this->config['show_nation'] ): ?>
		<th class="td_c">&nbsp;</th>
		<?php endif; ?>

		<th class="td_l"><?php echo JText::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_TEAM'); ?></th>


		<?php if ($show_icons == 1): ?>
		<th class="td_c" nowrap="nowrap">
			<?php
				$iconPath=$rows->icon;
				if (!strpos(' '.$iconPath,'/')){$iconPath='media/com_sportsmanagement/events/'.$iconPath;}
				echo JHtml::image($iconPath,JText::_($rows->name),array('title'=> JText::_($rows->name),'align'=> 'top','hspace'=> '2'));
			?>
			</th>
		<?php else: ?>
		<th class="td_c" nowrap="nowrap"><?php	echo JText::_($rows->name); ?></th>
		<?php endif; ?>
	</tr>
	</thead>
	<tbody>
	<?php
	if ( isset($this->eventranking[$rows->id]) && count($this->eventranking[$rows->id]) > 0)
	{
		$k = 0;
        $counter = 0;
		$lastrank = 0;
		foreach($this->eventranking[$rows->id] as $row)
		{
			if ($lastrank == $row->rank)
			{
				$rank = '-';
			}
			else
			{
				$rank = $row->rank;
			}
			$lastrank = $row->rank;

			$favStyle = '';
			$isFavTeam = in_array($row->tid,$this->favteams);
			$highlightFavTeam = $this->config['highlight_fav'] == 1 && $isFavTeam;
			if ($highlightFavTeam && $this->project->fav_team_highlight_type == 1)
			{
				$format = "%s";
				$favStyle = ' style="';
				$favStyle .= ($this->project->fav_team_text_bold != '') ? 'font-weight:bold;' : '';
				$favStyle .= (trim($this->project->fav_team_text_color) != '') ? 'color:'.trim($this->project->fav_team_text_color).';' : '';
				$favStyle .= (trim($this->project->fav_team_color) != '') ? 'background-color:' . trim($this->project->fav_team_color) . ';' : '';
				if ($favStyle != ' style="')
				{
				  $favStyle .= '"';
				}
				else {
				  $favStyle = '';
				}
			}

			?>
	<tr class=""<?php echo $favStyle; ?>>
		<td class="rank"><?php echo $rank; ?></td>
		<?php $playerName = sportsmanagementHelper::formatName(null, $row->fname, $row->nname, $row->lname, $this->config['name_format']); ?>
		<?php if ( $this->config['show_picture_thumb'] ): ?>
		<td class="td_c playerpic">
		<?php
 		$picture = isset($row->teamplayerpic) ? $row->teamplayerpic : null;
 		if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player") ))
 		{
 			$picture = $row->picture;
 		}
 		if ( !file_exists( $picture ) )
 		{
 			$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
 		}
		
 echo sportsmanagementHelperHtml::getBootstrapModalImage('evplayer' . $row->pid, COM_SPORTSMANAGEMENT_PICTURE_SERVER . $picture, $playerName, $this->config['player_picture_width'])        
        ?>

		</td>
		<?php endif; ?>

		<td class="td_l playername" width="30%">
		<?php			
			if ( $this->config['link_to_player'] )
			{
			 $routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['tid'] = $row->team_slug;
$routeparameter['pid'] = $row->person_slug;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter);
				echo JHtml::link($link, $playerName);
			}
			else
			{
				echo $playerName;
			}
		?>
		</td>

		<?php if ( $this->config['show_nation'] ): ?>
		<td class="td_c playercountry"><?php echo JSMCountries::getCountryFlag($row->country); ?></td>
		<?php endif; ?>

		<td class="td_l playerteam" width="30%">
			<?php
			$team = $this->teams[$row->tid];
			if (( $this->config['link_to_team'] ) &&
				($this->project->id > 0) && ($row->tid > 0)) {
				    $routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['tid'] = $row->team_slug;
$routeparameter['ptid'] = $row->projectteam_slug;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
			} else {
				$link = null;
			} 
			$teamName = sportsmanagementHelper::formatTeamName($team,'e'.$rows->id.'c'.$counter.'t'.$row->tid,$this->config, $highlightFavTeam, $link);
			echo $teamName;
			?>
		</td>

		<?php
		$value=($row->p > 9) ? $row->p : '&nbsp;'.$row->p;
		?>
		<td class="td_c playertotal"><?php echo $value; ?></td>
	</tr>
	<?php
		$k=(1-$k);
        $counter++;
		}
	}
	?>
	</tbody>
</table>
<?php if ($this->multiple_events == 1):?>
<div class="fulltablelink">
<?php 
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['division'] = (isset($this->division->id) ? $this->division->id : 0);
$routeparameter['tid'] = $this->teamid;
$routeparameter['evid'] = $rows->event_slug;
$routeparameter['mid'] = (isset($this->matchid) ? $this->matchid : 0);
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('eventsranking',$routeparameter);
echo JHtml::link($link, JText::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_MORE')); ?>
</div>
<?php else: ?>
<div class="pageslinks">
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>

<p class="pagescounter">
	<?php echo $this->pagination->getPagesCounter(); ?>
</p>
<?php endif;?>

<?php endforeach; ?>
