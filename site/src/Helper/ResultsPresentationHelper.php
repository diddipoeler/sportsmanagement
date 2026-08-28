<?php
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

    private static function position(object $referee): string
    {
        $position = trim((string) ($referee->position_name ?? ''));
        return $position !== '' ? ' (' . self::escape(Text::_($position)) . ')' : '';
    }

    private static function value(mixed $value): string
    {
        return $value === null || $value === '' ? '-' : self::escape((string) $value);
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
