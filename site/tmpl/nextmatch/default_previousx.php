<?php
/**
 * Joomla 5/6 previous-match presentation for the next-match view.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\MatchResultHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\TeamLogoHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$databaseSelector = $this->input->getInt('cfg_which_database', 0);
$seasonId = $this->input->getInt('s', 0);
$tableClass = (string) ($this->config['hystory_table_class'] ?? 'table');
$modalMode = (int) ($this->overallconfig['use_jquery_modal'] ?? 0);
?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="nextmatch-previous">
    <?php foreach ($this->teams as $currentTeam) : ?>
        <?php
        $currentProjectTeamId = (int) ($currentTeam->projectteamid ?? 0);
        $previousMatches = $this->previousx[$currentProjectTeamId] ?? [];

        if (!$previousMatches) {
            continue;
        }

        $this->notes = [
            Text::sprintf('COM_SPORTSMANAGEMENT_NEXTMATCH_PREVIOUS', (string) ($currentTeam->name ?? ''))
                . ' ' . $this->newmatchtext,
        ];
        echo $this->loadTemplate('jsm_notes');
        ?>

        <table class="table">
            <tr>
                <td>
                    <table class="<?php echo htmlspecialchars($tableClass, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php foreach ($previousMatches as $game) : ?>
                            <?php
                            $resultLink = SiteRouteHelper::view('results', [
                                'cfg_which_database' => $databaseSelector,
                                's' => $seasonId,
                                'p' => $game->project_slug,
                                'r' => $game->round_slug,
                                'division' => 0,
                                'mode' => 0,
                                'order' => '',
                                'layout' => '',
                            ]);
                            $reportLink = SiteRouteHelper::view('matchreport', [
                                'cfg_which_database' => $databaseSelector,
                                's' => $seasonId,
                                'p' => $game->project_slug,
                                'mid' => $game->match_slug,
                            ]);

                            $home = $this->allteams[(int) ($game->projectteam1_id ?? 0)] ?? null;
                            $away = $this->allteams[(int) ($game->projectteam2_id ?? 0)] ?? null;

                            if (!$home || !$away) {
                                continue;
                            }

                            $homePicture = (object) [
                                'name' => (string) ($home->name ?? ''),
                                'picture' => (string) ($game->home_picture ?? ''),
                            ];
                            $awayPicture = (object) [
                                'name' => (string) ($away->name ?? ''),
                                'picture' => (string) ($game->away_picture ?? ''),
                            ];
                            ?>
                            <tr>
                                <td><?php echo HTMLHelper::link($resultLink, (string) ($game->roundcode ?? '')); ?></td>
                                <td class="nowrap">
                                    <?php echo HTMLHelper::date($game->match_date, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE')); ?>
                                </td>
                                <td><?php echo substr((string) ($game->match_date ?? ''), 11, 5); ?></td>
                                <td class="nowrap"><?php echo htmlspecialchars((string) ($home->name ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="nowrap">
                                    <?php
                                    echo TeamLogoHelper::render(
                                        $homePicture,
                                        'nextmatchprev' . (int) ($game->id ?? 0) . '-' . (int) ($game->projectteam1_id ?? 0),
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
                                        'nextmatchprev' . (int) ($game->id ?? 0) . '-' . (int) ($game->projectteam2_id ?? 0),
                                        true,
                                        20,
                                        $this->modalwidth,
                                        $this->modalheight,
                                        $modalMode
                                    );
                                    ?>
                                </td>
                                <td class="nowrap"><?php echo htmlspecialchars((string) ($away->name ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="nowrap"><?php echo isset($game->team1_result) ? $game->team1_result : ''; ?></td>
                                <td class="nowrap"><?php echo htmlspecialchars((string) ($this->overallconfig['seperator'] ?? ':'), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="nowrap"><?php echo isset($game->team2_result) ? $game->team2_result : ''; ?></td>
                                <td class="nowrap">
                                    <?php if ((int) ($game->show_report ?? 0) === 1) : ?>
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
                                <?php if (!empty($this->config['show_thumbs_picture'])) : ?>
                                    <td><?php echo MatchResultHelper::renderOutcomeIcon($game, $currentProjectTeamId); ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </td>
            </tr>
        </table>
    <?php endforeach; ?>
</div>
