<?php
/**
 * Joomla 5/6 JoomLeague staging import service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/**
 * Copy the historical JoomLeague source tables into SportsManagement staging rows.
 *
 * The legacy importer used an INSERT ... SELECT statement through the Joomla
 * database connection. That only works when both products share one database.
 * This service reads through the dedicated JoomLeague connection and writes
 * through the SportsManagement connection so separate databases remain valid.
 */
final class JoomLeagueStagingImportService
{
    private const CHUNK_SIZE = 500;

    private const TABLES = [
        'associations',
        'club',
        'division',
        'league',
        'match',
        'match_event',
        'match_player',
        'match_referee',
        'match_staff',
        'match_staff_statistic',
        'match_statistic',
        'match_commentary',
        'person',
        'playground',
        'position',
        'eventtype',
        'position_eventtype',
        'position_statistic',
        'project',
        'project_position',
        'project_referee',
        'project_team',
        'rosterposition',
        'round',
        'season',
        'statistic',
        'team',
        'team_trainingdata',
        'team_player',
        'team_staff',
        'template_config',
        'user_extra_fields',
        'user_extra_fields_values',
        'prediction_admin',
        'prediction_game',
        'prediction_groups',
        'prediction_member',
        'prediction_project',
        'prediction_result',
        'prediction_result_round',
        'prediction_template',
    ];

    private const EXCLUDED_FIELDS = [
        'import',
        'ordering',
        'checked_out',
        'checked_out_time',
        'modified',
        'modified_by',
        'out',
        'double',
        'founded',
        'dissolved',
    ];

    public function __construct(
        private readonly DatabaseInterface $target,
        private readonly DatabaseInterface $source
    ) {
    }

    /**
     * @return array<int,array{table:string,success:bool,copied:int,message:string}>
     */
    public function stage(int $sportsTypeId): array
    {
        $results = [];
        $sourceTables = array_fill_keys((array) $this->source->getTableList(), true);
        $sourcePrefix = $this->source->getPrefix();
        $targetPrefix = $this->target->getPrefix();

        foreach (self::TABLES as $suffix) {
            $sourceTable = $sourcePrefix . 'joomleague_' . $suffix;
            $targetTable = $targetPrefix . 'sportsmanagement_' . $suffix;

            try {
                $targetColumns = $this->ensureColumn($targetTable, 'import_id', 'INTEGER NOT NULL DEFAULT 0');
                $this->deletePreviousImports($targetTable);
            } catch (\Throwable $exception) {
                $results[] = $this->result($suffix, false, 0, $exception->getMessage());
                continue;
            }

            if (!isset($sourceTables[$sourceTable])) {
                $results[] = $this->result($suffix, true, 0, 'Quelltabelle nicht vorhanden - übersprungen');
                continue;
            }

            try {
                $sourceColumns = $this->source->getTableColumns($sourceTable, false);
                $mapping = $this->buildFieldMapping($sourceColumns, $targetColumns);

                if ($mapping === []) {
                    $results[] = $this->result($suffix, false, 0, 'Keine kompatiblen Felder gefunden');
                    continue;
                }

                $copied = $this->copyRows($sourceTable, $targetTable, $mapping);

                if ($suffix === 'position' && $sportsTypeId > 0) {
                    $this->updatePositionSportsType($targetTable, $sportsTypeId);
                }

                $results[] = $this->result($suffix, true, $copied, 'importiert');
            } catch (\Throwable $exception) {
                $results[] = $this->result($suffix, false, 0, $exception->getMessage());
            }
        }

        $predictionRoundTable = $targetPrefix . 'sportsmanagement_prediction_result_round';

        try {
            $this->ensureColumn($predictionRoundTable, 'division_id', 'INTEGER NOT NULL DEFAULT 0');
        } catch (\Throwable $exception) {
            $results[] = $this->result('prediction_result_round.division_id', false, 0, $exception->getMessage());
        }

        return $results;
    }

    /**
     * @param array<string,mixed> $sourceColumns
     * @param array<string,mixed> $targetColumns
     * @return array<string,string> target field => source field
     */
    private function buildFieldMapping(array $sourceColumns, array $targetColumns): array
    {
        $mapping = [];

        foreach (array_keys($sourceColumns) as $field) {
            $field = (string) $field;

            if (in_array($field, self::EXCLUDED_FIELDS, true)) {
                continue;
            }

            if ($field === 'id') {
                if (array_key_exists('id', $targetColumns) && array_key_exists('import_id', $targetColumns)) {
                    $mapping['import_id'] = 'id';
                }

                continue;
            }

            if (array_key_exists($field, $targetColumns)) {
                $mapping[$field] = $field;
            }
        }

        return $mapping;
    }

    /**
     * @param array<string,string> $mapping target field => source field
     */
    private function copyRows(string $sourceTable, string $targetTable, array $mapping): int
    {
        $sourceFields = array_values($mapping);
        $targetFields = array_keys($mapping);
        $select = $this->source->createQuery()
            ->select(array_map([$this->source, 'quoteName'], $sourceFields))
            ->from($this->source->quoteName($sourceTable));
        $offset = 0;
        $copied = 0;

        while (true) {
            $this->source->setQuery($select, $offset, self::CHUNK_SIZE);
            $rows = $this->source->loadAssocList() ?: [];

            if ($rows === []) {
                break;
            }

            $insert = $this->target->createQuery()
                ->insert($this->target->quoteName($targetTable))
                ->columns(array_map([$this->target, 'quoteName'], $targetFields));

            foreach ($rows as $row) {
                $values = [];

                foreach ($sourceFields as $sourceField) {
                    $value = $row[$sourceField] ?? null;
                    $values[] = $value === null ? 'NULL' : $this->target->quote((string) $value);
                }

                $insert->values(implode(',', $values));
            }

            $this->target->setQuery($insert);
            $this->target->execute();
            $count = count($rows);
            $copied += $count;

            if ($count < self::CHUNK_SIZE) {
                break;
            }

            $offset += self::CHUNK_SIZE;
        }

        return $copied;
    }

    /** @return array<string,mixed> */
    private function ensureColumn(string $table, string $column, string $definition): array
    {
        $columns = $this->target->getTableColumns($table, false);

        if (array_key_exists($column, $columns)) {
            return $columns;
        }

        $sql = 'ALTER TABLE ' . $this->target->quoteName($table)
            . ' ADD ' . $this->target->quoteName($column) . ' ' . $definition;
        $this->target->setQuery($sql);
        $this->target->execute();

        return $this->target->getTableColumns($table, false);
    }

    private function deletePreviousImports(string $targetTable): void
    {
        $zero = 0;
        $query = $this->target->createQuery()
            ->delete($this->target->quoteName($targetTable))
            ->where($this->target->quoteName('import_id') . ' <> :zero')
            ->bind(':zero', $zero);
        $this->target->setQuery($query);
        $this->target->execute();
    }

    private function updatePositionSportsType(string $targetTable, int $sportsTypeId): void
    {
        $query = $this->target->createQuery()
            ->update($this->target->quoteName($targetTable))
            ->set($this->target->quoteName('sports_type_id') . ' = ' . $sportsTypeId)
            ->where($this->target->quoteName('sports_type_id') . ' <> ' . $sportsTypeId);
        $this->target->setQuery($query);
        $this->target->execute();
    }

    /** @return array{table:string,success:bool,copied:int,message:string} */
    private function result(string $table, bool $success, int $copied, string $message): array
    {
        return [
            'table' => $table,
            'success' => $success,
            'copied' => $copied,
            'message' => $message,
        ];
    }
}
