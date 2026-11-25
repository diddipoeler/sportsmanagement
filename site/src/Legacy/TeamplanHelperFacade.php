<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\MatchResultHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

/**
 * Narrow Joomla 5/6 facade for the historical sportsmanagementHelper calls
 * still present in the teamplan templates.
 */
final class TeamplanHelperFacade
{
    /** @var array<int, object|null> */
    private static array $favoriteSettingsCache = [];

    /**
     * Render the configured short, medium or full team name with favourite styling and optional expandable navigation icons. Wrap the result in the supplied link when present.
     * @param object $team Team metadata.
     * @param mixed $containerPrefix Prefix for the expandable menu identifier.
     * @param array $config Team display options.
     * @param mixed $isFav Favourite-team flag.
     * @param mixed $link Optional outer link.
     * @param mixed $databaseSelector Route database choice.
     * @return string Team name markup.
     */
    public static function formatTeamName($team, $containerPrefix, &$config, $isFav = 0, $link = null, $databaseSelector = 0): string
    {
        $output = '';
        $description = '';

        if (!empty($config['results_below']) && !empty($config['show_logo_small'])) {
            $jsFunction = 'visibleMenu';
            $styleAppend = 'visibility:hidden';
            $container = 'span';
        } else {
            $jsFunction = 'switchMenu';
            $styleAppend = 'display:none';
            $container = 'div';
        }

        $showIcons = (
            ((int) ($config['show_info_link'] ?? 0) === 2 && $isFav)
            || ((int) ($config['show_info_link'] ?? 0) === 1 && (
                !empty($config['show_club_link'])
                || !empty($config['show_team_link'])
                || !empty($config['show_curve_link'])
                || !empty($config['show_plan_link'])
                || !empty($config['show_teaminfo_link'])
                || !empty($config['show_teamstats_link'])
                || !empty($config['show_clubplan_link'])
                || !empty($config['show_rivals_link'])
            ))
        );

        $containerId = (string) $containerPrefix . 't' . (int) ($team->id ?? 0) . 'p' . (int) ($team->project_id ?? 0);
        $style = 'padding:2px;';

        if (!empty($config['highlight_fav']) && $isFav) {
            $fav = self::getProjectFavTeams((int) ($team->project_id ?? 0));
            if ($fav) {
                $style .= !empty($fav->fav_team_text_bold) ? 'font-weight:bold;' : '';
                $style .= trim((string) ($fav->fav_team_text_color ?? '')) !== '' ? 'color:' . trim((string) $fav->fav_team_text_color) . ';' : '';
                $style .= trim((string) ($fav->fav_team_color ?? '')) !== '' ? 'background-color:' . trim((string) $fav->fav_team_color) . ';' : '';
            }
        }

        $description .= '<span style="' . $style . '">';
        $formattedName = '';
        if ((int) ($config['team_name_format'] ?? 2) === 0) {
            $formattedName = (string) ($team->short_name ?? '');
        } elseif ((int) ($config['team_name_format'] ?? 2) === 1) {
            $formattedName = (string) ($team->middle_name ?? '');
        }
        if ($formattedName === '') {
            $formattedName = (string) ($team->name ?? '');
        }

        if ((int) ($config['team_name_format'] ?? 2) === 0 && !empty($team->short_name)) {
            $description .= '<acronym title="' . (string) ($team->name ?? '') . '">' . (string) $team->short_name . '</acronym>';
        } else {
            $description .= $formattedName;
        }
        $description .= '</span>';

        if ($showIcons) {
            $params = ['onclick' => $jsFunction . '(\'' . $containerId . '\');return false;'];
            $output .= HTMLHelper::link('javascript:void(0);', $description, $params);
            $output .= '<' . $container . ' id="' . $containerId . '" style="' . $styleAppend . ';" class="rankingteam jsmeventsshowhide">';
            $output .= self::showTeamIcons($team, $config, $databaseSelector);
            $output .= '</' . $container . '>';
        } else {
            $output = $description;
        }

        return $link !== null ? HTMLHelper::link($link, $output) : $output;
    }

