<?php
/**
 * Joomla 5/6 native project XML import finalizer.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use RuntimeException;

/**
 * Converts imported staging members to the final seasonal data structure.
 */
final class XmlProjectFinalizerService
{
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /** @return array{messages:array<string,string>} */
    public function finalize(int $projectId, int $seasonId): array
    {
        if ($projectId <= 0 || $seasonId <= 0) {
            throw new RuntimeException('Missing project/season for XML import finalization.', 400);
        }

        $message = '';
        $matchIds = $this->loadProjectMatchIds($projectId);
        $message .= $this->finalizeReferees($projectId, $seasonId);
        $projectTeams = $this->loadProjectTeams($projectId);
        $projectTeamIds = [];

        foreach ($projectTeams as $projectTeam) {
            $projectTeamId = (int) ($projectTeam->id ?? 0);
            $seasonTeamId = (int) ($projectTeam->team_id ?? 0);

            if ($projectTeamId <= 0 || $seasonTeamId <= 0) {
                continue;
            }

            $teamId = $this->resolveTeamId($seasonTeamId, $seasonId);

            if ($teamId <= 0) {
                $message .= $this->skippedMessage('project-team finalization', $projectTeamId);
                continue;
            }

            $projectTeamIds[] = $projectTeamId;
            $message .= $this->finalizeMembers(
                '#__sportsmanagement_team_player',
                $projectTeamId,
                $projectId,
                $seasonId,
                $teamId,
                1,
                $matchIds
            );
            $message .= $this->finalizeMembers(
                '#__sportsmanagement_team_staff',
                $projectTeamId,
                $projectId,
                $seasonId,
                $teamId,
                2,
                $matchIds
            );
        }

        $this->deleteProjectStagingRows('#__sportsmanagement_team_player', $projectTeamIds);
        $this->deleteProjectStagingRows('#__sportsmanagement_team_staff', $projectTeamIds);
        $this->updateRoundDates($projectId);

        return ['messages' => ['Finalizing project member data:' => $message]];
    }

