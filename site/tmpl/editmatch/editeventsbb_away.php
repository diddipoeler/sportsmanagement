<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       editeventsbb_away.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<fieldset class="adminform">
    <legend><?php echo $escape($this->teams->team2 ?? ''); ?></legend>
    <table class="adminlist">
        <thead>
        <tr>
            <th style="text-align: left; width: 10px;"></th>
            <th style="text-align:left;"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EEBB_PERSON'); ?></th>
            <?php foreach ($this->events as $ev) : ?>
                <th style="text-align: center;">
                    <?php
                    $eventText = Text::_((string) ($ev->text ?? ''));
                    $iconFileName = (string) ($ev->icon ?? '');

                    if ($iconFileName !== '' && File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . $iconFileName)) {
                        echo HTMLHelper::_('image', $iconFileName, $eventText, ['title' => $eventText]);
                    } else {
                        echo $eventText;
                    }
                    ?>
                </th>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php
        $model = $this->getModel();
        $teap = 0;

        for ($i = 0, $n = count($this->awayRoster); $i < $n; $i++) :
            $row = $this->awayRoster[$i];

            if ((int) ($row->value ?? 0) === 0) {
                continue;
            }

            $checkboxId = 'cb_a' . $i;
            $personName = PersonNameFormatter::format(
                null,
                (string) ($row->firstname ?? ''),
                (string) ($row->nickname ?? ''),
                (string) ($row->lastname ?? ''),
                14
            );
            ?>
            <tr id="row<?php echo $i; ?>">
                <td style="text-align: left;">
                    <input type="hidden" name="player_id_a_<?php echo $i; ?>" value="<?php echo (int) ($row->value ?? 0); ?>">
                    <input type="hidden" name="team_id_a_<?php echo $i; ?>" value="<?php echo (int) ($row->projectteam_id ?? 0); ?>">
                    <input
                        type="checkbox"
                        id="<?php echo $checkboxId; ?>"
                        name="cid_a<?php echo $i; ?>"
                        value="cb_a"
                        class="event-player-check"
                    >
                </td>
                <td style="text-align: left;">
                    <?php echo $escape('(' . Text::_((string) ($row->positionname ?? '')) . ') - ' . $personName); ?>
                </td>
                <?php
                $teap = 0;

                foreach ($this->events as $ev) :
                    $teap++;
                    $eventRows = $model->getPlayerEventsbb(
                        (int) ($row->value ?? 0),
                        (int) ($ev->value ?? 0),
                        (int) ($this->item->id ?? 0)
                    );
                    $playerEvent = $eventRows[0] ?? null;
                    $eventSum = (float) ($playerEvent->event_sum ?? 0) > 0 ? (string) $playerEvent->event_sum : '';
                    $eventTime = (float) ($playerEvent->event_time ?? 0) > 0 ? (string) $playerEvent->event_time : '';
                    $notice = (string) ($playerEvent->notice ?? '');
                    ?>
                    <td style="text-align: center;">
                        <input
                            type="hidden"
                            name="event_type_id_a_<?php echo $i . '_' . $teap; ?>"
                            value="<?php echo (int) ($ev->value ?? 0); ?>"
                        >
                        <input
                            type="hidden"
                            name="event_id_a_<?php echo $i . '_' . $teap; ?>"
                            value="<?php echo (int) ($playerEvent->id ?? 0); ?>"
                        >
                        <input
                            type="text"
                            size="1"
                            class="inputbox"
                            name="event_sum_a_<?php echo $i . '_' . $teap; ?>"
                            value="<?php echo $escape($eventSum); ?>"
                            title="<?php echo $escape(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_VALUE_SUM')); ?>"
                            data-player-checkbox="<?php echo $checkboxId; ?>"
                        >
                        <input
                            type="text"
                            size="2"
                            class="inputbox"
                            name="event_time_a_<?php echo $i . '_' . $teap; ?>"
                            value="<?php echo $escape($eventTime); ?>"
                            title="<?php echo $escape(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_TIME')); ?>"
                            data-player-checkbox="<?php echo $checkboxId; ?>"
                        >
                        <input
                            type="text"
                            size="2"
                            class="inputbox"
                            name="notice_a_<?php echo $i . '_' . $teap; ?>"
                            value="<?php echo $escape($notice); ?>"
                            title="<?php echo $escape(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_MATCH_NOTICE')); ?>"
                            data-player-checkbox="<?php echo $checkboxId; ?>"
                        >
                        &nbsp;&nbsp;
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endfor; ?>
        <input type="hidden" name="total_a_players" value="<?php echo $i; ?>">
        <input type="hidden" name="teap" value="<?php echo $teap; ?>">
        </tbody>
    </table>
</fieldset>