    /**
     * Build the enabled roster, schedule, club, statistics and other team navigation icons using project and team route slugs.
     * @param object $team Team metadata.
     * @param array $config Enabled navigation options.
     * @param mixed $databaseSelector Route database choice.
     * @param mixed $seasonId Route season identifier.
     * @return string Navigation list markup or empty string.
     */
    public static function showTeamIcons(&$team, &$config, $databaseSelector = 0, $seasonId = 0): string
    {
        if (!isset($team->projectteamid)) {
            return '';
        }

        $projectTeamSlug = (string) ($team->projectteam_slug ?? $team->projectteamid);
        $teamName = (string) ($team->name ?? '');
        $teamId = (int) ($team->team_id ?? $team->id ?? 0);
        $teamSlug = (string) ($team->team_slug ?? $teamId);
        $clubSlug = (string) ($team->club_slug ?? $team->club_id ?? 0);
        $divisionSlug = (string) ($team->division_slug ?? $team->division_id ?? 0);
        $projectSlug = (string) ($team->project_slug ?? $team->project_id ?? 0);
        $width = 20;
        $output = '<ul class="list-inline">';

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

        foreach ($icons as [$view, $parameters, $titleKey, $picture]) {
            $parameters = array_merge([
                'cfg_which_database' => (int) $databaseSelector,
                's' => (int) $seasonId,
            ], $parameters);
            $link = SiteRouteHelper::view($view, $parameters);
            $title = Text::_($titleKey) . '&nbsp;' . $teamName;
            $description = self::getPictureThumb($picture, $title, $width, 0, 4);
            $output .= '<li class="list-inline-item">' . HTMLHelper::link($link, $description) . '</li>';
        }

        return $output . '</ul>';
    }

    /**
     * Read and cache project favourite-team identifiers and text/background styling for the duration of the request.
     * @param int $projectId Project identifier.
     * @return object|null Favourite settings or null.
     */
    public static function getProjectFavTeams(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        if (array_key_exists($projectId, self::$favoriteSettingsCache)) {
            return self::$favoriteSettingsCache[$projectId];
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('fav_team'),
                $db->quoteName('fav_team_text_bold'),
                $db->quoteName('fav_team_text_color'),
                $db->quoteName('fav_team_color'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);
        self::$favoriteSettingsCache[$projectId] = $db->loadObject() ?: null;

        return self::$favoriteSettingsCache[$projectId];
    }

    /**
     * Compatibility bridge for the project heading template.
     *
     * The historical helper accepted backend/frontend mode plus a template
     * name. Teamplan only needs the frontend read path, which is delegated to
     * the native Joomla 5/6 extra-fields helper.
     *
     * @return array<int, object>
     */
    public static function getUserExtraFields($itemId, $template = 'backend', $databaseSelector = 0, $templateName = 'clubinfo'): array
    {
        if ((string) $template !== 'frontend') {
            return [];
        }

        return \Diddipoeler\Component\SportsManagement\Site\Helper\ExtraFieldsReadHelper::load(
            self::database(),
            max(0, (int) $itemId),
            (string) $templateName
        );
    }

    /**
     * Render an image at the requested dimensions, selecting the configured placeholder when the original is unavailable. Optionally wrap it in a Highslide link.
     * @param mixed $picture Image path or URL.
     * @param mixed $altText Alternative text and tooltip.
     * @param mixed $width Image width.
     * @param mixed $height Image height.
     * @param mixed $type Placeholder category.
     * @return string Image markup or empty string.
     */
    public static function getPictureThumb($picture, $altText, $width = 40, $height = 40, $type = 0): string
    {
        $picture = (string) $picture;
        $altText = (string) $altText;
        $width = (int) $width;
        $height = is_numeric($height) ? (int) $height : 0;
        $type = (int) $type;
        $params = ComponentHelper::getParams('com_sportsmanagement');

        if (!self::existPicture($picture)) {
            $picture = match ($type) {
                1 => (string) $params->get('ph_logo_big', ''),
                2 => (string) $params->get('ph_logo_medium', ''),
                3 => (string) $params->get('ph_logo_small', ''),
                4 => (string) $params->get('ph_icon', ''),
                5 => (string) $params->get('ph_team', ''),
                default => (string) $params->get('ph_player', ''),
            };
        }

        if ($picture === '') {
            return '';
        }

        $src = self::pictureUrl($picture);
        $title = $altText;
        $useHighslide = (bool) $params->get('use_highslide', false) && $type !== 4;
        if ($useHighslide) {
            $title .= ' (' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CLICK_TO_ENLARGE') . ')';
        }

        $attributes = ' src="' . $src . '"';
        if ($width > 0) {
            $attributes .= ' width="' . $width . '"';
        }
        if ($height > 0) {
            $attributes .= ' height="' . $height . '"';
        }
        $image = '<img' . $attributes . ' alt="' . $altText . '" title="' . $title . '"/>';

        return $useHighslide ? '<a href="' . $src . '" class="highslide">' . $image . '</a>' : $image;
    }

