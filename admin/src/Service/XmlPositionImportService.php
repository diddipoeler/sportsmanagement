<?php
/**
 * Joomla 5/6 position XML import service.
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
 * Native writer for parent-position and position XML rows.
 *
 * Standalone imports process both stages together. Project imports can prepare
 * the two stages independently so backend_xmlimport_step remains meaningful.
 */
final class XmlPositionImportService
{
    /**
     * Create the Joomla position and sports-type XML import service.
     * Stores the database used to find or insert sport types, parent positions and individual position definitions.
     *
     * @param DatabaseInterface $database Joomla database driver.
     * @return void
     */
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * Import a hierarchy of sport types, parent positions and child positions from XML.
     * Resolves or creates the sport type, maps original parent-position IDs to local rows and imports selected positions using that mapping, reporting reused and newly created entities.
     *
     * @param array<string,mixed> $post Administrator selection and override values.
     * @param array<string,mixed> $parsedData Parsed sport and position XML collections.
     * @return array<string,string> HTML status messages for the imported hierarchy.
     */
    public function import(array $post, array $parsedData): array
    {
        $sportType = $this->resolveSportType($post);
        $parents = $this->processParentPositions($post, $parsedData, $sportType);
        $positions = $this->processPositions($post, $parsedData, $sportType, $parents['oldIdMap']);
        $sportTypeName = $this->escape((string) $sportType->name);
        $sportTypeMessage = !empty($sportType->created)
            ? '<span style="color:green">Created new sportstype data: </span><strong>' . $sportTypeName . '</strong><br />'
            : '<span style="color:orange">Using existing sportstype data: </span><strong>' . $sportTypeName . '</strong><br />';

        return [
            'Importing sports type data:' => $sportTypeMessage,
            'Importing parent-position data:' => $parents['message'],
            'Importing position data:' => $positions['message'],
        ];
    }

    /**
     * Prepare project step 6 and return form data that makes the legacy writer
     * consume the resolved database IDs instead of inserting parent positions.
     *
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     *
     * @return array<string, mixed>
     */
    public function prepareProjectParents(array $post, array $parsedData): array
    {
        $sportType = $this->resolveSportType($post);
        $parents = $this->processParentPositions($post, $parsedData, $sportType);

        foreach ($parents['keyIds'] as $key => $databaseId) {
            $post['dbParentPositionID_' . $key] = $databaseId;
            unset($post['parentPositionID_' . $key]);
        }

        return $post;
    }

    /**
     * Prepare project step 7 after step 6 has promoted the parent IDs.
     *
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     *
     * @return array<string, mixed>
     */
    public function prepareProjectPositions(array $post, array $parsedData): array
    {
        $sportType = $this->resolveSportType($post);
        $parentMap = $this->buildParentMapFromPost($post, $parsedData);
        $positions = $this->processPositions($post, $parsedData, $sportType, $parentMap);

        foreach ($positions['keyIds'] as $key => $databaseId) {
            $post['dbPositionID_' . $key] = $databaseId;
            unset($post['positionID_' . $key]);
        }

        return $post;
    }

