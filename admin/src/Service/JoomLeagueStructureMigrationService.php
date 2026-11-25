<?php
/**
 * Joomla 5/6 JoomLeague structure migration service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/** Convert staged JoomLeague relations to the current season-based SportsManagement structure. */
final class JoomLeagueStructureMigrationService
{
    private const IMPORT_TABLES = [
        '#__sportsmanagement_season_team_id',
        '#__sportsmanagement_season_person_id',
        '#__sportsmanagement_season_team_person_id',
        '#__sportsmanagement_person_project_position',
    ];

    /**
     * Initialise the structure migration service with the database connection containing the imported SportsManagement records.
     * @param DatabaseInterface $db Target database connection.
     */
    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    /**
     * Prepare the configured import tables and run the season-team, staff, player and referee migrations independently. Collect per-step success, affected-row counts and diagnostic messages so a failed step does not suppress the remaining results.
     * @return array<int,array{label:string,success:bool,count:int,message:string}> Migration step results.
     */
    public function migrate(): array
    {
        $results = [];

        foreach (self::IMPORT_TABLES as $table) {
            $results[] = $this->prepareImportTable($table);
        }

        try {
            $seasonTeamCount = $this->migrateProjectTeams();
            $results[] = $this->result('Saison-Mannschaften', true, $seasonTeamCount, 'aktualisiert');
        } catch (\Throwable $exception) {
            $results[] = $this->result('Saison-Mannschaften', false, 0, $exception->getMessage());
        }

        try {
            $staffCount = $this->migrateTeamStaff();
            $results[] = $this->result('Saison-Team-Staff', true, $staffCount, 'aktualisiert');
        } catch (\Throwable $exception) {
            $results[] = $this->result('Saison-Team-Staff', false, 0, $exception->getMessage());
        }

        try {
            $playerCount = $this->migrateTeamPlayers();
            $results[] = $this->result('Saison-Team-Spieler', true, $playerCount, 'aktualisiert');
        } catch (\Throwable $exception) {
            $results[] = $this->result('Saison-Team-Spieler', false, 0, $exception->getMessage());
        }

        try {
            $refereeCount = $this->migrateProjectReferees();
            $results[] = $this->result('Saison-Schiedsrichter', true, $refereeCount, 'aktualisiert');
        } catch (\Throwable $exception) {
            $results[] = $this->result('Saison-Schiedsrichter', false, 0, $exception->getMessage());
        }

        return $results;
    }

    /**
     * Ensure the import identifier column exists, then delete previously imported rows with a nonzero import identifier. Report the affected-row count or the caught database failure.
     * @param string $table Configured import table name.
     * @return array{label:string,success:bool,count:int,message:string} Preparation result.
     */
    private function prepareImportTable(string $table): array
    {
        try {
            $this->ensureImportIdColumn($table);
            $query = $this->db->createQuery()
                ->delete($this->db->quoteName($table))
                ->where($this->db->quoteName('import_id') . ' <> 0');
            $this->db->setQuery($query);
            $this->db->execute();

            return $this->result($table, true, $this->affectedRows(), 'vorbereitet');
        } catch (\Throwable $exception) {
            return $this->result($table, false, 0, $exception->getMessage());
        }
    }

    /**
     * Inspect the target table columns when supported by the driver and add an integer import_id column with default zero if it is absent.
     * @param string $table Target table name including the Joomla prefix placeholder.
     * @return void
     */
    private function ensureImportIdColumn(string $table): void
    {
        $resolvedTable = method_exists($this->db, 'replacePrefix')
            ? $this->db->replacePrefix($table)
            : $table;

        if (method_exists($this->db, 'getTableColumns')) {
            $columns = $this->db->getTableColumns($resolvedTable, false);

            if (isset($columns['import_id'])) {
                return;
            }
        }

        $sql = 'ALTER TABLE ' . $this->db->quoteName($table)
            . ' ADD ' . $this->db->quoteName('import_id') . ' INTEGER NOT NULL DEFAULT 0';
        $this->db->setQuery($sql);
        $this->db->execute();
    }

