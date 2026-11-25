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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Native Joomla 5/6 team presentation for the team-plan layouts.
 */
final class TeamplanTeamPresentationHelper
{
    /**
     * Render the selected team name format with escaped text, optional favourite-team styling and an optional link. Add an accessible toggle and contextual information links when enabled by the team-plan configuration.
     * @param object $team Team display record.
     * @param string $containerPrefix Prefix for the information container identifier.
     * @param array $config Team-plan display options.
     * @param bool $isFavorite Whether the team is a favourite.
     * @param string|null $link Optional team destination.
     * @param int $databaseSelector SportsManagement database selector.
     * @param int $seasonId Season identifier.
     * @param object|null $project Optional favourite-team styling context.
     * @return string Team name and optional information markup.
     */
    public static function renderName(
        object $team,
        string $containerPrefix,
        array $config,
        bool $isFavorite,
        ?string $link,
        int $databaseSelector,
        int $seasonId,
        ?object $project = null
    ): string {
        $teamName = self::teamName($team, (int) ($config['team_name_format'] ?? 2));
        $style = 'padding:2px;';

        if (!empty($config['highlight_fav']) && $isFavorite && $project) {
            if (!empty($project->fav_team_text_bold)) {
                $style .= 'font-weight:bold;';
            }
            $textColor = trim((string) ($project->fav_team_text_color ?? ''));
            if ($textColor !== '') {
                $style .= 'color:' . self::cssValue($textColor) . ';';
            }
            $background = trim((string) ($project->fav_team_color ?? ''));
            if ($background !== '') {
                $style .= 'background-color:' . self::cssValue($background) . ';';
            }
        }

        $description = '<span style="' . self::escape($style) . '">';
        if ((int) ($config['team_name_format'] ?? 2) === 0 && trim((string) ($team->short_name ?? '')) !== '') {
            $description .= '<abbr title="' . self::escape((string) ($team->name ?? '')) . '">'
                . self::escape((string) $team->short_name) . '</abbr>';
        } else {
            $description .= self::escape($teamName);
        }
        $description .= '</span>';

        $output = $link !== null && $link !== '' ? HTMLHelper::link($link, $description) : $description;
        $showIcons = (
            ((int) ($config['show_info_link'] ?? 0) === 2 && $isFavorite)
            || ((int) ($config['show_info_link'] ?? 0) === 1 && self::hasInfoIcons($config))
        );

        if (!$showIcons) {
            return $output;
        }

        $containerId = self::safeId($containerPrefix . 't' . (int) ($team->id ?? 0) . 'p' . (int) ($team->project_id ?? 0));
        $useVisibility = !empty($config['results_below']) && !empty($config['show_logo_small']);
        $element = $useVisibility ? 'span' : 'div';
        $hiddenStyle = $useVisibility ? 'visibility:hidden' : 'display:none';
        $toggleMode = $useVisibility ? 'visibility' : 'display';
        $toggleTitle = Text::_('JGLOBAL_MORE_DETAILS');
        $toggle = '<a href="#" role="button" class="ms-1 teamplan-team-info-toggle"'
            . ' title="' . self::escape($toggleTitle) . '"'
            . ' aria-controls="' . self::escape($containerId) . '" aria-expanded="false"'
            . ' data-jsm-teamplan-toggle data-jsm-teamplan-target="' . self::escape($containerId) . '"'
            . ' data-jsm-teamplan-mode="' . $toggleMode . '">'
            . '<span class="fa fa-info-circle" aria-hidden="true"></span><span class="visually-hidden">'
            . self::escape($toggleTitle) . '</span></a>';

        return $output . $toggle
            . '<' . $element . ' id="' . $containerId . '" style="' . $hiddenStyle
            . ';" class="rankingteam jsmeventsshowhide">'
            . self::renderInfoIcons($team, $config, $databaseSelector, $seasonId)
            . '</' . $element . '>';
    }

