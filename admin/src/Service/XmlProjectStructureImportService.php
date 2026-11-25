<?php
/**
 * Joomla 5/6 native project-structure XML import service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use RuntimeException;

/**
 * Native writer for project XML import steps 16-19.
 *
 * The returned maps use the historical source IDs as keys so the remaining
 * legacy steps can consume the same conversion state without re-inserting the
 * project structure rows.
 */
final class XmlProjectStructureImportService
{
    /**
     * Initialise the native project structure importer with the target database connection.
     * @param DatabaseInterface $database Target database connection.
     */
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * Import the project structure for stages 16 through 19 in dependency order: divisions, positions, referees and teams. Collect source-to-target identifier maps and reports for subsequent import stages.
     * @param array<string,mixed> $post Prepared entity selections.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @param int $projectId Target project identifier.
     * @param int $seasonId Target season identifier.
     * @param int $adminId Administrator assigned to project teams.
     * @param string $importVersion Source format version.
     * @param string $step Current import stage.
     * @return array{maps:array<string,array<int,int>>,messages:array<string,string>} Conversion maps and reports.
     * @throws RuntimeException If the target project is missing or a required write fails.
     */
    public function import(
        array $post,
        array $parsedData,
        int $projectId,
        int $seasonId,
        int $adminId,
        string $importVersion,
        string $step
    ): array {
        if ($projectId <= 0) {
            throw new RuntimeException('Missing prepared project for project-structure XML import.', 400);
        }

        $maps = [
            '_convertDivisionID' => [],
            '_convertProjectPositionID' => [],
            '_convertProjectRefereeID' => [],
            '_convertProjectTeamID' => [],
        ];
        $messages = [];

        if (version_compare($step, '16', 'ge')) {
            $division = $this->importDivisions($parsedData, $projectId);
            $maps['_convertDivisionID'] = $division['map'];
            $messages['Importing division data:'] = $division['message'];
        }

        if (version_compare($step, '17', 'ge')) {
            $positionMap = $this->buildPreparedMap($post, $parsedData, 'position', 'dbPositionID_');
            $projectPosition = $this->importProjectPositions($parsedData, $projectId, $positionMap);
            $maps['_convertProjectPositionID'] = $projectPosition['map'];
            $messages['Importing project-position data:'] = $projectPosition['message'];
        }

        if (version_compare($step, '18', 'ge')) {
            $personMap = $this->buildPreparedMap($post, $parsedData, 'person', 'dbPersonID_');
            $projectReferee = $this->importProjectReferees(
                $parsedData,
                $projectId,
                $personMap,
                $maps['_convertProjectPositionID']
            );
            $maps['_convertProjectRefereeID'] = $projectReferee['map'];
            $messages['Importing project-referee data:'] = $projectReferee['message'];
        }

        if (version_compare($step, '19', 'ge')) {
            $teamMap = $this->buildPreparedMap($post, $parsedData, 'team', 'dbTeamID_');
            $playgroundMap = $this->buildPreparedMap($post, $parsedData, 'playground', 'dbPlaygroundID_');
            $projectTeam = $this->importProjectTeams(
                $parsedData,
                $projectId,
                $seasonId,
                $adminId,
                $importVersion,
                $teamMap,
                $playgroundMap,
                $maps['_convertDivisionID']
            );
            $maps['_convertProjectTeamID'] = $projectTeam['map'];
            $messages['Importing project-team data:'] = $projectTeam['message'];
        }

        return ['maps' => $maps, 'messages' => $messages];
    }

