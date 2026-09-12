<?php
/**
 * Joomla 5/6 compatibility facade for historical results template overrides.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Keeps old results template overrides working while delegating their rendering
 * to the Joomla 5/6 native presentation helpers.
 */
final class ResultsLegacyHtmlCompatibility
{
    public static int $roundid = 0;

    /** @var object|array|null */
    public static $project = null;

    /** @var array<int|string, object> */
    public static array $teams = [];

    public static function register(): void
    {
        if (!class_exists('sportsmanagementHelperHtml', false)) {
            class_alias(self::class, 'sportsmanagementHelperHtml');
        }
    }

    public static function getBootstrapModalImage(
        $target = '',
        $picture = '',
        $text = '',
        $pictureHeight = '20',
        $url = '',
        $width = '100',
        $height = '200',
        $useJqueryModal = 0,
        $schemaAttribute = 'itemprop',
        $schemaValue = 'logo'
    ): string {
        return ModalImageHelper::render(
            (string) $target,
            (string) $picture,
            (string) $text,
            $pictureHeight,
            (string) $url,
            $width,
            $height,
            (int) $useJqueryModal,
            (string) $schemaAttribute,
            (string) $schemaValue
        );
    }

    public static function getRoundSelectNavigation($form, $cfgWhichDatabase = 0, $seasonId = 0): string
    {
        $project = is_object(self::$project) ? self::$project : null;

        if (!$project || (int) ($project->id ?? 0) <= 0) {
            return '';
        }

        $app = SportsManagementSiteApplicationResolver::resolve();
        $input = $app->getInput();
        $resolvedSeasonId = (int) $seasonId;

        if ($resolvedSeasonId <= 0) {
            $resolvedSeasonId = (int) ($project->season_id ?? $input->getInt('s', 0));
        }

        return RoundPaginationHelper::selectNavigation(
            $project,
            (int) $cfgWhichDatabase === 1 ? 1 : 0,
            $resolvedSeasonId,
            $form ? 'form' : ''
        );
    }

    public static function showMatchTime(
        $game,
        $config = [],
        $overallConfig = [],
        $project = null
    ): string {
        if (!is_object($game)) {
            return '';
        }

        return MatchTimeHelper::format(
            $game,
            (array) $config,
            (array) $overallConfig,
            is_object($project) ? $project : null
        );
    }

    public static function showMatchPlayground(&$game, $config = []): string
    {
        if (!is_object($game)) {
            return '';
        }

        $projectTeamId = (int) ($game->projectteam1_id ?? 0);
        $homeTeam = self::$teams[$projectTeamId] ?? null;

        if (!is_object($homeTeam)) {
            $homeTeam = (object) [
                'standard_playground' => 0,
                'playground_name' => '',
                'playground_short_name' => '',
            ];
        }

        $app = SportsManagementSiteApplicationResolver::resolve();
        $input = $app->getInput();
        $project = is_object(self::$project) ? self::$project : null;
        $output = TeamplanMatchPresentationHelper::renderPlayground(
            $game,
            $homeTeam,
            (array) $config,
            $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0,
            (int) ($project->season_id ?? $input->getInt('s', 0)),
            $project
        );

        echo $output;

        return '';
    }

    public static function showEventsContainerInResults(
        $matchInfo = [],
        $projectEvents = [],
        $matchEvents = [],
        $substitutions = null,
        $config = [],
        $project = []
    ): string {
        if (!is_object($matchInfo)) {
            return '';
        }

        return MatchEventPresentationHelper::render(
            $matchInfo,
            array_values((array) $projectEvents),
            array_values((array) $matchEvents),
            array_values((array) ($substitutions ?? [])),
            (array) $config
        );
    }

