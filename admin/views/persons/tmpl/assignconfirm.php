<?php defined('_JEXEC') or die('Restricted access');
?>
<script>
<!--
	function submitbutton(pressbutton)
	{
		if (pressbutton == 'saveassigned')
		{
			submitform(pressbutton);
		}
	}
//-->
</script>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm">
	<fieldset>
		<legend>
			<?php
			echo JText::sprintf('Assign persons to a team or the project [%1$s]','<i>'.$this->projectname.'</i>');
			?>
		</legend>
		<ul>
			<?php
			foreach ($this->persons AS $p)
			{
				?>
				<li>
					<input type="hidden" name="pid" value="<?php echo $p->id ?>" />
					<?php echo JoomleagueHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, 0) ?>
				</li>
				<?php
			}
			?>
		</ul>
		<p class="instructions">
			<?php
			echo JText::_('Assign selected persons as a player,staff or referee');
			?>
		</p>
		<?php
		echo $this->lists['type'];
		?>
		<p class="instructions">
			<?php
			echo JText::_('Select the team to assign the selected persons to if you want to assign players or staff.');
			echo '<br />';
			echo JText::_('Assigning Referees needs the following selection to be left untouched!');
			?>
		</p>
		<?php
		echo $this->lists['teams'];
		?>
	</fieldset>
	<input type="hidden" name="project_id"	value="<?php echo JRequest::getVar('project_id'); ?>" />
	<input type="hidden" name="task"		value="" />
	<?php echo JHTML::_('form.token'); ?>
</form>