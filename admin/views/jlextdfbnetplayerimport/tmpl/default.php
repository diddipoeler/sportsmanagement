<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                jlextdfbnetplayerimport.php
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

defined( '_JEXEC' ) or die( 'Restricted access' );
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
$option = JFactory::getApplication()->input->getCmd('option');

JHtml::_( 'behavior.tooltip' );
JHtml::_( 'behavior.modal' );

//$url = JPATH_ADMINISTRATOR . DS. 'components'.DS.$option. DS.'assets'.DS.'icons'.DS.'dfbnet-logo.gif';
$url = 'administrator'.DS.'components'.DS.$option. DS.'assets'.DS.'icons'.DS.'dfbnet-logo.gif';
//$url16 = 'components/com_joomleague/extensions/jlextdfbnetplayerimport/admin/assets/images/dfbnet-logo-16.gif';
$alt = 'DFBNet';

$attribs['width'] = '184px';
$attribs['height'] = '77px';
$attribs['align'] = 'left';
//$logo = JHtml::_('image', $url, $alt, $attribs);

// Set toolbar items for the page
//$doc = JFactory::getDocument();
//$style = " .icon-48-fb {components/com_joomleague/extensions/jlextdfbnetplayerimport/admin/assets/images/dfbnet-logo-16.gif); no-repeat; }";
//$doc->addStyleDeclaration( $style );

//JToolbarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT' ), 'generic.png' );

//JToolbarHelper::title(   JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT' ), $url16 );

//JToolbarHelper::save();
//JToolbarHelper::apply();


/*
echo 'default project <br>';
echo '<pre>';
print_r($url);
echo '</pre>';
*/

/*
echo 'default projectteams <br>';
echo '<pre>';
print_r($this->projectteams);
echo '</pre>';
*/

?>

<div id="editcell">
	<form enctype='multipart/form-data' action='<?php echo $this->request_url; ?>' method='post' name="adminForm" id="adminForm">
		<table class='table'>
			<thead>
			  <tr>
			    <th>
			      <?php echo JHtml::_('image', $url, $alt, $attribs);; ?>
			      <?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_TABLE_TITLE_1',$this->config->get('upload_maxsize')); ?>
			    </th>
			  </tr>
			</thead>
			<tfoot>
			  <tr>
			    <td>
				<?php
				echo '<br />';
				echo '<b>'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_EXTENTION_INFO').'</b><br />';
				echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_HINT1').'<br />';
				echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_HINT2',$this->revisionDate);
				?>
			    </td>
			  </tr>
			</tfoot>
			<tbody>
      <?php
/**
 * TODO: Check update functionality in later version of that extension. For now, disabled
 */
      if ( 0 )
      {
      ?>
      <tr>
      <td>
      <fieldset>
      <legend>
				<?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_SELECT_USE_PROJECT');
				?>
			</legend>      
      <input class='input_box' type='checkbox' id='dfbimportupdate' name='dfbimportupdate'  /><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_USE_PROJECT'); ?>      
      </fieldset>
      </td>
      </tr>
      <?php
      }
      ?>
      <tr>
      <td>
      <fieldset>
      <legend>
				<?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_WHICH_FILE');
				?>
			</legend>
      <input type="radio" name="whichfile" value="playerfile" checked> <?PHP echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_PLAYERFILE'); ?><br><br>
      <input type="radio" name="whichfile" value="matchfile"> <?PHP echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_MATCHFILE'); ?><br><br>
      <input type="radio" name="whichfile" value="icsfile"> <?PHP echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_ICSFILE'); ?><br>
      </fieldset>
      </td>
      </tr>
<tr>
      <td>
      <fieldset>
      <legend>
				<?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_WHICH_SEASON');
				?>
			</legend>
     <?PHP
      echo $this->lists['seasons'];
      ?>       
</fieldset>
      </td>
      </tr>            
      <?php
/**
 * TODO: Check update functionality in later version of that extension. For now, disabled
 */
      if ( 0 )
      {
      ?>
      <tr>
      <td>
      <fieldset>
      <legend>
				<?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_DELIMITER' );
				?>
			</legend>
			
      <input type="radio" name="delimiter" value=";" checked> <?PHP echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_DELIMITER_SEMICOLON'); ?><br><br>
      <input type="radio" name="delimiter" value=","> <?PHP echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_DELIMITER_COMMA'); ?><br><br>
      <input type="radio" name="delimiter" value="\t"> <?PHP echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_DELIMITER_TABULAR'); ?><br>
      </fieldset>
      </td>
      </tr>
      <?php
      }
      ?>
      
      
      <tr>
      <td>
      <fieldset>
            <legend>
				<?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_FILE' );
				?>
			</legend>

				<input class="input_box" id="import_package" name="import_package" type="file" size="57" />
				<input class="button" type="submit" onclick="return Joomla.submitform('jlextdfbnetplayerimport.save')" value="<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_UPLOAD_BUTTON'); ?>" />
			</fieldset>
      </td>
      </tr>
      </tbody>
		</table>
		<input type="hidden" name='sent' value='1' />
		<input type="hidden" name='MAX_FILE_SIZE' value='<?php echo $this->config->get('upload_maxsize'); ?>' />
		<input type="hidden" name='task' value='jlextdfbnetplayerimport.save' />
		<?php echo JHtml::_('form.token')."\n"; ?>
	</form>
</div>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   
