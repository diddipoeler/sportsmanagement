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
    /**
     * Create the position-to-event-type XML relationship importer.
     * Stores Joomla's database driver for resolving and inserting links between imported position and event-type definitions.
     *
     * @param DatabaseInterface $database Joomla database driver.
     * @return void
     */
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * Import position-to-event-type relations using earlier XML import steps.
     *
     * Only positive source identifiers and prepared local database IDs are
     * accepted. Reject nested, overflowing, partially numeric and malformed
     * source or form values instead of casting them to unrelated Joomla
     * records. Skip incomplete links and already-existing associations while
     * preserving the original import order. A database insert failure aborts
     * the import rather than reporting a partial success.
     *
     * @param array<string,mixed> $post Selected local IDs from administrator forms.
     * @param array<string,mixed> $parsedData Imported XML positions, events and relations.
     * @return void
     * @throws RuntimeException When a valid relation cannot be inserted.
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

            $oldEventId = self::positiveId($relation->eventtype_id ?? null);
            $oldPositionId = self::positiveId($relation->position_id ?? null);
            $eventId = $eventMap[$oldEventId] ?? 0;
            $positionId = $positionMap[$oldPositionId] ?? 0;

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
     * Map imported position or event-type IDs to their prepared local database IDs.
     * Combines the original XML source collection with resolved form fields.
     * Uses the same strict positive-decimal validation for source and local
     * IDs, preventing nested or overflowing imported values from pointing
     * to unrelated entities.
     *
     * @param array<string,mixed> $post Prepared form selections.
     * @param array<string,mixed> $parsedData Parsed XML import collections.
     * @param string $collection Source collection name, such as position or event.
     * @param string $fieldPrefix Form prefix containing resolved local IDs.
     * @return array<int,int> Mapping from original XML entity ID to local database ID.
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

            $oldId = self::positiveId($source->id ?? null);
            $databaseId = self::positiveId($post[$fieldPrefix . $key] ?? null);

            if ($oldId > 0 && $databaseId > 0) {
                $map[$oldId] = $databaseId;
            }
        }

        return $map;
    }

    /**
     * Parse a positive local or XML record ID without partial PHP casts.
     *
     * Accepts positive integers and decimal strings with leading zeroes.
     * Rejects negative numbers, floats, booleans, arrays, suffixes and
     * overflows so that invalid administrator form values cannot create
     * unintended position/event-type relationships.
     *
     * @param mixed $value Source XML ID or selected local database ID.
     * @return int Positive integer ID, or zero for invalid values.
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

        $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }

    /**
     * Check whether a position-to-event-type relationship already exists.
     * Uses bound position and event-type IDs to avoid inserting duplicate mapping records while importing XML relations.
     *
     * @param int $positionId Local imported position ID.
     * @param int $eventId Local imported event-type ID.
     * @return bool True when the relation row exists.
     */
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
