<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<div class="no-column">
	<div class="contentpaneopen">
		<div class="contentheading">
			<?php echo JText::_('COM_SPORTSMANAGEMENT_CLUBINFO_TEAMS'); ?>
		</div>
	</div>
	<table>
	<div class="left-column-teamlist">
	<?php
	$params=array();
	$params['width']="30";
	
		foreach ( $this->teams as $team )
		{
			if ( $team->team_name )
			{
				//$link = sportsmanagementHelperRoute::getProjectTeamInfoRoute( $team->pid, $team->ptid );
                
                // diddipoeler
                //$link = sportsmanagementHelperRoute::getTeamInfoRoute( $team->pid, $team->id );
                $link = sportsmanagementHelperRoute::getTeamInfoRoute( $team->pid, $team->team_slug );
				?>
				<tr>
				<td>
				<span class="">
					<?php
					//echo JHTML::link( $link, $team->team_name );
						//echo JHTML::image($team->trikot_home, $team->team_name, $params).JHTML::link( $link, $team->team_name );
						
//            echo JHTML::link( $link, $team->team_name.JHTML::image($team->trikot_home, $team->team_name, $params) );
						
						if ( $team->team_shortcut ) 
            { 
            //echo "(" . $team->team_shortcut . ")";
            if ( $this->config['show_teams_trikot_of_club'] )
            {
            
            if ( $this->config['show_teams_shortcut_of_club'] )
            {
            echo JHTML::link( $link, JHTML::image($team->trikot_home, $team->team_name, $params).$team->team_name." (" . $team->team_shortcut . ")" );
            }
            else
            {
            echo JHTML::link( $link, JHTML::image($team->trikot_home, $team->team_name, $params).$team->team_name );
            }
            
            }
            else
            {
            if ( $this->config['show_teams_shortcut_of_club'] )
            {
            echo JHTML::link( $link, $team->team_name." (" . $team->team_shortcut . ")" );
            }
            else
            {
            echo JHTML::link( $link, $team->team_name );
            }
            
            }
                         
            }
            else
            {
            if ( $this->config['show_teams_trikot_of_club'] )
            {
            echo JHTML::link( $link, JHTML::image($team->trikot_home, $team->team_name, $params).$team->team_name );
            }
            else
            {
            echo JHTML::link( $link, $team->team_name );
            }
            
            }
            echo "&nbsp;";
					?>
				</span>
				</td>
				<td>
				<span class="clubinfo_team_value">
				<?php
				if ( $team->team_description && $this->config['show_teams_description_of_club'] )
				{
					echo $team->team_description;
				}
				else
				{
					echo "&nbsp;";
				}
				?>
				</span>
				</td>
				</tr>
				<?php
			}
		}
	?>
	</div>
	</table>
</div>