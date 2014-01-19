<?php 
defined( '_JEXEC' ) or die( 'Restricted access' );

JHtml::_( 'behavior.tooltip' );

$uri = JFactory::getURI();
?>
<!-- import the functions to move the events between selection lists  -->
<?php
$version = urlencode(sportsmanagementHelper::getVersion());
echo JHtml::script( 'JL_eventsediting.js?v='.$version,'administrator/components/com_sportsmanagement/assets/js/');
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
			<button id="cancel" type="button" onclick="<?php echo JRequest::getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
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
						</b><br />
						<?php
						echo $this->lists['teams'];
						?>
					</td>
					<td style="text-align:center; ">
                    
<input id="moveright" type="button" value="Move Right" onclick="move_list_items('teamslist','project_teamslist');" />
<input id="moverightall" type="button" value="Move Right All" onclick="move_list_items_all('teamslist','project_teamslist');" />
<input id="moveleft" type="button" value="Move Left" onclick="move_list_items('project_teamslist','teamslist');" />
<input id="moveleftall" type="button" value="Move Left All" onclick="move_list_items_all('project_teamslist','teamslist');" />                    
						
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
						</b><br />
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