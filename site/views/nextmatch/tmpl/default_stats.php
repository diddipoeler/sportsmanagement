<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_stats.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage nextmatch
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 

//echo 'homeranked <br><pre>'.print_r($this->homeranked,true).'</pre>';

?>

<h4><?php echo JText::_('COM_SPORTSMANAGEMENT_NEXTMATCH_H2H'); ?></h4>
<table class="table">
	<thead>
        <tr class="" align="center">
        <th class="h2h" width="33%">
            <?php
            if ( !is_null ( $this->teams ) )
            {
                echo $this->teams[0]->name;
            }
            else
            {
                echo JText::_( "COM_SPORTSMANAGEMENT_NEXTMATCH_UNKNOWNTEAM" );
            }
            ?>
        </th>
			
			<th  class="h2h" width="33%">
                <?php
                echo JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_STATS' );
                ?>
            </th>
        <th class="h2h">
            <?php
            if ( !is_null ( $this->teams ) )
            {
                echo $this->teams[1]->name;
            }
            else
            {
                echo JText::_( "COM_SPORTSMANAGEMENT_NEXTMATCH_UNKNOWNTEAM" );
            }
            ?>
        </th>
		
        </tr>
	</thead>
    <?php
    if ($this->config['show_chances'] == 1) 
	{
	?>
        <tr class="sectiontableentry1">
            <td class="valueleft">
                <?php
                echo $this->chances[0]."%";
                ?>
            </td>
            <td class="statlabel">
                <?php
                echo JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_CHANCES' );
                ?>
            </td>
            <td class="valueright">
                <?php
                echo $this->chances[1]."%";
                ?>
            </td>
        </tr>
    <?php
	}
	
    if ($this->config['show_current_rank'] == 1) 
	{
    ?>		
        <tr class="sectiontableentry2">
            <td class="valueleft">
                <?php
                echo $this->homeranked->rank;
                ?>
            </td>
            <td class="statlabel">
                <?php
                echo JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_CURRENT_RANK' );
                ?>
            </td>
            <td class="valueright">
                <?php
                echo $this->awayranked->rank;
                ?>
            </td>
        </tr>
    <?php
    }

    if ( $this->config['show_match_count'] == 1 )
    {
    ?>
        <tr class="sectiontableentry1">
            <td class="valueleft">
                <?php
                echo $this->homeranked->cnt_matches;
                ?>
            </td>
            <td class="statlabel">
                <?php
                echo JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_COUNT_MATCHES' );
                ?>
            </td>
            <td class="valueright">
                <?php
                echo $this->awayranked->cnt_matches;
                ?>
            </td>
        </tr>
    <?php
    }

    if ( $this->config['show_match_total'] == 1 )
    {
        ?>
        <tr class="sectiontableentry2">
            <td class="valueleft">
                <?php
                printf( "%s/%s/%s", $this->homeranked->cnt_won, $this->homeranked->cnt_draw, $this->homeranked->cnt_lost);
                ?>
            </td>
            <td class="statlabel">
                   <?php
                echo JText::_('COM_SPORTSMANAGEMENT_NEXTMATCH_TOTAL');
                ?>
            </td>
            <td class="valueright">
                <?php
                printf( "%s/%s/%s", $this->awayranked->cnt_won, $this->awayranked->cnt_draw, $this->awayranked->cnt_lost);
                ?>
            </td>
        </tr>
    <?php
    }

