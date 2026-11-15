<?php
/**
 * Joomla 5/6 native project-member XML import service.
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

/** Native writer for project XML import steps 21 and 22. */
final class XmlProjectMemberImportService
{
    /**
     * Initialise the native project-member XML import writer.
     *
     * @param DatabaseInterface $database Joomla database used for member writes.
     * @return void
     */
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * Import player and staff assignments after their dependencies are mapped.
     *
     * Preserves the legacy step 21/22 import order and map/message keys.
     * Rejects malformed source and destination IDs before writing rows;
     * missing required team/person references skip the affected assignment.
     *
     * @param array<string,mixed> $parsedData Parsed project XML collections.
     * @param array<int,int> $projectTeamMap Source-to-local project teams.
     * @param array<int,int> $personMap Source-to-local people.
     * @param array<int,int> $projectPositionMap Source-to-local position IDs.
     * @param string $step Last requested XML import step.
     * @return array{maps:array<string,array<int,int>>,messages:array<string,string>} Maps and legacy status text.
     * @throws RuntimeException When a database insert or ID retrieval fails.
     */
    public function import(
        array $parsedData,
        array $projectTeamMap,
        array $personMap,
        array $projectPositionMap,
        string $step
    ): array {
        $maps = [
            '_convertTeamPlayerID' => [],
            '_convertTeamStaffID' => [],
        ];
        $messages = [];

        if (version_compare($step, '21', 'ge')) {
            $players = $this->importCollection(
                (array) ($parsedData['teamplayer'] ?? []),
                '#__sportsmanagement_team_player',
                'team-player',
                $projectTeamMap,
                $personMap,
                $projectPositionMap
            );
            $maps['_convertTeamPlayerID'] = $players['map'];
            $messages['Importing team-player data:'] = $players['message'];
        }

        if (version_compare($step, '22', 'ge')) {
            $staff = $this->importCollection(
                (array) ($parsedData['teamstaff'] ?? []),
                '#__sportsmanagement_team_staff',
                'team-staff',
                $projectTeamMap,
                $personMap,
                $projectPositionMap
            );
            $maps['_convertTeamStaffID'] = $staff['map'];
            $messages['Importing team-staff data:'] = $staff['message'];
        }

        return ['maps' => $maps, 'messages' => $messages];
    }

    /**
     * Write one imported player/staff collection with its resolved relations.
     *
     * Reads only complete positive identifiers, verifies mapped destinations
     * and excludes nested source column values from SQL inserts. A missing
     * post-insert ID aborts when a source ID must be mapped downstream.
     *
     * @param array<int|string,mixed> $collection Parsed player/staff rows.
     * @param string $table Trusted destination database table.
     * @param string $label Trusted legacy player/staff status label.
     * @param array<int,int> $projectTeamMap Prepared project-team IDs.
     * @param array<int,int> $personMap Prepared person IDs.
     * @param array<int,int> $projectPositionMap Prepared position IDs.
     * @return array{map:array<int,int>,message:string} Source map and HTML status.
     * @throws RuntimeException When insert or ID retrieval fails.
     */
    private function importCollection(
        array $collection,
        string $table,
        string $label,
        array $projectTeamMap,
        array $personMap,
        array $projectPositionMap
    ): array {
        $map = [];
        $message = '';

        foreach (array_values($collection) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = self::positiveId($source->id ?? null);
            $oldProjectTeamId = self::positiveId($source->projectteam_id ?? null);
            $oldPersonId = self::positiveId($source->person_id ?? null);
            $projectTeamId = self::positiveId($projectTeamMap[$oldProjectTeamId] ?? null);
            $personId = self::positiveId($personMap[$oldPersonId] ?? null);

            if ($projectTeamId <= 0 || $personId <= 0) {
                $message .= $this->skippedMessage($label, $oldId);
                continue;
            }

            $row = $this->filterSourceFields($source, $table);
            $row->projectteam_id = $projectTeamId;
            $row->person_id = $personId;
            $oldProjectPositionId = self::positiveId($source->project_position_id ?? null);
            $row->project_position_id = $oldProjectPositionId > 0
                ? self::positiveId($projectPositionMap[$oldProjectPositionId] ?? null)
                : 0;
            $row->published = 1;

            if (!$this->database->insertObject($table, $row)) {
                throw new RuntimeException('Unable to store imported ' . $label . '.', 500);
            }

            $databaseId = self::positiveId($this->database->insertid());

            if ($oldId > 0) {
                if ($databaseId <= 0) {
                    throw new RuntimeException('Unable to resolve imported ' . $label . ' database ID.', 500);
                }

                $map[$oldId] = $databaseId;
            }

            $message .= $this->createdMessage($label, $oldId);
        }

        return ['map' => $map, 'message' => $message];
    }

    /**
     * Parse a complete positive XML or resolved local record identifier.
     *
     * Accepts positive PHP integers and zero-padded decimal strings. Rejects
     * negative, overflowing, nested, partially numeric and boolean values,
     * avoiding wrong team/person associations from implicit PHP 8 casts.
     *
     * @param mixed $value Untrusted record ID.
     * @return int Positive record ID, or zero if invalid.
     */
    private static function positiveId(mixed $value): int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : 0;
        }

        if (!is_string($value) || $value === '' || strlen($value) > 128
            || preg_match('/^[0-9]+$/D', $value) !== 1) {
            return 0;
        }

        $digits = ltrim($value, '0');

        if ($digits === '') {
            return 0;
        }

        $result = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $result === false ? 0 : $result;
    }

    /**
     * Copy valid imported scalar fields present in the local table schema.
     *
     * Foreign keys and publication state are assigned explicitly by the
     * importer. Ignore unknown or nested XML values rather than passing
     * arrays or objects to Joomla's SQL field binder under PHP 8.
     *
     * @param object $source Raw XML member record.
     * @param string $table Trusted SportsManagement member table.
     * @return object Whitelisted import fields.
     */
    private function filterSourceFields(object $source, string $table): object
    {
        $columns = $this->database->getTableColumns($table);
        $row = new \stdClass();

        if (!is_array($columns)) {
            throw new RuntimeException('Unable to inspect imported member table fields.', 500);
        }

        foreach ($source as $field => $value) {
            if (in_array($field, ['id', 'projectteam_id', 'person_id', 'project_position_id', 'published'], true)
                || !array_key_exists((string) $field, $columns)
                || (!is_scalar($value) && $value !== null)) {
                continue;
            }

            $row->{$field} = $value;
        }

        return $row;
    }

    /**
     * Format a successful member import message with trusted label and ID.
     *
     * @param string $label Static player or staff label.
     * @param int $oldId Validated source member identifier.
     * @return string Legacy administrator HTML status.
     */
    private function createdMessage(string $label, int $oldId): string
    {
        return '<span style="color:green">Created new ' . $label . ' data from source ID: </span><strong>'
            . $oldId . '</strong><br />';
    }

    /**
     * Format a skipped member message without imported HTML content.
     *
     * @param string $label Static player or staff label.
     * @param int $oldId Validated source member identifier.
     * @return string Legacy administrator HTML status.
     */
    private function skippedMessage(string $label, int $oldId): string
    {
        return '<span style="color:red">Skipping ' . $label . ' source ID: </span><strong>'
            . $oldId . '</strong><br />';
    }
}
