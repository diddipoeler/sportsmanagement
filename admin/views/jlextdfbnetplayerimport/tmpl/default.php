<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlextdfbnetplayerimport
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
$option = Factory::getApplication()->input->getCmd('option');

$url = 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $option . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . 'dfbnet-logo.gif';
$alt = 'DFBNet';

$attribs['width']  = '184px';
$attribs['height'] = '77px';
$attribs['align']  = 'left';

?>

<div id="editcell">
    <form enctype='multipart/form-data' action='<?php echo $this->request_url; ?>' method='post' name="adminForm"
          id="adminForm">
        <table class='table'>
            <thead>
            <tr>
                <th>
					<?php echo HTMLHelper::_('image', $url, $alt, $attribs);; ?>
					<?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_TABLE_TITLE_1', $this->config->get('upload_maxsize')); ?>
                </th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td>
					<?php
					echo '<br />';
					echo '<b>' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_EXTENTION_INFO') . '</b><br />';
					echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_HINT1') . '<br />';
					echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_HINT2', $this->revisionDate);
					?>
                </td>
            </tr>
            </tfoot>
            <tbody>
			<?php
			/**
			 * TODO: Check update functionality in later version of that extension. For now, disabled
			 */
			if (0)
			{
				?>
                <tr>
                    <td>
                        <fieldset>
                            <legend>
								<?php
								echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_SELECT_USE_PROJECT');
								?>
                            </legend>
                            <input class='input_box' type='checkbox' id='dfbimportupdate'
                                   name='dfbimportupdate'/><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_USE_PROJECT'); ?>
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
							echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_WHICH_FILE');
							?>
                        </legend>
                        <input type="radio" name="whichfile" value="playerfile"
                               checked> <?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_PLAYERFILE'); ?>
                        <br><br>
                        <input type="radio" name="whichfile"
                               value="matchfile"> <?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_MATCHFILE'); ?>
                        <br><br>
                        <input type="radio" name="whichfile"
                               value="icsfile"> <?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_ICSFILE'); ?>
                        <br>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <td>
                    <fieldset>
                        <legend>
							<?php
							echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_WHICH_SEASON');
							?>
                        </legend>
						<?PHP
						echo $this->lists['seasons'];
			    echo $this->lists['nation2'];
						?>
                    </fieldset>
                </td>
            </tr>
			<?php
			/**
			 * TODO: Check update functionality in later version of that extension. For now, disabled
			 */
			if (0)
			{
				?>
                <tr>
                    <td>
                        <fieldset>
                            <legend>
								<?php
								echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_DELIMITER');
								?>
                            </legend>

                            <input type="radio" name="delimiter" value=";"
                                   checked> <?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_DELIMITER_SEMICOLON'); ?>
                            <br><br>
                            <input type="radio" name="delimiter"
                                   value=","> <?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_DELIMITER_COMMA'); ?>
                            <br><br>
                            <input type="radio" name="delimiter"
                                   value="\t"> <?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_DELIMITER_TABULAR'); ?>
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
							echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_FILE');
							?>
                        </legend>

                        <input class="input_box" id="import_package" name="import_package" type="file" size="57"/>
                        <input class="button" type="submit"
                               onclick="return Joomla.submitform('jlextdfbnetplayerimport.save')"
                               value="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_UPLOAD_BUTTON'); ?>"/>
                    </fieldset>
                </td>
            </tr>
            </tbody>
        </table>
        <input type="hidden" name='sent' value='1'/>
        <input type="hidden" name='MAX_FILE_SIZE' value='<?php echo $this->config->get('upload_maxsize'); ?>'/>
        <input type="hidden" name='task' value='jlextdfbnetplayerimport.save'/>
		<?php echo HTMLHelper::_('form.token') . "\n"; ?>
    </form>
</div>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?> 
