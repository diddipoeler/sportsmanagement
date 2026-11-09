<?php
/**
 * Native Joomla 5/6 database loader for ranking data.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Database-only input loader for the native ranking engine.
 */
final class RankingDataLoader
{
    private const DEFAULT_RANKING_ORDER = 'POINTS, PLAYEDASC, DIFF, FOR';
    private const DEFAULT_SORT_ORDER = 'DESC, ASC, DESC, DESC';

    /**
     * Create the ranking input reader with the Joomla database driver.
     * Stores the database connection used by the native ranking calculator to load projects, teams, configuration and fixtures.
     *
     * @param DatabaseInterface $db Joomla database driver.
     * @return void
     */
    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    /**
     * Assemble the complete data set for a project's native ranking table.
     * Loads the project, inherited ranking template settings, eligible teams, published scored matches and optional round-range filters, then parses colour rules for display.
     *
     * @param int $projectId Ranking project identifier.
     * @param int $divisionId Optional division to limit teams and games.
     * @param int $fromRoundId Optional first round in the requested range.
     * @param int $toRoundId Optional last round in the requested range.
     * @return array{project:?object,teams:array<int,RankingRow>,matches:array<int,object>,config:array,colors:array} Ranking engine input.
     */
    public function load(int $projectId, int $divisionId = 0, int $fromRoundId = 0, int $toRoundId = 0): array
    {
        $project = $this->loadProject($projectId);
        if (!$project) {
            return ['project' => null, 'teams' => [], 'matches' => [], 'config' => [], 'colors' => []];
        }

        $config = $this->loadTemplateConfig('ranking', $project);
        $teams = $this->loadTeams($projectId, $divisionId);
        $matches = $this->loadMatches($projectId, $divisionId, (string) ($project->sport_type_name ?? ''));
        $matches = $this->filterRoundRange($matches, $projectId, $fromRoundId, $toRoundId);

        return [
            'project' => $project,
            'teams' => $teams,
            'matches' => $matches,
            'config' => $config,
            'colors' => $this->parseColors($config['colors'] ?? ''),
        ];
    }

