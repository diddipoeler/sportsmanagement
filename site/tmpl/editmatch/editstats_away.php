<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       editstats_away.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$i = 0;
$j = 0;
?>

<fieldset class="adminform">
    <legend><?php echo $escape(Text::_((string) ($this->teams->team2 ?? ''))); ?></legend>
    <?php foreach ($this->positions as $position) : ?>
        <h3><?php echo $escape(Text::_((string) ($position->text ?? ''))); ?></h3>
        <table class="adminlist">
            <thead>
            <tr>
                <th style="text-align:left;width:10px;"></th>
                <th style="text-align:left;"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ES_NAME'); ?></th>
                <?php foreach ($this->stats as $stat) : ?>
                    <?php if (!$stat->getCalculated() && (int) $stat->position_id === (int) $position->posid) : ?>
                        <th style="text-align:center;"><?php echo $stat->getImage(); ?></th>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->awayRoster as $row) : ?>
                <?php if ((int) $row->tpid === 0 || (int) $row->position_id === (int) $position->posid) : ?>
                    <?php
                    $playerId = (int) $row->tpid;
                    $projectTeamId = (int) $row->projectteam_id;
                    ?>
                    <tr id="away-stat-row-<?php echo $i; ?>" class="statrow">
                        <td style="text-align:left;">
                            <input type="hidden" name="teamplayer_id[]" value="<?php echo $playerId; ?>">
                            <input type="hidden" name="projectteam_id[]" value="<?php echo $projectTeamId; ?>">
                            <input
                                type="checkbox"
                                class="statcheck"
                                id="away-stat-cb-<?php echo $i; ?>"
                                name="cid[]"
                                value="<?php echo $i; ?>"
                            >
                        </td>
                        <td style="text-align:left;width:200px;">
                            <?php
                            echo $escape(PersonNameFormatter::format(
                                null,
                                (string) ($row->firstname ?? ''),
                                (string) ($row->nickname ?? ''),
                                (string) ($row->lastname ?? ''),
                                0
                            ));
                            ?>
                        </td>
                        <?php foreach ($this->stats as $stat) : ?>
                            <?php if (!$stat->getCalculated() && (int) $stat->position_id === (int) $position->posid) : ?>
                                <?php
                                $statId = (int) $stat->id;
                                $value = $this->playerstats[$projectTeamId][$playerId][$statId] ?? '';
                                ?>
                                <td style="text-align:center;">
                                    <input
                                        type="text"
                                        size="3"
                                        class="inputbox stat"
                                        title="<?php echo $escape($stat->name ?? ''); ?>"
                                        name="stat<?php echo $playerId; ?>_<?php echo $statId; ?>"
                                        value="<?php echo $escape($value); ?>"
                                    >
                                </td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                    <?php $i++; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

    <?php if ($this->awayStaff !== []) : ?>
        <hr>
        <?php foreach ($this->staffpositions as $position) : ?>
            <h3><?php echo $escape(Text::_((string) ($position->text ?? ''))); ?></h3>
            <table class="adminlist">
                <thead>
                <tr>
                    <th style="text-align:left;"></th>
                    <th style="text-align:left;width:200px;"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ES_NAME'); ?></th>
                    <?php foreach ($this->stats as $stat) : ?>
                        <?php if (!$stat->getCalculated() && (int) $stat->position_id === (int) $position->posid) : ?>
                            <th style="text-align:center;"><?php echo $stat->getImage(); ?></th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($this->awayStaff as $row) : ?>
                    <?php if ((int) $row->team_staff_id === 0 || (int) $row->position_id === (int) $position->posid) : ?>
                        <?php
                        $staffId = (int) $row->team_staff_id;
                        $projectTeamId = (int) $row->projectteam_id;
                        ?>
                        <tr id="away-staff-stat-row-<?php echo $j; ?>" class="staffstatrow">
                            <td style="text-align:left;">
                                <input type="hidden" name="team_staff_id[]" value="<?php echo $staffId; ?>">
                                <input type="hidden" name="sprojectteam_id[]" value="<?php echo $projectTeamId; ?>">
                                <input
                                    type="checkbox"
                                    class="staffstatcheck"
                                    id="away-staff-stat-cb-<?php echo $j; ?>"
                                    name="staffcid[]"
                                    value="<?php echo $j; ?>"
                                >
                            </td>
                            <td style="text-align:left;width:200px;">
                                <?php
                                echo $escape(PersonNameFormatter::format(
                                    null,
                                    (string) ($row->firstname ?? ''),
                                    (string) ($row->nickname ?? ''),
                                    (string) ($row->lastname ?? ''),
                                    0
                                ));
                                ?>
                            </td>
                            <?php foreach ($this->stats as $stat) : ?>
                                <?php if (!$stat->getCalculated() && (int) $stat->position_id === (int) $position->posid) : ?>
                                    <?php
                                    $statId = (int) $stat->id;
                                    $value = $this->staffstats[$projectTeamId][$staffId][$statId] ?? 0;
                                    ?>
                                    <td style="text-align:center;">
                                        <input
                                            type="text"
                                            size="3"
                                            class="inputbox staffstat"
                                            title="<?php echo $escape($stat->name ?? ''); ?>"
                                            name="staffstat<?php echo $staffId; ?>_<?php echo $statId; ?>"
                                            value="<?php echo $escape($value); ?>"
                                        >
                                    </td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                        <?php $j++; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php endif; ?>
</fieldset>
