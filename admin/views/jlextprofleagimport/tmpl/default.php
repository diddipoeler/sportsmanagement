<?php 
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');JHtml::_('behavior.modal');
$templatesToLoad = array('footer');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<div id="editcell">
	<form enctype='multipart/form-data' action='<?php echo $this->request_url; ?>' method='post' id='adminForm'>
		<table class='adminlist'>
			<thead><tr><th><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_TABLE_TITLE_1', $this->config->get('upload_maxsize') ); ?></th></tr></thead>
			<tfoot><tr><td><?php
				echo '<p>';
				echo '<b>'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_EXTENTION_INFO').'</b>';
				echo '</p>';
				echo '<p>';
				echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_HINT1').'<br>';
				echo '</p>';
				?></td></tr></tfoot>
			<tbody>
      <tr>
      <td>
      <fieldset style='text-align: center; '>
				<input class='input_box' id='import_package' name='import_package' type='file' size='57' />
				<input class='button' type='submit' value='<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_UPLOAD_BUTTON'); ?>' />
			</fieldset>
      </td>
      </tr>
      <tr>
			
			<td><?php echo $this->countries; ?>&nbsp;<?php echo JSMCountries::getCountryFlag($this->country); ?>&nbsp;(<?php echo $this->country; ?>)</td>
		  </tr>
      </tbody>
		</table>
		<input type='hidden' name='sent' value='1' />
		<input type='hidden' name='MAX_FILE_SIZE' value='<?php echo $this->config->get('upload_maxsize'); ?>' />
		<input type='hidden' name='task' value='jlextprofleagimport.save' />
		<?php echo JHtml::_('form.token')."\n"; ?>
	</form>
</div>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   