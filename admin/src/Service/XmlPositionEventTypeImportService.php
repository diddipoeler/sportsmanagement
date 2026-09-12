<?php
/**
 * Joomla 5/6 native position-eventtype XML import service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use RuntimeException;

/** Native writer for project XML import step 8. */
final class XmlPositionEventTypeImportService
{
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * Import position/event-type links using IDs prepared by steps 4 and 7.
     *
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     */
    public function import(array $post, array $parsedData): void
    {
        $relations = array_values((array) ($parsedData['positioneventtype'] ?? []));

        if ($relations === []) {
            return;
        }

        $eventMap = $this->buildPreparedMap($post, $parsedData, 'event', 'dbEventID_');
        $positionMap = $this->buildPreparedMap($post, $parsedData, 'position', 'dbPositionID_');

        foreach ($relations as $relation) {
            if (!is_object($relation)) {
                continue;
            }

            $oldEventId = (int) ($relation->eventtype_id ?? 0);
            $oldPositionId = (int) ($relation->position_id ?? 0);
            $eventId = (int) ($eventMap[$oldEventId] ?? 0);
            $positionId = (int) ($positionMap[$oldPositionId] ?? 0);

            // Keep the historical behavior: incomplete relations are skipped.
            if ($eventId <= 0 || $positionId <= 0) {
                continue;
            }

            if ($this->linkExists($positionId, $eventId)) {
                continue;
            }

            $row = (object) [
                'position_id' => $positionId,
                'eventtype_id' => $eventId,
            ];

            if (!$this->database->insertObject('#__sportsmanagement_position_eventtype', $row)) {
                throw new RuntimeException(
                    'Unable to store imported position/event-type relation.',
                    500
                );
            }
        }
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     *
     * @return array<int, int>
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

    private function linkExists(int $positionId, int $eventId): bool
    {
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('id'))
            ->from($this->database->quoteName('#__sportsmanagement_position_eventtype'))
            ->where($this->database->quoteName('position_id') . ' = :positionId')
            ->where($this->database->quoteName('eventtype_id') . ' = :eventId')
            ->bind(':positionId', $positionId, ParameterType::INTEGER)
            ->bind(':eventId', $eventId, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadResult() !== null;
    }
}
