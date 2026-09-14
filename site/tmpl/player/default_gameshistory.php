<?php
/**
 * Native Joomla 5/6 player games history layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa https://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$games = is_array($this->games ?? null) ? $this->games : [];

if (!$games) {
    return;
}

$config = is_array($this->config ?? null) ? $this->config : [];
$overallConfig = is_array($this->overallconfig ?? null) ? $this->overallconfig : [];
$allEvents = is_array($this->AllEvents ?? null) ? $this->AllEvents : [];
$gamesStats = is_array($this->gamesstats ?? null) ? $this->gamesstats : [];
$gamesEvents = is_array($this->gamesevents ?? null) ? $this->gamesevents : [];
$teams = is_array($this->teams ?? null) ? $this->teams : [];
$eventImagePath = 'images/com_sportsmanagement/database/events';
$containerClass = $this->escape((string) ($this->divclassrow ?? ''));
$tableClass = $this->escape((string) ($config['history_table_class'] ?? 'table'));
$databaseSelector = (int) $this->input->getInt('cfg_which_database', 0);
$seasonId = (int) $this->input->getInt('s', 0);
$modalWidth = (int) ($this->modalwidth ?? 100);
$modalHeight = (int) ($this->modalheight ?? 200);
$modalMode = (int) ($overallConfig['use_jquery_modal'] ?? 0);
$zeroValue = $overallConfig['zero_events_value'] ?? 0;
$showSubstitutions = !empty($config['show_substitution_stats']) && !empty($overallConfig['use_jl_substitution']);
$showCareerEvents = !empty($config['show_career_events_stats']);
$showCareerStats = !empty($config['show_career_stats']);
$isGolfBillard = (string) ($this->project->sport_type_name ?? '') === 'COM_SPORTSMANAGEMENT_ST_GOLF_BILLARD';
$model = $this->getModel();
?>
<div class="<?php echo $containerClass; ?> table-responsive" id="playergameshistory">
    <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_GAMES_HISTORY'); ?></h2>
    <table class="<?php echo $tableClass; ?>">
        <tr>
            <td>
                <table id="playergameshistorytable" class="<?php echo $tableClass; ?>">
                    <thead>
                        <tr>
                            <th colspan="6" id="playergames"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_GAMES'); ?></th>
                            <?php if ($showSubstitutions) : ?>
                                <th id="playerstartroster">
                                    <?php
                                    $imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_STARTROSTER');
                                    $picture = $eventImagePath . ($isGolfBillard ? '/golfbillard.png' : '/startroster.png');
                                    echo HTMLHelper::image($picture, $imageTitle, ['title' => $imageTitle] + ($isGolfBillard ? ['width' => 40] : []));
                                    ?>
                                </th>
                                <?php if (!$isGolfBillard) : ?>
                                    <th id="playerin">
                                        <?php
                                        $imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_IN');
                                        echo HTMLHelper::image($eventImagePath . '/in.png', $imageTitle, ['title' => $imageTitle]);
                                        ?>
                                    </th>
                                    <th id="playerout">
                                        <?php
                                        $imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_OUT');
                                        echo HTMLHelper::image($eventImagePath . '/out.png', $imageTitle, ['title' => $imageTitle]);
                                        ?>
                                    </th>
                                    <th id="playertime">
                                        <?php
                                        $imageTitle = Text::_('COM_SPORTSMANAGEMENT_PLAYED_TIME');
                                        echo HTMLHelper::image($eventImagePath . '/uhr.png', $imageTitle, ['title' => $imageTitle, 'height' => 11]);
                                        ?>
                                    </th>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($showCareerEvents) : ?>
                                <?php foreach ($allEvents as $eventType) : ?>
                                    <?php
                                    $iconPath = (string) ($eventType->icon ?? '');
                                    if ($iconPath !== '' && !str_contains($iconPath, '/')) {
                                        $iconPath = $eventImagePath . '/' . $iconPath;
                                    }
                                    $eventName = Text::_((string) ($eventType->name ?? ''));
                                    ?>
                                    <th><?php echo HTMLHelper::image($iconPath, $eventName, ['title' => $eventName, 'align' => 'top', 'width' => 30, 'hspace' => '2']); ?></th>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if ($showCareerStats) : ?>
                                <?php foreach ($gamesStats as $stat) : ?>
                                    <?php if (!empty($stat)) : ?>
                                        <?php try { ?>
                                            <?php if ($stat->showInPlayer()) : ?>
                                                <th><?php echo $stat->getImage(); ?></th>
                                            <?php endif; ?>
                                        <?php } catch (\Throwable $e) {
                                            $this->app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . (string) $stat), 'error');
                                        } ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (!empty($config['show_player_market_value'])) : ?>
                                <th class="td_c" id="playermarketvalue"><?php echo Text::_('COM_SPORTSMANAGEMENT_EURO_MARKET_VALUE'); ?></th>
                            <?php endif; ?>
                            <?php if (!empty($config['show_player_market_text'])) : ?>
                                <th class="td_c" id="playermarkettext"><?php echo Text::_('COM_SPORTSMANAGEMENT_MARKET_TEXT'); ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totals = ['startRoster' => 0, 'in' => 0, 'out' => 0, 'playedtime' => 0];
                        $eventTotals = [];
                        ?>
                        <?php foreach ($games as $game) : ?>
                            <?php
                            $gameId = (int) ($game->id ?? 0);
                            $homeProjectTeamId = (int) ($game->projectteam1_id ?? 0);
                            $awayProjectTeamId = (int) ($game->projectteam2_id ?? 0);
                            $currentProjectTeamId = (int) ($game->projectteam_id ?? 0);
                            $homeTeam = $teams[$homeProjectTeamId] ?? null;
                            $awayTeam = $teams[$awayProjectTeamId] ?? null;

                            $reportLink = SiteRouteHelper::view('matchreport', [
                                'cfg_which_database' => $databaseSelector,
                                's' => $seasonId,
                                'p' => (string) ($this->project->slug ?? ''),
                                'mid' => (string) ($game->match_slug ?? ''),
                            ]);
                            $homeLink = SiteRouteHelper::view('teaminfo', [
                                'cfg_which_database' => $databaseSelector,
                                's' => $seasonId,
                                'p' => (string) ($this->project->slug ?? ''),
                                'tid' => (string) ($homeTeam->team_slug ?? ''),
                                'ptid' => 0,
                            ]);
                            $awayLink = SiteRouteHelper::view('teaminfo', [
                                'cfg_which_database' => $databaseSelector,
                                's' => $seasonId,
                                'p' => (string) ($this->project->slug ?? ''),
                                'tid' => (string) ($awayTeam->team_slug ?? ''),
                                'ptid' => 0,
                            ]);

                            $personEvents = $overallConfig['person_events'] ?? null;
                            $regularTime = (int) ($this->project->game_regular_time ?? 0);
                            if (!empty($game->match_result_type)) {
                                $regularTime += (int) ($this->project->add_time ?? 0);
                            }
                            $timePlayed = $model->getTimePlayed(
                                (int) ($game->teamplayer_id ?? 0),
                                $regularTime,
                                $gameId,
                                $personEvents,
                                (int) ($game->project_id ?? 0),
                                0
                            );

                            $date = Factory::getDate((string) ($game->match_date ?? 'now'));
                            $date->setTimezone(new \DateTimeZone((string) ($this->project->timezone ?? 'UTC')));
                            $dateText = $date->format((string) ($config['games_date_format'] ?? 'Y-m-d H:i'));
                            $homeName = (string) ($homeTeam->name ?? $game->home_name ?? '');
                            $awayName = (string) ($awayTeam->name ?? $game->away_name ?? '');
                            ?>
                            <tr>
                                <td id="playergamesdate"><?php echo HTMLHelper::link($reportLink, $this->escape($dateText)); ?></td>
                                <td class="<?php echo $currentProjectTeamId === $homeProjectTeamId ? 'playerteam' : ''; ?>" id="playergameshometeam">
                                    <?php
                                    echo ModalImageHelper::render(
                                        'gameshistory' . $gameId . '-' . $homeProjectTeamId,
                                        (string) ($game->home_logo ?? ''),
                                        (string) ($game->home_name ?? $homeName),
                                        20,
                                        '',
                                        $modalWidth,
                                        $modalHeight,
                                        $modalMode
                                    );
                                    echo !empty($config['show_gameshistory_teamlink'])
                                        ? HTMLHelper::link($homeLink, $this->escape($homeName))
                                        : $this->escape($homeName);
                                    ?>
                                </td>
                                <td id="playergameshomeresult"><?php echo $this->escape((string) ($game->team1_result ?? '')); ?></td>
                                <td id="playergamesseperator"><?php echo $this->escape((string) ($overallConfig['seperator'] ?? ':')); ?></td>
                                <td id="playergamesawayresult"><?php echo $this->escape((string) ($game->team2_result ?? '')); ?></td>
                                <td class="<?php echo $currentProjectTeamId === $awayProjectTeamId ? 'playerteam' : ''; ?>" id="playergamesawayteam">
                                    <?php
                                    echo ModalImageHelper::render(
                                        'gameshistory' . $gameId . '-' . $awayProjectTeamId,
                                        (string) ($game->away_logo ?? ''),
                                        (string) ($game->away_name ?? $awayName),
                                        20,
                                        '',
                                        $modalWidth,
                                        $modalHeight,
                                        $modalMode
                                    );
                                    echo !empty($config['show_gameshistory_teamlink'])
                                        ? HTMLHelper::link($awayLink, $this->escape($awayName))
                                        : $this->escape($awayName);
                                    ?>
                                </td>

                                <?php if ($showSubstitutions) : ?>
                                    <?php $totals['startRoster'] += (int) ($game->started ?? 0); ?>
                                    <td id="startRoster-gameshistory"><?php echo !empty($game->started) ? (int) $game->started : $this->escape((string) $zeroValue); ?></td>
                                    <?php if (!$isGolfBillard) : ?>
                                        <?php
                                        $totals['in'] += (int) ($game->sub_in ?? 0);
                                        $totals['out'] += (int) ($game->sub_out ?? 0);
                                        $totals['playedtime'] += (int) $timePlayed;
                                        ?>
                                        <td id="in-gameshistory"><?php echo !empty($game->sub_in) ? (int) $game->sub_in : $this->escape((string) $zeroValue); ?></td>
                                        <td id="out-gameshistory"><?php echo !empty($game->sub_out) ? (int) $game->sub_out : $this->escape((string) $zeroValue); ?></td>
                                        <td id="playedtime-gameshistory"><?php echo (int) $timePlayed; ?></td>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if ($showCareerEvents) : ?>
                                    <?php foreach ($allEvents as $eventType) : ?>
                                        <?php
                                        $eventId = (int) ($eventType->id ?? 0);
                                        $eventValue = (int) ($gamesEvents[$gameId][$eventId] ?? 0);
                                        $eventTotals[$eventId] = (int) ($eventTotals[$eventId] ?? 0) + $eventValue;
                                        ?>
                                        <td id="<?php echo $this->escape((string) ($eventType->name ?? '')); ?>"><?php echo $eventValue > 0 ? $eventValue : $this->escape((string) $zeroValue); ?></td>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if ($showCareerStats) : ?>
                                    <?php foreach ($gamesStats as $stat) : ?>
                                        <?php if (!empty($stat) && $stat->showInPlayer()) : ?>
                                            <?php $value = isset($stat->gamesstats[$gameId]) ? $stat->gamesstats[$gameId]->value : $zeroValue; ?>
                                            <td class="hasTip" title="<?php echo $this->escape((string) ($stat->name ?? '')); ?>"><?php echo $this->escape((string) $value); ?></td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if (!empty($config['show_player_market_value'])) : ?>
                                    <td class="hasTip" title="<?php echo $this->escape(number_format((float) ($game->market_value ?? 0), 0, ',', '.')); ?>"></td>
                                <?php endif; ?>
                                <?php if (!empty($config['show_player_market_text'])) : ?>
                                    <td class="hasTip" title="<?php echo $this->escape((string) ($game->market_text ?? '')); ?>"></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>

                        <tr class="career_stats_total">
                            <td colspan="6"><b><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_GAMES_TOTAL'); ?></b></td>
                            <?php if ($showSubstitutions) : ?>
                                <td><?php echo $totals['startRoster'] > 0 ? $totals['startRoster'] : $this->escape((string) $zeroValue); ?></td>
                                <?php if (!$isGolfBillard) : ?>
                                    <td><?php echo $totals['in'] > 0 ? $totals['in'] : $this->escape((string) $zeroValue); ?></td>
                                    <td><?php echo $totals['out'] > 0 ? $totals['out'] : $this->escape((string) $zeroValue); ?></td>
                                    <td><?php echo $totals['playedtime']; ?></td>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($showCareerEvents) : ?>
                                <?php foreach ($allEvents as $eventType) : ?>
                                    <?php $eventId = (int) ($eventType->id ?? 0); ?>
                                    <td><?php echo (int) ($eventTotals[$eventId] ?? 0); ?></td>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if ($showCareerStats) : ?>
                                <?php foreach ($gamesStats as $stat) : ?>
                                    <?php if (!empty($stat) && $stat->showInPlayer() && isset($stat->gamesstats['totals'])) : ?>
                                        <?php $value = $stat->gamesstats['totals']->value; ?>
                                        <td class="hasTip" title="<?php echo $this->escape((string) ($stat->name ?? '')); ?>"><?php echo $value > 0 ? $this->escape((string) $value) : $this->escape((string) $zeroValue); ?></td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</div>
