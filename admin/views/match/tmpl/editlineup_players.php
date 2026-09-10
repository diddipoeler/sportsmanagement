<?php
/**
 * SportsManagement administrator match lineup players template for Joomla 5/6.
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
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUP_START_LU'); ?></legend>
    <table class="adminlist">
        <thead>
            <tr>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUP_ROSTER'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUP_ASSIGNED'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2">
                    <?php if ($this->preFillSuccess) : ?>
                        <span class="text-danger">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_PREFILL_DONE'); ?>
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="text-center align-middle">
                    <?php echo $this->lists['team_players']; ?>
                </td>
                <td class="text-center align-top">
                    <table>
                        <tbody>
                        <?php foreach ($this->positions ?: [] as $positionId => $position) : ?>
                            <?php
                            $positionId = (int) $positionId;
                            $targetId = 'position' . $positionId;
                            ?>
                            <tr>
                                <td class="text-center align-middle">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-secondary"
                                        data-lineup-action="move-selected"
                                        data-source-select="roster"
                                        data-destination-select="<?php echo $targetId; ?>"
                                    >
                                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ASSIGN_TO_LINEUP'); ?>
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-secondary"
                                        data-lineup-action="move-selected"
                                        data-source-select="<?php echo $targetId; ?>"
                                        data-destination-select="roster"
                                    >
                                        <?php echo Text::_('COM_SPORTSMANAGEMENT_DELETE_FROM_LINEUP'); ?>
                                    </button>
                                </td>
                                <td>
                                    <strong><?php echo Text::_($position->text); ?></strong><br>
                                    <?php echo $this->lists['team_players' . $positionId]; ?>
                                </td>
                                <td class="text-center align-middle">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-secondary move-up"
                                        data-lineup-action="move-up"
                                        data-target-select="<?php echo $targetId; ?>"
                                    >
                                        <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?>
                                    </button>
                                    <br>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-secondary move-down"
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
