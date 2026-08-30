<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

final class TeamstatsModel extends SportsManagementProjectModel
{
    public static int $projectid = 0;
    public static int $teamid = 0;
    public static int $projectteamid = 0;
    public static $highest_home = null;
    public static $highest_away = null;
    public static $highestdef_home = null;
    public static $highestdef_away = null;
    public static $highestdraw_home = null;
    public static $highestdraw_away = null;
    public static $totalshome = null;
    public static $totalsaway = null;
    public static $matchdaytotals = null;
    public static $totalrounds = null;
    public static $attendanceranking = null;
    public static $team = null;
    public static $nogoals_against = null;
    public static int $cfg_which_database = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$projectid = $this->projectId;
        self::$teamid = $input->getInt('tid', 0);
        self::$projectteamid = $input->getInt('ptid', 0);
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0);

        self::$team = null;
        self::$totalshome = null;
        self::$totalsaway = null;
        self::$matchdaytotals = null;
        self::$totalrounds = null;
        self::$attendanceranking = null;
        self::$nogoals_against = null;

        self::getTeam();
    }

    public static function getTeam()
    {
        if (self::$team !== null) {
            return self::$team;
        }
        if (self::$teamid <= 0) {
            return false;
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select('t.*')
            ->select("CONCAT_WS(':', t.id, t.alias) AS slug")
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->where($db->quoteName('t.id') . ' = ' . self::$teamid);
        $db->setQuery($query, 0, 1);
        self::$team = $db->loadObject() ?: null;

        return self::$team ?: false;
    }

    public static function getHighest($homeaway, $which)
    {
        $team = self::getTeam();
        if (!$team || self::$projectid <= 0) {
            return false;
        }

        $homeAway = strtoupper((string) $homeaway) === 'AWAY' ? 'AWAY' : 'HOME';
        $mode = strtoupper((string) $which);
        if (!in_array($mode, ['WIN', 'DEF', 'DRAW'], true)) {
            return false;
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id', 'matchid'),
                $db->quoteName('t1.name', 'hometeam'),
                $db->quoteName('t2.name', 'guestteam'),
                $db->quoteName('m.team1_result', 'homegoals'),
                $db->quoteName('m.team2_result', 'guestgoals'),
                $db->quoteName('t1.id', 'team1_id'),
                $db->quoteName('t2.id', 'team2_id'),
                $db->quoteName('pt1.id', 'pt1_id'),
                $db->quoteName('pt2.id', 'pt2_id'),
                $db->quoteName('st1.id', 'st1_id'),
                $db->quoteName('st2.id', 'st2_id'),
                "CONCAT_WS(':', t1.id, t1.alias) AS team1_slug",
                "CONCAT_WS(':', t2.id, t2.alias) AS team2_slug",
                "CONCAT_WS(':', m.id, CONCAT_WS('_', t1.alias, t2.alias)) AS match_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->where($db->quoteName('pt1.project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('m.alt_decision') . ' = 0')
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)');

        if ($homeAway === 'HOME') {
            $query->where($db->quoteName('t1.id') . ' = ' . (int) $team->id);
            if ($mode === 'WIN') {
                $query->where($db->quoteName('m.team1_result') . ' > ' . $db->quoteName('m.team2_result'))
                    ->order('(' . $db->quoteName('m.team1_result') . '-' . $db->quoteName('m.team2_result') . ') DESC');
            } elseif ($mode === 'DEF') {
                $query->where($db->quoteName('m.team2_result') . ' > ' . $db->quoteName('m.team1_result'))
                    ->order('(' . $db->quoteName('m.team2_result') . '-' . $db->quoteName('m.team1_result') . ') DESC');
            } else {
                $query->where($db->quoteName('m.team2_result') . ' = ' . $db->quoteName('m.team1_result'))
                    ->order($db->quoteName('m.team1_result') . ' DESC');
            }
        } else {
            $query->where($db->quoteName('t2.id') . ' = ' . (int) $team->id);
            if ($mode === 'WIN') {
                $query->where($db->quoteName('m.team2_result') . ' > ' . $db->quoteName('m.team1_result'))
                    ->order('(' . $db->quoteName('m.team2_result') . '-' . $db->quoteName('m.team1_result') . ') DESC');
            } elseif ($mode === 'DEF') {
                $query->where($db->quoteName('m.team1_result') . ' > ' . $db->quoteName('m.team2_result'))
                    ->order('(' . $db->quoteName('m.team1_result') . '-' . $db->quoteName('m.team2_result') . ') DESC');
            } else {
                $query->where($db->quoteName('m.team1_result') . ' = ' . $db->quoteName('m.team2_result'))
                    ->order($db->quoteName('m.team2_result') . ' DESC');
            }
        }

        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: false;
    }

    public static function getNoGoalsAgainst()
    {
        $team = self::getTeam();
        if (!$team || self::$projectid <= 0) {
            return false;
        }
        if (self::$nogoals_against !== null) {
            return self::$nogoals_against;
        }

        $teamId = (int) $team->id;
        $db = self::database();
        $query = $db->getQuery(true)
            ->select('COUNT(' . $db->quoteName('m.id') . ') AS totalzero')
            ->select('SUM(CASE WHEN ' . $db->quoteName('t1.id') . ' = ' . $teamId . ' AND ' . $db->quoteName('m.team2_result') . ' = 0 THEN 1 ELSE 0 END) AS homezero')
            ->select('SUM(CASE WHEN ' . $db->quoteName('t2.id') . ' = ' . $teamId . ' AND ' . $db->quoteName('m.team1_result') . ' = 0 THEN 1 ELSE 0 END) AS awayzero')
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->where($db->quoteName('pt1.project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('m.alt_decision') . ' = 0')
            ->where('((' . $db->quoteName('t1.id') . ' = ' . $teamId . ' AND ' . $db->quoteName('m.team2_result') . ' = 0) OR (' . $db->quoteName('t2.id') . ' = ' . $teamId . ' AND ' . $db->quoteName('m.team1_result') . ' = 0))')
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)');
        $db->setQuery($query, 0, 1);
        self::$nogoals_against = $db->loadObject() ?: false;
        return self::$nogoals_against;
    }

    public static function getSeasonTotals($which)
    {
        $team = self::getTeam();
        if (!$team || self::$projectid <= 0) {
            return false;
        }

        $mode = strtoupper((string) $which) === 'AWAY' ? 'AWAY' : 'HOME';
        if ($mode === 'HOME' && self::$totalshome !== null) {
            return self::$totalshome;
        }
        if ($mode === 'AWAY' && self::$totalsaway !== null) {
            return self::$totalsaway;
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select([
                'COUNT(m.id) AS totalmatches',
                'COUNT(m.team1_result) AS playedmatches',
                'COUNT(m.crowd) AS attendedmatches',
                'SUM(m.crowd) AS sumspectators',
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'));

        if ($mode === 'HOME') {
            $query->select('IFNULL(SUM(m.team1_result),0) AS goalsfor, IFNULL(SUM(m.team2_result),0) AS goalsagainst, IFNULL(SUM(m.team1_result + m.team2_result),0) AS totalgoals, IFNULL(SUM(IF(m.team1_result=m.team2_result,1,0)),0) AS totaldraw, IFNULL(SUM(IF(m.team1_result<m.team2_result,1,0)),0) AS totalloss, IFNULL(SUM(IF(m.team1_result>m.team2_result,1,0)),0) AS totalwin')
                ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.id') . ' = ' . $db->quoteName('m.projectteam1_id'));
        } else {
            $query->select('IFNULL(SUM(m.team2_result),0) AS goalsfor, IFNULL(SUM(m.team1_result),0) AS goalsagainst, IFNULL(SUM(m.team2_result + m.team1_result),0) AS totalgoals, IFNULL(SUM(IF(m.team2_result=m.team1_result,1,0)),0) AS totaldraw, IFNULL(SUM(IF(m.team2_result<m.team1_result,1,0)),0) AS totalloss, IFNULL(SUM(IF(m.team2_result>m.team1_result,1,0)),0) AS totalwin')
                ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.id') . ' = ' . $db->quoteName('m.projectteam2_id'));
        }

        $query->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('t.id') . ' = ' . (int) $team->id)
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)');

        $db->setQuery($query, 0, 1);
        $result = $db->loadObject() ?: false;
        if ($mode === 'HOME') {
            self::$totalshome = $result;
        } else {
            self::$totalsaway = $result;
        }
        return $result;
    }

    public static function getMatchDayTotals()
    {
        if (self::$matchdaytotals !== null) {
            return self::$matchdaytotals;
        }
        if (self::$projectid <= 0 || self::$teamid <= 0) {
            return [];
        }

        $db = self::database();
        $query = self::matchDayQuery($db, false);
        $db->setQuery($query);
        self::$matchdaytotals = $db->loadObjectList() ?: [];
        return self::$matchdaytotals;
    }

    public static function getTotalRounds(): int
    {
        if (self::$totalrounds !== null) {
            return (int) self::$totalrounds;
        }
        if (self::$projectid <= 0) {
            return 0;
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select('COUNT(' . $db->quoteName('id') . ')')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . self::$projectid);
        $db->setQuery($query, 0, 1);
        self::$totalrounds = (int) ($db->loadResult() ?: 0);
        if (!self::$totalrounds) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_RANKING_NO_ROUNDS'), Log::INFO, 'jsmerror');
        }
        return (int) self::$totalrounds;
    }

    public static function getBestAttendance()
    {
        $attendance = self::_getAttendance();
        return $attendance ? max($attendance) : 0;
    }

    public static function _getAttendance(): array
    {
        if (is_array(self::$attendanceranking)) {
            return self::$attendanceranking;
        }
        if (self::$projectid <= 0 || self::$teamid <= 0) {
            return [];
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select($db->quoteName('m.crowd'))
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->where($db->quoteName('pt1.project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('st1.team_id') . ' = ' . self::$teamid)
            ->where($db->quoteName('m.crowd') . ' > 0')
            ->where($db->quoteName('m.published') . ' = 1')
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)');
        $db->setQuery($query);
        self::$attendanceranking = array_map('intval', $db->loadColumn() ?: []);
        return self::$attendanceranking;
    }

    public static function getWorstAttendance()
    {
        $attendance = self::_getAttendance();
        return $attendance ? min($attendance) : 0;
    }

    public static function getTotalAttendance()
    {
        return array_sum(self::_getAttendance());
    }

    public static function getAverageAttendance()
    {
        $attendance = self::_getAttendance();
        return $attendance ? round(array_sum($attendance) / count($attendance), 0) : 0;
    }

    public static function getChartURL(): string
    {
        if (!class_exists('sportsmanagementHelperRoute', false)) {
            $routeFile = JPATH_SITE . '/components/com_sportsmanagement/helpers/route.php';

            if (is_file($routeFile)) {
                require_once $routeFile;
            }
        }

        if (!class_exists('sportsmanagementHelperRoute', false)) {
            return '';
        }

        return str_replace('&', '%26', \sportsmanagementHelperRoute::getTeamStatsChartDataRoute(
            self::$projectid,
            self::$teamid,
            self::$cfg_which_database
        ));
    }

    public static function getLogo(): string
    {
        if (self::$teamid <= 0) {
            return '';
        }
        $db = self::database();
        $query = $db->getQuery(true)
            ->select($db->quoteName('c.logo_big'))
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id'))
            ->where($db->quoteName('t.id') . ' = ' . self::$teamid);
        $db->setQuery($query, 0, 1);
        $logo = (string) ($db->loadResult() ?: '');
        return $logo === '' ? '' : Uri::root() . ltrim($logo, '/');
    }

    public static function getResults(): array
    {
        if (self::$projectid <= 0 || self::$teamid <= 0) {
            return self::emptyResults();
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.projectteam1_id'),
                $db->quoteName('m.projectteam2_id'),
                $db->quoteName('pt1.team_id', 'steam1_id'),
                $db->quoteName('pt2.team_id', 'steam2_id'),
                $db->quoteName('m.team1_result'),
                $db->quoteName('m.team2_result'),
                $db->quoteName('m.alt_decision'),
                $db->quoteName('m.team1_result_decision'),
                $db->quoteName('m.team2_result_decision'),
                $db->quoteName('t1.id', 'team1_id'),
                $db->quoteName('t2.id', 'team2_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->where($db->quoteName('pt1.project_id') . ' = ' . self::$projectid)
            ->where('((' . $db->quoteName('st1.team_id') . ' = ' . self::$teamid . ') OR (' . $db->quoteName('st2.team_id') . ' = ' . self::$teamid . '))')
            ->where('(' . $db->quoteName('m.team1_result') . ' IS NOT NULL OR ' . $db->quoteName('m.alt_decision') . ' > 0)')
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)');
        $db->setQuery($query);
        $matches = $db->loadObjectList() ?: [];
        $results = self::emptyResults();

        foreach ($matches as $match) {
            $home = (int) $match->team1_id === self::$teamid;
            if (!(int) $match->alt_decision) {
                $own = $home ? $match->team1_result : $match->team2_result;
                $other = $home ? $match->team2_result : $match->team1_result;
                self::classifyResult($results, $match, $home, $own, $other);
                continue;
            }

            $ownDecision = $home ? $match->team1_result_decision : $match->team2_result_decision;
            $otherDecision = $home ? $match->team2_result_decision : $match->team1_result_decision;
            if (empty($ownDecision)) {
                $results['forfeit'][] = $match;
            } elseif (empty($otherDecision)) {
                $results['win'][] = $match;
            } else {
                self::classifyResult($results, $match, $home, $ownDecision, $otherDecision);
            }
        }
        return $results;
    }

    public function getChartData(): array
    {
        if (self::$projectid <= 0 || self::$teamid <= 0) {
            return [];
        }
        $db = $this->getDatabase();
        $query = self::matchDayQuery($db, true);
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getStats(): array
    {
        $teamStats = [];
        foreach ($this->getProjectStats() as $positionStats) {
            foreach ((array) $positionStats as $statId => $stat) {
                if (!is_object($stat) || !method_exists($stat, 'getParam') || !method_exists($stat, 'getRosterTotalStats')) {
                    continue;
                }
                if (!$stat->getParam('show_in_teamstats', 1) || isset($teamStats[$statId])) {
                    continue;
                }
                $teamStats[$statId] = $stat;
                $teamStats[$statId]->value = $stat->getRosterTotalStats(self::$teamid, self::$projectid);
            }
        }
        return $teamStats;
    }

    private static function matchDayQuery(DatabaseInterface $db, bool $chart)
    {
        $query = $db->getQuery(true)
            ->select([$db->quoteName('r.id'), $db->quoteName('r.roundcode')])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->where($db->quoteName('r.project_id') . ' = ' . self::$projectid)
            ->where('((' . $db->quoteName('st1.team_id') . ' = ' . self::$teamid . ') OR (' . $db->quoteName('st2.team_id') . ' = ' . self::$teamid . '))')
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)')
            ->group([$db->quoteName('r.id'), $db->quoteName('r.roundcode')])
            ->order($db->quoteName('r.roundcode') . ' ASC');

        if ($chart) {
            $query->select('SUM(CASE WHEN st1.team_id = ' . self::$teamid . ' THEN m.team1_result ELSE m.team2_result END) AS goalsfor')
                ->select('SUM(CASE WHEN st1.team_id = ' . self::$teamid . ' THEN m.team2_result ELSE m.team1_result END) AS goalsagainst')
                ->where($db->quoteName('m.team1_result') . ' IS NOT NULL');
        } else {
            $query->select([
                'COUNT(m.round_id) AS totalmatchespd',
                'COUNT(m.id) AS playedmatchespd',
                'SUM(m.team1_result) AS homegoalspd',
                'SUM(m.team2_result) AS guestgoalspd',
            ]);
        }
        return $query;
    }

    private static function emptyResults(): array
    {
        return [
            'win' => [], 'tie' => [], 'loss' => [], 'forfeit' => [],
            'home_wins' => 0, 'home_draws' => 0, 'home_losses' => 0,
            'away_wins' => 0, 'away_draws' => 0, 'away_losses' => 0,
        ];
    }

    private static function classifyResult(array &$results, object $match, bool $home, $own, $other): void
    {
        if ($own > $other) {
            $results['win'][] = $match;
            $results[$home ? 'home_wins' : 'away_wins']++;
        } elseif ($own < $other) {
            $results['loss'][] = $match;
            $results[$home ? 'home_losses' : 'away_losses']++;
        } else {
            $results['tie'][] = $match;
            $results[$home ? 'home_draws' : 'away_draws']++;
        }
    }

    private static function database(): DatabaseInterface
    {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);

        return SportsManagementDatabaseResolver::resolve(
            $joomlaDatabase,
            self::$cfg_which_database
        );
    }
}
