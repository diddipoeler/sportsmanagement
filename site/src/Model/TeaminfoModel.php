<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\PersonAgeHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;

final class TeaminfoModel extends SportsManagementProjectModel
{
    public static int $projectid = 0;
    public static int $projectteamid = 0;
    public static int $teamid = 0;
    public static $team = null;
    public static $club = null;
    public static int $cfg_which_database = 0;

    private static $database = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$projectid = $this->getProjectId();
        self::$projectteamid = max(0, $input->getInt('ptid', 0));
        self::$teamid = max(0, $input->getInt('tid', 0));
        self::$cfg_which_database = max(0, $input->getInt('cfg_which_database', 0));
        self::$database = $this->getDatabase();
    }

    public static function getTeam($inserthits = 0, $teamid = 0)
    {
        $input = self::frontendApplication()->getInput();
        $requestProjectId = max(0, $input->getInt('p', self::$projectid));
        if ($requestProjectId > 0) {
            self::$projectid = $requestProjectId;
        }

        if (self::$projectid <= 0) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_NO_RANKING_PROJECTINFO'), Log::ERROR, 'jsmerror');
        }

        self::$teamid = (int) $teamid > 0
            ? (int) $teamid
            : max(0, $input->getInt('cid', self::$teamid));

        self::updateHits(self::$teamid, $inserthits);

        if (self::$team === null && self::$teamid > 0) {
            $db = self::database();
            $query = $db->getQuery(true)
                ->select('t.*')
                ->from($db->quoteName('#__sportsmanagement_team', 't'))
                ->where($db->quoteName('t.id') . ' = ' . self::$teamid);
            $db->setQuery($query, 0, 1);
            self::$team = $db->loadObject() ?: null;
        }

        return self::$team;
    }

    public static function getTrainigData($projectid): array
    {
        if (self::$teamid <= 0) {
            return [];
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_team_trainingdata'))
            ->where($db->quoteName('team_id') . ' = ' . self::$teamid)
            ->order($db->quoteName('dayofweek') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public static function getTrainingData($projectid): array
    {
        return self::getTrainigData($projectid);
    }

    public static function getClub()
    {
        if (self::$club !== null) {
            return self::$club;
        }

        $team = self::getTeamByProject();
        $clubId = (int) ($team->club_id ?? 0);
        if ($clubId <= 0) {
            return null;
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select('c.*')
            ->select("CONCAT_WS(':', c.id, c.alias) AS slug")
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->where($db->quoteName('c.id') . ' = ' . $clubId);
        $db->setQuery($query, 0, 1);
        self::$club = $db->loadObject() ?: null;

        return self::$club;
    }

    public static function getTeamByProject($inserthits = 0)
    {
        self::updateHits(self::$teamid, $inserthits);

        if (self::$team !== null) {
            return self::$team;
        }
        if (self::$projectid <= 0 || (self::$projectteamid <= 0 && self::$teamid <= 0)) {
            return null;
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select('t.*, t.name AS tname, t.website AS team_website, t.email AS team_email, pt.*, pt.notes AS projectteamnotes')
            ->select('t.extended AS teamextended, t.picture AS team_picture, pt.picture AS projectteam_picture, pt.cr_picture AS cr_projectteam_picture, c.*')
            ->select("CONCAT_WS(':', t.id, t.alias) AS slug")
            ->select('pt.id AS projectteamid, t.id AS teamid, t.id AS id, t.notes AS teamnotes')
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('pt.is_in_score') . ' = 1');

        if (self::$projectteamid > 0) {
            $query->where($db->quoteName('pt.id') . ' = ' . self::$projectteamid);
        } else {
            $query->where($db->quoteName('t.id') . ' = ' . self::$teamid);
        }

        try {
            $db->setQuery($query, 0, 1);
            self::$team = $db->loadObject() ?: null;
            if (self::$team) {
                self::$projectteamid = (int) (self::$team->projectteamid ?? self::$projectteamid);
                self::$teamid = (int) (self::$team->teamid ?? self::$teamid);
            }
        } catch (\Throwable $e) {
            self::frontendApplication()->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                'error'
            );
            self::$team = null;
        }

        return self::$team;
    }

    public static function updateHits($teamid = 0, $inserthits = 0): void
    {
        $teamId = max(0, (int) $teamid);
        if (!$inserthits || $teamId <= 0) {
            return;
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__sportsmanagement_team'))
            ->set($db->quoteName('hits') . ' = ' . $db->quoteName('hits') . ' + 1')
            ->where($db->quoteName('id') . ' = ' . $teamId);
        $db->setQuery($query);
        $db->execute();
    }

    public static function getSeasons($config, $history = 0): array
    {
        if (self::$teamid <= 0 && self::$projectteamid <= 0) {
            return [];
        }

        $db = self::database();
        $forceRankingCache = (bool) ComponentHelper::getParams('com_sportsmanagement')->get('force_ranking_cache', 0);
        $seasonOrdering = !empty($config['ordering_teams_seasons']) ? 'DESC' : 'ASC';

        $query = $db->getQuery(true)
            ->select('pt.id AS ptid, pt.team_id AS season_team_id, pt.picture, pt.info, pt.project_id AS projectid')
            ->select('p.name AS projectname, p.season_id, p.current_round, p.current_round_auto, p.auto_time, pt.division_id, p.project_type')
            ->select('s.name AS season')
            ->select('t.id AS team_id')
            ->select('st.picture AS season_picture')
            ->select('l.name AS league, t.extended AS teamextended, l.country AS leaguecountry')
            ->select("CONCAT_WS(':', p.id, p.alias) AS project_slug")
            ->select("CONCAT_WS(':', t.id, t.alias) AS team_slug")
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->where($db->quoteName('pt.is_in_score') . ' = 1')
            ->order($db->quoteName('s.name') . ' ' . $seasonOrdering . ', ' . $db->quoteName('p.name') . ' ASC');

        if ($forceRankingCache) {
            $query->select([
                $db->quoteName('pt.cache_points_finally', 'points_finally'),
                $db->quoteName('pt.cache_neg_points_finally', 'neg_points_finally'),
                $db->quoteName('pt.finaltablerank'),
                $db->quoteName('pt.champion'),
                $db->quoteName('pt.cache_matches_finally', 'matches_finally'),
                $db->quoteName('pt.cache_won_finally', 'won_finally'),
                $db->quoteName('pt.cache_draws_finally', 'draws_finally'),
                $db->quoteName('pt.cache_lost_finally', 'lost_finally'),
                $db->quoteName('pt.cache_homegoals_finally', 'homegoals_finally'),
                $db->quoteName('pt.cache_guestgoals_finally', 'guestgoals_finally'),
            ]);
        } else {
            $query->select('pt.points_finally, pt.neg_points_finally, pt.finaltablerank, pt.champion, pt.matches_finally, pt.won_finally, pt.draws_finally, pt.lost_finally, pt.homegoals_finally, pt.guestgoals_finally');
        }

        if ($history) {
            $query->where($db->quoteName('t.id') . ' = ' . self::$teamid);
        } elseif (self::$projectteamid > 0) {
            $query->where($db->quoteName('pt.id') . ' = ' . self::$projectteamid);
        } else {
            $query->where($db->quoteName('t.id') . ' = ' . self::$teamid);
        }

        $db->setQuery($query);
        $seasons = $db->loadObjectList() ?: [];

        foreach ($seasons as $season) {
            $season->division_slug = null;
            $season->division_name = null;
            $season->division_short_name = null;
            $season->round_slug = null;

            if ((int) $season->division_id > 0) {
                $divisionQuery = $db->getQuery(true)
                    ->select("CONCAT_WS(':', d.id, d.alias) AS division_slug")
                    ->select($db->quoteName('d.name', 'division_name'))
                    ->select($db->quoteName('d.shortname', 'division_short_name'))
                    ->from($db->quoteName('#__sportsmanagement_division', 'd'))
                    ->where($db->quoteName('d.id') . ' = ' . (int) $season->division_id)
                    ->where($db->quoteName('d.project_id') . ' = ' . (int) $season->projectid);
                $db->setQuery($divisionQuery, 0, 1);
                $division = $db->loadObject();
                if ($division) {
                    $season->division_slug = $division->division_slug;
                    $season->division_name = $division->division_name;
                    $season->division_short_name = $division->division_short_name;
                }
            }

            if ((int) $season->current_round > 0) {
                $roundQuery = $db->getQuery(true)
                    ->select("CONCAT_WS(':', r.id, r.alias) AS round_slug")
                    ->from($db->quoteName('#__sportsmanagement_round', 'r'))
                    ->where($db->quoteName('r.id') . ' = ' . (int) $season->current_round)
                    ->where($db->quoteName('r.project_id') . ' = ' . (int) $season->projectid);
                $db->setQuery($roundQuery, 0, 1);
                $season->round_slug = $db->loadResult() ?: null;
            }

            if ($forceRankingCache) {
                $season->rank = (int) ($season->finaltablerank ?? 0);
                $season->games = (int) ($season->matches_finally ?? 0);
                $season->goals = (int) ($season->homegoals_finally ?? 0) . ':' . (int) ($season->guestgoals_finally ?? 0);
                $season->series = (int) ($season->won_finally ?? 0) . '/' . (int) ($season->draws_finally ?? 0) . '/' . (int) ($season->lost_finally ?? 0);
                $season->points = $season->points_finally ?? 0;
            } else {
                $ranking = self::getTeamRanking((int) $season->projectid, (int) $season->division_id);
                $season->rank = (int) ($ranking['rank'] ?? 0);
                $season->games = (int) ($ranking['games'] ?? 0);
                $season->points = $ranking['points'] ?? 0;
                $season->series = $ranking['series'] ?? 0;
                $season->goals = $ranking['goals'] ?? 0;
            }

            $season->leaguename = self::getLeague((int) $season->projectid);
            $season->playercnt = self::getPlayerCount((int) $season->projectid, (int) $season->ptid, (int) $season->season_id, 1);
            $season->playermeanage = self::getPlayerMeanAge((int) $season->projectid, (int) $season->ptid, (int) $season->season_id, 1);
            $season->market_value = self::getPlayerMarketValue((int) $season->projectid, (int) $season->ptid, (int) $season->season_id, 1);
        }

        return $seasons;
    }

    public static function getTeamRanking($projectid, $division_id): array
    {
        $projectId = max(0, (int) $projectid);
        if ($projectId <= 0 || self::$teamid <= 0) {
            return [];
        }

        $project = self::loadProjectInfo($projectId);
        if (!$project) {
            return [];
        }

        if (!class_exists('JSMRanking')) {
            if (is_file(JPATH_SITE . '/components/com_sportsmanagement/helpers/ranking.php')) {
                require_once JPATH_SITE . '/components/com_sportsmanagement/helpers/ranking.php';
            }
        }
        if (!class_exists('JSMRanking')) {
            return [];
        }

        $ranking = \JSMRanking::getInstance($project, self::$cfg_which_database);
        if (!$ranking) {
            return [];
        }
        $ranking->setProjectId($projectId, self::$cfg_which_database);
        $currentRound = self::resolveCurrentRoundId($project);
        if ($currentRound <= 0) {
            return [];
        }

        $rows = $ranking->getRanking(0, $currentRound, max(0, (int) $division_id), self::$cfg_which_database);
        foreach ((array) $rows as $value) {
            if (!is_object($value) || !method_exists($value, 'getTeamId') || (int) $value->getTeamId() !== self::$teamid) {
                continue;
            }
            return [
                'rank' => (int) ($value->rank ?? 0),
                'games' => (int) ($value->cnt_matches ?? 0),
                'points' => method_exists($value, 'getPoints') ? $value->getPoints() : 0,
                'series' => (int) ($value->cnt_won ?? 0) . '/' . (int) ($value->cnt_draw ?? 0) . '/' . (int) ($value->cnt_lost ?? 0),
                'goals' => (int) ($value->sum_team1_result ?? 0) . ':' . (int) ($value->sum_team2_result ?? 0),
            ];
        }

        return [];
    }

    public static function getLeague($projectid): string
    {
        $projectId = max(0, (int) $projectid);
        if ($projectId <= 0) {
            return '';
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select($db->quoteName('l.name'))
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->where($db->quoteName('p.id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);

        return (string) ($db->loadResult() ?: '');
    }

    public static function getPlayerCount($projectid, $projectteamid, $season_id, $persontype = 0): int
    {
        $db = self::database();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_person', 'ps'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('tp.person_id') . ' = ' . $db->quoteName('ps.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id') . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . max(0, (int) $projectid))
            ->where($db->quoteName('pt.id') . ' = ' . max(0, (int) $projectteamid))
            ->where($db->quoteName('tp.season_id') . ' = ' . max(0, (int) $season_id))
            ->where($db->quoteName('st.season_id') . ' = ' . max(0, (int) $season_id))
            ->where($db->quoteName('ps.published') . ' = 1');

        if ((int) $persontype > 0) {
            $query->where($db->quoteName('tp.persontype') . ' = ' . (int) $persontype);
        }
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    public static function getPlayerMeanAge($projectid, $projectteamid, $season_id, $persontype = 0): float
    {
        $projectId = max(0, (int) $projectid);
        $seasonId = max(0, (int) $season_id);
        $db = self::database();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('ps.birthday'), $db->quoteName('ps.deathday')])
            ->from($db->quoteName('#__sportsmanagement_person', 'ps'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('tp.person_id') . ' = ' . $db->quoteName('ps.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id') . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('pt.id') . ' = ' . max(0, (int) $projectteamid))
            ->where($db->quoteName('tp.season_id') . ' = ' . $seasonId)
            ->where($db->quoteName('st.season_id') . ' = ' . $seasonId)
            ->where($db->quoteName('tp.published') . ' = 1')
            ->where($db->quoteName('ps.published') . ' = 1');

        if ((int) $persontype > 0) {
            $query->where($db->quoteName('tp.persontype') . ' = ' . (int) $persontype);
        }
        $db->setQuery($query);
        $players = $db->loadObjectList() ?: [];

        $dateQuery = $db->getQuery(true)
            ->select('MAX(' . $db->quoteName('round_date_first') . ') AS firstday')
            ->select('MAX(' . $db->quoteName('round_date_last') . ') AS lastday')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($dateQuery, 0, 1);
        $roundDate = $db->loadObject();

        $firstDay = (string) ($roundDate->firstday ?? '');
        $lastDay = (string) ($roundDate->lastday ?? '');
        if ($lastDay === '' || $lastDay === '0000-00-00' || ($firstDay !== '' && $firstDay > $lastDay)) {
            $lastDay = $firstDay;
        }
        if ($lastDay === '') {
            $lastDay = '0000-00-00';
        }

        $age = 0.0;
        $count = 0;
        foreach ($players as $player) {
            $birthday = (string) ($player->birthday ?? '');
            if ($birthday === '' || $birthday === '0000-00-00') {
                continue;
            }
            $referenceDay = $lastDay;
            $deathDay = (string) ($player->deathday ?? '');
            if ($deathDay !== '' && $deathDay !== '0000-00-00' && ($referenceDay === '0000-00-00' || $deathDay < $referenceDay)) {
                $referenceDay = $deathDay;
            }
            $age += (float) PersonAgeHelper::calculate($birthday, $referenceDay);
            $count++;
        }

        return $count > 0 ? round($age / $count, 2) : 0.0;
    }

    public static function getPlayerMarketValue($projectid, $projectteamid, $season_id, $persontype = 0)
    {
        $db = self::database();
        $query = $db->getQuery(true)
            ->select('SUM(' . $db->quoteName('stp.market_value') . ')')
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'stp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('stp.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . max(0, (int) $projectid))
            ->where($db->quoteName('pt.id') . ' = ' . max(0, (int) $projectteamid))
            ->where($db->quoteName('st.season_id') . ' = ' . max(0, (int) $season_id))
            ->where($db->quoteName('stp.season_id') . ' = ' . max(0, (int) $season_id))
            ->where($db->quoteName('stp.published') . ' = 1');

        if ((int) $persontype > 0) {
            $query->where($db->quoteName('stp.persontype') . ' = ' . (int) $persontype);
        }
        $db->setQuery($query);

        return $db->loadResult() ?: 0;
    }

    public static function getLeagueRankOverviewDetail($seasonsranking): array
    {
        $overview = [];
        foreach ((array) $seasonsranking as $season) {
            $league = (string) ($season->league ?? '');
            if ($league === '') {
                continue;
            }
            if (!isset($overview[$league])) {
                $overview[$league] = (object) [
                    'match' => 0,
                    'won' => 0,
                    'draw' => 0,
                    'loss' => 0,
                    'goalsfor' => 0,
                    'goalsagain' => 0,
                ];
            }

            $overview[$league]->match += (int) ($season->games ?? 0);
            $series = explode('/', (string) ($season->series ?? '0/0/0'));
            $overview[$league]->won += (int) ($series[0] ?? 0);
            $overview[$league]->draw += (int) ($series[1] ?? 0);
            $overview[$league]->loss += (int) ($series[2] ?? 0);
            $goals = explode(':', (string) ($season->goals ?? '0:0'));
            $overview[$league]->goalsfor += (int) ($goals[0] ?? 0);
            $overview[$league]->goalsagain += (int) ($goals[1] ?? 0);
        }

        return $overview;
    }

    public static function getLeagueRankOverview($seasonsranking): array
    {
        $overview = [];
        foreach ((array) $seasonsranking as $season) {
            $league = (string) ($season->league ?? '');
            $rank = (int) ($season->rank ?? 0);
            if ($league === '') {
                continue;
            }
            $overview[$league][$rank] = ($overview[$league][$rank] ?? 0) + 1;
        }

        ksort($overview);
        foreach ($overview as &$ranks) {
            ksort($ranks);
        }
        unset($ranks);

        return $overview;
    }

    public function getMergeClubs($merge_clubs): array
    {
        $ids = [];
        foreach (preg_split('/[^0-9]+/', (string) $merge_clubs, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        if (!$ids) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('c.*')
            ->select("CONCAT_WS(':', c.id, c.alias) AS slug")
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->where($db->quoteName('c.id') . ' IN (' . implode(',', array_values($ids)) . ')');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function hasEditPermission($task = null): bool
    {
        $identity = $this->siteApplication()->getIdentity();
        $action = trim((string) $task);
        $allowed = $identity->authorise('core.edit', 'com_sportsmanagement');
        if (!$allowed && $action !== '') {
            $allowed = $identity->authorise($action, 'com_sportsmanagement');
        }

        if ((int) $identity->id > 0 && !$allowed) {
            $team = self::getTeamByProject();
            $allowed = $team && (int) ($team->admin ?? 0) === (int) $identity->id;
        }

        return $allowed;
    }

    private static function database(): DatabaseInterface
    {
        if (self::$database instanceof DatabaseInterface) {
            return self::$database;
        }

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        self::$database = SportsManagementDatabaseResolver::resolve(
            $joomlaDatabase,
            self::$cfg_which_database
        );

        return self::$database;
    }

    private static function frontendApplication(): SiteApplication
    {
        return Factory::getContainer()->get(SiteApplication::class);
    }

    private static function loadProjectInfo(int $projectId): ?object
    {
        $db = self::database();
        $query = $db->getQuery(true)
            ->select('p.*')
            ->select($db->quoteName('l.country'))
            ->select($db->quoteName('st.id', 'sport_type_id'))
            ->select($db->quoteName('st.name', 'sport_type_name'))
            ->select($db->quoteName('s.name', 'season_name'))
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('p.sports_type_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id'))
            ->where($db->quoteName('p.id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    private static function resolveCurrentRoundId(object $project): int
    {
        $projectId = (int) ($project->id ?? 0);
        if ($projectId <= 0) {
            return 0;
        }

        $db = self::database();
        $mode = (int) ($project->current_round_auto ?? 0);
        $autoTime = (int) ($project->auto_time ?? 0);
        if ($autoTime <= 0) {
            $autoTime = 7200;
        }
        $currentDate = date('Y-m-d');

        $query = $db->getQuery(true)
            ->select([$db->quoteName('r.id'), $db->quoteName('r.roundcode')])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = ' . $projectId);

        switch ($mode) {
            case 0:
                if ((int) ($project->current_round ?? 0) > 0) {
                    $query->where($db->quoteName('r.id') . ' = ' . (int) $project->current_round);
                }
                break;
            case 1:
                $query->where('(r.round_date_first - INTERVAL ' . $autoTime . ' MINUTE < ' . $db->quote($currentDate) . ')')
                    ->order($db->quoteName('r.round_date_first') . ' DESC');
                break;
            case 2:
                $query->where('(r.round_date_last - INTERVAL ' . $autoTime . ' MINUTE < ' . $db->quote($currentDate) . ')')
                    ->order($db->quoteName('r.round_date_first') . ' DESC');
                break;
            case 3:
                $query->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
                    ->where('(m.match_date - INTERVAL ' . $autoTime . ' MINUTE < ' . $db->quote($currentDate) . ')')
                    ->order($db->quoteName('m.match_date') . ' DESC');
                break;
            case 4:
                $query->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
                    ->where('(m.match_date + INTERVAL ' . $autoTime . ' MINUTE < ' . $db->quote($currentDate) . ')')
                    ->order($db->quoteName('m.match_date') . ' ASC');
                break;
        }

        $db->setQuery($query, 0, 1);
        $round = $db->loadObject();

        if (!$round && (int) ($project->current_round ?? 0) > 0) {
            $fallback = $db->getQuery(true)
                ->select([$db->quoteName('r.id'), $db->quoteName('r.roundcode')])
                ->from($db->quoteName('#__sportsmanagement_round', 'r'))
                ->where($db->quoteName('r.id') . ' = ' . (int) $project->current_round)
                ->where($db->quoteName('r.project_id') . ' = ' . $projectId);
            $db->setQuery($fallback, 0, 1);
            $round = $db->loadObject();
        }

        if (!$round) {
            $fallback = $db->getQuery(true)
                ->select([$db->quoteName('r.id'), $db->quoteName('r.roundcode')])
                ->from($db->quoteName('#__sportsmanagement_round', 'r'))
                ->where($db->quoteName('r.project_id') . ' = ' . $projectId)
                ->order($db->quoteName('r.roundcode') . (in_array($mode, [0, 2], true) ? ' DESC' : ' ASC'));
            $db->setQuery($fallback, 0, 1);
            $round = $db->loadObject();
        }

        if ($round && (int) ($project->current_round ?? 0) !== (int) $round->id) {
            $db->updateObject('#__sportsmanagement_project', (object) [
                'id' => $projectId,
                'current_round' => (int) $round->id,
            ], 'id');
        }

        return (int) ($round->id ?? 0);
    }
}
