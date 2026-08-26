<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       edit_scoredetails.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

$useLegs = (int) ($this->projectws->use_legs ?? 0) === 1;
$alternativeLegs = (string) ($this->table_config['alternative_legs'] ?? '');
?>
<fieldset class="adminform">
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_SD'); ?></legend>
    <table class="admintable">
        <thead>
        <tr>
            <th></th>
            <th><?php echo $this->match->hometeam; ?></th>
            <th></th>
            <th><?php echo $this->match->awayteam; ?></th>
        </tr>
        </thead>
        <tbody>
        <?php if ($useLegs) : ?>
            <tr>
                <td>
                    <?php echo $alternativeLegs === ''
                        ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_SD_SETS')
                        : $alternativeLegs; ?>:
                </td>
                <td>
                    <input type="text" name="team1_legs" value="<?php echo $this->match->team1_legs; ?>" size="3" tabindex="100" class="inputbox">
                </td>
                <td style="text-align:center;">:</td>
                <td>
                    <input type="text" name="team2_legs" value="<?php echo $this->match->team2_legs; ?>" size="3" tabindex="101" class="inputbox">
                </td>
            </tr>
        <?php endif; ?>
        <tr>
            <td class="key">
                <label><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_SD_BONUS'); ?></label>
            </td>
            <td>
                <input type="text" name="team1_bonus" value="<?php echo $this->match->team1_bonus; ?>" size="3" class="inputbox">
            </td>
            <td style="text-align:center;">:</td>
            <td>
                <input type="text" name="team2_bonus" value="<?php echo $this->match->team2_bonus; ?>" size="3" class="inputbox">
            </td>
        </tr>
        <tr>
            <td class="key">
                <label for="match_result_detail"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_SD_SCORE_NOTICE'); ?></label>
            </td>
            <td colspan="3">
                <input
                    type="text"
                    id="match_result_detail"
                    name="match_result_detail"
                    value="<?php echo $this->match->match_result_detail; ?>"
                    size="40"
                    class="inputbox"
                >
            </td>
        </tr>
        </tbody>
    </table>
</fieldset>
