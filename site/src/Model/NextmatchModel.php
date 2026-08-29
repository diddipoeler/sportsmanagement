<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Throwable;

/**
 * Native Joomla 5/6 model for the next-match view.
 *
 * Match, team, referee and history data use the component database resolver.
 * The established JSMRanking helper remains a compatibility boundary until
 * the ranking engine itself has been migrated.
 */
final class NextmatchModel extends SportsManagementProjectModel
{
    private int $matchId = 0;
    private int $projectTeamId = 0;
    private int $showPics = 0;
    private int $databaseSelector = 0;
    private int $homeDivisionId = 0;
    private int $awayDivisionId = 0;

    private ?object $match = null;
    private ?array $teams = null;
    private ?array $ranking = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        $this->matchId = max(0, $input->getInt('mid', 0));
        $this->projectTeamId = max(0, $input->getInt('ptid', 0));
        $this->showPics = max(0, $input->getInt('pics', 0));
        $this->databaseSelector = $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;

        if ($this->projectTeamId > 0) {
            $this->getSpecifiedMatch($this->projectId, $this->projectTeamId, $this->matchId);
        }
    }

    public function getDatabaseSelector(): int
    {
        return $this->databaseSelector;
    }

    public function getShowPics(): int
    {
        return $this->showPics;
    }

    public function getSpecifiedMatch(int $projectId, int $projectTeamId, int $matchId): ?object
    {
        if ($this->match !== null) {
            return $this->match;
        }

        $db = $this->getDatabase();
        $expiryTime = max(0, (int) ($this->getTemplateConfig('nextmatch')['expiry_time'] ?? 0));
        $query = $db->getQuery(true)
            ->select([
                'm.*',
                'DATE_FORMAT(m.time_present, "%H:%i") AS time_present',
                $db->quoteName('pt1.project_id'),
                $db->quoteName('r.roundcode'),
                "CONCAT_WS(':', pl.id, pl.alias) AS playground_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt1')
                . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt2')
                . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_playground', 'pl')
                . ' ON ' . $db->quoteName('pl.id') . ' = ' . $db->quoteName('m.playground_id')
            )
            ->where('DATE_ADD(' . $db->quoteName('m.match_date') . ', INTERVAL ' . $expiryTime . ' MINUTE) >= NOW()')
            ->where($db->quoteName('m.cancel') . ' = 0');

        if ($matchId > 0) {
            $query->where($db->quoteName('m.id') . ' = ' . $matchId);
        } else {
            $query->where(
                '(' . $db->quoteName('m.team1_result') . ' IS NULL'
                . ' OR ' . $db->quoteName('m.team2_result') . ' IS NULL)'
            );

            if ($projectTeamId > 0) {
                $query->where(
                    '(' . $db->quoteName('m.projectteam1_id') . ' = ' . $projectTeamId
                    . ' OR ' . $db->quoteName('m.projectteam2_id') . ' = ' . $projectTeamId . ')'
                );
            } else {
                $query->where(
                    '(' . $db->quoteName('m.projectteam1_id') . ' > 0'
                    . ' OR ' . $db->quoteName('m.projectteam2_id') . ' > 0)'
                );
            }
        }

        if ($projectId > 0) {
            $query->where($db->quoteName('pt1.project_id') . ' = ' . $projectId);
        }

        $query->order($db->quoteName('m.match_date') . ' ASC');
        $db->setQuery($query, 0, 1);
        $this->match = $db->loadObject() ?: null;

        if ($this->match !== null) {
            $this->projectId = (int) $this->match->project_id;
            $this->matchId = (int) $this->match->id;
        }

        return $this->match;
    }

    public function getMatch(): ?object
    {
        if ($this->match !== null) {
            return $this->match;
        }

        if ($this->matchId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'm.*',
                'DATE_FORMAT(m.time_present, "%H:%i") AS time_present',
                $db->quoteName('pt1.project_id'),
                $db->quoteName('r.roundcode'),
                "CONCAT_WS(':', pl.id, pl.alias) AS playground_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt1')
                . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_playground', 'pl')
                . ' ON ' . $db->quoteName('pl.id') . ' = ' . $db->quoteName('m.playground_id')
            )
            ->where($db->quoteName('m.id') . ' = ' . $this->matchId);

        try {
            $db->setQuery($query, 0, 1);
            $this->match = $db->loadObject() ?: null;
        } catch (Throwable $e) {
            $this->siteApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . $e->getMessage()), 'error');
            return null;
        }

        if ($this->match !== null) {
            $this->projectId = (int) $this->match->project_id;
        }

        return $this->match;
    }

    public function getMatchTeams(): ?array
    {
        if ($this->teams !== null) {
            return $this->teams;
        }

        $match = $this->getMatch();
        if ($match === null) {
            return null;
        }

        $team1 = $this->loadProjectTeam((int) $match->projectteam1_id);
        $team2 = $this->loadProjectTeam((int) $match->projectteam2_id);

        if ($team1 === null || $team2 === null) {
            return null;
        }

        $this->homeDivisionId = (int) ($team1->division_id ?? 0);
        $this->awayDivisionId = (int) ($team2->division_id ?? 0);
        $this->teams = [$team1, $team2];

        return $this->teams;
    }

    public function getReferees(): array
    {
        $match = $this->getMatch();
        if ($match === null) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.firstname'),
                $db->quoteName('p.nickname'),
                $db->quoteName('p.lastname'),
                $db->quoteName('p.country'),
                $db->quoteName('p.id', 'person_id'),
                $db->quoteName('pos.name', 'position_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_referee', 'mr'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_referee', 'pref')
                . ' ON ' . $db->quoteName('mr.project_referee_id') . ' = ' . $db->quoteName('pref.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_person_id', 'spi')
                . ' ON ' . $db->quoteName('pref.person_id') . ' = ' . $db->quoteName('spi.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_person', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('spi.person_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('mr.project_position_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_position', 'pos')
                . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id')
            )
            ->where($db->quoteName('mr.match_id') . ' = ' . (int) $match->id)
            ->where($db->quoteName('p.published') . ' = 1');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getHomeHighestHomeWin(): ?object
    {
        return $this->highestForTeam(0, 'HOME', 'WIN');
    }

    public function getAwayHighestHomeWin(): ?object
    {
        return $this->highestForTeam(1, 'AWAY', 'WIN');
    }

    public function getHomeHighestHomeDef(): ?object
    {
        return $this->highestForTeam(0, 'HOME', 'LOST');
    }

    public function getAwayHighestHomeDef(): ?object
    {
        return $this->highestForTeam(1, 'AWAY', 'LOST');
    }

    public function getHomeHighestAwayWin(): ?object
    {
        return $this->highestForTeam(0, 'AWAY', 'WIN');
    }

    public function getAwayHighestAwayWin(): ?object
    {
        return $this->highestForTeam(1, 'HOME', 'WIN');
    }

    public function getHomeHighestAwayDef(): ?object
    {
        return $this->highestForTeam(0, 'AWAY', 'LOST');
    }

    public function getAwayHighestAwayDef(): ?object
    {
        return $this->highestForTeam(1, 'HOME', 'LOST');
    }

    public function _getHighestMatches(int $teamId, string $whichTeam, string $gameType): ?object
    {
        if ($teamId <= 0 || $this->projectId <= 0) {
            return null;
        }

        $whichTeam = strtoupper($whichTeam) === 'AWAY' ? 'AWAY' : 'HOME';
        $gameType = strtoupper($gameType) === 'LOST' ? 'LOST' : 'WIN';
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id', 'mid'),
                $db->quoteName('m.team1_result', 'homegoals'),
                $db->quoteName('m.team2_result', 'awaygoals'),
                $db->quoteName('t1.name', 'hometeam'),
                $db->quoteName('t2.name', 'awayteam'),
                $db->quoteName('pt1.project_id', 'pid'),
                "CONCAT_WS(':', m.id, CONCAT_WS('_', t1.alias, t2.alias)) AS match_slug",
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id'))
            ->where($db->quoteName('pt1.project_id') . ' = ' . $this->projectId)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('m.alt_decision') . ' = 0');

        if ($whichTeam === 'HOME') {
            $query->where($db->quoteName('t1.id') . ' = ' . $teamId);
            if ($gameType === 'WIN') {
                $query->where('(m.team1_result - m.team2_result > 0)')->order('(m.team1_result - m.team2_result) DESC');
            } else {
                $query->where('(m.team1_result - m.team2_result < 0)')->order('(m.team1_result - m.team2_result) ASC');
            }
        } else {
            $query->where($db->quoteName('t2.id') . ' = ' . $teamId);
            if ($gameType === 'WIN') {
                $query->where('(m.team2_result - m.team1_result > 0)')->order('(m.team2_result - m.team1_result) DESC');
            } else {
                $query->where('(m.team2_result - m.team1_result < 0)')->order('(m.team2_result - m.team1_result) ASC');
            }
        }

        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }

    public function getGames(): array
    {
        $teams = $this->getMatchTeams();
        if ($teams === null) {
            return [];
        }

        $team1Id = (int) ($teams[0]->team_id ?? 0);
        $team2Id = (int) ($teams[1]->team_id ?? 0);
        if ($team1Id <= 0 || $team2Id <= 0) {
            return [];
        }

        $result1 = $this->loadHeadToHeadGames($team1Id, $team2Id);
        $result2 = $this->loadHeadToHeadGames($team2Id, $team1Id);
        $usedProjectIds = [];

        foreach (array_merge($result1, $result2) as $row) {
            $projectId = (int) ($row->project_id ?? 0);
            if ($projectId > 0) {
                $usedProjectIds[$projectId] = $projectId;
            }
        }

        $result3 = $this->loadSharedProjectsWithoutMatches($team2Id, $team1Id, array_values($usedProjectIds));
        $result = array_merge($result1, $result2, $result3);

        usort($result, static function (object $a, object $b): int {
            $season = strnatcasecmp((string) ($b->seasonname ?? ''), (string) ($a->seasonname ?? ''));
            if ($season !== 0) {
                return $season;
            }

            $project = strnatcasecmp((string) ($a->project_name ?? ''), (string) ($b->project_name ?? ''));
            if ($project !== 0) {
                return $project;
            }

            return ((int) ($a->roundcode ?? 0)) <=> ((int) ($b->roundcode ?? 0));
        });

        return $result;
    }

    public function getTeamsFromMatches(&$games, $config = []): array
    {
        if (empty($games) || !is_iterable($games)) {
            return [];
        }

        $ids = [];
        foreach ($games as $match) {
            foreach ([(int) ($match->projectteam1_id ?? 0), (int) ($match->projectteam2_id ?? 0)] as $id) {
                if ($id > 0) {
                    $ids[$id] = $id;
                }
            }
        }

        if ($ids === []) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t.id'),
                $db->quoteName('t.name'),
                $db->quoteName('pt.id', 'ptid'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->where($db->quoteName('pt.id') . ' IN (' . implode(',', array_values($ids)) . ')');

        switch ((string) ($config['show_picture'] ?? '')) {
            case 'team_picture':
                $query->select($db->quoteName('t.picture', 'picture'));
                break;
            case 'projectteam_picture':
                $query->select($db->quoteName('st.picture', 'picture'));
                break;
            case 'logo_small':
                $query->select($db->quoteName('c.logo_small', 'picture'));
                break;
            case 'logo_middle':
                $query->select($db->quoteName('c.logo_middle', 'picture'));
                break;
            case 'logo_big':
                $query->select($db->quoteName('c.logo_big', 'picture'));
                break;
        }

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->siteApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . $e->getMessage()), 'error');
            return [];
        }

        $teams = [];
        foreach ($rows as $row) {
            $teams[(int) $row->ptid] = $row;
        }

        return $teams;
    }

    public function getChances(): ?array
    {
        $home = $this->getHomeRanked();
        $away = $this->getAwayRanked();
        $matches1 = (int) ($home->cnt_matches ?? 0);
        $matches2 = (int) ($away->cnt_matches ?? 0);

        if ($matches1 <= 0 || $matches2 <= 0) {
            return null;
        }

        $ax = (100 * (float) ($home->cnt_won ?? 0) / $matches1)
            + (100 * (float) ($away->cnt_lost ?? 0) / $matches2);
        $bx = (100 * (float) ($away->cnt_won ?? 0) / $matches2)
            + (100 * (float) ($home->cnt_lost ?? 0) / $matches1);
        $cx = ((float) ($home->sum_team1_result ?? 0) / $matches1)
            + ((float) ($away->sum_team2_result ?? 0) / $matches2);
        $dx = ((float) ($away->sum_team1_result ?? 0) / $matches2)
            + ((float) ($home->sum_team2_result ?? 0) / $matches1);
        $ex = $ax + $bx;
        $fx = $cx + $dx;

        if ($ex <= 0 || $fx <= 0) {
            return null;
        }

        $ax = round(10000 * $ax / $ex);
        $bx = round(10000 * $bx / $ex);
        $cx = round(10000 * $cx / $fx);
        $dx = round(10000 * $dx / $fx);

        return [
            number_format((($ax + $cx) / 200), 2, ',', '.'),
            number_format((($bx + $dx) / 200), 2, ',', '.'),
        ];
    }

    public function getHomeRanked(): object
    {
        return $this->getRankedTeam(0);
    }

    public function getAwayRanked(): object
    {
        return $this->getRankedTeam(1);
    }

    public function _getRanking(): array
    {
        if ($this->ranking !== null) {
            return $this->ranking;
        }

        $project = $this->getProject();
        if ($project === null) {
            return [];
        }

        LegacyBootstrap::bootForView('nextmatch');
        if (!class_exists('JSMRanking')) {
            return [];
        }

        $divisionId = $this->homeDivisionId === $this->awayDivisionId ? $this->homeDivisionId : 0;
        $ranking = \JSMRanking::getInstance($project, $this->databaseSelector);
        $ranking->setProjectId((int) $project->id, $this->databaseSelector);
        $this->ranking = $ranking->getRanking(
            0,
            $this->getCurrentRound(),
            $divisionId,
            $this->databaseSelector
        ) ?: [];

        return $this->ranking;
    }

    public function getPreviousX($config = []): array
    {
        $match = $this->getMatch();
        if ($match === null) {
            return [];
        }

        return [
            (int) $match->projectteam1_id => $this->_getTeamPreviousX((int) $match->roundcode, (int) $match->projectteam1_id, $config),
            (int) $match->projectteam2_id => $this->_getTeamPreviousX((int) $match->roundcode, (int) $match->projectteam2_id, $config),
        ];
    }

    public function _getTeamPreviousX(int $currentRoundCode, int $projectTeamId, $config = []): array
    {
        $config = is_array($config) && $config !== [] ? $config : $this->getTemplateConfig('nextmatch');
        $limit = max(0, (int) ($config['nb_previous'] ?? 0));
        if ($projectTeamId <= 0 || $limit === 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.match_date'),
                $db->quoteName('m.team1_result'),
                $db->quoteName('m.team2_result'),
                $db->quoteName('m.show_report'),
                $db->quoteName('m.projectteam1_id'),
                $db->quoteName('m.projectteam2_id'),
                $db->quoteName('r.project_id'),
                $db->quoteName('r.id', 'roundid'),
                $db->quoteName('r.roundcode'),
                "CONCAT_WS(':', m.id, CONCAT_WS('_', t1.alias, t2.alias)) AS match_slug",
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                "CONCAT_WS(':', r.id, r.alias) AS round_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c1') . ' ON ' . $db->quoteName('c1.id') . ' = ' . $db->quoteName('t1.club_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c2') . ' ON ' . $db->quoteName('c2.id') . ' = ' . $db->quoteName('t2.club_id'))
            ->where($db->quoteName('r.roundcode') . ' < ' . $currentRoundCode)
            ->where(
                '(' . $db->quoteName('m.projectteam1_id') . ' = ' . $projectTeamId
                . ' OR ' . $db->quoteName('m.projectteam2_id') . ' = ' . $projectTeamId . ')'
            )
            ->where($db->quoteName('m.published') . ' = 1')
            ->order($db->quoteName('r.roundcode') . ' DESC');

        switch ((string) ($config['show_picture'] ?? '')) {
            case 'team_picture':
                $query->select([$db->quoteName('t1.picture', 'home_picture'), $db->quoteName('t2.picture', 'away_picture')]);
                break;
            case 'projectteam_picture':
                $query->select([$db->quoteName('st1.picture', 'home_picture'), $db->quoteName('st2.picture', 'away_picture')]);
                break;
            case 'logo_small':
                $query->select([$db->quoteName('c1.logo_small', 'home_picture'), $db->quoteName('c2.logo_small', 'away_picture')]);
                break;
            case 'logo_middle':
                $query->select([$db->quoteName('c1.logo_middle', 'home_picture'), $db->quoteName('c2.logo_middle', 'away_picture')]);
                break;
            case 'logo_big':
                $query->select([$db->quoteName('c1.logo_big', 'home_picture'), $db->quoteName('c2.logo_big', 'away_picture')]);
                break;
        }

        try {
            $db->setQuery($query, 0, $limit);
            $rows = $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->siteApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . $e->getMessage()), 'error');
            return [];
        }

        return array_reverse($rows);
    }

    private function highestForTeam(int $index, string $whichTeam, string $gameType): ?object
    {
        $teams = $this->getMatchTeams();
        if ($teams === null || !isset($teams[$index])) {
            return null;
        }

        return $this->_getHighestMatches((int) ($teams[$index]->team_id ?? 0), $whichTeam, $gameType);
    }

    private function getRankedTeam(int $index): object
    {
        $match = $this->getMatch();
        $teams = $this->getMatchTeams();
        $rankings = $teams === null ? [] : $this->_getRanking();
        $projectTeamId = $match === null
            ? 0
            : (int) ($index === 0 ? $match->projectteam1_id : $match->projectteam2_id);

        if ($projectTeamId > 0 && isset($rankings[$projectTeamId])) {
            return $rankings[$projectTeamId];
        }

        LegacyBootstrap::bootForView('nextmatch');
        if (class_exists('JSMRankingTeamClass')) {
            return new \JSMRankingTeamClass(0);
        }

        return (object) [
            'cnt_matches' => 0,
            'cnt_won' => 0,
            'cnt_lost' => 0,
            'cnt_draw' => 0,
            'sum_team1_result' => 0,
            'sum_team2_result' => 0,
        ];
    }

    private function loadProjectTeam(int $projectTeamId): ?object
    {
        if ($projectTeamId <= 0) {
            return null;
        }

        foreach ($this->getProjectTeams(0) as $team) {
            if ((int) ($team->projectteamid ?? 0) === $projectTeamId) {
                return $team;
            }
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt.id', 'projectteamid'),
                $db->quoteName('pt.division_id'),
                $db->quoteName('pt.picture', 'projectteam_picture'),
                $db->quoteName('pt.project_id'),
                $db->quoteName('st.team_id'),
                $db->quoteName('t.id'),
                $db->quoteName('t.name'),
                $db->quoteName('t.short_name'),
                $db->quoteName('t.middle_name'),
                $db->quoteName('t.club_id'),
                $db->quoteName('t.picture', 'team_picture'),
                $db->quoteName('c.name', 'club_name'),
                $db->quoteName('c.logo_small'),
                $db->quoteName('c.logo_middle'),
                $db->quoteName('c.logo_big'),
                "CONCAT_WS(':', t.id, t.alias) AS team_slug",
                "CONCAT_WS(':', pt.id, t.alias) AS projectteam_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->where($db->quoteName('pt.id') . ' = ' . $projectTeamId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    private function loadHeadToHeadGames(int $homeTeamId, int $awayTeamId): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.match_date'),
                $db->quoteName('m.team1_result'),
                $db->quoteName('m.team2_result'),
                $db->quoteName('m.show_report'),
                $db->quoteName('m.projectteam1_id'),
                $db->quoteName('m.projectteam2_id'),
                'DATE_FORMAT(m.time_present, "%H:%i") AS time_present',
                $db->quoteName('pt1.project_id'),
                $db->quoteName('s.name', 'seasonname'),
                $db->quoteName('s.id', 'season_id'),
                $db->quoteName('l.name', 'leaguename'),
                $db->quoteName('l.id', 'league_id'),
                $db->quoteName('p.name', 'project_name'),
                $db->quoteName('p.id', 'prid'),
                $db->quoteName('r.id', 'roundid'),
                $db->quoteName('r.roundcode'),
                $db->quoteName('r.name', 'mname'),
                $db->quoteName('t1.id', 'team1_id'),
                $db->quoteName('t2.id', 'team2_id'),
                "CONCAT_WS(':', m.id, CONCAT_WS('_', t1.alias, t2.alias)) AS match_slug",
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                "CONCAT_WS(':', r.id, r.alias) AS round_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt1.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
            ->where($db->quoteName('st1.team_id') . ' = ' . $homeTeamId)
            ->where($db->quoteName('st2.team_id') . ' = ' . $awayTeamId)
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('m.published') . ' = 1')
            ->order([$db->quoteName('s.name') . ' DESC', $db->quoteName('m.match_date') . ' ASC']);

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->siteApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . $e->getMessage()), 'notice');
            return [];
        }
    }

    private function loadSharedProjectsWithoutMatches(int $homeTeamId, int $awayTeamId, array $excludedProjectIds): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt1.project_id'),
                $db->quoteName('pt1.id', 'projectteam1_id'),
                $db->quoteName('pt2.id', 'projectteam2_id'),
                $db->quoteName('s.name', 'seasonname'),
                $db->quoteName('s.id', 'season_id'),
                $db->quoteName('l.name', 'leaguename'),
                $db->quoteName('l.id', 'league_id'),
                $db->quoteName('p.name', 'project_name'),
                $db->quoteName('p.id', 'prid'),
                $db->quoteName('t1.id', 'team1_id'),
                $db->quoteName('t2.id', 'team2_id'),
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                $db->quote(Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY1') . ':' . Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY2')) . ' AS round_slug',
                $db->quote(Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY1') . ':' . Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY2')) . ' AS roundid',
                $db->quote(Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY1') . ':' . Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY2')) . ' AS roundcode',
                $db->quote('0000-00-00 00:00:00') . ' AS match_date',
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt1'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.project_id') . ' = ' . $db->quoteName('pt1.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt1.project_id') . ' AND ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt2.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id'))
            ->where($db->quoteName('st1.team_id') . ' = ' . $homeTeamId)
            ->where($db->quoteName('st2.team_id') . ' = ' . $awayTeamId)
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('p.project_type') . ' = ' . $db->quote('SIMPLE_LEAGUE'));

        if ($excludedProjectIds !== []) {
            $ids = implode(',', array_map('intval', $excludedProjectIds));
            $query->where($db->quoteName('p.id') . ' NOT IN (' . $ids . ')');
        }

        $query->order($db->quoteName('s.name') . ' DESC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->siteApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . $e->getMessage()), 'notice');
            return [];
        }
    }
}
