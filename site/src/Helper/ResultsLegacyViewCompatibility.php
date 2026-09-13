<?php
/**
 * Joomla 5/6 compatibility facade for historical results view helpers.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Keeps historical sportsmanagementViewResults calls working for native
 * Joomla 5/6 results views and third-party template overrides.
 */
final class ResultsLegacyViewCompatibility
{
    /** @var array<int, int> */
    private static array $favteams = [];

    /** @var array<int, array<int, object>> */
    private static array $refereesByMatch = [];

    public static function register(): void
    {
        if (!class_exists('sportsmanagementViewResults', false)) {
            class_alias(self::class, 'sportsmanagementViewResults');
        }
    }

    /**
     * @param array<int, int> $favteams
     * @param array<int, array<int, object>> $refereesByMatch
     */
    public static function sync(array $favteams, array $refereesByMatch): void
    {
        self::$favteams = array_values(array_map('intval', $favteams));
        self::$refereesByMatch = $refereesByMatch;
    }

    public static function showNotPlayingTeams(&$games, &$teams, &$config, &$favteams, &$project): string
    {
        $playingTeams = [];

        foreach ((array) $games as $game) {
            if (!is_object($game)) {
                continue;
            }

            self::addPlayingTeams(
                $playingTeams,
                (int) ($game->projectteam1_id ?? 0),
                (int) ($game->projectteam2_id ?? 0),
                !empty($game->published)
            );
        }

        $notPlaying = count((array) $teams) - count($playingTeams);
        if ($notPlaying <= 0) {
            return '';
        }

        $output = '<b>' . Text::sprintf('COM_SPORTSMANAGEMENT_RESULTS_TEAMS_NOT_PLAYING', $notPlaying) . '</b> ';
        $rendered = [];

        foreach ((array) $teams as $team) {
            if (!is_object($team)) {
                continue;
            }

            $projectTeamId = (int) ($team->projectteamid ?? 0);
            if ($projectTeamId > 0 && in_array($projectTeamId, $playingTeams, true)) {
                continue;
            }

            $entry = '';
            if (!empty($config['show_logo_small']) && !empty($config['show_dnp_teams_icons'])) {
                $entry .= self::getTeamClubIcon($team, (int) $config['show_logo_small']) . '&nbsp;';
            }

            $entry .= self::formatTeamName($team, (array) $config, in_array((int) ($team->id ?? 0), (array) $favteams, true));
            $rendered[] = $entry;
        }

        return $output . implode(', ', $rendered);
    }

    public static function addPlayingTeams(&$playingTeams, $homeTeam, $awayTeam, $published = false): void
    {
        if (!$published) {
            return;
        }

        foreach ([(int) $homeTeam, (int) $awayTeam] as $teamId) {
            if ($teamId > 0 && !in_array($teamId, $playingTeams, true)) {
                $playingTeams[] = $teamId;
            }
        }
    }

    public static function getTeamClubIcon(
        $team,
        $type = 1,
        $attribs = [],
        $modalWidth = '100',
        $modalHeight = '200',
        $useJqueryModal = 0,
        $schemaAttribute = 'itemprop',
        $schemaValue = 'logo'
    ): string {
        if (!is_object($team) || !isset($team->name)) {
            return '';
        }

        foreach (['logo_small', 'logo_middle', 'logo_big'] as $logoField) {
            if (empty($team->{$logoField})) {
                $team->{$logoField} = self::defaultPlaceholder($logoField);
            }
        }

        $title = (string) $team->name;
        $width = (string) ((array) $attribs)['width'] ?? '20';
        if ($width === '') {
            $width = '20';
        }
        $countryFlag = self::countryFlag((string) ($team->country ?? ''));
        $projectTeamId = (int) ($team->projectteamid ?? $team->projectteam_id ?? 0);
        $pictureServer = \defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')
            ? (string) COM_SPORTSMANAGEMENT_PICTURE_SERVER
            : Uri::root();

        $renderLogo = static function (string $logo) use (
            $projectTeamId,
            $pictureServer,
            $title,
            $width,
            $modalWidth,
            $modalHeight,
            $useJqueryModal,
            $schemaAttribute,
            $schemaValue
        ): string {
            return ResultsLegacyHtmlCompatibility::getBootstrapModalImage(
                'resultsteam' . $projectTeamId,
                $pictureServer . $logo,
                $title,
                $width,
                '',
                $modalWidth,
                $modalHeight,
                $useJqueryModal,
                $schemaAttribute,
                $schemaValue
            );
        };

        return match ((int) $type) {
            1 => $renderLogo((string) $team->logo_small),
            2 => $countryFlag,
            3 => $renderLogo((string) $team->logo_small) . ($countryFlag !== '' ? ' ' . $countryFlag : ''),
            4 => ($countryFlag !== '' ? $countryFlag . ' ' : '') . $renderLogo((string) $team->logo_small),
            5 => $renderLogo((string) $team->logo_middle),
            6 => $renderLogo((string) $team->logo_big),
            7 => $renderLogo((string) $team->logo_big) . ($countryFlag !== '' ? ' ' . $countryFlag : ''),
            8 => ($countryFlag !== '' ? $countryFlag . ' ' : '') . $renderLogo((string) $team->logo_big),
            default => '',
        };
    }

