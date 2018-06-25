<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

JHtml::_( 'behavior.tooltip' );

$uri = JFactory::getURI();
?>
<!-- import the functions to move the events between selection lists  -->
<?php
//$version = urlencode(sportsmanagementHelper::getVersion());
//echo JHtml::script( 'JL_eventsediting.js?v='.$version,'administrator/components/com_sportsmanagement/assets/js/');
?>
<script>
	function submitbutton(pressbutton)
	{
		var form = $('adminForm');
		if (pressbutton == 'cancel')
		{
			submitform( pressbutton );
			return;
		}
		var mylist = document.getElementById('project_teamslist');
		for(var i=0; i<mylist.length; i++)
		{
			  mylist[i].selected = true;
		}
		submitform( pressbutton );
	}
</script>

<style type="text/css">
	table.paramlist td.paramlist_key {
		width: 92px;
		text-align: left;
		height: 30px;
	}
</style>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<fieldset>
		<div class="fltrt">
			<button type="button" onclick="jQuery('select#project_teamslist > option').prop('selected', 'selected');Joomla.submitform('projectteams.assign', this.form)">
				<?php echo JText::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
	</fieldset>
	<div class="col50">
		<fieldset class="adminform">
			<legend>
				<?php
                if ( $this->project->project_art_id != 3 )
                {
				echo JText::sprintf( 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_TITLE', '<i>' . $this->project->name . '</i>');
                }
                else
                {
                echo JText::sprintf( 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTPERSONS_ASSIGN_TITLE', '<i>' . $this->project->name . '</i>');
                }
				?>
			</legend>
			<table class="admintable" border="0">
				<tr>
					<td>
						<b>
							<?php
                            if ( $this->project->project_art_id != 3 )
                            {
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_AVAIL_TEAMS' );
                            }
                            else
                            {
                            echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_AVAIL_PERSONS' );
                            }
							?>
						</b>
                        <br />
						<?php
						echo $this->lists['teams'];
						?>
					</td>
					<td style="text-align:center; ">
                    
<input id="moveright" type="button" value="<?php echo JText::_('COM_SPORTSMANAGEMENT_ASSIGN_TEAM_TO_PROJECT'); ?>" onclick="move_list_items('teamslist','project_teamslist');" />
<br />
<input id="moverightall" type="button" value="<?php echo JText::_('COM_SPORTSMANAGEMENT_ASSIGN_TEAM_ALL_TO_PROJECT'); ?>" onclick="move_list_items_all('teamslist','project_teamslist');" />
<br />
<input id="moveleft" type="button" value="<?php echo JText::_('COM_SPORTSMANAGEMENT_UNASSIGN_TEAM_TO_PROJECT'); ?>" onclick="move_list_items('project_teamslist','teamslist');" />
<br />
<input id="moveleftall" type="button" value="<?php echo JText::_('COM_SPORTSMANAGEMENT_UNASSIGN_TEAM_ALL_TO_PROJECT'); ?>" onclick="move_list_items_all('project_teamslist','teamslist');" />                    
<br />						
					</td>
					<td>
						<b>
							<?php
                            if ( $this->project->project_art_id != 3 )
                            {
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_PROJ_TEAMS' );
                            }
                            else
                            {
                            echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_PROJ_PERSONS' );
                            }
							?>
						</b>
                        <br />
						<?php
						echo $this->lists['project_teams'];
						?>
					</td>
			   </tr>
			</table>
		</fieldset>
		<div class="clr"></div>

		<input type="hidden" name="teamschanges_check"	value="0"	id="teamschanges_check" />
		<input type="hidden" name="option"				value="com_sportsmanagement" />
		<input type="hidden" name="project_id"				value="<?php echo $this->project->id; ?>" />
		<input type="hidden" name="task"				value="projectteam.save_matcheslist" />
	</div>
</form>