<?php
/** Native Joomla 5/6 shared team-plan match layout. */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\MatchCommentsHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\MatchEventPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\MatchResultHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\MatchTimeHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\TeamplanMatchPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\TeamplanTeamPresentationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (empty($this->matches)) {
    echo '<h3>' . $this->escape(Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_NO_MATCHES')) . '</h3>';
    return;
}

$groupByDate = $this->groupMatchesByDate;
$matches = $this->matches;
if ($groupByDate) {
    usort($matches, static fn (object $a, object $b): int => strcmp(
        (string) ($a->match_date ?? ''),
        (string) ($b->match_date ?? '')
    ));
}

$groups = [];
foreach ($matches as $match) {
    $key = $groupByDate ? substr((string) ($match->match_date ?? ''), 0, 10) : 'all';
    $groups[$key][] = $match;
}

$config = $this->config;
$showVisual = !empty($config['show_logo_small']);
$visualMode = (int) ($config['show_logo_small'] ?? 0);
$resultStyle = (int) ($config['result_style'] ?? 0);
$showDateColumn = !$groupByDate && !empty($config['show_date']);
$teamsAsReferees = !empty($this->project->teams_as_referees);
$timezone = trim((string) ($this->project->timezone ?? 'UTC')) ?: 'UTC';
$modalMode = (int) ($this->overallconfig['use_jquery_modal'] ?? 0);

$columnCount = 0;
$columnCount += !empty($config['show_events']) ? 1 : 0;
$columnCount += !empty($config['show_matchday']) ? 1 : 0;
$columnCount += !empty($config['show_match_number']) ? 1 : 0;
$columnCount += (($this->project->project_type ?? '') === 'DIVISIONS_LEAGUE' && !empty($config['show_division'])) ? 1 : 0;
$columnCount += (!empty($config['show_playground']) || !empty($config['show_playground_alert'])) ? 1 : 0;
$columnCount += $showDateColumn ? 1 : 0;
$columnCount += !empty($config['show_time']) ? 1 : 0;
$columnCount += !empty($config['show_time_present']) ? 1 : 0;
$columnCount += $resultStyle === 1 ? 3 : 4;
$columnCount += $showVisual ? 2 : 0;
$columnCount += !empty($config['show_referee']) ? 1 : 0;
$columnCount += (!empty($config['show_thumbs_picture']) && $this->teamId > 0) ? 1 : 0;
$columnCount += !empty($config['show_historylink']) ? 1 : 0;
$columnCount += !empty($config['show_matchreport_column']) ? 1 : 0;
$columnCount += !empty($config['show_attendance_column']) ? 1 : 0;
$columnCount += in_array((int) ($config['show_comments_count'] ?? 0), [1, 2], true) ? 1 : 0;
?>
<div class="<?php echo $this->escape($this->divclassrow); ?> table-responsive" id="teamplanoutput">
    <?php foreach ($groups as $dateKey => $groupMatches) : ?>
        <?php if ($groupByDate && $groupMatches !== []) : ?>
            <?php
            $first = $groupMatches[0];
            $dateText = Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_DATE_EMPTY');
            if ($dateKey !== '' && $dateKey !== '0000-00-00') {
                $date = Factory::getDate((string) $first->match_date);
                $date->setTimezone(new DateTimeZone($timezone));
                $dateText = $date->format(Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHDATE'));
            }
            ?>
            <h3><?php echo $this->escape($dateText); ?></h3>
            <?php if (!empty($first->name)) : ?>
                <p><strong><?php echo $this->escape((string) $first->name); ?></strong></p>
            <?php endif; ?>
        <?php endif; ?>

        <table class="<?php echo $this->escape((string) ($config['table_class'] ?? 'table')); ?>">
            <thead>
            <tr>
                <?php if (!empty($config['show_events'])) : ?><th scope="col">&nbsp;</th><?php endif; ?>
                <?php if (!empty($config['show_matchday'])) : ?><th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_MATCHDAY'); ?></th><?php endif; ?>
                <?php if (!empty($config['show_match_number'])) : ?><th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_NUMBER'); ?></th><?php endif; ?>
                <?php if (($this->project->project_type ?? '') === 'DIVISIONS_LEAGUE' && !empty($config['show_division'])) : ?><th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_DIVISION'); ?></th><?php endif; ?>
                <?php if (!empty($config['show_playground']) || !empty($config['show_playground_alert'])) : ?><th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_PLAYGROUND'); ?></th><?php endif; ?>
                <?php if ($showDateColumn) : ?><th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_EDIT_DATE'); ?></th><?php endif; ?>
                <?php if (!empty($config['show_time'])) : ?><th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_EDIT_TIME'); ?></th><?php endif; ?>
                <?php if (!empty($config['show_time_present'])) : ?><th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_TIME_PRESENT'); ?></th><?php endif; ?>

                <th scope="col" class="text-end">
                    <?php if (!empty($config['show_home_guest_team_marker'])) : ?>
                        <?php echo Text::_(!empty($config['switch_home_guest']) ? 'COM_SPORTSMANAGEMENT_TEAMPLAN_AWAY_TEAM' : 'COM_SPORTSMANAGEMENT_TEAMPLAN_HOME_TEAM'); ?>
                    <?php endif; ?>
                </th>
                <?php if ($showVisual) : ?><th scope="col">&nbsp;</th><?php endif; ?>
                <?php if ($resultStyle === 1) : ?>
                    <th scope="col" class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_RESULT'); ?></th>
                <?php else : ?>
                    <th scope="col" class="text-center">&nbsp;</th>
                <?php endif; ?>
                <?php if ($showVisual) : ?><th scope="col">&nbsp;</th><?php endif; ?>
                <th scope="col">
                    <?php if (!empty($config['show_home_guest_team_marker'])) : ?>
                        <?php echo Text::_(!empty($config['switch_home_guest']) ? 'COM_SPORTSMANAGEMENT_TEAMPLAN_HOME_TEAM' : 'COM_SPORTSMANAGEMENT_TEAMPLAN_AWAY_TEAM'); ?>
                    <?php endif; ?>
                </th>
                <?php if ($resultStyle !== 1) : ?><th scope="col" class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_RESULT'); ?></th><?php endif; ?>

                <?php if (!empty($config['show_referee'])) : ?><th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_REFEREE'); ?></th><?php endif; ?>
                <?php if (!empty($config['show_thumbs_picture']) && $this->teamId > 0) : ?><th scope="col">&nbsp;</th><?php endif; ?>
                <?php if (!empty($config['show_historylink'])) : ?><th scope="col">&nbsp;</th><?php endif; ?>
                <?php if (!empty($config['show_matchreport_column'])) : ?><th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_PAGE_TITLE'); ?></th><?php endif; ?>
                <?php if (!empty($config['show_attendance_column'])) : ?><th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_ATTENDANCE'); ?></th><?php endif; ?>
                <?php if (in_array((int) ($config['show_comments_count'] ?? 0), [1, 2], true)) : ?><th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_COMMENTS'); ?></th><?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($groupMatches as $match) : ?>
                <?php
                $matchId = (int) ($match->id ?? 0);
                $homeTeam = $this->teams[(int) ($match->projectteam1_id ?? 0)] ?? null;
                $awayTeam = $this->teams[(int) ($match->projectteam2_id ?? 0)] ?? null;
                if (!$homeTeam) {
                    continue;
                }

                $homeFavorite = in_array((int) ($homeTeam->id ?? 0), $this->favteams, true);
                $awayFavorite = $awayTeam && in_array((int) ($awayTeam->id ?? 0), $this->favteams, true);
                $rowStyles = [];
                if (!empty($config['highlight_fav']) && $this->teamId <= 0 && ($homeFavorite || $awayFavorite)
                    && (int) ($this->project->fav_team_highlight_type ?? 0) === 1) {
                    if (!empty($this->project->fav_team_text_bold)) {
                        $rowStyles[] = 'font-weight:bold';
                    }
                    if (trim((string) ($this->project->fav_team_text_color ?? '')) !== '') {
                        $rowStyles[] = 'color:' . preg_replace('/[^#A-Za-z0-9(),.%\s-]/', '', (string) $this->project->fav_team_text_color);
                    }
                    if (trim((string) ($this->project->fav_team_color ?? '')) !== '') {
                        $rowStyles[] = 'background-color:' . preg_replace('/[^#A-Za-z0-9(),.%\s-]/', '', (string) $this->project->fav_team_color);
                    }
                }

                $routeBase = [
                    'cfg_which_database' => $this->databaseSelector,
                    's' => $this->seasonId,
                    'p' => (string) ($this->project->slug ?? $match->project_slug ?? ''),
                    'division' => (string) ($match->division_slug ?? ''),
                    'mode' => 0,
                    'ptid' => 0,
                ];
                $homeLink = null;
                $awayLink = null;
                if (!empty($config['show_teamplan_link'])) {
                    $homeLink = SiteRouteHelper::view('teamplan', $routeBase + ['tid' => (string) ($homeTeam->team_slug ?? $homeTeam->id ?? 0)]);
                    if ($awayTeam) {
                        $awayLink = SiteRouteHelper::view('teamplan', $routeBase + ['tid' => (string) ($awayTeam->team_slug ?? $awayTeam->id ?? 0)]);
                    }
                }

                $homeName = TeamplanTeamPresentationHelper::renderName(
                    $homeTeam,
                    'g' . $matchId . 'h',
                    $config,
                    $homeFavorite,
                    $homeLink,
                    $this->databaseSelector,
                    $this->seasonId,
                    $this->project
                );
                $awayName = $awayTeam
                    ? TeamplanTeamPresentationHelper::renderName(
                        $awayTeam,
                        'g' . $matchId . 'a',
                        $config,
                        $awayFavorite,
                        $awayLink,
                        $this->databaseSelector,
                        $this->seasonId,
                        $this->project
                    )
                    : '-';
                $homeVisual = $showVisual
                    ? TeamplanTeamPresentationHelper::renderVisual(
                        $homeTeam,
                        $visualMode,
                        $matchId,
                        $this->modalwidth,
                        $this->modalheight,
                        $modalMode,
                        (int) ($config['team_picture_width'] ?? 40)
                    )
                    : '';
                $awayVisual = $showVisual && $awayTeam
                    ? TeamplanTeamPresentationHelper::renderVisual(
                        $awayTeam,
                        $visualMode,
                        $matchId,
                        $this->modalwidth,
                        $this->modalheight,
                        $modalMode,
                        (int) ($config['team_picture_width'] ?? 40)
                    )
                    : '';

                if (!empty($config['switch_home_guest'])) {
                    [$homeName, $awayName] = [$awayName, $homeName];
                    [$homeVisual, $awayVisual] = [$awayVisual, $homeVisual];
                }

                $events = $this->matchEvents[$matchId] ?? [];
                $substitutions = $this->matchSubstitutions[$matchId] ?? [];
                $hasEvents = !empty($config['show_events'])
                    && ($events !== [] || (!empty($config['use_tabs_events']) && $substitutions !== []));
                ?>
                <tr<?php echo $rowStyles !== [] ? ' style="' . $this->escape(implode(';', $rowStyles) . ';') . '"' : ''; ?>>
                    <?php if (!empty($config['show_events'])) : ?>
                        <td>
                            <?php if ($hasEvents) : ?>
                                <button type="button" class="btn btn-link p-0" title="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_EVENTS')); ?>" onclick="switchMenu('info<?php echo $matchId; ?>');return false;">
                                    <?php echo HTMLHelper::image('media/com_sportsmanagement/jl_images/events.png', Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_EVENTS')); ?>
                                </button>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>

                    <?php if (!empty($config['show_matchday'])) : ?>
                        <td>
                            <?php
                            echo HTMLHelper::link(
                                SiteRouteHelper::view('results', [
                                    'cfg_which_database' => $this->databaseSelector,
                                    's' => $this->seasonId,
                                    'p' => (string) ($this->project->slug ?? $match->project_slug ?? ''),
                                    'r' => (string) ($match->round_slug ?? ''),
                                    'division' => (string) ($match->division_slug ?? ''),
                                    'mode' => 0,
                                ]),
                                $this->escape((string) ($match->roundcode ?? ''))
                            );
                            ?>
                        </td>
                    <?php endif; ?>

                    <?php if (!empty($config['show_match_number'])) : ?><td><?php echo $this->escape((string) (($match->match_number ?? '') ?: '-')); ?></td><?php endif; ?>

                    <?php if (($this->project->project_type ?? '') === 'DIVISIONS_LEAGUE' && !empty($config['show_division'])) : ?>
                        <td><?php echo TeamplanMatchPresentationHelper::renderDivision($homeTeam, $awayTeam, $config, $this->databaseSelector, $this->seasonId, $this->project); ?></td>
                    <?php endif; ?>

                    <?php if (!empty($config['show_playground']) || !empty($config['show_playground_alert'])) : ?>
                        <td><?php echo TeamplanMatchPresentationHelper::renderPlayground($match, $homeTeam, $config, $this->databaseSelector, $this->seasonId, $this->project); ?></td>
                    <?php endif; ?>

                    <?php if ($showDateColumn) : ?>
                        <td>
                            <?php if (!empty($match->match_date) && !str_contains((string) $match->match_date, '0000-00-00')) : ?>
                                <?php
                                $matchDate = Factory::getDate((string) $match->match_date);
                                $matchDate->setTimezone(new DateTimeZone($timezone));
                                ?>
                                <?php if (!empty($config['show_date_image'])) : ?>
                                    <div class="jsmcalendar">
                                        <div class="jsmcalendar-month"><?php echo $this->escape($matchDate->format('M')); ?></div>
                                        <div class="jsmcalendar-day"><?php echo $this->escape($matchDate->format('d')); ?></div>
                                        <div class="jsmcalendar-dayname"><?php echo $this->escape($matchDate->format('D')); ?></div>
                                    </div>
                                <?php else : ?>
                                    <?php echo $this->escape($matchDate->format(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE'))); ?>
                                <?php endif; ?>
                            <?php else : ?>
                                <?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_DATE_EMPTY'); ?>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>

                    <?php if (!empty($config['show_time'])) : ?><td><?php echo MatchTimeHelper::format($match, $config, $this->overallconfig, $this->project); ?></td><?php endif; ?>
                    <?php if (!empty($config['show_time_present'])) : ?><td><?php echo $this->escape((string) (($match->time_present ?? '') ?: '-')); ?></td><?php endif; ?>

                    <td class="text-end"><?php echo $homeName; ?></td>
                    <?php if ($showVisual) : ?><td class="text-center"><?php echo $homeVisual; ?></td><?php endif; ?>
                    <?php if ($resultStyle === 1) : ?>
                        <td class="text-center"><?php echo TeamplanMatchPresentationHelper::renderScore($match, $config, $this->databaseSelector, $this->seasonId, $this->project); ?></td>
                    <?php else : ?>
                        <td class="text-center"><?php echo $this->escape((string) ($config['seperator'] ?? '-')); ?></td>
                    <?php endif; ?>
                    <?php if ($showVisual) : ?><td class="text-center"><?php echo $awayVisual; ?></td><?php endif; ?>
                    <td><?php echo $awayName; ?></td>
                    <?php if ($resultStyle !== 1) : ?><td class="text-center"><?php echo TeamplanMatchPresentationHelper::renderScore($match, $config, $this->databaseSelector, $this->seasonId, $this->project); ?></td><?php endif; ?>

                    <?php if (!empty($config['show_referee'])) : ?>
                        <td><?php echo TeamplanMatchPresentationHelper::renderReferees((array) ($match->referees ?? []), $config, $teamsAsReferees, $this->databaseSelector, $this->seasonId, $this->project); ?></td>
                    <?php endif; ?>

                    <?php if (!empty($config['show_thumbs_picture']) && $this->teamId > 0) : ?>
                        <td class="text-center"><?php echo MatchResultHelper::renderOutcomeIcon($match, $this->ptid); ?></td>
                    <?php endif; ?>

                    <?php if (!empty($config['show_historylink'])) : ?>
                        <td class="text-center"><?php echo TeamplanMatchPresentationHelper::renderHistoryLink($match, $this->databaseSelector, $this->seasonId, $this->project); ?></td>
                    <?php endif; ?>

                    <?php if (!empty($config['show_matchreport_column'])) : ?>
                        <td><?php echo TeamplanMatchPresentationHelper::renderMatchReportLink($match, $config, $this->databaseSelector, $this->seasonId, $this->project); ?></td>
                    <?php endif; ?>

                    <?php if (!empty($config['show_attendance_column'])) : ?><td class="text-center"><?php echo (int) ($match->crowd ?? 0) > 0 ? $this->escape((string) $match->crowd) : ''; ?></td><?php endif; ?>

                    <?php if (in_array((int) ($config['show_comments_count'] ?? 0), [1, 2], true)) : ?>
                        <td class="text-center"><?php echo MatchCommentsHelper::render($match, $homeTeam, $awayTeam ?? (object) [], $config, $this->project); ?></td>
                    <?php endif; ?>
                </tr>

                <?php if ($hasEvents) : ?>
                    <tr class="events">
                        <td colspan="<?php echo $columnCount; ?>">
                            <div id="info<?php echo $matchId; ?>" class="jsmeventsshowhide" style="display:none;">
                                <?php echo MatchEventPresentationHelper::render($match, $this->projectevents, $events, $substitutions, $config); ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
</div>
