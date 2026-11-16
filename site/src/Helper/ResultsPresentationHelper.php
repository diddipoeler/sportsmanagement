<?php
/**
 * Joomla 5/6 SportsManagement site helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** Native presentation helpers for the regular results view. */
final class ResultsPresentationHelper
{
    /** @return array<string, array<int, object>> */
    public static function groupByDate(array $matches): array
    {
        $dates = [];
        foreach ($matches as $match) {
            $date = substr((string) ($match->match_date ?? ''), 0, 10);
            $dates[$date !== '' ? $date : '0000-00-00'][] = $match;
        }

        return $dates;
    }

    /**
     * Render the requested combination of team logo and country flag.
     * Maps eight legacy display modes to the shared image and country helpers while retaining image targets and modal dimensions.
     * @param object $team Team record with country and logo fields.
     * @param int $type Icon display mode from the template settings.
     * @param string $target Image/modal target identifier.
     * @param int $modalWidth Modal window width.
     * @param int $modalHeight Modal window height.
     * @param int $modalMode Modal rendering mode.
     * @return string Team icon HTML or empty markup for an unknown mode.
     */
    public static function renderTeamIcon(
        object $team,
        int $type,
        string $target,
        int $modalWidth,
        int $modalHeight,
        int $modalMode = 0
    ): string {
        $flag = static fn (): string => CountryPresentationHelper::flag((string) ($team->country ?? ''));
        $logo = static function (string $property) use ($team, $target, $modalWidth, $modalHeight, $modalMode): string {
            return TeamLogoHelper::renderVariant(
                $team,
                $property,
                $target,
                20,
                $modalWidth,
                $modalHeight,
                $modalMode
            );
        };

        return match ($type) {
            1 => $logo('logo_small'),
            2 => $flag(),
            3 => $logo('logo_small') . ' ' . $flag(),
            4 => $flag() . ' ' . $logo('logo_small'),
            5 => $logo('logo_middle'),
            6 => $logo('logo_big'),
            7 => $logo('logo_big') . ' ' . $flag(),
            8 => $flag() . ' ' . $logo('logo_big'),
            default => '',
        };
    }

    /**
     * Format a team label for the current results view.
     * Delegates to the shared name presenter with favourite highlighting, project/season context and selected SportsManagement database.
     * @param object $team Team record.
     * @param string $prefix Template identifier prefix.
     * @param array $config Name-format configuration.
     * @param bool $isFavourite Whether the team is a favourite.
     * @param object|null $project Current project, when available.
     * @param int $databaseSelector Selected component database.
     * @param int $seasonId Current season ID.
     * @return string Formatted team name markup.
     */
    public static function renderTeamName(
        object $team,
        string $prefix,
        array $config,
        bool $isFavourite,
        ?object $project,
        int $databaseSelector,
        int $seasonId
    ): string {
        return TeamPresentationHelper::formatName(
            $team,
            $prefix,
            $config,
            $isFavourite,
            $project,
            $databaseSelector,
            $seasonId
        );
    }

