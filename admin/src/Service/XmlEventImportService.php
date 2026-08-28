<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;
use RuntimeException;

/**
 * Native writer for standalone event XML imports.
 *
 * Project imports still use the explicit legacy engine. This service only
 * covers the self-contained EventType export/import workflow.
 */
final class XmlEventImportService
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
        $sportTypeId = (int) $sportType->id;
        $sportTypeName = $this->escape((string) $sportType->name);
        $sportTypeMessage = !empty($sportType->created)
            ? '<span style="color:green">Created new sportstype data: </span><strong>' . $sportTypeName . '</strong><br />'
            : '<span style="color:orange">Using existing sportstype data: </span><strong>' . $sportTypeName . '</strong><br />';

        $eventMessage = '';
        $events = array_values((array) ($parsedData['event'] ?? []));

        foreach ($events as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $databaseEventId = (int) ($post['dbEventID_' . $key] ?? 0);

            if ($databaseEventId > 0) {
                $existing = $this->findEventById($databaseEventId);

                if ($existing !== null) {
                    $eventMessage .= '<span style="color:orange">Using existing event data: </span><strong>'
                        . $this->escape((string) $existing->name) . '</strong><br />';
                }

                continue;
            }

            // Disabled form controls are not submitted. The historical form
            // enables eventID_* only for rows selected for creation.
            if (!array_key_exists('eventID_' . $key, $post)) {
                continue;
            }

            $eventName = trim((string) ($post['eventName_' . $key] ?? ($source->name ?? '')));

            if ($eventName === '') {
                continue;
            }

            $existing = $this->findEventByName($eventName);

            if ($existing !== null) {
                $eventMessage .= '<span style="color:orange">Using existing eventtype data: </span><strong>'
                    . $this->escape((string) $existing->name) . '</strong><br />';
                continue;
            }

            $event = $this->copyTableFields($source, '#__sportsmanagement_eventtype');
            $event->name = $eventName;
            $event->sports_type_id = $sportTypeId;
            $event->alias = OutputFilter::stringURLSafe($eventName);

            if (!$this->database->insertObject('#__sportsmanagement_eventtype', $event)) {
                throw new RuntimeException('Unable to store imported event type: ' . $eventName, 500);
            }

            $eventMessage .= '<span style="color:green">Created new eventtype data: </span><strong>'
                . $this->escape($eventName) . '</strong><br />';
        }

        return [
            'Importing sports type data:' => $sportTypeMessage,
            'Importing event data:' => $eventMessage,
        ];
    }

    /**
     * @param array<string, mixed> $post
     */
    private function resolveSportType(array $post): object
    {
        $sportTypeId = (int) ($post['sportstype'] ?? 0);

        if ($sportTypeId > 0) {
            $sportType = $this->findSportTypeById($sportTypeId);

            if ($sportType === null) {
                throw new RuntimeException('Selected sports type was not found.', 404);
            }

            $sportType->created = false;

            return $sportType;
        }

        $sportTypeName = substr(trim((string) ($post['sportstypeNew'] ?? '')), 0, 25);

        if ($sportTypeName === '') {
            throw new RuntimeException('Missing sports type for standalone event import.', 400);
        }

        $sportType = $this->findSportTypeByName($sportTypeName);

        if ($sportType !== null) {
            $sportType->created = false;

            return $sportType;
        }

        $row = (object) ['name' => $sportTypeName];

        if (!$this->database->insertObject('#__sportsmanagement_sports_type', $row)) {
            throw new RuntimeException('Unable to store sports type: ' . $sportTypeName, 500);
        }

        $row->id = (int) $this->database->insertid();
        $row->created = true;

        return $row;
    }

    private function findSportTypeById(int $id): ?object
    {
        $query = $this->database->getQuery(true)
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_sports_type'))
            ->where($this->database->quoteName('id') . ' = ' . $id);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    private function findSportTypeByName(string $name): ?object
    {
        $query = $this->database->getQuery(true)
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_sports_type'))
            ->where($this->database->quoteName('name') . ' = ' . $this->database->quote($name));
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    private function findEventById(int $id): ?object
    {
        $query = $this->database->getQuery(true)
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_eventtype'))
            ->where($this->database->quoteName('id') . ' = ' . $id);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    private function findEventByName(string $name): ?object
    {
        $query = $this->database->getQuery(true)
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_eventtype'))
            ->where($this->database->quoteName('name') . ' = ' . $this->database->quote($name));
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    private function copyTableFields(object $source, string $table): object
    {
        $columns = $this->database->getTableColumns($table);
        $row = new \stdClass();

        foreach ($source as $field => $value) {
            $field = (string) $field;

            if ($field === 'id' || !array_key_exists($field, $columns)) {
                continue;
            }

            $row->{$field} = (string) $value;
        }

        return $row;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
