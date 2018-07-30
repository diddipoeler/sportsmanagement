<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jlxmlimports
 */

defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div id="editcell">
	<form enctype='multipart/form-data' action='<?php echo $this->request_url; ?>' method='post' id='adminForm' name='adminForm'>
    
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
    
		<table class='adminlist'>
			<thead><tr><th><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TABLE_TITLE_1', $this->upload_maxsize ); ?></th></tr></thead>
			<tfoot><tr><td><?php
				echo '<p>';
				echo '<b>'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_EXTENSION_INFO').'</b>';
				echo '</p>';
				echo '<p>';
				echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_HINT1').'<br>';
				echo '</p>';
				?></td></tr></tfoot>
			<tbody>
      <!--      
      <tr>
      <td>
      <fieldset style='text-align: center; '>
      <legend>
				<?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_PROJECT_FUSSBALLINEUROPA');
				?>
			</legend>      
      <input class='input_box' type='text' id='projektfussballineuropa' name='projektfussballineuropa'  value="<?php echo $this->projektfussballineuropa; ?>"/><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_USE_PROJECT_ID'); ?>      
      </fieldset>
      </td>
      </tr>
      -->
      
      <!--
      <tr>
      <td>
      <fieldset style='text-align: center; '>
      <legend>
				<?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_USE_PROJECT');
				?>
			</legend>      
      <input class='input_box' type='checkbox' id='importupdate' name='importupdate'  /><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_USE_PROJECT'); ?>      
      </fieldset>
      </td>
      </tr>
      -->
            <tr>
            <td>
            <fieldset style='text-align: center; '>
				<input class='input_box' id='import_package' name='import_package' type='file' size='57' />
				<input class='button' type='submit' value='<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_UPLOAD_BUTTON'); ?>' />
			</fieldset>
            </td>
            </tr>
            </tbody>
		</table>
		<input type='hidden' name='sent' value='1' />
		<input type='hidden' name='MAX_FILE_SIZE' value='<?php echo $this->config->get('upload_maxsize'); ?>' />
		<input type='hidden' name='filter_season' value='<?php echo $this->filter_season; ?>' />
		<input type='hidden' name='task' value='jlxmlimport.save' />
		<?php echo JHtml::_('form.token')."\n"; ?>
	</form>
</div>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   
