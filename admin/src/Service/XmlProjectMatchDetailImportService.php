<?php
/**
 * Joomla 5/6 native project-match detail XML import service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use RuntimeException;

/**
 * Native writer for project XML import steps 26-29.
 */
final class XmlProjectMatchDetailImportService
{
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $matchMap
     * @param array<int, int> $teamPlayerMap
     * @param array<int, int> $teamStaffMap
     * @param array<int, int> $projectRefereeMap
     * @param array<int, int> $projectPositionMap
     * @param array<int, int> $projectTeamMap
     * @param array<int, int> $eventMap
     *
     * @return array{messages:array<string,string>}
     */
    public function import(
        array $parsedData,
        array $matchMap,
        array $teamPlayerMap,
        array $teamStaffMap,
        array $projectRefereeMap,
        array $projectPositionMap,
        array $projectTeamMap,
        array $eventMap,
        string $step
    ): array {
        $messages = [];

        if (version_compare($step, '26', 'ge')) {
            $messages['Importing match-player data:'] = $this->importMatchPlayers(
                $parsedData,
                $matchMap,
                $teamPlayerMap,
                $projectPositionMap
            );
        }

        if (version_compare($step, '27', 'ge')) {
            $messages['Importing match-staff data:'] = $this->importMatchStaff(
                $parsedData,
                $matchMap,
                $teamStaffMap,
                $projectPositionMap
            );
        }

        if (version_compare($step, '28', 'ge')) {
            $messages['Importing match-referee data:'] = $this->importMatchReferees(
                $parsedData,
                $matchMap,
                $projectRefereeMap,
                $projectPositionMap
            );
        }

        if (version_compare($step, '29', 'ge')) {
            $messages['Importing match-event data:'] = $this->importMatchEvents(
                $parsedData,
                $matchMap,
                $projectTeamMap,
                $teamPlayerMap,
                $eventMap
            );
        }

        return ['messages' => $messages];
    }

