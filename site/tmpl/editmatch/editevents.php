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

use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$this->getDocument()->getWebAssetManager()->useScript('jquery');

$savenewcomment = [
    $this->match->id,
    $this->eventsprojecttime,
    "'" . Route::_(Uri::base() . 'index.php?option=com_sportsmanagement') . "'",
];
$baseurl = "'" . Route::_(Uri::base() . 'index.php?option=com_sportsmanagement') . "'";

$buildRoster = function (array $players): array {
    $roster = [];

    foreach ($players as $player) {
        $name = PersonNameFormatter::format(
            null,
            (string) $player->firstname,
            (string) $player->nickname,
            (string) $player->lastname,
            $this->default_name_format
        );

        if ($this->default_name_dropdown_list_order === 'position') {
            $name = '(' . Text::_($player->positionname) . ') - ' . $name;
        }

        $roster[] = [
            'value' => $player->value,
            'text' => $name,
        ];
    }

    return $roster;
};

$homeRoster = $buildRoster((array) $this->rosters['home']);
$awayRoster = $buildRoster((array) $this->rosters['away']);
?>
<script>
    var homeroster = <?php echo json_encode($homeRoster, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var awayroster = <?php echo json_encode($awayRoster, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var rosters = [homeroster, awayroster];

    document.addEventListener('DOMContentLoaded', function () {
        updatePlayerSelect();

        const teamSelect = document.getElementById('team_id');

        if (teamSelect) {
            teamSelect.addEventListener('change', updatePlayerSelect);
        }
    });
</script>

<form action="<?php echo $this->uri->toString(); ?>" id="editevents" method="post" name="editevents">
    <button type="button" onclick="Joomla.submitform('editmatch.cancel', this.form);">
        <?php echo Text::_('JCANCEL'); ?>
    </button>
    <div id="gamesevents">
        <div id="UserError"></div>
        <div id="UserErrorWrapper"></div>

        <div id="ajaxresponse"></div>
        <fieldset>
            <div class="configuration">
                <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_TITLE', $this->teams->team1, $this->teams->team2); ?>
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
                        if ($event->event_type_id != 0) {
                            ?>
                            <tr id="rowevent-<?php echo $event->id; ?>" class="<?php echo "row$k"; ?>">
                                <td><?php echo $event->team; ?></td>
                                <td>
                                    <?php echo preg_replace('/\'\' /', '', $event->player1); ?>
                                </td>
                                <td style="text-align:center;"><?php echo Text::_($event->event); ?></td>
                                <td style="text-align:center;"><?php echo $event->event_sum; ?></td>
                                <td style="text-align:center;"><?php echo $event->event_time; ?></td>
                                <td title="" class="hasTip">
                                    <?php echo strlen($event->notice) > 20 ? substr($event->notice, 0, 17) . '...' : $event->notice; ?>
                                </td>
                                <td style="text-align:center;">
                                    <input
                                        id="deleteevent-<?php echo $event->id; ?>"
                                        type="button"
                                        class="inputbox button-delete-event"
                                        onclick="deleteevent(<?php echo $event->id; ?>)"
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
                            onclick="save_new_comment(<?php echo implode(',', $savenewcomment); ?>)"
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
                        ?>
                        <tr id="rowcomment-<?php echo $event->id; ?>" class="<?php echo "row$k"; ?>">
                            <td>
                                <?php
                                switch ($event->type) {
                                    case 2:
                                        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_LIVE_TYPE_2');
                                        break;
                                    case 1:
                                        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_LIVE_TYPE_1');
                                        break;
                                }
                                ?>
                            </td>
                            <td style="text-align:center;">
                                <?php echo $event->event_time; ?>
                            </td>
                            <td title="" class="hasTip" style="width: 500px;">
                                <?php echo $event->notes; ?>
                            </td>
                            <td style="text-align:center;">
                                <input
                                    onclick="button_delete_commentary(<?php echo $event->id; ?>,<?php echo $baseurl; ?>)"
                                    id="deletecomment-<?php echo $event->id; ?>"
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