if ( $this->config['show_match_total_home'] == 1 )
{
?>
    <tr class="sectiontableentry1">
        <td class="valueleft">
        <?php printf(
                "%s/%s/%s",
                $this->homeranked->cnt_won_home,
                $this->homeranked->cnt_draw_home,
                $this->homeranked->cnt_lost_home);?>
        </td>
        <td class="statlabel">
			<?php echo JText::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HOME');?>
		</td>
        <td class="valueright">
        <?php printf(
                "%s/%s/%s",
                $this->awayranked->cnt_won_home,
                $this->awayranked->cnt_draw_home,
                $this->awayranked->cnt_lost_home);?>
        </td>
    </tr>
<?php
}
?>
<?php
if ( $this->config['show_match_total_away'] == 1 )
{
?>
    <tr class="sectiontableentry2">
        <td class="valueleft">
        <?php printf(
                "%s/%s/%s",
                $this->homeranked->cnt_won-$this->homeranked->cnt_won_home,
                $this->homeranked->cnt_draw-$this->homeranked->cnt_draw_home,
                $this->homeranked->cnt_lost-$this->homeranked->cnt_lost_home);?>
        </td>
        <td class="statlabel">
			<?php echo JText::_('COM_SPORTSMANAGEMENT_NEXTMATCH_AWAY');?>
		</td>
        <td class="valueright">
        <?php printf(
                "%s/%s/%s",
                $this->awayranked->cnt_won-$this->awayranked->cnt_won_home,
                $this->awayranked->cnt_draw-$this->awayranked->cnt_draw_home,
                $this->awayranked->cnt_lost-$this->awayranked->cnt_lost_home);?>
        </td>
    </tr>
<?php
}
?>
<?php
if ( $this->config['show_match_points'] == 1 )
{
?>
    <tr class="sectiontableentry1">
        <td class="valueleft">
        <?php 
        echo $this->homeranked->sum_points;
        //echo JSMRankingTeam->getPoints();
        ?>
        </td>
        <td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_NEXTMATCH_POINTS');?></td>
        <td class="valueright"><?php echo $this->awayranked->sum_points;?></td>
    </tr>
<?php
}
?>
<?php
if ( $this->config['show_match_goals'] == 1 )
{
?>
    <tr class="sectiontableentry2">
        <td class="valueleft">
        <?php printf(
                "%s : %s",
                $this->homeranked->sum_team1_result,
                $this->homeranked->sum_team2_result);?>
        </td>
        <td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_NEXTMATCH_GOALS');?></td>
        <td class="valueright">
        <?php printf(
                "%s : %s",
                $this->awayranked->sum_team1_result,
                $this->awayranked->sum_team2_result);?>
        </td>
    </tr>
<?php
}
?>
<?php
if ( $this->config['show_match_diff'] == 1 )
{
?>
    <tr class="sectiontableentry1">
        <td class="valueleft">
        <?php echo $this->homeranked->diff_team_results;?>
        </td>
        <td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_NEXTMATCH_DIFFERENCE');?></td>
        <td class="valueright">
        <?php echo $this->awayranked->diff_team_results;?>
        </td>
    </tr>
<?php
}

$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);