    /**
     * Resolve imported project teams to season-team assignments, creating missing assignments. Move existing references through temporary identifiers before writing their final values to avoid collisions.
     * @return int Number of final project-team reference updates.
     */
    private function migrateProjectTeams(): int
    {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('p.id', 'project_id'),
                $this->db->quoteName('p.season_id'),
                $this->db->quoteName('pt.id', 'project_team_id'),
                $this->db->quoteName('pt.team_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $this->db->quoteName('pt.project_id') . ' = ' . $this->db->quoteName('p.id')
            )
            ->where($this->db->quoteName('p.import_id') . ' <> 0')
            ->where($this->db->quoteName('pt.import_id') . ' <> 0');
        $this->db->setQuery($query);
        $rows = $this->db->loadObjectList() ?: [];
        $mappings = [];

        foreach ($rows as $row) {
            $projectTeamId = (int) ($row->project_team_id ?? 0);
            $seasonId = (int) ($row->season_id ?? 0);
            $teamId = (int) ($row->team_id ?? 0);

            if ($projectTeamId <= 0 || $seasonId <= 0 || $teamId <= 0) {
                continue;
            }

            $seasonTeamId = $this->findOrCreateSeasonTeam($seasonId, $teamId);

            if ($seasonTeamId > 0) {
                $mappings[$projectTeamId] = $seasonTeamId;
            }
        }

        if ($mappings === []) {
            return 0;
        }

        $maxProjectTeamId = max(array_keys($mappings));
        $maxCurrentTeamId = $this->loadMax('#__sportsmanagement_project_team', 'team_id');
        $maxSeasonTeamId = $this->loadMax('#__sportsmanagement_season_team_id', 'id');
        $temporaryOffset = max($maxCurrentTeamId, $maxSeasonTeamId) + $maxProjectTeamId + 1000;

        foreach ($mappings as $projectTeamId => $seasonTeamId) {
            $this->updateById(
                '#__sportsmanagement_project_team',
                $projectTeamId,
                'team_id',
                $temporaryOffset + $projectTeamId
            );
        }

        $updated = 0;

        foreach ($mappings as $projectTeamId => $seasonTeamId) {
            $updated += $this->updateById(
                '#__sportsmanagement_project_team',
                $projectTeamId,
                'team_id',
                $seasonTeamId
            );
        }

        return $updated;
    }

