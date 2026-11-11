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

/**
 * Render team names and their frontend navigation actions without legacy helpers.
 */
final class TeamPresentationHelper
{
    /**
     * Render a team name with configured logo, link and actions.
     * @param object $team Team display record.
     * @param string $containerPrefix Unique HTML element prefix.
     * @param array $config Team display options.
     * @param bool $isFavourite Whether the team is marked as favourite.
     * @param object|null $project Optional project metadata.
     * @param int $databaseSelector Component database selector.
     * @param int $seasonId Selected season.
     * @param string|null $link Optional destination URL.
     * @return string Team name HTML.
     */
    public static function formatName(
        object $team,
        string $containerPrefix,
        array $config,
        bool $isFavourite = false,
        ?object $project = null,
        int $databaseSelector = 0,
        int $seasonId = 0,
        ?string $link = null
    ): string {
        $resultsBelow = self::enabledSetting($config['results_below'] ?? null)
            && self::enabledSetting($config['show_logo_small'] ?? null);
        $javascriptFunction = $resultsBelow ? 'visibleMenu' : 'switchMenu';
        $container = $resultsBelow ? 'span' : 'div';
        $hiddenStyle = $resultsBelow ? 'visibility:hidden' : 'display:none';

        $showInfoLink = self::selectedOption($config['show_info_link'] ?? 0, [0, 1, 2], 0);
        $hasStandardAction = self::hasStandardAction($config);
        $showActions = ($showInfoLink === 2 && $isFavourite)
            || ($showInfoLink === 1 && $hasStandardAction);

        $teamId = self::positiveId($team->team_id ?? $team->id ?? null);
        $projectId = self::positiveId($team->project_id ?? null);
        $containerId = self::safeId($containerPrefix . 't' . $teamId . 'p' . $projectId);
        $description = self::nameMarkup($team, $config, $isFavourite, $project);

        if (!$showActions || $teamId <= 0 || $projectId <= 0) {
            return self::wrapLink($description, $link);
        }

        $onclick = $javascriptFunction . "('" . $containerId . "');return false;";
        $output = HTMLHelper::link('javascript:void(0);', $description, ['onclick' => $onclick]);
        $output .= '<' . $container
            . ' id="' . self::escape($containerId) . '"'
            . ' style="' . self::escape($hiddenStyle) . ';"'
            . ' class="rankingteam jsmeventsshowhide">';
        $output .= self::renderActions($team, $config, $databaseSelector, $seasonId);
        $output .= '</' . $container . '>';

        return self::wrapLink($output, $link);
    }