    /**
     * Generate the match result cell and its optional report link.
     * Handles cancellations, swapped teams, awarded results, partial scores, overtime, shootouts and favourite-dependent link settings.
     * @param object $match Match data including result and routing fields.
     * @param array $config Results presentation options.
     * @param bool $hasFavourite Whether the match features a favourite.
     * @param int $databaseSelector Selected component database.
     * @param int $seasonId Current season ID.
     * @param object|null $project Optional project used to generate links.
     * @return string HTML-safe score/details markup and optional report link.
     */
    public static function renderScore(
        object $match,
        array $config,
        bool $hasFavourite,
        int $databaseSelector,
        int $seasonId,
        ?object $project
    ): string {
        if (!empty($match->cancel)) {
            return self::escape(Text::_((string) ($match->cancel_reason ?? '')));
        }

        $separator = trim((string) ($config['seperator'] ?? '-')) ?: '-';
        $switch = !empty($config['switch_home_guest']);
        $home = $switch ? ($match->team2_result ?? null) : ($match->team1_result ?? null);
        $away = $switch ? ($match->team1_result ?? null) : ($match->team2_result ?? null);
        $score = self::value($home) . '&nbsp;' . self::escape($separator) . '&nbsp;' . self::value($away);

        if (!empty($match->alt_decision)) {
            $homeDecision = $switch ? ($match->team2_result_decision ?? '') : ($match->team1_result_decision ?? '');
            $awayDecision = $switch ? ($match->team1_result_decision ?? '') : ($match->team2_result_decision ?? '');
            $score = '<strong class="text-danger">'
                . self::value($homeDecision) . '&nbsp;' . self::escape($separator) . '&nbsp;' . self::value($awayDecision)
                . '</strong>';
        }

        $details = [];
        if (!empty($config['show_part_results'])) {
            $left = preg_split('/;/', (string) ($switch ? ($match->team2_result_split ?? '') : ($match->team1_result_split ?? '')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $right = preg_split('/;/', (string) ($switch ? ($match->team1_result_split ?? '') : ($match->team2_result_split ?? '')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            foreach ($left as $index => $value) {
                if (array_key_exists($index, $right)) {
                    $details[] = self::escape((string) $value) . '&nbsp;' . self::escape($separator) . '&nbsp;' . self::escape((string) $right[$index]);
                }
            }
        }

        $otHome = $switch ? ($match->team2_result_ot ?? null) : ($match->team1_result_ot ?? null);
        $otAway = $switch ? ($match->team1_result_ot ?? null) : ($match->team2_result_ot ?? null);
        if ($otHome !== null || $otAway !== null) {
            $details[] = self::escape(Text::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME')) . ': '
                . self::value($otHome) . '&nbsp;' . self::escape($separator) . '&nbsp;' . self::value($otAway);
        }

        $soHome = $switch ? ($match->team2_result_so ?? null) : ($match->team1_result_so ?? null);
        $soAway = $switch ? ($match->team1_result_so ?? null) : ($match->team2_result_so ?? null);
        if ($soHome !== null || $soAway !== null) {
            $details[] = self::escape(Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT')) . ': '
                . self::value($soHome) . '&nbsp;' . self::escape($separator) . '&nbsp;' . self::value($soAway);
        }

        if ($details !== []) {
            $score .= '<br><small>' . implode('<br>', $details) . '</small>';
        } elseif ((int) ($match->match_result_type ?? 0) === 1) {
            $score .= ' (' . self::escape(Text::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME')) . ')';
        } elseif ((int) ($match->match_result_type ?? 0) === 2) {
            $score .= ' (' . self::escape(Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT')) . ')';
        }

        $linkMode = (int) ($config['show_link_matchreport'] ?? 0);
        if ($linkMode === 0 || ($linkMode === 2 && !$hasFavourite)) {
            return $score;
        }

        $view = isset($match->team1_result) ? 'matchreport' : 'nextmatch';
        $link = SiteRouteHelper::view($view, [
            'cfg_which_database' => $databaseSelector,
            's' => $seasonId,
            'p' => (string) ($project->slug ?? $match->project_slug ?? $match->project_id ?? ''),
            'mid' => (string) ($match->slug ?? $match->id ?? 0),
        ]);

        return HTMLHelper::link(
            $link,
            '<span class="score0">' . $score . '</span>',
            ['title' => Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOW_MATCHREPORT')]
        );
    }

    /**
     * Render the officials assigned to the match.
     * Supports referee teams and persons, translated positions, formatted names, optional profile links and compact tooltip icon mode.
     * @param array $referees Official or officiating-team records.
     * @param bool $teamsAsReferees Whether records represent teams.
     * @param array $config Referee name and link settings.
     * @param int $databaseSelector Selected component database.
     * @param int $seasonId Current season ID.
     * @param object|null $project Optional project for profile URLs.
     * @return string Official labels, tooltip icon or a non-breaking space.
     */
    public static function renderReferees(
        array $referees,
        bool $teamsAsReferees,
        array $config,
        int $databaseSelector,
        int $seasonId,
        ?object $project
    ): string {
        if ($referees === []) {
            return '&nbsp;';
        }

        $labels = [];
        foreach ($referees as $referee) {
            if ($teamsAsReferees) {
                $name = trim((string) ($referee->teamname ?? ''));
                if ($name !== '') {
                    $labels[] = self::escape($name) . self::position($referee);
                }
                continue;
            }

            $person = (object) [
                'firstname' => (string) ($referee->firstname ?? ''),
                'nickname' => (string) ($referee->nickname ?? ''),
                'lastname' => (string) ($referee->lastname ?? ''),
            ];
            $name = NamePresentationHelper::person($person, $config['referee_name_format'] ?? $config['name_format'] ?? 0);
            if ($name === '') {
                continue;
            }

            if (!empty($config['show_referee_link'])) {
                $name = HTMLHelper::link(
                    SiteRouteHelper::view('referee', [
                        'cfg_which_database' => $databaseSelector,
                        's' => $seasonId,
                        'p' => (string) ($project->slug ?? $project->id ?? ''),
                        'pid' => (string) ($referee->person_slug ?? $referee->id ?? 0),
                    ]),
                    $name
                );
            }

            $labels[] = $name . self::position($referee);
        }

        if ($labels === []) {
            return '&nbsp;';
        }

        if ((int) ($config['show_referee'] ?? 1) === 2) {
            $plain = trim(strip_tags(implode(' | ', $labels)));
            return '<span title="' . self::escape($plain) . '">'
                . HTMLHelper::image(
                    'media/com_sportsmanagement/jl_images/icon-16-Referees.png',
                    Text::_('COM_SPORTSMANAGEMENT_RESULTS_REF_TOOLTIP'),
                    ['width' => 16, 'height' => 16]
                )
                . '</span>';
        }

        return implode('<br>', $labels);
    }

    /**
     * List project teams that do not appear in published round matches.
     * Collects the participating project-team IDs, then renders missing teams with configured icons, labels and favourite presentation.
     * @param array $matches Matches in the selected round.
     * @param array $teams Project teams to consider.
     * @param array $config Results display settings.
     * @param array $favoriteTeamIds Favourite team IDs.
     * @param object|null $project Optional project context.
     * @param int $databaseSelector Selected component database.
     * @param int $seasonId Current season ID.
     * @param int $modalWidth Team image modal width.
     * @param int $modalHeight Team image modal height.
     * @param int $modalMode Modal rendering mode.
     * @return string Translated non-playing-teams HTML or empty string.
     */
    public static function renderNotPlayingTeams(
        array $matches,
        array $teams,
        array $config,
        array $favoriteTeamIds,
        ?object $project,
        int $databaseSelector,
        int $seasonId,
        int $modalWidth,
        int $modalHeight,
        int $modalMode
    ): string {
        $playing = [];
        foreach ($matches as $match) {
            if (empty($match->published)) {
                continue;
            }
            foreach ([(int) ($match->projectteam1_id ?? 0), (int) ($match->projectteam2_id ?? 0)] as $projectTeamId) {
                if ($projectTeamId > 0) {
                    $playing[$projectTeamId] = true;
                }
            }
        }

        $missing = [];
        foreach ($teams as $team) {
            $projectTeamId = (int) ($team->projectteamid ?? 0);
            if ($projectTeamId <= 0 || isset($playing[$projectTeamId])) {
                continue;
            }

            $parts = [];
            if (!empty($config['show_logo_small']) && !empty($config['show_dnp_teams_icons'])) {
                $parts[] = self::renderTeamIcon(
                    $team,
                    (int) $config['show_logo_small'],
                    'results-dnp-' . $projectTeamId,
                    $modalWidth,
                    $modalHeight,
                    $modalMode
                );
            }
            $parts[] = self::renderTeamName(
                $team,
                'results-dnp-',
                $config,
                in_array((int) ($team->id ?? $team->team_id ?? 0), $favoriteTeamIds, true),
                $project,
                $databaseSelector,
                $seasonId
            );
            $missing[] = implode(' ', array_filter($parts));
        }

        if ($missing === []) {
            return '';
        }

        return '<strong>' . Text::sprintf('COM_SPORTSMANAGEMENT_RESULTS_TEAMS_NOT_PLAYING', count($missing)) . '</strong> '
            . implode(', ', $missing);
    }

    /**
     * Format an optional referee position next to the name.
     * Translates the stored position name, escapes it for HTML and adds parentheses only when a position was supplied.
     * @param object $referee Record with an optional position_name.
     * @return string Position suffix or empty string.
     */
    private static function position(object $referee): string
    {
        $position = trim((string) ($referee->position_name ?? ''));
        return $position !== '' ? ' (' . self::escape(Text::_($position)) . ')' : '';
    }

    /**
     * Turn a match score field into safe display text.
     * Uses a dash for missing results and escapes all other scalar score values before combining them with HTML markup.
     * @param mixed $value Score, decision or other result value.
     * @return string Safe score value or placeholder.
     */
    private static function value(mixed $value): string
    {
        return $value === null || $value === '' ? '-' : self::escape((string) $value);
    }

    /**
     * HTML-encode a results label or user-supplied value.
     * Encodes both quote types using UTF-8 so generated results markup cannot accidentally interpret the value as HTML.
     * @param string $value Plain text to encode.
     * @return string Escaped HTML text.
     */
    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
