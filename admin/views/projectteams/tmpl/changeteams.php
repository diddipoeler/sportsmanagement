<?php defined('_JEXEC') or die('Restricted access');

?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm">
	<fieldset class="adminform">
		<legend>
		<?php
		echo JText::_( 'COM_JOOMLEAGUE_ADMIN_PROJECTTEAMS_CHANGEASSIGN_TEAMS' );
		?>
		</legend>
		<table class="adminlist">
			<thead>
				<tr>
					<th class="title"><?PHP echo JText::_( '' ); ?>
					</th>
					<th class="title"><?PHP echo JText::_( 'COM_JOOMLEAGUE_ADMIN_PROJECTTEAMS_CHANGE' ); ?>
					</th>
					<th class="title"><?PHP echo JText::_( 'COM_JOOMLEAGUE_ADMIN_PROJECTTEAMS_SELECT_OLD_TEAM' ); ?>
					</th>
					<th class="title"><?PHP echo JText::_( 'COM_JOOMLEAGUE_ADMIN_PROJECTTEAMS_SELECT_NEW_TEAM' ); ?>
					</th>
				</tr>
			</thead>

			<?PHP

			//$lfdnummer = 1;
			$k = 0;
			$i = 0;

			foreach ( $this->projectteam as $row )
			{
				$checked = JHTML::_( 'grid.id', 'oldteamid'.$i, $row->id, $row->checked_out, 'oldteamid' );
				$append=' style="background-color:#bbffff"';
				$inputappend	= '';
				$selectedvalue = 0;
				?>
			<tr class="<?php echo "row$k"; ?>">
				<td class="center"><?php
				echo $i;
				?>
				</td>
				<td class="center"><?php
				echo $checked;
				?>
				</td>
				<td><?php
				echo $row->name;
				?>
				</td>
				<td class="nowrap" class="center"><?php
				echo JHTML::_( 'select.genericlist', $this->lists['all_teams'], 'newteamid[' . $row->id . ']', $inputappend . 'class="inputbox" size="1" onchange="document.getElementById(\'cboldteamid' . $i . '\').checked=true"' . $append, 'value', 'text', $selectedvalue );
				?>
				</td>
			</tr>
			<?php
			$i++;
			$k=(1-$k);
			}
			?>
		</table>
	</fieldset>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option"				value="com_joomleague" />
	<?php echo JHTML::_('form.token')."\n"; ?>
</form>
