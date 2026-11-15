<?php
/**
 * Joomla 5/6 native project position/statistic XML import service.
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

/**
 * Native writer for project XML import step 30.
 */
final class XmlProjectPositionStatisticImportService
{
    /**
     * Bind Joomla's database service for project position-statistic imports.
     *
     * @param DatabaseInterface $database Active native Joomla database.
     * @return void
     */
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * Import position/statistic relations using verified source and local IDs.
     *
     * Preserves existing import messages and duplicate-link detection.
     * Rejects malformed XML IDs, nested values, partial decimal strings and
     * overflowing destination IDs before database lookup or insertion.
     * An insert failure aborts instead of reporting incomplete success.
     *
     * @param array<string,mixed> $parsedData Legacy XML relationship records.
     * @param array<int,int> $positionMap Source-to-local position identifiers.
     * @param array<int,int> $statisticMap Source-to-local statistic identifiers.
     * @return array{messages:array<string,string>} Administrator status messages.
     * @throws RuntimeException When a valid link cannot be inserted.
     */
    public function import(array $parsedData, array $positionMap, array $statisticMap): array
    {
        $message = '';

        foreach (array_values((array) ($parsedData['positionstatistic'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = self::positiveId($source->id ?? null);
            $oldPositionId = self::positiveId($source->position_id ?? null);
            $oldStatisticId = self::positiveId($source->statistic_id ?? null);
            $positionId = self::positiveId($positionMap[$oldPositionId] ?? null);
            $statisticId = self::positiveId($statisticMap[$oldStatisticId] ?? null);

            if ($oldPositionId <= 0 || $positionId <= 0 || $oldStatisticId <= 0 || $statisticId <= 0) {
                $message .= '<span style="color:red">Skipping position-statistic ID <strong>'
                    . htmlspecialchars((string) $oldId, ENT_QUOTES, 'UTF-8')
                    . '</strong>; position/statistic mapping is unavailable.</span><br />';
                continue;
            }

            $existingId = $this->findExisting($positionId, $statisticId);

            if ($existingId > 0) {
                $message .= $this->message('Using existing', $positionId, $statisticId);
                continue;
            }

            $row = (object) [
                'position_id' => $positionId,
                'statistic_id' => $statisticId,
            ];

            if (!$this->database->insertObject('#__sportsmanagement_position_statistic', $row)) {
                throw new RuntimeException('Unable to store imported position/statistic relation.', 500);
            }

            $message .= $this->message('Created new', $positionId, $statisticId);
        }

        return ['messages' => ['Importing position statistic data:' => $message]];
    }

    /**
     * Read a complete positive ID from imported XML, mapping or database data.
     *
     * Supports zero-padded decimal strings but rejects booleans, arrays,
     * floats, trailing text, negatives and integer overflow, preventing
     * unintended references to unrelated position/statistic records.
     *
     * @param mixed $value Raw XML or database identifier.
     * @return int Positive ID, or zero when the value is malformed.
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
     * Look up an existing relation through bound integer query parameters.
     *
     * A malformed primary-key result is treated as absent rather than being
     * partially cast to another integer ID by PHP.
     *
     * @param int $positionId Valid local position ID.
     * @param int $statisticId Valid local statistic ID.
     * @return int Existing positive association ID, or zero when absent.
     */
    private function findExisting(int $positionId, int $statisticId): int
    {
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('id'))
            ->from($this->database->quoteName('#__sportsmanagement_position_statistic'))
            ->where($this->database->quoteName('position_id') . ' = :positionId')
            ->where($this->database->quoteName('statistic_id') . ' = :statisticId')
            ->bind(':positionId', $positionId, ParameterType::INTEGER)
            ->bind(':statisticId', $statisticId, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);

        return self::positiveId($this->database->loadResult());
    }

    /**
     * Render an escaped legacy administrator relation status message.
     *
     * @param string $action Internal created/existing action label.
     * @param int $positionId Verified local position ID.
     * @param int $statisticId Verified local statistic ID.
     * @return string Escaped status HTML with legacy wording.
     */
    private function message(string $action, int $positionId, int $statisticId): string
    {
        $color = $action === 'Created new' ? 'green' : 'orange';

        return '<span style="color:' . $color . '">' . $action
            . ' position-statistic data. Position: </span><strong>'
            . htmlspecialchars((string) $positionId, ENT_QUOTES, 'UTF-8')
            . '</strong> / Statistic: <strong>'
            . htmlspecialchars((string) $statisticId, ENT_QUOTES, 'UTF-8')
            . '</strong><br />';
    }
}
