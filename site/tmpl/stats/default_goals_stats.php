<?php
/**
 * Native Joomla 5/6 goals statistics layout.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage stats
 * @file       default_goals_stats.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$this->tips = [Text::_('COM_SPORTSMANAGEMENT_STATS_GOALS')];
echo $this->loadTemplate('jsm_tips');

$playedMatches = max(0, (int) ($this->totals->playedmatches ?? 0));
$totalMatches = max(0, (int) ($this->totals->totalmatches ?? 0));
$totalRounds = max(0, (int) ($this->totalrounds ?? 0));
$sumGoals = (int) ($this->totals->sumgoals ?? 0);
$homeGoals = (int) ($this->totals->homegoals ?? 0);
$awayGoals = (int) ($this->totals->guestgoals ?? 0);
$tableClass = trim((string) ($this->config['goals_table_class'] ?? 'table'));

$perMatch = static fn (int $value): float|int =>
    $playedMatches > 0 ? round($value / $playedMatches, 2) : 0;

$perMatchday = static fn (int $value): float|int =>
    $playedMatches > 0 && $totalRounds > 0
        ? round(($value / $playedMatches) * ($totalMatches / $totalRounds), 2)
        : 0;
?>
<div class="<?php echo $this->escape((string) $this->divclassrow); ?> table-responsive" id="goalsstats">
    <table class="<?php echo $this->escape($tableClass); ?>">
        <tbody>
            <tr class="sectiontableentry1">
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_GOALS_TOTAL'); ?>:</td>
                <td class="statvalue"><?php echo $sumGoals; ?></td>
            </tr>
            <tr class="sectiontableentry2">
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_GOALS_TOTAL_PER_MATCHDAY'); ?>:</td>
                <td class="statvalue"><?php echo $perMatchday($sumGoals); ?></td>
            </tr>
            <tr class="sectiontableentry1">
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_GOALS_TOTAL_PER_MATCH'); ?>:</td>
                <td class="statvalue"><?php echo $perMatch($sumGoals); ?></td>
            </tr>

            <?php if (!empty($this->config['home_away_stats'])) : ?>
                <tr class="sectiontableentry2">
                    <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_GOALS_HOME'); ?></td>
                    <td class="statvalue"><?php echo $homeGoals; ?></td>
                </tr>
                <tr class="sectiontableentry1">
                    <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_GOALS_HOME_PER_MATCHDAY'); ?>:</td>
                    <td class="statvalue"><?php echo $perMatchday($homeGoals); ?></td>
                </tr>
                <tr class="sectiontableentry2">
                    <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_GOALS_HOME_PER_MATCH'); ?>:</td>
                    <td class="statvalue"><?php echo $perMatch($homeGoals); ?></td>
                </tr>
                <tr class="sectiontableentry1">
                    <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_GOALS_AWAY'); ?></td>
                    <td class="statvalue"><?php echo $awayGoals; ?></td>
                </tr>
                <tr class="sectiontableentry2">
                    <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_GOALS_AWAY_PER_MATCHDAY'); ?>:</td>
                    <td class="statvalue"><?php echo $perMatchday($awayGoals); ?></td>
                </tr>
                <tr class="sectiontableentry1">
                    <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_GOALS_AWAY_PER_MATCH'); ?>:</td>
                    <td class="statvalue"><?php echo $perMatch($awayGoals); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
