<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       editlineup_players.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
                <span class="red">
                    <?php if (!empty($this->preFillSuccess)) : ?>
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_PREFILL_DONE'); ?><br><br>
                    <?php endif; ?>
                </span>
            </td>
        </tr>
        <tr>
            <td style="text-align:center;vertical-align:middle;">
                <?php echo $this->lists['team_players'] ?? Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </td>
            <td style="text-align:center;vertical-align:top;">
                <table>
                    <tbody>
                    <?php foreach ($this->positions as $positionId => $position) : ?>
                        <?php $positionKey = (int) $positionId; ?>
                        <tr>
                            <td style="text-align:center;vertical-align:middle;">
                                <br>
                                <button
                                    type="button"
                                    data-list-source="roster"
                                    data-list-destination="position<?php echo $positionKey; ?>"
                                >
                                    <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_RIGHT'); ?>
                                </button>
                                <button
                                    type="button"
                                    data-list-source="position<?php echo $positionKey; ?>"
                                    data-list-destination="roster"
                                >
                                    <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LEFT'); ?>
                                </button>
                            </td>
                            <td>
                                <b><?php echo $escape(Text::_((string) ($position->text ?? ''))); ?></b><br>
                                <?php echo $this->lists['team_players' . $positionId]; ?>
                            </td>
                            <td style="text-align:center;vertical-align:middle;">
                                <br>
                                <button
                                    type="button"
                                    class="inputbox move-up"
                                    data-list-move-up="position<?php echo $positionKey; ?>"
                                >
                                    <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?>
                                </button><br>
                                <button
                                    type="button"
                                    class="inputbox move-down"
                                    data-list-move-down="position<?php echo $positionKey; ?>"
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
