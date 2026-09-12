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
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     *
     * @return array<string, string>
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
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     *
     * @return array{oldIdMap:array<int,int>,keyIds:array<int,int>,message:string}
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
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $parentMap
     *
     * @return array{keyIds:array<int,int>,message:string}
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
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     *
     * @return array<int, int>
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

    /** @param array<string, mixed> $post */
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

    private function findSportTypeById(int $id): ?object
    {
        return $this->findOne('#__sportsmanagement_sports_type', ['id' => $id]);
    }

    private function findSportTypeByName(string $name): ?object
    {
        return $this->findOne('#__sportsmanagement_sports_type', ['name' => $name]);
    }

    private function findPositionById(int $id): ?object
    {
        return $this->findOne('#__sportsmanagement_position', ['id' => $id]);
    }

    private function findPositionByNameAndParent(string $name, int $parentId): ?object
    {
        return $this->findOne('#__sportsmanagement_position', ['name' => $name, 'parent_id' => $parentId]);
    }

    /** @param array<string, int|string> $criteria */
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

    private function existingMessage(string $type, string $name): string
    {
        return '<span style="color:orange">Using existing ' . $type . ' data: </span><strong>'
            . $this->escape($name) . '</strong><br />';
    }

    private function createdMessage(string $type, string $name): string
    {
        return '<span style="color:green">Created new ' . $type . ' data: </span><strong>'
            . $this->escape($name) . '</strong><br />';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