    /**
     * Render a country flag, team photograph or selected club logo according to the visual mode. Use the configured image fallback and modal helper for supported logo modes.
     * @param object $team Team display record.
     * @param int $mode Visual display mode.
     * @param int $matchId Match identifier used in the modal identifier.
     * @param int $modalWidth Modal width.
     * @param int $modalHeight Modal height.
     * @param int $modalMode Modal display mode.
     * @param int $teamPictureWidth Team photograph width.
     * @return string Visual markup or an empty string.
     */
    public static function renderVisual(
        object $team,
        int $mode,
        int $matchId,
        int $modalWidth,
        int $modalHeight,
        int $modalMode,
        int $teamPictureWidth = 40
    ): string {
        if ($mode === 2) {
            return CountryPresentationHelper::flag((string) ($team->country ?? $team->club_country ?? ''));
        }

        if ($mode === 3) {
            $picture = self::resolvePicture(
                (string) ($team->picture ?? $team->team_picture ?? ''),
                'ph_team'
            );

            return $picture !== ''
                ? HTMLHelper::image(
                    $picture,
                    (string) ($team->name ?? ''),
                    ['width' => max(1, $teamPictureWidth), 'height' => 'auto']
                )
                : '';
        }

        $property = match ($mode) {
            1 => 'logo_small',
            5 => 'logo_middle',
            6 => 'logo_big',
            default => '',
        };

        if ($property === '') {
            return '';
        }

        $placeholder = match ($property) {
            'logo_big' => 'ph_logo_big',
            'logo_middle' => 'ph_logo_medium',
            default => 'ph_logo_small',
        };
        $picture = self::resolvePicture((string) ($team->{$property} ?? ''), $placeholder);
        if ($picture === '') {
            return '';
        }

        return ModalImageHelper::render(
            'teamplan-' . $matchId . '-team-' . (int) ($team->id ?? $team->team_id ?? 0) . '-' . $property,
            $picture,
            (string) ($team->name ?? ''),
            20,
            '',
            $modalWidth,
            $modalHeight,
            $modalMode
        );
    }

    /**
     * Build the enabled roster, schedule, statistics and related team or club links using the current project, season and database context. Render each destination as a labelled image link.
     * @param object $team Team routing and display record.
     * @param array $config Enabled information links.
     * @param int $databaseSelector Database selector carried into routes.
     * @param int $seasonId Season identifier.
     * @return string Information icon list or an empty string.
     */
    private static function renderInfoIcons(
        object $team,
        array $config,
        int $databaseSelector,
        int $seasonId
    ): string {
        $projectTeamSlug = (string) ($team->projectteam_slug ?? $team->projectteamid ?? 0);
        $teamId = (int) ($team->team_id ?? $team->id ?? 0);
        $teamSlug = (string) ($team->team_slug ?? $teamId);
        $clubSlug = (string) ($team->club_slug ?? $team->club_id ?? 0);
        $divisionSlug = (string) ($team->division_slug ?? $team->division_id ?? 0);
        $projectSlug = (string) ($team->project_slug ?? $team->project_id ?? 0);
        $teamName = (string) ($team->name ?? '');
        $icons = [];

        if (!empty($config['show_team_link'])) {
            $icons[] = ['roster', ['p' => $projectSlug, 'tid' => $teamSlug, 'ptid' => $projectTeamSlug], 'COM_SPORTSMANAGEMENT_TEAMICONS_ROSTER_LINK', 'media/com_sportsmanagement/jl_images/user_32x32.png'];
        }
        if (!empty($config['show_alltime_team_link'])) {
            $icons[] = ['rosteralltime', ['p' => $projectSlug, 'tid' => $teamSlug], 'COM_SPORTSMANAGEMENT_TEAMICONS_ALLTIME_ROSTER_LINK', 'media/com_sportsmanagement/jl_images/team_icon.png'];
        }
        if (!empty($config['show_plan_link'])) {
            $icons[] = ['teamplan', ['p' => $projectSlug, 'tid' => $teamSlug, 'division' => $divisionSlug, 'mode' => 0, 'ptid' => $projectTeamSlug], 'COM_SPORTSMANAGEMENT_TEAMICONS_TEAMPLAN_LINK', 'media/com_sportsmanagement/jl_images/cal_32x32.png'];
        }
        if (!empty($config['show_curve_link'])) {
            $icons[] = ['curve', ['p' => $projectSlug, 'tid1' => $teamSlug, 'tid2' => 0, 'division' => $divisionSlug], 'COM_SPORTSMANAGEMENT_TEAMICONS_CURVE_LINK', 'media/com_sportsmanagement/jl_images/line_graph_32x32.png'];
        }
        if (!empty($config['show_teaminfo_link'])) {
            $icons[] = ['teaminfo', ['p' => $projectSlug, 'tid' => $teamSlug, 'ptid' => $projectTeamSlug], 'COM_SPORTSMANAGEMENT_TEAMICONS_TEAMINFO_LINK', 'media/com_sportsmanagement/jl_images/workflow_32x32.png'];
        }
        if (!empty($config['show_club_link'])) {
            $icons[] = ['clubinfo', ['p' => $projectSlug, 'cid' => $clubSlug], 'COM_SPORTSMANAGEMENT_TEAMICONS_CLUBINFO_LINK', 'media/com_sportsmanagement/jl_images/mail_32x32.png'];
        }
        if (!empty($config['show_teamstats_link'])) {
            $icons[] = ['teamstats', ['p' => $projectSlug, 'tid' => $teamSlug], 'COM_SPORTSMANAGEMENT_TEAMICONS_TEAMSTATS_LINK', 'media/com_sportsmanagement/jl_images/line_chart_32x32.png'];
        }
        if (!empty($config['show_clubplan_link'])) {
            $icons[] = ['clubplan', ['p' => $projectSlug, 'cid' => $clubSlug], 'COM_SPORTSMANAGEMENT_TEAMICONS_CLUBPLAN_LINK', 'media/com_sportsmanagement/jl_images/clock_32x32.png'];
        }
        if (!empty($config['show_rivals_link'])) {
            $icons[] = ['rivals', ['p' => $projectSlug, 'tid' => $teamSlug], 'COM_SPORTSMANAGEMENT_TEAMICONS_RIVALS_LINK', 'media/com_sportsmanagement/jl_images/calculator_32x32.png'];
        }

        if ($icons === []) {
            return '';
        }

        $output = '<ul class="list-inline mb-0">';
        foreach ($icons as [$view, $parameters, $titleKey, $picture]) {
            $parameters = array_merge([
                'cfg_which_database' => $databaseSelector,
                's' => $seasonId,
            ], $parameters);
            $title = Text::_($titleKey) . ' ' . $teamName;
            $image = HTMLHelper::image(
                $picture,
                $title,
                ['width' => 20, 'height' => 'auto', 'title' => $title]
            );
            $output .= '<li class="list-inline-item">'
                . HTMLHelper::link(SiteRouteHelper::view($view, $parameters), $image)
                . '</li>';
        }

        return $output . '</ul>';
    }