    /** @return list<int> */
    private function loadProjectMatchIds(int $projectId): array
    {
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('m.id'))
            ->from($this->database->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $this->database->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $this->database->quoteName('r.id') . ' = ' . $this->database->quoteName('m.round_id')
            )
            ->where($this->database->quoteName('r.project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $this->database->setQuery($query);

        return array_values(array_map('intval', $this->database->loadColumn() ?: []));
    }

    /** @return list<object> */
    private function loadProjectTeams(int $projectId): array
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('team_id'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_project_team'))
            ->where($this->database->quoteName('project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $this->database->setQuery($query);

        return $this->database->loadObjectList() ?: [];
    }

    private function finalizeReferees(int $projectId, int $seasonId): string
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('person_id'),
                $this->database->quoteName('picture'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_project_referee'))
            ->where($this->database->quoteName('project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $this->database->setQuery($query);
        $message = '';

        foreach ($this->database->loadObjectList() ?: [] as $referee) {
            $personId = (int) ($referee->person_id ?? 0);

            if ($personId <= 0) {
                continue;
            }

            $seasonPersonId = $this->resolveSeasonPerson(
                $personId,
                $seasonId,
                3,
                (string) ($referee->picture ?? ''),
                0
            );

            $row = (object) [
                'id' => (int) $referee->id,
                'person_id' => $seasonPersonId,
            ];

            if (!$this->database->updateObject('#__sportsmanagement_project_referee', $row, 'id')) {
                throw new RuntimeException('Unable to finalize imported project referee.', 500);
            }

            $message .= $this->createdMessage('referee seasonal assignment', $seasonPersonId);
        }

        return $message;
    }

    private function resolveTeamId(int $seasonTeamId, int $seasonId): int
    {
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('team_id'))
            ->from($this->database->quoteName('#__sportsmanagement_season_team_id'))
            ->where($this->database->quoteName('id') . ' = :seasonTeamId')
            ->where($this->database->quoteName('season_id') . ' = :seasonId')
            ->bind(':seasonTeamId', $seasonTeamId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);

        return (int) ($this->database->loadResult() ?? 0);
    }

    private function finalizeMembers(
        string $stagingTable,
        int $projectTeamId,
        int $projectId,
        int $seasonId,
        int $teamId,
        int $personType,
        array $matchIds
    ): string {
        $query = $this->database->createQuery()
            ->select('s.*')
            ->select($this->database->quoteName('p.position_id'))
            ->from($this->database->quoteName($stagingTable, 's'))
            ->join(
                'INNER',
                $this->database->quoteName('#__sportsmanagement_person', 'p')
                . ' ON ' . $this->database->quoteName('p.id') . ' = ' . $this->database->quoteName('s.person_id')
            )
            ->where($this->database->quoteName('s.projectteam_id') . ' = :projectTeamId')
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER);
        $this->database->setQuery($query);
        $message = '';

        foreach ($this->database->loadObjectList() ?: [] as $member) {
            $stagingId = (int) ($member->id ?? 0);
            $personId = (int) ($member->person_id ?? 0);
            $positionId = max(0, (int) ($member->position_id ?? 0));
            $projectPositionId = max(0, (int) ($member->project_position_id ?? 0));

            if ($stagingId <= 0 || $personId <= 0) {
                continue;
            }

            $this->resolveSeasonPerson(
                $personId,
                $seasonId,
                $personType,
                (string) ($member->picture ?? ''),
                $positionId
            );
            $seasonTeamPersonId = $this->resolveSeasonTeamPerson(
                $member,
                $personId,
                $seasonId,
                $teamId,
                $personType,
                $positionId,
                $projectPositionId
            );
            $this->ensureProjectPosition($personId, $projectId, $projectPositionId, $personType);

            if ($personType === 1) {
                $this->updateMemberReferences(
                    '#__sportsmanagement_match_player',
                    'teamplayer_id',
                    $stagingId,
                    $seasonTeamPersonId,
                    $matchIds
                );
                $this->updateMemberReferences(
                    '#__sportsmanagement_match_event',
                    'teamplayer_id',
                    $stagingId,
                    $seasonTeamPersonId,
                    $matchIds
                );
                $this->updateMemberReferences(
                    '#__sportsmanagement_match_event',
                    'teamplayer_id2',
                    $stagingId,
                    $seasonTeamPersonId,
                    $matchIds
                );
                $this->updateMemberReferences(
                    '#__sportsmanagement_match_player',
                    'in_for',
                    $stagingId,
                    $seasonTeamPersonId,
                    $matchIds
                );
            } else {
                $this->updateMemberReferences(
                    '#__sportsmanagement_match_staff',
                    'team_staff_id',
                    $stagingId,
                    $seasonTeamPersonId,
                    $matchIds
                );
            }

            $message .= $this->createdMessage(
                $personType === 1 ? 'player seasonal assignment' : 'staff seasonal assignment',
                $seasonTeamPersonId
            );
        }

        return $message;
    }

    private function resolveSeasonPerson(
        int $personId,
        int $seasonId,
        int $personType,
        string $picture,
        int $positionId
    ): int {
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('id'))
            ->from($this->database->quoteName('#__sportsmanagement_season_person_id'))
            ->where($this->database->quoteName('person_id') . ' = :personId')
            ->where($this->database->quoteName('season_id') . ' = :seasonId')
            ->where($this->database->quoteName('persontype') . ' IN (0, ' . $personType . ')')
            ->bind(':personId', $personId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);
        $existing = (int) ($this->database->loadResult() ?? 0);

        if ($existing > 0) {
            return $existing;
        }

        $row = $this->filterTableFields(
            (object) [
                'person_id' => $personId,
                'season_id' => $seasonId,
                'persontype' => $personType,
                'picture' => $picture,
                'position_id' => $positionId,
            ],
            '#__sportsmanagement_season_person_id'
        );

        if (!$this->database->insertObject('#__sportsmanagement_season_person_id', $row)) {
            throw new RuntimeException('Unable to store imported seasonal person assignment.', 500);
        }

        return (int) $this->database->insertid();
    }

    private function resolveSeasonTeamPerson(
        object $member,
        int $personId,
        int $seasonId,
        int $teamId,
        int $personType,
        int $positionId,
        int $projectPositionId
    ): int {
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('id'))
            ->from($this->database->quoteName('#__sportsmanagement_season_team_person_id'))
            ->where($this->database->quoteName('person_id') . ' = :personId')
            ->where($this->database->quoteName('season_id') . ' = :seasonId')
            ->where($this->database->quoteName('team_id') . ' = :teamId')
            ->where($this->database->quoteName('persontype') . ' IN (0, ' . $personType . ')')
            ->bind(':personId', $personId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':teamId', $teamId, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);
        $existing = (int) ($this->database->loadResult() ?? 0);

        if ($existing > 0) {
            return $existing;
        }

        $source = (object) [
            'person_id' => $personId,
            'season_id' => $seasonId,
            'team_id' => $teamId,
            'persontype' => $personType,
            'published' => 1,
            'picture' => (string) ($member->picture ?? ''),
            'project_position_id' => $projectPositionId,
            'position_id' => $positionId,
        ];

        if ($personType === 1) {
            $source->jerseynumber = $member->jerseynumber ?? null;
        }

        $row = $this->filterTableFields($source, '#__sportsmanagement_season_team_person_id');

        if (!$this->database->insertObject('#__sportsmanagement_season_team_person_id', $row)) {
            throw new RuntimeException('Unable to store imported seasonal team/person assignment.', 500);
        }

        return (int) $this->database->insertid();
    }

    private function ensureProjectPosition(
        int $personId,
        int $projectId,
        int $projectPositionId,
        int $personType
    ): void {
        if ($projectPositionId <= 0) {
            return;
        }

        $query = $this->database->createQuery()
            ->select($this->database->quoteName('person_id'))
            ->from($this->database->quoteName('#__sportsmanagement_person_project_position'))
            ->where($this->database->quoteName('person_id') . ' = :personId')
            ->where($this->database->quoteName('project_id') . ' = :projectId')
            ->where($this->database->quoteName('project_position_id') . ' = :projectPositionId')
            ->where($this->database->quoteName('persontype') . ' IN (0, ' . $personType . ')')
            ->bind(':personId', $personId, ParameterType::INTEGER)
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':projectPositionId', $projectPositionId, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);

        if ($this->database->loadResult() !== null) {
            return;
        }

        $row = $this->filterTableFields(
            (object) [
                'person_id' => $personId,
                'project_id' => $projectId,
                'project_position_id' => $projectPositionId,
                'persontype' => $personType,
            ],
            '#__sportsmanagement_person_project_position'
        );

        if (!$this->database->insertObject('#__sportsmanagement_person_project_position', $row)) {
            throw new RuntimeException('Unable to store imported person/project-position assignment.', 500);
        }
    }

