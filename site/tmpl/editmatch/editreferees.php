<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       editreferees.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div id="lineup">
    <form action="<?php echo $escape($this->uri->toString()); ?>" id="editreferees" method="post" name="editreferees">
        <fieldset>
            <div class="fltrt">
                <button
                    type="button"
                    data-submit-task="editmatch.saveReferees"
                    data-select-all-before-submit="select.position-starters option"
                >
                    <?php echo Text::_('JSAVE'); ?>
                </button>
                <button type="button" data-submit-task="editmatch.cancel">
                    <?php echo Text::_('JCANCEL'); ?>
                </button>
            </div>
            <div class="configuration">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_TITLE'); ?>
            </div>
        </fieldset>
        <div class="clear"></div>
        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_DESCR'); ?></legend>
            <table class="adminlist">
                <thead>
                <tr>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_REFS'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_ASSIGNED'); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="text-align:center;">
                        <?php echo $this->lists['team_referees']; ?>
                    </td>
                    <td style="text-align:center;vertical-align:top;">
                        <table>
                            <tbody>
                            <?php foreach ($this->positions as $key => $position) : ?>
                                <?php $positionKey = (int) $key; ?>
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
                                        <?php echo $this->lists['team_referees' . $key]; ?>
                                    </td>
                                    <td style="text-align:center;vertical-align:middle;">
                                        <br>
                                        <button
                                            type="button"
                                            id="moveup-<?php echo $positionKey; ?>"
                                            class="inputbox move-up"
                                            data-list-move-up="position<?php echo $positionKey; ?>"
                                        >
                                            <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?>
                                        </button><br>
                                        <button
                                            type="button"
                                            id="movedown-<?php echo $positionKey; ?>"
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
        <br>
        <br>
        <input type="hidden" name="task" value="">
        <input type="hidden" name="view" value="">
        <input type="hidden" name="close" id="close" value="0">
        <input type="hidden" name="id" value="<?php echo (int) ($this->match->id ?? 0); ?>">
        <input type="hidden" name="project_id" value="<?php echo (int) $this->project_id; ?>">
        <input type="hidden" name="changes_check" value="0" id="changes_check">
        <input type="hidden" name="p" value="<?php echo (int) EditmatchModel::$projectid; ?>">
        <input type="hidden" name="r" value="<?php echo (int) EditmatchModel::$roundid; ?>">
        <input type="hidden" name="s" value="<?php echo (int) EditmatchModel::$seasonid; ?>">
        <input type="hidden" name="division" value="<?php echo (int) EditmatchModel::$divisionid; ?>">
        <input type="hidden" name="cfg_which_database" value="<?php echo (int) EditmatchModel::$cfg_which_database; ?>">
        <input type="hidden" name="positionscount" value="<?php echo count($this->positions); ?>" id="positioncount">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
