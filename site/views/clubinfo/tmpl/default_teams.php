<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<div class="row-fluid">
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">	
<h4>
<?php echo JText::_('COM_SPORTSMANAGEMENT_CLUBINFO_TEAMS'); ?>
</h4>
	
	<table class="table">
	
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
                <address>
			
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
            </address>
            <?PHP
		}
	?>
	
	</table>
</div>
</div>