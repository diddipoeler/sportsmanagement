<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       editevents.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<form action="<?php echo $escape($this->uri->toString()); ?>" id="editevents" method="post" name="editevents">
    <button type="button" data-editmatch-submit-task="editmatch.cancel">
        <?php echo Text::_('JCANCEL'); ?>
    </button>
    <div id="gamesevents">
        <div id="UserError"></div>
        <div id="UserErrorWrapper"></div>

        <div id="ajaxresponse"></div>
        <fieldset>
            <div class="configuration">
                <?php echo $escape(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_TITLE', $this->teams->team1, $this->teams->team2)); ?>
            </div>
        </fieldset>

        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_DESCR'); ?></legend>
            <table id="table-event" class="adminlist">
                <thead>
                <tr>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_TEAM'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_PLAYER'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_EVENT'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_VALUE_SUM'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_TIME'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_MATCH_NOTICE'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_EVENT_ACTION'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $k = 0;

                if (isset($this->matchevents)) {
                    foreach ($this->matchevents as $event) {
                        if ((int) ($event->event_type_id ?? 0) !== 0) {
                            $eventId = (int) ($event->id ?? 0);
                            $playerName = preg_replace('/\'\' /', '', (string) ($event->player1 ?? '')) ?? '';
                            $notice = (string) ($event->notice ?? '');
                            $noticeLength = function_exists('mb_strlen') ? mb_strlen($notice) : strlen($notice);
                            $displayNotice = $notice;

                            if ($noticeLength > 20) {
                                $displayNotice = (function_exists('mb_substr') ? mb_substr($notice, 0, 17) : substr($notice, 0, 17)) . '...';
                            }
                            ?>
                            <tr id="rowevent-<?php echo $eventId; ?>" class="row<?php echo $k; ?>">
                                <td><?php echo $escape($event->team ?? ''); ?></td>
                                <td><?php echo $escape($playerName); ?></td>
                                <td style="text-align:center;"><?php echo Text::_((string) ($event->event ?? '')); ?></td>
                                <td style="text-align:center;"><?php echo $escape($event->event_sum ?? ''); ?></td>
                                <td style="text-align:center;"><?php echo $escape($event->event_time ?? ''); ?></td>
                                <td title="" class="hasTip"><?php echo $escape($displayNotice); ?></td>
                                <td style="text-align:center;">
                                    <input
                                        id="deleteevent-<?php echo $eventId; ?>"
                                        type="button"
                                        class="inputbox button-delete-event"
                                        value="<?php echo Text::_('JACTION_DELETE'); ?>"
                                    >
                                </td>
                            </tr>
                            <?php
                        }

                        $k = 1 - $k;
                    }
                }
                ?>
                <tr id="row-new">
                    <td><?php echo $this->lists['teams']; ?></td>
                    <td id="cell-player">&nbsp;</td>
                    <td><?php echo $this->lists['events']; ?></td>
                    <td style="text-align:center;">
                        <input type="text" size="3" value="" id="event_sum" name="event_sum" class="inputbox">
                    </td>
                    <td style="text-align:center;">
                        <input type="text" size="3" value="" id="event_time" name="event_time" class="inputbox">
                    </td>
                    <td style="text-align:center;">
                        <input type="text" size="20" value="" id="notice" name="notice" class="inputbox">
                    </td>
                    <td style="text-align:center;">
                        <input id="save-new-event" type="button" class="inputbox button-save-event" value="<?php echo Text::_('JSAVE'); ?>">
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>

        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_LIVE_COMMENTARY_DESCR'); ?></legend>
            <table class="adminlist" id="table-commentary">
                <thead>
                <tr>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_LIVE_TYPE'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_TIME'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_LIVE_NOTES'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_EVENT_ACTION'); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr id="rowcomment-new">
                    <td>
                        <select name="ctype" id="ctype" class="inputbox select-commenttype">
                            <option value="1"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_LIVE_TYPE_1'); ?></option>
                            <option value="2"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_LIVE_TYPE_2'); ?></option>
                        </select>
                    </td>
                    <td style="text-align:center;">
                        <input type="text" size="3" value="" id="c_event_time" name="c_event_time" class="inputbox">
                    </td>
                    <td style="text-align:center;">
                        <textarea rows="2" cols="70" id="notes" name="notes"></textarea>
                    </td>
                    <td style="text-align:center;">
                        <input
                            id="save-new-comment"
                            type="button"
                            class="inputbox button-save-comment"
                            value="<?php echo Text::_('JSAVE'); ?>"
                        >
                    </td>
                </tr>
                <?php
                $k = 0;

                if (isset($this->matchcommentary)) {
                    foreach ($this->matchcommentary as $event) {
                        $commentId = (int) ($event->id ?? 0);
                        ?>
                        <tr id="rowcomment-<?php echo $commentId; ?>" class="row<?php echo $k; ?>">
                            <td>
                                <?php
                                switch ((int) ($event->type ?? 0)) {
                                    case 2:
                                        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_LIVE_TYPE_2');
                                        break;
                                    case 1:
                                        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_LIVE_TYPE_1');
                                        break;
                                }
                                ?>
                            </td>
                            <td style="text-align:center;"><?php echo $escape($event->event_time ?? ''); ?></td>
                            <td title="" class="hasTip" style="width: 500px;"><?php echo $escape($event->notes ?? ''); ?></td>
                            <td style="text-align:center;">
                                <input
                                    id="deletecomment-<?php echo $commentId; ?>"
                                    type="button"
                                    class="inputbox button-delete-commentary"
                                    value="<?php echo Text::_('JACTION_DELETE'); ?>"
                                >
                            </td>
                        </tr>
                        <?php
                        $k = 1 - $k;
                    }
                }
                ?>
                </tbody>
            </table>
        </fieldset>
    </div>
    <div style="clear: both"></div>
    <input type="hidden" name="task" value="">
    <input type="hidden" name="view" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
