<?php
/**
 * Joomla 5/6 JoomLeague import cleanup service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\Database\DatabaseInterface;

/** Finish the data-only cleanup after a JoomLeague import. */
final class JoomLeagueCleanupService
{
    /**
     * Create the administrator cleanup service for migrated JoomLeague data.
     * Keeps the Joomla database connection immutable so all cleanup steps share the same transaction and query API.
     *
     * @param DatabaseInterface $db Joomla database driver.
     * @return void
     */
    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    /**
     * Perform the scheduled post-import cleanup operations for migrated records.
     * Refreshes missing project and match timestamps and normalises match-player positions, returning per-operation status instead of silently ignoring failures.
     *
     * @return array<int,array{label:string,success:bool,count:int,message:string}> Cleanup summaries.
     */
    public function cleanup(): array
    {
        return [
            $this->updateProjectTimestamps(),
            $this->updateMatchTimestamps(),
            $this->normaliseMatchPlayerPositions(),
        ];
    }

    /**
     * Populate missing project timestamp values from their stored modified dates.
     * Queries projects with a zero timestamp, parses their dates safely and reports the number of successfully updated records.
     *
     * @return array{label:string,success:bool,count:int,message:string} Project cleanup result.
     */
    private function updateProjectTimestamps(): array
    {
        try {
            $query = $this->db->createQuery()
                ->select([
                    $this->db->quoteName('id'),
                    $this->db->quoteName('modified'),
                ])
                ->from($this->db->quoteName('#__sportsmanagement_project'))
                ->where($this->db->quoteName('modified_timestamp') . ' = 0');
            $this->db->setQuery($query);
            $rows = $this->db->loadObjectList() ?: [];
            $updated = 0;

            foreach ($rows as $row) {
                $timestamp = $this->toTimestamp((string) ($row->modified ?? ''));

                if ($timestamp === null) {
                    continue;
                }

                $updated += $this->updateById(
                    '#__sportsmanagement_project',
                    (int) ($row->id ?? 0),
                    'modified_timestamp',
                    $timestamp
                );
            }

            return $this->result('Projekt-Zeitstempel', true, $updated, 'aktualisiert');
        } catch (\Throwable $exception) {
            return $this->result('Projekt-Zeitstempel', false, 0, $exception->getMessage());
        }
    }

    /**
     * Populate missing numeric match timestamps from match date fields.
     * Converts valid match dates through Joomla's date API and leaves missing or unparsable values untouched.
     *
     * @return array{label:string,success:bool,count:int,message:string} Match cleanup result.
     */
    private function updateMatchTimestamps(): array
    {
        try {
            $query = $this->db->createQuery()
                ->select([
                    $this->db->quoteName('id'),
                    $this->db->quoteName('match_date'),
                ])
                ->from($this->db->quoteName('#__sportsmanagement_match'))
                ->where($this->db->quoteName('match_timestamp') . ' = 0');
            $this->db->setQuery($query);
            $rows = $this->db->loadObjectList() ?: [];
            $updated = 0;

            foreach ($rows as $row) {
                $timestamp = $this->toTimestamp((string) ($row->match_date ?? ''));

                if ($timestamp === null) {
                    continue;
                }

                $updated += $this->updateById(
                    '#__sportsmanagement_match',
                    (int) ($row->id ?? 0),
                    'match_timestamp',
                    $timestamp
                );
            }

            return $this->result('Spiel-Zeitstempel', true, $updated, 'aktualisiert');
        } catch (\Throwable $exception) {
            return $this->result('Spiel-Zeitstempel', false, 0, $exception->getMessage());
        }
    }

