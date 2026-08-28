<?php
/**
 * Joomla 5/6 head-to-head statistics for the next-match view.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$value = static fn (?object $row, string $property, mixed $default = 0): mixed => $row->{$property} ?? $default;
$record = static fn (?object $row, string $suffix = ''): string => sprintf(
    '%s/%s/%s',
    $row->{'cnt_won' . $suffix} ?? 0,
    $row->{'cnt_draw' . $suffix} ?? 0,
    $row->{'cnt_lost' . $suffix} ?? 0
);
$awayRecord = static fn (?object $row): string => sprintf(
    '%s/%s/%s',
    ($row->cnt_won ?? 0) - ($row->cnt_won_home ?? 0),
    ($row->cnt_draw ?? 0) - ($row->cnt_draw_home ?? 0),
    ($row->cnt_lost ?? 0) - ($row->cnt_lost_home ?? 0)
);

$databaseSelector = $this->input->getInt('cfg_which_database', 0);
$seasonId = $this->input->getInt('s', 0);
$homeName = (string) ($this->teams[0]->name ?? Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_UNKNOWNTEAM'));
$awayName = (string) ($this->teams[1]->name ?? Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_UNKNOWNTEAM'));
$homeRank = is_object($this->homeranked) ? $this->homeranked : (object) [];
$awayRank = is_object($this->awayranked) ? $this->awayranked : (object) [];
$chances = is_array($this->chances) ? $this->chances : ['', ''];

$statLink = static function (?object $stat) use ($databaseSelector, $seasonId): string {
    if (!$stat) {
        return '----';
    }

    $link = SiteRouteHelper::view('matchreport', [
        'cfg_which_database' => $databaseSelector,
        's' => $seasonId,
        'p' => (string) ($stat->project_slug ?? ''),
        'mid' => (string) ($stat->match_slug ?? ''),
    ]);
    $label = sprintf(
        '%s - %s %s:%s',
        (string) ($stat->hometeam ?? ''),
        (string) ($stat->awayteam ?? ''),
        (string) ($stat->homegoals ?? ''),
        (string) ($stat->awaygoals ?? '')
    );

    return HTMLHelper::link($link, htmlspecialchars($label, ENT_QUOTES, 'UTF-8'));
};

$this->notes = [Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_H2H')];
?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="nextmatch-stats">
    <?php echo $this->loadTemplate('jsm_notes'); ?>

    <table class="table" id="nextmatch-default-stats">
        <thead>
        <tr class="text-center">
            <th class="h2h" style="width:33%"><?php echo $escape($homeName); ?></th>
            <th class="h2h" style="width:33%"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_STATS'); ?></th>
            <th class="h2h"><?php echo $escape($awayName); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($this->config['show_chances'])) : ?>
            <tr class="sectiontableentry1">
                <td class="valueleft"><?php echo $escape($chances[0] !== '' ? $chances[0] . '%' : ''); ?></td>
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_CHANCES'); ?></td>
                <td class="valueright"><?php echo $escape($chances[1] !== '' ? $chances[1] . '%' : ''); ?></td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->config['show_current_rank'])) : ?>
            <tr class="sectiontableentry2">
                <td class="valueleft"><?php echo $escape($value($homeRank, 'rank', '')); ?></td>
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_CURRENT_RANK'); ?></td>
                <td class="valueright"><?php echo $escape($value($awayRank, 'rank', '')); ?></td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->config['show_match_count'])) : ?>
            <tr class="sectiontableentry1">
                <td class="valueleft"><?php echo $escape($value($homeRank, 'cnt_matches')); ?></td>
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_COUNT_MATCHES'); ?></td>
                <td class="valueright"><?php echo $escape($value($awayRank, 'cnt_matches')); ?></td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->config['show_match_total'])) : ?>
            <tr class="sectiontableentry2">
                <td class="valueleft"><?php echo $escape($record($homeRank)); ?></td>
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_TOTAL'); ?></td>
                <td class="valueright"><?php echo $escape($record($awayRank)); ?></td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->config['show_match_total_home'])) : ?>
            <tr class="sectiontableentry1">
                <td class="valueleft"><?php echo $escape($record($homeRank, '_home')); ?></td>
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HOME'); ?></td>
                <td class="valueright"><?php echo $escape($record($awayRank, '_home')); ?></td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->config['show_match_total_away'])) : ?>
            <tr class="sectiontableentry2">
                <td class="valueleft"><?php echo $escape($awayRecord($homeRank)); ?></td>
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_AWAY'); ?></td>
                <td class="valueright"><?php echo $escape($awayRecord($awayRank)); ?></td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->config['show_match_points'])) : ?>
            <tr class="sectiontableentry1">
                <td class="valueleft"><?php echo $escape($value($homeRank, 'sum_points')); ?></td>
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_POINTS'); ?></td>
                <td class="valueright"><?php echo $escape($value($awayRank, 'sum_points')); ?></td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->config['show_match_goals'])) : ?>
            <tr class="sectiontableentry2">
                <td class="valueleft"><?php echo $escape($value($homeRank, 'sum_team1_result') . ' : ' . $value($homeRank, 'sum_team2_result')); ?></td>
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_GOALS'); ?></td>
                <td class="valueright"><?php echo $escape($value($awayRank, 'sum_team1_result') . ' : ' . $value($awayRank, 'sum_team2_result')); ?></td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->config['show_match_diff'])) : ?>
            <tr class="sectiontableentry1">
                <td class="valueleft"><?php echo $escape($value($homeRank, 'diff_team_results')); ?></td>
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_DIFFERENCE'); ?></td>
                <td class="valueright"><?php echo $escape($value($awayRank, 'diff_team_results')); ?></td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->config['show_match_highest_stats'])) : ?>
            <?php if (!empty($this->config['show_match_highest_won'])) : ?>
                <tr class="sectiontableentry2">
                    <td class="valueleft"><?php echo $statLink($this->home_highest_home_win); ?></td>
                    <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HIGHEST_WON_HOME'); ?></td>
                    <td class="valueright"><?php echo $statLink($this->away_highest_away_win); ?></td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($this->config['show_match_highest_loss'])) : ?>
                <tr class="sectiontableentry1">
                    <td class="valueleft"><?php echo $statLink($this->home_highest_home_def); ?></td>
                    <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HIGHEST_LOSS_HOME'); ?></td>
                    <td class="valueright"><?php echo $statLink($this->away_highest_away_def); ?></td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($this->config['show_match_highest_won_away'])) : ?>
                <tr class="sectiontableentry2">
                    <td class="valueleft"><?php echo $statLink($this->home_highest_away_win); ?></td>
                    <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HIGHEST_WON_AWAY'); ?></td>
                    <td class="valueright"><?php echo $statLink($this->away_highest_home_win); ?></td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($this->config['show_match_highest_loss_away'])) : ?>
                <tr class="sectiontableentry1">
                    <td class="valueleft"><?php echo $statLink($this->home_highest_away_def); ?></td>
                    <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HIGHEST_LOSS_AWAY'); ?></td>
                    <td class="valueright"><?php echo $statLink($this->away_highest_home_def); ?></td>
                </tr>
            <?php endif; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <h4><?php echo $escape($homeName . ' ' . Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_VS') . ' ' . $awayName); ?></h4>
    <h4><?php echo $escape(Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_DATA_OF') . ' ' . $homeName); ?></h4>

    <table class="table table-striped" id="nextmatch-h2h-summary">
        <thead>
        <tr>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LEAGUE'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_COUNT_MATCHES'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_WON'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DRAW'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LOST'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_SCOREFOR'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_SCOREAGAINST'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->gesamtspiele as $league => $stats) : ?>
            <tr>
                <td><?php echo $escape($league); ?></td>
                <td><?php echo (int) ($stats->gesamtspiele ?? 0); ?></td>
                <td><?php echo (int) ($stats->gewonnen ?? 0); ?></td>
                <td><?php echo (int) ($stats->unentschieden ?? 0); ?></td>
                <td><?php echo (int) ($stats->verloren ?? 0); ?></td>
                <td><?php echo (int) ($stats->plustore ?? 0); ?></td>
                <td><?php echo (int) ($stats->minustore ?? 0); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <table class="table table-striped" id="nextmatch-h2h-home-away">
        <thead>
        <tr>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LEAGUE'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_LOCAL_WINS'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_LOCAL_DRAWS'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_LOCAL_LOSTS'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_AWAY_WINS'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_AWAY_DRAWS'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_AWAY_LOSTS'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->gesamtspiele as $league => $stats) : ?>
            <tr>
                <td><?php echo $escape($league); ?></td>
                <td><?php echo (int) ($stats->localwin ?? 0); ?></td>
                <td><?php echo (int) ($stats->localdraw ?? 0); ?></td>
                <td><?php echo (int) ($stats->locallost ?? 0); ?></td>
                <td><?php echo (int) ($stats->awaywin ?? 0); ?></td>
                <td><?php echo (int) ($stats->awaydraw ?? 0); ?></td>
                <td><?php echo (int) ($stats->awaylost ?? 0); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h4><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY_COUNT_RESULT'); ?></h4>

    <?php
    $scoreTabs = [
        'gesamt' => [Text::_('COM_SPORTSMANAGEMENT_STATS_MATCHES_OVERALL'), $this->statgames['gesamt'] ?? []],
        'home' => [$homeName . ' ' . Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_VS') . ' ' . $awayName, $this->statgames['home'] ?? []],
        'away' => [$awayName . ' ' . Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_VS') . ' ' . $homeName, $this->statgames['away'] ?? []],
    ];
    ?>
    <ul class="nav nav-tabs" id="nextmatch-score-tabs" role="tablist">
        <?php $tabIndex = 0; ?>
        <?php foreach ($scoreTabs as $tabId => [$tabLabel, $scores]) : ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link<?php echo $tabIndex === 0 ? ' active' : ''; ?>"
                        id="nextmatch-<?php echo $escape($tabId); ?>-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#nextmatch-<?php echo $escape($tabId); ?>"
                        type="button"
                        role="tab"
                        aria-selected="<?php echo $tabIndex === 0 ? 'true' : 'false'; ?>">
                    <?php echo $escape($tabLabel); ?>
                </button>
            </li>
            <?php $tabIndex++; ?>
        <?php endforeach; ?>
    </ul>
    <div class="tab-content" id="nextmatch-score-tab-content">
        <?php $tabIndex = 0; ?>
        <?php foreach ($scoreTabs as $tabId => [$tabLabel, $scores]) : ?>
            <?php if (is_array($scores)) { ksort($scores); } ?>
            <div class="tab-pane fade<?php echo $tabIndex === 0 ? ' show active' : ''; ?>"
                 id="nextmatch-<?php echo $escape($tabId); ?>"
                 role="tabpanel"
                 aria-labelledby="nextmatch-<?php echo $escape($tabId); ?>-tab"
                 tabindex="0">
                <table class="table table-striped mt-2">
                    <thead>
                    <tr>
                        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_SCORE'); ?></th>
                        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_SCORE_FREQUENCY'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ((array) $scores as $score => $frequency) : ?>
                        <tr>
                            <td><?php echo $escape($score); ?></td>
                            <td><?php echo (int) $frequency; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php $tabIndex++; ?>
        <?php endforeach; ?>
    </div>
</div>
<br>