    public static function showDivisonRemark(&$homeTeam, &$guestTeam, &$config, $divisionId = 0): string
    {
        if (!is_object($homeTeam) || !is_object($guestTeam)) {
            return '&nbsp;';
        }

        $divisionId = max(0, (int) $divisionId);
        if ($divisionId > 0) {
            self::applyDivision($homeTeam, $divisionId);
            self::applyDivision($guestTeam, $divisionId);
        }

        $app = SportsManagementSiteApplicationResolver::resolve();
        $input = $app->getInput();
        $project = is_object(self::$project) ? self::$project : null;
        $seasonId = (int) ($project->season_id ?? $input->getInt('s', 0));

        return TeamplanMatchPresentationHelper::renderDivision(
            $homeTeam,
            $guestTeam,
            (array) $config,
            $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0,
            $seasonId,
            $project
        );
    }

    public static function showMatchdaysTitle($title, $currentRound, &$config, $mode = 0): string
    {
        $title = (string) $title;
        $currentRound = max(0, (int) $currentRound);
        $output = $title !== '' ? self::escape($title) . ' - ' : '';

        if ($currentRound > 0) {
            $round = self::loadRound($currentRound);

            if ($round) {
                $roundName = trim((string) ($round->name ?? ''));
                if ((int) ($config['type_section_heading'] ?? 0) === 1 && $roundName !== '') {
                    $label = self::escape($roundName);

                    if ((int) $mode === 1) {
                        $app = SportsManagementSiteApplicationResolver::resolve();
                        $input = $app->getInput();
                        $project = is_object(self::$project) ? self::$project : null;
                        $link = SiteRouteHelper::view('ranking', [
                            'cfg_which_database' => $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0,
                            's' => (int) ($project->season_id ?? $input->getInt('s', 0)),
                            'p' => (string) ($project->slug ?? $input->getInt('p', 0)),
                            'type' => 0,
                            'r' => (string) ($round->slug ?? $round->id ?? $currentRound),
                            'from' => 0,
                            'to' => 0,
                            'division' => 0,
                        ]);
                        $label = HTMLHelper::link($link, $label);
                    }

                    $output .= $label;
                } else {
                    $output .= $currentRound . '. ' . self::escape(Text::_('COM_SPORTSMANAGEMENT_RESULTS_MATCHDAY'));
                }

                if (!empty($config['show_rounds_dates'])) {
                    $dates = [];
                    $firstDate = (string) ($round->round_date_first ?? '');
                    $lastDate = (string) ($round->round_date_last ?? '');

                    if ($firstDate !== '' && !str_contains($firstDate, '0000-00-00')) {
                        $dates[] = HTMLHelper::date($firstDate, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE'));
                    }
                    if ($lastDate !== ''
                        && $lastDate !== $firstDate
                        && !str_contains($lastDate, '0000-00-00')) {
                        $dates[] = HTMLHelper::date($lastDate, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE'));
                    }
                    if ($dates !== []) {
                        $output .= ' (' . implode(' - ', $dates) . ')';
                    }
                }
            } else {
                $output .= $currentRound . '. ' . self::escape(Text::_('COM_SPORTSMANAGEMENT_RESULTS_MATCHDAY'));
            }
        }

        echo $output;

        return '';
    }

    private static function applyDivision(object $team, int $divisionId): void
    {
        $division = self::loadDivision($divisionId);
        $team->division_id = $divisionId;
        $team->division_slug = $division
            ? $divisionId . ':' . (string) ($division->alias ?? '')
            : (string) $divisionId;
        $team->division_name = (string) ($division->name ?? '');
        $team->division_shortname = (string) ($division->shortname ?? '');
    }

    private static function loadDivision(int $divisionId): ?object
    {
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('alias'),
                $db->quoteName('name'),
                $db->quoteName('shortname'),
            ])
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('id') . ' = :divisionId')
            ->bind(':divisionId', $divisionId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    private static function loadRound(int $roundId): ?object
    {
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('alias'),
                $db->quoteName('name'),
                $db->quoteName('round_date_first'),
                $db->quoteName('round_date_last'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = :roundId')
            ->bind(':roundId', $roundId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $round = $db->loadObject();

        if ($round) {
            $round->slug = (int) $round->id . ':' . (string) ($round->alias ?? '');
        }

        return $round ?: null;
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
