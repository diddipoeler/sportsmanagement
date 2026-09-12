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
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $projectTeamMap
     * @param array<int, int> $personMap
     * @param array<int, int> $projectPositionMap
     *
     * @return array{maps:array<string,array<int,int>>,messages:array<string,string>}
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
     * @param array<int|string, mixed> $collection
     * @param array<int, int> $projectTeamMap
     * @param array<int, int> $personMap
     * @param array<int, int> $projectPositionMap
     * @return array{map:array<int,int>,message:string}
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

            $oldId = (int) ($source->id ?? 0);
            $oldProjectTeamId = (int) ($source->projectteam_id ?? 0);
            $oldPersonId = (int) ($source->person_id ?? 0);
            $projectTeamId = (int) ($projectTeamMap[$oldProjectTeamId] ?? 0);
            $personId = (int) ($personMap[$oldPersonId] ?? 0);

            if ($projectTeamId <= 0 || $personId <= 0) {
                $message .= $this->skippedMessage($label, $oldId);
                continue;
            }

            $row = $this->filterSourceFields($source, $table);
            $row->projectteam_id = $projectTeamId;
            $row->person_id = $personId;
            $oldProjectPositionId = (int) ($source->project_position_id ?? 0);
            $row->project_position_id = $oldProjectPositionId > 0
                ? (int) ($projectPositionMap[$oldProjectPositionId] ?? 0)
                : 0;
            $row->published = 1;

            if (!$this->database->insertObject($table, $row)) {
                throw new RuntimeException('Unable to store imported ' . $label . '.', 500);
            }

            $databaseId = (int) $this->database->insertid();

            if ($oldId > 0) {
                $map[$oldId] = $databaseId;
            }

            $message .= $this->createdMessage($label, $oldId);
        }

        return ['map' => $map, 'message' => $message];
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

    private function createdMessage(string $label, int $oldId): string
    {
        return '<span style="color:green">Created new ' . $label . ' data from source ID: </span><strong>'
            . $oldId . '</strong><br />';
    }

    private function skippedMessage(string $label, int $oldId): string
    {
        return '<span style="color:red">Skipping ' . $label . ' source ID: </span><strong>'
            . $oldId . '</strong><br />';
    }
}
