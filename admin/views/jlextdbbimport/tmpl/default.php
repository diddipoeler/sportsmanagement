<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlextdbbimport
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$option          = Factory::getApplication()->input->getCmd('option');
$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

$url = 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $option . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . 'dbb-gross.png';
$alt = 'DFBNet';

$attribs['width']  = '101px';
$attribs['height'] = '160px';
$attribs['align']  = 'left';
?>
<div id="editcell">
    <form enctype='multipart/form-data' method='post' name="adminForm">
        <table class='adminlist'>
            <thead>
            <tr>
                <th>
					<?php echo HTMLHelper::_('image', $url, $alt, $attribs);; ?>
					<?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_TABLE_TITLE_1', $this->config->get('upload_maxsize')); ?>
                </th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td>
					<?php
					echo '<br />';
					echo '<b>' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_EXTENTION_INFO') . '</b><br />';
					echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_HINT1') . '<br />';
					echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_HINT2', $this->revisionDate);
					?>
                </td>
            </tr>
            </tfoot>
            <tbody>
			<?php
			// TODO: Check update functionality in later version of that extension. For now, disabled
			if (0)
			{
				?>
                <tr>
                    <td>
                        <fieldset>
                            <legend>
								<?php
								echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_SELECT_USE_PROJECT');
								?>
                            </legend>
                            <input class='input_box' type='checkbox' id='dfbimportupdate'
                                   name='dfbimportupdate'/><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_USE_PROJECT'); ?>
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
							echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_WHICH_FILE');
							?>
                        </legend>
                        <input type="radio" name="whichfile"
                               value="playerfile"> <?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_PLAYERFILE'); ?>
                        <br><br>
                        <input type="radio" name="whichfile"
                               value="matchfile"> <?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_MATCHFILE'); ?>
                        <br><br>
                        <input type="radio" name="whichfile" value="icsfile"
                               checked> <?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_ICSFILE'); ?><br>
                    </fieldset>
                </td>
            </tr>

			<?php
			// TODO: Disabled, set delimiter hardcoded to tab in the model (because DFBNet uses only that delimiter since 2013)
			if (0)
			{
				?>
                <tr>
                    <td>
                        <fieldset>
                            <legend>
								<?php
								echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_DELIMITER');
								?>
                            </legend>

                            <input type="radio" name="delimiter" value=";"
                                   checked> <?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_DELIMITER_SEMICOLON'); ?>
                            <br><br>
                            <input type="radio" name="delimiter"
                                   value=","> <?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_DELIMITER_COMMA'); ?>
                            <br><br>
                            <input type="radio" name="delimiter"
                                   value="\t"> <?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_DELIMITER_TABULAR'); ?>
                            <br>
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
							echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_FILE');
							?>
                        </legend>

                        <input class="input_box" id="import_package" name="import_package" type="file" size="57"/>
                        <input class="button" type="submit" onclick="return Joomla.submitform('jlextdbbimport.save')"
                               value="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_UPLOAD_BUTTON'); ?>"/>
                    </fieldset>
                </td>
            </tr>
            </tbody>
        </table>
        <input type="hidden" name='sent' value='1'/>
        <input type="hidden" name='MAX_FILE_SIZE' value='<?php echo $this->config->get('upload_maxsize'); ?>'/>
        <input type="hidden" name="option" value="com_sportsmanagement"/>
        <input type="hidden" name='task' value='jlextdbbimport.save'/>
		<?php echo HTMLHelper::_('form.token') . "\n"; ?>
    </form>
</div>
<?PHP

echo $this->loadTemplate('footer');

?> 