    /**
     * Render configured links for team, club and related views.
     * @param object $team Team record.
     * @param array $config Link visibility options.
     * @param int $databaseSelector Component database selector.
     * @param int $seasonId Selected season.
     * @return string Team action HTML, with malformed project-team slugs ignored.
     */
    public static function renderActions(
        object $team,
        array $config,
        int $databaseSelector = 0,
        int $seasonId = 0
    ): string {
        $teamId = self::positiveId($team->team_id ?? $team->id ?? null);
        $projectId = self::positiveId($team->project_id ?? null);

        if ($teamId <= 0 || $projectId <= 0) {
            return '';
        }

        $projectTeam = self::safeText($team->projectteam_slug ?? null);

        if ($projectTeam === '') {
            $projectTeam = self::safeText($team->projectteamid ?? null);
        }
        $clubId = self::positiveId($team->club_id ?? null);
        $divisionId = self::positiveId($team->division_id ?? null);
        $teamName = self::safeText($team->name ?? null);
        $common = [
            'cfg_which_database' => $databaseSelector === 1 ? 1 : 0,
            's' => max(0, $seasonId),
            'p' => $projectId,
        ];
        $actions = [];

        if (self::enabledSetting($config['show_team_link'] ?? null)) {
            $actions[] = self::action(
                'roster',
                $common + ['tid' => $teamId, 'ptid' => $projectTeam],
                'media/com_sportsmanagement/jl_images/user_32x32.png',
                Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_ROSTER_LINK') . ' ' . $teamName
            );
        }

        if (self::enabledSetting($config['show_alltime_team_link'] ?? null)) {
            $actions[] = self::action(
                'rosteralltime',
                $common + ['tid' => $teamId],
                'media/com_sportsmanagement/jl_images/team_icon.png',
                Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_ALLTIME_ROSTER_LINK') . ' ' . $teamName
            );
        }

        if (self::enabledSetting($config['show_plan_link'] ?? null)) {
            $actions[] = self::action(
                'teamplan',
                $common + [
                    'tid' => $teamId,
                    'division' => $divisionId,
                    'mode' => 0,
                    'ptid' => $projectTeam,
                ],
                'media/com_sportsmanagement/jl_images/cal_32x32.png',
                Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_TEAMPLAN_LINK') . ' ' . $teamName
            );
        }

        if (self::enabledSetting($config['show_curve_link'] ?? null)) {
            $actions[] = self::action(
                'curve',
                $common + ['tid1' => $teamId, 'tid2' => 0, 'division' => $divisionId],
                'media/com_sportsmanagement/jl_images/line_graph_32x32.png',
                Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_CURVE_LINK') . ' ' . $teamName
            );
        }

        if (self::enabledSetting($config['show_teaminfo_link'] ?? null)) {
            $actions[] = self::action(
                'teaminfo',
                $common + ['tid' => $teamId, 'ptid' => $projectTeam],
                'media/com_sportsmanagement/jl_images/workflow_32x32.png',
                Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_TEAMINFO_LINK') . ' ' . $teamName
            );
        }

        if (self::enabledSetting($config['show_club_link'] ?? null) && $clubId > 0) {
            $actions[] = self::action(
                'clubinfo',
                $common + ['cid' => $clubId],
                'media/com_sportsmanagement/jl_images/mail_32x32.png',
                Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_CLUBINFO_LINK') . ' ' . $teamName
            );
        }

        if (self::enabledSetting($config['show_teamstats_link'] ?? null)) {
            $actions[] = self::action(
                'teamstats',
                $common + ['tid' => $teamId],
                'media/com_sportsmanagement/jl_images/line_chart_32x32.png',
                Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_TEAMSTATS_LINK') . ' ' . $teamName
            );
        }

        if (self::enabledSetting($config['show_clubplan_link'] ?? null) && $clubId > 0) {
            $actions[] = self::action(
                'clubplan',
                $common + ['cid' => $clubId],
                'media/com_sportsmanagement/jl_images/clock_32x32.png',
                Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_CLUBPLAN_LINK') . ' ' . $teamName
            );
        }

        if (self::enabledSetting($config['show_rivals_link'] ?? null)) {
            $actions[] = self::action(
                'rivals',
                $common + ['tid' => $teamId],
                'media/com_sportsmanagement/jl_images/calculator_32x32.png',
                Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_RIVALS_LINK') . ' ' . $teamName
            );
        }

        $actions = array_values(array_filter($actions));

        return $actions ? '<ul class="list-inline">' . implode('', $actions) . '</ul>' : '';
    }

    /**
     * Build an escaped team name with favourite highlighting.
     * @param object $team Team record.
     * @param array $config Name format settings.
     * @param bool $isFavourite Whether this is a favourite team.
     * @param object|null $project Optional project metadata.
     * @return string Formatted team name HTML, falling back to available names
     *     and rejecting unsafe inline CSS colors from project settings.
     */
    private static function nameMarkup(
        object $team,
        array $config,
        bool $isFavourite,
        ?object $project
    ): string {
        $format = self::selectedOption($config['team_name_format'] ?? 2, [0, 1, 2], 2);
        $fullName = self::safeText($team->name ?? null);
        $shortName = self::safeText($team->short_name ?? null);
        $middleName = self::safeText($team->middle_name ?? null);
        $formattedName = match ($format) {
            0 => $shortName,
            1 => $middleName,
            default => $fullName,
        };

        if ($formattedName === '') {
            // Older imports may have only one of the three name fields.
            // Keep an available team name visible instead of a blank label.
            $formattedName = $fullName !== ''
                ? $fullName
                : ($middleName !== '' ? $middleName : $shortName);
        }

        $style = 'padding:2px;';
        if (self::enabledSetting($config['highlight_fav'] ?? null) && $isFavourite && $project) {
            if (self::safeText($project->fav_team_text_bold ?? null) !== '') {
                $style .= 'font-weight:bold;';
            }
            $textColor = self::safeColor($project->fav_team_text_color ?? null);
            if ($textColor !== '') {
                $style .= 'color:' . $textColor . ';';
            }
            $background = self::safeColor($project->fav_team_color ?? null);
            if ($background !== '') {
                $style .= 'background-color:' . $background . ';';
            }
        }

        // Only mark up an abbreviation when its expanded team name exists.
        // An empty title offers no accessible explanation for short-only data.
        if ($format === 0 && $shortName !== '' && $fullName !== '') {
            $name = '<abbr title="' . self::escape($fullName) . '">' . self::escape($shortName) . '</abbr>';
        } else {
            $name = self::escape($formattedName);
        }

        return '<span style="' . self::escape($style) . '">' . $name . '</span>';
    }