    /**
     * Create season-person and season-team-person records for imported staff and replace legacy staff references in imported match data.
     * @return int Number of updated imported references.
     */
    private function migrateTeamStaff(): int
    {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('ts.id', 'team_staff_id'),
                $this->db->quoteName('ts.person_id'),
                $this->db->quoteName('p.season_id'),
                $this->db->quoteName('sst.team_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_team_staff', 'ts'))
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $this->db->quoteName('pt.id') . ' = ' . $this->db->quoteName('ts.projectteam_id')
            )
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $this->db->quoteName('p.id') . ' = ' . $this->db->quoteName('pt.project_id')
            )
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_season_team_id', 'sst')
                . ' ON ' . $this->db->quoteName('sst.id') . ' = ' . $this->db->quoteName('pt.team_id')
                . ' AND ' . $this->db->quoteName('sst.season_id') . ' = ' . $this->db->quoteName('p.season_id')
            )
            ->where($this->db->quoteName('p.import_id') . ' <> 0');
        $this->db->setQuery($query);
        $rows = $this->db->loadObjectList() ?: [];
        $updated = 0;

        foreach ($rows as $row) {
            $oldId = (int) ($row->team_staff_id ?? 0);
            $seasonId = (int) ($row->season_id ?? 0);
            $personId = (int) ($row->person_id ?? 0);
            $teamId = (int) ($row->team_id ?? 0);

            if ($oldId <= 0 || $seasonId <= 0 || $personId <= 0 || $teamId <= 0) {
                continue;
            }

            $this->findOrCreateSeasonPerson('#__sportsmanagement_season_person_id', $seasonId, $personId, $teamId, 2);
            $newId = $this->findOrCreateSeasonPerson('#__sportsmanagement_season_team_person_id', $seasonId, $personId, $teamId, 2);

            if ($newId <= 0) {
                continue;
            }

            $updated += $this->replaceImportedReference('#__sportsmanagement_match_staff', 'team_staff_id', $oldId, $newId);
            $updated += $this->replaceImportedReference('#__sportsmanagement_match_staff_statistic', 'team_staff_id', $oldId, $newId);
        }

        return $updated;
    }

    /**
     * Create season-person and season-team-person records for imported players and replace their historical identifiers in imported match and event relations.
     * @return int Number of updated imported references.
     */
    private function migrateTeamPlayers(): int
    {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('tp.id', 'team_player_id'),
                $this->db->quoteName('tp.person_id'),
                $this->db->quoteName('p.season_id'),
                $this->db->quoteName('sst.team_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_team_player', 'tp'))
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $this->db->quoteName('pt.id') . ' = ' . $this->db->quoteName('tp.projectteam_id')
            )
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $this->db->quoteName('p.id') . ' = ' . $this->db->quoteName('pt.project_id')
            )
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_season_team_id', 'sst')
                . ' ON ' . $this->db->quoteName('sst.id') . ' = ' . $this->db->quoteName('pt.team_id')
                . ' AND ' . $this->db->quoteName('sst.season_id') . ' = ' . $this->db->quoteName('p.season_id')
            )
            ->where($this->db->quoteName('p.import_id') . ' <> 0');
        $this->db->setQuery($query);
        $rows = $this->db->loadObjectList() ?: [];
        $updated = 0;

        foreach ($rows as $row) {
            $oldId = (int) ($row->team_player_id ?? 0);
            $seasonId = (int) ($row->season_id ?? 0);
            $personId = (int) ($row->person_id ?? 0);
            $teamId = (int) ($row->team_id ?? 0);

            if ($oldId <= 0 || $seasonId <= 0 || $personId <= 0 || $teamId <= 0) {
                continue;
            }

            $this->findOrCreateSeasonPerson('#__sportsmanagement_season_person_id', $seasonId, $personId, $teamId, 1);
            $newId = $this->findOrCreateSeasonPerson('#__sportsmanagement_season_team_person_id', $seasonId, $personId, $teamId, 1);

            if ($newId <= 0) {
                continue;
            }

            $updated += $this->replaceImportedReference('#__sportsmanagement_match_player', 'teamplayer_id', $oldId, $newId);
            $updated += $this->replaceImportedReference('#__sportsmanagement_match_event', 'teamplayer_id', $oldId, $newId);
            $updated += $this->replaceImportedReference('#__sportsmanagement_match_statistic', 'teamplayer_id', $oldId, $newId);
            $updated += $this->replaceImportedReference('#__sportsmanagement_match_player', 'in_for', $oldId, $newId);
        }

        return $updated;
    }

    /**
     * Remap imported match referee references using the original import identifiers, then associate imported project referees with season-person records of referee type.
     * @return int Number of updated references.
     */
    private function migrateProjectReferees(): int
    {
        $updated = 0;
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('id'),
                $this->db->quoteName('import_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project_referee'))
            ->where($this->db->quoteName('import_id') . ' <> 0')
            ->where($this->db->quoteName('id') . ' <> ' . $this->db->quoteName('import_id'));
        $this->db->setQuery($query);
        $mappings = $this->db->loadObjectList() ?: [];

        foreach ($mappings as $mapping) {
            $newId = (int) ($mapping->id ?? 0);
            $oldId = (int) ($mapping->import_id ?? 0);

            if ($newId > 0 && $oldId > 0 && $newId !== $oldId) {
                $updated += $this->replaceImportedReference(
                    '#__sportsmanagement_match_referee',
                    'project_referee_id',
                    $oldId,
                    $newId
                );
            }
        }

        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('pr.id'),
                $this->db->quoteName('pr.person_id'),
                $this->db->quoteName('p.season_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project_referee', 'pr'))
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $this->db->quoteName('p.id') . ' = ' . $this->db->quoteName('pr.project_id')
            )
            ->where($this->db->quoteName('pr.import_id') . ' <> 0');
        $this->db->setQuery($query);
        $rows = $this->db->loadObjectList() ?: [];

        foreach ($rows as $row) {
            $projectRefereeId = (int) ($row->id ?? 0);
            $seasonId = (int) ($row->season_id ?? 0);
            $personId = (int) ($row->person_id ?? 0);

            if ($projectRefereeId <= 0 || $seasonId <= 0 || $personId <= 0) {
                continue;
            }

            $seasonPersonId = $this->findOrCreateSeasonPerson(
                '#__sportsmanagement_season_person_id',
                $seasonId,
                $personId,
                null,
                3
            );

            if ($seasonPersonId > 0) {
                $updated += $this->updateById(
                    '#__sportsmanagement_project_referee',
                    $projectRefereeId,
                    'person_id',
                    $seasonPersonId
                );
            }
        }

        return $updated;
    }

    /**
     * Reuse the season-team assignment for a season and team or insert a published imported assignment and look up its identifier.
     * @param int $seasonId Season identifier.
     * @param int $teamId Team identifier.
     * @return int Season-team identifier or zero.
     */
    private function findOrCreateSeasonTeam(int $seasonId, int $teamId): int
    {
        $id = $this->findSeasonTeam($seasonId, $teamId);

        if ($id > 0) {
            return $id;
        }

        $query = $this->db->createQuery()
            ->insert($this->db->quoteName('#__sportsmanagement_season_team_id'))
            ->columns([
                $this->db->quoteName('season_id'),
                $this->db->quoteName('team_id'),
                $this->db->quoteName('import_id'),
                $this->db->quoteName('published'),
            ])
            ->values($seasonId . ', ' . $teamId . ', 1, 1');
        $this->db->setQuery($query);
        $this->db->execute();

        return $this->findSeasonTeam($seasonId, $teamId);
    }

    /**
     * Look up the first season-team assignment matching the supplied season and team.
     * @param int $seasonId Season identifier.
     * @param int $teamId Team identifier.
     * @return int Assignment identifier or zero.
     */
    private function findSeasonTeam(int $seasonId, int $teamId): int
    {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_season_team_id'))
            ->where($this->db->quoteName('season_id') . ' = ' . $seasonId)
            ->where($this->db->quoteName('team_id') . ' = ' . $teamId);
        $this->db->setQuery($query, 0, 1);

        return (int) $this->db->loadResult();
    }

    /**
     * Reuse or insert a published imported person assignment for the specified season, person, optional team and role in the selected season-person table.
     * @param string $table Target season-person table.
     * @param int $seasonId Season identifier.
     * @param int $personId Person identifier.
     * @param int|null $teamId Optional team identifier.
     * @param int $personType Person role identifier.
     * @return int Assignment identifier or zero.
     */
    private function findOrCreateSeasonPerson(
        string $table,
        int $seasonId,
        int $personId,
        ?int $teamId,
        int $personType
    ): int {
        $id = $this->findSeasonPerson($table, $seasonId, $personId, $teamId, $personType);

        if ($id > 0) {
            return $id;
        }

        $columns = [
            $this->db->quoteName('season_id'),
            $this->db->quoteName('person_id'),
        ];
        $values = [$seasonId, $personId];

        if ($teamId !== null) {
            $columns[] = $this->db->quoteName('team_id');
            $values[] = $teamId;
        }

        $columns[] = $this->db->quoteName('persontype');
        $columns[] = $this->db->quoteName('import_id');
        $columns[] = $this->db->quoteName('published');
        $values[] = $personType;
        $values[] = 1;
        $values[] = 1;

        $query = $this->db->createQuery()
            ->insert($this->db->quoteName($table))
            ->columns($columns)
            ->values(implode(', ', array_map('intval', $values)));
        $this->db->setQuery($query);
        $this->db->execute();

        return $this->findSeasonPerson($table, $seasonId, $personId, $teamId, $personType);
    }

    /**
     * Find the first person assignment matching the season, person and role, adding a team constraint when supplied.
     * @param string $table Season-person table name.
     * @param int $seasonId Season identifier.
     * @param int $personId Person identifier.
     * @param int|null $teamId Optional team identifier.
     * @param int $personType Person role identifier.
     * @return int Assignment identifier or zero.
     */
    private function findSeasonPerson(
        string $table,
        int $seasonId,
        int $personId,
        ?int $teamId,
        int $personType
    ): int {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName($table))
            ->where($this->db->quoteName('season_id') . ' = ' . $seasonId)
            ->where($this->db->quoteName('person_id') . ' = ' . $personId)
            ->where($this->db->quoteName('persontype') . ' = ' . $personType);

        if ($teamId !== null) {
            $query->where($this->db->quoteName('team_id') . ' = ' . $teamId);
        }

        $this->db->setQuery($query, 0, 1);

        return (int) $this->db->loadResult();
    }

    /**
     * Replace a legacy identifier with its migrated value only in rows marked as imported. Skip invalid or unchanged identifiers.
     * @param string $table Target relation table.
     * @param string $field Reference column.
     * @param int $oldId Legacy identifier.
     * @param int $newId Replacement identifier.
     * @return int Number of affected rows.
     */
    private function replaceImportedReference(string $table, string $field, int $oldId, int $newId): int
    {
        if ($oldId <= 0 || $newId <= 0 || $oldId === $newId) {
            return 0;
        }

        $query = $this->db->createQuery()
            ->update($this->db->quoteName($table))
            ->set($this->db->quoteName($field) . ' = ' . $newId)
            ->where($this->db->quoteName($field) . ' = ' . $oldId)
            ->where($this->db->quoteName('import_id') . ' <> 0');
        $this->db->setQuery($query);
        $this->db->execute();

        return $this->affectedRows();
    }

    /**
     * Update one integer field in the row identified by its primary key in the supplied migration table.
     * @param string $table Target table.
     * @param int $id Row identifier.
     * @param string $field Column to update.
     * @param int $value Replacement integer value.
     * @return int Number of affected rows.
     */
    private function updateById(string $table, int $id, string $field, int $value): int
    {
        $query = $this->db->createQuery()
            ->update($this->db->quoteName($table))
            ->set($this->db->quoteName($field) . ' = ' . $value)
            ->where($this->db->quoteName('id') . ' = ' . $id);
        $this->db->setQuery($query);
        $this->db->execute();

        return $this->affectedRows();
    }

    /**
     * Read the maximum numeric column value from a migration table to calculate temporary reference identifiers.
     * @param string $table Target table.
     * @param string $field Numeric column.
     * @return int Maximum value or zero.
     */
    private function loadMax(string $table, string $field): int
    {
        $query = $this->db->createQuery()
            ->select('MAX(' . $this->db->quoteName($field) . ')')
            ->from($this->db->quoteName($table));
        $this->db->setQuery($query);

        return (int) $this->db->loadResult();
    }

    /**
     * Return the affected-row count from the database driver when supported, otherwise return zero.
     * @return int Number of affected rows.
     */
    private function affectedRows(): int
    {
        return method_exists($this->db, 'getAffectedRows') ? (int) $this->db->getAffectedRows() : 0;
    }

    /**
     * Build the structured migration status entry used by the administrator import report.
     * @param string $label Migration step label.
     * @param bool $success Whether the step succeeded.
     * @param int $count Affected-row count.
     * @param string $message Status or diagnostic message.
     * @return array{label:string,success:bool,count:int,message:string} Structured step result.
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
