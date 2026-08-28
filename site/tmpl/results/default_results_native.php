<?php
/** Native Joomla 5/6 results match renderer. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\MatchCommentsHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\MatchEventPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\MatchTimeHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ResultsPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\TeamplanMatchPresentationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

if (!$this->project || $this->matches === []) {
    return;
}

$dates = ResultsPresentationHelper::groupByDate($this->matches);
$showEvents = !empty($this->config['show_events']);
$showSummary = !empty($this->config['show_match_summary']);
$showDivision = !empty($this->config['show_division']);
$showTime = !empty($this->config['show_time']);
$showPlayground = !empty($this->config['show_playground']) || !empty($this->config['show_playground_alert']);
$showReferee = !empty($this->config['show_referee']);
$showScoresheet = !empty($this->config['show_scoresheet']);
$showAttendance = !empty($this->config['show_attendance_column']);
$showComments = $this->commentsEnabled;
$showMatchNumber = !empty($this->config['show_match_number']);
$logoType = (int) ($this->config['show_logo_small'] ?? 0);
$modalMode = (int) ($this->overallconfig['use_jquery_modal'] ?? 0);
$teamsAsReferees = !empty($this->project->teams_as_referees);

$baseColumns = 3; // home, score, away
$extraColumns = (int) $showMatchNumber
    + (int) $showEvents
    + (int) $showSummary
    + (int) $showDivision
    + (int) $showTime
    + (int) $showPlayground
    + (int) $showReferee
    + (int) $showScoresheet
    + (int) $showAttendance
    + (int) $showComments;
$columnCount = $baseColumns + $extraColumns;
?>
<div class="<?php echo $this->escape($this->divclassrow); ?> table-responsive" id="defaultresults">
    <form name="adminForm" id="adminForm" action="<?php echo $this->escape($this->uri->toString()); ?>" method="get">
        <input type="hidden" name="option" value="com_sportsmanagement">
        <input type="hidden" name="view" value="results">
        <input type="hidden" name="cfg_which_database" value="<?php echo $this->cfg_which_database; ?>">
        <input type="hidden" name="s" value="<?php echo $this->season_id; ?>">
        <input type="hidden" name="p" value="<?php echo (int) ($this->project->id ?? 0); ?>">
        <input type="hidden" name="r" value="<?php echo $this->roundid; ?>">
        <?php if ($this->division) : ?>
            <input type="hidden" name="division" value="<?php echo (int) $this->division->id; ?>">
        <?php endif; ?>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
                <?php echo $this->pagination ? $this->pagination->getLimitBox() : ''; ?>
            </div>
            <?php if ($this->pagination) : ?>
                <div><?php echo $this->pagination->getPagesCounter(); ?></div>
                <div><?php echo $this->pagination->getResultsCounter(); ?></div>
            <?php endif; ?>
        </div>

        <?php if ($this->pagination) : ?>
            <div class="mb-3"><?php echo $this->pagination->getPagesLinks(); ?></div>
        <?php endif; ?>

        <table class="<?php echo $this->escape((string) ($this->config['table_class'] ?? 'table table-striped')); ?>" id="results">
            <?php foreach ($dates as $date => $games) : ?>
                <thead>
                <tr class="results-date-header">
                    <th colspan="<?php echo $columnCount; ?>">
                        <?php if ($date === '0000-00-00') : ?>
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_DATE_EMPTY'); ?>
                        <?php else : ?>
                            <time datetime="<?php echo $this->escape($date); ?>">
                                <?php echo HTMLHelper::date($date, Text::_('COM_SPORTSMANAGEMENT_RESULTS_GAMES_DATE_DAY')); ?>
                            </time>
                        <?php endif; ?>
                        <?php if (!empty($this->config['show_matchday_dateheader']) && $this->roundcode !== '') : ?>
                            - <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_RESULTS_GAMEDAY_NB', $this->roundcode); ?>
                        <?php endif; ?>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($games as $game) : ?>
                    <?php
                    $homeProjectTeamId = (int) ($game->projectteam1_id ?? 0);
                    $awayProjectTeamId = (int) ($game->projectteam2_id ?? 0);
                    $homeTeam = $this->teams[$homeProjectTeamId] ?? null;
                    $awayTeam = $this->teams[$awayProjectTeamId] ?? null;
                    if (!$homeTeam || !$awayTeam) {
                        continue;
                    }

                    $displayHome = !empty($this->config['switch_home_guest']) ? $awayTeam : $homeTeam;
                    $displayAway = !empty($this->config['switch_home_guest']) ? $homeTeam : $awayTeam;
                    $homeTeamId = (int) ($displayHome->id ?? $displayHome->team_id ?? 0);
                    $awayTeamId = (int) ($displayAway->id ?? $displayAway->team_id ?? 0);
                    $homeFavourite = in_array($homeTeamId, $this->favteams, true);
                    $awayFavourite = in_array($awayTeamId, $this->favteams, true);
                    $hasFavourite = $homeFavourite || $awayFavourite;
                    $events = $this->eventsByMatch[(int) $game->id] ?? [];
                    $substitutions = $this->substitutionsByMatch[(int) $game->id] ?? [];
                    $referees = $this->refereesByMatch[(int) $game->id] ?? [];
                    $hasEvents = $showEvents && ($events !== [] || (!empty($this->config['use_tabs_events']) && $substitutions !== []));
                    $collapseId = 'results-events-' . (int) $game->id;
                    $rowStyle = '';

                    if ($hasFavourite
                        && !empty($this->config['highlight_fav'])
                        && (int) ($this->project->fav_team_highlight_type ?? 0) === 1) {
                        $styles = [];
                        if (trim((string) ($this->project->fav_team_text_bold ?? '')) !== '') {
                            $styles[] = 'font-weight:bold';
                        }
                        if (trim((string) ($this->project->fav_team_text_color ?? '')) !== '') {
                            $styles[] = 'color:' . trim((string) $this->project->fav_team_text_color);
                        }
                        if (trim((string) ($this->project->fav_team_color ?? '')) !== '') {
                            $styles[] = 'background-color:' . trim((string) $this->project->fav_team_color);
                        }
                        $rowStyle = implode(';', $styles);
                    }
                    ?>
                    <tr class="team<?php echo $homeProjectTeamId; ?> team<?php echo $awayProjectTeamId; ?>"<?php echo $rowStyle !== '' ? ' style="' . $this->escape($rowStyle) . '"' : ''; ?>>
                        <?php if ($showMatchNumber) : ?>
                            <td class="text-center"><?php echo (int) ($game->match_number ?? 0) ?: '&nbsp;'; ?></td>
                        <?php endif; ?>

                        <?php if ($showEvents) : ?>
                            <td class="text-center">
                                <?php if ($hasEvents) : ?>
                                    <button
                                        class="btn btn-sm btn-link p-0"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#<?php echo $collapseId; ?>"
                                        aria-expanded="false"
                                        aria-controls="<?php echo $collapseId; ?>"
                                        title="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_EVENTS')); ?>"
                                    >
                                        <?php echo HTMLHelper::image('media/com_sportsmanagement/jl_images/events.png', Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_EVENTS'), ['width' => 20, 'height' => 20]); ?>
                                    </button>
                                <?php else : ?>
                                    &nbsp;
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>

                        <?php if ($showSummary) : ?>
                            <td class="text-center">
                                <?php if (!empty($game->content_id)) : ?>
                                    <?php
                                    $summaryTitle = trim((string) ($displayHome->name ?? '') . ' - ' . (string) ($displayAway->name ?? ''));
                                    echo ModalImageHelper::render(
                                        'results-summary-' . (int) $game->id,
                                        'media/com_sportsmanagement/jl_images/information.png',
                                        $summaryTitle,
                                        20,
                                        Uri::base() . 'index.php?tmpl=component&option=com_content&view=article&id=' . (int) $game->content_id,
                                        $this->modalwidth,
                                        $this->modalheight,
                                        $modalMode
                                    );
                                    ?>
                                <?php else : ?>
                                    &nbsp;
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>

                        <?php if ($showDivision) : ?>
                            <td>
                                <?php echo TeamplanMatchPresentationHelper::renderDivision(
                                    $homeTeam,
                                    $awayTeam,
                                    $this->config,
                                    $this->cfg_which_database,
                                    $this->season_id,
                                    $this->project
                                ); ?>
                            </td>
                        <?php endif; ?>

                        <?php if ($showTime) : ?>
                            <td class="text-nowrap">
                                <?php echo MatchTimeHelper::format($game, $this->config, $this->overallconfig, $this->project); ?>
                            </td>
                        <?php endif; ?>

                        <?php if ($showPlayground) : ?>
                            <td>
                                <?php echo TeamplanMatchPresentationHelper::renderPlayground(
                                    $game,
                                    $homeTeam,
                                    $this->config,
                                    $this->cfg_which_database,
                                    $this->season_id,
                                    $this->project
                                ); ?>
                            </td>
                        <?php endif; ?>

                        <td class="text-end">
                            <?php if ($logoType > 0) : ?>
                                <?php echo ResultsPresentationHelper::renderTeamIcon(
                                    $displayHome,
                                    $logoType,
                                    'results-home-' . (int) $game->id,
                                    $this->modalwidth,
                                    $this->modalheight,
                                    $modalMode
                                ); ?>
                            <?php endif; ?>
                            <?php echo ResultsPresentationHelper::renderTeamName(
                                $displayHome,
                                'results-' . (int) $game->id . '-',
                                $this->config,
                                $homeFavourite,
                                $this->project,
                                $this->cfg_which_database,
                                $this->season_id
                            ); ?>
                        </td>

                        <td class="score text-center text-nowrap">
                            <?php echo ResultsPresentationHelper::renderScore(
                                $game,
                                $this->config,
                                $hasFavourite,
                                $this->cfg_which_database,
                                $this->season_id,
                                $this->project
                            ); ?>
                            <?php if (!empty($this->config['show_historylink'])) : ?>
                                <?php
                                $historyLink = SiteRouteHelper::view('nextmatch', [
                                    'cfg_which_database' => $this->cfg_which_database,
                                    's' => $this->season_id,
                                    'p' => (string) ($this->project->slug ?? $this->project->id),
                                    'mid' => (string) ($game->slug ?? $game->id),
                                ]);
                                echo ' ' . HTMLHelper::link(
                                    $historyLink,
                                    HTMLHelper::image(
                                        'components/com_sportsmanagement/assets/images/history-icon-png--21.png',
                                        Text::_('COM_SPORTSMANAGEMENT_HISTORY'),
                                        ['width' => 20, 'height' => 20, 'title' => Text::_('COM_SPORTSMANAGEMENT_HISTORY')]
                                    )
                                );
                                ?>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php echo ResultsPresentationHelper::renderTeamName(
                                $displayAway,
                                'results-' . (int) $game->id . '-',
                                $this->config,
                                $awayFavourite,
                                $this->project,
                                $this->cfg_which_database,
                                $this->season_id
                            ); ?>
                            <?php if ($logoType > 0) : ?>
                                <?php echo ResultsPresentationHelper::renderTeamIcon(
                                    $displayAway,
                                    $logoType,
                                    'results-away-' . (int) $game->id,
                                    $this->modalwidth,
                                    $this->modalheight,
                                    $modalMode
                                ); ?>
                            <?php endif; ?>
                        </td>

                        <?php if ($showReferee) : ?>
                            <td class="referees">
                                <?php echo ResultsPresentationHelper::renderReferees(
                                    $referees,
                                    $teamsAsReferees,
                                    $this->config,
                                    $this->cfg_which_database,
                                    $this->season_id,
                                    $this->project
                                ); ?>
                            </td>
                        <?php endif; ?>

                        <?php if ($showScoresheet) : ?>
                            <td class="text-center">
                                <?php
                                $scoresheet = SiteRouteHelper::view('scoresheet', [
                                    'cfg_which_database' => $this->cfg_which_database,
                                    'p' => (int) $this->project->id,
                                    'mid' => (int) $game->id,
                                ]);
                                $scoresheetTitle = Text::_('COM_SPORTSMANAGEMENT_SCORESHEET_EXPORT');
                                echo HTMLHelper::link(
                                    $scoresheet,
                                    HTMLHelper::image('media/com_sportsmanagement/jl_images/pdf_button.png', $scoresheetTitle),
                                    ['title' => $scoresheetTitle]
                                );
                                ?>
                            </td>
                        <?php endif; ?>

                        <?php if ($showAttendance) : ?>
                            <td class="text-end">
                                <?php echo (int) ($game->crowd ?? 0) > 0 ? (int) $game->crowd : '&nbsp;'; ?>
                            </td>
                        <?php endif; ?>

                        <?php if ($showComments) : ?>
                            <td class="text-center">
                                <?php echo MatchCommentsHelper::render(
                                    $game,
                                    $homeTeam,
                                    $awayTeam,
                                    $this->config,
                                    $this->project
                                ); ?>
                            </td>
                        <?php endif; ?>
                    </tr>

                    <?php if ($hasEvents) : ?>
                        <tr class="events">
                            <td colspan="<?php echo $columnCount; ?>" class="p-0 border-0">
                                <div class="collapse" id="<?php echo $collapseId; ?>">
                                    <div class="card card-body border-0">
                                        <?php echo MatchEventPresentationHelper::render(
                                            $game,
                                            $this->projectevents,
                                            $events,
                                            $substitutions,
                                            $this->config
                                        ); ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            <?php endforeach; ?>
        </table>
    </form>
</div>
