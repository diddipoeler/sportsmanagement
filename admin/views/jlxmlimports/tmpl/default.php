<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
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
