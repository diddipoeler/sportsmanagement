<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage jlxmlimports
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

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
			<thead><tr><th><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TABLE_TITLE_1', $this->upload_maxsize ); ?></th></tr></thead>
			<tfoot><tr><td><?php
				echo '<p>';
				echo '<b>'.Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_EXTENSION_INFO').'</b>';
				echo '</p>';
				echo '<p>';
				echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_HINT1').'<br>';
				echo '</p>';
				?></td></tr></tfoot>
			<tbody>
      <!--      
      <tr>
      <td>
      <fieldset style='text-align: center; '>
      <legend>
				<?php
				echo Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_PROJECT_FUSSBALLINEUROPA');
				?>
			</legend>      
      <input class='input_box' type='text' id='projektfussballineuropa' name='projektfussballineuropa'  value="<?php echo $this->projektfussballineuropa; ?>"/><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_USE_PROJECT_ID'); ?>      
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
				echo Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_USE_PROJECT');
				?>
			</legend>      
      <input class='input_box' type='checkbox' id='importupdate' name='importupdate'  /><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_USE_PROJECT'); ?>      
      </fieldset>
      </td>
      </tr>
      -->
            <tr>
            <td>
            <fieldset style='text-align: center; '>
				<input class='input_box' id='import_package' name='import_package' type='file' size='57' />
				<input class='button' type='submit' value='<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_UPLOAD_BUTTON'); ?>' />
			</fieldset>
            </td>
            </tr>
<tr>
      <td>
      <fieldset style='text-align: center; '>
      <legend>
				<?php
				echo Text::_( '1. Èlanska liga MNZ Maribor');
				?>
			</legend>      
      <input class='input_box' type='checkbox' id='importelanska' name='importelanska'  /><?php echo Text::_('1. Èlanska liga MNZ Maribor'); ?>      
      </fieldset>
      </td>
      </tr>
				
            </tbody>
		</table>
		<input type='hidden' name='sent' value='1' />
		<input type='hidden' name='MAX_FILE_SIZE' value='<?php echo $this->config->get('upload_maxsize'); ?>' />
		<input type='hidden' name='filter_season' value='<?php echo $this->filter_season; ?>' />
		<input type='hidden' name='task' value='jlxmlimport.save' />
		<?php echo HTMLHelper::_('form.token')."\n"; ?>
	</form>
</div>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   
