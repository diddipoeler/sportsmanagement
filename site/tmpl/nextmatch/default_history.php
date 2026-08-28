<?php
/**
 * Joomla 5/6 match history for the next-match view.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\MatchEventPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\TeamLogoHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

if (!$this->games) {
    return;
}

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$databaseSelector = $this->input->getInt('cfg_which_database', 0);
$seasonId = $this->input->getInt('s', 0);
$tableClass = (string) ($this->config['hystory_table_class'] ?? 'table');
$modalMode = (int) ($this->overallconfig['use_jquery_modal'] ?? 0);
$separator = (string) ($this->overallconfig['seperator'] ?? ':');
$gamesByProject = [];

foreach ($this->games as $game) {
    $gamesByProject[(string) ($game->project_name ?? '')][] = $game;
}

$this->notes = [Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY')];
?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="nextmatch-history">
    <?php echo $this->loadTemplate('jsm_notes'); ?>

    <table class="<?php echo $escape($tableClass); ?>">
        <tbody>
        <?php foreach ($gamesByProject as $projectName => $games) : ?>
            <tr class="sectiontableheader">
                <th colspan="12"><?php echo $escape($projectName); ?></th>
            </tr>

            <?php foreach ($games as $game) : ?>
                <?php
                $homeProjectTeamId = (int) ($game->projectteam1_id ?? 0);
                $awayProjectTeamId = (int) ($game->projectteam2_id ?? 0);
                $home = $this->gamesteams[$homeProjectTeamId] ?? null;
                $away = $this->gamesteams[$awayProjectTeamId] ?? null;

                if (!$home || !$away) {
                    continue;
                }

                $resultLink = SiteRouteHelper::view('results', [
                    'cfg_which_database' => $databaseSelector,
                    's' => $seasonId,
                    'p' => (string) ($game->project_slug ?? ''),
                    'r' => (string) ($game->round_slug ?? ''),
                    'division' => 0,
                    'mode' => 0,
                    'order' => '',
                    'layout' => '',
                ]);

                $reportLink = '';
                if (!empty($game->match_slug)) {
                    $reportLink = SiteRouteHelper::view('matchreport', [
                        'cfg_which_database' => $databaseSelector,
                        's' => $seasonId,
                        'p' => (string) ($game->project_slug ?? ''),
                        'mid' => (string) $game->match_slug,
                    ]);
                }

                $matchId = (int) ($game->id ?? 0);
                $events = $matchId > 0 ? ($this->historyEvents[$matchId] ?? []) : [];
                $substitutions = $matchId > 0 ? ($this->historySubstitutions[$matchId] ?? []) : [];
                $hasEvents = !empty($this->config['show_events'])
                    && ($events || (!empty($this->config['use_tabs_events']) && $substitutions));
                $collapseId = 'nextmatch-history-events-' . $matchId;

                $homePicture = (object) [
                    'name' => (string) ($home->name ?? ''),
                    'picture' => (string) ($home->picture ?? ''),
                ];
                $awayPicture = (object) [
                    'name' => (string) ($away->name ?? ''),
                    'picture' => (string) ($away->picture ?? ''),
                ];

                $matchDate = (string) ($game->match_date ?? '');
                $hasDate = $matchDate !== '' && $matchDate !== '0000-00-00 00:00:00';
                ?>
                <tr>
                    <td class="nowrap">
                        <?php echo HTMLHelper::link($resultLink, $escape((string) ($game->roundcode ?? ''))); ?>
                        <?php if ($hasEvents && $matchId > 0) : ?>
                            <button class="btn btn-sm btn-link p-0 ms-1"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#<?php echo $escape($collapseId); ?>"
                                    aria-expanded="false"
                                    aria-controls="<?php echo $escape($collapseId); ?>"
                                    title="<?php echo $escape(Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_EVENTS')); ?>">
                                <?php echo HTMLHelper::image('media/com_sportsmanagement/jl_images/events.png', Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_EVENTS')); ?>
                            </button>
                        <?php endif; ?>
                    </td>
                    <td class="nowrap">
                        <?php echo $hasDate
                            ? HTMLHelper::date($matchDate, Text::_('COM_SPORTSMANAGEMENT_MATCHDAYDATE'))
                            : Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_DATE_EMPTY'); ?>
                    </td>
                    <td class="nowrap"><?php echo $hasDate ? $escape(substr($matchDate, 11, 5)) : ''; ?></td>
                    <td class="nowrap"><?php echo $escape((string) ($home->name ?? '')); ?></td>
                    <td class="nowrap">
                        <?php
                        echo TeamLogoHelper::render(
                            $homePicture,
                            'nextmatch-history-home-' . ($matchId > 0 ? $matchId : $homeProjectTeamId),
                            true,
                            20,
                            $this->modalwidth,
                            $this->modalheight,
                            $modalMode
                        );
                        ?>
                    </td>
                    <td class="nowrap">-</td>
                    <td class="nowrap">
                        <?php
                        echo TeamLogoHelper::render(
                            $awayPicture,
                            'nextmatch-history-away-' . ($matchId > 0 ? $matchId : $awayProjectTeamId),
                            true,
                            20,
                            $this->modalwidth,
                            $this->modalheight,
                            $modalMode
                        );
                        ?>
                    </td>
                    <td class="nowrap"><?php echo $escape((string) ($away->name ?? '')); ?></td>
                    <td class="nowrap"><?php echo isset($game->team1_result) ? $escape($game->team1_result) : ''; ?></td>
                    <td class="nowrap"><?php echo $escape($separator); ?></td>
                    <td class="nowrap"><?php echo isset($game->team2_result) ? $escape($game->team2_result) : ''; ?></td>
                    <td class="nowrap">
                        <?php if ((int) ($game->show_report ?? 0) === 1 && $reportLink !== '') : ?>
                            <?php
                            $reportIcon = HTMLHelper::image(
                                Uri::root() . 'media/com_sportsmanagement/jl_images/zoom.png',
                                Text::_('Match Report'),
                                ['title' => Text::_('Match Report')]
                            );
                            echo HTMLHelper::link($reportLink, $reportIcon);
                            ?>
                        <?php endif; ?>
                    </td>
                </tr>

                <?php if ($hasEvents && $matchId > 0) : ?>
                    <tr class="events">
                        <td colspan="12" class="p-0 border-0">
                            <div class="collapse" id="<?php echo $escape($collapseId); ?>">
                                <div class="card card-body my-1">
                                    <?php
                                    echo MatchEventPresentationHelper::render(
                                        $game,
                                        $this->overallevents,
                                        $events,
                                        $substitutions,
                                        $this->config
                                    );
                                    ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