    /** @param list<int> $matchIds */
    private function updateMemberReferences(
        string $table,
        string $field,
        int $oldId,
        int $newId,
        array $matchIds
    ): void {
        if ($matchIds === []) {
            return;
        }

        $matchList = implode(',', array_map('intval', $matchIds));
        $query = $this->database->createQuery()
            ->update($this->database->quoteName($table))
            ->set($this->database->quoteName($field) . ' = :newId')
            ->where($this->database->quoteName($field) . ' = :oldId')
            ->where($this->database->quoteName('match_id') . ' IN (' . $matchList . ')')
            ->bind(':newId', $newId, ParameterType::INTEGER)
            ->bind(':oldId', $oldId, ParameterType::INTEGER);
        $this->database->setQuery($query);
        $this->database->execute();
    }

    /** @param list<int> $projectTeamIds */
    private function deleteProjectStagingRows(string $table, array $projectTeamIds): void
    {
        if ($projectTeamIds === []) {
            return;
        }

        $projectTeamList = implode(',', array_map('intval', $projectTeamIds));
        $query = $this->database->createQuery()
            ->delete($this->database->quoteName($table))
            ->where($this->database->quoteName('projectteam_id') . ' IN (' . $projectTeamList . ')');
        $this->database->setQuery($query);
        $this->database->execute();
    }

