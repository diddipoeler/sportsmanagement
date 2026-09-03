<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$sportType = $statistics['sportstype'] ?? null;
$counts = $statistics['counts'] ?? [];

if (($counts['projects'] ?? 0) === 0) {
    echo '<p class="modjlgsports">' . Text::_('MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_NO_PROJECTS') . '</p>';
    return;
}

$rows = [
    ['show_project', 'projects', 'MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_PROJECTS', 'administrator/components/com_sportsmanagement/assets/images/projects.png'],
    ['show_leagues', 'leagues', 'MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_LEAGUES', 'administrator/components/com_sportsmanagement/assets/images/leagues.png'],
    ['show_seasons', 'seasons', 'MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_SEASONS', 'administrator/components/com_sportsmanagement/assets/images/seasons.png'],
    ['show_playgrounds', 'playgrounds', (string) $params->get('text_playgrounds', 'MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_PLAYGROUNDS'), 'administrator/components/com_sportsmanagement/assets/images/playground.png'],
    ['show_clubs', 'clubs', (string) $params->get('text_clubs', 'MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_CLUBS'), 'administrator/components/com_sportsmanagement/assets/images/clubs.png'],
    ['show_teams', 'teams', (string) $params->get('text_teams', 'MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_TEAMS'), 'administrator/components/com_sportsmanagement/assets/images/teams.png'],
    ['show_players', 'players', (string) $params->get('text_players', 'MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_PLAYERS'), 'administrator/components/com_sportsmanagement/assets/images/persons.png'],
    ['show_divisions', 'divisions', (string) $params->get('text_divisions', 'MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_DIVISIONS'), 'administrator/components/com_sportsmanagement/assets/images/division.png'],
    ['show_rounds', 'rounds', (string) $params->get('text_rounds', 'MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_ROUNDS'), 'administrator/components/com_sportsmanagement/assets/images/icon-16-Matchdays.png'],
    ['show_matches', 'matches', (string) $params->get('text_matches', 'MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_MATCHES'), 'administrator/components/com_sportsmanagement/assets/images/matches.png'],
    ['show_player_events', 'player_events', (string) $params->get('text_player_events', 'MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_PLAYER_EVENTS'), 'administrator/components/com_sportsmanagement/assets/images/events.png'],
    ['show_player_stats', 'player_stats', (string) $params->get('text_player_stats', 'MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_PLAYER_STATS'), 'administrator/components/com_sportsmanagement/assets/images/icon-48-statistics.png'],
];
?>
<div class="<?= htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($sportType) : ?>
        <h4>
            <?php if (!empty($sportType->icon)) : ?>
                <img src="<?= htmlspecialchars(Uri::root() . ltrim((string) $sportType->icon, '/'), ENT_QUOTES, 'UTF-8') ?>"
                     alt=""
                     width="<?= (int) $params->get('sportstypes_picture_width', 40) ?>">
            <?php endif; ?>
            <?= htmlspecialchars(Text::_((string) $sportType->name), ENT_QUOTES, 'UTF-8') ?>
        </h4>
    <?php endif; ?>

    <ul class="list-group">
        <?php foreach ($rows as [$showParam, $countKey, $labelKey, $icon]) : ?>
            <?php if ((int) $params->get($showParam, 1) !== 1 || !array_key_exists($countKey, $counts)) { continue; } ?>
            <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                <span>
                    <?php if ((int) $params->get('show_icon', 1) === 1) : ?>
                        <img src="<?= htmlspecialchars(Uri::root() . $icon, ENT_QUOTES, 'UTF-8') ?>"
                             alt="<?= htmlspecialchars(Text::_($labelKey), ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>
                    <?= htmlspecialchars(Text::_($labelKey), ENT_QUOTES, 'UTF-8') ?>
                </span>
                <span class="badge bg-secondary rounded-pill"><?= (int) $counts[$countKey] ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
