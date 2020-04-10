<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage match
 * @file       editlineup_staff.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

?>
<fieldset class="adminform">
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUS'); ?></legend>
    <table class='adminlist'>
        <thead>
        <tr>
            <th>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUS_STAFF'); ?>
            </th>
            <th>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUS_ASSIGNED'); ?>
            </th>
        </tr>
        </thead>
        <tr>
            <td colspan="2">
        </tr>
        <tr>
            <td style="text-align:center; vertical-align:middle; ">
				<?php
				// Echo select list of non assigned players from team roster
				echo $this->lists['team_staffs'];
				?>
            </td>
            <td style="text-align:center; vertical-align:top; ">
                <table border='0'>
					<?php
					if ($this->staffpositions)
					{
						foreach ($this->staffpositions AS $position_id => $pos)
						{
							?>
                            <tr>
                                <td style='text-align:center; vertical-align:middle; '>
                                    <!-- left / right buttons -->
                                    <br/>


                                    <input id="moveright" type="button"
                                           value="<?php echo Text::_('COM_SPORTSMANAGEMENT_ASSIGN_TO_LINEUP'); ?>"
                                           onclick="move_list_items('staff','staffposition<?php echo $position_id; ?>');"/>
                                    <input id="moveleft" type="button"
                                           value="<?php echo Text::_('COM_SPORTSMANAGEMENT_DELETE_FROM_LINEUP'); ?>"
                                           onclick="move_list_items('staffposition<?php echo $position_id; ?>','staff');"/>


                                </td>
                                <td>
                                    <!-- player affected to this position -->
                                    <b><?php echo Text::_($pos->text); ?></b><br/>
									<?php echo $this->lists['team_staffs' . $position_id]; ?>
                                </td>
                                <td style='text-align:center; vertical-align:middle; '>
                                    <!-- up/down buttons -->
                                    <br/>
                                    <input type="button" onclick="move_up('staffposition<?php echo $position_id; ?>');"
                                           class="inputbox smove-up"
                                           value="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?>"/><br/>
                                    <input type="button"
                                           onclick="move_down('staffposition<?php echo $position_id; ?>');"
                                           class="inputbox smove-down"
                                           value="<?php echo Text::_('JGLOBAL_DOWN'); ?>"/>
                                </td>
                            </tr>
							<?php
						}
					}
					?>
                </table>
            </td>
        </tr>
    </table>
</fieldset>
