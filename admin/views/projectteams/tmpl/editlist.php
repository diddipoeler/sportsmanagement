<?php defined( '_JEXEC' ) or die( 'Restricted access' );

JHTML::_( 'behavior.tooltip' );

$uri = JFactory::getURI();
?>
<!-- import the functions to move the events between selection lists  -->
<?php
$version = urlencode(JoomleagueHelper::getVersion());
echo JHTML::script( 'JL_eventsediting.js?v='.$version,'administrator/components/com_joomleague/assets/js/');
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

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm">
	<div class="col50">
		<fieldset class="adminform">
			<legend>
				<?php
				echo JText::sprintf( 'COM_JOOMLEAGUE_ADMIN_PROJECTTEAMS_ASSIGN_TITLE', '<i>' . $this->projectws->name . '</i>');
				?>
			</legend>
			<table class="admintable" border="0">
				<tr>
					<td>
						<b>
							<?php
							echo JText::_( 'COM_JOOMLEAGUE_ADMIN_PROJECTTEAMS_ASSIGN_AVAIL_TEAMS' );
							?>
						</b><br />
						<?php
						echo $this->lists['teams'];
						?>
					</td>
					<td style="text-align:center; ">
						&nbsp;&nbsp;
						<input	type="button" class="inputbox"
								onclick="handleMoveLeftToRight()"
								value="&gt;&gt;" />
						&nbsp;&nbsp;<br />&nbsp;&nbsp;
					 	<input	type="button" class="inputbox"
					 			onclick="handleMoveRightToLeft()"
								value="&lt;&lt;" />
						&nbsp;&nbsp;
					</td>
					<td>
						<b>
							<?php
							echo JText::_( 'COM_JOOMLEAGUE_ADMIN_PROJECTTEAMS_ASSIGN_PROJ_TEAMS' );
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
		<input type="hidden" name="option"				value="com_joomleague" />
		<input type="hidden" name="cid[]"				value="<?php echo $this->projectws->id; ?>" />
		<input type="hidden" name="task"				value="projectteam.save_matcheslist" />
	</div>
</form>