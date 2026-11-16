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
use Joomla\CMS\Application\CMSApplicationInterface;
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
    /**
     * Resolve the active Joomla site application for legacy results templates.
     * Reuses the component's frontend application resolver so old template overrides work without global Joomla 3 application access.
     * @return CMSApplicationInterface Current Joomla site application.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Obtain the Joomla database service for compatibility lookups.
     * Reads the typed DatabaseInterface from Joomla's dependency container instead of calling the removed legacy database factory.
     * @return DatabaseInterface Joomla database connection.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        return $database;
    }

    public static int $roundid = 0;

    /** @var object|array|null */
    public static $project = null;

    /** @var array<int|string, object> */
    public static array $teams = [];

    /**
     * Register the historical global SportsManagement HTML helper class name.
     * Aliases the native compatibility facade only when the old name has not already been declared by an extension or template override.
     * @return void
     */
    public static function register(): void
    {
        if (!class_exists('sportsmanagementHelperHtml', false)) {
            class_alias(self::class, 'sportsmanagementHelperHtml');
        }
    }

    /**
     * Render a legacy results image link through the native Bootstrap modal helper.
     * Normalises historical scalar arguments and passes size, schema markup, link and modal options to ModalImageHelper.
     * @param mixed $target Unique link and modal target.
     * @param mixed $picture Picture filename or URL.
     * @param mixed $text Alternative image/link description.
     * @param mixed $pictureHeight Target image height.
     * @param mixed $url Destination URL.
     * @param mixed $width Modal width.
     * @param mixed $height Modal height.
     * @param mixed $useJqueryModal Legacy modal display option.
     * @param mixed $schemaAttribute Schema.org attribute name.
     * @param mixed $schemaValue Schema.org attribute value.
     * @return string Image and modal HTML fragment.
     */
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

    /**
     * Build the round navigation control for the active results project.
     * Obtains the project and season context from the facade or current site request, then delegates to the native round pagination renderer.
     * @param mixed $form Whether the selector should be rendered as a form.
     * @param mixed $cfgWhichDatabase Component database selection.
     * @param mixed $seasonId Optional explicit season ID.
     * @return string Round navigation markup, empty without a valid project.
     */
    public static function getRoundSelectNavigation($form, $cfgWhichDatabase = 0, $seasonId = 0): string
    {
        $project = is_object(self::$project) ? self::$project : null;

        if (!$project || (int) ($project->id ?? 0) <= 0) {
            return '';
        }

        $app = self::siteApplication();
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

    /**
     * Format a match kickoff time in legacy results templates.
     * Checks that the match is an object before forwarding presentation options and optional project context to MatchTimeHelper.
     * @param mixed $game Match record containing start date/time.
     * @param mixed $config Match-specific formatting settings.
     * @param mixed $overallConfig Component-wide formatting settings.
     * @param mixed $project Optional project context.
     * @return string Formatted kickoff time or empty string.
     */
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

    /**
     * Output the match venue for old results template overrides.
     * Resolves the home team's project record and current frontend context, renders its venue with the native helper, echoes the markup and returns an empty string.
     * @param mixed $game Match object, passed by reference for legacy compatibility.
     * @param mixed $config Venue display configuration.
     * @return string Always empty; venue markup is echoed directly.
     */
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

        $app = self::siteApplication();
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

    /**
     * Create the match event and substitution container in the results view.
     * Normalises optional event arrays, verifies the match record and delegates the actual event list rendering to the native presentation helper.
     * @param mixed $matchInfo Match record.
     * @param mixed $projectEvents Configured event types.
     * @param mixed $matchEvents Recorded match events.
     * @param mixed $substitutions Optional substitutions.
     * @param mixed $config Event output settings.
     * @param mixed $project Legacy project argument, retained for compatibility.
     * @return string Event markup or an empty string for invalid match data.
     */
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

    /**
     * Format the division labels for the two teams in a results fixture.
     * Optionally enriches both team records with a bound division lookup and delegates the visible division layout to TeamplanMatchPresentationHelper.
     * @param mixed $homeTeam Home-team record, passed by reference.
     * @param mixed $guestTeam Away-team record, passed by reference.
     * @param mixed $config Template configuration, passed by reference.
     * @param mixed $divisionId Optional division identifier.
     * @return string Division HTML or a non-breaking space without valid teams.
     */
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

        $app = self::siteApplication();
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

    /**
     * Output the translated round heading, optional ranking link and round dates.
     * Resolves the selected round, chooses its configured label or matchday number, optionally links to its ranking and appends formatted start/end dates.
     * @param mixed $title Optional leading section title.
     * @param mixed $currentRound Round identifier.
     * @param mixed $config Section heading and date settings, passed by reference.
     * @param mixed $mode Whether to create a ranking link for the round.
     * @return string Always empty; heading markup is echoed for legacy layouts.
     */
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
                        $app = self::siteApplication();
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

    /**
     * Decorate a project team with its selected division metadata.
     * Looks up the division once, stores its ID, slug, display name and short name on the team object, and handles missing divisions with a numeric slug.
     * @param object $team Team record to extend in place.
     * @param int $divisionId Division identifier to apply.
     * @return void
     */
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

    /**
     * Read the division label and alias for a results-team display.
     * Selects only the required fields by a bound integer ID using Joomla's native query builder.
     * @param int $divisionId Division identifier.
     * @return object|null Division object or null when no record exists.
     */
    private static function loadDivision(int $divisionId): ?object
    {
        /** @var DatabaseInterface $db */
        $db = self::joomlaDatabase();
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

    /**
     * Load the round used to build a legacy matchday heading.
     * Fetches its alias, title and date range using a bound ID, and adds the old id:alias slug expected by route-building helpers.
     * @param int $roundId Round identifier.
     * @return object|null Enriched round object or null if unavailable.
     */
    private static function loadRound(int $roundId): ?object
    {
        /** @var DatabaseInterface $db */
        $db = self::joomlaDatabase();
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

    /**
     * Escape a legacy results heading or division value for safe HTML.
     * Encodes both quote types and special characters as UTF-8 HTML entities to keep database labels from changing template markup.
     * @param string $value Text to encode.
     * @return string Escaped display text.
     */
    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
