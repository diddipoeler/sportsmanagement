<?php
/**
 * Native Joomla 5/6 frontend curve model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Legacy\CurveRankingAdapter;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class CurveModel extends SportsManagementProjectModel
{
    /** @deprecated Compatibility state for third-party legacy callers. */
    public static int $projectid = 0;
    /** @deprecated Compatibility state for third-party legacy callers. */
    public static int $teamid1 = 0;
    /** @deprecated Compatibility state for third-party legacy callers. */
    public static int $teamid2 = 0;
    /** @deprecated Compatibility state for third-party legacy callers. */
    public static int $division = 0;
    /** @deprecated Compatibility state for third-party legacy callers. */
    public static int $cfg_which_database = 0;
    /** @deprecated Compatibility state for third-party legacy callers. */
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

    private int $selectedTeamId1 = 0;
    private int $selectedTeamId2 = 0;
    private int $curveDivisionId = 0;
    private int $databaseSelector = 0;
    private int $requestSeasonId = 0;
    private array $divisionDataCache = [];

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        $this->curveDivisionId = $this->divisionId;
        $this->selectedTeamId1 = max(0, $input->getInt('tid1', 0));
        $this->selectedTeamId2 = max(0, $input->getInt('tid2', 0));
        $this->databaseSelector = max(0, $input->getInt('cfg_which_database', 0));
        $this->requestSeasonId = max(0, $input->getInt('s', 0));
        $this->both = max(0, $input->getInt('both', 0));

        if ($this->curveDivisionId > 0) {
            $postedTeam1 = $input->getInt('tid1_' . $this->curveDivisionId, 0);
            $postedTeam2 = $input->getInt('tid2_' . $this->curveDivisionId, 0);
            if ($postedTeam1 > 0 || $postedTeam2 > 0) {
                $this->selectedTeamId1 = max(0, $postedTeam1);
                $this->selectedTeamId2 = max(0, $postedTeam2);
            }
        }

        $this->determineTeam1And2();
        $this->syncLegacyState();
    }

    public function determineTeam1And2(): void
    {
        if ($this->selectedTeamId1 === 0 && $this->selectedTeamId2 === 0) {
            $favorites = $this->getFavTeams();
            $this->selectedTeamId1 = (int) ($favorites[0] ?? 0);
            $this->selectedTeamId2 = (int) ($favorites[1] ?? 0);
        }

        if ($this->selectedTeamId1 > 0 && $this->selectedTeamId2 > 0) {
            $this->syncLegacyState();
            return;
        }

        $knownTeamId = $this->selectedTeamId1 > 0
            ? $this->selectedTeamId1
            : $this->selectedTeamId2;
        if ($knownTeamId <= 0 || $this->projectId <= 0) {
            $this->syncLegacyState();
            return;
        }

        $config = $this->getTemplateConfig('curve');
        $expiryTime = max(0, (int) ($config['expiry_time'] ?? 0));
        $match = $this->findRelatedMatch($knownTeamId, $expiryTime, true);
        if (!$match) {
            $match = $this->findRelatedMatch($knownTeamId, $expiryTime, false);
        }

        if ($match) {
            $this->selectedTeamId1 = (int) $match->teamid1;
            $this->selectedTeamId2 = (int) $match->teamid2;
        }

        $this->syncLegacyState();
    }

    public function getSelectedTeamId1(): int
    {
        return $this->selectedTeamId1;
    }

    public function getSelectedTeamId2(): int
    {
        return $this->selectedTeamId2;
    }

    public function setSelectedTeamIds(int $teamId1, int $teamId2): void
    {
        $this->selectedTeamId1 = max(0, $teamId1);
        $this->selectedTeamId2 = max(0, $teamId2);
        $this->syncLegacyState();
    }

    public function getCurveDivisionId(): int
    {
        return $this->curveDivisionId;
    }

    public function getDatabaseSelector(): int
    {
        return $this->databaseSelector;
    }

    public function getRequestSeasonId(): int
    {
        return $this->requestSeasonId;
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
        if ($this->selectedTeamId1 <= 0) {
            return false;
        }
        foreach ($this->getDataByDivision((int) $division) as $team) {
            if ((int) ($team->id ?? 0) === $this->selectedTeamId1) {
                return $team;
            }
        }
        return false;
    }

    public function getDataByDivision($division = 0): array
    {
        $divisionId = max(0, (int) $division);
        if (array_key_exists($divisionId, $this->divisionDataCache)) {
            return $this->divisionDataCache[$divisionId];
        }

        $project = $this->getProject();
        if (!$project) {
            return $this->divisionDataCache[$divisionId] = [];
        }

        $rounds = $this->getRounds('ASC');
        $teams = [];
        foreach ($this->getProjectTeams($divisionId) as $team) {
            $projectTeamId = (int) ($team->projectteamid ?? 0);
            if ($projectTeamId > 0) {
                $teams[$projectTeamId] = $team;
            }
        }

        if (!$rounds || !$teams) {
            return $this->divisionDataCache[$divisionId] = $teams;
        }

        $rankings = CurveRankingAdapter::getRankings(
            $this,
            $project,
            $rounds,
            $divisionId,
            $this->databaseSelector
        );

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

        return $this->divisionDataCache[$divisionId] = $teams;
    }

    public function getTeam2($division = 0)
    {
        if ($this->selectedTeamId2 <= 0) {
            return false;
        }
        foreach ($this->getDataByDivision((int) $division) as $team) {
            if ((int) ($team->id ?? 0) === $this->selectedTeamId2) {
                return $team;
            }
        }
        return false;
    }

    public function getDivisionId(): int
    {
        return $this->curveDivisionId;
    }

    public function getDivisions(): array
    {
        if ($this->projectId <= 0) {
            return [];
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('project_id') . ' = ' . $this->projectId)
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
            ->where($db->quoteName('pt1.project_id') . ' = ' . $this->projectId)
            ->where($db->quoteName('pt2.project_id') . ' = ' . $this->projectId)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)');

        if ($this->curveDivisionId > 0) {
            $query->where($db->quoteName('pt1.division_id') . ' = ' . $this->curveDivisionId)
                ->where($db->quoteName('pt2.division_id') . ' = ' . $this->curveDivisionId);
        }

        if ($this->both) {
            $query->where('(' . $db->quoteName('st1.team_id') . ' = ' . $teamId . ' OR ' . $db->quoteName('st2.team_id') . ' = ' . $teamId . ')');
        } elseif ($this->selectedTeamId1 > 0) {
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

    private function syncLegacyState(): void
    {
        self::$projectid = $this->projectId;
        self::$teamid1 = $this->selectedTeamId1;
        self::$teamid2 = $this->selectedTeamId2;
        self::$division = $this->curveDivisionId;
        self::$cfg_which_database = $this->databaseSelector;
        self::$season_id = $this->requestSeasonId;
    }
}
