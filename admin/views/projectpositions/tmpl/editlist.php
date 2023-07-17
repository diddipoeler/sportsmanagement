<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage projectpositions
 * @file       editlist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>

<form action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>" id='component-form' method='post'
      style='display:inline' name='adminform'>
    <div class="col50">

        <fieldset>
            <div class="fltrt">
                <button type="button"
                        onclick="jQuery('select#project_positionslist > option').prop('selected', 'selected');Joomla.submitform('projectpositions.store', this.form)">
					<?php echo Text::_('JSAVE'); ?></button>
                <button id="cancel" type="button" onclick="Joomla.submitform('projectpositions.cancel', this.form)">
					<?php echo Text::_('JCANCEL'); ?></button>
            </div>
        </fieldset>

        <fieldset class="adminform">
            <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_EDIT_LEGEND', '<i>' . $this->project->name . '</i>'); ?></legend>
            <table class="<?php echo $this->table_data_class; ?>">
                <thead>
                <tr>
                    <th class="" width=""><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_EDIT_AVAILABLE'); ?></th>
                    <th class="" width=""></th>
                    <th class="" width=""><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_EDIT_ASSIGNED'); ?></th>

                </tr>
                </thead>
                <tr>
                    <td class="" width=""><?php echo $this->lists['positions']; ?></td>
                    <td class="" width="" style="text-align:center;">
                        <input id="moveright" type="button" value="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_ASSIGN'); ?>"
                               onclick="move_list_items('positionslist','project_positionslist');"/>
                               <br />
                        <input id="moverightall" type="button" value="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_ASSIGNALL'); ?>"
                               onclick="move_list_items_all('positionslist','project_positionslist');"/>
                               <br />
                        <input id="moveleft" type="button" value="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_NOT_ASSIGN'); ?>"
                               onclick="move_list_items('project_positionslist','positionslist');"/>
                               <br />
                        <input id="moveleftall" type="button" value="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_NOT_ASSIGNALL'); ?>"
                               onclick="move_list_items_all('project_positionslist','positionslist');"/>

                    </td>
                    <td class="" width=""><?php echo $this->lists['project_positions']; ?></td>
                </tr>
            </table>
        </fieldset>
        <div class="clr"></div>
        <input type="hidden" name="positionschanges_check" value="0" id="positionschanges_check"/>
        <input type="hidden" name="pid" value="<?php echo $this->project->id; ?>"/>
        <input type="hidden" name="task" value=""/>
        <input type='hidden' name='project_id' value='<?php echo $this->project->id; ?>'/>
        <input type="hidden" name="component" value="com_sportsmanagement"/>
    </div>
	<?php echo HTMLHelper::_('form.token') . "\n"; ?>
</form>