    /**
     * Resolve or create parent positions required by the imported sport hierarchy.
     * Matches selected existing rows or deduplicates names under the root parent, then maps original XML IDs to local position IDs for subsequent child imports.
     *
     * @param array<string,mixed> $post Administrator parent-position selections.
     * @param array<string,mixed> $parsedData XML parent-position collection.
     * @param object $sportType Local sport-type record.
     * @return array Parent IDs keyed by original ID and form index, together with HTML messages.
     */
    private function processParentPositions(array $post, array $parsedData, object $sportType): array
    {
        $oldIdMap = [];
        $keyIds = [];
        $message = '';

        foreach (array_values((array) ($parsedData['parentposition'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = (int) ($source->id ?? 0);
            $databaseId = (int) ($post['dbParentPositionID_' . $key] ?? 0);

            if ($databaseId > 0) {
                $existing = $this->findPositionById($databaseId);

                if ($existing === null) {
                    throw new RuntimeException('Selected parent position was not found.', 404);
                }

                if ($oldId > 0) {
                    $oldIdMap[$oldId] = (int) $existing->id;
                }

                $keyIds[$key] = (int) $existing->id;
                $message .= $this->existingMessage('parent-position', (string) $existing->name);
                continue;
            }

            if (!array_key_exists('parentPositionID_' . $key, $post)) {
                continue;
            }

            $name = trim((string) ($post['parentPositionName_' . $key] ?? ($source->name ?? '')));

            if ($name === '') {
                continue;
            }

            $existing = $this->findPositionByNameAndParent($name, 0);

            if ($existing !== null) {
                $databaseId = (int) $existing->id;

                if ($oldId > 0) {
                    $oldIdMap[$oldId] = $databaseId;
                }

                $keyIds[$key] = $databaseId;
                $message .= $this->existingMessage('parent-position', (string) $existing->name);
                continue;
            }

            $row = (object) [
                'name' => $name,
                'parent_id' => 0,
                'persontype' => max(1, (int) ($source->persontype ?? 1)),
                'sports_type_id' => (int) $sportType->id,
                'published' => 1,
                'alias' => OutputFilter::stringURLSafe($name),
            ];
            $row = $this->filterTableFields($row, '#__sportsmanagement_position');

            if (!$this->database->insertObject('#__sportsmanagement_position', $row)) {
                throw new RuntimeException('Unable to store imported parent position: ' . $name, 500);
            }

            $databaseId = (int) $this->database->insertid();

            if ($oldId > 0) {
                $oldIdMap[$oldId] = $databaseId;
            }

            $keyIds[$key] = $databaseId;
            $message .= $this->createdMessage('parent-position', $name);
        }

        return [
            'oldIdMap' => $oldIdMap,
            'keyIds' => $keyIds,
            'message' => $message,
        ];
    }

    /**
     * Resolve or insert child positions while preserving their imported parent references.
     * Uses the parent-position conversion table, matches previously stored positions by local parent and name and builds a form-index map of resolved database identifiers.
     *
     * @param array<string,mixed> $post Administrator position selections and overrides.
     * @param array<string,mixed> $parsedData Parsed XML position records.
     * @param object $sportType Local sport-type record.
     * @param array<int,int> $parentMap Original XML parent IDs mapped to local IDs.
     * @return array Position IDs by form index and escaped import status messages.
     */
    private function processPositions(
        array $post,
        array $parsedData,
        object $sportType,
        array $parentMap
    ): array {
        $keyIds = [];
        $message = '';

        foreach (array_values((array) ($parsedData['position'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $databaseId = (int) ($post['dbPositionID_' . $key] ?? 0);

            if ($databaseId > 0) {
                $existing = $this->findPositionById($databaseId);

                if ($existing === null) {
                    throw new RuntimeException('Selected position was not found.', 404);
                }

                $keyIds[$key] = (int) $existing->id;
                $message .= $this->existingMessage('position', (string) $existing->name);
                continue;
            }

            if (!array_key_exists('positionID_' . $key, $post)) {
                continue;
            }

            $name = trim((string) ($post['positionName_' . $key] ?? ($source->name ?? '')));

            if ($name === '') {
                continue;
            }

            $oldParentId = (int) ($source->parent_id ?? 0);
            $parentId = $oldParentId > 0 ? (int) ($parentMap[$oldParentId] ?? 0) : 0;
            $existing = $this->findPositionByNameAndParent($name, $parentId);

            if ($existing !== null) {
                $keyIds[$key] = (int) $existing->id;
                $message .= $this->existingMessage('position', (string) $existing->name);
                continue;
            }

            $row = (object) [
                'name' => $name,
                'parent_id' => $parentId,
                'persontype' => max(1, (int) ($source->persontype ?? 1)),
                'sports_type_id' => (int) $sportType->id,
                'published' => 1,
                'alias' => OutputFilter::stringURLSafe($name),
            ];
            $row = $this->filterTableFields($row, '#__sportsmanagement_position');

            if (!$this->database->insertObject('#__sportsmanagement_position', $row)) {
                throw new RuntimeException('Unable to store imported position: ' . $name, 500);
            }

            $databaseId = (int) $this->database->insertid();
            $keyIds[$key] = $databaseId;
            $message .= $this->createdMessage('position', $name);
        }

        return [
            'keyIds' => $keyIds,
            'message' => $message,
        ];
    }

    /**
     * Build a verified parent-position ID mapping from an already prepared import form.
     * Checks that each old XML parent ID has a positive selected local ID and that the destination position still exists before mapping it.
     *
     * @param array<string,mixed> $post Prepared administrator import form values.
     * @param array<string,mixed> $parsedData XML parent-position collection.
     * @return array<int,int> Original XML parent IDs mapped to existing local position IDs.
     */
    private function buildParentMapFromPost(array $post, array $parsedData): array
    {
        $map = [];

        foreach (array_values((array) ($parsedData['parentposition'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = (int) ($source->id ?? 0);
            $databaseId = (int) ($post['dbParentPositionID_' . $key] ?? 0);

            if ($oldId <= 0 || $databaseId <= 0) {
                continue;
            }

            if ($this->findPositionById($databaseId) === null) {
                throw new RuntimeException('Prepared parent position was not found.', 404);
            }

            $map[$oldId] = $databaseId;
        }

        return $map;
    }

    /**
     * Resolve or create the sport type selected for an imported position hierarchy.
     * Validates the selected existing sport type or builds a new entry from the requested sport name, marking the result as created or reused.
     *
     * @param array<string,mixed> $post Sport-type selections and optional new name from the import form.
     * @return object Sport-type record with identifier, name and creation status.
     */
    private function resolveSportType(array $post): object
    {
        $id = (int) ($post['sportstype'] ?? 0);

        if ($id > 0) {
            $sportType = $this->findSportTypeById($id);

            if ($sportType === null) {
                throw new RuntimeException('Selected sports type was not found.', 404);
            }

            $sportType->created = false;

            return $sportType;
        }

        $name = substr(trim((string) ($post['sportstypeNew'] ?? '')), 0, 25);

        if ($name === '') {
            throw new RuntimeException('Missing sports type for position import.', 400);
        }

        $sportType = $this->findSportTypeByName($name);

        if ($sportType !== null) {
            $sportType->created = false;

            return $sportType;
        }

        $row = (object) ['name' => $name];

        if (!$this->database->insertObject('#__sportsmanagement_sports_type', $row)) {
            throw new RuntimeException('Unable to store sports type: ' . $name, 500);
        }

        $row->id = (int) $this->database->insertid();
        $row->created = true;

        return $row;
    }

    /**
     * Find an existing sport type by its local numeric database ID.
     * Delegates to the bound common lookup helper for the sports-type table.
     *
     * @param int $id Local sport-type identifier.
     * @return object|null Matching sport type, or null.
     */
    private function findSportTypeById(int $id): ?object
    {
        return $this->findOne('#__sportsmanagement_sports_type', ['id' => $id]);
    }

    /**
     * Find a stored sport type matching the imported name.
     * Uses the common lookup helper to avoid creating a duplicate sports-type record.
     *
     * @param string $name Sport-type label from the submitted import.
     * @return object|null Existing sport type with that name, or null.
     */
    private function findSportTypeByName(string $name): ?object
    {
        return $this->findOne('#__sportsmanagement_sports_type', ['name' => $name]);
    }

    /**
     * Resolve an existing position using its local database identifier.
     * Delegates to a bound position-table lookup when the importer reuses a preselected position.
     *
     * @param int $id Local position record identifier.
     * @return object|null Matching position record, or null.
     */
    private function findPositionById(int $id): ?object
    {
        return $this->findOne('#__sportsmanagement_position', ['id' => $id]);
    }

    /**
     * Find a position within its imported parent-position hierarchy.
     * Restricts the lookup by both name and parent ID so positions under different parent groups remain distinct.
     *
     * @param string $name Position label.
     * @param int $parentId Local parent-position identifier.
     * @return object|null Existing position in that parent group, or null.
     */
    private function findPositionByNameAndParent(string $name, int $parentId): ?object
    {
        return $this->findOne('#__sportsmanagement_position', ['name' => $name, 'parent_id' => $parentId]);
    }

    /**
     * Execute a bounded lookup against a trusted internal import table.
     * Builds a Joomla query from the supplied column/value criteria and binds each parameter according to its integer or string type.
     *
     * @param string $table Trusted sport-type or position database table.
     * @param array<string,int|string> $criteria Internal field/value conditions for the lookup.
     * @return object|null First matching ID/name record, or null.
     */
    private function findOne(string $table, array $criteria): ?object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName($table));
        $placeholders = [];
        $values = [];
        $types = [];

        foreach ($criteria as $field => $value) {
            $placeholder = ':criterion' . count($values);
            $query->where($this->database->quoteName($field) . ' = ' . $placeholder);
            $placeholders[] = $placeholder;
            $values[] = $value;
            $types[] = is_int($value) ? ParameterType::INTEGER : ParameterType::STRING;
        }

        if ($placeholders !== []) {
            $query->bind($placeholders, $values, $types);
        }

        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    /**
     * Keep only source properties supported by the installed destination table.
     * Reads Joomla database-column metadata and strips properties not valid for the current position or sport-type schema.
     *
     * @param object $source Prepared import record.
     * @param string $table Trusted destination database table name.
     * @return object Data object containing only supported column names.
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
     * Format an administrator status line for a reused imported entity.
     * Returns a legacy orange status message and HTML-escapes the imported entity name.
     *
     * @param string $type Trusted entity label displayed in the import report.
     * @param string $name Name of the reused position or sport type.
     * @return string HTML status message identifying the reused entity.
     */
    private function existingMessage(string $type, string $name): string
    {
        return '<span style="color:orange">Using existing ' . $type . ' data: </span><strong>'
            . $this->escape($name) . '</strong><br />';
    }

    /**
     * Format an administrator status line for a newly inserted entity.
     * Returns a legacy green confirmation message and escapes the sport or position name before rendering.
     *
     * @param string $type Trusted import entity category.
     * @param string $name Name of the newly created position or sport type.
     * @return string HTML status message identifying the new entity.
     */
    private function createdMessage(string $type, string $name): string
    {
        return '<span style="color:green">Created new ' . $type . ' data: </span><strong>'
            . $this->escape($name) . '</strong><br />';
    }

    /**
     * Escape XML position names before including them in administrator HTML output.
     * Encodes special HTML characters and quotes in untrusted values from the import form or XML source.
     *
     * @param string $value XML-derived or submitted display value.
     * @return string HTML-escaped output text.
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
