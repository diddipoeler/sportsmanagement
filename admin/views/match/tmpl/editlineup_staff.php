<?php
/**
 * SportsManagement administrator match lineup staff template for Joomla 5/6.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage match
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
?>
<fieldset class="adminform">
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUS'); ?></legend>
    <table class="adminlist">
        <thead>
            <tr>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUS_STAFF'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUS_ASSIGNED'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center align-middle">
                    <?php echo $this->lists['team_staffs']; ?>
                </td>
                <td class="text-center align-top">
                    <table>
                        <tbody>
                        <?php foreach ($this->staffpositions ?: [] as $positionId => $position) : ?>
                            <?php
                            $positionId = (int) $positionId;
                            $targetId = 'staffposition' . $positionId;
                            ?>
                            <tr>
                                <td class="text-center align-middle">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-secondary"
                                        data-lineup-action="move-selected"
                                        data-source-select="staff"
                                        data-destination-select="<?php echo $targetId; ?>"
                                    >
                                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ASSIGN_TO_LINEUP'); ?>
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-secondary"
                                        data-lineup-action="move-selected"
                                        data-source-select="<?php echo $targetId; ?>"
                                        data-destination-select="staff"
                                    >
                                        <?php echo Text::_('COM_SPORTSMANAGEMENT_DELETE_FROM_LINEUP'); ?>
                                    </button>
                                </td>
                                <td>
                                    <strong><?php echo Text::_($position->text); ?></strong><br>
                                    <?php echo $this->lists['team_staffs' . $positionId]; ?>
                                </td>
                                <td class="text-center align-middle">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-secondary smove-up"
                                        data-lineup-action="move-up"
                                        data-target-select="<?php echo $targetId; ?>"
                                    >
                                        <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?>
                                    </button>
                                    <br>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-secondary smove-down"
                                        data-lineup-action="move-down"
                                        data-target-select="<?php echo $targetId; ?>"
                                    >
                                        <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DOWN'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</fieldset>
