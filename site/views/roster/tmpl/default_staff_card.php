<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_staff_card.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage roster
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
						  $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['tid'] = $this->team->slug;
       $routeparameter['pid'] = $row->person_slug;
       
					$link = sportsmanagementHelperRoute::getSportsmanagementRoute('staff',$routeparameter);
							
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
echo sportsmanagementHelperHtml::getBootstrapModalImage('rosterstaff'.$row->person_id,
$picture,
$playerName,
$this->config['staff_picture_width'],
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);
		
  
		?>
					  
					</td>
				    <td style="padding-left: 10px;">
				      <div class="player-position"><?php
				echo  $row->position;
				?></div>
					  <div class="player-name"><?php $projectid = $this->project->id;
					  $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
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
