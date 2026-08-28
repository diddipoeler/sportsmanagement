<?php
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

    public static function getTeamMatchResult($game, $projectTeamId)
    {
        return MatchResultHelper::outcome($game, (int) $projectTeamId);
    }

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

    private static function append(array &$parts, string $value): void
    {
        if ($value !== '') {
            $parts[] = $value;
        }
    }

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

    private static function database(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }
}
