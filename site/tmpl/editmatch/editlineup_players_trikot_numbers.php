<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       editlineup_players_trikot_numbers.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
<fieldset class="adminform">
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUP_TRIKOT_NUMBER'); ?></legend>
    <?php if (isset($this->positions)) : ?>
        <?php foreach ($this->positions as $positionId => $pos) : ?>
            <fieldset class="adminform">
                <legend><?php echo Text::_($pos->text); ?></legend>
                <table>
                    <tbody>
                    <?php foreach ($this->starters[$positionId] ?? [] as $player) : ?>
                        <tr>
                            <td><?php echo $player->firstname; ?></td>
                            <td><?php echo $player->lastname; ?></td>
                            <td><?php echo $player->jerseynumber; ?></td>
                            <td>
                                <input
                                    type="text"
                                    name="trikot_number[<?php echo (int) $player->value; ?>]"
                                    value="<?php echo $player->trikot_number; ?>"
                                >
                            </td>
                            <td>
                                <?php
                                echo HTMLHelper::_(
                                    'select.genericlist',
                                    $this->lists['captain'],
                                    'captain[' . (int) $player->value . ']',
                                    'class="inputbox" size="1" style="background-color:#bbffff"',
                                    'value',
                                    'text',
                                    $player->captain
                                );
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </fieldset>
        <?php endforeach; ?>
    <?php endif; ?>
</fieldset>
