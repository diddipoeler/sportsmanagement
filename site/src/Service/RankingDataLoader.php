<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Database-only input loader for the native ranking engine.
 */
final class RankingDataLoader
{
    private const DEFAULT_RANKING_ORDER = 'POINTS, PLAYEDASC, DIFF, FOR';
    private const DEFAULT_SORT_ORDER = 'DESC, ASC, DESC, DESC';

    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    /**
     * @return array{project:?object, teams:array<int,RankingRow>, matches:array<int,object>, config:array, colors:array}
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
            'colors' => $this->parseColors((string) ($config['colors'] ?? '')),
        ];
    }

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
            ->where($db->quoteName('p.id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }

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

    private function loadSavedTemplateParams(string $template, int $projectId): ?string
    {
        $db = $this->db;
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__sportsmanagement_template_config'))
            ->where($db->quoteName('template') . ' = ' . $db->quote($template))
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);
        $value = $db->loadResult();
        return $value === null ? null : (string) $value;
    }

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

    /** @return array<int, RankingRow> */
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
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId);

        if ($divisionFinal) {
            $query->where($db->quoteName('ptd.is_in_score') . ' = 1')
                ->where($db->quoteName('ptd.use_finally') . ' = 1')
                ->where($db->quoteName('ptd.division_id') . ' = ' . $divisionId);
        } else {
            $query->where($db->quoteName('pt.is_in_score') . ' = 1');
            if ($divisionId > 0) {
                $query->join('INNER', $db->quoteName('#__sportsmanagement_match', 'dm') . ' ON (' . $db->quoteName('dm.projectteam1_id') . ' = ' . $db->quoteName('pt.id') . ' OR ' . $db->quoteName('dm.projectteam2_id') . ' = ' . $db->quoteName('pt.id') . ')')
                    ->where($db->quoteName('dm.division_id') . ' = ' . $divisionId);
            }
        }
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

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
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('pt.is_in_score') . ' = 1')
            ->where($db->quoteName('pt.use_finally') . ' = 1');
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    /** @return array<int, object> */
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
            ->where($db->quoteName('pt1.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('r.published') . ' = 1')
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)')
            ->where($db->quoteName('m.count_result') . ' = 1');

        if ($sportType !== 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION') {
            $query->where('( (' . $db->quoteName('m.team1_result') . ' IS NOT NULL AND ' . $db->quoteName('m.team2_result') . ' IS NOT NULL) OR ' . $db->quoteName('m.alt_decision') . ' = 1 )')
                ->where($db->quoteName('m.projectteam1_id') . ' > 0')
                ->where($db->quoteName('m.projectteam2_id') . ' > 0');
        }
        if ($divisionId > 0) {
            $query->where($db->quoteName('m.division_id') . ' = ' . $divisionId);
        }
        $db->setQuery($query);
        $matches = [];
        foreach ($db->loadObjectList() ?: [] as $row) {
            $matches[(int) $row->id] = $row;
        }
        return $matches;
    }

    private function filterRoundRange(array $matches, int $projectId, int $fromRoundId, int $toRoundId): array
    {
        if ($fromRoundId <= 0 && $toRoundId <= 0) {
            return $matches;
        }
        $db = $this->db;
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('roundcode')])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query);
        $codes = [];
        foreach ($db->loadObjectList() ?: [] as $round) {
            $codes[(int) $round->id] = (int) $round->roundcode;
        }
        $from = $fromRoundId > 0 ? ($codes[$fromRoundId] ?? null) : null;
        $to = $toRoundId > 0 ? ($codes[$toRoundId] ?? null) : null;
        return array_filter($matches, static function ($match) use ($codes, $from, $to): bool {
            $code = $codes[(int) ($match->roundid ?? 0)] ?? null;
            return $code !== null && ($from === null || $code >= $from) && ($to === null || $code <= $to);
        });
    }

    private function parseColors(string $config): array
    {
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
