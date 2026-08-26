<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       editlineup_staff.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
            <td style="text-align:center;vertical-align:middle;">
                <?php echo $this->lists['team_staffs'] ?? Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </td>
            <td style="text-align:center;vertical-align:top;">
                <table>
                    <tbody>
                    <?php foreach ($this->staffpositions as $positionId => $position) : ?>
                        <?php $positionKey = (int) $positionId; ?>
                        <tr>
                            <td style="text-align:center;vertical-align:middle;">
                                <br>
                                <button
                                    type="button"
                                    data-list-source="staff"
                                    data-list-destination="staffposition<?php echo $positionKey; ?>"
                                >
                                    <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_RIGHT'); ?>
                                </button>
                                <button
                                    type="button"
                                    data-list-source="staffposition<?php echo $positionKey; ?>"
                                    data-list-destination="staff"
                                >
                                    <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LEFT'); ?>
                                </button>
                            </td>
                            <td>
                                <b><?php echo $escape(Text::_((string) ($position->text ?? ''))); ?></b><br>
                                <?php echo $this->lists['team_staffs' . $positionId]; ?>
                            </td>
                            <td style="text-align:center;vertical-align:middle;">
                                <br>
                                <button
                                    type="button"
                                    class="inputbox smove-up"
                                    data-list-move-up="staffposition<?php echo $positionKey; ?>"
                                >
                                    <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?>
                                </button><br>
                                <button
                                    type="button"
                                    class="inputbox smove-down"
                                    data-list-move-down="staffposition<?php echo $positionKey; ?>"
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
