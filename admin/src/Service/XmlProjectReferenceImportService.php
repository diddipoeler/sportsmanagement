<?php
/**
 * Joomla 5/6 native resolver for early project XML import data.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use RuntimeException;

/**
 * Resolves independent project rows before the remaining legacy ID graph runs.
 *
 * Project steps 1-14 are written natively. Their real database IDs are written
 * back into the historical form fields so the legacy importer can keep building
 * its conversion maps for the still-dependent project objects.
 */
final class XmlProjectReferenceImportService
{
    /**
     * Initialise the reference importer with the database connection used by the native project import stages.
     * @param DatabaseInterface $database Target database connection.
     */
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * Validate the project import context and execute the requested cumulative import stages 1 through 14. Resolve sport, league and season references, delegate related entity imports and write the resolved identifiers back into the historical form fields for subsequent processing.
     * @param array<string,mixed> $post Submitted import selections.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @param string $step Last import stage to execute.
     * @param int $importProjectId Existing import project identifier when applicable.
     * @return array{post:array<string,mixed>,messages:list<string>,projectId:?int} Prepared selections, reference messages and resolved project identifier.
     */
    public function prepare(
        array $post,
        array $parsedData,
        string $step,
        int $importProjectId = 0
    ): array {
        $this->validateProject($post, $parsedData);

        $messages = [];
        $projectId = null;
        $sportTypeId = max(0, (int) ($post['sportstype'] ?? 0));

        if (version_compare($step, '1', 'ge')) {
            $sportType = $this->resolveSportsType($post);
            $sportTypeId = (int) $sportType->id;
            $post['sportstype'] = $sportTypeId;
            $post['sportstypeNew'] = '';
            $messages[] = $this->message('sportstype', (string) $sportType->name, (bool) $sportType->created);
        }

        if (version_compare($step, '2', 'ge')) {
            if ($sportTypeId <= 0) {
                throw new RuntimeException('Missing sports type for project XML import.', 400);
            }

            $league = $this->resolveLeague($post, $parsedData, $sportTypeId);
            $post['league'] = (int) $league->id;
            $post['leagueNew'] = '';
            $messages[] = $this->message('league', (string) $league->name, (bool) $league->created);
        }

        if (version_compare($step, '3', 'ge')) {
            $season = $this->resolveSeason($post);
            $post['season'] = (int) $season->id;
            $post['seasonNew'] = '';
            $messages[] = $this->message('season', (string) $season->name, (bool) $season->created);
        }

        if (version_compare($step, '4', 'ge')) {
            (new XmlEventImportService($this->database))->import($post, $parsedData);
            $post = $this->promoteEventIds($post, $parsedData);
        }

        if (version_compare($step, '5', 'ge')) {
            (new XmlStatisticImportService($this->database))->import($post, $parsedData);
            $post = $this->promoteStatisticIds($post, $parsedData);
        }

        if (version_compare($step, '6', 'ge')) {
            $positionService = new XmlPositionImportService($this->database);
            $post = $positionService->prepareProjectParents($post, $parsedData);

            if (version_compare($step, '7', 'ge')) {
                $post = $positionService->prepareProjectPositions($post, $parsedData);
            }
        }

        if (version_compare($step, '8', 'ge')) {
            (new XmlPositionEventTypeImportService($this->database))->import($post, $parsedData);
        }

        if (version_compare($step, '9', 'ge')) {
            $post = (new XmlProjectVenueTeamImportService($this->database))->prepare(
                $post,
                $parsedData,
                $step
            );
        }

        if (version_compare($step, '13', 'ge')) {
            $post = (new XmlProjectPersonImportService($this->database))->prepare($post, $parsedData);
        }

        if (version_compare($step, '14', 'ge')) {
            $projectId = (new XmlProjectCoreImportService($this->database))->import(
                $post,
                $parsedData,
                $importProjectId
            );
        }

        return [
            'post' => $post,
            'messages' => $messages,
            'projectId' => $projectId,
        ];
    }

    /**
     * Mirror the legacy project-name/object checks before any native insert.
     *
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     */
    private function validateProject(array $post, array $parsedData): void
    {
        if (!array_key_exists('name', $post)) {
            throw new RuntimeException('Missing projectname', 400);
        }

        if (!isset($parsedData['project']) || !is_object($parsedData['project'])) {
            throw new RuntimeException('Project object is missing inside import file.', 400);
        }

        $projectName = substr(stripslashes((string) $post['name']), 0, 100);
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('id'))
            ->from($this->database->quoteName('#__sportsmanagement_project'))
            ->where($this->database->quoteName('name') . ' = :projectName')
            ->bind(':projectName', $projectName, ParameterType::STRING);
        $this->database->setQuery($query, 0, 1);