if ( $this->config['show_match_highest_stats'] == 1 ): ?>
	
	<?php if ($this->config['show_match_highest_won'] == 1): ?>
		<tr class="sectiontableentry2">
      <td class="valueleft">
	    	<?php if ($stat = $this->home_highest_home_win): ?>
	        	<?php 
                $routeparameter['p'] = $this->home_highest_home_win->project_slug;
                $routeparameter['mid'] = $this->home_highest_home_win->match_slug;
                echo JHtml::link(sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter),sprintf("%s - %s %s:%s", $stat->hometeam, $stat->awayteam, $stat->homegoals, $stat->awaygoals) ); ?>
	      <?php else: ?>
	      	----
	      <?php endif; ?>
      </td>
      <td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HIGHEST_WON_HOME');?></td>
       <td class="valueright">
	    	<?php if ($stat = $this->away_highest_away_win): ?>
	        	<?php  
                $routeparameter['p'] = $this->away_highest_away_win->project_slug;
                $routeparameter['mid'] = $this->away_highest_away_win->match_slug;
                echo JHtml::link(sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter),sprintf("%s - %s %s:%s", $stat->hometeam, $stat->awayteam, $stat->homegoals, $stat->awaygoals) ); ?>
	      <?php else: ?>
	      	----
	      <?php endif; ?>
      </td>

    </tr>
	<?php endif; ?>

	<?php if ( $this->config['show_match_highest_loss'] == 1 ): ?>
    <tr class="sectiontableentry1">
      <td class="valueleft">
	    	<?php if ($stat = $this->home_highest_home_def): ?>
	        	<?php 
                $routeparameter['p'] = $this->home_highest_home_def->project_slug;
                $routeparameter['mid'] = $this->home_highest_home_def->match_slug;
                echo JHtml::link(sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter),sprintf("%s - %s %s:%s", $stat->hometeam, $stat->awayteam, $stat->homegoals, $stat->awaygoals) ); ?>
	      <?php else: ?>
	      	----
	      <?php endif; ?>
      </td>
      <td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HIGHEST_LOSS_HOME');?></td>
      <td class="valueright">
	    	<?php if ($stat = $this->away_highest_away_def): ?>
	        	<?php 
                $routeparameter['p'] = $this->away_highest_away_def->project_slug;
                $routeparameter['mid'] = $this->away_highest_away_def->match_slug;
                echo JHtml::link(sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter),sprintf("%s - %s %s:%s", $stat->hometeam, $stat->awayteam, $stat->homegoals, $stat->awaygoals) ); ?>
	      <?php else: ?>
	      	----
	      <?php endif; ?>
      </td>
 
    </tr>
	<?php endif; ?>

	<?php if ( $this->config['show_match_highest_won_away'] == 1 ): ?>
    <tr class="sectiontableentry2">
      <td class="valueleft">
	    	<?php if ($stat = $this->home_highest_away_win): ?>
	        	<?php 
                $routeparameter['p'] = $this->home_highest_away_win->project_slug;
                $routeparameter['mid'] = $this->home_highest_away_win->match_slug;
                echo JHtml::link(sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter),sprintf("%s - %s %s:%s", $stat->hometeam, $stat->awayteam, $stat->homegoals, $stat->awaygoals) ); ?>
	      <?php else: ?>
	      	----
	      <?php endif; ?>
      </td>
      <td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HIGHEST_WON_AWAY');?></td>
      <td class="valueright">
	    	<?php if ($stat = $this->away_highest_home_win): ?>
	        	<?php 
                $routeparameter['p'] = $this->away_highest_home_win->project_slug;
                $routeparameter['mid'] = $this->away_highest_home_win->match_slug;
                echo JHtml::link(sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter),sprintf("%s - %s %s:%s", $stat->hometeam, $stat->awayteam, $stat->homegoals, $stat->awaygoals) ); ?>
	      <?php else: ?>
	      	----
	      <?php endif; ?>
      </td>
    </tr>
	<?php endif; ?>

	<?php if ($this->config['show_match_highest_loss_away'] == 1 ): ?>
    <tr class="sectiontableentry1">
      <td class="valueleft">
	    	<?php if ($stat = $this->home_highest_away_def): ?>
	        	<?php 
                $routeparameter['p'] = $this->home_highest_away_def->project_slug;
                $routeparameter['mid'] = $this->home_highest_away_def->match_slug;
                echo JHtml::link(sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter),sprintf("%s - %s %s:%s", $stat->hometeam, $stat->awayteam, $stat->homegoals, $stat->awaygoals) ); ?>
	      <?php else: ?>
	      	----
	      <?php endif; ?>
      </td>
      <td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HIGHEST_LOSS_AWAY');?></td>
      <td class="valueright">
	    	<?php if ($stat = $this->away_highest_home_def): ?>
	        	<?php 
                $routeparameter['p'] = $this->away_highest_home_def->project_slug;
                $routeparameter['mid'] = $this->away_highest_home_def->match_slug;
                echo JHtml::link(sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter),sprintf("%s - %s %s:%s", $stat->hometeam, $stat->awayteam, $stat->homegoals, $stat->awaygoals) ); ?>
	      <?php else: ?>
	      	----
	      <?php endif; ?>
      </td>            
    </tr>
	<?php endif; ?>
<?php endif; ?>

</table>

<!-- Main END -->
<br>
<br>

