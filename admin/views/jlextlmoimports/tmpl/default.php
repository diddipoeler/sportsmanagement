<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlextlmoimports
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
    <div id="editcell">
        <form enctype='multipart/form-data' action='<?php echo $this->request_url; ?>' method='post' id='adminForm'>
            <table class='table'>
                <thead>
                <tr>
                    <th><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_TABLE_TITLE_1', $this->config->get('upload_maxsize')); ?></th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <td><?php
						echo '<p>';
						echo '<b>' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_EXTENTION_INFO') . '</b>';
						echo '</p>';
						echo '<p>';
						echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_HINT1') . '<br>';
						echo '</p>';
						?></td>
                </tr>
                </tfoot>
                <tbody>
                <tr>
                    <td>
                        <fieldset style='text-align: center; '>
                            <input class='input_box' id='import_package' name='import_package' type='file' size='57'/>
                            <input class='button' type='submit'
                                   value='<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_UPLOAD_BUTTON'); ?>'/>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $this->countries; ?>&nbsp;<?php echo JSMCountries::getCountryFlag($this->country); ?>
                        &nbsp;(<?php echo $this->country; ?>)
                    </td>
                </tr>
                <tr>
                    <td><?php echo $this->agegroup; ?></td>
                </tr>
                <tr>
                    <td style='background-color:#EEEEEE'>
                        <select name='copyTemplate' id='copyTemplate' style="display:none">
                            <option value="0"
                                    selected><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TEMPLATES_USEOWN'); ?></option>
							<?php
							foreach ($this->templates AS $row)
							{
								echo "<option value=\"$row->id\">$row->name</option>\n";
							}
							?>
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>
            <input type='hidden' name='sent' value='1'/>
            <input type='hidden' name='MAX_FILE_SIZE' value='<?php echo $this->config->get('upload_maxsize'); ?>'/>
            <input type='hidden' name='task' value='jlextlmoimports.save'/>
			<?php echo HTMLHelper::_('form.token') . "\n"; ?>
        </form>
    </div>
<?PHP

echo $this->loadTemplate('footer');