        if ($this->database->loadResult() !== null) {
            throw new RuntimeException('Projectname already exists', 409);
        }
    }

    /**
     * Reuse a selected sport type or find one by its truncated submitted name. Insert a missing type and indicate whether it was created during this import.
     * @param array<string,mixed> $post Import form selections.
     * @return object Sport type record with creation status.
     * @throws RuntimeException If the selection is invalid or the write fails.
     */
    private function resolveSportsType(array $post): object
    {
        $id = max(0, (int) ($post['sportstype'] ?? 0));

        if ($id > 0) {
            return $this->requireById('#__sportsmanagement_sports_type', $id, 'sports type');
        }

        $name = substr(trim((string) ($post['sportstypeNew'] ?? '')), 0, 25);

        if ($name === '') {
            throw new RuntimeException('Missing sports type for project XML import.', 400);
        }

        $existing = $this->findByName('#__sportsmanagement_sports_type', $name);

        if ($existing !== null) {
            $existing->created = false;

            return $existing;
        }

        $row = (object) ['name' => $name];

        if (!$this->database->insertObject('#__sportsmanagement_sports_type', $row)) {
            throw new RuntimeException('Unable to store project sports type: ' . $name, 500);
        }

        $row->id = (int) $this->database->insertid();
        $row->created = true;

        return $row;
    }

    /**
     * Resolve an explicitly selected league or reuse a matching name. Create a missing league with generated aliases, sport and age-group associations, source country and the configured placeholder image, retaining only supported database fields.
     * @param array<string,mixed> $post Import form selections.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @param int $sportTypeId Resolved sport type identifier.
     * @return object League record with creation status.
     * @throws RuntimeException If the selection is invalid or insertion fails.
     */
    private function resolveLeague(array $post, array $parsedData, int $sportTypeId): object
    {
        $id = max(0, (int) ($post['league'] ?? 0));

        if ($id > 0) {
            return $this->requireById('#__sportsmanagement_league', $id, 'league');
        }

        $name = substr(trim((string) ($post['leagueNew'] ?? '')), 0, 75);

        if ($name === '') {
            throw new RuntimeException('Missing league for project XML import.', 400);
        }

        $leagueName = substr($name, 0, 74);
        $existing = $this->findByName('#__sportsmanagement_league', $leagueName);

        if ($existing !== null) {
            $existing->created = false;

            return $existing;
        }

        $slug = OutputFilter::stringURLSafe($name);
        $source = isset($parsedData['league']) && is_object($parsedData['league'])
            ? $parsedData['league']
            : null;
        $row = (object) [
            'name' => $leagueName,
            'alias' => substr($slug, 0, 74),
            'short_name' => substr($slug, 0, 14),
            'middle_name' => substr($slug, 0, 24),
            'country' => trim((string) ($source->country ?? '')),
            'sports_type_id' => $sportTypeId,
            'agegroup_id' => max(0, (int) ($post['agegroup_id'] ?? 0)),
            'picture' => (string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_big', ''),
        ];
        $row = $this->filterTableFields($row, '#__sportsmanagement_league');

        if (!$this->database->insertObject('#__sportsmanagement_league', $row)) {
            throw new RuntimeException('Unable to store project league: ' . $leagueName, 500);
        }

        $row->id = (int) $this->database->insertid();
        $row->name = $leagueName;
        $row->created = true;

        return $row;
    }

    /**
     * Reuse a selected season or look up the submitted new season name. Insert a missing season with its generated alias and record whether it was newly created.
     * @param array<string,mixed> $post Import form selections.
     * @return object Season record with creation status.
     * @throws RuntimeException If the selection is invalid or insertion fails.
     */
    private function resolveSeason(array $post): object
    {
        $id = max(0, (int) ($post['season'] ?? 0));

        if ($id > 0) {
            return $this->requireById('#__sportsmanagement_season', $id, 'season');
        }

        $name = substr(trim((string) ($post['seasonNew'] ?? '')), 0, 75);

        if ($name === '') {
            throw new RuntimeException('Missing season for project XML import.', 400);
        }

        $existing = $this->findByName('#__sportsmanagement_season', $name);

        if ($existing !== null) {
            $existing->created = false;

            return $existing;
        }

        $row = (object) [
            'name' => $name,
            'alias' => OutputFilter::stringURLSafe($name),
        ];
        $row = $this->filterTableFields($row, '#__sportsmanagement_season');

        if (!$this->database->insertObject('#__sportsmanagement_season', $row)) {
            throw new RuntimeException('Unable to store project season: ' . $name, 500);
        }

        $row->id = (int) $this->database->insertid();
        $row->name = $name;
        $row->created = true;

        return $row;
    }

    /**
     * Validate selected event identifiers and resolve newly prepared event records by name. Replace creation flags with database identifiers so subsequent legacy stages can reuse the records.
     * @param array<string,mixed> $post Import selections.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @return array<string,mixed> Updated import selections.
     * @throws RuntimeException If a required event reference cannot be resolved.
     */
    private function promoteEventIds(array $post, array $parsedData): array
    {
        foreach (array_values((array) ($parsedData['event'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $field = 'dbEventID_' . $key;
            $databaseId = max(0, (int) ($post[$field] ?? 0));

            if ($databaseId > 0) {
                $this->requireById('#__sportsmanagement_eventtype', $databaseId, 'event type');
                continue;
            }

            if (!array_key_exists('eventID_' . $key, $post)) {
                continue;
            }

            $name = trim((string) ($post['eventName_' . $key] ?? ($source->name ?? '')));

            if ($name === '') {
                throw new RuntimeException('Missing event name for project XML import.', 400);
            }

            $event = $this->findByName('#__sportsmanagement_eventtype', $name);

            if ($event === null) {
                throw new RuntimeException('Prepared project event type was not found: ' . $name, 500);
            }

            $post[$field] = (int) $event->id;
            unset($post['eventID_' . $key]);
        }

        return $post;
    }

    /**
     * Validate selected statistic identifiers and resolve newly prepared statistics by name and class. Replace creation flags with the corresponding database identifiers.
     * @param array<string,mixed> $post Import selections.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @return array<string,mixed> Updated import selections.
     * @throws RuntimeException If a required statistic reference cannot be resolved.
     */
    private function promoteStatisticIds(array $post, array $parsedData): array
    {
        foreach (array_values((array) ($parsedData['statistic'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $field = 'dbStatisticID_' . $key;
            $databaseId = max(0, (int) ($post[$field] ?? 0));

            if ($databaseId > 0) {
                $this->requireById('#__sportsmanagement_statistic', $databaseId, 'statistic');
                continue;
            }

            if (!array_key_exists('statisticID_' . $key, $post)) {
                continue;
            }

            $name = trim((string) ($post['statisticName_' . $key] ?? ($source->name ?? '')));
            $class = trim((string) ($source->class ?? ''));

            if ($name === '') {
                throw new RuntimeException('Missing statistic name for project XML import.', 400);
            }

            $statistic = $this->findStatisticByNameAndClass($name, $class);

            if ($statistic === null) {
                throw new RuntimeException('Prepared project statistic was not found: ' . $name, 500);
            }

            $post[$field] = (int) $statistic->id;
            unset($post['statisticID_' . $key]);
        }

        return $post;
    }

    /**
     * Retrieve a reference record from the specified table using a bound identifier and mark it as an existing record.
     * @param string $table Reference table name.
     * @param int $id Selected identifier.
     * @param string $label Reference type for error reporting.
     * @return object Existing reference record.
     * @throws RuntimeException If the selected reference is missing.
     */
    private function requireById(string $table, int $id, string $label): object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName($table))
            ->where($this->database->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);
        $row = $this->database->loadObject();

        if (!$row) {
            throw new RuntimeException('Selected ' . $label . ' was not found.', 404);
        }

        $row->created = false;

        return $row;
    }

    /**
     * Find the first reference record matching the supplied name in the specified table using a bound query parameter.
     * @param string $table Reference table name.
     * @param string $name Reference name.
     * @return object|null Matching reference or null.
     */
    private function findByName(string $table, string $name): ?object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName($table))
            ->where($this->database->quoteName('name') . ' = :name')
            ->bind(':name', $name, ParameterType::STRING);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    /**
     * Find a statistic matching both its name and class using bound query parameters.
     * @param string $name Statistic name.
     * @param string $class Statistic class.
     * @return object|null Matching statistic or null.
     */
    private function findStatisticByNameAndClass(string $name, string $class): ?object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_statistic'))
            ->where($this->database->quoteName('name') . ' = :name')
            ->where($this->database->quoteName('class') . ' = :class')
            ->bind(':name', $name, ParameterType::STRING)
            ->bind(':class', $class, ParameterType::STRING);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    /**
     * Copy only properties whose names correspond to columns in the installed target table.
     * @param object $source Prepared reference record.
     * @param string $table Target table name.
     * @return object Schema-compatible record.
     */
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

    /**
     * Build an HTML report entry distinguishing created and reused reference records and escape the reference name for display.
     * @param string $type Internal reference type label.
     * @param string $name Reference display name.
     * @param bool $created Whether the record was newly inserted.
     * @return string HTML report entry.
     */
    private function message(string $type, string $name, bool $created): string
    {
        $action = $created ? 'Created new ' : 'Using existing ';
        $color = $created ? 'green' : 'orange';

        return '<span style="color:' . $color . '">' . $action . $type . ' data: </span><strong>'
            . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</strong><br />';
    }
}
