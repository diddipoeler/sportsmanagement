<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SqlImportHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;

/** Native Joomla 5/6 administrator model for the DFB-key schedule importer. */
final class JlextdfbkeyimportModel extends SportsManagementListModel
{
    public array $savedfb = [];

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $maxImportTime = 480;
        $currentLimit = (int) ini_get('max_execution_time');

        if ($currentLimit > 0 && $currentLimit < $maxImportTime) {
            @set_time_limit($maxImportTime);
        }
    }

    public function _loadData()
    {
        return null;
    }

    public function _initData()
    {
        return null;
    }

    public function getProjectType($project_id = 0)
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('project_type'))
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        try {
            $db->setQuery($query, 0, 1);
            $result = $db->loadResult();

            return $result !== null ? $result : false;
        } catch (\Throwable $e) {
            $this->reportDatabaseError($e);

            return false;
        }
    }

    public function getProjectteams($project_id = 0, $division_id = 0): array
    {
        $projectId = (int) $project_id;
        $divisionId = (int) $division_id;

        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt.id', 'value'),
                $db->quoteName('t.name', 'text'),
                $db->quoteName('t.notes'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('t.name') . ' ASC');

        if ($divisionId > 0) {
            $query
                ->where($db->quoteName('pt.division_id') . ' = :divisionId')
                ->bind(':divisionId', $divisionId, ParameterType::INTEGER);
        }

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->reportDatabaseError($e);

            return [];
        }
    }

    public function getDFBKey($number, $matchdays): array
    {
        $projectId = (int) $this->administratorApplication()->getUserState('com_sportsmanagement.pid', 0);
        $country = (string) $this->getCountry($projectId);

        if ($country === 'ENG') {
            $country = 'DEU';
        }

        if ($country === '') {
            return [];
        }

        $number = (int) $number;
        $number += $number % 2;
        $mode = strtoupper((string) $matchdays);
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->from($db->quoteName('#__sportsmanagement_dfbkey'))
            ->where($db->quoteName('schluessel') . ' = :keyNumber')
            ->where($db->quoteName('country') . ' = :country')
            ->bind(':keyNumber', $number, ParameterType::INTEGER)
            ->bind(':country', $country, ParameterType::STRING);

        if ($mode === 'ALL') {
            $query
                ->select($db->quoteName('spieltag'))
                ->group($db->quoteName('spieltag'))
                ->order($db->quoteName('spieltag') . ' ASC');
        } else {
            $query->select('*');

            if ($mode === 'FIRST') {
                $firstMatchday = 1;
                $query
                    ->where($db->quoteName('spieltag') . ' = :firstMatchday')
                    ->bind(':firstMatchday', $firstMatchday, ParameterType::INTEGER);
            }

            $query
                ->order($db->quoteName('spieltag') . ' ASC')
                ->order($db->quoteName('spielnummer') . ' ASC');
        }

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->reportDatabaseError($e);

            return [];
        }
    }

    public function getCountry($project_id = 0)
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('l.country'))
            ->from($db->quoteName('#__sportsmanagement_league', 'l'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.league_id') . ' = ' . $db->quoteName('l.id')
            )
            ->where($db->quoteName('p.id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        try {
            $db->setQuery($query, 0, 1);
            $country = $db->loadResult();

            return $country !== null ? $country : false;
        } catch (\Throwable $e) {
            $this->reportDatabaseError($e);

            return false;
        }
    }

    public function getMatchdays($project_id = 0): array
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('roundcode') . ' ASC');

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->reportDatabaseError($e);

            return [];
        }
    }

    public function getMatches($project_id = 0, $division_id = 0): int
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();

        try {
            $roundQuery = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_round'))
                ->where($db->quoteName('project_id') . ' = :projectId')
                ->bind(':projectId', $projectId, ParameterType::INTEGER);
            $db->setQuery($roundQuery);
            $roundIds = array_map('intval', $db->loadColumn() ?: []);

            if (!$roundIds) {
                return 0;
            }

            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__sportsmanagement_match'))
                ->where($db->quoteName('round_id') . ' IN (' . implode(',', $roundIds) . ')');
            $db->setQuery($query);

            return (int) $db->loadResult();
        } catch (\Throwable $e) {
            $this->reportDatabaseError($e);

            return 0;
        }
    }

    public function getSchedule($post = [], $project_id = 0, $division_id = 0): array
    {
        $post = is_array($post) ? $post : [];
        $projectId = (int) $project_id;
        $divisionId = (int) $division_id;
        $chosenTeams = [];
        $db = $this->getDatabase();

        foreach ($post as $key => $element) {
            if (strncmp((string) $key, 'chooseteam', 10) !== 0) {
                continue;
            }

            $parts = explode('_', (string) $key, 2);

            if (!isset($parts[1]) || !is_numeric($parts[1])) {
                continue;
            }

            $slot = (int) $parts[1];
            $projectTeamId = (int) $element;
            $teamName = '';

            if ($projectTeamId > 0) {
                $query = $db->getQuery(true)
                    ->select($db->quoteName('team.name'))
                    ->from($db->quoteName('#__sportsmanagement_team', 'team'))
                    ->join(
                        'INNER',
                        $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                        . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('team.id')
                    )
                    ->join(
                        'INNER',
                        $db->quoteName('#__sportsmanagement_project_team', 'pt')
                        . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
                    )
                    ->where($db->quoteName('pt.id') . ' = :projectTeamId')
                    ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER);

                if ($divisionId > 0) {
                    $query
                        ->where($db->quoteName('pt.division_id') . ' = :divisionId')
                        ->bind(':divisionId', $divisionId, ParameterType::INTEGER);
                }

                $db->setQuery($query, 0, 1);
                $teamName = (string) $db->loadResult();
            }

            $chosenTeams[$slot] = [
                'projectteamid' => $projectTeamId,
                'teamname' => $teamName,
            ];
        }

        $number = count($chosenTeams);

        if ($number === 0 || $projectId <= 0) {
            $this->savedfb = [];

            return [];
        }

        $number += $number % 2;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('dfb') . '.*',
                $db->quoteName('jr.id'),
                $db->quoteName('jr.round_date_first'),
            ])
            ->from($db->quoteName('#__sportsmanagement_dfbkey', 'dfb'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'jr')
                . ' ON ' . $db->quoteName('dfb.spieltag') . ' = ' . $db->quoteName('jr.roundcode')
            )
            ->where($db->quoteName('dfb.schluessel') . ' = :keyNumber')
            ->where($db->quoteName('jr.project_id') . ' = :projectId')
            ->bind(':keyNumber', $number, ParameterType::INTEGER)
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('dfb.spielnummer') . ' ASC');
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $result = [];

        foreach ($rows as $row) {
            $pair = array_map('intval', explode(',', preg_replace('/\s+/', '', (string) $row->paarung)));

            if (count($pair) < 2 || empty($chosenTeams[$pair[0]]['projectteamid']) || empty($chosenTeams[$pair[1]]['projectteamid'])) {
                continue;
            }

            $result[] = (object) [
                'spieltag' => $row->spieltag,
                'round_id' => (int) $row->id,
                'spielnummer' => $row->spielnummer,
                'match_date' => $row->round_date_first,
                'division_id' => $divisionId,
                'projectteam1_id' => (int) $chosenTeams[$pair[0]]['projectteamid'],
                'projectteam2_id' => (int) $chosenTeams[$pair[1]]['projectteamid'],
                'projectteam1_name' => (string) $chosenTeams[$pair[0]]['teamname'],
                'projectteam2_name' => (string) $chosenTeams[$pair[1]]['teamname'],
            ];
        }

        $this->savedfb = $result;

        return $result;
    }

    public function getDivisions($project_id): array
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function checkTable(): bool
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_dfbkey'));
        $db->setQuery($query);

        if ((int) $db->loadResult() > 0) {
            return true;
        }

        $sqlFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/sql/dfbkeys.sql';

        try {
            SqlImportHelper::importFile($db, $sqlFile);

            return true;
        } catch (\Throwable $e) {
            $this->reportDatabaseError($e);

            return false;
        }
    }

    private function reportDatabaseError(\Throwable $e): void
    {
        $this->administratorApplication()->enqueueMessage($e->getMessage(), 'error');
    }
}
