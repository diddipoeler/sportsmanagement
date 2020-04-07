<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlextsisimport
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$option = Factory::getApplication()->input->getCmd('option');
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.modal');

$url = 'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.$option.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'icons'.DIRECTORY_SEPARATOR.'sislogo.png';
$alt = 'DFBNet';

$attribs['width'] = '170px';
$attribs['height'] = '26px';
$attribs['align'] = 'left';

?>

<div id="editcell">
    <form enctype='multipart/form-data' method='post' name="adminForm">
        <table class='adminlist'>
            <thead>
              <tr>
                <th>
            <?php echo HTMLHelper::_('image', $url, $alt, $attribs);; ?>
            <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_SIS_IMPORT_TABLE_TITLE_1', $this->config->get('upload_maxsize')); ?>
                </th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <td>
                <?php
                echo '<br />';
                echo '<b>'.Text::_('COM_SPORTSMANAGEMENT_ADMIN_SIS_IMPORT_EXTENTION_INFO').'</b><br />';
                echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_SIS_IMPORT_HINT1').'<br />';
                echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_SIS_IMPORT_HINT2', $this->revisionDate);
                ?>
                </td>
              </tr>
            </tfoot>
            <tbody>
        <?php
        // TODO: Check update functionality in later version of that extension. For now, disabled
        if (0 ) {
            ?>
          <tr>
          <td>
          <fieldset>
          <legend>
        <?php
        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_SIS_IMPORT_SELECT_USE_PROJECT');
        ?>
       </legend>    
        <input class='input_box' type='checkbox' id='dfbimportupdate' name='dfbimportupdate'  /><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_SIS_IMPORT_USE_PROJECT'); ?>    
        </fieldset>
        </td>
        </tr>
        <?php
        }
        ?>
    

        <?php
    
        ?>
    
    
      <tr>
      <td>
      <fieldset>
            <legend>
                <?php
                echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_SIS_IMPORT_FILE');
                ?>
            </legend>

          
                <input type="text" name='liganummer' value='' size="100" />
                <input class="button" type="submit" onclick="return Joomla.submitform('jlextsisimport.save')" value="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_SIS_IMPORT_UPLOAD_BUTTON'); ?>" />
            </fieldset>
      </td>
      </tr>
      </tbody>
        </table>
        <input type="hidden" name='sent' value='1' />
        <input type="hidden" name='MAX_FILE_SIZE' value='<?php echo $this->config->get('upload_maxsize'); ?>' />
        <input type="hidden" name="option" value="com_sportsmanagement" />
        <input type="hidden" name='task' value='jlextsisimport.save' />
    <?php echo HTMLHelper::_('form.token')."\n"; ?>
    </form>
</div>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?> 