    /** @return array<string, array<int, object>> */
    public static function sortByDate($matches): array
    {
        $dates = [];

        foreach ((array) $matches as $match) {
            if (!is_object($match)) {
                continue;
            }

            $date = substr((string) ($match->match_date ?? ''), 0, 10);
            $dates[$date] ??= [];
            $dates[$date][] = $match;
        }

        return $dates;
    }

    public static function showMatchRefereesAsTooltip(&$game, $project = [], $config = []): void
    {
        if (!is_object($game) || empty($config['show_referee'])) {
            return;
        }

        $referees = self::$refereesByMatch[(int) ($game->id ?? 0)] ?? [];
        if ($referees === []) {
            echo '&nbsp;';
            return;
        }

        $teamsAsReferees = is_object($project) && !empty($project->teams_as_referees);
        $labels = [];

        foreach ($referees as $referee) {
            $position = Text::_((string) ($referee->position_name ?? ''));
            if ($teamsAsReferees) {
                $name = (string) ($referee->teamname ?? '');
            } else {
                $firstName = trim((string) ($referee->firstname ?? ''));
                $lastName = trim((string) ($referee->lastname ?? ''));
                $name = trim($firstName . ' ' . $lastName);
            }

            $labels[] = trim($name . ($position !== '' ? ' (' . $position . ')' : ''));
        }

        $tooltip = htmlspecialchars(implode(' &lt;br /&gt; ', $labels), ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_RESULTS_REF_TOOLTIP'), ENT_QUOTES, 'UTF-8');

        if ((int) $config['show_referee'] === 2) {
            echo '<span class="hasTip" title="' . $title . ' :: ' . $tooltip . '">'
                . '<img src="' . Uri::root() . 'media/com_sportsmanagement/jl_images/icon-16-Referees.png" alt="" />'
                . '</span>';
            return;
        }

        echo '<span class="hasTip" title="' . $title . ' :: ' . $tooltip . '">'
            . htmlspecialchars((string) ($labels[0] ?? ''), ENT_QUOTES, 'UTF-8')
            . '</span>';
    }

    public static function showReportDecisionIcons(&$game): string
    {
        if (!is_object($game)) {
            return '&nbsp;';
        }

        $app = SportsManagementSiteApplicationResolver::resolve();
        $input = $app->getInput();
        $link = SiteRouteHelper::view('matchreport', [
            'cfg_which_database' => $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0,
            's' => $input->getInt('s', 0),
            'p' => (int) ($game->project_id ?? 0),
            'mid' => (int) ($game->id ?? 0),
        ]);

        if (!((!empty($game->show_report) && trim((string) ($game->summary ?? '')) !== '')
            || !empty($game->alt_decision)
            || (int) ($game->match_result_type ?? 0) > 0)) {
            return '&nbsp;';
        }

        if (!empty($game->alt_decision)) {
            $imageTitle = Text::_((string) ($game->decision_info ?? ''));
            $image = 'media/com_sportsmanagement/jl_images/court.gif';
        } else {
            $imageTitle = Text::_('Has match summary');
            $image = 'media/com_sportsmanagement/jl_images/zoom.png';
        }

        return HTMLHelper::link(
            $link,
            HTMLHelper::image(Uri::root() . $image, $imageTitle, ['border' => 0, 'title' => $imageTitle]),
            ['title' => $imageTitle]
        );
    }

    public static function showEventsContainerInResults(
        $matchInfo,
        $projectEvents,
        $matchEvents,
        $substitutions = null,
        $config = [],
        $project = []
    ): string {
        return ResultsLegacyHtmlCompatibility::showEventsContainerInResults(
            $matchInfo,
            $projectEvents,
            $matchEvents,
            $substitutions,
            $config,
            $project
        );
    }

    public static function formatResult(&$team1, &$team2, &$game, &$reportLink, &$config): string
    {
        if (!is_object($game)) {
            return '';
        }

        $favorite = (is_object($team1) && in_array((int) ($team1->id ?? 0), self::$favteams, true))
            || (is_object($team2) && in_array((int) ($team2->id ?? 0), self::$favteams, true));
        $linkMode = (int) ($config['show_link_matchreport'] ?? 0);
        $state = self::showMatchState($game, $config);

        if ($linkMode === 1 || ($linkMode === 2 && $favorite)) {
            $output = HTMLHelper::link(
                (string) $reportLink,
                '<span class="score0">' . $state . '</span>',
                ['title' => Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOW_MATCHREPORT')]
            );
        } else {
            $output = $state;
        }

        if (!empty($config['show_part_results'])) {
            $left = explode(';', (string) ($game->team1_result_split ?? ''));
            $right = explode(';', (string) ($game->team2_result_split ?? ''));
            $count = min(count($left), count($right));

            for ($index = 0; $index < $count; $index++) {
                if ($left[$index] === '' && $right[$index] === '') {
                    continue;
                }

                $result = $left[$index] . '&nbsp;' . (string) ($config['seperator'] ?? ':') . '&nbsp;' . $right[$index];
                $period = $index + 1;
                $output .= '<br /><span class="hasTip" title="'
                    . Text::sprintf('COM_SPORTSMANAGEMENT_GLOBAL_NPART', (string) $period)
                    . '::' . $result . '">' . $result . '</span>';
            }
        }

        if (!empty($game->team1_legs) || !empty($game->team2_legs)) {
            $output .= '<br /><span>' . (string) ($game->team1_legs ?? '')
                . '&nbsp;' . (string) ($config['seperator'] ?? ':') . '&nbsp;'
                . (string) ($game->team2_legs ?? '') . '</span>';
        }

        return $output;
    }

    public static function showMatchState(&$game, &$config): string
    {
        if (!is_object($game)) {
            return '';
        }

        if ((int) ($game->cancel ?? 0) > 0) {
            return (string) ($game->cancel_reason ?? '');
        }

        return self::formatScoreInline($game, $config);
    }

    public static function formatScoreInline($game, &$config): string
    {
        if (!is_object($game)) {
            return '';
        }

        $switch = !empty($config['switch_home_guest']);
        $separator = (string) ($config['seperator'] ?? ':');
        $homeResult = $switch ? ($game->team2_result ?? '') : ($game->team1_result ?? '');
        $awayResult = $switch ? ($game->team1_result ?? '') : ($game->team2_result ?? '');
        $homeOt = $switch ? ($game->team2_result_ot ?? null) : ($game->team1_result_ot ?? null);
        $awayOt = $switch ? ($game->team1_result_ot ?? null) : ($game->team2_result_ot ?? null);
        $homeSo = $switch ? ($game->team2_result_so ?? null) : ($game->team1_result_so ?? null);
        $awaySo = $switch ? ($game->team1_result_so ?? null) : ($game->team2_result_so ?? null);
        $homeDecision = $switch ? ($game->team2_result_decision ?? '') : ($game->team1_result_decision ?? '');
        $awayDecision = $switch ? ($game->team1_result_decision ?? '') : ($game->team2_result_decision ?? '');
        $result = $homeResult . '&nbsp;' . $separator . '&nbsp;' . $awayResult;

        if (!empty($game->alt_decision)) {
            $result = '<b style="color:red;">' . $homeDecision . '&nbsp;' . $separator . '&nbsp;' . $awayDecision . '</b>';
        }

        $break = (int) ($config['result_style'] ?? 0) === 1 ? '<br />' : ' ';

        if ($homeSo !== null || $awaySo !== null) {
            $result .= $break . '(' . Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT') . ' '
                . $homeSo . '&nbsp;' . $separator . '&nbsp;' . $awaySo . ')';
        } elseif ((int) ($game->match_result_type ?? 0) === 2) {
            $result .= $break . '(' . Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT') . ')';
        }

        if ($homeOt !== null || $awayOt !== null) {
            $result .= $break . '(' . Text::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME') . ' '
                . $homeOt . '&nbsp;' . $separator . '&nbsp;' . $awayOt . ')';
        } elseif ((int) ($game->match_result_type ?? 0) === 1) {
            $result .= $break . '(' . Text::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME') . ')';
        }

        return $result;
    }

    private static function defaultPlaceholder(string $type): string
    {
        if (class_exists('sportsmanagementHelper') && method_exists('sportsmanagementHelper', 'getDefaultPlaceholder')) {
            return (string) \sportsmanagementHelper::getDefaultPlaceholder($type);
        }

        return '';
    }

    private static function countryFlag(string $country): string
    {
        if ($country !== '' && class_exists('JSMCountries') && method_exists('JSMCountries', 'getCountryFlag')) {
            return (string) \JSMCountries::getCountryFlag($country);
        }

        return '';
    }

    private static function formatTeamName(object $team, array $config, bool $favorite): string
    {
        if (class_exists('sportsmanagementHelper') && method_exists('sportsmanagementHelper', 'formatTeamName')) {
            return (string) \sportsmanagementHelper::formatTeamName(
                $team,
                't' . (int) ($team->id ?? 0),
                $config,
                $favorite
            );
        }

        return htmlspecialchars((string) ($team->name ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
