<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Legacy\RankingProjectFacade;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class CurveModel extends SportsManagementProjectModel
{
    public static int $projectid = 0;
    public static int $teamid1 = 0;
    public static int $teamid2 = 0;
    public static int $division = 0;
    public static int $cfg_which_database = 0;
    public static int $season_id = 0;

    public $project = null;
    public array $team1 = [];
    public array $team2 = [];
    public $allteams = null;
    public $divisions = null;
    public $favteams = null;
    public $divlevel = null;
    public int $height = 180;
    public array $selectoptions = [];
    public array $teamlist2options = [];
    public int $round = 0;
    public array $roundsName = [];
    public array $ranking1 = [];
    public array $ranking2 = [];
    public array $ranking = [];
    public array $teamcount = [];
    public int $both = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = Factory::getApplication()->getInput();
        self::$projectid = $this->projectId;
        self::$division = $this->divisionId;
        self::$teamid1 = max(0, $input->getInt('tid1', 0));
        self::$teamid2 = max(0, $input->getInt('tid2', 0));
        self::$cfg_which_database = max(0, $input->getInt('cfg_which_database', 0));
        self::$season_id = max(0, $input->getInt('s', 0));
        $this->both = max(0, $input->getInt('both', 0));

        if (self::$division > 0) {
            $postedTeam1 = $input->getInt('tid1_' . self::$division, 0);
            $postedTeam2 = $input->getInt('tid2_' . self::$division, 0);
            if ($postedTeam1 > 0 || $postedTeam2 > 0) {
                self::$teamid1 = max(0, $postedTeam1);
                self::$teamid2 = max(0, $postedTeam2);
            }
        }

        $this->determineTeam1And2();
    }

    public function determineTeam1And2(): void
    {
        if (self::$teamid1 === 0 && self::$teamid2 === 0) {
            $favorites = $this->getFavTeams();
            self::$teamid1 = (int) ($favorites[0] ?? 0);
            self::$teamid2 = (int) ($favorites[1] ?? 0);
        }

        if (self::$teamid1 > 0 && self::$teamid2 > 0) {
            return;
        }

        $knownTeamId = self::$teamid1 > 0 ? self::$teamid1 : self::$teamid2;
        if ($knownTeamId <= 0 || self::$projectid <= 0) {
            return;
        }

        $config = $this->getTemplateConfig('curve');
        $expiryTime = max(0, (int) ($config['expiry_time'] ?? 0));
        $match = $this->findRelatedMatch($knownTeamId, $expiryTime, true);
        if (!$match) {
            $match = $this->findRelatedMatch($knownTeamId, $expiryTime, false);
        }

        if ($match) {
            self::$teamid1 = (int) $match->teamid1;
            self::$teamid2 = (int) $match->teamid2;
        }
    }

    public function getDivLevel()
    {
        if ($this->divlevel === null) {
            $config = $this->getTemplateConfig('ranking');
            $this->divlevel = (int) ($config['default_division_view'] ?? 0);
        }
        return $this->divlevel;
    }

    public function getTeam1($division = 0)
    {
        if (self::$teamid1 <= 0) {
            return false;
        }
        foreach ($this->getDataByDivision((int) $division) as $team) {
            if ((int) ($team->id ?? 0) === self::$teamid1) {
                return $team;
            }
        }
        return false;
    }

    public function getDataByDivision($division = 0): array
    {
        $project = $this->getProject();
        if (!$project) {
            return [];
        }

        $divisionId = max(0, (int) $division);
        $rounds = $this->getRounds('ASC');
        $teams = [];
        foreach ($this->getProjectTeams($divisionId) as $team) {
            $projectTeamId = (int) ($team->projectteamid ?? 0);
            if ($projectTeamId > 0) {
                $teams[$projectTeamId] = $team;
            }
        }

        if (!$rounds || !$teams) {
            return $teams;
        }

        // JSMRanking still calls the historical global project model. Bind its
        // narrow compatibility surface to this already active native model so
        // curve stays on the Joomla 5/6 namespaced MVC path.
        RankingProjectFacade::setModel($this);
        if (!class_exists('sportsmanagementModelProject', false)) {
            class_alias(RankingProjectFacade::class, 'sportsmanagementModelProject');
        }

        if (!class_exists('JSMRanking')) {
            \JLoader::register(
                'JSMRanking',
                JPATH_SITE . '/components/com_sportsmanagement/helpers/ranking.php'
            );
        }
        if (!class_exists('JSMRanking')) {
            return $teams;
        }

        $rankingHelper = \JSMRanking::getInstance($project, self::$cfg_which_database);
        if (!$rankingHelper) {
            return $teams;
        }
        $rankingHelper->setProjectId((int) $project->id, self::$cfg_which_database);

        $firstRoundId = (int) ($rounds[0]->id ?? 0);
        if ($firstRoundId <= 0) {
            return $teams;
        }

        $rankings = [];
        foreach ($rounds as $round) {
            $roundId = (int) ($round->id ?? 0);
            if ($roundId <= 0) {
                continue;
            }
            $rankings[$roundId] = $rankingHelper->getRanking(
                $firstRoundId,
                $roundId,
                $divisionId,
                self::$cfg_which_database
            );
        }

        foreach ($teams as $projectTeamId => $team) {
            if (isset($team->is_in_score) && (int) $team->is_in_score === 0) {
                continue;
            }
            $teamRankings = [];
            foreach ($rankings as $ranking) {
                if (!isset($ranking[$projectTeamId])) {
                    continue;
                }
                $teamRankings[] = (int) ($ranking[$projectTeamId]->rank ?? 0);
            }
            $team->rankings = $teamRankings;
        }

        return $teams;
    }

    public function getTeam2($division = 0)
    {
        if (self::$teamid2 <= 0) {
            return false;
        }
        foreach ($this->getDataByDivision((int) $division) as $team) {
            if ((int) ($team->id ?? 0) === self::$teamid2) {
                return $team;
            }
        }
        return false;
    }

    public function getDivisionId(): int
    {
        return self::$division;
    }

    public function getDivisions(): array
    {
        if (self::$projectid <= 0) {
            return [];
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('ordering') . ' ASC');
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getTeamsForDivision(int $divisionId = 0): array
    {
        return $this->getProjectTeams(max(0, $divisionId));
    }

    public function getColors(string $configColors = ''): array
    {
        $colors = [[
            'from' => '',
            'to' => '',
            'color' => '',
            'description' => '',
        ]];

        if (trim($configColors) === '') {
            return $colors;
        }

        $colors = [];
        foreach (explode(';', $configColors) as $entry) {
            $parts = array_map('trim', explode(',', $entry));
            if (count($parts) !== 4) {
                continue;
            }
            $colors[] = [
                'from' => $parts[0],
                'to' => $parts[1],
                'color' => $parts[2],
                'description' => $parts[3],
            ];
        }
        return $colors ?: [[
            'from' => '',
            'to' => '',
            'color' => '',
            'description' => '',
        ]];
    }

    private function findRelatedMatch(int $teamId, int $expiryTime, bool $upcoming): ?object
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t1.id', 'teamid1'),
                $db->quoteName('t2.id', 'teamid2'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt1.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('st1.team_id') . ' = ' . $db->quoteName('t1.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt2.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('st2.team_id') . ' = ' . $db->quoteName('t2.id'))
            ->where($db->quoteName('pt1.project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('pt2.project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)');

        if (self::$division > 0) {
            $query->where($db->quoteName('pt1.division_id') . ' = ' . self::$division)
                ->where($db->quoteName('pt2.division_id') . ' = ' . self::$division);
        }

        if ($this->both) {
            $query->where('(' . $db->quoteName('st1.team_id') . ' = ' . $teamId . ' OR ' . $db->quoteName('st2.team_id') . ' = ' . $teamId . ')');
        } elseif (self::$teamid1 > 0) {
            $query->where($db->quoteName('st1.team_id') . ' = ' . $teamId);
        } else {
            $query->where($db->quoteName('st2.team_id') . ' = ' . $teamId);
        }

        if ($upcoming) {
            $query->where('(' . $db->quoteName('m.team1_result') . ' IS NULL OR ' . $db->quoteName('m.team2_result') . ' IS NULL)')
                ->where('DATE_ADD(' . $db->quoteName('m.match_date') . ', INTERVAL ' . $expiryTime . ' MINUTE) >= NOW()')
                ->order($db->quoteName('m.match_date') . ' ASC');
        } else {
            $query->where($db->quoteName('m.team1_result') . ' IS NOT NULL')
                ->where($db->quoteName('m.team2_result') . ' IS NOT NULL')
                ->order($db->quoteName('m.match_date') . ' DESC');
        }

        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }
}
