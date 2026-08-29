<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Native Joomla 5/6 MVC model for the team plan.
 *
 * Project context, rounds, teams, favourites, project events, matches,
 * divisions and referee data are all resolved through the namespaced model
 * hierarchy and Joomla's database interface.
 */
final class TeamplanModel extends SportsManagementProjectModel
{
    use TeamplanEventDataTrait;

    private int $teamId = 0;
    private int $projectTeamId = 0;
    private int $mode = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        $this->teamId = max(0, $input->getInt('tid', 0));
        $this->projectTeamId = max(0, $input->getInt('ptid', 0));
        $this->mode = max(0, $input->getInt('mode', 0));
    }

    public function getPlanRounds(string $ordering = 'ASC'): array
    {
        return $this->getRounds($this->normaliseOrdering($ordering), true);
    }

    public function getPlanTeams(): array
    {
        $teams = [];

        // Legacy teamplan templates address teams by project_team.id, not by
        // the underlying team id. Keep that established array contract.
        foreach ($this->getProjectTeams(0) as $team) {
            $projectTeamId = (int) ($team->projectteamid ?? 0);
            if ($projectTeamId > 0) {
                $teams[$projectTeamId] = $team;
            }
        }

        return $teams;
    }

    public function getPlanFavTeams(): array
    {
        return $this->getFavTeams();
    }

    public function getPlanProjectEvents(): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ])
            ->from($db->quoteName('#__sportsmanagement_eventtype', 'et'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_position_eventtype', 'pet')
                . ' ON ' . $db->quoteName('pet.eventtype_id') . ' = ' . $db->quoteName('et.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pet.position_id')
            )
            ->where($db->quoteName('ppos.project_id') . ' = ' . $this->projectId)
            ->group([
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ]);

        $db->setQuery($query);

        return $db->loadObjectList('id') ?: [];
    }

    public function getPlanDivision(): ?object
    {
        return $this->getDivision();
    }

    public function getProjectTeamId(): int
    {
        if ($this->projectId <= 0 || $this->teamId <= 0) {
            $this->projectTeamId = 0;
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('pt.id'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
            )
            ->where($db->quoteName('pt.project_id') . ' = ' . $this->projectId)
            ->where($db->quoteName('t.id') . ' = ' . $this->teamId);

        $db->setQuery($query, 0, 1);
        $this->projectTeamId = (int) $db->loadResult();

        return $this->projectTeamId;
    }

    public function getMatches(array $config): array
    {
        $ordering = $this->normaliseOrdering((string) ($config['plan_order'] ?? 'DESC'));

        return $this->loadPlanMatches(
            $ordering,
            0,
            true,
            !empty($config['show_referee'])
        );
    }

    public function getMatchesRefering(array $config): array
    {
        $ordering = $this->normaliseOrdering((string) ($config['plan_order'] ?? 'DESC'));
        $this->ensureProjectTeamId();

        return $this->loadPlanMatches(
            $ordering,
            $this->projectTeamId,
            true,
            !empty($config['show_referee'])
        );
    }

    public function getMatchesPerRound(array $config, array $rounds): array
    {
        $ordering = $this->normaliseOrdering((string) ($config['plan_order'] ?? 'DESC'));
        $this->ensureProjectTeamId();
        $matchesPerRound = [];

        foreach ($rounds as $round) {
            $roundCode = (int) ($round->roundcode ?? 0);
            $matchesPerRound[$roundCode] = $this->loadRoundMatches(
                $roundCode,
                $ordering,
                true,
                !empty($config['show_referee'])
            );
        }

        return $matchesPerRound;
    }

    private function loadRoundMatches(
        int $roundCode,
        string $ordering,
        bool $withPlayground,
        bool $withReferees
    ): array {
        if ($this->projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('matches.*')
            ->from($db->quoteName('#__sportsmanagement_match', 'matches'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('matches.round_id') . ' = ' . $db->quoteName('r.id')
            )
            ->where($db->quoteName('r.project_id') . ' = ' . $this->projectId)
            ->where($db->quoteName('r.roundcode') . ' = ' . $roundCode)
            ->where($db->quoteName('matches.published') . ' = 1')
            ->order($db->quoteName('matches.match_date') . ' ' . $ordering . ', ' . $db->quoteName('matches.match_number'));

        if ($this->projectTeamId > 0) {
            $query->where(
                '(' . $db->quoteName('matches.projectteam1_id') . ' = ' . $this->projectTeamId
                . ' OR ' . $db->quoteName('matches.projectteam2_id') . ' = ' . $this->projectTeamId . ')'
            );
        }

        if ($this->divisionId > 0) {
            $query
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_project_team', 'pt1')
                    . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('matches.projectteam1_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_project_team', 'pt2')
                    . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('matches.projectteam2_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_division', 'd1')
                    . ' ON ' . $db->quoteName('d1.id') . ' = ' . $db->quoteName('pt1.division_id')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_division', 'd2')
                    . ' ON ' . $db->quoteName('d2.id') . ' = ' . $db->quoteName('pt2.division_id')
                )
                ->where(
                    '(' . $db->quoteName('d1.id') . ' = ' . $this->divisionId
                    . ' OR ' . $db->quoteName('d1.parent_id') . ' = ' . $this->divisionId
                    . ' OR ' . $db->quoteName('d2.id') . ' = ' . $this->divisionId
                    . ' OR ' . $db->quoteName('d2.parent_id') . ' = ' . $this->divisionId
                    . ' OR ' . $db->quoteName('matches.division_id') . ' = ' . $this->divisionId . ')'
                );
        }

        if ($withPlayground) {
            $query
                ->select([
                    $db->quoteName('playground.name', 'playground_name'),
                    $db->quoteName('playground.short_name', 'playground_short_name'),
                ])
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_playground', 'playground')
                    . ' ON ' . $db->quoteName('playground.id') . ' = ' . $db->quoteName('matches.playground_id')
                );
        }

        $db->setQuery($query);
        $matches = $db->loadObjectList() ?: [];

        if ($withReferees) {
            $this->loadRefereesByMatch($matches);
        }

        return $matches;
    }

    private function loadPlanMatches(
        string $ordering,
        int $refereeProjectTeamId,
        bool $withPlayground,
        bool $withReferees
    ): array {
        if ($this->projectId <= 0) {
            return [];
        }

        $this->ensureProjectTeamId();
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'm.*',
                'DATE_FORMAT(m.time_present, "%H:%i") AS time_present',
                $db->quoteName('r.roundcode'),
                $db->quoteName('r.id', 'roundid'),
                $db->quoteName('r.project_id'),
                $db->quoteName('r.name'),
                $db->quoteName('t1.id', 'team1'),
                $db->quoteName('t2.id', 'team2'),
                'CONCAT_WS(\':\', m.id, CONCAT_WS("_", t1.alias, t2.alias)) AS match_slug',
                'CONCAT_WS(\':\', r.id, r.alias) AS round_slug',
                'CONCAT_WS(\':\', p.id, p.alias) AS project_slug',
                'CONCAT_WS(\':\', d.id, d.alias) AS division_slug',
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_division', 'd')
                . ' ON ' . $db->quoteName('d.id') . ' = ' . $db->quoteName('m.division_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'pt1')
                . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st1')
                . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 't1')
                . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'pt2')
                . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st2')
                . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 't2')
                . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id')
            )
            ->where($db->quoteName('m.published') . ' = 1');

        if ($this->mode === 1 && $this->projectTeamId > 0) {
            $query->where(
                '((' . $db->quoteName('m.projectteam1_id') . ' = ' . $this->projectTeamId
                . ' AND ' . $db->quoteName('m.team1_result') . ' > ' . $db->quoteName('m.team2_result') . ')'
                . ' OR (' . $db->quoteName('m.projectteam2_id') . ' = ' . $this->projectTeamId
                . ' AND ' . $db->quoteName('m.team1_result') . ' < ' . $db->quoteName('m.team2_result') . '))'
            );
        } elseif ($this->mode === 2) {
            $query->where($db->quoteName('m.team1_result') . ' = ' . $db->quoteName('m.team2_result'));
        } elseif ($this->mode === 3 && $this->projectTeamId > 0) {
            $query->where(
                '((' . $db->quoteName('m.projectteam1_id') . ' = ' . $this->projectTeamId
                . ' AND ' . $db->quoteName('m.team1_result') . ' < ' . $db->quoteName('m.team2_result') . ')'
                . ' OR (' . $db->quoteName('m.projectteam2_id') . ' = ' . $this->projectTeamId
                . ' AND ' . $db->quoteName('m.team1_result') . ' > ' . $db->quoteName('m.team2_result') . '))'
            );
        }

        $divisionTeamIds = $this->getDirectChildDivisionIds();
        if ($divisionTeamIds !== []) {
            $ids = implode(',', $divisionTeamIds);
            $query->where(
                '(' . $db->quoteName('pt1.division_id') . ' IN (' . $ids . ')'
                . ' OR ' . $db->quoteName('pt2.division_id') . ' IN (' . $ids . ')'
                . ' OR ' . $db->quoteName('m.division_id') . ' IN (' . $ids . '))'
            );
        }

        if ($refereeProjectTeamId > 0) {
            $project = $this->getProject();
            $seasonId = (int) ($project->season_id ?? 0);
            $query
                ->select($db->quoteName('p.name', 'project_name'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_match_referee', 'mref')
                    . ' ON ' . $db->quoteName('mref.match_id') . ' = ' . $db->quoteName('m.id')
                )
                ->where($db->quoteName('mref.project_referee_id') . ' = ' . $refereeProjectTeamId);

            if ($seasonId > 0) {
                $query->where($db->quoteName('p.season_id') . ' = ' . $seasonId);
            }
        } else {
            $query->where($db->quoteName('r.project_id') . ' = ' . $this->projectId);
        }

        if ($this->teamId > 0 && $this->projectTeamId > 0) {
            $query->where(
                '(' . $db->quoteName('m.projectteam1_id') . ' = ' . $this->projectTeamId
                . ' OR ' . $db->quoteName('m.projectteam2_id') . ' = ' . $this->projectTeamId . ')'
            );
        }

        $query->order([
            $db->quoteName('r.roundcode') . ' ' . $ordering,
            $db->quoteName('m.match_date'),
            $db->quoteName('m.match_number'),
        ]);

        if ($withPlayground) {
            $query
                ->select([
                    $db->quoteName('playground.name', 'playground_name'),
                    $db->quoteName('playground.short_name', 'playground_short_name'),
                    'CONCAT_WS(\':\', playground.id, playground.alias) AS playground_slug',
                ])
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_playground', 'playground')
                    . ' ON ' . $db->quoteName('playground.id') . ' = ' . $db->quoteName('m.playground_id')
                );
        }

        $db->setQuery($query);
        $matches = $db->loadObjectList() ?: [];

        if ($withReferees) {
            $this->loadRefereesByMatch($matches);
        }

        return $matches;
    }

    private function loadRefereesByMatch(array $matches): void
    {
        if ($matches === []) {
            return;
        }

        $project = $this->getProject();
        $teamsAsReferees = !empty($project->teams_as_referees);
        $db = $this->getDatabase();

        foreach ($matches as $match) {
            $matchId = (int) ($match->id ?? 0);
            if ($matchId <= 0) {
                $match->referees = [];
                continue;
            }

            $query = $db->getQuery(true);

            if ($teamsAsReferees) {
                $query
                    ->select([
                        $db->quoteName('mr.project_referee_id', 'value'),
                        $db->quoteName('t.name', 'referee_name'),
                        $db->quoteName('pos.name', 'position_name'),
                        $db->quoteName('pos.ordering'),
                    ])
                    ->from($db->quoteName('#__sportsmanagement_match_referee', 'mr'))
                    ->join(
                        'LEFT',
                        $db->quoteName('#__sportsmanagement_project_team', 'pt')
                        . ' ON ' . $db->quoteName('pt.id') . ' = ' . $db->quoteName('mr.project_referee_id')
                    )
                    ->join(
                        'LEFT',
                        $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                        . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
                    )
                    ->join(
                        'LEFT',
                        $db->quoteName('#__sportsmanagement_team', 't')
                        . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
                    )
                    ->join(
                        'LEFT',
                        $db->quoteName('#__sportsmanagement_position', 'pos')
                        . ' ON ' . $db->quoteName('mr.project_position_id') . ' = ' . $db->quoteName('pos.id')
                    )
                    ->where($db->quoteName('mr.match_id') . ' = ' . $matchId)
                    ->order([
                        $db->quoteName('pos.name'),
                        $db->quoteName('mr.ordering') . ' ASC',
                    ]);
            } else {
                $query
                    ->select([
                        $db->quoteName('ref.firstname', 'referee_firstname'),
                        $db->quoteName('ref.lastname', 'referee_lastname'),
                        $db->quoteName('ref.id', 'referee_id'),
                        $db->quoteName('ref.nickname', 'referee_nickname'),
                        $db->quoteName('ppos.position_id'),
                        $db->quoteName('pos.name', 'referee_position_name'),
                        $db->quoteName('pos.ordering'),
                    ])
                    ->from($db->quoteName('#__sportsmanagement_person', 'ref'))
                    ->join(
                        'LEFT',
                        $db->quoteName('#__sportsmanagement_season_person_id', 'sp')
                        . ' ON ' . $db->quoteName('sp.person_id') . ' = ' . $db->quoteName('ref.id')
                    )
                    ->join(
                        'LEFT',
                        $db->quoteName('#__sportsmanagement_project_referee', 'pref')
                        . ' ON ' . $db->quoteName('pref.person_id') . ' = ' . $db->quoteName('sp.id')
                    )
                    ->join(
                        'LEFT',
                        $db->quoteName('#__sportsmanagement_match_referee', 'link')
                        . ' ON ' . $db->quoteName('link.project_referee_id') . ' = ' . $db->quoteName('pref.id')
                    )
                    ->join(
                        'INNER',
                        $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                        . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('link.project_position_id')
                    )
                    ->join(
                        'INNER',
                        $db->quoteName('#__sportsmanagement_position', 'pos')
                        . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id')
                    )
                    ->where($db->quoteName('link.match_id') . ' = ' . $matchId)
                    ->where($db->quoteName('ref.published') . ' = 1')
                    ->order($db->quoteName('link.ordering'));
            }

            $db->setQuery($query);
            $match->referees = $db->loadObjectList() ?: [];
        }
    }

    private function getDirectChildDivisionIds(): array
    {
        if ($this->divisionId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('parent_id') . ' = ' . $this->divisionId);
        $db->setQuery($query);

        return array_values(array_filter(array_map('intval', $db->loadColumn() ?: [])));
    }

    private function ensureProjectTeamId(): void
    {
        if ($this->projectTeamId <= 0 && $this->teamId > 0) {
            $this->getProjectTeamId();
        }
    }

    private function normaliseOrdering(string $ordering): string
    {
        return strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';
    }
}