    /**
     * Insert divisions for the target project with generated aliases, then restore their parent relationships using the newly assigned identifiers rather than foreign primary keys.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @param int $projectId Target project identifier.
     * @return array{map:array<int,int>,message:string} Division conversion map and report.
     * @throws RuntimeException If insertion or parent mapping fails.
     */
    private function importDivisions(array $parsedData, int $projectId): array
    {
        $map = [];
        $parents = [];
        $message = '';

        foreach (array_values((array) ($parsedData['division'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = (int) ($source->id ?? 0);
            $name = trim((string) ($source->name ?? ''));
            $row = $this->filterSourceFields($source, '#__sportsmanagement_division');
            $row->project_id = $projectId;
            $row->alias = OutputFilter::stringURLSafe($name);

            // Resolve hierarchical IDs only after all imported divisions have
            // local IDs. This avoids carrying foreign database IDs forward.
            if (property_exists($row, 'parent_id')) {
                $row->parent_id = 0;
            }

            if (!$this->database->insertObject('#__sportsmanagement_division', $row)) {
                throw new RuntimeException('Unable to store imported division: ' . $name, 500);
            }

            $databaseId = (int) $this->database->insertid();

            if ($oldId > 0) {
                $map[$oldId] = $databaseId;
                $parents[$oldId] = max(0, (int) ($source->parent_id ?? 0));
            }

            $message .= $this->createdMessage('division', $name);
        }

        foreach ($parents as $oldId => $oldParentId) {
            if ($oldParentId <= 0 || !isset($map[$oldId], $map[$oldParentId])) {
                continue;
            }

            $update = (object) [
                'id' => $map[$oldId],
                'parent_id' => $map[$oldParentId],
            ];

            if (!$this->database->updateObject('#__sportsmanagement_division', $update, 'id')) {
                throw new RuntimeException('Unable to map imported division parent.', 500);
            }
        }

        return ['map' => $map, 'message' => $message];
    }

    /**
     * Resolve imported positions through the prepared position map, reuse matching project-position relations or create missing ones and report unresolved source records.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @param int $projectId Target project identifier.
     * @param array<int,int> $positionMap Source position IDs mapped to target IDs.
     * @return array{map:array<int,int>,message:string} Project-position map and report.
     */
    private function importProjectPositions(array $parsedData, int $projectId, array $positionMap): array
    {
        $map = [];
        $message = '';

        foreach (array_values((array) ($parsedData['projectposition'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = (int) ($source->id ?? 0);
            $oldPositionId = (int) ($source->position_id ?? 0);
            $positionId = (int) ($positionMap[$oldPositionId] ?? 0);

            if ($positionId <= 0) {
                $message .= $this->skippedMessage('project-position', $oldId);
                continue;
            }

            $databaseId = $this->findProjectPosition($projectId, $positionId);

            if ($databaseId <= 0) {
                $row = (object) [
                    'project_id' => $projectId,
                    'position_id' => $positionId,
                ];

                if (!$this->database->insertObject('#__sportsmanagement_project_position', $row)) {
                    throw new RuntimeException('Unable to store imported project position.', 500);
                }

                $databaseId = (int) $this->database->insertid();
                $message .= $this->createdMessage('project-position', (string) $positionId);
            } else {
                $message .= $this->existingMessage('project-position', (string) $positionId);
            }

            if ($oldId > 0) {
                $map[$oldId] = $databaseId;
            }
        }

        return ['map' => $map, 'message' => $message];
    }

    /**
     * Insert published project referee records using the mapped person and project-position identifiers, skipping records whose required relations cannot be resolved.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @param int $projectId Target project identifier.
     * @param array<int,int> $personMap Source person IDs mapped to target IDs.
     * @param array<int,int> $projectPositionMap Source project-position IDs mapped to target IDs.
     * @return array{map:array<int,int>,message:string} Referee conversion map and report.
     */
    private function importProjectReferees(
        array $parsedData,
        int $projectId,
        array $personMap,
        array $projectPositionMap
    ): array {
        $map = [];
        $message = '';

        foreach (array_values((array) ($parsedData['projectreferee'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = (int) ($source->id ?? 0);
            $personId = (int) ($personMap[(int) ($source->person_id ?? 0)] ?? 0);
            $projectPositionId = (int) ($projectPositionMap[(int) ($source->project_position_id ?? 0)] ?? 0);

            if ($personId <= 0 || $projectPositionId <= 0) {
                $message .= $this->skippedMessage('project-referee', $oldId);
                continue;
            }

            $row = $this->filterSourceFields($source, '#__sportsmanagement_project_referee');
            $row->project_id = $projectId;
            $row->person_id = $personId;
            $row->project_position_id = $projectPositionId;
            $row->published = 1;

            if (!$this->database->insertObject('#__sportsmanagement_project_referee', $row)) {
                throw new RuntimeException('Unable to store imported project referee.', 500);
            }

            $databaseId = (int) $this->database->insertid();

            if ($oldId > 0) {
                $map[$oldId] = $databaseId;
            }

            $message .= $this->createdMessage('project-referee', (string) $personId);
        }

        return ['map' => $map, 'message' => $message];
    }

    /**
     * Resolve seasonal team relations and insert project teams with mapped divisions and default venues. Translate historical description and information fields and choose the conversion-map key according to the source format.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @param int $projectId Target project identifier.
     * @param int $seasonId Target season identifier.
     * @param int $adminId Assigned administrator identifier.
     * @param string $importVersion Source format version.
     * @param array<int,int> $teamMap Source team IDs mapped to target IDs.
     * @param array<int,int> $playgroundMap Source venue IDs mapped to target IDs.
     * @param array<int,int> $divisionMap Source division IDs mapped to target IDs.
     * @return array{map:array<int,int>,message:string} Project-team conversion map and report.
     */
    private function importProjectTeams(
        array $parsedData,
        int $projectId,
        int $seasonId,
        int $adminId,
        string $importVersion,
        array $teamMap,
        array $playgroundMap,
        array $divisionMap
    ): array {
        $map = [];
        $message = '';

        foreach (array_values((array) ($parsedData['projectteam'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = (int) ($source->id ?? 0);
            $oldTeamId = (int) ($source->team_id ?? 0);
            $teamId = (int) ($teamMap[$oldTeamId] ?? 0);

            if ($teamId <= 0 || $seasonId <= 0) {
                $message .= $this->skippedMessage('project-team', $oldId);
                continue;
            }

            $seasonTeamId = $this->resolveSeasonTeam(
                $teamId,
                $seasonId,
                (string) ($source->picture ?? '')
            );
            $row = $this->filterSourceFields($source, '#__sportsmanagement_project_team');

            if (property_exists($source, 'description')) {
                $row->notes = (string) $source->description;
            }

            if (property_exists($source, 'info')) {
                $row->reason = (string) $source->info;
            }

            $row->project_id = $projectId;
            $row->team_id = $seasonTeamId;
            $row->admin = $adminId;
            $row->division_id = (int) ($divisionMap[(int) ($source->division_id ?? 0)] ?? 0);
            $row->standard_playground = (int) ($playgroundMap[(int) ($source->standard_playground ?? 0)] ?? 0);

            if (!$this->database->insertObject('#__sportsmanagement_project_team', $row)) {
                throw new RuntimeException('Unable to store imported project team.', 500);
            }

            $databaseId = (int) $this->database->insertid();
            $mapKey = strtoupper($importVersion) === 'NEW' ? $oldId : $oldTeamId;

            if ($mapKey > 0) {
                $map[$mapKey] = $databaseId;
            }

            $message .= $this->createdMessage('project-team', (string) $teamId);
        }

        return ['map' => $map, 'message' => $message];
    }

    /**
     * Find an existing team-season relation or insert one with the supplied team picture if none exists.
     * @param int $teamId Target team identifier.
     * @param int $seasonId Target season identifier.
     * @param string $picture Seasonal team picture.
     * @return int Existing or newly assigned season-team identifier.
     * @throws RuntimeException If insertion fails.
     */
    private function resolveSeasonTeam(int $teamId, int $seasonId, string $picture): int
    {
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('id'))
            ->from($this->database->quoteName('#__sportsmanagement_season_team_id'))
            ->where($this->database->quoteName('team_id') . ' = :teamId')
            ->where($this->database->quoteName('season_id') . ' = :seasonId')
            ->bind(':teamId', $teamId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);
        $existing = (int) ($this->database->loadResult() ?? 0);

        if ($existing > 0) {
            return $existing;
        }

        $row = (object) [
            'team_id' => $teamId,
            'season_id' => $seasonId,
            'picture' => $picture,
        ];

        if (!$this->database->insertObject('#__sportsmanagement_season_team_id', $row)) {
            throw new RuntimeException('Unable to store imported season/team relation.', 500);
        }

        return (int) $this->database->insertid();
    }

    /**
     * Look up the project-position relation matching the supplied project and position using bound query parameters.
     * @param int $projectId Target project identifier.
     * @param int $positionId Target position identifier.
     * @return int Relation identifier or zero.
     */
    private function findProjectPosition(int $projectId, int $positionId): int
    {
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('id'))
            ->from($this->database->quoteName('#__sportsmanagement_project_position'))
            ->where($this->database->quoteName('project_id') . ' = :projectId')
            ->where($this->database->quoteName('position_id') . ' = :positionId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':positionId', $positionId, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);

        return (int) ($this->database->loadResult() ?? 0);
    }

    /**
     * Build a conversion map from the selected parsed XML collection and its corresponding indexed form fields, retaining only positive source and target identifiers.
     * @param array<string,mixed> $post Prepared form selections.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @param string $collection Source collection name.
     * @param string $fieldPrefix Target selection field prefix.
     * @return array<int,int> Source-to-target identifier map.
     */
    private function buildPreparedMap(
        array $post,
        array $parsedData,
        string $collection,
        string $fieldPrefix
    ): array {
        $map = [];

        foreach (array_values((array) ($parsedData[$collection] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = (int) ($source->id ?? 0);
            $databaseId = max(0, (int) ($post[$fieldPrefix . $key] ?? 0));

            if ($oldId > 0 && $databaseId > 0) {
                $map[$oldId] = $databaseId;
            }
        }

        return $map;
    }

    /**
     * Copy fields supported by the installed target table schema into a new object, omitting the source primary key.
     * @param object $source Imported record.
     * @param string $table Target table name.
     * @return object Filtered insert record.
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
     * Build a success report entry for an inserted record with an escaped display value.
     * @param string $type Internal record-type label.
     * @param string $value Record display value.
     * @return string HTML success entry.
     */
    private function createdMessage(string $type, string $value): string
    {
        return '<span style="color:green">Created new ' . $type . ' data: </span><strong>'
            . $this->escape($value) . '</strong><br />';
    }

    /**
     * Build an informational report entry for a reused record with an escaped display value.
     * @param string $type Internal record-type label.
     * @param string $value Record display value.
     * @return string HTML information entry.
     */
    private function existingMessage(string $type, string $value): string
    {
        return '<span style="color:orange">Using existing ' . $type . ' data: </span><strong>'
            . $this->escape($value) . '</strong><br />';
    }

    /**
     * Build a warning report entry identifying the source record skipped during import.
     * @param string $type Internal record-type label.
     * @param int $oldId Source record identifier.
     * @return string HTML warning entry.
     */
    private function skippedMessage(string $type, int $oldId): string
    {
        return '<span style="color:red">Skipping ' . $type . ' source ID: </span><strong>'
            . $oldId . '</strong><br />';
    }

    /**
     * Escape a report value for HTML output using UTF-8 and both quote types.
     * @param string $value Raw display value.
     * @return string Escaped value.
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
