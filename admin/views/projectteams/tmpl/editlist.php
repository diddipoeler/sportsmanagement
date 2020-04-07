<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       editlist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage projectteams
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
HTMLHelper::_('behavior.tooltip');

?>
<!-- import the functions to move the events between selection lists  -->
<?php


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
				<?php echo Text::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="Joomla.submitform('projectteam.cancelmodal', this.form)">
<?php echo Text::_('JCANCEL');?></button>
		</div>
	</fieldset>
	<div class="col50">
		<fieldset class="adminform">
			<legend>
				<?php
				if ($this->project->project_art_id != 3)
				{
					echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_TITLE', '<i>' . $this->project->name . '</i>');
				}
				else
				{
					echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECTPERSONS_ASSIGN_TITLE', '<i>' . $this->project->name . '</i>');
				}
				?>
			</legend>
			<table class="admintable" border="0">
				<tr>
					<td>
						<b>
		<?php
		if ($this->project->project_art_id != 3)
		{
			echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_AVAIL_TEAMS');
		}
		else
		{
			echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_AVAIL_PERSONS');
		}
		?>
						</b>
						<br />
		<?php
		echo $this->lists['teams'];
		?>
					</td>
					<td style="text-align:center; ">

				  <input id="moveright" type="button" value="<?php echo Text::_('COM_SPORTSMANAGEMENT_ASSIGN_TEAM_TO_PROJECT'); ?>" onclick="move_list_items('teamslist','project_teamslist');" />
<br />
<input id="moverightall" type="button" value="<?php echo Text::_('COM_SPORTSMANAGEMENT_ASSIGN_TEAM_ALL_TO_PROJECT'); ?>" onclick="move_list_items_all('teamslist','project_teamslist');" />
<br />
<input id="moveleft" type="button" value="<?php echo Text::_('COM_SPORTSMANAGEMENT_UNASSIGN_TEAM_TO_PROJECT'); ?>" onclick="move_list_items('project_teamslist','teamslist');" />
<br />
<input id="moveleftall" type="button" value="<?php echo Text::_('COM_SPORTSMANAGEMENT_UNASSIGN_TEAM_ALL_TO_PROJECT'); ?>" onclick="move_list_items_all('project_teamslist','teamslist');" />                  
<br />						
					</td>
					<td>
						<b>
		<?php
		if ($this->project->project_art_id != 3)
		{
			echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_PROJ_TEAMS');
		}
		else
		{
			echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_PROJ_PERSONS');
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

		<input type="hidden" name="teamschanges_check" value="0" id="teamschanges_check" />
		<input type="hidden" name="option" value="com_sportsmanagement" />
		<input type="hidden" name="project_id" value="<?php echo $this->project->id; ?>" />
		<input type="hidden" name="task" value="projectteam.save_matcheslist" />
	<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
