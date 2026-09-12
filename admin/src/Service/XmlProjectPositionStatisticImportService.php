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
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $positionMap
     * @param array<int, int> $statisticMap
     *
     * @return array{messages:array<string,string>}
     */
    public function import(array $parsedData, array $positionMap, array $statisticMap): array
    {
        $message = '';

        foreach (array_values((array) ($parsedData['positionstatistic'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = max(0, (int) ($source->id ?? 0));
            $oldPositionId = max(0, (int) ($source->position_id ?? 0));
            $oldStatisticId = max(0, (int) ($source->statistic_id ?? 0));
            $positionId = (int) ($positionMap[$oldPositionId] ?? 0);
            $statisticId = (int) ($statisticMap[$oldStatisticId] ?? 0);

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

        return (int) ($this->database->loadResult() ?? 0);
    }

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