    /**
     * Map historical image categories to the corresponding component placeholder configuration.
     * @param mixed $type Legacy image category.
     * @return string Configured placeholder path.
     */
    public static function getDefaultPlaceholder($type = 'player'): string
    {
        $params = ComponentHelper::getParams('com_sportsmanagement');

        return match ((string) $type) {
            'trikot_home', 'trikot_away', 'clubs_trikot_home', 'clubs_trikot_away' => (string) $params->get('ph_trikot', ''),
            'projects' => (string) $params->get('ph_project', ''),
            'projectteams/trikot_home', 'projectteams/trikot_away', 'clublogosmall', 'logo_small', 'clubs_small' => (string) $params->get('ph_logo_small', ''),
            'stadium', 'playgrounds' => (string) $params->get('ph_stadium', ''),
            'menlarge' => (string) $params->get('ph_player_men_large', ''),
            'mensmall' => (string) $params->get('ph_player_men_small', ''),
            'womanlarge' => (string) $params->get('ph_player_woman_large', ''),
            'womansmall' => (string) $params->get('ph_player_woman_small', ''),
            'clublogobig', 'logo_big', 'clubs_large', 'league', 'leagues' => (string) $params->get('ph_logo_big', ''),
            'clublogomedium', 'logo_middle', 'clubs_medium' => (string) $params->get('ph_logo_medium', ''),
            'icon' => (string) $params->get('ph_icon', 'images/com_sportsmanagement/database/placeholders/placeholder_21.png'),
            'team', 'team_picture', 'teams', 'projectteams', 'projectteam_picture' => (string) $params->get('ph_team', ''),
            default => (string) $params->get('ph_player', ''),
        };
    }

    /**
     * Check whether a nonempty image path refers to an existing local file or an HTTP(S) URL. Remote URLs are recognised without checking network availability.
     * @param mixed $picture Image path or URL.
     * @param mixed $standard Retained compatibility argument, unused.
     * @return bool Whether the image is recognised.
     */
    public static function existPicture($picture = '', $standard = ''): bool
    {
        $picture = (string) $picture;
        if ($picture === '') {
            return false;
        }
        if (preg_match('#^https?://#i', $picture)) {
            return true;
        }

        $path = str_starts_with($picture, JPATH_ROOT)
            ? $picture
            : JPATH_ROOT . '/' . ltrim(str_replace('\\', '/', $picture), '/');

        return is_file($path);
    }

    /**
     * Delegate the match outcome calculation for the selected project team to the native result helper.
     * @param mixed $game Match record.
     * @param mixed $projectTeamId Project-team identifier.
     * @return mixed Outcome returned by the native helper.
     */
    public static function getTeamMatchResult($game, $projectTeamId)
    {
        return MatchResultHelper::outcome($game, (int) $projectTeamId);
    }

