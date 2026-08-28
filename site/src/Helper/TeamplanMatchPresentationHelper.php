<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 match presentation for the team-plan layouts.
 */
final class TeamplanMatchPresentationHelper
{
    public static function renderDivision(
        object $homeTeam,
        ?object $awayTeam,
        array $config,
        int $databaseSelector,
        int $seasonId,
        ?object $project
    ): string {
        $teams = [$homeTeam];
        if ($awayTeam) {
            $teams[] = $awayTeam;
        }
        if (!empty($config['switch_home_guest'])) {
            $teams = array_reverse($teams);
        }

        $labels = [];
        $seen = [];
        $nameProperty = ((string) ($config['show_division_name'] ?? 'name') === 'shortname')
            ? 'division_shortname'
            : 'division_name';

        foreach ($teams as $team) {
            $divisionId = (int) ($team->division_id ?? 0);
            if ($divisionId <= 0 || isset($seen[$divisionId])) {
                continue;
            }
            $seen[$divisionId] = true;
            $label = trim((string) ($team->{$nameProperty} ?? ''));
            if ($label === '') {
                continue;
            }

            if (!empty($config['show_division_link'])) {
                $label = HTMLHelper::link(
                    SiteRouteHelper::view('ranking', [
                        'cfg_which_database' => $databaseSelector,
                        's' => $seasonId,
                        'p' => (string) ($project->slug ?? $team->project_slug ?? $team->project_id ?? ''),
                        'type' => 0,
                        'division' => (string) ($team->division_slug ?? $divisionId),
                    ]),
                    self::escape($label)
                );
            } else {
                $label = self::escape($label);
            }
            $labels[] = $label;
        }

        return $labels !== []
            ? implode(self::escape((string) ($config['spacer'] ?? '/')), $labels)
            : '&nbsp;';
    }

    public static function renderPlayground(
        object $match,
        object $homeTeam,
        array $config,
        int $databaseSelector,
        int $seasonId,
        ?object $project
    ): string {
        if (empty($config['show_playground']) && empty($config['show_playground_alert'])) {
            return '';
        }

        $playgroundId = (int) ($match->playground_id ?? 0);
        $standardId = (int) ($homeTeam->standard_playground ?? 0);
        $name = trim((string) ($match->playground_name ?? ''));
        $shortName = trim((string) ($match->playground_short_name ?? ''));
        $slug = trim((string) ($match->playground_slug ?? ''));

        if ($playgroundId <= 0 && $standardId > 0) {
            $playgroundId = $standardId;
            $name = trim((string) ($homeTeam->playground_name ?? ''));
            $shortName = trim((string) ($homeTeam->playground_short_name ?? ''));
            $slug = (string) $playgroundId;
        }

        if ($playgroundId <= 0) {
            return '-';
        }

        if (empty($config['show_playground']) && !empty($config['show_playground_alert']) && $playgroundId === $standardId) {
            return '-';
        }

        $label = ((string) ($config['show_playground_name'] ?? 'name') === 'name') ? $name : $shortName;
        if ($label === '') {
            $label = $name !== '' ? $name : $shortName;
        }
        if ($label === '') {
            $label = (string) $playgroundId;
        }

        $isChanged = $standardId > 0 && $playgroundId !== $standardId;
        $alertMode = (int) ($config['show_playground_alert'] ?? 0);
        $prefix = $isChanged && $alertMode === 2
            ? '<strong class="text-danger">' . self::escape(Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_NEWS')) . ':</strong> '
            : '';
        $labelHtml = self::escape($label);
        if ($isChanged && $alertMode === 1) {
            $labelHtml = '<strong class="text-danger">' . $labelHtml . '</strong>';
        }

        $link = SiteRouteHelper::view('playground', [
            'cfg_which_database' => $databaseSelector,
            's' => $seasonId,
            'p' => (string) ($project->slug ?? $match->project_slug ?? ''),
            'pgid' => $slug !== '' ? $slug : $playgroundId,
        ]);

        return $prefix . HTMLHelper::link(
            $link,
            $labelHtml,
            ['title' => $isChanged ? Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_NEWS') : Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_MATCH')]
        );
    }

