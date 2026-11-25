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
    /**
     * Initialise the native match detail writer with the target database connection.
     * @param DatabaseInterface $database Target database connection.
     */
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * Execute the requested cumulative XML import stages 26 through 29 for match players, staff, referees and events using resolved source-to-target relation maps. Collect an HTML report for each executed stage.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @param array<int,int> $matchMap Match identifier map.
     * @param array<int,int> $teamPlayerMap Team-player identifier map.
     * @param array<int,int> $teamStaffMap Team-staff identifier map.
     * @param array<int,int> $projectRefereeMap Project-referee identifier map.
     * @param array<int,int> $projectPositionMap Project-position identifier map.
     * @param array<int,int> $projectTeamMap Project-team identifier map.
     * @param array<int,int> $eventMap Event-type identifier map.
     * @param string $step Last import stage to execute.
     * @return array{messages:array<string,string>} Import reports.
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
     * Insert imported match-player records after resolving the required match and player references. Translate optional position and substitution references and report records with unresolved required relations.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @param array<int,int> $matchMap Match identifier map.
     * @param array<int,int> $teamPlayerMap Team-player identifier map.
     * @param array<int,int> $projectPositionMap Project-position identifier map.
     * @return string HTML import report.
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
     * Insert match-staff records with mapped match, staff and optional project-position identifiers, skipping records whose required references cannot be resolved.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @param array<int,int> $matchMap Match identifier map.
     * @param array<int,int> $teamStaffMap Team-staff identifier map.
     * @param array<int,int> $projectPositionMap Project-position identifier map.
     * @return string HTML import report.
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
     * Insert match-referee assignments using mapped match and project-referee identifiers and optional position mappings. Report unresolved required references without inserting those records.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @param array<int,int> $matchMap Match identifier map.
     * @param array<int,int> $projectRefereeMap Project-referee identifier map.
     * @param array<int,int> $projectPositionMap Project-position identifier map.
     * @return string HTML import report.
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
     * Insert match events with mapped match, project-team, event-type and optional player references. Skip events with missing required mappings or unresolved nonzero player references.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @param array<int,int> $matchMap Match identifier map.
     * @param array<int,int> $projectTeamMap Project-team identifier map.
     * @param array<int,int> $teamPlayerMap Team-player identifier map.
     * @param array<int,int> $eventMap Event-type identifier map.
     * @return string HTML import report.
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

    /**
     * Insert the prepared detail record into its target table and report the source record identifier if the database rejects the write.
     * @param string $table Target table name.
     * @param object $row Prepared detail record.
     * @param string $label Record type for error reporting.
     * @param int $oldId Source record identifier.
     * @return void
     * @throws RuntimeException If insertion fails.
     */
    private function insert(string $table, object $row, string $label, int $oldId): void
    {
        if (!$this->database->insertObject($table, $row)) {
            throw new RuntimeException('Unable to store imported ' . $label . ' ID ' . $oldId . '.', 500);
        }
    }

    /**
     * Copy source properties supported by the installed target table while excluding the source primary key.
     * @param object $source Imported detail record.
     * @param string $table Target table name.
     * @return object Schema-compatible insert record.
     */
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

    /**
     * Build an escaped HTML success message identifying the imported source record and its target match.
     * @param string $label Record type.
     * @param int $oldId Source record identifier.
     * @param int $matchId Target match identifier.
     * @return string HTML success message.
     */
    private function createdMessage(string $label, int $oldId, int $matchId): string
    {
        return '<span style="color:green">Created new ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            . ' data: </span><strong>' . htmlspecialchars((string) $oldId, ENT_QUOTES, 'UTF-8')
            . '</strong> / Match <strong>' . htmlspecialchars((string) $matchId, ENT_QUOTES, 'UTF-8')
            . '</strong><br />';
    }

    /**
     * Build an escaped HTML warning identifying a source detail record skipped because a required relation could not be resolved.
     * @param string $label Record type.
     * @param int $oldId Source record identifier.
     * @return string HTML warning message.
     */
    private function skippedMessage(string $label, int $oldId): string
    {
        return '<span style="color:red">Skipping ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            . ' ID <strong>' . htmlspecialchars((string) $oldId, ENT_QUOTES, 'UTF-8')
            . '</strong>; a required imported relation could not be resolved.</span><br />';
    }
}