    /**
     * Assemble the name prefix, given name, nickname, surname and initials according to the historical numbered teamplan formatting rules.
     * @param mixed $prefix Optional name prefix.
     * @param mixed $firstName Given name.
     * @param mixed $nickName Nickname.
     * @param mixed $lastName Surname.
     * @param mixed $format Legacy formatting identifier.
     * @return string Formatted name.
     */
    public static function formatName($prefix, $firstName, $nickName, $lastName, $format): string
    {
        $name = [];
        if ($prefix) {
            $name[] = $prefix;
        }

        $firstName = (string) $firstName;
        $nickName = (string) $nickName;
        $lastName = (string) $lastName;

        switch ((int) $format) {
            case 0:
                self::append($name, $firstName);
                self::append($name, $nickName !== '' ? "'" . $nickName . "'" : '');
                self::append($name, $lastName);
                break;
            case 1:
                self::append($name, $lastName !== '' ? $lastName . ',' : '');
                self::append($name, $nickName !== '' ? "'" . $nickName . "'" : '');
                self::append($name, $firstName);
                break;
            case 2:
                self::append($name, $lastName !== '' ? $lastName . ',' : '');
                self::append($name, $firstName);
                self::append($name, $nickName !== '' ? "'" . $nickName . "'" : '');
                break;
            case 3:
                self::append($name, $firstName);
                self::append($name, $lastName);
                break;
            case 4:
                self::append($name, $lastName !== '' ? $lastName . ',' : '');
                self::append($name, $firstName);
                break;
            case 5:
                self::append($name, $nickName !== '' ? "'" . $nickName . "' - " : '');
                self::append($name, $firstName);
                self::append($name, $lastName);
                break;
            case 6:
                self::append($name, $nickName !== '' ? "'" . $nickName . "' - " : '');
                self::append($name, $lastName !== '' ? $lastName . ',' : '');
                self::append($name, $firstName);
                break;
            case 7:
                self::append($name, $firstName);
                self::append($name, $lastName);
                self::append($name, $nickName !== '' ? '(' . $nickName . ')' : '');
                break;
            case 8:
                self::append($name, $firstName !== '' ? $firstName[0] . '.' : '');
                self::append($name, $lastName);
                break;
            case 9:
                self::append($name, $lastName !== '' ? $lastName . ',' : '');
                self::append($name, $firstName !== '' ? $firstName[0] . '.' : '');
                break;
            case 10:
                self::append($name, $lastName);
                break;
            case 11:
                self::append($name, $firstName);
                self::append($name, $nickName !== '' ? "'" . $nickName . "'" : '');
                self::append($name, $lastName !== '' ? $lastName[0] . '.' : '');
                break;
            case 12:
                self::append($name, $nickName);
                break;
            case 13:
                self::append($name, $firstName);
                self::append($name, $lastName !== '' ? $lastName[0] . '.' : '');
                break;
            case 14:
                self::append($name, $lastName);
                self::append($name, $firstName);
                break;
            case 15:
                self::append($name, $lastName);
                if ($lastName !== '') {
                    $name[] = '<br \\>';
                }
                self::append($name, $firstName);
                break;
            case 16:
                self::append($name, $lastName);
                if ($lastName !== '') {
                    $name[] = '<br \\>';
                }
                self::append($name, $firstName);
                break;
        }

        return implode(' ', $name);
    }

    /**
     * Append a nonempty name fragment to the ordered formatting buffer.
     * @param array $parts Name fragments, updated by reference.
     * @param string $value Candidate fragment.
     * @return void
     */
    private static function append(array &$parts, string $value): void
    {
        if ($value !== '') {
            $parts[] = $value;
        }
    }

    /**
     * Keep an HTTP(S) image URL unchanged or convert a local image path into a site-relative URL using the Joomla installation root.
     * @param string $picture Image path or URL.
     * @return string Image URL.
     */
    private static function pictureUrl(string $picture): string
    {
        if (preg_match('#^https?://#i', $picture)) {
            return $picture;
        }
        $relative = str_starts_with($picture, JPATH_ROOT)
            ? substr($picture, strlen(JPATH_ROOT))
            : $picture;

        return rtrim(Uri::root(true), '/') . '/' . ltrim(str_replace('\\', '/', $relative), '/');
    }

    /**
     * Return the primary Joomla database service used by the teamplan compatibility helper.
     * @return DatabaseInterface Joomla database connection.
     */
    private static function database(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }
}
