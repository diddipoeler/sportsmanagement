<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_previousx.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage nextmatch
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 

//echo 'previousx <br><pre>'.print_r($this->previousx,true).'</pre>';
//echo 'allteams <br><pre>'.print_r($this->allteams,true).'</pre>';
//echo 'teams <br><pre>'.print_r($this->teams,true).'</pre>';

foreach ( $this->teams as $currentteam )
{
?>

<?php if ( isset($this->previousx[$currentteam->id])) :	?>
<!-- Start of last 5 matches -->

<h4><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_NEXTMATCH_PREVIOUS', $this->allteams[$currentteam->id]->name); ?></h4>
<table class="table">
	<tr>
		<td>
		<table class="<?php echo $this->config['hystory_table_class']; ?>">
			<?php
			$pr_id = 0;
			$k=0;
			
			foreach ( $this->previousx[$currentteam->id] as $game )
			{
                $routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $game->project_slug;
$routeparameter['r'] = $game->round_slug;
$routeparameter['division'] = 0;
$routeparameter['mode'] = 0;
$routeparameter['order'] = '';
$routeparameter['layout'] = '';
$result_link = sportsmanagementHelperRoute::getSportsmanagementRoute('results',$routeparameter);
				
				$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $game->project_slug;
$routeparameter['mid'] = $game->match_slug;
$report_link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
				
				$home = $this->allteams[$game->projectteam1_id];
				$away = $this->allteams[$game->projectteam2_id];
				?>
			<tr class="">
				<td><?php
				echo JHtml::link( $result_link, $game->roundcode );
				?></td>
				<td nowrap="nowrap"><?php
				echo JHtml::date( $game->match_date, JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE' ) );
				?></td>
				<td><?php
				echo substr( $game->match_date, 11, 5 );
				?></td>
				<td nowrap="nowrap"><?php
				echo $home->name;
				?></td>
                
                <td class="nowrap"><?php
				echo sportsmanagementHelperHtml::getBootstrapModalImage('nextmatchprev'.$game->id.'-'.$game->projectteam1_id,$game->home_picture,$home->name,'20')
				?></td>
                
				<td nowrap="nowrap">-</td>
                
                <td class="nowrap"><?php
				echo sportsmanagementHelperHtml::getBootstrapModalImage('nextmatchprev'.$game->id.'-'.$game->projectteam2_id,$game->away_picture,$away->name,'20')
				?></td>
                
				<td nowrap="nowrap"><?php
				echo $away->name;
				?></td>
				<td nowrap="nowrap"><?php
				echo $game->team1_result;
				?></td>
				<td nowrap="nowrap"><?php echo $this->overallconfig['seperator']; ?></td>
				<td nowrap="nowrap"><?php
				echo $game->team2_result;
				?></td>
				<td nowrap="nowrap"><?php
				if ($game->show_report==1)
				{
					$desc = JHtml::image( JURI::base()."media/com_sportsmanagement/jl_images/zoom.png",
					JText::_( 'Match Report' ),
					array( "title" => JText::_( 'Match Report' ) ) );
					echo JHtml::link( $report_link, $desc);
				}
				$k = 1 - $k;
				?></td>
				<?php	if (($this->config['show_thumbs_picture'])): ?>
				<td><?php echo sportsmanagementHelperHtml::getThumbUpDownImg($game, $currentteam->id); ?></td>
				<?php endif; ?>
			</tr>
			<?php
			}
			?>
		</table>
		</td>
	</tr>
</table>
<!-- End of  show matches -->
<?php 
endif; 

}
?>