    /**
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $matchMap
     * @param array<int, int> $teamPlayerMap
     * @param array<int, int> $projectPositionMap
     */
    private function importMatchPlayers(
        array $parsedData,
        array $matchMap,
        array $teamPlayerMap,
        array $projectPositionMap
    ): string {
        $message = '';

        foreach (array_values((array) ($parsedData['matchplayer'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = max(0, (int) ($source->id ?? 0));
            $oldMatchId = max(0, (int) ($source->match_id ?? 0));
            $oldTeamPlayerId = max(0, (int) ($source->teamplayer_id ?? 0));
            $matchId = (int) ($matchMap[$oldMatchId] ?? 0);
            $teamPlayerId = (int) ($teamPlayerMap[$oldTeamPlayerId] ?? 0);

            if ($oldMatchId <= 0 || $matchId <= 0 || $oldTeamPlayerId <= 0 || $teamPlayerId <= 0) {
                $message .= $this->skippedMessage('match-player', $oldId);
                continue;
            }

            $row = $this->filterSourceFields($source, '#__sportsmanagement_match_player');
            $row->match_id = $matchId;
            $row->teamplayer_id = $teamPlayerId;

            $oldPositionId = max(0, (int) ($source->project_position_id ?? 0));
            $row->project_position_id = $oldPositionId > 0
                ? (int) ($projectPositionMap[$oldPositionId] ?? 0)
                : 0;

            $oldInForId = max(0, (int) ($source->in_for ?? 0));
            $row->in_for = $oldInForId > 0
                ? (($teamPlayerMap[$oldInForId] ?? 0) > 0 ? (int) $teamPlayerMap[$oldInForId] : null)
                : null;

            $this->insert('#__sportsmanagement_match_player', $row, 'match-player', $oldId);
            $message .= $this->createdMessage('match-player', $oldId, $matchId);
        }

        return $message;
    }

    /**
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $matchMap
     * @param array<int, int> $teamStaffMap
     * @param array<int, int> $projectPositionMap
     */
    private function importMatchStaff(
        array $parsedData,
        array $matchMap,
        array $teamStaffMap,
        array $projectPositionMap
    ): string {
        $message = '';

        foreach (array_values((array) ($parsedData['matchstaff'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = max(0, (int) ($source->id ?? 0));
            $oldMatchId = max(0, (int) ($source->match_id ?? 0));
            $oldTeamStaffId = max(0, (int) ($source->team_staff_id ?? 0));
            $matchId = (int) ($matchMap[$oldMatchId] ?? 0);
            $teamStaffId = (int) ($teamStaffMap[$oldTeamStaffId] ?? 0);

            if ($oldMatchId <= 0 || $matchId <= 0 || $oldTeamStaffId <= 0 || $teamStaffId <= 0) {
                $message .= $this->skippedMessage('match-staff', $oldId);
                continue;
            }

            $row = $this->filterSourceFields($source, '#__sportsmanagement_match_staff');
            $row->match_id = $matchId;
            $row->team_staff_id = $teamStaffId;

            $oldPositionId = max(0, (int) ($source->project_position_id ?? 0));
            $row->project_position_id = $oldPositionId > 0
                ? (int) ($projectPositionMap[$oldPositionId] ?? 0)
                : 0;

            $this->insert('#__sportsmanagement_match_staff', $row, 'match-staff', $oldId);
            $message .= $this->createdMessage('match-staff', $oldId, $matchId);
        }

        return $message;
    }

    /**
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $matchMap
     * @param array<int, int> $projectRefereeMap
     * @param array<int, int> $projectPositionMap
     */
    private function importMatchReferees(
        array $parsedData,
        array $matchMap,
        array $projectRefereeMap,
        array $projectPositionMap
    ): string {
        $message = '';

        foreach (array_values((array) ($parsedData['matchreferee'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = max(0, (int) ($source->id ?? 0));
            $oldMatchId = max(0, (int) ($source->match_id ?? 0));
            $oldRefereeId = max(0, (int) ($source->project_referee_id ?? 0));
            $matchId = (int) ($matchMap[$oldMatchId] ?? 0);
            $refereeId = (int) ($projectRefereeMap[$oldRefereeId] ?? 0);

            if ($oldMatchId <= 0 || $matchId <= 0 || $oldRefereeId <= 0 || $refereeId <= 0) {
                $message .= $this->skippedMessage('match-referee', $oldId);
                continue;
            }

            $row = $this->filterSourceFields($source, '#__sportsmanagement_match_referee');
            $row->match_id = $matchId;
            $row->project_referee_id = $refereeId;

            $oldPositionId = max(0, (int) ($source->project_position_id ?? 0));
            $row->project_position_id = $oldPositionId > 0
                ? (int) ($projectPositionMap[$oldPositionId] ?? 0)
                : 0;

            $this->insert('#__sportsmanagement_match_referee', $row, 'match-referee', $oldId);
            $message .= $this->createdMessage('match-referee', $oldId, $matchId);
        }

        return $message;
    }

    /**
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $matchMap
     * @param array<int, int> $projectTeamMap
     * @param array<int, int> $teamPlayerMap
     * @param array<int, int> $eventMap
     */
    private function importMatchEvents(
        array $parsedData,
        array $matchMap,
        array $projectTeamMap,
        array $teamPlayerMap,
        array $eventMap
    ): string {
        $message = '';

        foreach (array_values((array) ($parsedData['matchevent'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = max(0, (int) ($source->id ?? 0));
            $oldMatchId = max(0, (int) ($source->match_id ?? 0));
            $oldProjectTeamId = max(0, (int) ($source->projectteam_id ?? 0));
            $oldTeamPlayerId = max(0, (int) ($source->teamplayer_id ?? 0));
            $oldTeamPlayerId2 = max(0, (int) ($source->teamplayer_id2 ?? 0));
            $oldEventId = max(0, (int) ($source->event_type_id ?? 0));

            $matchId = (int) ($matchMap[$oldMatchId] ?? 0);
            $projectTeamId = (int) ($projectTeamMap[$oldProjectTeamId] ?? 0);
            $teamPlayerId = $oldTeamPlayerId > 0 ? (int) ($teamPlayerMap[$oldTeamPlayerId] ?? 0) : 0;
            $teamPlayerId2 = $oldTeamPlayerId2 > 0 ? (int) ($teamPlayerMap[$oldTeamPlayerId2] ?? 0) : 0;
            $eventId = (int) ($eventMap[$oldEventId] ?? 0);

            if ($oldMatchId <= 0 || $matchId <= 0
                || $oldProjectTeamId <= 0 || $projectTeamId <= 0
                || ($oldTeamPlayerId > 0 && $teamPlayerId <= 0)
                || ($oldTeamPlayerId2 > 0 && $teamPlayerId2 <= 0)
                || $oldEventId <= 0 || $eventId <= 0
            ) {
                $message .= $this->skippedMessage('match-event', $oldId);
                continue;
            }

            $row = $this->filterSourceFields($source, '#__sportsmanagement_match_event');
            $row->match_id = $matchId;
            $row->projectteam_id = $projectTeamId;
            $row->teamplayer_id = $teamPlayerId;
            $row->teamplayer_id2 = $teamPlayerId2;
            $row->event_type_id = $eventId;

            $this->insert('#__sportsmanagement_match_event', $row, 'match-event', $oldId);
            $message .= $this->createdMessage('match-event', $oldId, $matchId);
        }

        return $message;
    }

    private function insert(string $table, object $row, string $label, int $oldId): void
    {
        if (!$this->database->insertObject($table, $row)) {
            throw new RuntimeException('Unable to store imported ' . $label . ' ID ' . $oldId . '.', 500);
        }
    }

    private function filterSourceFields(object $source, string $table): object
    {
        $columns = $this->database->getTableColumns($table);
        $row = new \stdClass();

        foreach ($source as $field => $value) {
            if ($field === 'id') {
                continue;
            }

            if (array_key_exists((string) $field, $columns)) {
                $row->{$field} = $value;
            }
        }

        return $row;
    }

    private function createdMessage(string $label, int $oldId, int $matchId): string
    {
        return '<span style="color:green">Created new ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            . ' data: </span><strong>' . htmlspecialchars((string) $oldId, ENT_QUOTES, 'UTF-8')
            . '</strong> / Match <strong>' . htmlspecialchars((string) $matchId, ENT_QUOTES, 'UTF-8')
            . '</strong><br />';
    }

    private function skippedMessage(string $label, int $oldId): string
    {
        return '<span style="color:red">Skipping ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            . ' ID <strong>' . htmlspecialchars((string) $oldId, ENT_QUOTES, 'UTF-8')
            . '</strong>; a required imported relation could not be resolved.</span><br />';
    }
}