    public static function renderScore(
        object $match,
        array $config,
        int $databaseSelector,
        int $seasonId,
        ?object $project
    ): string {
        if (!empty($match->cancel)) {
            return self::escape(Text::_((string) ($match->cancel_reason ?? '')));
        }

        $separator = trim((string) ($config['seperator'] ?? '-'));
        $separator = $separator !== '' ? $separator : '-';
        $switch = !empty($config['switch_home_guest']);
        $home = $match->team1_result ?? null;
        $away = $match->team2_result ?? null;
        $left = $switch ? $away : $home;
        $right = $switch ? $home : $away;

        $result = self::escape(self::resultValue($left))
            . '&nbsp;' . self::escape($separator) . '&nbsp;'
            . self::escape(self::resultValue($right));

        $matchResultType = (int) ($match->match_result_type ?? 0);
        if ($matchResultType === 1) {
            $result .= ' (' . self::escape(Text::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME')) . ')';
        } elseif ($matchResultType === 2) {
            $result .= ' (' . self::escape(Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT')) . ')';
        }

        $details = self::partResults($match, $switch, $separator);
        if ($details !== []) {
            if (!empty($config['show_part_results'])) {
                foreach ($details as $detail) {
                    $result .= '<br><span>' . self::escape($detail) . '</span>';
                }
            } else {
                $result = '<span title="' . self::escape(implode(' | ', $details)) . '">' . $result . '</span>';
            }
        }

        if (!empty($match->alt_decision)) {
            $decisionLeft = $switch ? ($match->team2_result_decision ?? '') : ($match->team1_result_decision ?? '');
            $decisionRight = $switch ? ($match->team1_result_decision ?? '') : ($match->team2_result_decision ?? '');
            $result = '<strong class="text-danger">'
                . self::escape(self::resultValue($decisionLeft))
                . '&nbsp;' . self::escape($separator) . '&nbsp;'
                . self::escape(self::resultValue($decisionRight))
                . '</strong>';
        }

        $view = isset($match->team1_result) ? 'matchreport' : 'nextmatch';
        $matchReference = (string) ($match->match_slug ?? $match->id ?? 0);
        $link = SiteRouteHelper::view($view, [
            'cfg_which_database' => $databaseSelector,
            's' => $seasonId,
            'p' => (string) ($project->slug ?? $match->project_slug ?? ''),
            'mid' => $matchReference,
        ]);

        return HTMLHelper::link($link, $result);
    }

    public static function renderReferees(
        array $referees,
        array $config,
        bool $teamsAsReferees,
        int $databaseSelector,
        int $seasonId,
        ?object $project
    ): string {
        if ($referees === []) {
            return '-';
        }

        usort($referees, static function (object $a, object $b): int {
            $position = ((int) ($a->ordering ?? 0)) <=> ((int) ($b->ordering ?? 0));
            return $position !== 0
                ? $position
                : ((int) ($a->match_referee_ordering ?? 0)) <=> ((int) ($b->match_referee_ordering ?? 0));
        });

        $labels = [];
        foreach ($referees as $referee) {
            if ($teamsAsReferees) {
                $name = trim((string) ($referee->referee_name ?? ''));
                if ($name !== '') {
                    $labels[] = self::escape($name);
                }
                continue;
            }

            $person = (object) [
                'firstname' => (string) ($referee->referee_firstname ?? ''),
                'nickname' => (string) ($referee->referee_nickname ?? ''),
                'lastname' => (string) ($referee->referee_lastname ?? ''),
            ];
            $name = NamePresentationHelper::person($person, $config['referee_name_format'] ?? 0);
            if ($name === '') {
                continue;
            }

            if (!empty($config['show_referee_link'])) {
                $name = HTMLHelper::link(
                    SiteRouteHelper::view('referee', [
                        'cfg_which_database' => $databaseSelector,
                        's' => $seasonId,
                        'p' => (string) ($project->slug ?? ''),
                        'pid' => (string) ($referee->referee_slug ?? $referee->referee_id ?? 0),
                    ]),
                    $name
                );
            }

            $position = trim((string) ($referee->referee_position_name ?? ''));
            if ($position !== '') {
                $name = '<span title="' . self::escape(Text::_($position)) . '">' . $name . '</span>';
            }
            $labels[] = $name;
        }

        if ($labels === []) {
            return '-';
        }

        if ((int) ($config['show_referee'] ?? 1) === 2) {
            $plain = trim(strip_tags(implode(' | ', $labels)));
            return '<span title="' . self::escape($plain) . '">'
                . HTMLHelper::image(
                    'media/com_sportsmanagement/jl_images/icon-16-Referees.png',
                    Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_REFEREE'),
                    ['width' => 16, 'height' => 16]
                )
                . '</span>';
        }

        return implode('<br>', $labels);
    }

