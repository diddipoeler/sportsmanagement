<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 form model for one match.
 *
 * The large match-report/line-up/event workflows remain independent services;
 * this model owns the match row itself plus the small helpers used by the
 * native list mass-add/copy controller.
 */
final class MatchModel extends SportsManagementAdminModel
{
    public function getTable($type = 'Match', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'Match') === 0) {
            return new MatchTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    protected function prepareSportsManagementData(array $data): array
    {
        $input = Factory::getApplication()->getInput();

        if (!isset($data['id'])) {
            $data['id'] = $input->getInt('id', 0);
        }

        foreach (['playground_id', 'cancel', 'count_result', 'alt_decision', 'team_won', 'show_report', 'overtime', 'team1_legs', 'team2_legs', 'old_match_id', 'new_match_id'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== '') {
                $data[$field] = (int) $data[$field];
            }
        }

        foreach (['team1_bonus', 'team2_bonus', 'team1_result_decision', 'team2_result_decision'] as $field) {
            if (array_key_exists($field, $data) && trim((string) $data[$field]) === '') {
                $data[$field] = null;
            }
        }

        if (!empty($data['match_date'])) {
            $data['match_timestamp'] = SportsManagementDateHelper::getTimestamp((string) $data['match_date']);
        }

        return parent::prepareSportsManagementData($data);
    }

    public function getMatchData(int $matchId): ?object
    {
        if ($matchId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m') . '.*',
                $db->quoteName('r.project_id'),
                $db->quoteName('r.roundcode'),
                $db->quoteName('t1.name', 'hometeam'),
                $db->quoteName('t1.id', 't1id'),
                $db->quoteName('t2.name', 'awayteam'),
                $db->quoteName('t2.id', 't2id'),
                $db->quoteName('pg.name', 'playground_name'),
                $db->quoteName('pg.picture', 'playground_picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_playground', 'pg') . ' ON ' . $db->quoteName('pg.id') . ' = ' . $db->quoteName('m.playground_id'))
            ->where($db->quoteName('m.id') . ' = ' . $matchId);

        try {
            $db->setQuery($query, 0, 1);
            $match = $db->loadObject();
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return null;
        }

        if (!$match) {
            return null;
        }

        $match->hometeam = (string) ($match->hometeam ?? '');
        $match->awayteam = (string) ($match->awayteam ?? '');
        $match->team1_legs = (int) ($match->team1_legs ?? 0);
        $match->team2_legs = (int) ($match->team2_legs ?? 0);
        $match->playground_id = (int) ($match->playground_id ?? 0);

        return $match;
    }

    /** @return array<int,object> */
    public function getMatchRelationsOptions(int $projectId, array $excludeMatchIds = []): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $excludeMatchIds = array_values(array_unique(array_filter(array_map('intval', $excludeMatchIds), static fn (int $id): bool => $id > 0)));
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id', 'value'),
                $db->quoteName('m.match_date'),
                $db->quoteName('t1.name', 't1_name'),
                $db->quoteName('t2.name', 't2_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->where($db->quoteName('r.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('m.published') . ' = 1')
            ->order($db->quoteName('m.match_date') . ' DESC');

        if ($excludeMatchIds) {
            $query->where($db->quoteName('m.id') . ' NOT IN (' . implode(',', $excludeMatchIds) . ')');
        }

        try {
            $db->setQuery($query);
            $matches = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return [];
        }

        foreach ($matches as $match) {
            $timestamp = strtotime((string) ($match->match_date ?? ''));
            $date = $timestamp === false ? '' : date('d.m.Y H:i', $timestamp);
            $match->text = '(' . $date . ') - ' . (string) ($match->t1_name ?? '') . ' - ' . (string) ($match->t2_name ?? '');
        }

        return $matches;
    }

    /** @return array<int,object> */
    public function getProjectRoundCodes(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('roundcode'),
                $db->quoteName('round_date_first'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->order($db->quoteName('roundcode') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /** @return array<int,object> */
    public function getRoundMatches(int $roundId): array
    {
        if ($roundId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('projectteam1_id'),
                $db->quoteName('projectteam2_id'),
                $db->quoteName('match_number'),
                $db->quoteName('match_date'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match'))
            ->where($db->quoteName('round_id') . ' = ' . $roundId)
            ->order($db->quoteName('id') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /** @return array<int,object> keyed by project-position id */
    public function getRefereePositions(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('ppos.id', 'value'),
                $db->quoteName('pos.id', 'position_id'),
                $db->quoteName('pos.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_position', 'ppos'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('ppos.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('pos.persontype') . ' = 3')
            ->order($db->quoteName('pos.ordering') . ' ASC');
        $db->setQuery($query);
        $positions = $db->loadObjectList('value') ?: [];

        foreach ($positions as $position) {
            $position->text = Text::_((string) $position->text);
        }

        return $positions;
    }

    /** @return array<int,object> */
    public function getAvailableRefereeOptions(int $projectId, int $matchId, bool $teamsAsReferees = false): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $assignedIds = $this->getAssignedRefereeIds($matchId);

        if ($teamsAsReferees) {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('pt.id', 'value'),
                    $db->quoteName('t.name', 'team_name'),
                    $db->quoteName('t.short_name'),
                ])
                ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
                ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
                ->order($db->quoteName('t.name') . ' ASC');
        } else {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('pref.id', 'value'),
                    $db->quoteName('p.firstname'),
                    $db->quoteName('p.nickname'),
                    $db->quoteName('p.lastname'),
                ])
                ->from($db->quoteName('#__sportsmanagement_project_referee', 'pref'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_season_person_id', 'sp') . ' ON ' . $db->quoteName('sp.id') . ' = ' . $db->quoteName('pref.person_id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('sp.person_id'))
                ->where($db->quoteName('pref.project_id') . ' = ' . $projectId)
                ->where($db->quoteName('p.published') . ' = 1')
                ->order($db->quoteName('p.lastname') . ' ASC, ' . $db->quoteName('p.firstname') . ' ASC');
        }

        if ($assignedIds) {
            $query->where(($teamsAsReferees ? $db->quoteName('pt.id') : $db->quoteName('pref.id')) . ' NOT IN (' . implode(',', $assignedIds) . ')');
        }

        $db->setQuery($query);
        $options = $db->loadObjectList() ?: [];

        foreach ($options as $option) {
            if ($teamsAsReferees) {
                $option->text = trim((string) ($option->team_name ?? $option->short_name ?? ''));
            } else {
                $nickname = trim((string) ($option->nickname ?? ''));
                $option->text = trim(
                    (string) ($option->lastname ?? '') . ', '
                    . (string) ($option->firstname ?? '')
                    . ($nickname !== '' ? ' (' . $nickname . ')' : '')
                );
            }
        }

        return $options;
    }

    /** @return array<int,array<int,object>> keyed by project-position id */
    public function getAssignedReferees(int $projectId, int $matchId, bool $teamsAsReferees = false): array
    {
        if ($projectId <= 0 || $matchId <= 0) {
            return [];
        }

        $positions = $this->getRefereePositions($projectId);
        $positionKeyById = [];
        foreach ($positions as $key => $position) {
            $positionKeyById[(int) ($position->position_id ?? 0)] = (int) $key;
        }

        $db = $this->getDatabase();
        if ($teamsAsReferees) {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('mr.project_referee_id', 'value'),
                    $db->quoteName('mr.project_position_id'),
                    $db->quoteName('mr.ordering'),
                    $db->quoteName('t.name', 'team_name'),
                    $db->quoteName('t.short_name'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match_referee', 'mr'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.id') . ' = ' . $db->quoteName('mr.project_referee_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
                ->where($db->quoteName('mr.match_id') . ' = ' . $matchId)
                ->order($db->quoteName('mr.project_position_id') . ' ASC, ' . $db->quoteName('mr.ordering') . ' ASC');
        } else {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('mr.project_referee_id', 'value'),
                    $db->quoteName('mr.project_position_id'),
                    $db->quoteName('mr.ordering'),
                    $db->quoteName('p.firstname'),
                    $db->quoteName('p.nickname'),
                    $db->quoteName('p.lastname'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match_referee', 'mr'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_project_referee', 'pref') . ' ON ' . $db->quoteName('pref.id') . ' = ' . $db->quoteName('mr.project_referee_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season_person_id', 'sp') . ' ON ' . $db->quoteName('sp.id') . ' = ' . $db->quoteName('pref.person_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('sp.person_id'))
                ->where($db->quoteName('mr.match_id') . ' = ' . $matchId)
                ->order($db->quoteName('mr.project_position_id') . ' ASC, ' . $db->quoteName('mr.ordering') . ' ASC');
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $assigned = [];

        foreach ($rows as $row) {
            $positionKey = $positionKeyById[(int) ($row->project_position_id ?? 0)] ?? 0;
            if ($positionKey <= 0) {
                continue;
            }

            if ($teamsAsReferees) {
                $row->text = trim((string) ($row->team_name ?? $row->short_name ?? ''));
            } else {
                $nickname = trim((string) ($row->nickname ?? ''));
                $row->text = trim(
                    (string) ($row->lastname ?? '') . ', '
                    . (string) ($row->firstname ?? '')
                    . ($nickname !== '' ? ' (' . $nickname . ')' : '')
                );
            }

            $assigned[$positionKey][] = $row;
        }

        return $assigned;
    }

    /** @return array<int,int> */
    private function getAssignedRefereeIds(int $matchId): array
    {
        if ($matchId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('project_referee_id'))
            ->from($db->quoteName('#__sportsmanagement_match_referee'))
            ->where($db->quoteName('match_id') . ' = ' . $matchId);
        $db->setQuery($query);

        return array_values(array_unique(array_filter(array_map('intval', $db->loadColumn() ?: []))));
    }

    public function delete(&$pks)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $pks), static fn (int $id): bool => $id > 0)));

        if (!$ids) {
            return true;
        }

        $db = $this->getDatabase();
        $in = implode(',', $ids);
        $availableTables = array_flip($db->getTableList());
        $childTables = [
            '#__sportsmanagement_match_statistic',
            '#__sportsmanagement_match_staff_statistic',
            '#__sportsmanagement_match_staff',
            '#__sportsmanagement_match_event',
            '#__sportsmanagement_match_referee',
            '#__sportsmanagement_match_player',
            '#__sportsmanagement_match_commentary',
            '#__sportsmanagement_match_single',
        ];

        try {
            $db->transactionStart();

            foreach ($childTables as $table) {
                $resolved = $db->replacePrefix($table);

                if (!isset($availableTables[$resolved])) {
                    continue;
                }

                $query = $db->getQuery(true)
                    ->delete($db->quoteName($table))
                    ->where($db->quoteName('match_id') . ' IN (' . $in . ')');
                $db->setQuery($query)->execute();
            }

            $deleteIds = $ids;

            if (!parent::delete($deleteIds)) {
                throw new \RuntimeException((string) $this->getError());
            }

            $db->transactionCommit();
            return true;
        } catch (\Throwable $e) {
            try {
                $db->transactionRollback();
            } catch (\Throwable) {
            }

            $this->setError($e->getMessage());
            return false;
        }
    }
}
