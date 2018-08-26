<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jlextdfbnetplayerimport
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\HTML\HTMLHelper;

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
$option = JFactory::getApplication()->input->getCmd('option');

HTMLHelper::_( 'behavior.tooltip' );
HTMLHelper::_( 'behavior.modal' );

//$url = JPATH_ADMINISTRATOR . DS. 'components'.DS.$option. DS.'assets'.DS.'icons'.DS.'dfbnet-logo.gif';
$url = 'administrator'.DS.'components'.DS.$option. DS.'assets'.DS.'icons'.DS.'dfbnet-logo.gif';
//$url16 = 'components/com_joomleague/extensions/jlextdfbnetplayerimport/admin/assets/images/dfbnet-logo-16.gif';
$alt = 'DFBNet';

$attribs['width'] = '184px';
$attribs['height'] = '77px';
$attribs['align'] = 'left';

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
			      <?php echo HTMLHelper::_('image', $url, $alt, $attribs);; ?>
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
		<?php echo HTMLHelper::_('form.token')."\n"; ?>
	</form>
</div>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   