    /**
     * Read a ranking project's sport, season, league and active round metadata.
     * Joins the project to its sport type and optional season, league and round, generating the ID:alias slugs required by frontend links.
     *
     * @param int $projectId Requested SportsManagement project ID.
     * @return object|null Project metadata with routing slugs, or null.
     */
    private function loadProject(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $db = $this->db;
        $query = $db->getQuery(true)
            ->select([
                'p.*',
                $db->quoteName('st.id', 'sport_type_id'),
                $db->quoteName('st.name', 'sport_type_name'),
                $db->quoteName('s.name', 'season_name'),
                $db->quoteName('l.name', 'league_name'),
                $db->quoteName('l.country', 'league_country'),
                "CONCAT_WS(':', p.id, p.alias) AS slug",
                "CONCAT_WS(':', s.id, s.alias) AS season_slug",
                "CONCAT_WS(':', l.id, l.alias) AS league_slug",
                "CONCAT_WS(':', r.id, r.alias) AS round_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('p.sports_type_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('p.current_round'))
            ->where($db->quoteName('p.id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }

    /**
     * Resolve a project's effective ranking template configuration.
     * Starts with defaults, reads project overrides and then master-template overrides when available, safely falling back when configuration data is missing or malformed.
     *
     * @param string $template Ranking template name.
     * @param object $project Project record including project and master-template IDs.
     * @return array Effective template settings with default values.
     */
    private function loadTemplateConfig(string $template, object $project): array
    {
        $defaults = $this->loadDefaultTemplateConfig($template);
        $projectId = (int) ($project->id ?? 0);
        $params = $projectId > 0 ? $this->loadSavedTemplateParams($template, $projectId) : null;

        if ($params === null) {
            $masterId = (int) ($project->master_template ?? 0);
            if ($masterId > 0 && $masterId !== $projectId) {
                $params = $this->loadSavedTemplateParams($template, $masterId);
            }
        }

        if ($params === null || trim($params) === '') {
            return $defaults;
        }

        try {
            $registry = new Registry();
            $registry->loadString($params);
            return array_merge($defaults, $registry->toArray());
        } catch (\Throwable) {
            return $defaults;
        }
    }

    /**
     * Load a saved template's raw registry configuration string.
     * Uses bound project and template keys to retrieve one persisted template configuration row.
     *
     * @param string $template Target template name.
     * @param int $projectId Project or master-template project identifier.
     * @return string|null Stored registry parameters, or null when absent.
     */
    private function loadSavedTemplateParams(string $template, int $projectId): ?string
    {
        $db = $this->db;
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__sportsmanagement_template_config'))
            ->where($db->quoteName('template') . ' = :template')
            ->where($db->quoteName('project_id') . ' = :projectId')
            ->bind(':template', $template, ParameterType::STRING)
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $value = $db->loadResult();
        return $value === null ? null : (string) $value;
    }

    /**
     * Read installed default ranking settings from a trusted template XML file.
     * Initialises core ranking and sort defaults, optionally loads XML field default attributes and preserves built-in values when the file or SimpleXML is unavailable.
     *
     * @param string $template Name of the installed template's default XML file.
     * @return array<string,mixed> Default ranking configuration values.
     */
    private function loadDefaultTemplateConfig(string $template): array
    {
        $defaults = [
            'ranking_order' => self::DEFAULT_RANKING_ORDER,
            'ranking_sort_order' => self::DEFAULT_SORT_ORDER,
            'colors' => '',
        ];
        $file = JPATH_SITE . '/components/com_sportsmanagement/settings/default/' . basename($template) . '.xml';
        if (!is_file($file) || !function_exists('simplexml_load_file')) {
            return $defaults;
        }

        try {
            $xml = simplexml_load_file($file);
            if ($xml !== false) {
                foreach ($xml->xpath('//field[@name]') ?: [] as $field) {
                    $attributes = $field->attributes();
                    if (isset($attributes['default'])) {
                        $defaults[(string) $attributes['name']] = (string) $attributes['default'];
                    }
                }
            }
        } catch (\Throwable) {
        }
        return $defaults;
    }

    /**
     * Create ranking rows from a project's eligible team and final-table relations.
     * Tries division-scoped project teams, stored final-division rows and fallback final standings, preserving precomputed scores and team metadata when final values are enabled.
     *
     * @param int $projectId Owning project ID.
     * @param int $divisionId Optional division ID.
     * @return array<int,RankingRow> Initial ranking rows keyed by project-team ID.
     */
    private function loadTeams(int $projectId, int $divisionId): array
    {
        $records = $this->queryProjectTeams($projectId, $divisionId, false);
        if (!$records && $divisionId > 0) {
            $records = $this->queryProjectTeams($projectId, $divisionId, true);
        }
        if (!$records) {
            $records = $this->queryFinalProjectTeams($projectId);
        }

        $rows = [];
        foreach ($records as $record) {
            $ptid = (int) ($record->projectteamid ?? 0);
            if ($ptid <= 0) {
                continue;
            }
            $row = new RankingRow($ptid);
            $row->teamid = (int) ($record->id ?? 0);
            $row->divisionid = (int) ($record->division_id ?? 0);
            if ($divisionId > 0 && $row->divisionid === 0) {
                $row->divisionid = $divisionId;
            }
            $row->start_points = (float) ($record->start_points ?? 0);
            $row->neg_points = (float) ($record->neg_points_finally ?? 0);
            $row->penalty_points = (float) ($record->penalty_points ?? 0);
            $row->is_in_score = (int) ($record->is_in_score ?? 1) === 1;
            $row->use_finally = (int) ($record->use_finally ?? 0) === 1;
            $row->finaltablerank = (int) ($record->finaltablerank ?? 0);
            $row->name = (string) ($record->name ?? '');
            $row->team = $record;

            if ($row->use_finally) {
                $row->sum_points = (float) ($record->points_finally ?? 0);
                $row->neg_points = (float) ($record->neg_points_finally ?? 0);
                $row->cnt_matches = (int) ($record->matches_finally ?? 0);
                $row->cnt_won = (int) ($record->won_finally ?? 0);
                $row->cnt_draw = (int) ($record->draws_finally ?? 0);
                $row->cnt_lost = (int) ($record->lost_finally ?? 0);
                $row->sum_team1_result = (float) ($record->homegoals_finally ?? 0);
                $row->sum_team2_result = (float) ($record->guestgoals_finally ?? 0);
                $row->diff_team_results = (float) ($record->diffgoals_finally ?? 0);
                $row->points = $row->sum_points;
                $row->scorefor = $row->sum_team1_result;
                $row->scoreagainst = $row->sum_team2_result;
                $row->goalsfor = $row->sum_team1_result;
                $row->goalsagainst = $row->sum_team2_result;
            }
            $rows[$ptid] = $row;
        }
        return $rows;
    }

    /**
     * Build a consistent field selection for ranking team and final-division queries.
     * Chooses which score and division relation supplies the standings values, while including team, club, logo and route slug metadata.
     *
     * @param bool $divisionRelation Whether final-division values come from the division relation.
     * @return array<int,string> Quoted SQL selection columns and aliases.
     */
    private function projectTeamSelect(bool $divisionRelation): array
    {
        $db = $this->db;
        $prefix = $divisionRelation ? 'ptd' : 'pt';
        return [
            $db->quoteName('pt.id', 'projectteamid'),
            $db->quoteName('pt.is_in_score'),
            $db->quoteName($prefix . '.start_points', 'start_points'),
            $db->quoteName($prefix . '.division_id', 'division_id'),
            $db->quoteName($prefix . '.use_finally', 'use_finally'),
            $db->quoteName($prefix . '.points_finally', 'points_finally'),
            $db->quoteName(($divisionRelation ? 'pt' : $prefix) . '.neg_points_finally', 'neg_points_finally'),
            $db->quoteName($prefix . '.matches_finally', 'matches_finally'),
            $db->quoteName($prefix . '.won_finally', 'won_finally'),
            $db->quoteName($prefix . '.draws_finally', 'draws_finally'),
            $db->quoteName($prefix . '.lost_finally', 'lost_finally'),
            $db->quoteName($prefix . '.homegoals_finally', 'homegoals_finally'),
            $db->quoteName($prefix . '.guestgoals_finally', 'guestgoals_finally'),
            $db->quoteName($prefix . '.diffgoals_finally', 'diffgoals_finally'),
            $db->quoteName($prefix . '.finaltablerank', 'finaltablerank'),
            $db->quoteName(($divisionRelation ? 'ptd' : 'pt') . '.penalty_points', 'penalty_points'),
            $db->quoteName('pt.standard_playground'),
            $db->quoteName('pt.picture', 'projectteam_picture'),
            $db->quoteName('t.id'),
            $db->quoteName('t.name'),
            $db->quoteName('t.short_name'),
            $db->quoteName('t.middle_name'),
            $db->quoteName('t.picture', 'team_picture'),
            $db->quoteName('t.club_id'),
            $db->quoteName('st.teamname'),
            $db->quoteName('st.season_teamname'),
            $db->quoteName('c.name', 'club_name'),
            $db->quoteName('c.logo_small'),
            $db->quoteName('c.logo_middle'),
            $db->quoteName('c.logo_big'),
            $db->quoteName('c.country'),
            $db->quoteName('c.trikot_home'),
            $db->quoteName('c.trikot_away'),
            "CONCAT_WS(':', t.id, t.alias) AS team_slug",
            "CONCAT_WS(':', pt.id, t.alias) AS projectteam_slug",
            "CONCAT_WS(':', d.id, d.alias) AS division_slug",
            "CONCAT_WS(':', c.id, c.alias) AS club_slug",
        ];
    }

    /**
     * Load project teams using normal or final-division scoring policies.
     * Joins season teams, club and division data and optionally restricts teams to matches in a division or recorded final-division standings.
     *
     * @param int $projectId Owning project identifier.
     * @param int $divisionId Optional division identifier.
     * @param bool $divisionFinal Whether to select final division-related standings.
     * @return array<int,object> Eligible team and ranking-metadata records.
     */
    private function queryProjectTeams(int $projectId, int $divisionId, bool $divisionFinal): array
    {
        $db = $this->db;
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . implode(', ', $this->projectTeamSelect($divisionFinal)))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'));

        if ($divisionFinal) {
            $query->join('INNER', $db->quoteName('#__sportsmanagement_project_team_division', 'ptd') . ' ON ' . $db->quoteName('ptd.team_id') . ' = ' . $db->quoteName('pt.id') . ' AND ' . $db->quoteName('ptd.project_id') . ' = ' . $db->quoteName('pt.project_id'));
        }

        $query->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd') . ' ON ' . $db->quoteName('d.id') . ' = ' . ($divisionFinal ? $db->quoteName('ptd.division_id') : $db->quoteName('pt.division_id')))
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        if ($divisionFinal) {
            $query->where($db->quoteName('ptd.is_in_score') . ' = 1')
                ->where($db->quoteName('ptd.use_finally') . ' = 1')
                ->where($db->quoteName('ptd.division_id') . ' = :divisionId')
                ->bind(':divisionId', $divisionId, ParameterType::INTEGER);
        } else {
            $query->where($db->quoteName('pt.is_in_score') . ' = 1');
            if ($divisionId > 0) {
                $query->join('INNER', $db->quoteName('#__sportsmanagement_match', 'dm') . ' ON (' . $db->quoteName('dm.projectteam1_id') . ' = ' . $db->quoteName('pt.id') . ' OR ' . $db->quoteName('dm.projectteam2_id') . ' = ' . $db->quoteName('pt.id') . ')')
                    ->where($db->quoteName('dm.division_id') . ' = :divisionId')
                    ->bind(':divisionId', $divisionId, ParameterType::INTEGER);
            }
        }
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    /**
     * Read project teams with stored final standings when ordinary team queries are empty.
     * Uses the project-team final-score flag to return only teams eligible for final-table results.
     *
     * @param int $projectId Owning ranking project.
     * @return array<int,object> Project-team records with saved final standing values.
     */
    private function queryFinalProjectTeams(int $projectId): array
    {
        $db = $this->db;
        $query = $db->getQuery(true)
            ->select($this->projectTeamSelect(false))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd') . ' ON ' . $db->quoteName('d.id') . ' = ' . $db->quoteName('pt.division_id'))
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->where($db->quoteName('pt.is_in_score') . ' = 1')
            ->where($db->quoteName('pt.use_finally') . ' = 1')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    /**
     * Load published, uncancelled, countable fixtures required for ranking calculation.
     * Restricts matches to project and optional division, keeps sport-specific small-bore scoring policy and maps each fixture's score, bonus, decision and round details.
     *
     * @param int $projectId Ranking project identifier.
     * @param int $divisionId Optional division filter.
     * @param string $sportType Sport type controlling special result requirements.
     * @return array<int,object> Countable match records keyed by match ID.
     */
    private function loadMatches(int $projectId, int $divisionId, string $sportType): array
    {
        $db = $this->db;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id'), $db->quoteName('m.projectteam1_id'), $db->quoteName('m.projectteam2_id'),
                $db->quoteName('m.team1_result', 'home_score'), $db->quoteName('m.team2_result', 'away_score'),
                $db->quoteName('m.team1_bonus', 'home_bonus'), $db->quoteName('m.team2_bonus', 'away_bonus'),
                $db->quoteName('m.team1_legs', 'l1'), $db->quoteName('m.team2_legs', 'l2'),
                $db->quoteName('m.team1_result_split', 'ls1'), $db->quoteName('m.team2_result_split', 'ls2'),
                $db->quoteName('m.team1_single_matchpoint', 'mp1'), $db->quoteName('m.team2_single_matchpoint', 'mp2'),
                $db->quoteName('m.team1_single_sets', 'se1'), $db->quoteName('m.team2_single_sets', 'se2'),
                $db->quoteName('m.team1_single_games', 'ga1'), $db->quoteName('m.team2_single_games', 'ga2'),
                $db->quoteName('m.match_result_type'), $db->quoteName('m.alt_decision', 'decision'),
                $db->quoteName('m.team1_result_decision', 'home_score_decision'), $db->quoteName('m.team2_result_decision', 'away_score_decision'),
                $db->quoteName('m.team1_result_ot', 'home_score_ot'), $db->quoteName('m.team2_result_ot', 'away_score_ot'),
                $db->quoteName('m.team1_result_so', 'home_score_so'), $db->quoteName('m.team2_result_so', 'away_score_so'),
                $db->quoteName('m.team_won'), $db->quoteName('r.id', 'roundid'), $db->quoteName('r.roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->where($db->quoteName('pt1.project_id') . ' = :projectId')
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('r.published') . ' = 1')
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)')
            ->where($db->quoteName('m.count_result') . ' = 1')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        if ($sportType !== 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION') {
            $query->where('( (' . $db->quoteName('m.team1_result') . ' IS NOT NULL AND ' . $db->quoteName('m.team2_result') . ' IS NOT NULL) OR ' . $db->quoteName('m.alt_decision') . ' = 1 )')
                ->where($db->quoteName('m.projectteam1_id') . ' > 0')
                ->where($db->quoteName('m.projectteam2_id') . ' > 0');
        }
        if ($divisionId > 0) {
            $query->where($db->quoteName('m.division_id') . ' = :divisionId')
                ->bind(':divisionId', $divisionId, ParameterType::INTEGER);
        }
        $db->setQuery($query);
        $matches = [];
        foreach ($db->loadObjectList() ?: [] as $row) {
            $matches[(int) $row->id] = $row;
        }
        return $matches;
    }

    /**
     * Limit ranking fixtures to the requested inclusive round-code interval.
     * Looks up the parent project's round codes and retains fixtures between
     * selected boundaries while preserving match keys. Unknown round IDs and
     * reversed ranges produce an empty selection rather than no restriction.
     * Unexpected or malformed round rows are ignored.
     *
     * @param array<int,object> $matches Previously loaded ranking matches.
     * @param int $projectId Project whose rounds define the range.
     * @param int $fromRoundId Optional first round identifier.
     * @param int $toRoundId Optional last round identifier.
     * @return array<int,object> Fixtures within the selected round-code interval.
     */
    private function filterRoundRange(array $matches, int $projectId, int $fromRoundId, int $toRoundId): array
    {
        if ($fromRoundId <= 0 && $toRoundId <= 0) {
            return $matches;
        }
        $db = $this->db;
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('roundcode')])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);
        $codes = [];
        foreach ($db->loadObjectList() ?: [] as $round) {
            if (!is_object($round) || !isset($round->id, $round->roundcode)
                || !is_scalar($round->id) || !is_scalar($round->roundcode)) {
                continue;
            }

            $roundId = filter_var($round->id, FILTER_VALIDATE_INT);
            $roundCode = filter_var($round->roundcode, FILTER_VALIDATE_INT);

            if ($roundId === false || $roundId <= 0 || $roundCode === false) {
                continue;
            }

            $codes[$roundId] = $roundCode;
        }
        // An explicitly requested round missing from this project must not
        // silently turn into an unbounded range (and include other matches).
        if (($fromRoundId > 0 && !array_key_exists($fromRoundId, $codes))
            || ($toRoundId > 0 && !array_key_exists($toRoundId, $codes))) {
            return [];
        }

        $from = $fromRoundId > 0 ? $codes[$fromRoundId] : null;
        $to = $toRoundId > 0 ? $codes[$toRoundId] : null;

        if ($from !== null && $to !== null && $from > $to) {
            return [];
        }

        return array_filter($matches, static function ($match) use ($codes, $from, $to): bool {
            $code = $codes[(int) ($match->roundid ?? 0)] ?? null;
            return $code !== null && ($from === null || $code >= $from) && ($to === null || $code <= $to);
        });
    }

    /**
     * Parse legacy ranking-colour rules into structured ranges.
     * Splits semicolon-separated rules into from, to, colour and description
     * fields while ignoring incomplete entries. Corrupt array/object settings
     * are treated as empty rather than cast to a string with PHP warnings.
     *
     * @param mixed $config Stored ranking colour definitions, normally a string.
     * @return array<int,array{from:string,to:string,color:string,description:string}> Parsed ranking-colour rules.
     */
    private function parseColors(mixed $config): array
    {
        if (!is_string($config) || trim($config) === '') {
            return [];
        }

        $colors = [];
        foreach (array_filter(array_map('trim', explode(';', $config))) as $item) {
            $parts = array_map('trim', explode(',', $item, 4));
            if (count($parts) === 4) {
                $colors[] = ['from' => $parts[0], 'to' => $parts[1], 'color' => $parts[2], 'description' => $parts[3]];
            }
        }
        return $colors;
    }
}
