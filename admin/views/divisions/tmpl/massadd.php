<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage divisions
 * @file       massadd.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

$close = Factory::getApplication()->input->getInt('close', 0);

if ($close == 1)
{
	?>
    <script>
        jQuery(document).ready(function($){
            $('cancel').onclick();
        });
    </script>
	<?php
}

?>
    <div id='alt_massadd_enter' style='display:block'>
        <fieldset class='adminform'>
            <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_MASSADD_LEGEND', '<i>' . $this->project->name . '</i>'); ?></legend>
            <form action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>" id='component-form'
                  method='post' style='display:inline' name='adminform'>

                <fieldset>
                    <div class="fltrt">
                        <button id="save" type="button" onclick="Joomla.submitform('divisions.massadd', this.form)">
							<?php echo Text::_('JSAVE'); ?></button>
                        <button id="cancel" type="button" onclick="Joomla.submitform('divisions.cancel', this.form)">
							<?php echo Text::_('JCANCEL'); ?></button>
                    </div>

                </fieldset>

                <input type='hidden' name='project_id' value='<?php echo $this->project->id; ?>'/>
                <input type='hidden' name='task' value=''/>
				<?php echo HTMLHelper::_('form.token') . "\n"; ?>
                <table class='admintable'>
                    <tbody>
                    <tr>
                        <td class='key'
                            nowrap='nowrap'><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_MASSADD_COUNT'); ?></td>
                        <td>
                            <input type='text' name='add_division_count' id='add_division_count' value='0' size='3'
                                   class='inputbox'/>
                        </td>

                    </tr>
                    </tbody>
                </table>
                <input type='hidden' name='project_id' value='<?php echo $this->project->id; ?>'/>
                <input type="hidden" name="component" value="com_sportsmanagement"/>

            </form>
        </fieldset>
    </div>

<?PHP

