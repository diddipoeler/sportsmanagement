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
        $resultsBelow = !empty($config['results_below']) && !empty($config['show_logo_small']);
        $javascriptFunction = $resultsBelow ? 'visibleMenu' : 'switchMenu';
        $container = $resultsBelow ? 'span' : 'div';
        $hiddenStyle = $resultsBelow ? 'visibility:hidden' : 'display:none';

        $showInfoLink = (int) ($config['show_info_link'] ?? 0);
        $hasStandardAction = self::hasStandardAction($config);
        $showActions = ($showInfoLink === 2 && $isFavourite)
            || ($showInfoLink === 1 && $hasStandardAction);

        $teamId = (int) ($team->team_id ?? $team->id ?? 0);
        $projectId = (int) ($team->project_id ?? 0);
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
     * @return string Team action HTML.
     */
    public static function renderActions(
        object $team,
        array $config,
        int $databaseSelector = 0,
        int $seasonId = 0
    ): string {
        $teamId = (int) ($team->team_id ?? $team->id ?? 0);
        $projectId = (int) ($team->project_id ?? 0);

        if ($teamId <= 0 || $projectId <= 0) {
            return '';
        }

        $projectTeam = (string) ($team->projectteam_slug ?? $team->projectteamid ?? '');
        $clubId = (int) ($team->club_id ?? 0);
        $divisionId = (int) ($team->division_id ?? 0);
        $teamName = self::safeText($team->name ?? null);
        $common = [
            'cfg_which_database' => $databaseSelector === 1 ? 1 : 0,
            's' => max(0, $seasonId),
            'p' => $projectId,
        ];
        $actions = [];

        if (!empty($config['show_team_link'])) {
            $actions[] = self::action(
                'roster',
                $common + ['tid' => $teamId, 'ptid' => $projectTeam],
                'media/com_sportsmanagement/jl_images/user_32x32.png',
                Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_ROSTER_LINK') . ' ' . $teamName
            );
        }

        if (!empty($config['show_alltime_team_link'])) {
            $actions[] = self::action(
                'rosteralltime',
                $common + ['tid' => $teamId],
                'media/com_sportsmanagement/jl_images/team_icon.png',
                Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_ALLTIME_ROSTER_LINK') . ' ' . $teamName
            );
        }

        if (!empty($config['show_plan_link'])) {
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

        if (!empty($config['show_curve_link'])) {
            $actions[] = self::action(
                'curve',
                $common + ['tid1' => $teamId, 'tid2' => 0, 'division' => $divisionId],
                'media/com_sportsmanagement/jl_images/line_graph_32x32.png',
                Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_CURVE_LINK') . ' ' . $teamName
            );
        }

        if (!empty($config['show_teaminfo_link'])) {
            $actions[] = self::action(
                'teaminfo',
                $common + ['tid' => $teamId, 'ptid' => $projectTeam],
                'media/com_sportsmanagement/jl_images/workflow_32x32.png',
                Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_TEAMINFO_LINK') . ' ' . $teamName
            );
        }

        if (!empty($config['show_club_link']) && $clubId > 0) {
            $actions[] = self::action(
                'clubinfo',
                $common + ['cid' => $clubId],
                'media/com_sportsmanagement/jl_images/mail_32x32.png',
                Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_CLUBINFO_LINK') . ' ' . $teamName
            );
        }

        if (!empty($config['show_teamstats_link'])) {
            $actions[] = self::action(
                'teamstats',
                $common + ['tid' => $teamId],
                'media/com_sportsmanagement/jl_images/line_chart_32x32.png',
                Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_TEAMSTATS_LINK') . ' ' . $teamName
            );
        }

        if (!empty($config['show_clubplan_link']) && $clubId > 0) {
            $actions[] = self::action(
                'clubplan',
                $common + ['cid' => $clubId],
                'media/com_sportsmanagement/jl_images/clock_32x32.png',
                Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_CLUBPLAN_LINK') . ' ' . $teamName
            );
        }

        if (!empty($config['show_rivals_link'])) {
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
     * @return string Formatted team name HTML, ignoring malformed legacy name fields
     *     and rejecting unsafe inline CSS colors from project settings.
     */
    private static function nameMarkup(
        object $team,
        array $config,
        bool $isFavourite,
        ?object $project
    ): string {
        $format = (int) ($config['team_name_format'] ?? 2);
        $fullName = self::safeText($team->name ?? null);
        $shortName = self::safeText($team->short_name ?? null);
        $middleName = self::safeText($team->middle_name ?? null);
        $formattedName = match ($format) {
            0 => $shortName,
            1 => $middleName,
            default => $fullName,
        };

        if ($formattedName === '') {
            $formattedName = $fullName;
        }

        $style = 'padding:2px;';
        if (!empty($config['highlight_fav']) && $isFavourite && $project) {
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

        if ($format === 0 && $shortName !== '') {
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
            if (!empty($config[$key])) {
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
     * @return string Linked or unlinked HTML.
     */
    private static function wrapLink(string $output, ?string $link): string
    {
        return $link !== null && $link !== '' ? HTMLHelper::link($link, $output) : $output;
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
