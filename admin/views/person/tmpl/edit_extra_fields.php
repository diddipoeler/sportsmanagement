<?php 
defined('_JEXEC') or die('Restricted access');

//echo 'ext_fields<br><pre>'.print_r($this->lists, true).'</pre><br>';

?>
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_EXTRA_FIELDS' );?>
			</legend>
<table class="">
<?php
for($p=0;$p<count($this->lists['ext_fields']);$p++){
			?>
			<tr>
				<td width="100">
					<?php echo $this->lists['ext_fields'][$p]->name;?>
				</td>
				<td>
					<input type="text" maxlength="255" size="60" name="extraf[]" value="<?php echo isset($this->lists['ext_fields'][$p]->fvalue)?htmlspecialchars($this->lists['ext_fields'][$p]->fvalue):""?>" />
					<input type="hidden" name="extra_id[]" value="<?php echo $this->lists['ext_fields'][$p]->id?>" />
				</td>
			</tr>
			<?php	
			}
				?>
			</table>
      			
		</fieldset>