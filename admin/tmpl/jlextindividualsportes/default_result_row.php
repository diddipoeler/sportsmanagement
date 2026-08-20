<?php
/** Row partial for the Joomla 5/6 individual-sport result editor. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$checked = HTMLHelper::_('grid.checkedout', $item, $count_i, 'id');
$rowNumberClass = (int) ($item->cancel ?? 0) > 0
    ? 'text-center jsm-cancelled-row-number'
    : 'text-center';
$matchNumber = $smallBore ? $item->match_number : ($item->match_number ?: ($count_i + 1));
$publishPrefix = $smallBore ? 'jlextindividualsportes.' : 'matches.';
$actionPrefix = $smallBore ? 'jlextindividualsportes' : 'matches';
?>
<tr class="row<?php echo $count_i % 2; ?>" data-jsm-result-row>
    <td class="<?php echo $rowNumberClass; ?>"><?php echo $this->pagination->getRowOffset($count_i); ?></td>
    <td class="text-center"><?php echo $checked; ?></td>
    <td class="text-center">
        <input type="text" name="match_number<?php echo $item->id; ?>" value="<?php echo $matchNumber; ?>" size="6" tabindex="1" class="inputbox">
    </td>
    <td class="text-center">
        <?php if ($smallBore) : ?>
            <?php echo Text::_('COM_SPORTSMANAGEMENT_' . $item->match_type); ?>
        <?php else : ?>
            <?php
            $matchTypeOptions = [
                HTMLHelper::_('select.option', 'SINGLE', Text::_('COM_SPORTSMANAGEMENT_PERSON_SINGLE')),
                HTMLHelper::_('select.option', 'DOUBLE', Text::_('COM_SPORTSMANAGEMENT_PERSON_DOUBLE')),
            ];
            echo HTMLHelper::_('select.genericlist', $matchTypeOptions, 'match_type' . $item->id, 'class="inputbox"', 'value', 'text', $item->match_type);
            ?>
        <?php endif; ?>
    </td>
    <td class="text-nowrap">
        <?php if ($item->match_type === 'SINGLE') : ?>
            <?php $homeClass = 'inputbox select-hometeam' . ($item->teamplayer1_id == 0 ? ' jsm-player-missing' : ''); ?>
            <?php echo HTMLHelper::_('select.genericlist', $this->lists['homeplayer'], 'teamplayer1_id' . $item->id, 'class="' . $homeClass . '" size="1"', 'value', 'text', $item->teamplayer1_id); ?>
        <?php elseif ($item->match_type === 'DOUBLE') : ?>
            <?php $homeClass = 'inputbox select-hometeam' . ($item->double_team1_player1 == 0 ? ' jsm-player-missing' : ''); ?>
            <?php echo HTMLHelper::_('select.genericlist', $this->lists['homeplayer'], 'double_team1_player1' . $item->id, 'class="' . $homeClass . '" size="1"', 'value', 'text', $item->double_team1_player1); ?>
            <br>
            <?php $homeClass = 'inputbox select-hometeam' . ($item->double_team1_player2 == 0 ? ' jsm-player-missing' : ''); ?>
            <?php echo HTMLHelper::_('select.genericlist', $this->lists['homeplayer'], 'double_team1_player2' . $item->id, 'class="' . $homeClass . '" size="1"', 'value', 'text', $item->double_team1_player2); ?>
        <?php endif; ?>
    </td>
    <td class="text-nowrap">
        <?php if ($item->match_type === 'SINGLE') : ?>
            <?php $awayClass = 'inputbox select-awayteam' . ($item->teamplayer2_id == 0 ? ' jsm-player-missing' : ''); ?>
            <?php echo HTMLHelper::_('select.genericlist', $this->lists['awayplayer'], 'teamplayer2_id' . $item->id, 'class="' . $awayClass . '" size="1"', 'value', 'text', $item->teamplayer2_id); ?>
        <?php elseif ($item->match_type === 'DOUBLE') : ?>
            <?php $awayClass = 'inputbox select-awayteam' . ($item->double_team2_player1 == 0 ? ' jsm-player-missing' : ''); ?>
            <?php echo HTMLHelper::_('select.genericlist', $this->lists['awayplayer'], 'double_team2_player1' . $item->id, 'class="' . $awayClass . '" size="1"', 'value', 'text', $item->double_team2_player1); ?>
            <br>
            <?php $awayClass = 'inputbox select-awayteam' . ($item->double_team2_player2 == 0 ? ' jsm-player-missing' : ''); ?>
            <?php echo HTMLHelper::_('select.genericlist', $this->lists['awayplayer'], 'double_team2_player2' . $item->id, 'class="' . $awayClass . '" size="1"', 'value', 'text', $item->double_team2_player2); ?>
        <?php endif; ?>
    </td>
    <td class="jsm-result-cell">
        <?php
        $decisionClass = (int) ($item->alt_decision ?? 0) === 1 ? ' subsequentdecision' : '';
        $decisionTitle = (int) ($item->alt_decision ?? 0) === 1 ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SUB_DECISION') : '';
        ?>
        <input type="text" name="team1_result<?php echo $item->id; ?>" value="<?php echo $item->team1_result; ?>" size="2" tabindex="4" class="inputbox<?php echo $decisionClass; ?>" title="<?php echo $decisionTitle; ?>"> :
        <input type="text" name="team2_result<?php echo $item->id; ?>" value="<?php echo $item->team2_result; ?>" size="2" tabindex="4" class="inputbox<?php echo $decisionClass; ?>" title="<?php echo $decisionTitle; ?>">

        <?php if (!$smallBore) : ?>
            <?php
            $partResults1 = isset($item->team1_result_split) && $item->team1_result_split !== null ? explode(';', $item->team1_result_split) : [];
            $partResults2 = isset($item->team2_result_split) && $item->team2_result_split !== null ? explode(';', $item->team2_result_split) : [];
            ?>
            <table>
                <?php for ($part = 0; $part < (int) $this->projectws->game_parts; $part++) : ?>
                    <tr>
                        <td>
                            <?php echo $part + 1; ?>.:
                            <input type="text" name="team1_result_split<?php echo $item->id; ?>[]" value="<?php echo $partResults1[$part] ?? ''; ?>" size="3" tabindex="1" class="inputbox jsm-part-result">
                            <input type="text" name="team2_result_split<?php echo $item->id; ?>[]" value="<?php echo $partResults2[$part] ?? ''; ?>" size="3" tabindex="1" class="inputbox jsm-part-result">
                        </td>
                    </tr>
                <?php endfor; ?>
            </table>
            <?php if ((int) $this->projectws->allow_add_time === 1) : ?>
                OT:
                <input type="text" name="team1_result_ot<?php echo $item->id; ?>" value="<?php echo $item->team1_result_ot ?? ''; ?>" size="3" tabindex="1" class="inputbox jsm-part-result"> :
                <input type="text" name="team2_result_ot<?php echo $item->id; ?>" value="<?php echo $item->team2_result_ot ?? ''; ?>" size="3" tabindex="1" class="inputbox jsm-part-result">
                <br>
                SO:
                <input type="text" name="team1_result_so<?php echo $item->id; ?>" value="<?php echo $item->team1_result_so ?? ''; ?>" size="3" tabindex="1" class="inputbox jsm-part-result"> :
                <input type="text" name="team2_result_so<?php echo $item->id; ?>" value="<?php echo $item->team2_result_so ?? ''; ?>" size="3" tabindex="1" class="inputbox jsm-part-result">
                <br>
            <?php endif; ?>
        <?php endif; ?>
    </td>
    <?php if ($this->projectws->allow_add_time) : ?>
        <td class="text-nowrap">
            <?php echo HTMLHelper::_('select.genericlist', $this->lists['match_result_type'], 'match_result_type' . $item->id, 'class="inputbox" size="1"', 'value', 'text', $item->match_result_type); ?>
        </td>
    <?php endif; ?>
    <td class="text-center">
        <div class="btn-group">
            <?php echo HTMLHelper::_('jgrid.published', $item->published, $count_i, $publishPrefix, $canChange, 'cb'); ?>
            <?php if ($canChange) : ?>
                <?php HTMLHelper::_('actionsdropdown.' . ((int) $item->published === 2 ? 'un' : '') . 'archive', 'cb' . $count_i, $actionPrefix); ?>
                <?php HTMLHelper::_('actionsdropdown.' . ((int) $item->published === -2 ? 'un' : '') . 'trash', 'cb' . $count_i, $actionPrefix); ?>
                <?php echo HTMLHelper::_('actionsdropdown.render', $this->escape($item->id)); ?>
            <?php endif; ?>
        </div>
    </td>
    <td class="text-center"><?php echo $item->id; ?></td>
</tr>