    /**
     * Correct project-position references in imported match-player records.
     * Loads defined project positions and synchronises legacy position values so match-lineup records use the expected identifier scheme.
     *
     * @return array{label:string,success:bool,count:int,message:string} Position cleanup result.
     */
    private function normaliseMatchPlayerPositions(): array
    {
        try {
            $query = $this->db->createQuery()
                ->select([
                    $this->db->quoteName('pp.id', 'project_position_id'),
                    $this->db->quoteName('pp.position_id'),
                    $this->db->quoteName('pp.project_id'),
                ])
                ->from($this->db->quoteName('#__sportsmanagement_project_position', 'pp'))
                ->where($this->db->quoteName('pp.position_id') . ' > 0');
            $this->db->setQuery($query);
            $positions = $this->db->loadObjectList() ?: [];
            $updated = 0;

            foreach ($positions as $position) {
                $projectPositionId = (int) ($position->project_position_id ?? 0);
                $positionId = (int) ($position->position_id ?? 0);
                $projectId = (int) ($position->project_id ?? 0);

                if ($projectPositionId <= 0 || $positionId <= 0 || $projectId <= 0) {
                    continue;
                }

                $matches = $this->db->createQuery()
                    ->select($this->db->quoteName('m.id'))
                    ->from($this->db->quoteName('#__sportsmanagement_match', 'm'))
                    ->join(
                        'INNER',
                        $this->db->quoteName('#__sportsmanagement_round', 'r')
                        . ' ON ' . $this->db->quoteName('r.id') . ' = ' . $this->db->quoteName('m.round_id')
                    )
                    ->where($this->db->quoteName('r.project_id') . ' = ' . $projectId);

                $update = $this->db->createQuery()
                    ->update($this->db->quoteName('#__sportsmanagement_match_player'))
                    ->set($this->db->quoteName('project_position_id') . ' = ' . $positionId)
                    ->where($this->db->quoteName('project_position_id') . ' = ' . $projectPositionId)
                    ->where($this->db->quoteName('match_id') . ' IN (' . $matches . ')');
                $this->db->setQuery($update);
                $this->db->execute();
                $updated += $this->affectedRows();
            }

            return $this->result('Spieler-Positionen', true, $updated, 'normalisiert');
        } catch (\Throwable $exception) {
            return $this->result('Spieler-Positionen', false, 0, $exception->getMessage());
        }
    }

    /**
     * Convert a database date value into a UTC Unix timestamp.
     * Rejects empty and Joomla null-date values and returns null when date parsing fails instead of writing invalid timestamps.
     *
     * @param string $value Stored SQL date or datetime.
     * @return int|null Valid UTC timestamp or null.
     */
    private function toTimestamp(string $value): ?int
    {
        $value = trim($value);
        $nullDate = method_exists($this->db, 'getNullDate') ? (string) $this->db->getNullDate() : '0000-00-00 00:00:00';

        if ($value === '' || $value === $nullDate || str_starts_with($value, '0000-00-00')) {
            return null;
        }

        try {
            return (new Date($value, 'UTC'))->toUnix();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Update one integer field of a known SportsManagement database row.
     * Skips invalid identifiers and executes a Joomla query by table, field and record ID, returning the affected-row count.
     *
     * @param string $table Table to update.
     * @param int $id Existing record identifier.
     * @param string $field Integer column to update.
     * @param int $value New numeric value.
     * @return int Number of affected database rows.
     */
    private function updateById(string $table, int $id, string $field, int $value): int
    {
        if ($id <= 0) {
            return 0;
        }

        $query = $this->db->createQuery()
            ->update($this->db->quoteName($table))
            ->set($this->db->quoteName($field) . ' = ' . $value)
            ->where($this->db->quoteName('id') . ' = ' . $id);
        $this->db->setQuery($query);
        $this->db->execute();

        return $this->affectedRows();
    }

    /**
     * Read the affected-row count from the active Joomla database driver.
     * Returns zero if the database implementation does not expose the optional counting method.
     *
     * @return int Affected row count or zero.
     */
    private function affectedRows(): int
    {
        return method_exists($this->db, 'getAffectedRows') ? (int) $this->db->getAffectedRows() : 0;
    }

    /**
     * Build the structured outcome reported by each cleanup operation.
     * Keeps the label, success flag, affected record count and user-facing message in a consistent format for administrator feedback.
     *
     * @param string $label Cleanup step label.
     * @param bool $success Whether the step completed successfully.
     * @param int $count Number of changed records.
     * @param string $message Descriptive result message.
     * @return array{label:string,success:bool,count:int,message:string} Cleanup summary.
     */
    private function result(string $label, bool $success, int $count, string $message): array
    {
        return [
            'label' => $label,
            'success' => $success,
            'count' => $count,
            'message' => $message,
        ];
    }
}
