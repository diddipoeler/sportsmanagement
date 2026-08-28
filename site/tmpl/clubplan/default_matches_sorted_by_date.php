<?php
/** Joomla 5/6 club-plan match list grouped by date. */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ClubLogoHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\MatchResultHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\MatchTimeHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\NamePresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$databaseSelector = (int) $this->databaseSelector;
$seasonId = (int) $this->seasonId;
$clubId = (int) $this->clubId;
$gamesByDate = [];

foreach ($this->matches as $game) {
    $dateKey = substr((string) ($game->match_date ?? ''), 0, 10);
    $gamesByDate[$dateKey][] = $game;
}
?>
<div class="<?php echo $this->escape($this->divclassrow); ?> table-responsive" id="clubplanmatchessbd">
    <?php foreach ($gamesByDate as $date => $games) : ?>
        <?php $firstGame = $games[0] ?? null; ?>
        <?php if (!$firstGame) { continue; } ?>

        <h3><?php echo HTMLHelper::date((string) $firstGame->match_date, Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHDATE')); ?></h3>
        <?php if (!empty($firstGame->roundname)) : ?>
            <p><strong><?php echo $this->escape((string) $firstGame->roundname); ?></strong></p>
        <?php endif; ?>

        <table class="<?php echo $this->escape((string) $this->config['table_class']); ?>">
            <tbody>
            <?php foreach ($games as $game) : ?>
                <?php
                $projectId = (int) ($game->project_id ?? $game->prid ?? 0);
                $matchId = (int) ($game->match_id ?? $game->id ?? 0);
                $settings = $this->favoriteSettings[$projectId] ?? null;
                $favoriteIds = (array) ($settings->favorite_team_ids ?? []);
                $homeFavorite = in_array((int) ($game->team1_id ?? 0), $favoriteIds, true);
                $awayFavorite = in_array((int) ($game->team2_id ?? 0), $favoriteIds, true);

                $baseRoute = [
                    'cfg_which_database' => $databaseSelector,
                    's' => $seasonId,
                    'p' => $game->project_slug,
                ];
                $matchreportLink = SiteRouteHelper::view('matchreport', $baseRoute + ['mid' => $game->match_slug]);
                $teaminfo1Link = SiteRouteHelper::view('teaminfo', $baseRoute + [
                    'tid' => $game->team1_slug,
                    'ptid' => $game->projectteam1_slug,
                ]);
                $teaminfo2Link = SiteRouteHelper::view('teaminfo', $baseRoute + [
                    'tid' => $game->team2_slug,
                    'ptid' => $game->projectteam2_slug,
                ]);
                $teamstats1Link = SiteRouteHelper::view('teamstats', $baseRoute + ['tid' => $game->team1_slug]);
                $teamstats2Link = SiteRouteHelper::view('teamstats', $baseRoute + ['tid' => $game->team2_slug]);
                $playgroundLink = SiteRouteHelper::view('playground', $baseRoute + ['pgid' => (int) ($game->playground_id ?? 0)]);

                $teamLinkMode = (int) ($this->config['which_link2'] ?? 0);
                $homeLink = $teamLinkMode === 1 ? $teaminfo1Link : ($teamLinkMode === 2 ? $teamstats1Link : '');
                $awayLink = $teamLinkMode === 1 ? $teaminfo2Link : ($teamLinkMode === 2 ? $teamstats2Link : '');
                $homeTeam = (object) [
                    'name' => (string) ($game->tname1 ?? ''),
                    'short_name' => (string) ($game->tname1_short ?? ''),
                    'middle_name' => (string) ($game->tname1_middle ?? ''),
                ];
                $awayTeam = (object) [
                    'name' => (string) ($game->tname2 ?? ''),
                    'short_name' => (string) ($game->tname2_short ?? ''),
                    'middle_name' => (string) ($game->tname2_middle ?? ''),
                ];
                $homeName = NamePresentationHelper::team($homeTeam, $this->config['team_name_format'] ?? 2, $homeLink);
                $awayName = NamePresentationHelper::team($awayTeam, $this->config['team_name_format'] ?? 2, $awayLink);

                $rowStyle = '';
                if (!empty($this->config['highlight_fav']) && $clubId <= 0 && ($homeFavorite || $awayFavorite)
                    && (int) ($settings->fav_team_highlight_type ?? 0) === 1) {
                    $styles = [];
                    if (!empty($settings->fav_team_text_bold)) {
                        $styles[] = 'font-weight:bold';
                    }
                    if (trim((string) ($settings->fav_team_text_color ?? '')) !== '') {
                        $styles[] = 'color:' . trim((string) $settings->fav_team_text_color);
                    }
                    if (trim((string) ($settings->fav_team_color ?? '')) !== '') {
                        $styles[] = 'background-color:' . trim((string) $settings->fav_team_color);
                    }
                    if ($styles !== []) {
                        $rowStyle = implode(';', $styles) . ';';
                    }
                }

                $useDecision = !empty($game->alt_decision);
                $homeResultProperty = $useDecision ? 'team1_result_decision' : 'team1_result';
                $awayResultProperty = $useDecision ? 'team2_result_decision' : 'team2_result';
                $homeResult = isset($game->{$homeResultProperty}) ? (string) $game->{$homeResultProperty} : 'X';
                $awayResult = isset($game->{$awayResultProperty}) ? (string) $game->{$awayResultProperty} : 'X';

                $perspectiveProjectTeamId = (int) ($game->projectteam1_id ?? 0);
                $matchType = (int) ($this->config['type_matches'] ?? 0);
                if ($matchType === 2) {
                    $perspectiveProjectTeamId = (int) ($game->projectteam2_id ?? 0);
                } elseif ($matchType !== 1 && $clubId > 0 && (int) ($game->club2_id ?? 0) === $clubId) {
                    $perspectiveProjectTeamId = (int) ($game->projectteam2_id ?? 0);
                }
                ?>
                <tr<?php echo $rowStyle !== '' ? ' style="' . $this->escape($rowStyle) . '"' : ''; ?>>
                    <?php if (!empty($this->config['show_match_nr'])) : ?><td><?php echo $this->escape((string) ($game->match_number ?? '')); ?></td><?php endif; ?>
                    <td><?php echo MatchTimeHelper::format($game, $this->config, $this->overallconfig, $this->project); ?></td>
                    <?php if (!empty($this->config['show_time_present'])) : ?><td><?php echo $this->escape((string) ($game->time_present ?? '')); ?></td><?php endif; ?>
                    <?php if (!empty($this->config['show_league'])) : ?><td><?php echo $this->escape((string) ($game->l_name ?? '')); ?></td><?php endif; ?>
                    <td class="td_r"><?php echo $homeName; ?></td>
                    <?php if (!empty($this->config['show_club_logo'])) : ?>
                        <td><?php echo ClubLogoHelper::render(
                            (string) ($game->home_logo_small ?? ''),
                            (string) ($game->club1_country ?? ''),
                            1
                        ); ?></td>
                    <?php endif; ?>
                    <td>-</td>
                    <?php if (!empty($this->config['show_club_logo'])) : ?>
                        <td><?php echo ClubLogoHelper::render(
                            (string) ($game->away_logo_small ?? ''),
                            (string) ($game->club2_country ?? ''),
                            1
                        ); ?></td>
                    <?php endif; ?>
                    <td><?php echo $awayName; ?></td>

                    <?php if (!empty($this->config['show_referee'])) : ?>
                        <td>
                            <?php foreach ($this->matchReferees[$matchId] ?? [] as $referee) : ?>
                                <?php
                                $refereeLink = SiteRouteHelper::view('referee', $baseRoute + ['pid' => $referee->person_slug ?? $referee->id]);
                                $refereeName = NamePresentationHelper::person($referee, $this->config['referee_name_format'] ?? 0);
                                echo HTMLHelper::link($refereeLink, $refereeName) . '<br>';
                                ?>
                            <?php endforeach; ?>
                        </td>
                    <?php endif; ?>

                    <?php if (!empty($this->config['show_playground'])) : ?>
                        <td><?php echo HTMLHelper::link($playgroundLink, $this->escape((string) ($game->pl_name ?? ''))); ?></td>
                    <?php endif; ?>

                    <?php if (!empty($game->cancel)) : ?>
                        <td colspan="3" class="text-center"><?php echo $this->escape((string) ($game->cancel_reason ?? '')); ?></td>
                    <?php elseif (!empty($this->config['show_matchreport_link'])) : ?>
                        <td colspan="3" class="text-center"><?php echo HTMLHelper::link($matchreportLink, $this->escape($homeResult . '-' . $awayResult)); ?></td>
                    <?php else : ?>
                        <td class="text-center"><?php echo $this->escape($homeResult); ?></td>
                        <td class="text-center">-</td>
                        <td class="text-center"><?php echo $this->escape($awayResult); ?></td>
                    <?php endif; ?>

                    <?php if (!empty($this->config['show_thumbs_picture'])) : ?>
                        <td class="text-center"><?php echo MatchResultHelper::renderOutcomeIcon($game, $perspectiveProjectTeamId); ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
</div>