    private function updateRoundDates(int $projectId): void
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('roundcode'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_round'))
            ->where($this->database->quoteName('project_id') . ' = :projectId')
            ->order($this->database->quoteName('roundcode') . ' DESC')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $this->database->setQuery($query);
        $rounds = $this->database->loadObjectList() ?: [];
        $currentRound = 0;
        $lastRound = 0;

        foreach ($rounds as $round) {
            $roundId = (int) ($round->id ?? 0);

            if ($roundId <= 0) {
                continue;
            }

            $lastRound = $roundId;
            $query = $this->database->createQuery()
                ->select([
                    'MIN(' . $this->database->quoteName('match_date') . ') AS ' . $this->database->quoteName('first_match'),
                    'MAX(' . $this->database->quoteName('match_date') . ') AS ' . $this->database->quoteName('last_match'),
                ])
                ->from($this->database->quoteName('#__sportsmanagement_match'))
                ->where($this->database->quoteName('round_id') . ' = :roundId')
                ->bind(':roundId', $roundId, ParameterType::INTEGER);
            $this->database->setQuery($query, 0, 1);
            $dates = $this->database->loadObject();
            $first = trim((string) ($dates->first_match ?? ''));
            $last = trim((string) ($dates->last_match ?? ''));

            if ($first !== '' && $last !== '') {
                $update = (object) [
                    'id' => $roundId,
                    'round_date_first' => substr($first, 0, 10),
                    'round_date_last' => substr($last, 0, 10),
                ];

                if (!$this->database->updateObject('#__sportsmanagement_round', $update, 'id')) {
                    throw new RuntimeException('Unable to update imported round dates.', 500);
                }

                $query = $this->database->createQuery()
                    ->select($this->database->quoteName('id'))
                    ->from($this->database->quoteName('#__sportsmanagement_match'))
                    ->where($this->database->quoteName('team1_result') . ' IS NULL')
                    ->where($this->database->quoteName('round_id') . ' = :roundId')
                    ->bind(':roundId', $roundId, ParameterType::INTEGER);
                $this->database->setQuery($query, 0, 1);

                if ($this->database->loadResult() !== null) {
                    $currentRound = $roundId;
                }
            }
        }

        if ($currentRound <= 0) {
            $currentRound = $lastRound;
        }

        $row = (object) [
            'id' => $projectId,
            'current_round' => $currentRound,
        ];

        if (!$this->database->updateObject('#__sportsmanagement_project', $row, 'id')) {
            throw new RuntimeException('Unable to update imported project current round.', 500);
        }
    }

    private function filterTableFields(object $source, string $table): object
    {
        $columns = $this->database->getTableColumns($table);
        $row = new \stdClass();

        foreach ($source as $field => $value) {
            if (array_key_exists((string) $field, $columns)) {
                $row->{$field} = $value;
            }
        }

        return $row;
    }

    private function createdMessage(string $label, int $id): string
    {
        return '<span style="color:green">Prepared ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            . ': </span><strong>' . htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8') . '</strong><br />';
    }

    private function skippedMessage(string $label, int $id): string
    {
        return '<span style="color:red">Skipping ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            . ' for ID <strong>' . htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8')
            . '</strong>; season/team mapping is unavailable.</span><br />';
    }
}