    /**
     * Select the short, middle or full team name according to the requested display format, falling back to the full name when the selected value is empty.
     * @param object $team Team record.
     * @param int $format Team name format.
     * @return string Trimmed display name.
     */
    private static function teamName(object $team, int $format): string
    {
        $value = match ($format) {
            0 => trim((string) ($team->short_name ?? '')),
            1 => trim((string) ($team->middle_name ?? '')),
            default => trim((string) ($team->name ?? '')),
        };

        return $value !== '' ? $value : trim((string) ($team->name ?? ''));
    }

    /**
     * Check whether any supported team or club information link is enabled in the team-plan configuration.
     * @param array $config Team-plan display options.
     * @return bool Whether at least one information icon is enabled.
     */
    private static function hasInfoIcons(array $config): bool
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
     * Resolve the image source using an HTTP(S) URL or a local file check and the configured placeholder when the supplied image is missing. Prefix relative paths with the configured picture server or Joomla root URL.
     * @param string $picture Image source.
     * @param string $placeholderKey Component parameter containing the fallback image.
     * @return string Image URL or an empty string.
     */
    private static function resolvePicture(string $picture, string $placeholderKey): string
    {
        $picture = trim($picture);
        $isRemote = $picture !== '' && preg_match('#^https?://#i', $picture) === 1;

        if ($picture === '' || (!$isRemote && !is_file(JPATH_ROOT . '/' . ltrim($picture, '/')))) {
            $picture = trim((string) ComponentHelper::getParams('com_sportsmanagement')->get($placeholderKey, ''));
        }

        if ($picture === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $picture)) {
            return $picture;
        }

        $server = \defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')
            ? trim((string) COM_SPORTSMANAGEMENT_PICTURE_SERVER)
            : Uri::root();
        $server = $server !== '' ? $server : Uri::root();

        return rtrim($server, '/') . '/' . ltrim($picture, '/');
    }

    /**
     * Replace unsupported identifier characters with hyphens and provide a default identifier when the resulting value is empty.
     * @param string $value Candidate HTML identifier.
     * @return string Normalised identifier.
     */
    private static function safeId(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9_-]+/', '-', $value) ?? '';

        return trim($value, '-') ?: 'teamplan-team';
    }

    /**
     * Remove characters outside the supported character set for inline colour values, preventing declaration separators from passing through.
     * @param string $value Configured CSS colour value.
     * @return string Filtered CSS value.
     */
    private static function cssValue(string $value): string
    {
        return preg_replace('/[^#A-Za-z0-9(),.%\s-]/', '', $value) ?? '';
    }

    /**
     * Escape a display string for HTML text and quoted attributes using UTF-8 and both quote types.
     * @param string $value Raw display value.
     * @return string HTML-escaped value.
     */
    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