    public static function renderHistoryLink(
        object $match,
        int $databaseSelector,
        int $seasonId,
        ?object $project
    ): string {
        $link = SiteRouteHelper::view('nextmatch', [
            'cfg_which_database' => $databaseSelector,
            's' => $seasonId,
            'p' => (string) ($project->slug ?? $match->project_slug ?? ''),
            'mid' => (string) ($match->match_slug ?? $match->id ?? 0),
        ]);

        return HTMLHelper::link(
            $link,
            HTMLHelper::image(
                'components/com_sportsmanagement/assets/images/history-icon-png--21.png',
                Text::_('COM_SPORTSMANAGEMENT_HISTORY'),
                ['width' => 20, 'height' => 20, 'title' => Text::_('COM_SPORTSMANAGEMENT_HISTORY')]
            )
        );
    }

    public static function renderMatchReportLink(
        object $match,
        array $config,
        int $databaseSelector,
        int $seasonId,
        ?object $project
    ): string {
        $hasResult = isset($match->team1_result);
        $view = $hasResult ? 'matchreport' : 'nextmatch';
        $textKey = $hasResult
            ? 'COM_SPORTSMANAGEMENT_TEAMPLAN_VIEW_MATCHREPORT'
            : 'COM_SPORTSMANAGEMENT_TEAMPLAN_VIEW_MATCHPREVIEW';
        $image = $hasResult
            ? (string) ($config['matchreport_image'] ?? '')
            : (string) ($config['matchpreview_image'] ?? '');
        $label = !empty($config['show_matchreport_image']) && $image !== ''
            ? HTMLHelper::image($image, Text::_($textKey))
            : self::escape(Text::_($textKey));

        return HTMLHelper::link(
            SiteRouteHelper::view($view, [
                'cfg_which_database' => $databaseSelector,
                's' => $seasonId,
                'p' => (string) ($project->slug ?? $match->project_slug ?? ''),
                'mid' => (string) ($match->match_slug ?? $match->id ?? 0),
            ]),
            $label
        );
    }

    /** @return array<int, string> */
    private static function partResults(object $match, bool $switch, string $separator): array
    {
        $home = preg_split('/;/', (string) ($match->team1_result_split ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $away = preg_split('/;/', (string) ($match->team2_result_split ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $left = $switch ? $away : $home;
        $right = $switch ? $home : $away;
        $details = [];

        foreach ($left as $index => $value) {
            if (!array_key_exists($index, $right)) {
                continue;
            }
            $details[] = Text::sprintf('COM_SPORTSMANAGEMENT_NPART', (string) ($index + 1))
                . ': ' . self::resultValue($value) . ' ' . $separator . ' ' . self::resultValue($right[$index]);
        }

        if (isset($match->team1_result_ot) || isset($match->team2_result_ot)) {
            $otLeft = $switch ? ($match->team2_result_ot ?? '') : ($match->team1_result_ot ?? '');
            $otRight = $switch ? ($match->team1_result_ot ?? '') : ($match->team2_result_ot ?? '');
            if ($otLeft !== '' || $otRight !== '') {
                $details[] = Text::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME2') . ': '
                    . self::resultValue($otLeft) . ' ' . $separator . ' ' . self::resultValue($otRight);
            }
        }
        if (isset($match->team1_result_so) || isset($match->team2_result_so)) {
            $soLeft = $switch ? ($match->team2_result_so ?? '') : ($match->team1_result_so ?? '');
            $soRight = $switch ? ($match->team1_result_so ?? '') : ($match->team2_result_so ?? '');
            if ($soLeft !== '' || $soRight !== '') {
                $details[] = Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT2') . ': '
                    . self::resultValue($soLeft) . ' ' . $separator . ' ' . self::resultValue($soRight);
            }
        }

        return $details;
    }

    private static function resultValue(mixed $value): string
    {
        return $value === null || $value === '' ? '-' : (string) $value;
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
