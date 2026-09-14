<?php
/**
 * Native Joomla 5/6 player career statistics layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa https://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\PersonModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$config = is_array($this->config ?? null) ? $this->config : [];
$overallConfig = is_array($this->overallconfig ?? null) ? $this->overallconfig : [];
$history = is_array($this->historyPlayer ?? null) ? $this->historyPlayer : [];
$allEvents = is_array($this->AllEvents ?? null) ? $this->AllEvents : [];
$stats = is_array($this->stats ?? null) ? $this->stats : [];
$projectStats = is_array($this->projectstats ?? null) ? $this->projectstats : [];
$componentParams = ComponentHelper::getParams('com_sportsmanagement');
$iconPlaceholder = trim((string) $componentParams->get('ph_icon', 'images/com_sportsmanagement/database/placeholders/placeholder_21.png'));
$clubPlaceholder = trim((string) $componentParams->get('ph_logo_big', ''));
$teamPlaceholder = trim((string) $componentParams->get('ph_team', ''));
$eventImagePath = 'images/com_sportsmanagement/database/events';
$containerClass = $this->escape((string) ($this->divclassrow ?? ''));
$tableClass = $this->escape((string) ($config['player_table_class'] ?? 'table'));
$databaseSelector = (int) $this->input->getInt('cfg_which_database', 0);
$seasonId = (int) $this->input->getInt('s', 0);
$modalWidth = (int) ($this->modalwidth ?? 100);
$modalHeight = (int) ($this->modalheight ?? 200);
$modalMode = (int) ($overallConfig['use_jquery_modal'] ?? 0);
$zeroValue = $overallConfig['zero_events_value'] ?? 0;
$showTeam = !empty($config['show_plstats_team']);
$showPlayerPicture = !empty($config['show_plstats_ppicture']);
$showSubstitutions = !empty($config['show_substitution_stats']) && !empty($overallConfig['use_jl_substitution']);
$showCareerEvents = !empty($config['show_career_events_stats']);
$showCareerStats = !empty($config['show_career_stats']);
$showEventsAsSum = !empty($config['show_events_as_sum']);
$timeModel = $this->getModel();
$personModel = new PersonModel();
$personModel->setDatabaseSelector($databaseSelector);

$renderIcon = static function (string $path, string $title, array $attributes = []) use ($iconPlaceholder): string {
    $path = trim($path) !== '' ? $path : $iconPlaceholder;
    return HTMLHelper::image($path, $title, ['title' => $title] + $attributes);
};

$eventIcon = static function (object $eventType) use ($eventImagePath, $iconPlaceholder): string {
    $path = trim((string) ($eventType->icon ?? ''));
    if ($path !== '' && !str_contains($path, '/')) {
        $path = $eventImagePath . '/' . $path;
    }
    return $path !== '' ? $path : $iconPlaceholder;
};

$career = [
    'played' => 0,
    'started' => 0,
    'in' => 0,
    'out' => 0,
    'playedtime' => 0,
];
$leagueHistory = [];
$teamHistory = [];
$colspan = 1 + ($showTeam ? 1 : 0) + ($showPlayerPicture ? 1 : 0);
?>
<div class="<?php echo $containerClass; ?> table-responsive" id="playerstats">
    <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PERSONAL_STATISTICS'); ?></h2>

    <table class="<?php echo $tableClass; ?>" id="playerstatstable">
        <thead>
            <tr class="sectiontableheader">
                <th class="td_l nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
                <?php if ($showTeam) : ?>
                    <th class="td_l nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_TEAM'); ?></th>
                <?php endif; ?>
                <?php if ($showPlayerPicture) : ?>
                    <th class="td_l nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE'); ?></th>
                <?php endif; ?>
                <th class="td_c">
                    <?php echo $renderIcon($eventImagePath . '/played.png', Text::_('COM_SPORTSMANAGEMENT_PERSON_PLAYED')); ?>
                </th>
                <?php if ($showSubstitutions) : ?>
                    <th class="td_c"><?php echo $renderIcon($eventImagePath . '/startroster.png', Text::_('COM_SPORTSMANAGEMENT_PERSON_STARTROSTER')); ?></th>
                    <th class="td_c"><?php echo $renderIcon($eventImagePath . '/in.png', Text::_('COM_SPORTSMANAGEMENT_PERSON_IN')); ?></th>
                    <th class="td_c"><?php echo $renderIcon($eventImagePath . '/out.png', Text::_('COM_SPORTSMANAGEMENT_PERSON_OUT')); ?></th>
                    <th class="td_c"><?php echo $renderIcon($eventImagePath . '/uhr.png', Text::_('COM_SPORTSMANAGEMENT_PLAYED_TIME'), ['height' => 11]); ?></th>
                <?php endif; ?>
                <?php if ($showCareerEvents) : ?>
                    <?php foreach ($allEvents as $eventType) : ?>
                        <?php $eventName = Text::_((string) ($eventType->name ?? '')); ?>
                        <th class="td_c"><?php echo HTMLHelper::image($eventIcon($eventType), $eventName, ['title' => $eventName, 'align' => 'top', 'width' => 20, 'hspace' => '2']); ?></th>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if ($showCareerStats) : ?>
                    <?php foreach ($stats as $stat) : ?>
                        <?php if (!empty($stat) && $stat->showInPlayer()) : ?>
                            <th class="td_c"><?php echo $stat->getImage(); ?></th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $station) : ?>
                <?php
                $projectId = (int) ($station->project_id ?? 0);
                $projectTeamId = (int) ($station->ptid ?? 0);
                $teamPlayerId = (int) ($station->tpid ?? 0);
                $teamId = (int) ($station->team_id ?? 0);
                $leagueId = (int) ($station->league_id ?? 0);
                $projectName = (string) ($station->project_name ?? '');
                $teamName = (string) ($station->team_name ?? '');
                $leagueName = (string) ($station->league_name ?? '');

                $inOut = $timeModel->getInOutStats($projectId, $projectTeamId, $teamPlayerId);
                $played = (int) ($inOut->played ?? 0);
                $started = (int) ($inOut->started ?? 0);
                $subIn = (int) ($inOut->sub_in ?? $inOut->in ?? 0);
                $subOut = (int) ($inOut->sub_out ?? $inOut->out ?? 0);
                $timePlayed = $timeModel->getTimePlayed(
                    $teamPlayerId,
                    (int) ($station->game_regular_time ?? 0),
                    null,
                    $overallConfig['person_events'] ?? null,
                    $projectId,
                    (int) ($station->add_time ?? 0)
                );

                $playerLink = SiteRouteHelper::view('player', [
                    'cfg_which_database' => $databaseSelector,
                    's' => $seasonId,
                    'p' => (string) ($station->project_slug ?? ''),
                    'tid' => (string) ($station->team_slug ?? ''),
                    'pid' => (string) ($this->person->slug ?? $station->person_slug ?? ''),
                ]);
                $teamLink = SiteRouteHelper::view('teaminfo', [
                    'cfg_which_database' => $databaseSelector,
                    's' => $seasonId,
                    'p' => (string) ($station->project_slug ?? ''),
                    'tid' => (string) ($station->team_slug ?? ''),
                    'ptid' => 0,
                ]);

                $projectPicture = trim((string) ($station->project_picture ?? ''));
                if ($projectPicture === '') {
                    $projectPicture = $clubPlaceholder;
                } elseif (in_array($projectPicture, [
                    'images/com_sportsmanagement/database/placeholders/placeholder_150.png',
                    'images/com_sportsmanagement/database/placeholders/placeholder_450_2.png',
                ], true)) {
                    $projectPicture = trim((string) ($station->league_picture ?? '')) ?: $clubPlaceholder;
                }
                $clubPicture = trim((string) ($station->club_picture ?? '')) ?: $clubPlaceholder;
                $teamPicture = trim((string) ($station->team_picture ?? '')) ?: $teamPlaceholder;
                $seasonPicture = trim((string) ($station->season_picture ?? '')) ?: $teamPlaceholder;

                $career['played'] += $played;
                $career['started'] += $started;
                $career['in'] += $subIn;
                $career['out'] += $subOut;
                $career['playedtime'] += (int) $timePlayed;

                $leagueHistory[$leagueId] ??= [
                    'name' => $leagueName,
                    'picture' => $projectPicture,
                    'played' => 0,
                    'started' => 0,
                    'in' => 0,
                    'out' => 0,
                    'playedtime' => 0,
                    'events' => [],
                ];
                $leagueHistory[$leagueId]['played'] += $played;
                $leagueHistory[$leagueId]['started'] += $started;
                $leagueHistory[$leagueId]['in'] += $subIn;
                $leagueHistory[$leagueId]['out'] += $subOut;
                $leagueHistory[$leagueId]['playedtime'] += (int) $timePlayed;

                $teamHistory[$teamId] ??= [
                    'name' => $teamName,
                    'team_picture' => $teamPicture,
                    'club_picture' => $clubPicture,
                    'played' => 0,
                    'started' => 0,
                    'in' => 0,
                    'out' => 0,
                    'playedtime' => 0,
                    'events' => [],
                ];
                $teamHistory[$teamId]['played'] += $played;
                $teamHistory[$teamId]['started'] += $started;
                $teamHistory[$teamId]['in'] += $subIn;
                $teamHistory[$teamId]['out'] += $subOut;
                $teamHistory[$teamId]['playedtime'] += (int) $timePlayed;
                ?>
                <tr>
                    <td id="show_project_logo">
                        <?php
                        if (!empty($config['show_project_logo']) && $projectPicture !== '') {
                            echo ModalImageHelper::render(
                                'playerstatsproject' . $projectId . '-' . $teamId,
                                $projectPicture,
                                $projectName,
                                (int) ($config['project_logo_height'] ?? 20),
                                '',
                                $modalWidth,
                                $modalHeight,
                                $modalMode
                            );
                        }
                        echo HTMLHelper::link($playerLink, $this->escape($projectName));
                        ?>
                    </td>

                    <?php if ($showTeam) : ?>
                        <td id="show_plstats_team">
                            <?php
                            if (!empty($config['show_team_logo']) && $clubPicture !== '') {
                                echo ModalImageHelper::render(
                                    'playerstatsteam' . $projectId . '-' . $teamId,
                                    $clubPicture,
                                    $teamName,
                                    (int) ($config['team_logo_height'] ?? 20),
                                    '',
                                    $modalWidth,
                                    $modalHeight,
                                    $modalMode
                                );
                            }
                            if (!empty($config['show_team_picture']) && $teamPicture !== '') {
                                echo ModalImageHelper::render(
                                    'playerstatsteampicture' . $projectId . '-' . $teamId,
                                    $teamPicture,
                                    $teamName,
                                    (int) ($config['team_picture_height'] ?? 20),
                                    '',
                                    $modalWidth,
                                    $modalHeight,
                                    $modalMode
                                );
                            }
                            echo !empty($config['show_playerstats_teamlink'])
                                ? HTMLHelper::link($teamLink, $this->escape($teamName))
                                : $this->escape($teamName);
                            ?>
                        </td>
                    <?php endif; ?>

                    <?php if ($showPlayerPicture) : ?>
                        <td id="show_plstats_ppicture">
                            <?php
                            if ($seasonPicture !== '') {
                                echo ModalImageHelper::render(
                                    'playerstatsperson' . $projectId . '-' . $teamId,
                                    $seasonPicture,
                                    $teamName,
                                    (int) ($config['plstats_ppicture_height'] ?? 20),
                                    '',
                                    $modalWidth,
                                    $modalHeight,
                                    $modalMode
                                );
                            }
                            ?>
                        </td>
                    <?php endif; ?>

                    <td><?php echo $played > 0 ? $played : $this->escape((string) $zeroValue); ?></td>
                    <?php if ($showSubstitutions) : ?>
                        <td id="started-playerstats"><?php echo $started > 0 ? $started : $this->escape((string) $zeroValue); ?></td>
                        <td id="in-playerstats"><?php echo $subIn > 0 ? $subIn : $this->escape((string) $zeroValue); ?></td>
                        <td id="out-playerstats"><?php echo $subOut > 0 ? $subOut : $this->escape((string) $zeroValue); ?></td>
                        <td id="playedtime-playerstats"><?php echo (int) $timePlayed; ?></td>
                    <?php endif; ?>

                    <?php if ($showCareerEvents) : ?>
                        <?php foreach ($allEvents as $eventType) : ?>
                            <?php
                            $eventId = (int) ($eventType->id ?? 0);
                            $eventValue = $personModel->getPlayerEvents($eventId, $projectId, $projectTeamId, $showEventsAsSum ? 1 : 0);
                            $leagueHistory[$leagueId]['events'][$eventId] = (int) ($leagueHistory[$leagueId]['events'][$eventId] ?? 0) + $eventValue;
                            $teamHistory[$teamId]['events'][$eventId] = (int) ($teamHistory[$teamId]['events'][$eventId] ?? 0) + $eventValue;
                            ?>
                            <td data-ptid="<?php echo $projectTeamId; ?>" data-event-id="<?php echo $eventId; ?>" title="<?php echo $projectId; ?>"><?php echo $eventValue > 0 ? $eventValue : $this->escape((string) $zeroValue); ?></td>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if ($showCareerStats) : ?>
                        <?php foreach ($stats as $stat) : ?>
                            <?php if (!empty($stat) && $stat->showInPlayer()) : ?>
                                <?php $value = $projectStats[$stat->id][$projectId][$projectTeamId] ?? $zeroValue; ?>
                                <td class="hasTip" title="<?php echo $this->escape(Text::_((string) ($stat->name ?? ''))); ?>"><?php echo $value != 0 ? $this->escape((string) $value) : $this->escape((string) $zeroValue); ?></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>

            <tr class="career_stats_total">
                <td colspan="<?php echo $colspan; ?>"><b><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_CAREER_TOTAL'); ?></b></td>
                <td><?php echo $career['played']; ?></td>
                <?php if ($showSubstitutions) : ?>
                    <td id="started-career_stats_total"><?php echo $career['started'] > 0 ? $career['started'] : $this->escape((string) $zeroValue); ?></td>
                    <td id="in-career_stats_total"><?php echo $career['in'] > 0 ? $career['in'] : $this->escape((string) $zeroValue); ?></td>
                    <td id="out-career_stats_total"><?php echo $career['out'] > 0 ? $career['out'] : $this->escape((string) $zeroValue); ?></td>
                    <td id="playedtime-career_stats_total"><?php echo $career['playedtime']; ?></td>
                <?php endif; ?>
                <?php if ($showCareerEvents) : ?>
                    <?php foreach ($allEvents as $eventType) : ?>
                        <?php
                        $eventId = (int) ($eventType->id ?? 0);
                        $total = $history ? $personModel->getPlayerEvents($eventId, null, null, $showEventsAsSum ? 1 : 0) : 0;
                        ?>
                        <td id="career_stats_total-<?php echo $eventId; ?>"><?php echo $total > 0 ? $total : $this->escape((string) $zeroValue); ?></td>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if ($showCareerStats) : ?>
                    <?php foreach ($stats as $stat) : ?>
                        <?php if (!empty($stat) && $stat->showInPlayer()) : ?>
                            <?php $value = $projectStats[$stat->id]['totals'] ?? $zeroValue; ?>
                            <td title="<?php echo $this->escape(Text::_((string) ($stat->name ?? ''))); ?>"><?php echo $value != 0 ? $this->escape((string) $value) : $this->escape((string) $zeroValue); ?></td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tr>
        </tbody>
    </table>

    <?php if ($leagueHistory) : ?>
        <table class="<?php echo $tableClass; ?>" id="playerstatsleaguetable">
            <thead>
                <tr class="sectiontableheader">
                    <th class="td_l nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
                    <th class="td_c"><?php echo $renderIcon($eventImagePath . '/played.png', Text::_('COM_SPORTSMANAGEMENT_PERSON_PLAYED')); ?></th>
                    <?php if ($showSubstitutions) : ?>
                        <th class="td_c"><?php echo $renderIcon($eventImagePath . '/startroster.png', Text::_('COM_SPORTSMANAGEMENT_PERSON_STARTROSTER')); ?></th>
                        <th class="td_c"><?php echo $renderIcon($eventImagePath . '/in.png', Text::_('COM_SPORTSMANAGEMENT_PERSON_IN')); ?></th>
                        <th class="td_c"><?php echo $renderIcon($eventImagePath . '/out.png', Text::_('COM_SPORTSMANAGEMENT_PERSON_OUT')); ?></th>
                        <th class="td_c"><?php echo $renderIcon($eventImagePath . '/uhr.png', Text::_('COM_SPORTSMANAGEMENT_PLAYED_TIME'), ['height' => 11]); ?></th>
                    <?php endif; ?>
                    <?php foreach ($allEvents as $eventType) : ?>
                        <?php $eventName = Text::_((string) ($eventType->name ?? '')); ?>
                        <th class="td_c"><?php echo HTMLHelper::image($eventIcon($eventType), $eventName, ['title' => $eventName, 'align' => 'top', 'width' => 20, 'hspace' => '2']); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leagueHistory as $leagueId => $league) : ?>
                    <tr>
                        <td class="td_l nowrap">
                            <?php
                            if (!empty($config['show_project_logo']) && $league['picture'] !== '') {
                                echo ModalImageHelper::render(
                                    'playerstatsleague' . (int) $leagueId,
                                    (string) $league['picture'],
                                    (string) $league['name'],
                                    (int) ($config['project_logo_height'] ?? 20),
                                    '',
                                    $modalWidth,
                                    $modalHeight,
                                    $modalMode
                                );
                            }
                            echo $this->escape((string) $league['name']);
                            ?>
                        </td>
                        <td class="td_l nowrap"><?php echo (int) $league['played']; ?></td>
                        <?php if ($showSubstitutions) : ?>
                            <td class="td_l nowrap"><?php echo (int) $league['started']; ?></td>
                            <td class="td_l nowrap"><?php echo (int) $league['in']; ?></td>
                            <td class="td_l nowrap"><?php echo (int) $league['out']; ?></td>
                            <td class="td_l nowrap"><?php echo (int) $league['playedtime']; ?></td>
                        <?php endif; ?>
                        <?php foreach ($allEvents as $eventType) : ?>
                            <?php $eventId = (int) ($eventType->id ?? 0); ?>
                            <td class="td_c"><?php echo (int) ($league['events'][$eventId] ?? 0); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($teamHistory) : ?>
        <table class="<?php echo $tableClass; ?>" id="playerstatsteamtable">
            <thead>
                <tr class="sectiontableheader">
                    <th class="td_l nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_TEAM'); ?></th>
                    <th class="td_c"><?php echo $renderIcon($eventImagePath . '/played.png', Text::_('COM_SPORTSMANAGEMENT_PERSON_PLAYED')); ?></th>
                    <?php if ($showSubstitutions) : ?>
                        <th class="td_c"><?php echo $renderIcon($eventImagePath . '/startroster.png', Text::_('COM_SPORTSMANAGEMENT_PERSON_STARTROSTER')); ?></th>
                        <th class="td_c"><?php echo $renderIcon($eventImagePath . '/in.png', Text::_('COM_SPORTSMANAGEMENT_PERSON_IN')); ?></th>
                        <th class="td_c"><?php echo $renderIcon($eventImagePath . '/out.png', Text::_('COM_SPORTSMANAGEMENT_PERSON_OUT')); ?></th>
                        <th class="td_c"><?php echo $renderIcon($eventImagePath . '/uhr.png', Text::_('COM_SPORTSMANAGEMENT_PLAYED_TIME'), ['height' => 11]); ?></th>
                    <?php endif; ?>
                    <?php foreach ($allEvents as $eventType) : ?>
                        <?php $eventName = Text::_((string) ($eventType->name ?? '')); ?>
                        <th class="td_c"><?php echo HTMLHelper::image($eventIcon($eventType), $eventName, ['title' => $eventName, 'align' => 'top', 'width' => 20, 'hspace' => '2']); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teamHistory as $teamId => $team) : ?>
                    <tr>
                        <td class="td_l nowrap">
                            <?php
                            if (!empty($config['show_team_logo']) && $team['club_picture'] !== '') {
                                echo ModalImageHelper::render(
                                    'playerstatsclubsummary' . (int) $teamId,
                                    (string) $team['club_picture'],
                                    (string) $team['name'],
                                    (int) ($config['team_logo_height'] ?? 20),
                                    '',
                                    $modalWidth,
                                    $modalHeight,
                                    $modalMode
                                );
                            }
                            if (!empty($config['show_team_picture']) && $team['team_picture'] !== '') {
                                echo ModalImageHelper::render(
                                    'playerstatsteamsummary' . (int) $teamId,
                                    (string) $team['team_picture'],
                                    (string) $team['name'],
                                    (int) ($config['team_picture_height'] ?? 20),
                                    '',
                                    $modalWidth,
                                    $modalHeight,
                                    $modalMode
                                );
                            }
                            echo $this->escape((string) $team['name']);
                            ?>
                        </td>
                        <td class="td_l nowrap"><?php echo (int) $team['played']; ?></td>
                        <?php if ($showSubstitutions) : ?>
                            <td class="td_l nowrap"><?php echo (int) $team['started']; ?></td>
                            <td class="td_l nowrap"><?php echo (int) $team['in']; ?></td>
                            <td class="td_l nowrap"><?php echo (int) $team['out']; ?></td>
                            <td class="td_l nowrap"><?php echo (int) $team['playedtime']; ?></td>
                        <?php endif; ?>
                        <?php foreach ($allEvents as $eventType) : ?>
                            <?php $eventId = (int) ($eventType->id ?? 0); ?>
                            <td class="td_c"><?php echo (int) ($team['events'][$eventId] ?? 0); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
