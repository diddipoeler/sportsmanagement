<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       editlineup_substitutions.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>

<!-- SUBSTITUTIONS START -->
<div id="io">
    <div id="ajaxresponse"></div>
    <fieldset class="adminform">
        <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUSUBST_SUBST'); ?></legend>
        <table class="adminlist" id="table-substitutions">
            <thead>
            <tr>
                <th>
                    <?php
                    echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/out.png', Text::_('Out'));
                    echo '&nbsp;' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUSUBST_OUT');
                    ?>
                </th>
                <th>
                    <?php
                    echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUSUBST_IN') . '&nbsp;';
                    echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/in.png', Text::_('In'));
                    ?>
                </th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUSUBST_POS'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUSUBST_TIME'); ?></th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $k = 0;

            foreach ($this->substitutions as $substitution) :
                ?>
                <tr id="sub-<?php echo (int) $substitution->id; ?>" class="row<?php echo $k; ?>">
                    <td>
                        <?php
                        if ((int) $substitution->came_in === 2) {
                            echo PersonNameFormatter::format(
                                null,
                                (string) $substitution->firstname,
                                (string) $substitution->nickname,
                                (string) $substitution->lastname,
                                0
                            );
                        } else {
                            echo PersonNameFormatter::format(
                                null,
                                (string) $substitution->out_firstname,
                                (string) $substitution->out_nickname,
                                (string) $substitution->out_lastname,
                                0
                            );
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if ((int) $substitution->came_in === 1) {
                            echo PersonNameFormatter::format(
                                null,
                                (string) $substitution->firstname,
                                (string) $substitution->nickname,
                                (string) $substitution->lastname,
                                0
                            );
                        }
                        ?>
                    </td>
                    <td><?php echo Text::_((string) $substitution->in_position); ?></td>
                    <td>
                        <?php echo $substitution->in_out_time !== null && (int) $substitution->in_out_time > 0
                            ? $substitution->in_out_time
                            : '--'; ?>
                    </td>
                    <td>
                        <input
                            onclick="deletesubst(<?php echo (int) $substitution->id; ?>)"
                            id="deletesubst-<?php echo (int) $substitution->id; ?>"
                            type="button"
                            class="inputbox button-delete-subst"
                            value="<?php echo Text::_('JACTION_DELETE'); ?>"
                        >
                    </td>
                </tr>
                <?php
                $k = 1 - $k;
            endforeach;
            ?>
            <tr id="row-new">
                <td><?php echo HTMLHelper::_('select.genericlist', $this->playersoptionsout, 'out', 'class="inputbox player-out"'); ?></td>
                <td><?php echo HTMLHelper::_('select.genericlist', $this->playersoptionsin, 'in', 'class="inputbox player-in"'); ?></td>
                <td>
                    <?php echo $this->lists['projectpositions'] ?? Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                </td>
                <td>
                    <input type="text" size="3" id="in_out_time" name="in_out_time" class="inputbox">
                </td>
                <td>
                    <input
                        id="save-new-subst"
                        onclick="save_new_subst()"
                        type="button"
                        class="inputbox button-save-subst"
                        value="<?php echo Text::_('JSAVE'); ?>"
                    >
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>
</div>
<!-- SUBSTITUTIONS END -->
