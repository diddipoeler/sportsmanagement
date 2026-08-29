<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;
use RuntimeException;

/** Native writer for standalone parent-position and position XML imports. */
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
        $parentMap = [];
        $parentMessage = '';
        $positionMessage = '';

        foreach (array_values((array) ($parsedData['parentposition'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = (int) ($source->id ?? 0);
            $databaseId = (int) ($post['dbParentPositionID_' . $key] ?? 0);

            if ($databaseId > 0) {
                $existing = $this->findPositionById($databaseId);

                if ($existing !== null) {
                    if ($oldId > 0) {
                        $parentMap[$oldId] = (int) $existing->id;
                    }

                    $parentMessage .= $this->existingMessage('parent-position', (string) $existing->name);
                }

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
                if ($oldId > 0) {
                    $parentMap[$oldId] = (int) $existing->id;
                }

                $parentMessage .= $this->existingMessage('parent-position', (string) $existing->name);
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

            $newId = (int) $this->database->insertid();

            if ($oldId > 0) {
                $parentMap[$oldId] = $newId;
            }

            $parentMessage .= $this->createdMessage('parent-position', $name);
        }

        foreach (array_values((array) ($parsedData['position'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $databaseId = (int) ($post['dbPositionID_' . $key] ?? 0);

            if ($databaseId > 0) {
                $existing = $this->findPositionById($databaseId);

                if ($existing !== null) {
                    $positionMessage .= $this->existingMessage('position', (string) $existing->name);
                }

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
                $positionMessage .= $this->existingMessage('position', (string) $existing->name);
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

            $positionMessage .= $this->createdMessage('position', $name);
        }

        $sportTypeName = $this->escape((string) $sportType->name);
        $sportTypeMessage = !empty($sportType->created)
            ? '<span style="color:green">Created new sportstype data: </span><strong>' . $sportTypeName . '</strong><br />'
            : '<span style="color:orange">Using existing sportstype data: </span><strong>' . $sportTypeName . '</strong><br />';

        return [
            'Importing sports type data:' => $sportTypeMessage,
            'Importing parent-position data:' => $parentMessage,
            'Importing position data:' => $positionMessage,
        ];
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
            throw new RuntimeException('Missing sports type for standalone position import.', 400);
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
        $query = $this->database->getQuery(true)
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName($table));

        foreach ($criteria as $field => $value) {
            $expression = is_int($value)
                ? (string) $value
                : $this->database->quote($value);
            $query->where($this->database->quoteName($field) . ' = ' . $expression);
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
