<?php 


defined('_JEXEC') or die('Restricted access');

//echo 'cfg_which_media_tool<pre>'.print_r($this->cfg_which_media_tool , true).'</pre><br>';

?>

		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_LOGO' );?>
			</legend>
			<table class="admintable" border='0'>
					<?php foreach ($this->form->getFieldset('picture') as $field): ?>
					<tr>
						<td class="key"><?php echo $field->label; ?></td>
						<td><?php echo $field->input; ?></td>
					</tr>					
					<?php endforeach; ?>
			</table>
		</fieldset>
        