    /**
     * Check whether any standard team navigation action is enabled.
     * @param array $config Display settings.
     * @return bool Whether action links are enabled.
     */
    private static function hasStandardAction(array $config): bool
    {
        foreach ([
            'show_club_link',
            'show_team_link',
            'show_alltime_team_link',
            'show_curve_link',
            'show_plan_link',
            'show_teaminfo_link',
            'show_teamstats_link',
            'show_clubplan_link',
            'show_rivals_link',
        ] as $key) {
            if (self::enabledSetting($config[$key] ?? null)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render one linked action icon for a team view.
     * @param string $view Destination view.
     * @param array $parameters Route parameters.
     * @param string $icon Action icon path.
     * @param string $title Accessible title.
     * @return string Action item HTML.
     */
    private static function action(string $view, array $parameters, string $icon, string $title): string
    {
        $link = SiteRouteHelper::view($view, $parameters);
        $image = HTMLHelper::image(
            $icon,
            $title,
            ['title' => $title, 'width' => 20, 'height' => 20]
        );

        return '<li class="list-inline-item">' . HTMLHelper::link($link, $image, ['title' => $title]) . '</li>';
    }

    /**
     * Wrap a display string in an optional HTML link.
     * @param string $output Inner HTML.
     * @param string|null $link Optional destination URL.
     * @return string Linked HTML for safe destinations or unlinked content
     *     for unsafe or empty optional destinations.
     */
    private static function wrapLink(string $output, ?string $link): string
    {
        return $link !== null && self::safeLink($link)
            ? HTMLHelper::link($link, $output)
            : $output;
    }

    /**
     * Validate an optional team destination before passing it to Joomla HTML.
     *
     * Block executable schemes, protocol-relative targets, URL credentials,
     * whitespace, encoded schemes and backslashes while allowing routed Joomla
     * relative links or valid absolute HTTP(S) destinations.
     *
     * @param string $url Legacy optional team link.
     * @return bool Whether the destination is safe for anchor rendering.
     */
    private static function safeLink(string $url): bool
    {
        if ($url === '' || trim($url) !== $url
            || preg_match('/[\\x00-\\x20\\x7f]/', $url)
            || str_contains($url, chr(92))
            || str_starts_with($url, '//')) {
            return false;
        }

        if (preg_match('~^https?://~i', $url)) {
            $parts = parse_url($url);

            return filter_var($url, FILTER_VALIDATE_URL) !== false
                && is_array($parts)
                && !empty($parts['host'])
                && !isset($parts['user'])
                && !isset($parts['pass']);
        }

        if (str_starts_with($url, '#') || str_starts_with($url, '?')) {
            return false;
        }

        // Inspect the URL path before any legitimate query or fragment.
        // Encoded URL schemes and protocol-relative hosts can be hidden by
        // several rounds of percent encoding in imported template links.
        $path = explode('#', explode('?', $url, 2)[0], 2)[0];

        for ($depth = 0; $depth < 8; ++$depth) {
            if (str_starts_with($path, '//')
                || preg_match('~^[a-z][a-z0-9+.-]*:~i', $path)
                || (preg_match('/[\\x00-\\x1f\\x7f]/', $path) || str_starts_with($path, ' '))
                || str_contains($path, chr(92))) {
                return false;
            }

            $decoded = rawurldecode($path);

            if ($decoded === $path) {
                break;
            }

            $path = $decoded;
        }

        return !str_starts_with($path, '//')
            && preg_match('~^[a-z][a-z0-9+.-]*:~i', $path) !== 1
            && preg_match('/[\\x00-\\x1f\\x7f]/', $path) !== 1
            && !str_starts_with($path, ' ')
            && !str_contains($path, chr(92))
            && preg_match('/%[0-9a-f]{2}/i', $path) !== 1;
    }

    /**
     * Normalise a user-facing identifier to safe HTML ID characters.
     * @param string $value Proposed identifier.
     * @return string Safe element ID.
     */
    private static function safeId(string $value): string
    {
        return preg_replace('/[^A-Za-z0-9_-]/', '-', $value) ?: 'team-actions';
    }

    /**
     * Interpret a legacy Joomla toggle without accepting arbitrary arrays.
     *
     * Enabled fields use the explicit boolean true, integer one or string
     * "1". Other values must not unexpectedly activate navigation actions.
     *
     * @param mixed $value Raw module or view configuration toggle.
     * @return bool Whether the feature is explicitly enabled.
     */
    private static function enabledSetting(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1';
    }

    /**
     * Select a supported display setting without coercing malformed values.
     *
     * Legacy Joomla Registry settings may hold arrays, booleans or unexpected
     * numeric strings. Only known integer choices or their canonical decimal
     * strings can control the team format and optional action visibility.
     *
     * @param mixed $value Raw configuration option.
     * @param array<int,int> $allowed Explicitly supported choices.
     * @param int $fallback Default choice for malformed values.
     * @return int Supported option or its safe fallback.
     */
    private static function selectedOption(mixed $value, array $allowed, int $fallback): int
    {
        if (is_string($value) && preg_match('/^[0-9]+$/D', $value) === 1) {
            $number = filter_var($value, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0],
            ]);
            $value = $number === false ? null : $number;
        }

        return is_int($value) && in_array($value, $allowed, true) ? $value : $fallback;
    }

    /**
     * Parse a positive legacy entity ID without coercing arrays or booleans.
     *
     * Team, project, club and division identifiers must be positive integers.
     * Reject malformed numeric fragments and values larger than PHP_INT_MAX
     * rather than generating routes for accidentally coerced ID 1.
     *
     * @param mixed $value Raw database identifier.
     * @return int Valid positive identifier or zero for malformed input.
     */
    private static function positiveId(mixed $value): int
    {
        if (is_int($value)) {
            return max(0, $value);
        }

        if (!is_string($value) || preg_match('/^[0-9]+$/D', $value) !== 1) {
            return 0;
        }

        $digits = ltrim($value, '0');

        if ($digits === '') {
            return 0;
        }

        $id = filter_var($digits, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $id === false ? 0 : $id;
    }

    /**
     * Convert optional text columns to usable display text.
     *
     * Legacy imports and overrides can contain arrays or arbitrary objects
     * instead of the scalar values expected by Joomla templates. Discard
     * non-stringable values rather than triggering PHP 8 conversion warnings.
     *
     * @param mixed $value Raw team or project display value.
     * @return string Trimmed scalar or stringable text, or an empty string.
     */
    private static function safeText(mixed $value): string
    {
        if (!is_scalar($value) && !$value instanceof \Stringable) {
            return '';
        }

        return trim((string) $value);
    }

    /**
     * Validate a project-configured highlight color before using inline CSS.
     *
     * Only 3-, 4-, 6- or 8-digit hexadecimal CSS colors or simple
     * alphabetic named colors are permitted. Reject declaration separators and CSS functions so imported
     * project settings cannot inject additional style declarations.
     *
     * @param mixed $value Raw project foreground or background color.
     * @return string Safe color token, or an empty string for invalid input.
     */
    private static function safeColor(mixed $value): string
    {
        $color = self::safeText($value);

        return preg_match('/^(?:\\#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})|[a-zA-Z]{1,20})$/D', $color) === 1
            ? $color
            : '';
    }

    /**
     * Escape text for safe HTML output.
     * @param string $value Untrusted display text.
     * @return string Escaped text.
     */
    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
