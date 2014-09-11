<?php defined('_JEXEC') or die('Restricted access');
?>
	<fieldset class="adminform">
		<legend>
			<?php
			echo JText::sprintf(	'COM_JOOMLEAGUE_ADMIN_TREETONODE_TITLE_DESCRIPTION',
									'<i>' . $this->node->node . '</i>',
									'<i>' . $this->projectws->name . '</i>');
			?>
		</legend>

			<table class="admintable" border="0">
				<tr>
					<td nowrap="nowrap" class="key" style="text-align:right; ">
						<?php echo JText::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_TITLE_NODE'); ?>
					</td>
					<td>
						<input	class="text_area" type="text" name="title" id="title" size="60" maxlength="250"
						value="<?php echo $this->node->title; ?>" />
					</td>
				</tr>
				<tr>
					<td class="nowrap" class="key" style="text-align:right; ">
						<?php echo JText::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_CONTENT_NODE'); ?>
					</td>
					<td>
						<input	class="text_area" type="text" name="content" id="content" size="60" maxlength="250"
						value="<?php echo $this->node->content; ?>" />
					</td>
				</tr>
				<tr>
					<td class="nowrap" class="key" style="text-align:right; ">
						<?php echo JText::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_TEAM'); ?>
					</td>
					<td>
				<?php
				$append='';
				if ($this->node->team_id == 0)
				{
					$append=' style="background-color:#bbffff"';
				}
				echo JHtml::_(	'select.genericlist',$this->lists['team'],'team_id'.$this->node->id,
				'class="inputbox select-hometeam" size="1"'.$append,'value','text',$this->node->team_id);
				?>
					</td>
				</tr>
			</table>

	</fieldset>