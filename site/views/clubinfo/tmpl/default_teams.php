<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_teams.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<div class="row-fluid" id="default_teams">
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">	
<h4>
<?php echo JText::_('COM_SPORTSMANAGEMENT_CLUBINFO_TEAMS'); ?>
</h4>
	
	<?php
	$params = array();
	$params['width'] = "30";
	
		foreach ( $this->teams as $team )
		{
			if ( $team->team_name )
			{
               $routeparameter = array();
              $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
              $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $team->pid;
       $routeparameter['tid'] = $team->team_slug;
       $routeparameter['ptid'] = $team->ptid;
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
				?>
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			
					<?php
						
						if ( $team->team_shortcut ) 
            { 
            //echo "(" . $team->team_shortcut . ")";
            if ( $this->config['show_teams_trikot_of_club'] )
            {
            
            if ( $this->config['show_teams_shortcut_of_club'] )
            {
            echo JHtml::link( $link, JHtml::image($team->trikot_home, $team->team_name, $params).$team->team_name." (" . $team->team_shortcut . ")" );
            }
            else
            {
            echo JHtml::link( $link, JHtml::image($team->trikot_home, $team->team_name, $params).$team->team_name );
            }
            
            }
            else
            {
            if ( $this->config['show_teams_shortcut_of_club'] )
            {
            echo JHtml::link( $link, $team->team_name." (" . $team->team_shortcut . ")" );
            }
            else
            {
            echo JHtml::link( $link, $team->team_name );
            }
            
            }
                         
            }
            else
            {
            if ( $this->config['show_teams_trikot_of_club'] )
            {
            echo JHtml::link( $link, JHtml::image($team->trikot_home, $team->team_name, $params).$team->team_name );
            }
            else
            {
            echo JHtml::link( $link, $team->team_name );
            }
            
            }
            echo "&nbsp;";
					?>
				
				<?php
				if ( $team->team_description && $this->config['show_teams_description_of_club'] )
				{
					echo $team->team_description;
				}
				else
				{
					echo "&nbsp;";
				}
			
            if ( $this->config['show_teams_picture'] )
            {
            echo sportsmanagementHelperHtml::getBootstrapModalImage('clubteam'.$team->id,$team->project_team_picture,$team->team_name,$this->config['team_picture_width']);       
            }
            	
			}
            ?>
            </div>
            <?PHP
		}
	?>
	
	
</div>
</div>
