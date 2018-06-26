<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_stats.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage statsranking
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

//echo 'stats <pre>',print_r($this->stats,true),'</pre>';

?>

<?php
$colspan	= 4;
$show_icons	= 0;
if ($this->config['show_picture_thumb'] == 1) $colspan++;
if ($this->config['show_nation'] == 1) $colspan++;
if ($this->config['show_icons'] == 1) $show_icons = 1;
?>

<?php foreach ( $this->stats AS $rows ): ?>
<?php if ($this->multiple_stats == 1) :?>
<h2><?php echo $rows->name; ?></h2>
<?php endif; ?>
<table class="<?php	echo $this->config['table_class'];	?>">
	<thead>
	<tr class="sectiontableheader">
		<th class="td_r rank"><?php	echo JText::_( 'COM_SPORTSMANAGEMENT_STATSRANKING_RANK' );	?></th>

		<?php if ($this->config['show_picture_thumb'] == 1 ):	?>
		<th class="td_c">&nbsp;</th>
		<?php endif; ?>

		<th class="td_l"><?php	echo JText::_( 'COM_SPORTSMANAGEMENT_STATSRANKING_PLAYER_NAME' ); ?>
		</th>

		<?php	if ( $this->config['show_nation'] == 1 ):	?>
		<th class="td_c">&nbsp;</th>
		<?php endif; ?>
		
		<?php	if ( $this->config['show_team'] == 1 ):	?>
		<th class="td_l"><?php	echo JText::_( 'COM_SPORTSMANAGEMENT_STATSRANKING_TEAM' );	?></th>
		<?php endif; ?>
		<?php	if ( $show_icons == 1 ):	?>
		<th class="td_r" class="nowrap"><?php	echo $rows->getImage(); ?></th>
		<?php else: ?>	
		<th class="td_r" class="nowrap"><?php	echo JText::_($rows->name); ?></th>
		<?php endif; ?>		
	</tr>
	</thead>
	<tbody>
	<?php
	if ( count( $this->playersstats[$rows->id]->ranking ) > 0 )
	{
		$k = 0;
		$lastrank = 0;
		foreach((array) $this->playersstats[$rows->id]->ranking as $row )
		{
			
            //echo 'row <pre>',print_r($row,true),'</pre>';
            
            if ( $lastrank == $row->rank )
			{
				$rank = '-';
			}
			else
			{
				$rank = $row->rank;
			}
			$lastrank  = $row->rank;
			

			$favStyle = '';
			$isFavTeam = in_array($row->team_id,$this->favteams);
			if ( $this->config['highlight_fav'] == 1 && $isFavTeam && $this->project->fav_team_highlight_type == 1 )
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
		<td class="td_r rank"><?php	echo $rank;	?></td>
		<?php	$playerName = sportsmanagementHelper::formatName(null, $row->firstname, $row->nickname, $row->lastname, $this->config["name_format"]);?>
		<?php	if ( $this->config['show_picture_thumb'] == 1 ): ?>
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
//		echo sportsmanagementHelper::getPictureThumb($picture, $playerName,
//												$this->config['player_picture_width'],
//												$this->config['player_picture_height']);
		?>

       
<a href="<?php echo $picture;?>"  title="<?php echo $playerName;?>" data-toggle="modal" data-target="#r<?php echo $row->person_id;?>">
<img src="<?php echo $picture;?>" alt="<?php echo $playerName;?>" width="<?php echo $this->config['player_picture_width'];?>" />
</a>
<div class="modal fade" id="r<?php echo $row->person_id;?>" tabindex="-1" role="dialog" aria-labelledby="modal" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
</div>
<?PHP
echo JHtml::image($picture, $playerName, array('title' => $playerName,'class' => "img-rounded" ));      
?>
</div>        
        
		</td>
		<?php endif; ?>

		<td class="td_l playername">
		<?php
		if ( $this->config['link_to_player'] == 1 ) {
			$link = sportsmanagementHelperRoute::getPlayerRoute( $this->project->id, $row->team_id, $row->person_id );
			echo JHtml::link( $link, $playerName );
		}
		else {
			echo $playerName;
		}
		?>
		</td>

		<?php	if ( $this->config['show_nation'] == 1 ): ?>
		<td class="td_c playercountry"><?php echo JSMCountries::getCountryFlag($row->country); ?></td>
		<?php endif;	?>

		<?php	if ( $this->config['show_team'] == 1 ):	?>
		<td class="td_l playerteam">
			<?php
			$team = $this->teams[$row->team_id];
			if ( ( $this->config['link_to_team'] == 1 ) && ( $this->project->id > 0 ) && ( $row->team_id > 0 ) )
			{
				$link = sportsmanagementHelperRoute::getTeamInfoRoute( $this->project->id, $row->team_id  );
			} else {
				$link = null;
			}
			$teamName = sportsmanagementHelper::formatTeamName($team,"t".$row->team_id,$this->config, $isFavTeam, $link);
			echo $teamName;
			?>
		</td>
		<?php endif; ?>

		<td class="td_r playertotal"><?php echo $row->total; ?></td>
	</tr>
	<?php
	$k=(1-$k);
		}
	}
	?>
	</tbody>
</table>

<?php 
if ($this->multiple_stats == 1)
{
?>
<div class="fulltablelink">
<?php 
$routeparameter = array();
$routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database',JComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database',0));
$routeparameter['s'] = JRequest::getInt('s',0);
$routeparameter['p'] = $this->project->id;
$routeparameter['division'] = $this->division->id;
$routeparameter['tid'] = $this->teamid;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('statsranking',$routeparameter);	 
echo JHtml::link($link, JText::_('COM_SPORTSMANAGEMENT_STATSRANKING_VIEW_FULL_TABLE')); 
?>
</div>
<?php
}
else
{
	jimport('joomla.html.pagination');
	$pagination = new JPagination( $this->playersstats[$rows->id]->pagination_total, $this->limitstart, $this->limit );
?>
<div class="pageslinks">
	<?php echo $pagination->getPagesLinks(); ?>
</div>

<p class="pagescounter">
	<?php echo $pagination->getPagesCounter(); ?>
</p>
<?php
}
?>

<?php endforeach; ?>
